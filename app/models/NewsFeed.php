<?php

 /**
  * NewsFeed Class
  *
  * @license    http://opensource.org/licenses/MIT The MIT License (MIT)
  * @author     Omar El Gabry <omar.elgabry.93@gmail.com>
  */
class NewsFeed extends Model{

    /**
     * get all news feed.
     *
     * @access public
     * @param  integer  $pageNum
     * @return array
     *
     */
    public function getAll($pageNum = 1){

        $pagination = Pagination::pagination("newsfeed", "", [], $pageNum);
        $offset     = $pagination->getOffset();
        $limit      = $pagination->perPage;

        $database   = Database::openConnection();
        $query  = "SELECT newsfeed.id AS id, users.profile_picture, users.id AS user_id, users.name AS user_name, newsfeed.content, newsfeed.date ";
        $query .= "FROM users, newsfeed ";
        $query .= "WHERE users.id = newsfeed.user_id ";
        $query .= "ORDER BY newsfeed.date DESC ";
        $query .= "LIMIT $limit OFFSET $offset";

        $database->prepare($query);
        $database->execute();
        $newsfeed = $database->fetchAllAssociative();

        return array("newsfeed" => $newsfeed, "pagination" => $pagination);
     }

    /**
     * get news feed by Id.
     *
     * @param  string  $newsfeedId
     * @return array
      */
    public function getById($newsfeedId){

        $database = Database::openConnection();
        $query  = "SELECT newsfeed.id AS id, users.profile_picture, users.id AS user_id, users.name AS user_name, newsfeed.content, newsfeed.date ";
        $query .= "FROM users, newsfeed ";
        $query .= "WHERE newsfeed.id = :id ";
        $query .= "AND users.id = newsfeed.user_id  LIMIT 1 ";

        $database->prepare($query);
        $database->bindValue(':id', (int)$newsfeedId);
        $database->execute();

        $feed = $database->fetchAllAssociative();
        return $feed;
     }

    /**
     * create news feed.
     *
     * @param  integer $userId
     * @param  string  $content
     * @return array feed created
     * @throws Exception if feed couldn't be created
     */
    public function create($userId, $content){

        $validation = new Validation();
        if(!$validation->validate(['Content'   => [$content, "required|minLen(4)|maxLen(300)"]])) {
            $this->errors = $validation->errors();
            return false;
        }

        $database = Database::openConnection();
        $query    = "INSERT INTO newsfeed (user_id, content) VALUES (:user_id, :content)";

        $database->prepare($query);
        $database->bindValue(':user_id', $userId);
        $database->bindValue(':content', $content);
        $database->execute();

        if($database->countRows() !== 1){
            throw new Exception("Couldn't add news feed");
        }

        $newsfeedId = $database->lastInsertedId();
        $feed = $this->getById($newsfeedId);
        return $feed;
     }

    /**
     * update news feed.
     *
     * @param  string  $newsfeedId
     * @param  string  $content
     * @return array   feed created
     * @throws Exception if feed couldn't be updated
     */
    public function update($newsfeedId, $content){

        $validation = new Validation();
        if(!$validation->validate(['Content'   => [$content, "required|minLen(4)|maxLen(300)"]])) {
            $this->errors = $validation->errors();
            return false;
        }

        $database = Database::openConnection();
        $query = "UPDATE newsfeed SET content = :content WHERE id = :id LIMIT 1";

        $database->prepare($query);
        $database->bindValue(':content', $content);
        $database->bindValue(':id', $newsfeedId);
        $result = $database->execute();

        if(!$result){
            throw new Exception("Couldn't update newsfeed of ID: " . $newsfeedId);
        }

        $feed = $this->getById($newsfeedId);
        return $feed;
     }

 }