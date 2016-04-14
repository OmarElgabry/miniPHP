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
     * @param int|string $code
     *
     */
    public function error($code){

        $errors = [
            404 => "notfound",
            500 => "system",
            400 => "badrequest",
            401 => "unauthorized",
            403 => "forbidden"
        ];

        if(!isset($errors[$code]) || !method_exists("ErrorsController", $errors[$code])){
            $code = 500;
        }

        $action = isset($errors[$code])? $errors[$code]: "System";
        $this->response->setStatusCode($code);

        // clear, get page, then send headers
        $this->response->clearBuffer();
        (new ErrorsController())->{$action}();
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
     * forces SSL request
     *
     * @see core/components/SecurityComponent::secureRequired()
     */
    public function forceSSL(){
        $secured  = "https://" . $this->request->currentUrl();
        Redirector::to($secured);
    }
}
