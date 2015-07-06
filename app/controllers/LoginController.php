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

        $action = $this->request->param('action');
        $actions = ['login', 'forgotPassword', 'updatePassword'];
        $this->Security->requireAjax($actions);
        $this->Security->requirePost($actions);
        $this->Security->requireGet(['resetPassword', 'logOut']);

        switch($action){
            case "register":
                $this->Security->config("form", [ 'fields' => ['name', 'email', 'password', 'confirm_password', 'captcha']]);
                break;
            case "login":
                $this->Security->config("form", [ 'fields' => ['email', 'password'], 'exclude' => ['remember_me']]);
                break;
            case "forgotPassword":
                $this->Security->config("form", [ 'fields' => ['email']]);
                break;
            case "updatePassword":
                $this->Security->config("form", [ 'fields' => ['password', 'confirm_password']]);
                break;
        }
    }

    /**
     * login form
     *
     */
    public function index(){

        //check first if user is already logged in via session or cookie
        if($this->Auth->isLoggedIn()){

            Redirector::dashboard();

        } else {

            //clear and regenerate session and cookies(instead of using the existing one in browser),
            //then show login form.

            //But, this won't allow user(un-trusted) to open more than one login form,
            //because every time it loads, it generates a new CSRF Token
            //So, keep it commented
            //$this->login->logOut(Session::getUserId(), true);

            echo $this->view->renderWithLayouts(LOGIN_PATH, LOGIN_PATH . "index.php");
        }
    }

    /**
     * get captcha image for registration form
     *
     * @return Gregwar\Captcha\CaptchaBuilder
     * @see views/login/index.php
     */
    public function getCaptcha(){

        //create a captcha with the Captcha library
        $captcha = new Gregwar\Captcha\CaptchaBuilder;
        $captcha->build();

        //save the captcha characters in session
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

        if(!$result){
            echo $this->view->renderErrors($this->login->errors());
        }else{
            echo $this->view->renderSuccess("Congratulations!, Your account has been created. Please check your email to validate your account within 24 hour");
        }
    }

    /**
     * verify user token
     * this token was sent by email as soon as user creates a new account
     * it will expire after 24 hour
     *
     */
    public function verifyUser(){

        $userId = Encryption::decryptId($this->request->query("id"));
        $token  = $this->request->query("token");

        $result = $this->login->isEmailVerificationTokenValid($userId, $token);

        if(!$result){
            $this->error("notfound");
        }else{
            echo $this->view->renderWithLayouts(LOGIN_PATH, LOGIN_PATH . 'userVerified.php');
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

        $result = $this->login->doLogIn($email, $password, $rememberMe, $this->request->clientIp(), $this->request->userAgent());

        if(!$result){
            echo $this->view->renderErrors($this->login->errors());
        }else{
            //$this->response->setStatusCode(403)->send();
            echo $this->view->JSONEncode(array("redirect" => PUBLIC_ROOT));
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

        if(!$result){
            echo $this->view->renderErrors($this->login->errors());
        }else{
            echo $this->view->renderSuccess("Email has been sent to you. Please check your email to validate your email address within 24 hour");
        }
    }

    /**
     * If password token valid, then show update password form
     *
     */
    public function resetPassword(){

        $userId  = Encryption::decryptId($this->request->query("id"));
        $token   = $this->request->query("token");

        $result = $this->login->isForgottenPasswordTokenValid($userId, $token);

        if(!$result){

            $this->error("notfound");

        } else {

            //If there is a user already logged in, then log out.
            //It not necessary for the logged in user to be the same as user_id in the requested reset password URL.

            //But, this won't allow user to open more than one update password form,
            //because every time it loads, it generates a new CSRF Token
            //So, keep it commented
            //$this->login->logOut(Session::getUserId(), true);

            //don't store the user id in a hidden field in the update password form,
            //because user can easily open inspector and change it,
            //so you will ending up using updatePassword() on an invalid user id.
            Session::set("user_id_reset_password", $userId);

            echo $this->view->renderWithLayouts(LOGIN_PATH, LOGIN_PATH . 'updatePassword.php');
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
            echo $this->view->renderErrors($this->login->errors());
        } else {

            //logout, and clear any existing session and cookies
            $this->login->logOut(Session::getUserId());

            echo $this->view->renderSuccess("Your password has been changed successfully, Please login again.");
        }
    }

    /**
     * logout
     *
     */
    public function logOut(){

        $this->login->logOut(Session::getUserId());
        Redirector::login();
    }

}
