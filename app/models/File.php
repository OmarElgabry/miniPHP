<?php

 /**
  * File Class
  *
  * @license    http://opensource.org/licenses/MIT The MIT License (MIT)
  * @author     Omar El Gabry <omar.elgabry.93@gmail.com>
  */

class File extends Model{

    /**
     * get all files.
     *
     * @access public
     * @param  integer  $pageNum
     * @return array
     *
     */
    public function getAll($pageNum = 1){

        // get pagination object
        $pagination = Pagination::pagination("files", "", [], $pageNum);
        $offset     = $pagination->getOffset();
        $limit      = $pagination->perPage;

        $database   = Database::openConnection();
        $query  = "SELECT files.id AS id, files.filename, users.id AS user_id, users.name AS user_name, files.extension AS format, files.hashed_filename, files.date ";
        $query .= "FROM users, files ";
        $query .= "WHERE users.id = files.user_id ";
        $query .= "ORDER BY files.date DESC ";
        $query .= "LIMIT $limit OFFSET $offset";

        $database->prepare($query);
        $database->execute();
        $files = $database->fetchAllAssociative();

        return array("files" => $files, "pagination" => $pagination);
     }

    /**
     * get file by Id.
     *
     * @access public
     * @param  string  $fileId
     * @return array   Array holds the data of the file
     */
    public function getById($fileId){

        $database = Database::openConnection();
        $query  = "SELECT files.id AS id, files.filename, users.id AS user_id, users.name AS user_name, files.extension AS format, files.hashed_filename, files.date ";
        $query .= "FROM users, files ";
        $query .= "WHERE files.id = :id ";
        $query .= "AND users.id = files.user_id LIMIT 1 ";

        $database->prepare($query);
        $database->bindValue(':id', (int)$fileId);
        $database->execute();

        $file = $database->fetchAllAssociative();
        return $file;
     }

    /**
     * get file by hashed name.
     * files are unique by the hashed file name(= hash(original filename . extension)).
     *
     * @access public
     * @param  string  $hashedFileName
     * @return array   Array holds the data of the file
     */
    public function getByHashedName($hashedFileName){

        $database = Database::openConnection();

        $query  = "SELECT files.id AS id, files.filename, files.extension, files.hashed_filename ";
        $query .= "FROM  files ";
        $query .= "WHERE hashed_filename = :hashed_filename ";
        $query .= "LIMIT 1 ";

        $database->prepare($query);
        $database->bindValue(':hashed_filename', $hashedFileName);
        $database->execute();

        $file = $database->fetchAssociative();
        return $file;
    }

    /**
     * create file.
     *
     * @access public
     * @param  integer   $userId
     * @param  array     $fileData
     * @return array     Array holds the created file
     * @throws Exception If file couldn't be created
     */
    public function create($userId, $fileData){

        // upload
        $file = Uploader::uploadFile($fileData);

        if(!$file) {
            $this->errors = Uploader::errors();
            return false;
        }

        $database = Database::openConnection();

        $query = "INSERT INTO files (user_id, filename, hashed_filename, extension) VALUES (:user_id, :filename, :hashed_filename, :extension)";

        $database->prepare($query);
        $database->bindValue(':user_id', $userId);
        $database->bindValue(':filename', $file["filename"]);
        $database->bindValue(':hashed_filename', $file["hashed_filename"]);
        $database->bindValue(':extension', strtolower($file["extension"]));
        $database->execute();

        // if insert failed, then delete the file
        if($database->countRows() !== 1){
            Uploader::deleteFile(APP ."uploads/" . $file["basename"]);
            throw new Exception ("Couldn't upload file");
        }

        $fileId = $database->lastInsertedId();
        $file = $this->getById($fileId);
        return $file;
     }

    /**
     * deletes file.
     * This method overrides the deleteById() method in Model class.
     *
     * @access public
     * @param  array    $id
     * @throws Exception If failed to delete the file
     *
     */
    public function deleteById($id){

        $database = Database::openConnection();

        $database->getById("files", $id);
        $file = $database->fetchAssociative();

        // start a transaction to guarantee the file will be deleted from both; database and filesystem
        $database->beginTransaction();
        $database->deleteById("files", $id);

        if($database->countRows() !== 1){
            $database->rollBack();
            throw new Exception ("Couldn't delete file");
        }

        $basename = $file["hashed_filename"] . "." . $file["extension"];
        Uploader::deleteFile(APP ."uploads/" . $basename);

        $database->commit();
     }

 }