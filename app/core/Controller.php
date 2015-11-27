<?php

/**
 * The controller class.
 *
 * The base controller for all other controllers.
 * Extend this for each created controller
 *
 * @license    http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author     Omar El Gabry <omar.elgabry.93@gmail.com>
 */

class Controller {

    /**
     * set of variables that will converted into JS vars in the footer.php
     *
     * @var
     * @see views/layout/footer.php
     */
    public $vars =[
        'root' => PUBLIC_ROOT,          /* public root used in ajax calls and redirection from client-side */
        'curPage' => null,              /* identifies the current page(s), and it will be used to add 'active' css class on navigation */
        'curPageId' => null,            /* the current page's id if exists, like in viewPost, and viewUser */
        'fileSizeOverflow' => 10485760  /* max file size, this is important to avoid overflow in files with big size */
    ];

    /**
     * view
     *
     * @var View
     */
    protected $view;

    /**
     * request
     *
     * @var Request
     */
    public $request;

    /**
     * response
     *
     * @var Response
     */
    protected $response;

    /**
     * loaded components
     *
     * @var array
     */
    public $components = [];

    /**
     * Constructor
     *
     * @param Request  $request
     * @param Response $response
    */
    public function __construct(Request $request = null, Response $response = null){

        $this->request  =  $request  !== null ? $request  : new Request();
        $this->response =  $response !== null ? $response : new Response();
        $this->view     =  new View($this);

        // events that that will be triggered for each controller:

        // 1. load components
        $this->initialize();

        // 2. any logic before calling controller's action(method)
        $this->beforeAction();

        // 3. trigger startup method of loaded components
        $this->triggerComponents();
    }

    /**
     * triggers component startup methods.
     * But, for auth, we are calling authentication and authorization separately
     *
     * You need to Fire the Components and Controller callbacks in the correct order,
     * For example, Authorization depends on form element, so you need to trigger Security first.
     *
     */
    public function triggerComponents(){

        // You need to Fire the Components and Controller callbacks in the correct orde
        // For example, Authorization depends on form element, so you need to trigger Security first.

        // We supposed to execute startup() method of each component,
        // but since we need to call Auth -> authenticate, then Security, Auth -> authorize separately

        // re-construct components in right order
        $components = ['Auth', 'Security'];
        foreach($components as $key => $component){
            if(!in_array($component, $this->components)){
                unset($components[$key]);
            }
        }

        foreach($components as $component){

            if($component === "Auth"){

                $authenticate = $this->Auth->config("authenticate");
                if(!empty($authenticate)){
                    if(!$this->Auth->authenticate()){
                        $this->Auth->unauthenticated();
                    }
                }

                // delay checking authorize till after the loop
                $authorize = $this->Auth->config("authorize");
                continue;

            }

            $this->{$component}->startup();
        }

        // authorize
        if(!empty($authorize)){
            if(!$this->Auth->authorize()){
                $this->Auth->unauthorized();
            }
        }
    }

    /**
     * Initialization method.
     * initialize components and optionally, assign configuration data
     *
     */
     public function initialize(){

         $this->loadComponents([
             'Auth' => [
                     'authenticate' => ['User'],
                     'authorize'    => ['Controller']
                 ],
             'Security'
         ]);
     }

    /**
     * load the components by setting the component's name to a controller's property.
     *
     * @param array $components
     */
    public function loadComponents(array $components){

        if(!empty($components)){

            $components = Utility::normalize($components);

            foreach($components as $component => $config){

                if(!in_array($component, $this->components, true)){
                    $this->components[] = $component;
                }

                $class = $component . "Component";
                $this->{$component} = empty($config)? new $class($this): new $class($this, $config);
            }
        }
    }

    /**
     * show error page
     *
     * call error action method and set response status code
     * This will work as well for ajax call, see how ajax calls are handled in main.js
     *
     * @param string $error
     *
     */
    public function error($error){

        $errorController = new ErrorsController();
        if(!method_exists("ErrorsController", $error)){
            $error = "System";
        }

        $errors = [
            "notfound"      => 404,
            "system"        => 500,
            "badrequest"    => 400,
            "unauthorized"  => 401,
            "forbidden"     => 403,
        ];

        $code = isset($errors[strtolower($error)])? $errors[strtolower($error)]: 500;
        $this->response->setStatusCode($code);

        // clear, get page, then send headers
        $this->response->clearBuffer();
        $errorController->{$error}();
        $this->response->send();
    }

    /**
     * Called before the controller action.
     * Used to perform logic that needs to happen before each controller action.
     *
     */
    public function beforeAction(){}

    /**
     * Magic accessor for model autoloading.
     *
     * @param  string $name Property name
     * @return object The model instance
     */
    public function __get($name) {
        return $this->loadModel($name);
    }

    /**
     * load model
     * It assumes the model's constructor doesn't need parameters for constructor
     *
     * @param string  $model class name
     */
    public function loadModel($model){
        return $this->{$model} = new $model();
    }

    /**
     * add a new variable to set of variables declared in $vars
     *
     * @param string $key
     * @param mixed  $value
     */
    public function addVar($key, $value){
        $this->vars[$key] = $value;
    }

    /**
     * forces SSL request
     *
     * @see core/components/SecurityComponent::secureRequired()
     */
    public function forceSSL(){
        $secured  = "https://" . $this->request->currentUrl();
        Redirector::to($secured);
    }
}
