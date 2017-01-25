<?php

/**
 * Login controller
 *
 * @license    http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author     Omar El Gabry <omar.elgabry.93@gmail.com>
 */

class LoginController extends Controller {

    /**
     * Initialization method.
     * load components, and optionally assign their $config
     *
     */
    public function initialize(){

        $this->loadComponents([
            'Auth',
            'Security'
        ]);
    }

    public function beforeAction(){

        parent::beforeAction();

        Config::setJsConfig('curPage', "login");

        $action = $this->request->param('action');
        $actions = ['login', 'register', 'forgotPassword', 'updatePassword'];
        $this->Security->requirePost($actions);
        $this->Security->requireGet(['index', 'verifyUser', 'resetPassword', 'logOut']);

        switch($action){
            case "register":
                $this->Security->config("form", [ 'fields' => ['name', 'email', 'password', 'confirm_password', 'captcha']]);
                break;
            case "login":
                $this->Security->config("form", [ 'fields' => ['email', 'password'], 'exclude' => ['remember_me', 'redirect']]);
                break;
            case "forgotPassword":
                $this->Security->config("form", [ 'fields' => ['email']]);
                break;
            case "updatePassword":
                $this->Security->config("form", [ 'fields' => ['password', 'confirm_password', 'id', 'token']]);
                break;
        }
    }

    /**
     * login form
     *
     */
    public function index(){

        // check first if user is already logged in via session or cookie
        if($this->Auth->isLoggedIn()){

            return $this->redirector->dashboard();

        } else {

            // Clearing the sesion won't allow user(un-trusted) to open more than one login form,
                // as every time the page loads, it generates a new CSRF Token.
            // Destroying the sesion won't allow accessing sesssion data (i.e. $_SESSION["csrf_token"]).

            // get redirect url if any
            $redirect = $this->request->query('redirect');

            $this->view->renderWithLayouts(Config::get('VIEWS_PATH') . "layout/login/", Config::get('LOGIN_PATH') . "index.php", ['redirect' => $redirect]);
        }
    }

    /**
     * get captcha image for registration form
     *
     * @return Gregwar\Captcha\CaptchaBuilder
     * @see views/login/index.php
     */
    public function getCaptcha(){

        // create a captcha with the Captcha library
        $captcha = new Gregwar\Captcha\CaptchaBuilder;
        $captcha->build();

        // save the captcha characters in session
        Session::set('captcha', $captcha->getPhrase());

        return $captcha;
    }

    /**
     * registers a new account
     *
     */
    public function register(){

        $name            = $this->request->data("name");
        $email           = $this->request->data("email");
        $password        = $this->request->data("password");
        $confirmPassword = $this->request->data("confirm_password");
        $userCaptcha     = $this->request->data("captcha");
        $sessionCaptcha  = Session::get('captcha');

        $result = $this->login->register($name, $email, $password, $confirmPassword, ['user' => $userCaptcha, 'session' => $sessionCaptcha]);
        Session::set('display-form', 'register');

        if(!$result){
            Session::set('register-errors', $this->login->errors());
        }else{
            Session::set('register-success', "Congratulations!, Your account has been created. Please check your email to validate your account within 24 hour");
        }

        return $this->redirector->login();
    }

    /**
     * verify user token
     * this token was sent by email as soon as user creates a new account
     * it will expire after 24 hour
     *
     */
    public function verifyUser(){

        $userId  = $this->request->query("id");
        $userId  = empty($userId)? null: Encryption::decryptId($this->request->query("id"));
        $token   = $this->request->query("token");

        $result = $this->login->isEmailVerificationTokenValid($userId, $token);

        if(!$result){
            return $this->error(404);
        }else{
            $this->view->renderWithLayouts(Config::get('VIEWS_PATH') . "layout/login/", Config::get('LOGIN_PATH') . 'userVerified.php');
        }
    }

    /**
     * do login
     *
     */
    public function login(){

        $email       = $this->request->data("email");
        $password    = $this->request->data("password");
        $rememberMe  = $this->request->data("remember_me");
        $redirect    = $this->request->data("redirect");

        $result = $this->login->doLogIn($email, $password, $rememberMe, $this->request->clientIp(), $this->request->userAgent());

        if(!$result){
            
            Session::set('login-errors', $this->login->errors());
            return $this->redirector->login($redirect);

        }else{

            // check if redirect url exists, then construct full url
            if(!empty($redirect)){
                $redirect = $this->request->getProtocolAndHost() . $redirect;
                return $this->redirector->to($redirect);
            }

            return $this->redirector->root();
        }
    }

    /**
     * If user forgot his password,
     * then the we will send him an email with token(expired after 24 hours)
     *
     */
    public function forgotPassword(){

        $email  = $this->request->data("email");
        $result = $this->login->forgotPassword($email);
        
        Session::set('display-form', 'forgot-password');

        if(!$result){
            Session::set('forgot-password-errors', $this->login->errors());
        }else{
            Session::set('forgot-password-success', "Email has been sent to you. Please check your email to validate your email address within 24 hour");
        }

        return $this->redirector->login();
    }

    /**
     * If password token valid, then show update password form
     *
     */
    public function resetPassword(){

        $userId  = $this->request->query("id");
        $userId  = empty($userId)? null: Encryption::decryptId($this->request->query("id"));
        $token   = $this->request->query("token");

        $result = $this->login->isForgottenPasswordTokenValid($userId, $token);

        if(!$result){

            return $this->error(404);

        } else {

            // If there is a user already logged in, then log out.
            // It not necessary for the logged in user to be the same as user_id in the requested reset password URL.

            // But, this won't allow user to open more than one update password form,
            // because every time it loads, it generates a new CSRF Token
            // So, keep it commented
            // $this->login->logOut(Session::getUserId(), true);

            // don't store the user id in a hidden field in the update password form,
            // because user can easily open inspector and change it,
            // so you will ending up using updatePassword() on an invalid user id.
            Session::set("user_id_reset_password", $userId);

            $this->view->renderWithLayouts(Config::get('VIEWS_PATH') . "layout/login/", Config::get('LOGIN_PATH') . 'updatePassword.php');
        }
    }

    /**
     * update user's password after reset password request
     *
     */
    public function updatePassword(){

        $password        = $this->request->data("password");
        $confirmPassword = $this->request->data("confirm_password");
        $userId          = Session::get("user_id_reset_password");

        $result =  $this->login->updatePassword($userId, $password, $confirmPassword);

        if(!$result){

            Session::set('update-password-errors', $this->login->errors());
            return $this->redirector->to(PUBLIC_ROOT . "Login/resetPassword", ['id' => $this->request->data("id"), 'token' => $this->request->data("token")]);

        } else {

            // logout, and clear any existing session and cookies
            $this->login->logOut(Session::getUserId());

            $this->view->renderWithLayouts(Config::get('VIEWS_PATH') . "layout/login/", Config::get('LOGIN_PATH') . 'passwordUpdated.php');
        }
    }

    /**
     * logout
     *
     */
    public function logOut(){

        $this->login->logOut(Session::getUserId());
        return $this->redirector->login();
    }

}
