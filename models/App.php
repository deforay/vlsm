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

    public function getHealthFacilities($testType = null, $user = null, $onlyActive = false, $facilityType = 1)
    {
        $facilityDb = new \Vlsm\Models\Facilities($this->db);
        $query = "SELECT hf.test_type, f.facility_id, f.facility_name, f.facility_code, f.facility_state, f.facility_district, f.facility_type 
                    from health_facilities AS hf 
                    INNER JOIN facility_details as f ON hf.facility_id=f.facility_id";
        $where = "";
        if (!empty($user)) {
            $facilityMap = $facilityDb->getFacilityMap($user);
            if (!empty($facilityMap)) {
                if(isset($where) && trim($where) != ""){
                    $where .= " AND ";
                } else{
                    $where .= " WHERE ";
                }
                $where .=" facility_id IN (" . $facilityMap . ")";
            }
        }

        if (!empty($testType)) {
            if(isset($where) && trim($where) != ""){
                $where .= " AND ";
            } else{
                $where .= " WHERE ";
            }
            $where .=" hf.test_type like '$testType'";
        }

        if ($onlyActive) {
            if(isset($where) && trim($where) != ""){
                $where .= " AND ";
            } else{
                $where .= " WHERE ";
            }
            $where .=" f.status like 'active'";
        }
        
        if ($facilityType) {
            if(isset($where) && trim($where) != ""){
                $where .= " AND ";
            } else{
                $where .= " WHERE ";
            }
            $where .=" f.facility_type = '$facilityType'";
        }
        $where .= 'ORDER BY facility_name ASC';
        $query .= $where;
        $result = $this->db->rawQuery($query);
        foreach($result as $key=>$row){
            $response[$key]['value'] = $row['facility_id'];
            $response[$key]['show'] = $row['facility_name'].' ('.$row['facility_code'].')';
            $response[$key]['state'] = $row['facility_state'];
            $response[$key]['province'] = $row['facility_district'];
        }
        return $response;
    }
}

