<?php

use App\Services\UsersService;
use App\Utilities\MiscUtility;
use App\Registries\AppRegistry;
use App\Services\CommonService;
use App\Services\DatabaseService;
use App\Exceptions\SystemException;
use App\Registries\ContainerRegistry;


/** @var Slim\Psr7\Request $request */
$request = AppRegistry::get('request');


$input = $request->getParsedBody();

/** @var DatabaseService $db */
$db = ContainerRegistry::get(DatabaseService::class);

/** @var CommonService $general */
$general = ContainerRegistry::get(CommonService::class);

/** @var UsersService $usersService */
$usersService = ContainerRegistry::get(UsersService::class);

$transactionId = MiscUtility::generateUUID();

try {
    if (!empty($input['userName']) && !empty($input['password'])) {
        $userQuery = "SELECT ud.user_id,
                        ud.user_name,
                        ud.email,
                        ud.phone_number,
                        ud.login_id,
                        ud.status,
                        ud.password,
                        ud.api_token,
                        r.role_id,
                        r.role_name,
                        r.role_code,
                        r.access_type,
                        r.landing_page,
                        (CASE WHEN (r.access_type = 'testing-lab') THEN 'yes' ELSE 'no' END) as testing_user
                        FROM user_details as ud
                        INNER JOIN roles as r ON ud.role_id=r.role_id
                        WHERE IFNULL(ud.app_access, 'no') = 'yes'
                        AND ud.status = 'active'
                        AND ud.login_id = ?";
        $userResult = $db->rawQueryOne($userQuery, [$input['userName']]);

        if (empty($userResult) || !password_verify((string) $input['password'], (string) $userResult['password'])) {
            throw new SystemException('Login failed. Please contact system administrator.');
        }

        // Not needed anymore in the following code
        unset($userResult['password']);

        $tokenData = $usersService->getAuthToken($userResult['api_token'], $userResult['user_id']);

        if (empty($tokenData)) {
            throw new SystemException('Authentication failed. Please contact system administrator.');
        }


        $data = [];

        $data['user'] = $userResult;
        $data['form'] = (int) $general->getGlobalConfig('vl_form');
        $data['api_token'] = $tokenData['token'];
        $data['new_token'] = $tokenData['token_updated'];
        $data['appMenuName'] = $general->getGlobalConfig('app_menu_name');
        $data['access'] = $usersService->getUserRolePrivileges($userResult['user_id']);

        $payload = [
            'status' => 1,
            'message' => 'Login Success',
            'timestamp' => time(),
            'transactionId' => $transactionId,
            'data' => $data
        ];
    } else {
        throw new SystemException('Login failed. Please contact system administrator.');
    }
} catch (Throwable $exc) {
    http_response_code(500);
    $payload = [
        'status' => 2,
        'message' => 'Login failed. Please contact system administrator.',
        'timestamp' => time(),
        'transactionId' => $transactionId
    ];
    error_log($exc->getMessage());
}
$payload = json_encode($payload);

$trackId = $general->addApiTracking($transactionId, $data['user']['user_id'], 1, 'login', 'common', $_SERVER['REQUEST_URI'], $input, $payload, 'json');

echo $payload;
