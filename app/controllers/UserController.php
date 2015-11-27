<?php

/**
 * User controller
 *
 * @license    http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author     Omar El Gabry <omar.elgabry.93@gmail.com>
 */

class UserController extends Controller{

    public function beforeAction(){

        parent::beforeAction();

        $action = $this->request->param('action');
        $actions = ['updateProfileInfo', 'updateProfilePicture', 'reportBug'];
        $this->Security->requireAjax($actions);
        $this->Security->requirePost($actions);

        switch($action){
            case "updateProfileInfo":
                $this->Security->config("form", [ 'fields' => ['name', 'password', 'email', 'confirm_email']]);
                break;
            case "updateProfilePicture":
                $this->Security->config("form", [ 'fields' => ['file']]);
                break;
            case "reportBug":
                $this->Security->config("form", [ 'fields' => ['subject', 'label', 'message']]);
                break;
        }
    }

    /**
     * show dashboard page
     *
     */
    public function index(){

        $this->vars['curPage'] = "dashboard";
        echo $this->view->renderWithLayouts(Config::get('VIEWS_PATH') . "layout/default/", Config::get('VIEWS_PATH') . 'dashboard/index.php');
    }

    public function profile(){
        $this->vars['curPage'] = "profile";
        echo $this->view->renderWithLayouts(Config::get('VIEWS_PATH') . "layout/default/", Config::get('VIEWS_PATH') . 'user/profile.php');
    }

    public function updateProfileInfo(){

        $name           = $this->request->data("name");
        $password       = $this->request->data("password");
        $email          = $this->request->data("email");
        $confirmEmail   = $this->request->data("confirm_email");

        $result = $this->user->updateProfileInfo(Session::getUserId(), $name, $password, $email, $confirmEmail);

        if(!$result){
            echo $this->view->renderErrors($this->user->errors());
        }else{
            $message  = "Your Profile has been updated. ";
            $message .= $result["emailUpdated"]? "Please check your new email to confirm the changes, or your current email to revoke the changes": "";
            echo $this->view->renderSuccess($message);
        }
    }

    public function updateProfilePicture(){

        $fileData = $this->request->data("file");
        $image = $this->user->updateProfilePicture(Session::getUserId(), $fileData);

        if(!$image){
            echo $this->view->renderErrors($this->user->errors());
        }else{
            echo $this->view->JSONEncode(array("data" => ["src" => PUBLIC_ROOT . "img/profile_pictures/" . $image["basename"]]));
        }
    }

    /**
     * revoke email updates
     *
     * You must be logged in with your current email
     */
    public function revokeEmail(){

        $userId  = $this->request->query("id");
        $userId  = empty($userId)? null: Encryption::decryptId($this->request->query("id"));
        $token   = $this->request->query("token");

        $result = $this->user->revokeEmail($userId, $token);

        if(!$result){
            $this->error("notfound");
        }else{
            echo $this->view->renderWithLayouts(Config::get('VIEWS_PATH') . "layout/default/", Config::get('VIEWS_PATH') . 'user/profile.php',
                array("emailUpdates" => ["success" => "Your email updates has been revoked successfully."]));
        }
    }

    /**
     * confirm on email updates
     *
     * You must be logged in with your current email
     */
    public function updateEmail(){

        $userId  = $this->request->query("id");
        $userId  = empty($userId)? null: Encryption::decryptId($this->request->query("id"));
        $token   = $this->request->query("token");

        $result = $this->user->updateEmail($userId, $token);
        $errors = $this->user->errors();

        if(!$result && empty($errors)){
            $this->error("notfound");
        }else if(!$result && !empty($errors)){
            echo $this->view->renderWithLayouts(Config::get('VIEWS_PATH') . "layout/default/", Config::get('VIEWS_PATH') . 'user/profile.php',
                array("emailUpdates" => ["errors" => $this->user->errors()]));
        }else{
            echo $this->view->renderWithLayouts(Config::get('VIEWS_PATH') . "layout/default/", Config::get('VIEWS_PATH') . 'user/profile.php',
                array("emailUpdates" => ["success" => "Your email updates has been updated successfully."]));
        }
    }

    /**
     * users can report bugs, features, or enhancement
     * - Bug is an error you encountered
     * - Feature is a new functionality you suggest to add
     * - Enhancement is an existing feature, but you want to improve
     *
     */
    public function bugs(){
        $this->vars['curPage'] = "bugs";
        echo $this->view->renderWithLayouts(Config::get('VIEWS_PATH') . "layout/default/", Config::get('VIEWS_PATH') . 'bugs/index.php');
    }

    /**
     * send email to admin for reporting any bugs, features, or enhancement
     *
     */
    public function reportBug(){

        $subject = $this->request->data("subject");
        $label   = $this->request->data("label");
        $message = $this->request->data("message");

        $result = $this->user->reportBug(Session::getUserId(), $subject, $label, $message);
        if(!$result){
            echo $this->view->renderErrors($this->user->errors());
        }else{
            echo $this->view->renderSuccess("Email has been sent successfully, We will consider your report.");
        }
    }

    public function isAuthorized(){
        return true;
    }
}
