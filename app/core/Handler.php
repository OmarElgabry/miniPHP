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

        set_error_handler(__CLASS__ . "::errorHandler");
        set_exception_handler(__CLASS__ .'::exceptionHandler');
        register_shutdown_function(__CLASS__ ."::fatalErrorHandler" );
    }

     /**
      * Handle & log fatal errors
      *
      * @return void
      */
    public static function fatalErrorHandler(){

        if (PHP_SAPI === 'cli') { return; }
        $error = error_get_last();

        if (!is_array($error)) { return; }

        $fatals = [E_USER_ERROR, E_ERROR, E_PARSE];

        if (!in_array($error['type'], $fatals, true)) {
            return;
        }

        // self::exceptionHandler(new Exception($error['message'], 500));
        self::errorHandler($error['type'], $error['message'], $error['file'], $error['line'], null);

    }

    /**
     * Handle & log errors
     *
     * @return void
     * @see http://php.net/manual/en/errorfunc.examples.php
     */
    public static function errorHandler($errno, $errmsg, $filename, $linenum, $vars){

        // set of errors for which a var trace will be saved
        $user_errors = array(E_USER_ERROR, E_USER_WARNING, E_USER_NOTICE);

        $err  = "<errorentry>\n";
        $err .= "\t<errortype>" . self::errorType($errno) . "</errortype>\n";
        $err .= "\t<errormsg>" . $errmsg . "</errormsg>\n";

        if (in_array($errno, $user_errors)) {
            $err .= "\t<vartrace>" . wddx_serialize_value($vars, "Variables") . "</vartrace>\n";
        }
        $err .= "</errorentry>\n\n";

        Logger::Log("ERROR", $err, $filename, $linenum);
        self::viewError();

    }

    /**
     * Handle & log exceptions
     *
     * @return void
     * @see http://php.net/manual/en/function.set-exception-handler.php
     */
    public static function exceptionHandler($e) {

        Logger::Log(get_class($e), $e->getMessage(), $e->getFile(), $e->getLine());
        self::viewError();
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

    /**
     * display system error page as result or error or exception
     *
     */
    private static  function viewError(){
        (new ErrorsController())->error(500);
    }

}
