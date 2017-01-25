<?php

/**
 * Login Class
 *
 * @license    http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author     Omar El Gabry <omar.elgabry.93@gmail.com>
 */

class Login extends Model{

    /**
     * register a new user
     *
     * @access public
     * @param  string  $name
     * @param  string  $email
     * @param  string  $password
     * @param  string  $confirmPassword
     * @param  array   $captcha holds the user's text and original captcha in session
     * @return bool
     *
     */
    public function register($name, $email, $password, $confirmPassword, $captcha){

        $isValid = true;
        $validation = new Validation();

        if(!$validation->validate([
            "User Name" => [$name, "required|alphaNumWithSpaces|minLen(4)|maxLen(30)"],
            "Email" => [$email, "required|email|emailUnique|maxLen(50)"],
            'Password' => [$password, "required|equals(".$confirmPassword.")|minLen(6)|password"],
            'Password Confirmation' => [$confirmPassword, 'required']])) {

            $this->errors = $validation->errors();
            $isValid = false;
        }

        // validate captcha
        if(empty($captcha['user']) || strtolower($captcha['user']) !== strtolower($captcha['session'])){
            $this->errors[] = "The entered characters for captcha don't match";
            $isValid = false;
        }

        if(!$isValid){
            return false;
        }

        $database = Database::openConnection();
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT, array('cost' => Config::get('HASH_COST_FACTOR')));

        // it's very important to use transaction to ensure both:
        // 1. user will be inserted to database
        // 2. the verification email will be sent
        $database->beginTransaction();
        $query = "INSERT INTO users (name, email, role, hashed_password, email_token, email_last_verification) ".
                 "VALUES (:name, :email, :role, :hashed_password, :email_token, :email_last_verification)";

        $database->prepare($query);
        $database->bindValue(':name', $name);
        $database->bindValue(':email', $email);
        $database->bindValue(':role', "user");
        $database->bindValue(':hashed_password', $hashedPassword);

        // email token and time of generating it
        $token = sha1(uniqid(mt_rand(), true));
        $database->bindValue(':email_token', $token);
        $database->bindValue(':email_last_verification', time());

        $database->execute();

        $id = $database->lastInsertedId();
        Email::sendEmail(Config::get('EMAIL_EMAIL_VERIFICATION'), $email, ["name" => $name, "id" => $id], ["email_token" => $token]);

        $database->commit();

        return true;
    }

    /**
     * login
     *
     * @param string $email
     * @param string $password
     * @param bool   $rememberMe
     * @param string $userIp
     * @param string $userAgent
     * @return bool
     */
    public function doLogIn($email, $password, $rememberMe, $userIp, $userAgent){

        // 1. check if user is blocked
        if($this->isIpBlocked($userIp)) {
            $this->errors[] = "Your IP Address has been blocked";
            return false;
        }

        // 2. validate only presence
        $validation = new Validation();
        if(!$validation->validate([
            "Your Email" => [$email, 'required'],
            "Your Password" => [$password, 'required']])){
            $this->errors = $validation->errors();
            return false;
        }

        // 3. check if user has previous failed login attempts
        $database = Database::openConnection();
        $database->getByUserEmail("failed_logins", $email);
        $failedLogin = $database->fetchAssociative();

        $last_time   = isset($failedLogin["last_failed_login"])? $failedLogin["last_failed_login"]: null;
        $count       = isset($failedLogin["failed_login_attempts"])? $failedLogin["failed_login_attempts"]: null;

        // check if the failed login attempts exceeded limits
        // @see Validation::attempts()
        if(!$validation->validate([
            'Failed Login' => [["last_time" => $last_time, "count" => $count], 'attempts']])){
            $this->errors = $validation->errors();
            return false;
        }

        // 4. get user from database
        $database->prepare("SELECT * FROM users WHERE email = :email AND is_email_activated = 1 LIMIT 1");
        $database->bindValue(':email', $email);
        $database->execute();
        $user = $database->fetchAssociative();

        $userId = isset($user["id"])? $user["id"]: null;
        $hashedPassword = isset($user["hashed_password"])? $user["hashed_password"]: null;

        // 5. validate data returned from users table
        if(!$validation->validate([
            "Login" => [["user_id" => $userId, "hashed_password" => $hashedPassword, "password" => $password], 'credentials']])){

            // if not valid, then increment number of failed logins
            $this->incrementFailedLogins($email, $failedLogin);

            // also, check if current IP address is trying to login using multiple accounts,
            // if so, then block it, if not, just add a new record to database
            $this->handleIpFailedLogin($userIp, $email);

            $this->errors = $validation->errors();
            return false;
        }

        // reset session
        Session::reset(["user_id" => $userId, "role" => $user["role"], "ip" => $userIp, "user_agent" => $userAgent]);

        // if remember me checkbox is checked, then save data to cookies as well
        if(!empty($rememberMe) && $rememberMe === "rememberme"){

            // reset cookie, Cookie token usable only once
            Cookie::reset($userId);

        } else {

            Cookie::remove($userId);
        }

        // if user credentials are valid then,
        // reset failed logins & forgotten password tokens
        $this->resetFailedLogins($email);
        $this->resetPasswordToken($userId);

        return true;
    }

    /**
     * block IP Address
     *
     * @access private
     * @param  string   $userIp
     *
     */
    private function blockIp($userIp){

        // if user is already blocked, this method won't be triggered
        /*if(!$this->isIpBlocked($userIp)){}*/

        $database = Database::openConnection();
        $database->prepare("INSERT INTO blocked_ips (ip) VALUES (:ip)");

        $database->bindValue(":ip", $userIp);
        $database->execute();
    }

    /**
     * is IP Address blocked?
     *
     * @access private
     * @param  string   $userIp
     * @return bool
     */
    private function isIpBlocked($userIp){

        $database = Database::openConnection();
        $database->prepare("SELECT ip FROM blocked_ips WHERE ip = :ip LIMIT 1");

        $database->bindValue(":ip", $userIp);
        $database->execute();

        return $database->countRows() >= 1;
    }

    /**
     * Adds a new record(if not exists) to ip_failed_logins table,
     * Also block the IP Address if number of attempts exceeded
     *
     * @access private
     * @param  string   $userIp
     * @param  string   $email
     */
    private function handleIpFailedLogin($userIp, $email){

        $database = Database::openConnection();
        $database->prepare("SELECT ip, user_email FROM ip_failed_logins WHERE ip = :ip ");
        $database->bindValue(":ip", $userIp);
        $database->execute();

        $ips   = $database->fetchAllAssociative();
        $count = count($ips);

        // block IP if there were failed login attempts using different emails(>= 10) from the same IP address
        if($count >= 10){

            $this->blockIp($userIp);

        } else {

            // check if ip_failed_logins already has a record with current ip + email
            // if not, then insert it.
            if(!in_array(["ip" => $userIp, "user_email" => $email], $ips, true)){
                $database->prepare("INSERT INTO ip_failed_logins (ip, user_email) VALUES (:ip, :user_email)");
                $database->bindValue(":ip", $userIp);
                $database->bindValue(":user_email", $email);
                $database->execute();
            }
        }
    }

    /**
     * Increment number of failed logins.
     *
     * @access private
     * @param  string   $email
     * @param  array    $failedLogin It determines if there was a previous record in the database or not
     * @throws Exception If couldn't increment failed logins
     *
     */
    private function incrementFailedLogins($email, $failedLogin){

        $database = Database::openConnection();

        if(!empty($failedLogin)){
            $query = "UPDATE failed_logins SET last_failed_login = :last_failed_login, " .
                     "failed_login_attempts = failed_login_attempts+1 WHERE user_email = :user_email";
        }else{
            $query = "INSERT INTO failed_logins (user_email, last_failed_login, failed_login_attempts) ".
                     "VALUES (:user_email, :last_failed_login, 1)";
        }

        // Remember? the user_email we are using here is not a foreign key from users table
        // Why? because this will block even un registered users
        $database->prepare($query);
        $database->bindValue(':last_failed_login', time());
        $database->bindValue(':user_email', $email);
        $result = $database->execute();

        if(!$result){
            throw new Exception("FAILED LOGIN", "Couldn't increment failed logins of User Email: " . $email, __FILE__, __LINE__);
        }
    }

    /**
     * Reset failed logins.
     *
     * @access private
     * @param  string   $email
     * @throws Exception If couldn't reset failed logins
     */
    private function resetFailedLogins($email){

        $database = Database::openConnection();
        $query = "UPDATE failed_logins SET last_failed_login = NULL, " .
                 "failed_login_attempts = 0 WHERE user_email = :user_email";

        $database->prepare($query);
        $database->bindValue(':user_email', $email);
        $result = $database->execute();

        if(!$result){
            throw new Exception("Couldn't reset failed logins for User Email " . $email);
        }
    }

    /**
     * What if user forgot his password?
     *
     * @param  string  $email
     * @return bool
     */
    public function forgotPassword($email){

        $validation = new Validation();
        if(!$validation->validate(['Email' => [$email, 'required|email']])) {
            $this->errors = $validation->errors();
            return false;
        }

        if($this->isEmailExists($email)){

            // depends on the last query made by isEmailExists()
            $database = Database::openConnection();
            $user     = $database->fetchAssociative();

            // If no previous records in forgotten_passwords, So, $forgottenPassword will be FALSE.
            $database->getByUserId("forgotten_passwords", $user["id"]);
            $forgottenPassword = $database->fetchAssociative();

            $last_time = isset($forgottenPassword["password_last_reset"])? $forgottenPassword["password_last_reset"]: null;
            $count     = isset($forgottenPassword["forgotten_password_attempts"])? $forgottenPassword["forgotten_password_attempts"]: null;

            if(!$validation->validate(['Failed Login' => [["last_time" => $last_time, "count" => $count], 'attempts']])){
                $this->errors = $validation->errors();
                return false;
            }

            // You need to get the new password token from the database after updating/inserting it
            $newPasswordToken = $this->generateForgottenPasswordToken($user["id"], $forgottenPassword);

            Email::sendEmail(Config::get('EMAIL_PASSWORD_RESET'), $user["email"], ["id" => $user["id"], "name" => $user["name"]], $newPasswordToken);
        }

        // This will return true even if the email doesn't exists,
        // because you don't want to give any clue
        // to (un)authenticated user if email is actually exists or not
        return true;
    }

    /**
     * Checks if email exists and activated in the database or not
     *
     * @access private
     * @param  string  $email
     * @return boolean
     *
     */
    private function isEmailExists($email){

        // email is already unique in the database,
        // So, we can't have more than 2 users with the same emails
        $database = Database::openConnection();
        $database->prepare("SELECT * FROM users WHERE email = :email AND is_email_activated = 1 LIMIT 1");
        $database->bindValue(':email', $email);
        $database->execute();

        return $database->countRows() === 1;
    }

    /**
     * Insert or Update(if already exists)
     *
     * @access private
     * @param  integer  $userId
     * @param  array    $forgottenPassword  It determines if there was a previous record in the database or not
     * @return array    new generated forgotten Password token
     * @throws Exception If couldn't generate the token.
     */
    private function generateForgottenPasswordToken($userId, $forgottenPassword){

        $database = Database::openConnection();

        if(!empty($forgottenPassword)){
            $query = "UPDATE forgotten_passwords SET password_token = :password_token, " .
                     "password_last_reset = :password_last_reset, forgotten_password_attempts = forgotten_password_attempts+1 ".
                     "WHERE user_id = :user_id";
        }else{
            $query = "INSERT INTO forgotten_passwords (user_id, password_token, password_last_reset, forgotten_password_attempts) ".
                     "VALUES (:user_id, :password_token, :password_last_reset, 1)";
        }

        // generate random hash for email verification (40 char string)
        $passwordToken = sha1(uniqid(mt_rand(), true));

        $database->prepare($query);
        $database->bindValue(':password_token', $passwordToken);
        $database->bindValue(':password_last_reset', time());
        $database->bindValue(':user_id', $userId);
        $result = $database->execute();

        if(!$result){
            throw new Exception("Couldn't generate token");
        }

        return ["password_token" => $passwordToken];
    }

    /**
     * Checks if forgotten password token is valid or not.
     *
     * @access public
     * @param  integer  $userId
     * @param  string   $passwordToken
     * @return boolean
     */
    public function isForgottenPasswordTokenValid($userId, $passwordToken){

        if (empty($userId) || empty($passwordToken)) {
            return false;
        }

        $database = Database::openConnection();
        $database->prepare("SELECT * FROM forgotten_passwords WHERE user_id = :user_id AND password_token = :password_token LIMIT 1");
        $database->bindValue(':user_id', $userId);
        $database->bindValue(':password_token', $passwordToken);
        $database->execute();
        $forgottenPassword = $database->fetchAssociative();

        // It's bad to send the users any passwords, because you can't be sure if the email will be secured,
        // Also don't send plain text password,
        // So, sending a token that will be expired after 24 hours is better.
        $expiry_time = (24 * 60 * 60);
        $time_elapsed = time() - $forgottenPassword['password_last_reset'];

        if ($database->countRows() === 1 && $time_elapsed < $expiry_time) {

            // reset token only after the user enters his password.
            return true;

        } else if($database->countRows() === 1 && $time_elapsed > $expiry_time){

            // reset if the user id & token exists in the database, but exceeded the $expiry_time
            $this->resetPasswordToken($userId);
            return false;

        }else {

            // reset the token if invalid,
            // But, if the user id was invalid, this won't make any affect on database
            $this->resetPasswordToken($userId);
            Logger::log("PASSWORD TOKEN", "User ID ". $userId . " is trying to reset password using invalid token: " . $passwordToken, __FILE__, __LINE__);
            return false;
        }
    }

    /**
     * update password after validating the password token.
     *
     * @access public
     * @param  integer  $userId
     * @param  string   $password
     * @param  string   $confirmPassword
     * @return bool
     * @throws Exception If password couldn't be updated
     *
     */
    public function updatePassword($userId, $password, $confirmPassword){

        $validation = new Validation();
        if(!$validation->validate([
            'Password' => [$password, "required|equals(".$confirmPassword.")|minLen(6)|password"],
            'Password Confirmation' => [$confirmPassword, 'required']])){
            $this->errors = $validation->errors();
            return false;
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT, array('cost' => Config::get('HASH_COST_FACTOR')));
        $database = Database::openConnection();

        $query = "UPDATE users SET hashed_password = :hashed_password WHERE id = :id LIMIT 1";
        $database->prepare($query);
        $database->bindValue(':hashed_password', $hashedPassword);
        $database->bindValue(':id', $userId);
        $result = $database->execute();

        if(!$result){
            throw new Exception("Couldn't update password");
        }

        // resetting the password token comes ONLY after successful updating password
        $this->resetPasswordToken($userId);

        return true;
    }

    /**
     * Reset forgotten password token
     *
     * @access private
     * @param  integer   $userId
     * @throws Exception  If couldn't reset password token
     */
    private function resetPasswordToken($userId){

        $database = Database::openConnection();
        $query = "UPDATE forgotten_passwords SET password_token = NULL, " .
                 "password_last_reset = NULL, forgotten_password_attempts = 0 ".
                 "WHERE user_id = :user_id LIMIT 1";

        $database->prepare($query);
        $database->bindValue(':user_id', $userId);
        $result = $database->execute();
        if(!$result){
            throw new Exception("Couldn't reset password token");
        }
    }

    /**
     * It checks if the token for email verification is valid or not.
     *
     * @access public
     * @param  integer $userId
     * @param  string  $emailToken Email Token
     * @return boolean If valid, it will return true, and vice-versa.
     *
     */
    public function isEmailVerificationTokenValid($userId, $emailToken){

        if (empty($userId) || empty($emailToken)) {
            return false;
        }

        $database = Database::openConnection();
        $database->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
        $database->bindValue(':id', $userId);
        $database->execute();
        $user = $database->fetchAssociative();
        $isTokenValid = ($user["email_token"] === $emailToken)? true: false;

        // check if user is already verified
        if(!empty($user["is_email_activated"])){
            $this->resetEmailVerificationToken($userId, true);
            return false;
        }

        // setting expiry time on email verification is much better,
        // you can't be sure if the email will be secured,
        // also any user can register with email of another person,
        // so this person won't be able to register at all!.
        $expiry_time = (24 * 60 * 60);
        $time_elapsed = time() - $user['email_last_verification'];

        // token is usable only once.
        if($database->countRows() === 1 && $isTokenValid && $time_elapsed < $expiry_time) {

            $this->resetEmailVerificationToken($userId, true);
            return true;

        }else if($database->countRows() === 1 && $isTokenValid && $time_elapsed > $expiry_time) {

            $this->resetEmailVerificationToken($userId, false);
            return false;

        }else{

            // reset token if invalid,
            // But, if the user id was invalid, this won't make any affect on database
            $this->resetEmailVerificationToken($userId, false);
            Logger::log("EMAIL TOKEN", "User ID ". $userId . " is trying to access using invalid email token " . $emailToken, __FILE__, __LINE__);
            return false;
        }
    }

    /**
     * Reset the email verification token.
     * Resetting the token depends on whether the email token was valid or not.
     *
     * @access private
     * @param  integer $userId
     * @param boolean $isValid
     * @throws Exception If couldn't reset email verification token
     */
    public function resetEmailVerificationToken($userId, $isValid){

        $database = Database::openConnection();

        if($isValid){
            $query = "UPDATE users SET email_token = NULL, " .
                "email_last_verification = NULL, is_email_activated = 1 ".
                "WHERE id = :id LIMIT 1";
        }else{
            $query = "DELETE FROM users WHERE id = :id";
        }

        $database->prepare($query);
        $database->bindValue(':id', $userId);
        $result = $database->execute();
        if(!$result){
            throw new Exception("Couldn't reset email verification token");
        }
    }

    /**
     * Logout by removing the Session and Cookies.
     *
     * @access public
     * @param  integer $userId
     *
     */
    public function logOut($userId){

        Session::remove();
        Cookie::remove($userId);
    }

}