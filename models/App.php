<?php

/**
 * General functions
 *
 * @author Amit
 */

namespace Vlsm\Models;

class App
{

    protected $db = null;

    public function __construct($db = null)
    {
        $this->db = $db;
    }

    public static function generateAuthToken($length = 8, $type = 'alphanum')
    {
        // Possible seeds
        $seeds['alpha'] = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwqyzABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwqyzABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwqyz';
        $seeds['numeric'] = '01234567890123456789012345678901234567890123456789';
        $seeds['alphanum'] = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwqyz0123456789abcdefghijklmnopqrstuvwqyz0123456789abcdefghijklmnopqrstuvwqyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $seeds['hexidec'] = '0123456789abcdef';

        if (isset($seeds[$type])) {
            $keyspace = $seeds[$type];
        }

        $pieces = [];
        $max = mb_strlen($keyspace, '8bit') - 1;
        for ($i = 0; $i < $length; ++$i) {
            $pieces[] = $keyspace[random_int(0, $max)];
        }
        return implode('', $pieces);
    }

    public function fetchAuthToken($input)
    {
        $response['status'] = false;
        if (isset($input['authToken']) && !empty($input['authToken'])) {
            $queryParams = array($input['authToken']);
            $response['data'] = $this->db->rawQueryOne("SELECT user_id,user_name,phone_number,login_id,status FROM user_details as ud WHERE ud.api_token = ?", $queryParams);
            if($response['data']){
                $response['status'] = true;
            }else{
                $response['message'] = "Please check your credentials and try to log in again";
            }
        }else{
            $response['status'] = false;
            $response['message'] = "Please give your credentials to continue";
        }
        return $response;
    }

    public function generateSelectOptions($options)
    {
        $i = 0;
        foreach($options as $key=>$show){
            $response[$i]['value'] = $key;
            $response[$i]['show'] = $show;
            $i++;
        }
        return $response;
    }
}
