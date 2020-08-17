<?php

header('Content-Type: application/json');

include_once(APPLICATION_PATH . "/includes/MysqliDb.php");
include_once(APPLICATION_PATH . '/models/General.php');
include_once(APPLICATION_PATH . "/vendor/autoload.php");

echo "<pre>";print_r($_POST);die;
session_unset(); // no need of session in json response
$general = new General($db);

$serialNo = isset($_POST['s']) && !empty($_POST['s']) ? $_POST['s'] : null;
$apiKey = isset($_POST['x-api-key']) && !empty($_POST['x-api-key']) ? $_POST['x-api-key'] : null;

if (!$apiKey) {
    $response = array(
        'status' => 'failed',
        'data' => 'API Key invalid',
        'timestamp' => $general->getDateTime()
    );
    echo json_encode($response);
    exit(0);
}

if (!$serialNo) {
    $response = array(
        'status' => 'failed',
        'data' => 'Serial Number missing in request',
        'timestamp' => $general->getDateTime()
    );
    echo json_encode($response);
    exit(0);
}

try {
    $sQuery = "";
    $aRow = $db->rawQueryOne($sQuery);

    if (isset($_FILES['userSignature']['name']) && $_FILES['userSignature']['name'] != "") {
        if (!file_exists(UPLOAD_PATH . DIRECTORY_SEPARATOR . "users-signature") && !is_dir(UPLOAD_PATH . DIRECTORY_SEPARATOR . "users-signature")) {
            mkdir(UPLOAD_PATH . DIRECTORY_SEPARATOR . "users-signature");
        }
        $extension = strtolower(pathinfo(UPLOAD_PATH . DIRECTORY_SEPARATOR . $_FILES['userSignature']['name'], PATHINFO_EXTENSION));
        $string = $general->generateRandomString(10) . ".";
        $imageName = "usign-" . $string . $extension;

        $signatureImagePath = UPLOAD_PATH . DIRECTORY_SEPARATOR . "users-signature" . DIRECTORY_SEPARATOR . $imageName;
        if (move_uploaded_file($_FILES["userSignature"]["tmp_name"], $signatureImagePath)) {
            $resizeObj = new ImageResize($signatureImagePath);
            $resizeObj->resizeImage(100, 100, 'auto');
            $resizeObj->saveImage($signatureImagePath, 100);
            $data['user_signature'] = $imageName;
        }
    }

    $password = sha1($_POST['password'] . $systemConfig['passwordSalt']);
    $idOne = $general->generateRandomString(8);
    $idTwo = $general->generateRandomString(4);
    $idThree = $general->generateRandomString(4);
    $idFour = $general->generateRandomString(4);
    $idFive = $general->generateRandomString(12);
    $data = array(
        'user_id' => $idOne . "-" . $idTwo . "-" . $idThree . "-" . $idFour . "-" . $idFive,
        'user_name' => $_POST['userName'],
        'email' => $_POST['email'],
        'login_id' => $_POST['loginId'],
        'phone_number' => $_POST['phoneNo'],
        'password' => $password,
        'role_id' => $_POST['role'],
        'status' => 'active',
    );
    $data['user_signature'] = $imageName;
    $id = $db->insert($tableName, $data);
    
    $payload = array(
        'status' => 'success',
        'data' => $row,
        'timestamp' => $general->getDateTime()
    );

    echo json_encode($payload);
    exit(0);
} catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
    exit(0);
}