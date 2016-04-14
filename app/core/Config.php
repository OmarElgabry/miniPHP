<?php

/**
 * Config class.
 * Gets a configuration value
 *
 * @license    http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author     Omar El Gabry <omar.elgabry.93@gmail.com>
 */

class Config{

    /**
     * Array of configurations
     *
     * @var array
     */
    public static $config = null;

    /**
     * Array of javascript configurations
     *
     * @var array
     */
    public static $jsConfig = null;

    /**
     * Gets a configuration value
     *
     * @param $key string
     * @return string|null
     * @throws Exception if configuration file doesn't exist
     */
    public static function get($key){

        if (!self::$config) {

	        $config_file = APP . 'config/config.php';

			if (!file_exists($config_file)) {
				throw new Exception("Configuration file doesn't exist");
			}

	        self::$config = require $config_file . "";
        }

        return isset(self::$config[$key])? self::$config[$key]: null;
    }

    /**
     * Loads javascript configurations
     *
     * @param $key string
     * @return string|null
     * @throws Exception if configuration file doesn't exist
     */
    private static function loadJsConfig(){

        if (!self::$jsConfig) {

            $config_file = APP . 'config/javascript.php';

            if (!file_exists($config_file)) {
                throw new Exception("JavaScript Configuration file doesn't exist");
            }

            self::$jsConfig = require $config_file . "";
        }
    }

    /**
     * Gets javascript configuration value(s)
     *
     * @param $key string
     * @return string|array|null
     * @throws Exception if configuration file doesn't exist
     */
    public static function getJsConfig($key = ""){
        
        if (!self::$jsConfig) {
            self::loadJsConfig();
        }

        if(empty($key)){
            return self::$jsConfig;
        }else if(isset(self::$jsConfig[$key])){
            return self::$jsConfig[$key];
        }
        return null;
    }

    /**
     * Adds a new variable to javascript configurations
     *
     * @param string $key
     * @param mixed  $value
     */
    public static function addJsConfig($key, $value){
        if (!self::$jsConfig) {
            self::loadJsConfig();
        }
        self::$jsConfig[$key] = $value;
    }
}
