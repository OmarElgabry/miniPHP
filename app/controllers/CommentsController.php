<?php

/**
 * The comments controller
 *
 * @license    http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author     Omar El Gabry <omar.elgabry.93@gmail.com>
 */

class CommentsController extends Controller{

    public function beforeAction(){

        parent::beforeAction();

        $action = $this->request->param('action');
        $actions = ['getAll', 'create', 'getUpdateForm', 'update', 'getById', 'delete'];
        $this->Security->requireAjax($actions);
        $this->Security->requirePost($actions);

        switch($action){
            case "getAll":
                $this->Security->config("form", [ 'fields' => ['post_id', 'page', 'comments_created']]);
                break;
            case "create":
                $this->Security->config("form", [ 'fields' => ['post_id', 'content']]);
                break;
            case "getUpdateForm":
                $this->Security->config("form", [ 'fields' => ['comment_id']]);
                break;
            case "update":
                $this->Security->config("form", [ 'fields' => ['comment_id', 'content']]);
                break;
            case "getById":
            case "delete":
                $this->Security->config("form", [ 'fields' => ['comment_id']]);
                break;
        }
    }

    /**
     * get all comments
     *
     */
    public function getAll(){

        $postId = Encryption::decryptId($this->request->data("post_id"));

        $pageNum         = $this->request->data("page");
        $commentsCreated = (int)$this->request->data("comments_created");

        $commentsData = $this->comment->getAll($postId, $pageNum, $commentsCreated);

        $commentsHTML   = $this->view->render(Config::get('VIEWS_PATH') . 'posts/comments.php', array("comments" => $commentsData["comments"]));
        $paginationHTML = $this->view->render(Config::get('VIEWS_PATH') . 'pagination/comments.php', array("pagination" => $commentsData["pagination"]));

        $this->view->renderJson(array("data" => ["comments" => $commentsHTML, "pagination" => $paginationHTML]));
    }

    public function create(){

        $postId = Encryption::decryptId($this->request->data("post_id"));

        $content  = $this->request->data("content");

        $comment = $this->comment->create(Session::getUserId(), $postId, $content);

        if(!$comment){
            $this->view->renderErrors($this->comment->errors());
        }else{

            $html = $this->view->render(Config::get('VIEWS_PATH') . 'posts/comments.php', array("comments" => $comment));
            $this->view->renderJson(array("data" => $html));
        }
    }

    /**
     * whenever the user hits 'edit' button,
     * a request will be sent to get the update form of that comment,
     * so that the user can 'update' or even 'cancel' the edit request.
     *
     */
    public function getUpdateForm(){

        $commentId = Encryption::decryptIdWithDash($this->request->data("comment_id"));

        if(!$this->comment->exists($commentId)){
            return $this->error(404);
        }

        $comment = $this->comment->getById($commentId);

        $commentsHTML = $this->view->render(Config::get('VIEWS_PATH') . 'posts/commentUpdateForm.php', array("comment" => $comment[0]));
        $this->view->renderJson(array("data" => $commentsHTML));
    }

    /**
     * update comment
     *
     */
    public function update(){

        $commentId = Encryption::decryptIdWithDash($this->request->data("comment_id"));
        $content  = $this->request->data("content");

        if(!$this->comment->exists($commentId)){
            return $this->error(404);
        }

        $comment = $this->comment->update($commentId, $content);

        if(!$comment){
            $this->view->renderErrors($this->comment->errors());
        }else{

            $html = $this->view->render(Config::get('VIEWS_PATH') . 'posts/comments.php', array("comments" => $comment));
            $this->view->renderJson(array("data" => $html));
        }
    }

    /**
     * get comment by Id
     *
     */
    public function getById(){

        $commentId = Encryption::decryptIdWithDash($this->request->data("comment_id"));

        if(!$this->comment->exists($commentId)){
            return $this->error(404);
        }

        $comment = $this->comment->getById($commentId);

        $commentsHTML = $this->view->render(Config::get('VIEWS_PATH') . 'posts/comments.php', array("comments" => $comment));
        $this->view->renderJson(array("data" => $commentsHTML));
    }

    public function delete(){

        $commentId = Encryption::decryptIdWithDash($this->request->data("comment_id"));

        if(!$this->comment->exists($commentId)){
            return $this->error(404);
        }

        $this->comment->deleteById($commentId);

        $this->view->renderJson(array("success" => true));
    }

    public function isAuthorized(){

        $action = $this->request->param('action');
        $role = Session::getUserRole();
        $resource = "comments";

        // only for admins
        Permission::allow('admin', $resource, ['*']);

        // only for normal users
        Permission::allow('user', $resource, ['getAll', 'getById', 'create']);
        Permission::allow('user', $resource, ['update', 'delete', 'getUpdateForm'], 'owner');

        $commentId = $this->request->data("comment_id");
        if(!empty($commentId)){
            $commentId = Encryption::decryptIdWithDash($commentId);
        }

        $config = [
            "user_id" => Session::getUserId(),
            "table" => "comments",
            "id" => $commentId];

        return Permission::check($role, $resource, $action, $config);
    }
}
