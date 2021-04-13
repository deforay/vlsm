<?php

namespace Vlsm\Models;

/**
 * General functions
 *
 * @author Amit
 */

class Users
{

    protected $db = null;
    protected $table = 'user_details';

    public function __construct($db = null)
    {
        $this->db = $db;
    }

    public function getUserInfo($userId, $columns = '*')
    {
        if (is_array($columns)) {
            $columns = implode(",", $columns);
        }
        $uQuery = "SELECT $columns FROM " . $this->table . " where user_id='$userId'";
        return $this->db->rawQueryOne($uQuery);
    }

    public function getActiveUserInfo()
    {
        $uQuery = "SELECT * FROM user_details where status='active'";
        return $this->db->rawQuery($uQuery);
    }

    public function addUserIfNotExists($name, $status = 'inactive', $role = 4)
    {
        $uQuery = "SELECT user_id FROM $this->table where user_name like '$name'";
        $result = $this->db->rawQueryOne($uQuery);
        if ($result == null) {
            $general = new \Vlsm\Models\General($this->db);
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



    public function getAuthToken($token)
    {
        $query = "SELECT * FROM $this->table WHERE api_token = ? and `status` = 'active'";
        $result = $this->db->rawQueryOne($query, array($token));
        if ($result['api_token_generated_datetime'] < date('Y-m-d H:i:s', strtotime('-30 days'))) {
            $general = new \Vlsm\Models\General($this->db);
            $token = $general->generateUserID();
            $data['api_token'] = $token;
            $data['api_token_generated_datetime'] = $general->getDateTime();

            $this->db = $this->db->where('user_id', $result['user_id']);
            $id = $this->db->update($this->table, $data);

            if ($id > 0) {
                $result['token-updated'] = true;
            } else {
                $result['token-updated'] = false;
            }
            $result['token-updated'] = true;
            $result['newToken'] = $token;
        }
        return $result;
    }
}
