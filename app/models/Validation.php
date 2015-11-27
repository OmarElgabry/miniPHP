<?php

/**
 * Validation class
 *
 * A small library for validation.
 * It has plenty of validation rules.
 *
 * @license    http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author     Omar El Gabry <omar.elgabry.93@gmail.com>
 */

class Validation {

    /**
     * validation errors
     *
     *
     * @var array
     */
    private $errors = [];

    /**
     * Custom rule messages.
     *
     * @var array
     */
    private $ruleMessages = [];

     /**
      * Start the validation using values and rules passed in $data
      *
      * @param  array  $data
      * @param  bool   $skip To skip validations as soon as one of the rules fails.
      *
      * @throws Exception if rule method doesn't exist
      * @return bool
      */
    public function validate($data, $skip = false){

        $passed = true;

        foreach($data as $placeholder => $rules){

            $value = $rules[0];
            $rules = explode('|', $rules[1]);

            // no need to validate the value if the value is empty and not required
            if(!$this->isRequired($rules) && $this->isEmpty($value)){
                continue;
            }

            // it doesn't make sense to continue and validate the rest of rules on an empty & required value.
            // instead add error, and skip this value.
            if($this->isRequired($rules) && $this->isEmpty($value)){
                $this->addError("required", $placeholder, $value);
                $passed = false;
                continue;
            }

            foreach($rules as $rule){

                $method = $rule;
                $args = [];

                // if it was empty and required or not required,
                // it would be detected by the previous ifs
                if($rule === "required") {
                    continue;
                }

                if(self::isruleHasArgs($rule)){

                    // get arguments for rules like in max(), min(), ..etc.
                    $method = $this->getRuleName($rule);
                    $args   = $this->getRuleArgs($rule);
                }

                if(!method_exists($this, $method)){
                    throw new Exception("Method doesnt exists: " . $method);
                }

                if(!call_user_func_array([$this, $method], [$value, $args])) {

                    $this->addError($method, $placeholder, $value, $args);
                    $passed = false;

                    if($skip){ return false; }
                }
            }
        }

        // possible change is to return the current validation object,
        // and use passes() instead.
        return $passed;
    }

    /**
     * Determine if a given value is empty,
     * excluding '0', false, 0, 0.0, and files uploaded with UPLOAD_ERR_NO_FILE error,
     * because these could be perfectly valid values,
     * then, the validation methods has to decide if this value is valid or not.
     *
     * @param  mixed  $value
     * @return bool
     *
     */
    private function isEmpty($value){

        if(is_null($value)) {
            return true;
        }
        else if(is_string($value)){
            if(trim($value) === '') return true;
        }
        else if (empty($value) && $value !== '0' && $value !== false && $value !== 0 && $value !== 0.0){
            return true;
        }
        else if (is_array($value) && isset($value['name'], $value['type'], $value['tmp_name'], $value['error'])) {
            return (int)$value['error'] === UPLOAD_ERR_NO_FILE;
        }
        return false;
     }

    /**
     * Determine if a given rules has 'required' rule
     *
     * @param  array  $rules
     * @return bool
     */
    private function isRequired($rules){
        return in_array("required", $rules, true);
    }

    /**
     * Determine if a given rule has arguments, Ex: max(4)
     *
     * @param  string  $rule
     * @return bool
     */
    private function isruleHasArgs($rule) {
        return isset(explode('(', $rule)[1]);
    }

    /**
     * get rule name for rules that have args
     *
     * @param  string  $rule
     * @return string
     */
    private function getRuleName($rule){
        return explode('(', $rule)[0];
    }

    /**
     * get arguments for rules that have args
     *
     * @param  string  $rule
     * @return array
     */
    private  function getRuleArgs($rule){

        $argsWithBracketAtTheEnd = explode('(', $rule)[1];
        $args = rtrim($argsWithBracketAtTheEnd, ')');
        $args = preg_replace('/\s+/', '', $args);

        // as result of an empty array coming from user input
        // $args will be empty string,
        // So, using explode(',', empty string) will return array with size = 1
        // return empty($args)? []: explode(',', $args);
        return explode(',', $args);
    }

    /**
     * Add a custom rule message.
     * This message will be displayed instead of default.
     *
     * @param  string  $rule
     * @param  string  $message
     * @return array
     */
    public function addRuleMessage($rule, $message){
        $this->ruleMessages[$rule] = $message;
    }

    /**
     * Add an error
     *
     * @param  string  $rule
     * @param  string  $placeholder for field
     * @param  mixed   $value
     * @param  array   $args
     *
     */
    private function addError($rule, $placeholder, $value, $args = []){

        if(isset($this->ruleMessages[$rule])){
            $this->errors[] = $this->ruleMessages[$rule];
        }

        else{

            // get the default message for the current $rule
            $message = self::defaultMessages($rule);

            if(isset($message)){

                // if $message is set to empty string,
                // this means the error will be added inside the validation method itself
                // check attempts()
                if(trim($message) !== ""){

                    // replace placeholder, value, arguments with their values
                    $replace = ['{placeholder}', '{value}'];
                    $value   = is_string($value)? $value: "";
                    $with    = array_merge([$placeholder, $value], $args);
                    $count   = count($args);

                    // arguments will take the shape of: {0} {1} {2} ...
                    for($i = 0; $i < $count; $i++) $replace[] = "{{$i}}";

                    $this->errors[] = str_replace($replace, $with, $message);
                }

            } else{

                // if no message defined, then use this one.
                $this->errors[] = "The value you entered for " . $placeholder . " is invalid";
            }
        }
    }

    /**
     * Checks if validation has passed.
     *
     * @return bool
     */
    public function passes(){
        return empty($this->errors);
    }

    /**
     * get all errors
     *
     * @return array
     */
    public function errors(){
        return $this->errors;
    }

    /**
     * clear all existing errors
     *
     * @return bool
     */
    public function clearErrors(){
        $this->errors = [];
    }

    /** *********************************************** **/
    /** **************    Validations    ************** **/
    /** *********************************************** **/

    /**
     * Is value not empty?
     *
     * @param  mixed  $value
     * @return bool
     */
    /*private function required($value){
        return !$this->isEmpty($value);
    }*/

    /**
     * min string length
     *
     * @param  string  $str
     * @param  array  $args(min)
     *
     * @return bool
     */
    private function minLen($str, $args){
        return mb_strlen($str, 'UTF-8') >= (int)$args[0];
    }

    /**
     * max string length
     *
     * @param  string  $str
     * @param  array  $args(max)
     *
     * @return bool
     */
    private function maxLen($str, $args){
        return mb_strlen($str, 'UTF-8') <= (int)$args[0];
    }

    /**
     * check if number between given range of numbers
     *
     * @param  int     $num
     * @param  array   $args(min,max)
     * @return bool
     */
    private function rangeNum($num, $args){
        return $num >= (int)$args[0] && $num <= (int)$args[1];
    }

    /**
     * check if value is a valid number
     *
     * @param  string|integer  $value
     * @return bool
     */
    private function integer($value){
        return filter_var($value, FILTER_VALIDATE_INT);
    }

    /**
     * check if value(s) is in a given array
     *
     * @param  string|array  $value
     * @param  array         $arr
     * @return bool
     */
    private function inArray($value, $arr){

        if(is_array($value)){
            foreach($value as $val){
                if(!in_array($val, $arr, true)){
                    return false;
                }
            }
            return true;
        }
        return in_array($value, $arr, true);
    }

    /**
     * check if value is contains alphabetic characters and numbers
     *
     * @param  mixed   $value
     * @return bool
     */
    private function alphaNum($value){
        return preg_match('/\A[a-z0-9]+\z/i', $value);
    }

    /**
     * check if value is contains alphabetic characters, numbers and spaces
     *
     * @param  mixed   $value
     * @return bool
     */
    private function alphaNumWithSpaces($value){
        return preg_match('/\A[a-z0-9 ]+\z/i', $value);
    }

    /**
     * check if password has at least
     * - one lowercase letter
     * - one uppercase letter
     * - one number
     * - one special(non-word) character
     *
     * @param  mixed   $value
     * @return bool
     * @see http://stackoverflow.com/questions/8141125/regex-for-password-php
     * @see http://code.runnable.com/UmrnTejI6Q4_AAIM/how-to-validate-complex-passwords-using-regular-expressions-for-php-and-pcre
     */
    private function password($value) {
        return preg_match_all('$\S*(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])(?=\S*[\W])\S*$', $value);
    }

    /**
     * check if value is equals to another value(strings)
     *
     * @param  string  $value
     * @param  array   $args(value)
     * @return bool
     */
    private function equals($value, $args){
        return $value === $args[0];
    }

    /**
     * check if value is not equal to another value(strings)
     *
     * @param  string  $value
     * @param  array   $args(value)
     * @return bool
     */
    private function notEqual($value, $args){
        return $value !== $args[0];
    }

    /**
     * check if value is a valid email
     *
     * @param  string  $email
     * @return bool
     */
    private function email($email){
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    /** *********************************************** **/
    /** ************  Database Validations  *********** **/
    /** *********************************************** **/

    /**
     * check if a value of a column is unique.
     *
     * @param  string  $value
     * @param  array   $args(table, column)
     * @return bool
     */
    private function unique($value, $args){

        $table = $args[0];
        $col   = $args[1];

        $database = Database::openConnection();
        $database->prepare("SELECT * FROM {$table} WHERE {$col} = :{$col}");
        $database->bindValue(":{$col}", $value);
        $database->execute();

        return $database->countRows() === 0;

    }

    /**
     * check if email is unique
     * This will check if email exists and activated.
     *
     * @param  string  $email
     * @return bool
     */
    private function emailUnique($email){

        $database = Database::openConnection();

        // email is unique in the database, So, we can't have more than 2 same emails
        $database->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $database->bindValue(':email', $email);
        $database->execute();
        $user = $database->fetchAssociative();

        if ($database->countRows() === 1) {

            if(!empty($user["is_email_activated"])){
                return false;

            } else {

                $expiry_time = (24 * 60 * 60);
                $time_elapsed = time() - $user['email_last_verification'];

                // If time elapsed exceeded the expiry time, it worth to reset the token, and the email as well.
                // This indicates the email of $user hasn't been verified, and token is expired.
                if($time_elapsed >= $expiry_time) {

                    $login = new Login();
                    $login->resetEmailVerificationToken($user["id"], false);
                    return true;

                }else {

                    // TODO check if $email is same as current user's email(not-activated),
                    // then ask the user to verify his email
                    return false;
                }
            }
        }
        return true;
    }

    /** *********************************************** **/
    /** ************    Login Validations   *********** **/
    /** *********************************************** **/

    /**
     * check if user credentials are valid or not.
     *
     * @param  array   $user
     * @return bool
     * @see Login::doLogin()
     */
    private function credentials($user){
        if(empty($user["hashed_password"]) || empty($user["user_id"])) {
            return false;
        }
        return password_verify($user["password"], $user["hashed_password"]);
    }

    /**
     * check if user has exceeded number of failed logins or number of forgotten password attempts.
     *
     * @param  array   $attempts
     * @return bool
     */
    private function attempts($attempts){

        if(empty($attempts['last_time']) && empty($attempts['count'])) {
            return true;
        }

        $block_time = (10 * 60);
        $time_elapsed = time() - $attempts['last_time'];

        // TODO If user is Blocked, Update failed logins/forgotten passwords
        // to current time and optionally number of attempts to be incremented,
        // but, this will reset the last_time every time there is a failed attempt

        if ($attempts["count"] >= 5 && $time_elapsed < $block_time) {

            // here i can't define a default error message as in defaultMessages()
            // because the error message depends on variables like $block_time & $time_elapsed
            $this->errors[] = "You exceeded number of possible attempts, please try again later after " .
                date("i", $block_time - $time_elapsed) . " minutes";
            return false;

        }else{

            return true;
        }
    }

    /** *********************************************** **/
    /** ************    File Validations    *********** **/
    /** *********************************************** **/

    /**
     * checks if file unique.
     *
     * @param  array  $path
     * @return bool
     *
     * @see
     */
    private function fileUnique($path){
        return !file_exists($path);
    }

    /**
     * checks for file errors
     *
     * @param  array   $file
     * @return bool
     */
    private function fileErrors($file){
        return (int)$file['error'] === UPLOAD_ERR_OK;
    }

    /**
     * checks if file uploaded successfully via HTTP POST
     *
     * @param  array   $file
     * @return bool
     *
     * @see
     */
    private function fileUploaded($file){
        return is_uploaded_file($file["tmp_name"]);
    }

    /**
     * checks from file size
     *
     * @param  array   $file
     * @param  array   $args(min,max)
     * @return bool
     */
    private function fileSize($file, $args){

        // size in bytes,
        // 1 KB = 1024 bytes, and 1 MB = 1048,576 bytes.
        $size = array ("min" => (int)$args[0], "max" => (int)$args[1]);

        if ($file['size'] > $size['max']) {
            $this->errors[] = "File size can't exceed max limit (". ($size['max']/1048576) . " MB)";
            return false;
        }

        // better not to say the min limits.
        if($file['size'] < $size['min']){
            $this->errors[] = "File size either is too small or corrupted";
            return false;
        }
        return true;
    }

    /**
     * checks from image size(dimensions)
     *
     * @param  array   $file
     * @param  array   $dimensions(width,height)
     * @return bool
     */
    private function imageSize($file, $dimensions){

        $imageSize  = array('width' => 0, 'height' => 0);
        list($imageSize['width'], $imageSize['height'])   = getimagesize($file["tmp_name"]);

        if($imageSize["width"] < 10 || $imageSize["height"] < 10){
            $this->errors[] = "This image is too small or corrupted";
            return false;
        }
        if($imageSize["width"] > $dimensions[0] || $imageSize["height"] > $dimensions[1]){
            $this->errors[] = "Image width & height must be below ". $dimensions[0] ." pixels";
            return false;
        }
        return true;
    }

    /**
     * validate mime type
     *
     * @param  array   $file
     * @param  array   $mimeTypes
     * @throws Exception if finfo_open() doesn't exists
     * @return bool
     */
    private function mimeType($file, $mimeTypes){

        if(!file_exists($file["tmp_name"])){
            return false;
        }
        if(!function_exists('finfo_open')) {
            throw new Exception("Function finfo_open() doesn't exist");
        }

        $finfo_open = finfo_open(FILEINFO_MIME_TYPE);
        $finfo_file = finfo_file($finfo_open, $file["tmp_name"]);
        finfo_close($finfo_open);

        list($mime) = explode(';', $finfo_file);

        // in case of zip file it returns application/octet-stream
        return in_array($mime, $mimeTypes, true);
    }

    /**
     * validate file extension returned from pathinfo() Vs mapped mime type to extension
     *
     * This reveal un desired errors in case of files with extension: zip, csv, ..etc
     *
     * @param  array   $file
     * @param  array   $extension
     * @return bool
     */
    private function fileExtension($file, $extension){

        if(isset($extension[0])){
            return $extension[0] === pathinfo($file['name'])['extension'];
        }
        return false;
    }

    /** *********************************************** **/
    /** ************   Default Messages     *********** **/
    /** *********************************************** **/

    /**
     * get default message for a rule
     *
     * Instead of passing your custom message every time,
     * you can define a set of default messages.
     *
     * The pitfall of this method is, if you changed the validation method name,
     * you need to change it here as well.
     *
     * @param  string  $rule
     * @return mixed
     */
    private static function defaultMessages($rule){
        $messages = [
            "required" => "{placeholder} can't be empty",
            "minLen"   => "{placeholder} can't be less than {0} character",
            "maxLen"   => "{placeholder} can't be greater than {0} character",
            "rangeNum" => "{placeholder} must be between {0} and {1}",
            "integer"  => "{placeholder} must be a valid number",
            "inArray"  => "{placeholder} is not valid",
            "alphaNum" => "Only letters and numbers are allowed for {placeholder}",
            "alphaNumWithSpaces" => "Only letters, numbers and spaces are allowed for {placeholder}",
            "password"      => "Passwords must contain at least one lowercase, uppercase, number and special character",
            "equals"        => "{placeholder}s aren't equal",
            "notEqual"      => "{placeholder} can't be equal to {0}",
            "email"         => "Invalid email, Please enter a valid email address",
            "unique"        => "{placeholder} already exists",
            "emailUnique"   => "Email already exists",
            "credentials"   => "User ID & Password combination doesn't exist",
            "attempts"      => "",
            "fileUnique"    => "File already exists",
            "fileUploaded"  => "Your uploaded file is invalid!",
            "fileErrors"    => "There was an error with the uploaded file",
            "fileSize"      => "",
            "imageSize"     => "",
            "mimeType"      => "Your file format is invalid",
            "fileExtension" => "Your file format is invalid"
        ];

        return isset($messages[$rule])? $messages[$rule]: null;
    }
}
