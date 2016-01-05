<?php
 
 /**
  * Todo Class
  *
  * @license    http://opensource.org/licenses/MIT The MIT License (MIT)
  * @author     Omar El Gabry <omar.elgabry.93@gmail.com>
  */

class Todo extends Model{

    public function getAll(){

        $database = Database::openConnection();
        $query  = "SELECT todo.id AS id, users.id AS user_id, users.name AS user_name, todo.content ";
        $query .= "FROM users, todo ";
        $query .= "WHERE users.id = todo.user_id ";

        $database->prepare($query);
        $database->execute();
        $todo = $database->fetchAllAssociative();

        return $todo;
     }

    public function create($userId, $content){

        // using validation class
        $validation = new Validation();
        if(!$validation->validate(['Content'   => [$content, "required|minLen(4)|maxLen(300)"]])) {
            $this->errors = $validation->errors();
            return false;
        }

        // using database class to insert new todo
        $database = Database::openConnection();
        $query    = "INSERT INTO todo (user_id, content) VALUES (:user_id, :content)";
        $database->prepare($query);
        $database->bindValue(':user_id', $userId);
        $database->bindValue(':content', $content);
        $database->execute();

        if($database->countRows() !== 1){
            throw new Exception("Couldn't create todo");
        }

        return true;
     }

    public function delete($id){

        $database = Database::openConnection();
        $database->deleteById("todo", $id);

        if($database->countRows() !== 1){
            throw new Exception ("Couldn't delete todo");
        }
    }
 }