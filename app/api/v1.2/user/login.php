<?php
session_unset(); // no need of session in json response
header('Content-Type: application/json');

$general = new \Vlsm\Models\General();
$users = new \Vlsm\Models\Users();
$app = new \Vlsm\Models\App();

$vlsmSystemConfig = $general->getSystemConfig();

$input = json_decode(file_get_contents("php://input"), true);
try {
    if (isset($input['userName']) && !empty($input['userName']) && isset($input['password']) && !empty($input['password'])) {

        /* Check hash login id exist */
        $hashCheckQuery = "SELECT `user_id`, `login_id`, `hash_algorithm`, `password` FROM user_details WHERE `login_id` = ?";
        $hashCheck = $db->rawQueryOne($hashCheckQuery, array($db->escape($input['userName'])));

        $username = $db->escape($input['userName']);
        $password = $db->escape($input['password']);

        $queryParams = array($username);
        $userResult = $db->rawQueryOne(
            "SELECT ud.user_id, ud.user_name, ud.email, ud.phone_number, ud.login_id, ud.status, ud.app_access, ud.password, ud.hash_algorithm, r.*, 
                                        (CASE WHEN (r.access_type = 'testing-lab') THEN 'yes' ELSE 'no' END) as testing_user 
                                        FROM user_details as ud 
                                        INNER JOIN roles as r ON ud.role_id=r.role_id 
                                        WHERE ud.login_id = ?",
            $queryParams
        );

        if ($userResult['testing_user'] == 'yes') {
            $remoteUser = "yes";
        } else {
            $remoteUser = "no";
        }
        if (count($userResult) > 0) {
            /* Update Phb hash password */


            if ($userResult['hash_algorithm'] == 'sha1') {
                $password = sha1($input['password'] . SYSTEM_CONFIG['passwordSalt']);
                if ($password == $userResult['password']) {
                    $passwordCheck = true;
                    $newPassword = $users->passwordHash($input['password']);
                    $db->where('user_id', $userResult['user_id']);
                    $db->update(
                        'user_details',
                        array(
                            'password' => $newPassword,
                            'hash_algorithm' => 'phb'
                        )
                    );
                } else {
                    $passwordCheck = false;
                }
            } else if ($userResult['hash_algorithm'] == 'phb') {
                if (!password_verify($input['password'], $userResult['password'])) {
                    $passwordCheck = false;
                } else {
                    $passwordCheck = true;
                }
            }


            if ($userResult['status'] != 'active' || $passwordCheck == false) {
                $payload = array(
                    'status' => 2,
                    'message' => 'Login failed. Please contact system administrator.',
                    'timestamp' => time(),
                );
            } else if (isset($userResult['app_access']) && $userResult['app_access'] == "no") {
                $payload = array(
                    'status' => 2,
                    'message' => 'Login failed. Please contact system administrator.',
                    'timestamp' => time(),
                );
            } else {
                $randomString = base64_encode($result['user_id'] . "-" . $general->generateToken(3));

                $userData['api_token'] = $randomString;
                $userData['api_token_generated_datetime'] = $general->getDateTime();
                $db = $db->where('user_id', $userResult['user_id']);
                $upId = $db->update('user_details', $userData);
                if ($upId) {
                    $data = array();

                    $data['user'] = $userResult;
                    $data['form'] = $general->getGlobalConfig('vl_form');
                    $data['api_token'] = $randomString;
                    $data['appMenuName'] = $general->getGlobalConfig('app_menu_name');
                    $data['access'] = $users->getUserRolePrivileges($userResult['user_id']);
                    // print_r($data);die;
                    unset($data['hash_algorithm']);
                    unset($data['password']);
                    $payload = array(
                        'status' => 1,
                        'message' => 'Login Success',
                        'data' => $data,
                        'timestamp' => time(),
                    );
                } else {
                    $payload = array(
                        'status' => 2,
                        'message' => 'Someting went wrong. Please try again later.',
                        'timestamp' => time(),
                    );
                }
            }
        } else {
            $payload = array(
                'status' => 2,
                'message' => 'Login failed. Please contact system administrator.',
                'timestamp' => time(),
            );
        }
    } else {
        $payload = array(
            'status' => 0,
            'message' => 'Please enter valid credentials',
            'timestamp' => time(),
        );
    }


    echo json_encode($payload);
    exit(0);
} catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
    exit(0);
}