<?php

/**
 * The redirector class.
 *
 * Provides multiple options for redirection
 *
 * @license    http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author     Omar El Gabry <omar.elgabry.93@gmail.com>
 */

class Redirector{

    /**
     * Constructor
     *
     */
    private function __construct(){}

    /**
     * Redirect to the given location
     *
     * @param string $location
     */
    public static function to($location){
        $response = new Response('', 302, ["Location" => $location]);
        $response->send();
    }

    /**
     * Redirect to the dashboard
     */
    public static function dashboard(){
        self::to(PUBLIC_ROOT . "User");
    }

    /**
     * Redirect to the login page
     * $redirect_url is to send the user back to where he/she came from after login
     *
     * @param string|null $redirect_url
     */
    public static function login($redirect_url = null){
        if(!empty($redirect_url)){
            self::to(PUBLIC_ROOT . "?redirect=" . urlencode($redirect_url));
        }else{
            self::to(PUBLIC_ROOT);
        }
    }

} 
