<?php

 /**
  * Logger class
  *
  * Used mainly to log failures, errors, exceptions, or any other malicious actions or attacks.
  *
  * @license    http://opensource.org/licenses/MIT The MIT License (MIT)
  * @author     Omar El Gabry <omar.elgabry.93@gmail.com>
  */

class Logger{

    /**
     * Constructor
     *
     */
    private function __construct(){}

    /**
     * log
     *
     * @access public
     * @static static method
     * @param  string  $header
     * @param  string  $message
     * @param  string  $filename
     * @param  string  $linenum
     */
    public static function log($header="", $message="", $filename="", $linenum=""){

        $logfile = APP . "logs/log.txt";
        $date = date("d/m/Y G:i:s");
        $err = $date." | ".$filename." | ".$linenum." | ".$header. "\n";

        $message = is_array($message)? implode("\n", $message): $message;
        $err .= $message . "\n*******************************************************************\n\n";

        // log/write error to log file
        error_log($err, 3, $logfile);
     }

 }