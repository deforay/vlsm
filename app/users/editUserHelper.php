<?php

use GuzzleHttp\Client;
use App\Services\UsersService;
use App\Utilities\DateUtility;
use App\Services\CommonService;
use App\Registries\ContainerRegistry;
use App\Utilities\ImageResizeUtility;
use App\Utilities\MiscUtility;

/** @var UsersService $usersService */
$usersService = ContainerRegistry::get(UsersService::class);

/** @var MysqliDb $db */
$db = ContainerRegistry::get('db');

/** @var CommonService $general */
$general = ContainerRegistry::get(CommonService::class);

// Sanitized values from $request object
/** @var Laminas\Diactoros\ServerRequest $request */
$request = $GLOBALS['request'];
$_POST = $request->getParsedBody();

$uploadedFiles = $request->getUploadedFiles();


$userId = base64_decode($_POST['userId']);

$userInfo = $usersService->getUserInfo($userId);

$signatureImagePath = UPLOAD_PATH . DIRECTORY_SEPARATOR . "users-signature";
MiscUtility::makeDirectory($signatureImagePath);
$signatureImagePath = realpath($signatureImagePath);

$signatureImage = null;

try {
    if (trim($_POST['userName']) != '' && trim($_POST['loginId']) != '' && ($_POST['role']) != '') {

        $data = array(
            'user_name'             => $_POST['userName'],
            'interface_user_name'   => (!empty($_POST['interfaceUserName']) && $_POST['interfaceUserName'] != "") ? json_encode(array_map('trim', explode(",", $_POST['interfaceUserName']))) : null,
            'email'                 => $_POST['email'],
            'phone_number'          => $_POST['phoneNo'],
            'login_id'              => $_POST['loginId'],
            'role_id'               => $_POST['role'],
            'status'                => $_POST['status'],
            'app_access'            => $_POST['appAccessable']
        );
        if (!empty($_POST['authToken'])) {
            $data['api_token'] = $_POST['authToken'];
            $data['api_token_generated_datetime'] = DateUtility::getCurrentDateTime();
        } elseif (!empty($_POST['appAccessable']) && $_POST['appAccessable'] == 'yes') {
            $data['api_token'] = $usersService->generateAuthToken();
            $data['api_token_generated_datetime'] = DateUtility::getCurrentDateTime();
        }
        if (isset($_POST['removedSignatureImage']) && trim($_POST['removedSignatureImage']) != "") {
            $fImagePath = $signatureImagePath . DIRECTORY_SEPARATOR . $_POST['removedSignatureImage'];
            if (!empty($fImagePath) && file_exists($fImagePath)) {
                unlink($fImagePath);
            }
            $data['user_signature'] = null;
        }


        if (isset($uploadedFiles['userSignature']) && $uploadedFiles['userSignature']->getError() === UPLOAD_ERR_OK) {
            $file = $uploadedFiles['userSignature'];
            $fileName = $file->getClientFilename();
            $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
            $tmpFilePath = $file->getStream()->getMetadata('uri');
            $fileSize = $file->getSize();
            $fileMimeType = $file->getClientMediaType();
            $signatureImage = "usign-" . $userId . "." . $fileExtension;
            $signatureImage = $signatureImagePath . DIRECTORY_SEPARATOR . $signatureImage;
            $file->moveTo($signatureImage);

            $resizeObj = new ImageResizeUtility();
            $resizeObj = $resizeObj->setFileName($signatureImage);
            $resizeObj->resizeToWidth(250);
            $resizeObj->save($signatureImage);
            $data['user_signature'] = $signatureImage;
        } else {
            $signatureImage = $userInfo['user_signature'] ?? null;
        }

        if (isset($_POST['password']) && trim($_POST['password']) != "") {

            /* Recency cross login block */
            if (SYSTEM_CONFIG['recency']['crosslogin'] && !empty(SYSTEM_CONFIG['recency']['url'])) {
                $client = new Client(['http_version' => 2.0]);
                $url = rtrim(SYSTEM_CONFIG['recency']['url'], "/");
                $newCrossLoginPassword = CommonService::encrypt($_POST['password'], base64_decode(SYSTEM_CONFIG['recency']['crossloginSalt']));
                $result = $client->post($url . '/api/update-password', [
                    'form_params' => [
                        'u' => $_POST['loginId'],
                        't' => $newCrossLoginPassword
                    ]
                ]);
                $response = json_decode($result->getBody()->getContents());
                if ($response->status == 'fail') {
                    error_log('Recency profile not updated! for the user ' . $_POST['userName']);
                }
            }

            $password = $usersService->passwordHash($_POST['password']);
            $data['password'] = $password;
            $data['hash_algorithm'] = 'phb';
            $data['force_password_reset'] = 1;
        }

        $db = $db->where('user_id', $userId);
        $db->update("user_details", $data);

        // Deleting old mapping of user to facilities
        $db = $db->where('user_id', $userId);
        $delId = $db->delete("user_facility_map");

        if ($userId != '' && trim($_POST['selectedFacility']) != '') {
            $selectedFacility = explode(",", $_POST['selectedFacility']);
            $uniqueFacilityId = array_unique($selectedFacility);
            for ($j = 0; $j <= count($uniqueFacilityId); $j++) {
                if (isset($uniqueFacilityId[$j])) {
                    $data = array(
                        'facility_id' => $uniqueFacilityId[$j],
                        'user_id' => $userId,
                    );
                    $db->insert("user_facility_map", $data);
                }
            }
        }
        $_SESSION['alertMsg'] = _translate("User updated successfully");

        $systemType = $general->getSystemConfig('sc_user_type');
        if (!empty(SYSTEM_CONFIG['remoteURL']) && $systemType == 'vluser') {
            $_POST['userId'] = $userId;
            $_POST['loginId'] = null;
            $_POST['password'] = null;
            $_POST['hashAlgorithm'] = 'phb';
            $_POST['role'] = 0;
            $_POST['status'] = 'inactive';
            $_POST['userId'] = base64_encode($userId);
            $apiUrl = SYSTEM_CONFIG['remoteURL'] . "/api/v1.1/user/save-user-profile.php";

            $multipart = [
                [
                    'name' => 'post',
                    'contents' => json_encode($_POST)
                ],
                [
                    'name' => 'x-api-key',
                    'contents' => $general->generateRandomString(18)
                ]
            ];

            if (!empty($signatureImage) && MiscUtility::imageExists($signatureImage)) {
                $multipart[] = [
                    'name' => 'sign',
                    'contents' => fopen($signatureImage, 'r')
                ];
            }

            $client = new Client();
            try {
                $response = $client->post($apiUrl, [
                    'multipart' => $multipart
                ]);

                $result = $response->getBody()->getContents();
                $deResult = json_decode($result, true);
            } catch (\Exception $e) {
                // Handle the exception
                echo "Error: " . $e->getMessage();
                echo ($e->getTraceAsString());
            }
        }
    }


    //Add event log
    $eventType = 'user-update';
    $action = $_SESSION['userName'] . ' updated details for user ' . $_POST['userName'];
    $resource = 'user';

    $general->activityLog($eventType, $action, $resource);

    header("Location:users.php");
} catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}
