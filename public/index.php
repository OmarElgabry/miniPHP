<?php

/*
|--------------------------------------------------------------------------
| Autoload
|--------------------------------------------------------------------------
|
| After running "composer install", we can use the autoloader file created.
|
*/

require  '../vendor/autoload.php';

/*
|--------------------------------------------------------------------------
| Register Error & Exception handlers
|--------------------------------------------------------------------------
|
| Here we will register the methods that will fire whenever there is an error
| or an exception has been thrown.
|
*/
Handler::register();

/*
|--------------------------------------------------------------------------
| Define Constants
|--------------------------------------------------------------------------
| 
| Define the main paths the application need to run 
|
*/

// path to public root directory where your index.php, css, and js files
define('PUBLIC_ROOT', 'http://' . Environment::get('HTTP_HOST') . str_replace(['public', '\\'], ['', '/'], dirname(Environment::get('SCRIPT_NAME'))));

// path to the directory that has all of your "app", "public", "vendor", ... directories
define('BASE_DIR', str_replace("\\", "/", dirname(__DIR__)));

// path to upload images, don't use it for displaying images, use "PUBLIC_ROOT/img/" instead
define('IMAGES',   str_replace("\\", "/", __DIR__) . "/img/");

// path to app directory
define('APP',  BASE_DIR . "/app/");

/*
|--------------------------------------------------------------------------
| Start Session
|--------------------------------------------------------------------------
|
*/
Session::init();

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| Here we will create the application instance which will take care of routing 
| the incoming request to the corresponding controller and action method if valid
|
*/
(new App())->run();

