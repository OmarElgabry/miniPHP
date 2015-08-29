<?php

/**
 * Posts controller
 *
 * @license    http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author     Omar El Gabry <omar.elgabry.93@gmail.com>
 */

class PostsController extends Controller{


    public function beforeAction(){

        parent::beforeAction();

        $this->vars['globalPage'] = "posts";

        $action  = $this->request->param('action');
        $actions = ['getAll', 'create', 'getUpdateForm', 'update', 'getById', 'delete'];
        $this->Security->requireAjax($actions);
        $this->Security->requirePost($actions);

        switch($action){
            case "getAll":
                $this->Security->config("form", [ 'fields' => ['page_number']]);
                break;
            case "create":
                $this->Security->config("form", [ 'fields' => ['title', 'content']]);
                break;
            case "getUpdateForm":
                $this->Security->config("form", [ 'fields' => ['post_id']]);
                break;
            case "update":
                $this->Security->config("form", [ 'fields' => ['post_id', 'title', 'content']]);
                break;
            case "getById":
            case "delete":
                $this->Security->config("form", [ 'fields' => ['post_id']]);
                break;
        }
    }

    /**
     * show posts page
     *
     */
    public function index(){

        //clear all notifications
        $this->user->clearNotifications(Session::getUserId(), $this->post->table);

        echo $this->view->renderWithLayouts(Config::get('VIEWS_PATH') . "layout/", Config::get('VIEWS_PATH') . 'posts/index.php');
    }

    /**
     * view a post
     *
     * @param integer|string $postId
     */
    public function view($postId = 0){

        $postId = Encryption::decryptId($postId);

        if(!$this->post->exists($postId)){
            $this->error("notfound");
        }

        $this->vars['globalPage'] = ["posts", "comments"];
        $this->vars['globalPageId'] = $postId;

        echo $this->view->renderWithLayouts(Config::get('VIEWS_PATH') . "layout/", Config::get('VIEWS_PATH') . 'posts/viewPost.php', array("postId" => $postId));
    }

    /**
     * show new post form
     */
    public function newPost(){
        echo $this->view->renderWithLayouts(Config::get('VIEWS_PATH') . "layout/", Config::get('VIEWS_PATH') . 'posts/newPost.php');
    }

    /**
     * get all posts
     *
     */
    public function getAll(){

        $pageNum = $this->request->data("page_number");

        $postData = $this->post->getAll($pageNum);

        if(!$postData){
            echo $this->view->renderErrors($this->post->errors());
        }else{

            $postsHTML      = $this->view->render(Config::get('VIEWS_PATH') . 'posts/posts.php', array("posts" => $postData["posts"]));
            $paginationHTML = $this->view->render(Config::get('VIEWS_PATH') . 'pagination/default.php', array("pagination" => $postData["pagination"]));
            echo $this->view->JSONEncode(array("data" => ["posts" => $postsHTML, "pagination" => $paginationHTML]));
        }
    }

    /**
     * creates a new post
     *
     */
    public function create(){

        $title    = $this->request->data("title");
        $content  = $this->request->data("content");

        $result = $this->post->create(Session::getUserId(), $title, $content);

        if(!$result){
            echo $this->view->renderErrors($this->post->errors());
        }else{
            echo $this->view->renderSuccess("Post has been created");
        }
    }

    /**
     * get update form for editing a post
     *
     */
    public function getUpdateForm(){

        $postId = $this->request->data("post_id");

        if(!$this->post->exists($postId)){
            $this->error("notfound");
        }

        $post = $this->post->getById($postId);

        $html = $this->view->render(Config::get('VIEWS_PATH') . 'posts/postUpdateForm.php', array("post" => $post));
        echo $this->view->JSONEncode(array("data" => $html));
    }

    /**
     * update a post
     *
     */
    public function update(){

        $postId  = $this->request->data("post_id");
        $title   = $this->request->data("title");
        $content = $this->request->data("content");

        if(!$this->post->exists($postId)){
            $this->error("notfound");
        }

        $post = $this->post->update($postId, $title, $content);
        if(!$post){
            echo $this->view->renderErrors($this->post->errors());
        }else{

            $html = $this->view->render(Config::get('VIEWS_PATH') . 'posts/post.php', array("post" => $post));
            echo $this->view->JSONEncode(array("data" => $html));
        }
    }

    /**
     * get post by Id
     *
     */
    public function getById(){

        $postId  = $this->request->data("post_id");

        if(!$this->post->exists($postId)){
            $this->error("notfound");
        }

        $post = $this->post->getById($postId);
        $html = $this->view->render(Config::get('VIEWS_PATH') . 'posts/post.php', array("post" => $post));

        echo $this->view->JSONEncode(array("data" => $html));
    }

    public function delete(){

        $postId  = $this->request->data("post_id");

        if(!$this->post->exists($postId)){
            $this->error("notfound");
        }

        $this->post->deleteById($postId);
        echo $this->view->renderSuccess("Post has been successfully deleted");
    }

    public function isAuthorized(){

        $action = $this->request->param('action');
        $role = Session::getUserRole();
        $resource = "posts";

        //only for admins
        Permission::allow('admin', $resource, ['*']);

        //only for normal users
        Permission::allow('user', $resource, ['index', 'view', 'newPost', 'getAll', 'getById', 'create']);
        Permission::allow('user', $resource, ['update', 'delete', 'getUpdateForm'], 'owner');

        $postId  = $this->request->data("post_id");

        $config = [
            "user_id" => Session::getUserId(),
            "table" => "posts",
            "id" => $postId
        ];

        return Permission::check($role, $resource, $action, $config);
    }
}
