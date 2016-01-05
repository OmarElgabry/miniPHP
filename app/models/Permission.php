<?php

 /**
  * Permission Class
  *
  * Handles all permissions for accessing resources(usually controllers), and what are the actions a user can perform
  *
  * This class requires from you to define all permission rules in you PHP code,
  * Another approach you could take is to define them in the database,
  * But, i find this approach is simpler and less costly at least for this application.
  *
  * @license    http://opensource.org/licenses/MIT The MIT License (MIT)
  * @author     Omar El Gabry <omar.elgabry.93@gmail.com>
  */

class Permission {

    /**
     * allowed permissions for actions on specific resources
     *
     * $perms[] = [
     *      'role' => 'student', //AROs
     *      'resource' => 'Post' //ACOs - actions could be ACOs instead.
     *      'actions' => ['edit', 'delete'],
     *      'conditions' => ['owner'] - things that you validate against if the user has access to the action
     *  ];
     *
     * @var array
     */
    public static $perms = [];

    /**
     * check if the $role has access to $action on $resource
     *
     * @param  string  $role
     * @param  string  $resource
     * @param  string  $action   if set to "*", then check if $actions parameter was assigned to "*" when using allow() method
     *                           This indicates the $role has access to all actions on $resource
     * @param  array   $config   configuration data to be passed to condition methods
     * @throws Exception if $config is empty or method doesn't exists
     * @return boolean
     */
    public static function check($role, $resource, $action = "*", array $config = []){

        // checks if action was allowed at least once
        $allowed = false;
        $action = strtolower($action);

        foreach(self::$perms as $perm){
            if($perm['role'] === $role && $perm['resource'] === $resource){

                if(in_array($action, $perm["actions"], true) || $perm["actions"] === ["*"]){

                    $allowed = true;

                    foreach($perm["conditions"] as $condition){

                        if (!method_exists(__CLASS__, $condition)) {
                            throw new Exception("Permission, Method doesnt exists: " . $condition);
                        }

                        if(self::$condition($config) === false){
                            Logger::log("Permission", $role . " is not allowed to perform '" . $action . "' action on " . $resource . " because of " . $condition, __FILE__, __LINE__);
                            return false;
                        }
                    }
                }
            }
        }

        if(!$allowed){
            Logger::log("Permission", $role . " is not allowed to perform '" . $action . "' action on " . $resource, __FILE__, __LINE__);
        }

        return $allowed;
    }

    /**
     * Add new rule: allow a $role for $actions on $resource,
     * You may add additional $conditions that must be fulfilled as well.
     *
     * @param  string  $role
     * @param  string  $resource
     * @param  mixed   $actions
     * @param  mixed   $conditions
     */
    public static function allow($role, $resource, $actions = "*", $conditions = []){

        $actions = array_map("strtolower", (array)$actions);

        self::$perms[] = ['role' => $role, 'resource' => $resource, 'actions' => $actions, 'conditions' => (array)$conditions];
    }

    /**
     *  deny or remove $actions for a $role on $resource
     *
     * @param  string  $role
     * @param  string  $resource
     * @param  mixed   $actions
     */
    public static function deny($role, $resource, $actions = "*"){

        $actions = array_map("strtolower", (array)$actions);

        foreach(self::$perms as $key => &$perm){
            if($perm['role'] === $role && $perm['resource'] === $resource){
                foreach($perm['actions'] as $index => $action){
                    if(in_array($action, $actions, true) || $actions === ["*"]){
                        unset($perm['actions'][$index]);
                    }
                }

                if(empty($perm['actions'])){
                    unset(self::$perms[$key]);
                }
            }
        }
    }

    /** *********************************************** **/
    /** **************    Conditions     ************** **/
    /** *********************************************** **/

    /**
     * checks if user is owner
     *
     * @param  array $config
     * @return bool
     */
    private static function owner($config){

        $database = Database::openConnection();

        $database->prepare('SELECT * FROM '.$config["table"]. ' WHERE id = :id AND user_id = :user_id LIMIT 1');
        $database->bindValue(':id', (int)$config["id"]);
        $database->bindValue(':user_id', (int)$config["user_id"]);
        $database->execute();

        return $database->countRows() === 1;
    }

 }
