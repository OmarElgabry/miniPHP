<?php

 /**
  * Admin Class
  * Admin Class inherits from User.
  *
  * @license    http://opensource.org/licenses/MIT The MIT License (MIT)
  * @author     Omar El Gabry <omar.elgabry.93@gmail.com>
  */

class Admin extends User{

    /**
     * get all users in the database
     *
     * @access public
     * @param  string  $name
     * @param  string  $email
     * @param  string  $role
     * @param  integer $pageNum
     * @return array
     *
     */
    public function getUsers($name = null, $email = null, $role = null, $pageNum = 1){

        // validate user inputs
        $validation = new Validation();
        if(!$validation->validate([
            'User Name' => [$name,  'alphaNumWithSpaces|maxLen(30)'],
            'Email'     => [$email, 'email|maxLen(50)'],
            'Role'      => [$role,  'inArray(admin, user)']])){
            $this->errors  = $validation->errors();
            return false;
        }

        // in $options array, add all possible values from user, and their name parameters
        // then applyOptions() method will see if value is not empty, then add it to our query
        $options = [
            $name      => "name LIKE :name ",
            $email     => "email = :email ",
            $role      => "role = :role "
        ];

        // get options query
        $options = $this->applyOptions($options, "AND ");
        $options = empty($options)? "": "WHERE " . $options;

        $values = [];
        if (!empty($name))  $values[":name"]  = "%". $name ."%";
        if (!empty($email)) $values[":email"] = $email;
        if (!empty($role))  $values[":role"]  = $role;

        // get pagination object so that we can add offset and limit in our query
        $pagination = Pagination::pagination("users", $options, $values, $pageNum);
        $offset     = $pagination->getOffset();
        $limit      = $pagination->perPage;

        $database   = Database::openConnection();
        $query   = "SELECT id, name, email, role, is_email_activated FROM users ";
        $query  .= $options;
        $query  .= "LIMIT $limit OFFSET $offset";

        $database->prepare($query);
        $database->execute($values);
        $users = $database->fetchAllAssociative();

        return array("users" => $users, "pagination" => $pagination);
     }

    /**
     *  Update info of a passed user id
     *
     * @access public
     * @param  integer $userId
     * @param  integer $adminId
     * @param  string  $name
     * @param  string  $password
     * @param  string  $role
     * @return bool
     * @throws Exception If password couldn't be updated
     *
     */
    public function updateUserInfo($userId, $adminId, $name, $password, $role){

         $user = $this->getProfileInfo($userId);

         $name = (!empty($name) && $name !== $user["name"])? $name: null;
         $role = (!empty($role) && $role !== $user["role"])? $role: null;

         // current admin can't change his role,
         // changing the role requires to logout or reset session,
         // because role is stored in the session
         if(!empty($role) && $adminId === $user["id"]){
             $this->errors[] = "You can't change your role";
             return false;
         }

        $validation = new Validation();
        if(!$validation->validate([
             "Name" => [$name, "alphaNumWithSpaces|minLen(4)|maxLen(30)"],
             "Password" => [$password, "minLen(6)|password"],
             'Role' => [$role, "inArray(admin, user)"]])){
             $this->errors = $validation->errors();
             return false;
         }

         if($password || $name || $role) {

             $options = [
                 $name     => "name = :name ",
                 $password => "hashed_password = :hashed_password ",
                 $role     => "role = :role "
             ];

             $database = Database::openConnection();
             $query   = "UPDATE users SET ";
             $query  .= $this->applyOptions($options, ", ");
             $query  .= "WHERE id = :id LIMIT 1 ";
             $database->prepare($query);

             if($name) {
                 $database->bindValue(':name', $name);
             }
             if($password) {
                 $database->bindValue(':hashed_password', password_hash($password, PASSWORD_DEFAULT, array('cost' => Config::get('HASH_COST_FACTOR'))));
             }
             if($role){
                 $database->bindValue(':role', $role);
             }

             $database->bindValue(':id', $userId);
             $result = $database->execute();

             if(!$result){
                 throw new Exception("Couldn't update profile");
             }
         }

         return true;
     }

    /**
     * Delete a user.
     *
     * @param  string  $adminId
     * @param  integer $userId
     * @throws Exception
     */
    public function deleteUser($adminId, $userId){

        // current admin can't delete himself
        $validation = new Validation();
        if(!$validation->validate([ 'User ID' => [$userId, "notEqual(".$adminId.")"]])) {
            $this->errors  = $validation->errors();
            return false;
        }

        $database = Database::openConnection();
        $database->deleteById("users", $userId);

        if ($database->countRows() !== 1) {
            throw new Exception ("Couldn't delete user");
        }
    }

     /**
      * Counting the number of users in the database.
      *
      * @access public
      * @static static  method
      * @return integer number of users
      *
      */
     public function countUsers(){
         return $this->countAll("users");
     }

     /**
      * Get the backup file from the backup directory in file system
      *
      * @access public
      * @return array
      */
     public function getBackups() {

         $files = scandir(APP . "backups/");
         $basename = $filename = $unixTimestamp = null;

         foreach ($files as $file) {
             if ($file != "." && $file != "..") {

                 $filename_array = explode('-', pathinfo($file, PATHINFO_FILENAME));
                 if(count($filename_array) !== 2){
                     continue;
                 }

                 // backup file has name with something like this: backup-1435788336
                 list($filename, $unixTimestamp) = $filename_array;
                 $basename = $file;
                 break;
             }
         }

         $data = array("basename" => $basename, "filename" => $filename, "date" => "On " . date("F j, Y", $unixTimestamp));
         return $data;
     }

    /**
     * Update the backup file from the backup directory in file system
     * The user of the database MUST be assigned privilege of ADMINISTRATION -> LOCK TABLES.
     *
     * @access public
     * @return bool
     */
    public function updateBackup(){

         $dir = APP . "backups/";
         $files = scandir($dir);

         // delete and clean all current files in backup directory
         foreach ($files as $file) {
             if ($file != "." && $file != "..") {
                 if (is_file("$dir/$file")) {
                     Uploader::deleteFile("$dir/$file");
                 }
             }
         }

         // you can use another username and password only for this function, while the main user has limited privileges
         $windows = true;
         if($windows){
             exec('C:\wamp\bin\mysql\mysql5.6.17\bin\mysqldump --user=' . escapeshellcmd(Config::get('DB_USER')) . ' --password=' . escapeshellcmd(Config::get('DB_PASS')) . ' ' . escapeshellcmd(Config::get('DB_NAME')) . ' > '. APP.'backups\backup-' . time() . '.sql');
         }else{
             exec('mysqldump --user=' . escapeshellcmd(Config::get('DB_USER')) . ' --password=' .escapeshellcmd(Config::get('DB_PASS')). ' '. escapeshellcmd(Config::get('DB_NAME')) .' > '. APP . 'backups/backup-' . time() . '.sql');
         }

         return true;
     }

    /**
     * Restore the backup file
     * The user of the database MUST assigned all privileges of SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, INDEX, DROP, LOCK TABLES, & TRIGGER.
     *
     * @access public
     * @return bool
     *
     */
    public function restoreBackup(){

         $basename = $this->getBackups()["basename"];

         $validation = new Validation();
         $validation->addRuleMessage("required", "Please update backups first!");

         if(!$validation->validate(["Backup" => [$basename, "required"]])) {
             $this->errors = $validation->errors();
             return false;
         }

         $windows = true;
         if($windows){
             exec('C:\wamp\bin\mysql\mysql5.6.17\bin\mysql --user=' . escapeshellcmd(Config::get('DB_USER')) . ' --password=' . escapeshellcmd(Config::get('DB_PASS')) . ' ' . escapeshellcmd(Config::get('DB_NAME')) . ' < '.APP.'\backups\\' . $basename);
         }else{
             exec('mysql --user='.escapeshellcmd(Config::get('DB_USER')).' --password='.escapeshellcmd(Config::get('DB_PASS')).' '.escapeshellcmd(Config::get('DB_NAME')).' < '. APP . 'backups/' . $basename);
         }

         return true;
     }

    /**
     * get users data.
     * Use this method to download users info in database as csv file.
     *
     * @access public
     * @return array
     */
    public function getUsersData(){

        $database = Database::openConnection();

        $database->prepare("SELECT name, role, email, is_email_activated FROM users");
        $database->execute();

        $users = $database->fetchAllAssociative();
        $cols  = array("User Name", "Role", "Email", "is Email Activated?");

        return ["rows" => $users, "cols" => $cols, "filename" => "users"];
    }

 }
   