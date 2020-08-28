<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once(dirname(__FILE__) . "/../startup.php");
require_once(APPLICATION_PATH . '/models/General.php');

/**
 * General functions
 *
 * @author Amit
 */

class Model_Users
{

    protected $db = null;
    protected $table = 'user_details';

    public function __construct($db = null)
    {
        $this->db = $db;
    }

    public function getUserInfo($userId, $columns = '*')
    {
        if(is_array($columns)){
            $columns = implode(",", $columns);
        }
        $uQuery = "SELECT $columns FROM " . $this->table . " where user_id='$userId'";
        return $this->db->rawQueryOne($uQuery);
    }

    public function addUserIfNotExists($name, $status = 'inactive', $role = 4)
    {

        $uQuery = "SELECT user_id FROM $this->table where user_name like '$name'";
        $result = $this->db->rawQueryOne($uQuery);

        if ($result == null) {
            $general = new General($this->db);
            $userId = $general->generateUserID();
            $userData = array(
                'user_id' => $userId,
                'user_name' => $name,
                'role_id' => $role,
                'status' => $status
            );
            $this->db->insert($this->table, $userData);
        } else {
            $userId = $result['user_id'];
        }


        return $userId;
    }
}
