<?php

/**
 * Security component class.
 *
 * Provides security methods for various tasks and validations.
 *
 * @license    http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author     Omar El Gabry <omar.elgabry.93@gmail.com>
 */

class SecurityComponent extends Component{

    /**
     * Default configurations
     *
     * @var array
     */
    protected $config = [
        'form' => [],
        'requireSecure' => [],
        'requirePost' => [],
        'requireAjax' => [],
        'requireGet' => [],
        'validateForm' => true,
        'validateCsrfToken' => false
    ];

    /**
     * Auth startup
     * All security checking are done in this method
     *
     */
    public function startup(){

        if(!$this->requestRequired()){
            return $this->invalidRequest();
        }

        if(!$this->secureRequired()){
            return $this->invalidRequest('forceSSL');
        }

        if(!$this->validateDomain()){
            return $this->invalidRequest();
        }

        if($this->request->isPost() && $this->config["validateForm"]){
            if(!$this->form($this->config["form"])){
                return $this->invalidRequest();
            }
        }

        if($this->config["validateCsrfToken"]){
            if(!$this->CsrfToken()){
                return $this->invalidRequest();
            }
        }
    }

    /**
     * Check & validate from the required HTTP methods, like: Post, Ajax, Get
     *
     * @return bool
    */
    private function requestRequired(){
        foreach (['Post', 'Ajax', 'Get'] as $method) {
            $key = 'require' . $method;
            if (!empty($this->config[$key])) {
                if (in_array($this->request->param('action'), $this->config[$key], true) || $this->config[$key] === ['*']) {
                    if (!$this->request->{"is" . $method}()) {
                        return false;
                    }
                }
            }
        }
        return true;
    }

    /**
     * Check & validate if secured connection is required.
     *
     * @return bool
     */
    private function secureRequired(){
        $key = "requireSecure";
        if(!empty($this->config[$key])){
            if (in_array($this->request->param('action'), $this->config[$key], true) || $this->config[$key] === ['*']) {
                if (!$this->request->isSSL()) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Check & validate if request is coming from the same domain; if equals to $this->request->host()
     * HTTP referer tells the domain where the request came from.
     *
     * @return bool
     */
    private function validateDomain(){

        $isValid = true;
        $referer = $this->request->referer();

        if($this->request->isPost()){
            if(!isset($referer)) {
                $isValid = false;
            } else {
                $referer_host = parse_url($referer, PHP_URL_HOST);
                $server_host  = $this->request->host();
                $isValid = ($referer_host === $server_host)? true: false;
            }
        }

        if(!$isValid){
            Logger::log("Request Domain", "User: ". Session::getUserId() ." Request is not coming from the same domain with invalid HTTP referer", __FILE__, __LINE__);
            return false;
        }
        return true;
    }

    /**
     * Handles invalid request with a 400 Bad Request Error If no callback is specified.
     *
     * @param string|null $callback
     * @return mixed
     * @throws Exception
     */
    private function invalidRequest($callback = null){
        if(is_callable([$this->controller, $callback])){
            return $this->controller->{$callback}();
        }
        throw new Exception('The request has been deined', 400);
    }

    /**
     * Sets the actions that require secured connection(SSL)
     *
     * @param array $actions
     */
    public function requireSecure($actions = []){
        $this->config['requireSecure'] = (array)$actions;
    }

    /**
     * Sets the actions that require a POST request
     *
     * @param array $actions
     */
    public function requirePost($actions = []){
        $this->config['requirePost'] = (array)$actions;
    }

    /**
     * Sets the actions that require a Ajax request
     *
     * @param array $actions
     */
    public function requireAjax($actions = []){
        $this->config['requireAjax'] = (array)$actions;
    }

    /**
     * Sets the actions that require a GET request
     *
     * @param array $actions
     */
    public function requireGet($actions = []){
        $this->config['requireGet'] = (array)$actions;
    }

     /**
      * validate submitted form
      * - Unknown fields cannot be added to the form.
      * - Fields cannot be removed from the form.
      *
      * Use $exclude to exclude anything mightn't be sent with the form, like possible empty arrays, checkboxes, radio buttons, ...etc.
      * By default, the submit field will be excluded.
      *
      * @param array  $config  configuration data
      * @return boolean
      */
    public function form($config){

        if(empty($config['fields']) || $this->request->dataSizeOverflow()){
             return false;
        }

        if(!in_array('csrf_token', $config['fields'], true)){
            $config['fields'][] = 'csrf_token';
        }

        // exclude any checkboxes, radio buttons, possible empty arrays, ...etc.
        $exclude = empty($config["exclude"])? []: (array)$config["exclude"];
        if(!in_array('submit', $exclude, true)){
            $exclude[] = 'submit';
        }

        if($this->request->countData($exclude) !== count($config['fields'])){
            Logger::log("Form Tampering", "User: ". Session::getUserId() ." is tampering the form with invalid number of fields", __FILE__, __LINE__);
            return false;
        }

        foreach($config['fields'] as $field){

            if(!array_key_exists($field, $this->request->data)){
                Logger::log("Form Tampering", "User: ". Session::getUserId() ." is tampering the form with invalid fields", __FILE__, __LINE__);
                return false;
            }
        }

        // by default, validate csrf token as well.
        return $this->CsrfToken();
    }

     /**
      * validate CSRF token
      * CSRF token can be passed with submitted forms and links associated with sensitive server-side operations.
      *
      * In case of GET request, you need to set 'validateCsrfToken' in $config to true.
      *
      * @param array  $config  configuration data
      * @return boolean
      */
    public function CsrfToken($config = []){

        $userToken = null;
        if($this->request->isPost()){
            $userToken = $this->request->data('csrf_token');
        }else{
            $userToken = $this->request->query('csrf_token');
        }

        if(empty($userToken) || $userToken !== Session::getCsrfToken()){
            Logger::log("CSRF Attack", "User: ". Session::getUserId() ." provided invalid CSRF Token " . $userToken, __FILE__, __LINE__);
            return false;
        }

        return $userToken === Session::getCsrfToken();
    }

}
