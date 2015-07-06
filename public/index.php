<?php

//path to public root directory where your index.php, css, and js files
define('PUBLIC_ROOT', 'http://' . $_SERVER['HTTP_HOST'] . str_replace(['public', '\\'], ['', '/'], dirname($_SERVER['SCRIPT_NAME'])));

//path to the directory that has all of your "app", "public", "vendor", ... directories
define('BASE_DIR', str_replace("\\", "/", dirname(__DIR__)));

//don't use it for displaying images, use "PUBLIC_ROOT/img/" instead
//It's used to upload images.
define('IMAGES',   str_replace("\\", "/", __DIR__) . "/img/");

//path to app directory
define('APP',  BASE_DIR . "/app/");

//session
session_start();

//configuration
require_once(APP . "config/config.php");

//autoload classes,  dependencies(phpmailer, captcha) via composer
require  '../vendor/autoload.php';

//error & exception handlers
Error::register();

$app = new App();

