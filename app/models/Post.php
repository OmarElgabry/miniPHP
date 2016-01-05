<?php

 /**
  * Post Class
  *
  * @license    http://opensource.org/licenses/MIT The MIT License (MIT)
  * @author     Omar El Gabry <omar.elgabry.93@gmail.com>
  */

 class Post extends Model{

     /**
      * get all posts
      *
      * @access public
      * @param  integer  $pageNum
      * @return array    Associative array of the posts, and Pagination Object.
      *
      */
     public function getAll($pageNum = 1){

         $pagination = Pagination::pagination("posts", "", [], $pageNum);
         $offset     = $pagination->getOffset();
         $limit      = $pagination->perPage;

         $database   = Database::openConnection();
         $query  = "SELECT posts.id AS id, users.profile_picture, users.id AS user_id, users.name AS user_name, posts.title, posts.content, posts.date ";
         $query .= "FROM users, posts ";
         $query .= "WHERE users.id = posts.user_id ";
         $query .= "ORDER BY posts.date DESC ";
         $query .= "LIMIT $limit OFFSET $offset";

         $database->prepare($query);
         $database->execute();
         $posts = $database->fetchAllAssociative();

         $this->appendNumberOfComments($posts, $database);

         return array("posts" => $posts, "pagination" => $pagination);
     }

     /**
      * append number of comments to the array of posts for each post.
      *
      * @access private
      * @param  array
      *
      */
     private function appendNumberOfComments(&$posts){

         $postId = 0;
         $database = Database::openConnection();

         $query  = "SELECT COUNT(*) AS comments FROM comments WHERE post_id = :post_id ";
         $database->prepare($query);
         $database->bindParam(':post_id', $postId);

         foreach($posts as $key => $post){
             $postId = (int)$posts[$key]["id"];
             $database->execute();
             $posts[$key]["comments"] = $database->fetchAssociative()["comments"];
         }
     }

     /**
      * get post by Id.
      *
      * @access public
      * @param  integer  $postId
      * @return array    Array holds the data of the post
      */
     public function getById($postId){

         $database = Database::openConnection();
         $query  = "SELECT posts.id AS id, users.profile_picture, users.id AS user_id, users.name AS user_name, posts.title, posts.content, posts.date ";
         $query .= "FROM users, posts ";
         $query .= "WHERE posts.id = :id ";
         $query .= "AND users.id = posts.user_id LIMIT 1 ";

         $database->prepare($query);
         $database->bindValue(':id', $postId);
         $database->execute();

         $post = $database->fetchAssociative();
         return $post;
     }

     /**
      * create post
      *
      * @access public
      * @param  integer   $userId
      * @param  string    $title
      * @param  string    $content
      * @return bool
      * @throws Exception If post couldn't be created
      *
      */
     public function create($userId, $title, $content){

         $validation = new Validation();
         if(!$validation->validate([
             'Title'   => [$title, "required|minLen(2)|maxLen(60)"],
             'Content'   => [$content, "required|minLen(4)|maxLen(1800)"]])) {
             $this->errors = $validation->errors();
             return false;
         }

         $database = Database::openConnection();
         $query    = "INSERT INTO posts (user_id, title, content) VALUES (:user_id, :title, :content)";

         $database->prepare($query);
         $database->bindValue(':user_id', $userId);
         $database->bindValue(':title', $title);
         $database->bindValue(':content', $content);
         $database->execute();

         if($database->countRows() !== 1){
             throw new Exception ("Couldn't add news feed");
         }

         return true;
     }

     /**
      * update Post
      *
      * @access public
      * @static static method
      * @param  string    $postId
      * @param  string    $title
      * @param  string    $content
      * @return array     Array of the updated post
      * @throws Exception If post couldn't be updated
      *
      */
     public function update($postId, $title, $content){

         $validation = new Validation();
         if(!$validation->validate([
             'Title'   => [$title, "required|minLen(2)|maxLen(60)"],
             'Content' => [$content, "required|minLen(4)|maxLen(1800)"]])) {
             $this->errors = $validation->errors();
             return false;
         }

         $database = Database::openConnection();
         $query = "UPDATE posts SET title = :title, content = :content WHERE id = :id LIMIT 1";

         $database->prepare($query);
         $database->bindValue(':title', $title);
         $database->bindValue(':content', $content);
         $database->bindValue(':id', $postId);
         $result = $database->execute();

         if(!$result){
             throw new Exception("Couldn't update post of ID: " . $postId);
         }

         $post = $this->getById($postId);
         return $post;
     }

 }
