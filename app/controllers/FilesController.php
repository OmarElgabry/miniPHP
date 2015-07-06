<?php

/**
 * Files controller
 *
 * @license    http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author     Omar El Gabry <omar.elgabry.93@gmail.com>
 */

class FilesController extends Controller {

    public function beforeAction(){

        parent::beforeAction();

        $this->vars['globalPage'] = "files";

        $action = $this->request->param('action');
        $actions = ['getAll', 'create', 'delete'];
        $this->Security->requireAjax($actions);
        $this->Security->requirePost($actions);

        switch($action){
            case "getAll":
                $this->Security->config("form", [ 'fields' => ['page_number']]);
                break;
            case "create":
                $this->Security->config("form", [ 'fields' => ['file']]);
                break;
            case "delete":
                $this->Security->config("form", [ 'fields' => ['file_id']]);
                break;
        }
    }

    public function index(){

        //clear all notifications whenever you hit 'files' in the navigation bar
        $this->user->clearNotifications(Session::getUserId(), $this->file->table);

        echo $this->view->renderWithLayouts(VIEWS_PATH . "layout/", VIEWS_PATH . 'files/index.php');
    }

    public function getAll(){

        $pageNum = $this->request->data("page_number");

        $filesData  = $this->file->getAll($pageNum);

        $filesHTML  = $this->view->render(VIEWS_PATH . 'files/files.php', array("files" => $filesData["files"]));
        $paginationHTML = $this->view->render(VIEWS_PATH . 'pagination/default.php', array("pagination" => $filesData["pagination"]));
        echo $this->view->JSONEncode(array("data" => ["files" => $filesHTML, "pagination" => $paginationHTML]));
    }

    public function create(){

        $fileData  = $this->request->data("file");

        $file = $this->file->create(Session::getUserId(), $fileData);

        if(!$file){
            echo $this->view->renderErrors($this->file->errors());
        }else{

            $fileHTML = $this->view->render(VIEWS_PATH . 'files/files.php', array("files" => $file));
            echo $this->view->JSONEncode(array("data" => $fileHTML));
        }
    }

    public function delete(){

        $fileId = Encryption::decryptIdWithDash($this->request->data("file_id"));

        if(!$this->file->exists($fileId)){
            $this->error("notfound");
        }

        $this->file->deleteById($fileId);

        echo $this->view->JSONEncode(array("success" => true));
    }

    public function isAuthorized(){

        $action = $this->request->param('action');
        $role = Session::getUserRole();
        $resource = "files";

        //only for admins
        Permission::allow('admin', $resource, ['*']);

        //only for normal users
        Permission::allow('user', $resource, ['index', 'getAll', 'create']);
        Permission::allow('user', $resource, ['delete'], 'owner');

        $fileId = Encryption::decryptIdWithDash($this->request->data("file_id"));
        $config = [
            "user_id" => Session::getUserId(),
            "table" => "files",
            "id" => $fileId
        ];

        return Permission::check($role, $resource, $action, $config);
    }
}
