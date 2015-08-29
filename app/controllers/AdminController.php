<?php

/**
 * The admin controller
 *
 * @license    http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author     Omar El Gabry <omar.elgabry.93@gmail.com>
 *
 */

class AdminController extends Controller {

    /**
     * A method that will be triggered before calling action method.
     * Any changes here will reflect then on Controller::triggerComponents() method
     *
     */
    public function beforeAction(){

        parent::beforeAction();

        $action = $this->request->param('action');
        $actions = ['getUsers', 'updateUserInfo', 'deleteUser', 'updateBackup', 'restoreBackup'];

        //define the action methods that needs to be triggered only through POST & Ajax request.
        $this->Security->requireAjax($actions);
        $this->Security->requirePost($actions);

        //You need to explicitly define the form fields that you expect to be returned in POST request,
        //if form field wasn't defined, this will detected as form tampering attempt.
        switch($action){
            case "getUsers":
                $this->Security->config("form", [ 'fields' => ['name', 'email', 'role', 'page_number']]);
                break;
            case "updateUserInfo":
                $this->Security->config("form", [ 'fields' => ['user_id', 'name', 'password', 'role']]);
                break;
            case "deleteUser":
                $this->Security->config("form", [ 'fields' => ['user_id']]);
                break;
            case "updateBackup":
            case "restoreBackup":
                $this->Security->config("validateForm", false);
                break;
        }
    }

    /**
     * show all users
     *
     */
    public function users(){

        $this->vars['globalPage'] = "users";
        echo $this->view->renderWithLayouts(Config::get('VIEWS_PATH') . "layout/", Config::get('ADMIN_VIEWS_PATH') . 'users/index.php');
    }

    /**
     * get users by name, email & role
     *
     */
    public function getUsers(){

        $name     = $this->request->data("name");
        $email    = $this->request->data("email");
        $role     = $this->request->data("role");
        $pageNum  = $this->request->data("page_number");

        $usersData = $this->admin->getUsers($name, $email, $role, $pageNum);

        if(!$usersData){
            echo $this->view->renderErrors($this->admin->errors());
        } else{

            $usersHTML       = $this->view->render(Config::get('ADMIN_VIEWS_PATH') . 'users/users.php', array("users" => $usersData["users"]));
            $paginationHTML  = $this->view->render(Config::get('VIEWS_PATH') . 'pagination/default.php', array("pagination" => $usersData["pagination"]));
            echo $this->view->JSONEncode(array("data" => ["users" => $usersHTML, "pagination" => $paginationHTML]));
        }
    }

    /**
     * view a user
     *
     * @param integer|string $userId
     */
    public function viewUser($userId = 0){

        $userId = Encryption::decryptId($userId);

        if(!$this->user->exists($userId)){
            $this->error("notfound");
        }

        $this->vars['globalPage']   = "users";
        $this->vars['globalPageId'] = $userId;

        echo $this->view->renderWithLayouts(Config::get('VIEWS_PATH') . "layout/", Config::get('ADMIN_VIEWS_PATH') . 'users/viewUser.php', array("userId" => $userId));
    }

    /**
     * update user profile info(name, password, role)
     *
     */
    public function updateUserInfo(){

        $userId     = (int)$this->request->data("user_id");
        $name       = $this->request->data("name");
        $password   = $this->request->data("password");
        $role       = $this->request->data("role");

        if(!$this->user->exists($userId)){
            $this->error("notfound");
        }

        $result = $this->admin->updateUserInfo($userId, Session::getUserId(), $name, $password, $role);

        if(!$result){
            echo $this->view->renderErrors($this->admin->errors());
        }else{
            echo $this->view->renderSuccess("Profile has been updated.");
        }
    }

    /**
     * delete a user
     *
     */
    public function deleteUser(){

        $userId = Encryption::decryptIdWithDash($this->request->data("user_id"));

        if(!$this->user->exists($userId)){
            $this->error("notfound");
        }

        $this->admin->deleteUser(Session::getUserId(), $userId);
        echo $this->view->JSONEncode(array("success" => true));
    }

    /**
     * view backups if exist
     *
     */
    public function backups(){

        $this->vars['globalPage'] = "backups";
        echo $this->view->renderWithLayouts(Config::get('VIEWS_PATH') . "layout/", Config::get('ADMIN_VIEWS_PATH') . 'backups.php');
    }

    /**
     * update backup
     *
     */
    public function updateBackup(){

        $this->admin->updateBackup();
        echo $this->view->renderSuccess("Backup has been updated");
    }

    /**
     * restore backup
     *
     */
    public function restoreBackup(){

        $result = $this->admin->restoreBackup();

        if(!$result){
            echo $this->view->renderErrors($this->admin->errors());
        }else{
            echo $this->view->renderSuccess("Backup has been restored successfully");
        }
    }

    /**
      * Is user authorized for admin controller & requested action method?
      *
      * @return bool
     */
    public function isAuthorized(){

        $role = Session::getUserRole();
        if(isset($role) && $role === "admin"){
            return true;
        }
        return false;
    }

 }
