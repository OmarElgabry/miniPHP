<?php
/**
 * Comment Class
 *
 * @license    http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author     Omar El Gabry <omar.elgabry.93@gmail.com>
 */

class Comment extends Model{

    /**
     * get all comments of a post
     *
     * @access public
     * @param  array     $postId
     * @param  integer   $pageNum
     * @param  integer   $commentsCreated
     * @return array    Associative array of the comments, and Pagination Object(View More).
     *
     */
    public function getAll($postId, $pageNum = 1, $commentsCreated = 0){

        // Only for comments, We use $commentsCreated
        // What's it? Whenever we create a comment, It will be added in-place to the current comments in current .php page,
        // So, we need to track of those were created, and skip them in the Pagination($offset & $totalCount).

        $options    = "WHERE comments.post_id = :post_id ";
        $pagination = Pagination::pagination("comments", $options, [":post_id" => $postId], $pageNum, $commentsCreated);
        $offset     = $pagination->getOffset() + $commentsCreated;
        $limit      = $pagination->perPage;

        $database   = Database::openConnection();
        $query  = "SELECT comments.id AS id, users.profile_picture, users.id AS user_id, users.name AS user_name, comments.content, comments.date ";
        $query .= "FROM users, posts, comments ";
        $query .= "WHERE comments.post_id = :post_id ";
        $query .= "AND posts.id = comments.post_id ";
        $query .= "AND users.id = comments.user_id ";
        $query .= "ORDER BY comments.date DESC ";
        $query .= "LIMIT $limit OFFSET $offset";

        $database->prepare($query);
        $database->bindValue(':post_id', (int)$postId);
        $database->execute();
        $comments = $database->fetchAllAssociative();

        // you can have post with no comments yet!
        return array("comments" => $comments, "pagination" => $pagination);
    }

    /**
     * get comment by Id
     *
     * @access public
     * @param  string   $commentId
     * @return array    Array holds the data of the comment
     *
     */
    public function getById($commentId){

        $database = Database::openConnection();
        $query  = "SELECT comments.id AS id, users.profile_picture, users.id AS user_id, users.name AS user_name, comments.content, comments.date ";
        $query .= "FROM users, posts, comments ";
        $query .= "WHERE comments.id = :id ";
        $query .= "AND posts.id = comments.post_id ";
        $query .= "AND users.id = comments.user_id LIMIT 1";

        $database->prepare($query);
        $database->bindValue(':id', (int)$commentId);
        $database->execute();

        $comment = $database->fetchAllAssociative();
        return $comment;
    }

    /**
     * create Comment.
     *
     * @access public
     * @param  string   $userId
     * @param  string   $postId
     * @param  string   $content
     * @return array    Array holds the created comment
     * @throws Exception If comment couldn't be created
     *
     */
    public function create($userId, $postId, $content){

        $validation = new Validation();
        if(!$validation->validate([ 'Content' => [$content, 'required|minLen(1)|maxLen(300)']])) {
            $this->errors = $validation->errors();
            return false;
        }

        $database = Database::openConnection();
        $query = "INSERT INTO comments (user_id, post_id, content) VALUES (:user_id, :post_id, :content)";

        $database->prepare($query);
        $database->bindValue(':user_id', $userId);
        $database->bindValue(':post_id', $postId);
        $database->bindValue(':content', $content);
        $database->execute();

        if($database->countRows() !== 1){
            throw new Exception ("Couldn't add comment");
        }

        $commentId = $database->lastInsertedId();
        $comment = $this->getById($commentId);
        return $comment;
    }

    /**
     * update Comment
     *
     * @access public
     * @param  string   $commentId
     * @param  string   $content
     * @return array    Array holds the updated comment
     * @throws Exception If comment couldn't be updated
     *
     */
    public function update($commentId, $content){

        $validation = new Validation();
        if(!$validation->validate([ 'Content' => [$content, 'required|minLen(1)|maxLen(300)']])) {
            $this->errors = $validation->errors();
            return false;
        }

        $database = Database::openConnection();
        $query = "UPDATE comments SET content = :content WHERE id = :id LIMIT 1 ";
        $database->prepare($query);
        $database->bindValue(':content', $content);
        $database->bindValue(':id', $commentId);
        $result = $database->execute();

        if(!$result){
            throw new Exception("Couldn't update comment of ID: " . $commentId);
        }

        $comment = $this->getById($commentId);
        return $comment;
    }

     /**
      * counting the number of comments of a post.
      *
      * @access public
      * @static static  method
      * @param  string  $postId
      * @return integer number of comments
      *
      */
    public static function countComments($postId){

        $database = Database::openConnection();
        $database->prepare("SELECT COUNT(*) AS count FROM comments WHERE post_id = :post_id");
        $database->bindValue(":post_id", $postId);
        $database->execute();

        return (int)$database->fetchAssociative()["count"];
    }

}