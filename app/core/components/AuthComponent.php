<?php

/**
 * Authentication & Authorization component class.
 *
 * Authenticate & Authorize the current user.
 *
 * @license    http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author     Omar El Gabry <omar.elgabry.93@gmail.com>
 */

class AuthComponent extends Component{

    /**
     * Default configurations
     *
     * @var array
     */
    protected $config = [
        'authenticate' => [],
        'authorize' => []
    ];

    /**
     * Auth startup
     * All authentication and authorization checking are done in this method
     *
     */
    public function startup(){

         // authenticate
         if(!empty($this->config["authenticate"])){
             if(!$this->authenticate()){
                 return $this->unauthenticated();
             }
         }

         // authorize
         if(!empty($this->config["authorize"])){
             if(!$this->authorize()){
                 return $this->unauthorized();
             }
         }
     }

    /**
     * Handles unauthenticated access attempt.
     *
     */
    public function unauthenticated(){

        $this->controller->login->logOut(Session::getUserId());

        if($this->request->isAjax()) { 
            return $this->controller->error(401); 
        }else{
            $redirect = $this->controller->request->isGet()? $this->controller->request->uri(): "";
            return $this->controller->redirector->login($redirect); 
        }
    }

    /**
     * Handles unauthorized access attempt.
     *
     */
    public function unauthorized(){
        return $this->controller->error(403);
    }

     /**
      * authenticate the user using the defined methods in $config
      *
      * @return boolean
      */
     public function authenticate(){
         return $this->check($this->config["authenticate"], "authenticate");
     }

     /**
      * authorize the user using the defined methods in $config
      *
      * @return boolean
      */
    public function authorize(){
        return $this->check($this->config["authorize"], "authorize");
    }

     /**
      * check for authentication or authorization
      *
      * @param  array  $config
      * @param  string $type
      * @throws Exception if $config is empty or method doesn't exists
      * @return boolean
      */
     private function check($config, $type){

         if (empty($config)) {
             throw new Exception($type . ' methods arent initialized yet in config');
         }

         $auth = Utility::normalize($config);

         foreach($auth as $method => $config){

             $method = "_" . ucfirst($method) . ucfirst($type);

             if (!method_exists(__CLASS__, $method)) {
                 throw new Exception('Auth Method doesnt exists: ' . $method);
             }

             if($this->{$method}($config) === false){
                 return false;
             }
         }
        return true;
    }

    /**
     * Is user is already logged in via session or cookie?
     *
     * @return boolean
     */
    public function isLoggedIn(){

        if(Session::getIsLoggedIn() === true){
            return true;
        }

        if(Cookie::isCookieValid()){
            return true;
        }

        return false;
    }

     /**
      * Is user authorized for the requested Controller & Action method?
      *
      * @param array  $config  configuration data
      * @throws Exception if isAuthorized method doesn't exists in the controller class
      * @return boolean
      */
    private function _ControllerAuthorize($config){

        if (!method_exists($this->controller, 'isAuthorized')) {
            throw new Exception(sprintf('%s does not implement an isAuthorized() method.', get_class($this->controller)));
        }
        return (bool)$this->controller->isAuthorized();
    }

     /**
      * Is user authenticated?
      * It checks for:
      *     - concurrent session
      *     - user credentials in session & cookies
      *     - cookies theft and manipulations
      *     - session Hijacking and fixation.
      *
      * @param array  $config  configuration data
      * @return boolean
      */
    private function _UserAuthenticate($config){

        if($this->concurentSession()){
            return false;
        }

        if(!$this->loggedIn()){
            return false;
        }

        return true;
    }

    /**
     * Checks if user is logged in or not.
     * It uses Session and Cookies to validate the current user.
     *
     * @access public
     * @static static method
     * @return boolean
     *
     */
    private function loggedIn(){

        if (Session::isSessionValid($this->request->clientIp(), $this->request->userAgent())) {
            return true;
        }

        if (Cookie::isCookieValid()) {

            // get role from user class, because cookies don't store roles
            $role = $this->controller->user->getProfileInfo(Cookie::getUserId())["role"];
            Session::reset(["user_id" => Cookie::getUserId(), "role" => $role, "ip" => $this->request->clientIp(), "user_agent" => $this->request->userAgent()]);

            // reset cookie, Cookie token is usable only once
            Cookie::reset(Session::getUserId());

            return true;
        }

        return false;
    }

    private function concurentSession(){
        return Session::isConcurrentSessionExists();
    }

}
