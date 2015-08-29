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
}
