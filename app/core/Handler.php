<?php

 /**
  * Handler class.
  *
  * Provides basic error and exception handling for your application.
  * It captures and handles all unhandled exceptions and errors.
  *
  * @license    http://opensource.org/licenses/MIT The MIT License (MIT)
  * @author     Omar El Gabry <omar.elgabry.93@gmail.com>
  */

class Handler{

    /**
     * Constructor
     *
     */
    private function __construct(){}

    /**
     * Register the error and exception handlers.
     * Must be called at the beginning of your application
     *
     * @return void
     */
    public static function register(){

        // turn off all error reporting as well,
        // because we will take care of it
        error_reporting(0);

        set_error_handler(__CLASS__ . "::handleError");
        set_exception_handler(__CLASS__ .'::handleException');
        register_shutdown_function(__CLASS__ ."::handleFatalError" );
    }

     /**
      * Handle fatal errors
      *
      * @return void
      */
    public static function handleFatalError(){

        if (PHP_SAPI === 'cli') { return; }
        $error = error_get_last();

        if (!is_array($error)) { return; }

        $fatals = [E_USER_ERROR, E_ERROR, E_PARSE];

        if (!in_array($error['type'], $fatals, true)) {
            return;
        }

        // self::handleError($error['type'], $error['message'], $error['file'], $error['line'], null);
        self::handleException(new ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']));
    }

    /**
     * Handle errors
     *
     * @return void
     * @throws ErrorException
     */
    public static function handleError($errno, $errmsg, $filename, $linenum, $vars){
        throw new ErrorException($errmsg, 0, $errno, $filename, $linenum);
    }

    /**
     * Handle & log exceptions
     *
     * @param  Throwable  $e
     * @return void
     * @see http://php.net/manual/en/function.set-exception-handler.php
     */
    public static function handleException($e) {
        Logger::Log(get_class($e), $e->getMessage(), $e->getFile(), $e->getLine());
        self::render($e)->send();
    }

    /**
     * display system error page as result of an error or exception
     *
     * @param  Throwable  $e
     * @return Response
     */
    private static  function render($e){

        if($e->getCode() === 400){
            return (new ErrorsController())->error(400);
        }
        
        return (new ErrorsController())->error(500);
    }

    /**
     * Map an error code to error text
     *
     * @param int $errno
     * @return string error text
     */
    private static function errorType($errno){

        // define an assoc array of error string
        $errortype = array (
            E_ERROR              => 'Error',
            E_WARNING            => 'Warning',
            E_PARSE              => 'Parsing Error',
            E_NOTICE             => 'Notice',
            E_CORE_ERROR         => 'Core Error',
            E_CORE_WARNING       => 'Core Warning',
            E_COMPILE_ERROR      => 'Compile Error',
            E_COMPILE_WARNING    => 'Compile Warning',
            E_USER_ERROR         => 'User Error',
            E_USER_WARNING       => 'User Warning',
            E_USER_NOTICE        => 'User Notice',
            E_STRICT             => 'Runtime Notice',
            E_RECOVERABLE_ERROR  => 'Catchable Fatal Error'
        );

        return $errortype[$errno];
    }

}
