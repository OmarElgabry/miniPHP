<?php

/**
 * Model Class
 *
 * Main/Super class for model classes
 *
 * @license    http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author     Omar El Gabry <omar.elgabry.93@gmail.com>
 */

class Model {

    /**
     * Table name for the Model.
     *
     * @var string
    */
    public $table = false;

    /**
     * Array of validation errors
     *
     * @var array
     */
    protected $errors = [];

    /**
     * Constructor
     *
     */
    public function __construct(){
        if($this->table === false){
            $this->table = $this->pluralize(get_class($this));
        }
    }

    /**
     * pluralize for table names
     *
     * Automatically selects a database table name based on a pluralized lowercase object class name
     * (i.e. class 'User' => table 'users')
     *
     * @param   string $word
     * @return  string
     */
    private function pluralize($word){

        $word = strtolower($word);
        $plural = [
            "newsfeed" => "newsfeed",
            "man" => "men",
            "woman" => "women"
        ];

        return isset($plural[$word])? $plural[$word]: $word . "s";
    }

    /**
     * delete record by id
     *
     * @param  string $id
     * @return bool
     * @throws Exception if feed couldn't be deleted
     */
    public function deleteById($id){

        $database = Database::openConnection();
        $database->deleteById($this->table, $id);

        if($database->countRows() !== 1){
            throw new Exception ("Couldn't delete news feed");
        }
    }

    /**
     * get errors
     *
     * @return array errors
     */
    public function errors(){
        return $this->errors;
    }

    /**
     * is record exists?
     *
     * Almost all methods in model needs you to pass the current user id,
     * Another approach is to create in Model class a property call id(will be inherited by all extending classes)
     * Ex:
     *  Inside postsController:
     *  post->id = $postId
     *  post->updatePost(....) -> Inside updatePost() you can get the post id by: $this->id
     *
     * @param  string  $id
     * @return bool
     */
    public function exists($id){

        $database = Database::openConnection();
        $database->getById($this->table, $id);

        return $database->countRows() === 1;
    }

    /**
     * Counting the number of a current model's table.
     *
     * @return integer
     */
    public function countAll(){

        $database = Database::openConnection();
        return $database->countAll($this->table);
    }

    /**
     * adds parts to current query using an array.
     *
     * The array will have $key which is the field value, and the $value is the query to be added to the current query
     * Only fields with existing value(false, and 0 are accepted as valid values) will be considered in our query.
     *
     * @param  array  $options
     * @param  string $implodeBy
     * @return string
     */
    public function applyOptions(array $options, $implodeBy){

        $queries = [];

        foreach($options as $key => $value){
            if(!empty($key) || $key === false || $key === 0){
                $queries[] = $value;
            }
        }
        return implode($implodeBy, $queries);
    }

}
