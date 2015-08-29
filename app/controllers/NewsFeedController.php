<?php

/**
 * NewsFeed controller
 *
 * @license    http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author     Omar El Gabry <omar.elgabry.93@gmail.com>
 */

class NewsFeedController extends Controller{

    public function beforeAction(){

        parent::beforeAction();

        $this->vars['globalPage'] = "newsfeed";

        $action = $this->request->param('action');
        $actions = ['getAll', 'create', 'getUpdateForm', 'update', 'getById', 'delete'];
        $this->Security->requireAjax($actions);
        $this->Security->requirePost($actions);

        switch($action){
            case "getAll":
                $this->Security->config("form", [ 'fields' => ['page_number']]);
                break;
            case "create":
                $this->Security->config("form", [ 'fields' => ['content']]);
                break;
            case "getUpdateForm":
                $this->Security->config("form", [ 'fields' => ['newsfeed_id']]);
                break;
            case "update":
                $this->Security->config("form", [ 'fields' => ['newsfeed_id', 'content']]);
                break;
            case "getById":
            case "delete":
                $this->Security->config("form", [ 'fields' => ['newsfeed_id']]);
                break;
        }
    }

    public function index(){

        $this->user->clearNotifications(Session::getUserId(), $this->newsfeed->table);
        echo $this->view->renderWithLayouts(Config::get('VIEWS_PATH') . "layout/", Config::get('VIEWS_PATH') . 'newsfeed/index.php');
    }

    public function getAll(){

        $pageNum  = $this->request->data("page_number");

        $data   = $this->newsfeed->getAll($pageNum);
        $html   = $this->view->render(Config::get('VIEWS_PATH') . 'newsfeed/newsfeed.php', array("newsfeed" => $data["newsfeed"]));
        $pagination = $this->view->render(Config::get('VIEWS_PATH') . 'pagination/default.php', array("pagination" => $data["pagination"]));

        echo $this->view->JSONEncode(array("data" => ["newsfeed" => $html, "pagination" => $pagination]));
    }

    public function create(){

        $content  = $this->request->data("content");

        $newsfeed = $this->newsfeed->create(Session::getUserId(), $content);

        if(!$newsfeed){
            echo $this->view->renderErrors($this->newsfeed->errors());
        }else{

            $html = $this->view->render(Config::get('VIEWS_PATH') . 'newsfeed/newsfeed.php', array("newsfeed" => $newsfeed));
            echo $this->view->JSONEncode(array("data" => $html));
        }
    }

    public function getUpdateForm(){

        $newsfeedId = Encryption::decryptIdWithDash($this->request->data("newsfeed_id"));

        if(!$this->newsfeed->exists($newsfeedId)){
            $this->error("notfound");
        }

        $newsfeed = $this->newsfeed->getById($newsfeedId);

        $html = $this->view->render(Config::get('VIEWS_PATH') . 'newsfeed/updateForm.php', array("newsfeed" => $newsfeed[0]));
        echo $this->view->JSONEncode(array("data" => $html));
    }

    public function update(){

        //Remember? each news feed has an id that looks like this: feed-51b2cfa
        $newsfeedId = Encryption::decryptIdWithDash($this->request->data("newsfeed_id"));
        $content    = $this->request->data("content");

        if(!$this->newsfeed->exists($newsfeedId)){
            $this->error("notfound");
        }

        $newsfeed = $this->newsfeed->update($newsfeedId, $content);
        if(!$newsfeed){
            echo $this->view->renderErrors($this->newsfeed->errors());
        }else{

            $html = $this->view->render(Config::get('VIEWS_PATH') . 'newsfeed/newsfeed.php', array("newsfeed" => $newsfeed));
            echo $this->view->JSONEncode(array("data" => $html));
        }
    }

    public function getById(){

        $newsfeedId = Encryption::decryptIdWithDash($this->request->data("newsfeed_id"));

        if(!$this->newsfeed->exists($newsfeedId)){
            $this->error("notfound");
        }

        $newsfeed = $this->newsfeed->getById($newsfeedId);

        $html = $this->view->render(Config::get('VIEWS_PATH') . 'newsfeed/newsfeed.php', array("newsfeed" => $newsfeed));
        echo $this->view->JSONEncode(array("data" => $html));
    }

    public function delete(){

        $newsfeedId = Encryption::decryptIdWithDash($this->request->data("newsfeed_id"));

        $this->newsfeed->deleteById($newsfeedId);
        echo $this->view->JSONEncode(array("success" => true));
    }

    public function isAuthorized(){

        $action = $this->request->param('action');
        $role = Session::getUserRole();
        $resource = "newsfeed";

        //only for admins
        Permission::allow('admin', $resource, ['*']);

        //only for normal users
        Permission::allow('user', $resource, ['index', 'getAll', 'getById', 'create']);
        Permission::allow('user', $resource, ['update', 'delete', 'getUpdateForm'], 'owner');

        $newsfeedId = $this->request->data("newsfeed_id");
        if(!empty($newsfeedId)){
            Encryption::decryptIdWithDash($newsfeedId);
        }

        $config = [
            "user_id" => Session::getUserId(),
            "table" => "newsfeed",
            "id" => $newsfeedId];

        return Permission::check($role, $resource, $action, $config);
    }

}
