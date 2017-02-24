<?php

 /**
  * User Class
  *
  * @license    http://opensource.org/licenses/MIT The MIT License (MIT)
  * @author     Omar El Gabry <omar.elgabry.93@gmail.com>
  */

class User extends Model{

    /**
      * Table name for this & extending classes.
      *
      * @var string
      */
    public $table = "users";

    /**
     * returns an associative array holds the user info(image, name, id, ...etc.)
     *
     * @access public
     * @param  integer $userId
     * @return array Associative array of current user info/data.
     * @throws Exception if $userId is invalid.
     */
    public function getProfileInfo($userId){

        $database = Database::openConnection();
        $database->getById("users", $userId);

        if($database->countRows() !== 1){
            throw new Exception("User ID " .  $userId . " doesn't exists");
        }

        $user = $database->fetchAssociative();

        $user["id"]    = (int)$user["id"];
        $user["image"] = PUBLIC_ROOT . "img/profile_pictures/" . $user['profile_picture'];
        // $user["email"] = empty($user['is_email_activated'])? null: $user['email'];

        return $user;
      }

    /**
     * Update the current profile
     *
     * @access public
     * @param  integer $userId
     * @param  string  $name
     * @param  string  $password
     * @param  string  $email
     * @param  string  $confirmEmail
     * @return bool|array
     * @throws Exception If profile couldn't be updated
     *
     */
    public function updateProfileInfo($userId, $name, $password, $email, $confirmEmail){

        $database = Database::openConnection();
        $curUser = $this->getProfileInfo($userId);

        $name   = (!empty($name) && $name !== $curUser["name"])? $name: null;
        $email  = (!empty($confirmEmail) || (!empty($email) && $email !== $curUser["email"]))? $email: null;

        // if new email === old email, this shouldn't return any errors for email,
        // because they are not 'required', same for name.
        $validation = new Validation();
        if(!$validation->validate([
            "Name" => [$name, "alphaNumWithSpaces|minLen(4)|maxLen(30)"],
            "Password" => [$password, "minLen(6)|password"],
            "Email" => [$email, "email|emailUnique|maxLen(50)|equals(".$confirmEmail.")"]])){
            $this->errors = $validation->errors();
            return false;
        }

        $profileUpdated = ($password || $name || $email)? true: false;
        if($profileUpdated) {

            $options = [
                $name     => "name = :name ",
                $password => "hashed_password = :hashed_password ",
                $email    => "pending_email = :pending_email, pending_email_token = :pending_email_token, email_token = :email_token "
            ];

            $database->beginTransaction();
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
            if($email) {
                $emailToken = sha1(uniqid(mt_rand(), true));
                $pendingEmailToken = sha1(uniqid(mt_rand(), true));
                $database->bindValue(':pending_email', $email);
                $database->bindValue(':pending_email_token', $pendingEmailToken);
                $database->bindValue(':email_token', $emailToken);
            }

            $database->bindValue(':id', $userId);
            $result = $database->execute();

            if(!$result){
                $database->rollBack();
                throw new Exception("Couldn't update profile");
            }

            // If email was updated, then send two emails,
            // one for the current one asking user optionally to revoke,
            // and another one for the new email asking user to confirm changes.
            if($email){
                $name = ($name)? $name: $curUser["name"];
                Email::sendEmail(Config::get('EMAIL_REVOKE_EMAIL'), $curUser["email"], ["name" => $name, "id" => $curUser["id"]], ["email_token" => $emailToken]);
                Email::sendEmail(Config::get('EMAIL_UPDATE_EMAIL'), $email, ["name" => $name, "id" => $curUser["id"]], ["pending_email_token" => $pendingEmailToken]);
            }

            $database->commit();
        }

        return ["emailUpdated" => (($email)? true: false)];
    }

    /**
     * Update Profile Picture.
     *
     * @access public
     * @param  integer $userId
     * @param  array   $fileData
     * @return mixed
     * @throws Exception If failed to update profile picture.
     */
    public function updateProfilePicture($userId, $fileData){

        $image = Uploader::uploadPicture($fileData, $userId);

        if(!$image) {
            $this->errors = Uploader::errors();
            return false;
        }

        $database = Database::openConnection();
        $query  =  "UPDATE users SET profile_picture = :profile_picture WHERE id = :id LIMIT 1";

        $database->prepare($query);
        $database->bindValue(':profile_picture', $image["basename"]);
        $database->bindValue(':id', $userId);
        $result = $database->execute();

        // if update failed, then delete the user picture
        if(!$result){
            Uploader::deleteFile(IMAGES . "profile_pictures/" . $image["basename"]);
            throw new Exception("Profile Picture ". $image["basename"] . " couldn't be updated");
        }

        return $image;
      }

    /**
     * revoke Email updates
     *
     * @access public
     * @param  integer  $userId
     * @param  string   $emailToken
     * @return mixed
     * @throws Exception If failed to revoke email updates.
     */
    public function revokeEmail($userId, $emailToken){

        if (empty($userId) || empty($emailToken)) {
            return false;
        }

        $database = Database::openConnection();
        $database->prepare("SELECT * FROM users WHERE id = :id AND email_token = :email_token AND is_email_activated = 1 LIMIT 1");
        $database->bindValue(':id', $userId);
        $database->bindValue(':email_token', $emailToken);
        $database->execute();
        $users = $database->countRows();

        $query = "UPDATE users SET email_token = NULL, pending_email = NULL, pending_email_token = NULL WHERE id = :id LIMIT 1";
        $database->prepare($query);
        $database->bindValue(':id', $userId);
        $result = $database->execute();

        if(!$result){
            throw new Exception("Couldn't revoke email updates");
        }

        if ($users === 1){
            return true;
        }else{
            Logger::log("REVOKE EMAIL", "User ID ". $userId . " is trying to revoke email using wrong token " . $emailToken, __FILE__, __LINE__);
            return false;
        }
    }

    /**
     * update Email
     *
     * @access public
     * @param  integer  $userId
     * @param  string   $emailToken
     * @return mixed
     * @throws Exception If failed to update current email.
     */
    public function updateEmail($userId, $emailToken){

        if (empty($userId) || empty($emailToken)) {
            return false;
        }

        $database = Database::openConnection();
        $database->prepare("SELECT * FROM users WHERE id = :id AND pending_email_token = :pending_email_token AND is_email_activated = 1 LIMIT 1");
        $database->bindValue(':id', $userId);
        $database->bindValue(':pending_email_token', $emailToken);
        $database->execute();

        if($database->countRows() === 1){

            $user = $database->fetchAssociative();
            $validation = new Validation();
            $validation->addRuleMessage("emailUnique", "We can't change your email because it has been already taken!");

            if(!$validation->validate(["Email" => [$user["pending_email"], "emailUnique"]])){

                $query = "UPDATE users SET email_token = NULL, pending_email = NULL, pending_email_token = NULL WHERE id = :id LIMIT 1";
                $database->prepare($query);
                $database->bindValue(':id', $userId);
                $database->execute();

                $this->errors = $validation->errors();

                return false;

            }else{

                $query = "UPDATE users SET email = :email, email_token = NULL, pending_email = NULL, pending_email_token = NULL WHERE id = :id LIMIT 1";
                $database->prepare($query);
                $database->bindValue(':id', $userId);
                $database->bindValue(':email', $user["pending_email"]);
                $result = $database->execute();

                if(!$result){
                    throw new Exception("Couldn't update current email");
                }

                return true;
            }
        }else {

            $query = "UPDATE users SET email_token = NULL, pending_email = NULL, pending_email_token = NULL WHERE id = :id LIMIT 1";
            $database->prepare($query);
            $database->bindValue(':id', $userId);
            $database->execute();

            Logger::log("UPDATE EMAIL", "User ID ". $userId . " is trying to update email using wrong token " . $emailToken, __FILE__, __LINE__);
            return false;
        }

    }

    /**
     * Get Notifications for newsfeed, posts & files.
     *
     * @access public
     * @param  integer $userId
     * @return array
     */
    public function getNotifications($userId){

        $database = Database::openConnection();
        $query = "SELECT target, count FROM notifications WHERE user_id = :user_id";

        $database->prepare($query);
        $database->bindValue(":user_id", $userId);
        $database->execute();

        $notifications = $database->fetchAllAssociative();
        return $notifications;
      }

    /**
     * Clear Notifications for a specific target
     *
     * @access public
     * @param  integer $userId
     * @param  string $table
     */
    public function clearNotifications($userId, $table){

          $database = Database::openConnection();
          $query = "UPDATE notifications SET count = 0 WHERE user_id = :user_id AND target = :target";

          $database->prepare($query);
          $database->bindValue(":user_id", $userId);
          $database->bindValue(":target", $table);
          $result = $database->execute();

          if(!$result) {
              Logger::log("NOTIFICATIONS", "Couldn't clear notifications", __FILE__, __LINE__);
          }
      }

    /**
     * Returns an overview about the current system:
     * 1. counts of newsfeed, posts, files, users
     * 2. latest updates by using "UNION"
     *
     * @access public
     * @return array
     *
     */
    public function dashboard(){

        $database = Database::openConnection();

        // 1. count
        $tables = ["newsfeed", "posts", "files", "users"];
        $stats  = [];

        foreach($tables as $table){
            $stats[$table] = $database->countAll($table);
        }

        // 2. latest updates
        // Using UNION to union the data fetched from different tables.
        // @see http://www.w3schools.com/sql/sql_union.asp
        // @see (mikeY) http://stackoverflow.com/questions/6849063/selecting-data-from-two-tables-and-ordering-by-date

        // Sub Query: In SELECT, The outer SELECT must have alias, like "updates" here.
        // NOTE: The outer SELECT is not needed; You don't need to wrap the union-ed select statements.
        // @see http://stackoverflow.com/questions/1888779/every-derived-table-must-have-its-own-alias

        $query  = "SELECT * FROM (";
        $query .= "SELECT 'newsfeed' AS target, content AS title, date, users.name FROM newsfeed, users WHERE user_id = users.id UNION ";
        $query .= "SELECT 'posts' AS target, title, date, users.name FROM posts, users WHERE user_id = users.id UNION ";
        $query .= "SELECT 'files' AS target, filename AS title, date, users.name FROM files, users WHERE user_id = users.id ";
        $query .= ") AS updates ORDER BY date DESC LIMIT 10";
        $database->prepare($query);
        $database->execute();
        $updates = $database->fetchAllAssociative();

        $data = array("stats" => $stats, "updates" => $updates);
        return $data;
    }

    /**
     * Reporting Bug, Feature, or Enhancement.
     *
     * @access public
     * @param  integer $userId
     * @param  string  $subject
     * @param  string  $label
     * @param  string  $message
     * @return bool
     *
     */
    public function reportBug($userId, $subject, $label, $message){

        $validation = new Validation();
        if(!$validation->validate([
            "Subject" => [$subject, "required|minLen(4)|maxLen(80)"],
            "Label" => [$label, "required|inArray(".Utility::commas(["bug", "feature", "enhancement"]).")"],
            "Message" => [$message, "required|minLen(4)|maxLen(1800)"]])){

            $this->errors = $validation->errors();
            return false;
          }

        $curUser = $this->getProfileInfo($userId);
        $data = ["subject" => $subject, "label" => $label, "message" => $message];

        // email will be sent to the admin
        Email::sendEmail(Config::get('EMAIL_REPORT_BUG'), Config::get('ADMIN_EMAIL'), ["id" => $userId, "name" => $curUser["name"]], $data);

        return true;
      }
  }
