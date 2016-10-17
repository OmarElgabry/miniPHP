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
        $actions = ['getUsers', 'updateUserInfo', 'deleteUser'];

        // define the action methods that needs to be triggered only through POST & Ajax request.
        $this->Security->requireAjax($actions);
        $this->Security->requirePost($actions);

        // You need to explicitly define the form fields that you expect to be returned in POST request,
        // if form field wasn't defined, this will detected as form tampering attempt.
        switch($action){
            case "getUsers":
                $this->Security->config("form", [ 'fields' => ['name', 'email', 'role', 'page']]);
                break;
            case "updateUserInfo":
                $this->Security->config("form", [ 'fields' => ['user_id', 'name', 'password', 'role']]);
                break;
            case "deleteUser":
                $this->Security->config("form", [ 'fields' => ['user_id']]);
                break;
            case "updateBackup":
            case "restoreBackup":
                $this->Security->config("validateCsrfToken", true);
                break;
        }
    }

    /**
     * show all users
     *
     */
    public function users(){

        Config::setJsConfig('curPage', "users");
        $this->view->renderWithLayouts(Config::get('VIEWS_PATH') . "layout/default/", Config::get('ADMIN_VIEWS_PATH') . 'users/index.php');
    }

    /**
     * get users by name, email & role
     *
     */
    public function getUsers(){

        $name     = $this->request->data("name");
        $email    = $this->request->data("email");
        $role     = $this->request->data("role");
        $pageNum  = $this->request->data("page");

        $usersData = $this->admin->getUsers($name, $email, $role, $pageNum);

        if(!$usersData){
            $this->view->renderErrors($this->admin->errors());
        } else{

            $usersHTML       = $this->view->render(Config::get('ADMIN_VIEWS_PATH') . 'users/users.php', array("users" => $usersData["users"]));
            $paginationHTML  = $this->view->render(Config::get('VIEWS_PATH') . 'pagination/default.php', array("pagination" => $usersData["pagination"]));
            $this->view->renderJson(array("data" => ["users" => $usersHTML, "pagination" => $paginationHTML]));
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
            return $this->error(404);
        }

        Config::setJsConfig('curPage', "users");
        Config::setJsConfig('userId', Encryption::encryptId($userId));

        $this->view->renderWithLayouts(Config::get('VIEWS_PATH') . "layout/default/", Config::get('ADMIN_VIEWS_PATH') . 'users/viewUser.php', array("userId" => $userId));
    }

    /**
     * update user profile info(name, password, role)
     *
     */
    public function updateUserInfo(){

        $userId     = Encryption::decryptId($this->request->data("user_id"));
        $name       = $this->request->data("name");
        $password   = $this->request->data("password");
        $role       = $this->request->data("role");

        if(!$this->user->exists($userId)){
            return $this->error(404);
        }

        $result = $this->admin->updateUserInfo($userId, Session::getUserId(), $name, $password, $role);

        if(!$result){
            $this->view->renderErrors($this->admin->errors());
        }else{
            $this->view->renderSuccess("Profile has been updated.");
        }
    }

    /**
     * delete a user
     *
     */
    public function deleteUser(){

        $userId = Encryption::decryptIdWithDash($this->request->data("user_id"));

        if(!$this->user->exists($userId)){
            return $this->error(404);
        }

        $this->admin->deleteUser(Session::getUserId(), $userId);
        $this->view->renderJson(array("success" => true));
    }

    /**
     * view backups if exist
     *
     */
    public function backups(){

        Config::setJsConfig('curPage', "backups");
        $this->view->renderWithLayouts(Config::get('VIEWS_PATH') . "layout/default/", Config::get('ADMIN_VIEWS_PATH') . 'backups.php');
    }

    /**
     * update backup
     *
     */
    public function updateBackup(){

        $this->admin->updateBackup();

        Session::set('backup-success', "Backup has been updated");
        return $this->redirector->root("Admin/Backups");
    }

    /**
     * restore backup
     *
     */
    public function restoreBackup(){

        $result = $this->admin->restoreBackup();

        if(!$result){
            Session::set('backup-errors', $this->admin->errors());
            return $this->redirector->root("Admin/Backups");
        }else{
            Session::set('backup-success', "Backup has been restored successfully");
            return $this->redirector->root("Admin/Backups");
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
