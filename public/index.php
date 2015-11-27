<?php

// autoload classes,  dependencies(phpmailer, captcha) via composer
require  '../vendor/autoload.php';

// error & exception handlers
Error::register();

// path to public root directory where your index.php, css, and js files
define('PUBLIC_ROOT', 'http://' . Environment::get('HTTP_HOST') . str_replace(['public', '\\'], ['', '/'], dirname(Environment::get('SCRIPT_NAME'))));

// path to the directory that has all of your "app", "public", "vendor", ... directories
define('BASE_DIR', str_replace("\\", "/", dirname(__DIR__)));

// don't use it for displaying images, use "PUBLIC_ROOT/img/" instead
// It's used to upload images.
define('IMAGES',   str_replace("\\", "/", __DIR__) . "/img/");

// path to app directory
define('APP',  BASE_DIR . "/app/");

// start session
Session::init();

$app = new App();

