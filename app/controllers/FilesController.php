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

        Config::addJsConfig('curPage', "files");

        $action = $this->request->param('action');
        $actions = ['create', 'delete'];
        $this->Security->requireAjax($actions);
        $this->Security->requirePost($actions);

        switch($action){
            case "create":
                $this->Security->config("form", [ 'fields' => ['file']]);
                break;
            case "delete":
                $this->Security->config("form", [ 'fields' => ['file_id']]);
                break;
        }
    }

    public function index(){

        // clear all notifications whenever you hit 'files' in the navigation bar
        $this->user->clearNotifications(Session::getUserId(), $this->file->table);

        $pageNum  = $this->request->query("page");

        echo $this->view->renderWithLayouts(Config::get('VIEWS_PATH') . "layout/default/", Config::get('VIEWS_PATH') . 'files/index.php', ['pageNum' => $pageNum]);
    }

    public function create(){

        $fileData  = $this->request->data("file");

        $file = $this->file->create(Session::getUserId(), $fileData);

        if(!$file){
            echo $this->view->renderErrors($this->file->errors());
        }else{

            $fileHTML = $this->view->render(Config::get('VIEWS_PATH') . 'files/files.php', array("files" => $file));
            echo $this->view->JSONEncode(array("data" => $fileHTML));
        }
    }

    public function delete(){

        $fileId = Encryption::decryptIdWithDash($this->request->data("file_id"));

        if(!$this->file->exists($fileId)){
            $this->error(404);
        }

        $this->file->deleteById($fileId);

        echo $this->view->JSONEncode(array("success" => true));
    }

    public function isAuthorized(){

        $action = $this->request->param('action');
        $role = Session::getUserRole();
        $resource = "files";

        // only for admins
        Permission::allow('admin', $resource, ['*']);

        // only for normal users
        Permission::allow('user', $resource, ['index', 'create']);
        Permission::allow('user', $resource, ['delete'], 'owner');

        $fileId = $this->request->data("file_id");
        if(!empty($fileId)){
            $fileId = Encryption::decryptIdWithDash($fileId);
        }

        $config = [
            "user_id" => Session::getUserId(),
            "table" => "files",
            "id" => $fileId
        ];

        return Permission::check($role, $resource, $action, $config);
    }
}
