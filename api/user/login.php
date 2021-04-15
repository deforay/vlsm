<?php
header('Content-Type: application/json');

$general = new \Vlsm\Models\General($db);
$app = new \Vlsm\Models\App($db);

$input = json_decode(file_get_contents("php://input"),true);
try {
    if (isset($input['userName']) && !empty($input['userName']) && isset($input['password']) && !empty($input['password'])) {
        
        $username = $db->escape($input['userName']);
        $password = $db->escape($input['password']);
        // $systemConfig['passwordSalt']='PUT-A-RANDOM-STRING-HERE';
        $password = sha1($password . $systemConfig['passwordSalt']);
        $queryParams = array($username, $password);
        $admin = $db->rawQueryOne("SELECT user_name,phone_number,status FROM user_details as ud WHERE ud.login_id = ? AND ud.password = ?", $queryParams);
        // print_r($admin);die;
        if (count($admin) > 0) {
            if($admin['status'] != 'active'){
                $payload = array(
                    'status' => 2,
                    'message'=>'You are not activated. Kindly contact administrator.',
                    'timestamp' => $general->getDateTime()
                );
            }else{
                $randomString = $general->generateUserID();
                
                $userData['api_token'] = $randomString;
                $userData['api_token_generated_datetime'] = $general->getDateTime();
                $db = $db->where('user_id', $admin[0]['user_id']);
                $upId = $db->update('user_details', $userData);
                $data = array();
                $configFormQuery = "SELECT * FROM global_config WHERE name ='vl_form'";
                $configFormResult = $db->rawQuery($configFormQuery);
                $data['user'] = $admin;
                $data['form'] = $configFormResult[0]['value'];
                $data['api_token'] = $randomString;
                // print_r($data);die;
                $payload = array(
                    'status' => 1,
                    'message'=>'Login Success',
                    'data' => $data,
                    'timestamp' => $general->getDateTime()
                );
            }
        } else {
            $payload = array(
                'status' => 2,
                'message'=>'Please check your login credentials',
                'timestamp' => $general->getDateTime()
            );
        }
    } else {
        $payload = array(
            'status' => 0,
            'message'=>'Please enter the credentials',
            'timestamp' => $general->getDateTime()
        );
    }
    

    echo json_encode($payload);
    exit(0);
} catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
    exit(0);
}
