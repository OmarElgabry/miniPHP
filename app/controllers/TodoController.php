<?php

/**
 * Todo Controller
 *
 * @license    http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author     Omar El Gabry <omar.elgabry.93@gmail.com>
 */

class TodoController extends Controller{

    // override this method to perform any logic before calling action method as explained above
    public function beforeAction(){

        parent::beforeAction();

        // define the actions in this Controller
        $action = $this->request->param('action');

        // restrict the request to action methods
        // $this->Security->requireAjax(['create', 'delete']);
        $this->Security->requirePost(['create', 'delete']);

        // define the expected form fields for every action if exist
        switch($action){
            case "create":
                // you can exclude form fields if you don't care if they were sent with form fields or not
                $this->Security->config("form", [ 'fields' => ['content']]);
                break;
            case "delete":
				// If you want to disable validation for form tampering
				// $this->Security->config("validateForm", false);
                $this->Security->config("form", [ 'fields' => ['todo_id']]);
                break;
        }
    }

    public function index(){

        // display todo list
        $this->view->renderWithLayouts(Config::get('VIEWS_PATH') . "layout/todo/", Config::get('VIEWS_PATH') . 'todo/index.php');
    }

    public function create(){

        $content  = $this->request->data("content");
        $todo     = $this->todo->create(Session::getUserId(), $content);

        if(!$todo){

            // in case of normal post request
            Session::set('errors', $this->todo->errors());
            return $this->redirector->root("Todo");

            // in case of ajax
            // $this->view->renderErrors($this->todo->errors());

        }else{

            // in case of normal post request
            Session::set('success', "Todo has been created");
            return $this->redirector->root("Todo");

            // in case of ajax
            // $this->view->renderJson(array("success" => "Todo has been created"));
        }
    }

    public function delete(){

        $todoId = Encryption::decryptIdWithDash($this->request->data("todo_id"));
        $this->todo->delete($todoId);

        // in case of normal post request
        Session::set('success', "Todo has been deleted");
        return $this->redirector->root("Todo");

        // in case of ajax
        // $this->view->renderJson(array("success" => "Todo has been deleted"));
    }

    public function isAuthorized(){

        $action = $this->request->param('action');
        $role = Session::getUserRole();
        $resource = "todo";

        // only for admins
        Permission::allow('admin', $resource, ['*']);

        // only for normal users
        Permission::allow('user', $resource, ['delete'], 'owner');

        $todoId = $this->request->data("todo_id");

        if(!empty($todoId)){
            $todoId = Encryption::decryptIdWithDash($todoId);
        }

        $config = [
            "user_id" => Session::getUserId(),
            "table" => "todo",
            "id" => $todoId];

        return Permission::check($role, $resource, $action, $config);
    }
}