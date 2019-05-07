<?php
session_start();

include_once '../../startup.php';

/**
 * General functions
 *
 * @author Amit
 */

class Users
{

    protected $db = null;
    protected $table = 'user_details';

    public function __construct($db = null) {
        $this->db = $db;
    }

    public function getUserInfo($userId , $columns = '*'){
        $uQuery="SELECT $columns FROM ".$this->table." where user_id='$userId'";
        return $this->db->rawQueryOne($uQuery); 
    }

}
