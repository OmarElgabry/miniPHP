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

        Config::setJsConfig('curPage', "posts");

        $action  = $this->request->param('action');
        $actions = ['create', 'update'];
        $this->Security->requirePost($actions);

        switch($action){
            case "create":
                $this->Security->config("form", [ 'fields' => ['title', 'content']]);
                break;
            case "update":
                $this->Security->config("form", [ 'fields' => ['post_id', 'title', 'content']]);
                break;
            case "delete":
                $this->Security->config("validateCsrfToken", true);
                $this->Security->config("form", [ 'fields' => ['post_id']]);
                break;
        }
    }

    /**
     * show posts page
     *
     */
    public function index(){

        // clear all notifications
        $this->user->clearNotifications(Session::getUserId(), $this->post->table);

        $pageNum  = $this->request->query("page");

        $this->view->renderWithLayouts(Config::get('VIEWS_PATH') . "layout/default/", Config::get('VIEWS_PATH') . 'posts/index.php', ['pageNum' => $pageNum]);
    }

    /**
     * view a post
     *
     * @param integer|string $postId
     */
    public function view($postId = 0){

        $postId = Encryption::decryptId($postId);

        if(!$this->post->exists($postId)){
            return $this->error(404);
        }

        Config::setJsConfig('curPage', ["posts", "comments"]);
        Config::setJsConfig('postId', Encryption::encryptId($postId));

        $action  = $this->request->query('action');
        $this->view->renderWithLayouts(Config::get('VIEWS_PATH') . "layout/default/", Config::get('VIEWS_PATH') . 'posts/viewPost.php', ["action"=> $action, "postId" => $postId]);
    }

    /**
     * show new post form
     */
    public function newPost(){
        $this->view->renderWithLayouts(Config::get('VIEWS_PATH') . "layout/default/", Config::get('VIEWS_PATH') . 'posts/newPost.php');
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
            Session::set('posts-errors', $this->post->errors());
        }else{
            Session::set('posts-success', "Post has been created");
        }

        return $this->redirector->root("Posts/newPost");
    }

    /**
     * update a post
     *
     */
    public function update(){

        $postId  = $this->request->data("post_id");
        $title   = $this->request->data("title");
        $content = $this->request->data("content");

        $postId = Encryption::decryptId($postId);

        if(!$this->post->exists($postId)){
            return $this->error(404);
        }

        $post = $this->post->update($postId, $title, $content);

        if(!$post){

            Session::set('posts-errors', $this->post->errors());
            return $this->redirector->root("Posts/View/" . urlencode(Encryption::encryptId($postId)) . "?action=update");

        }else{
            return $this->redirector->root("Posts/View/" . urlencode(Encryption::encryptId($postId)));
        }
    }

    public function delete($postId = 0){

        $postId = Encryption::decryptId($postId);

        if(!$this->post->exists($postId)){
            return $this->error(404);
        }

        $this->post->deleteById($postId);

        return $this->redirector->root("Posts");
    }

    public function isAuthorized(){

        $action = $this->request->param('action');
        $role = Session::getUserRole();
        $resource = "posts";

        // only for admins
        Permission::allow('admin', $resource, ['*']);

        // only for normal users
        Permission::allow('user', $resource, ['index', 'view', 'newPost', 'create']);
        Permission::allow('user', $resource, ['update', 'delete'], 'owner');

        $postId  = ($action === "delete")? $this->request->param("args")[0]: $this->request->data("post_id");
        if(!empty($postId)){
            $postId = Encryption::decryptId($postId);
        } 

        $config = [
            "user_id" => Session::getUserId(),
            "table" => "posts",
            "id" => $postId
        ];

        return Permission::check($role, $resource, $action, $config);
    }
}
