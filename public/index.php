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
| Define Application Configuration Constants
|--------------------------------------------------------------------------
| 
| PUBLIC_ROOT: 	the root URL for the application (see below).
| BASE_DIR: 	path to the directory that has all of your "app", "public", "vendor", ... directories.
| IMAGES:		path to upload images, don't use it for displaying images, use Config::get('root') . "/img/" instead.
| APP:			path to app directory.
|
*/

// Config::set('base', str_replace("\\", "/", dirname(__DIR__)));
// Config::set('images', str_replace("\\", "/", __DIR__) . "/img/");
// Config::set('app', Config::get('base') . "/app/");

define('BASE_DIR', str_replace("\\", "/", dirname(__DIR__)));
define('IMAGES',   str_replace("\\", "/", __DIR__) . "/img/");
define('APP',  BASE_DIR . "/app/");

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

$app = new App();

// Config::set('root', $app->request->root());
define('PUBLIC_ROOT', $app->request->root());

/*
|--------------------------------------------------------------------------
| Run The Application
|--------------------------------------------------------------------------
| 
| Once we have the application instance, we can handle the incoming request
| and send a response back to the client's browser.
|
*/

$app->run();