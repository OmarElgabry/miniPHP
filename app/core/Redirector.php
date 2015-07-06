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
     */
    public static function login(){
        self::to(PUBLIC_ROOT);
    }

} 
