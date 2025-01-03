<?php

use App\Registries\AppRegistry;
use App\Registries\ContainerRegistry;
use App\Services\CommonService;
use App\Services\DatabaseService;
use App\Utilities\DateUtility;




// Sanitized values from $request object
/** @var Laminas\Diactoros\ServerRequest $request */
$request = AppRegistry::get('request');
$_POST = _sanitizeInput($request->getParsedBody());

/** @var DatabaseService $db */
$db = ContainerRegistry::get(DatabaseService::class);

/** @var CommonService $general */
$general = ContainerRegistry::get(CommonService::class);
$tableName = "rejection_type";
$value = trim((string) $_POST['value']);
$data = 0;
if ($value != '') {
    $rej = "SELECT * FROM rejection_type WHERE rejection_type = ? ";
    $rejInfo = $db->rawQuery($rej, [$value]);

    if (empty($rejInfo)) {
        $data = array(
            'rejection_type' => $value,
            'updated_datetime' => DateUtility::getCurrentDateTime(),
        );

        $db->insert($tableName, $data);
        $lastId = $db->getInsertId();
    }
}

if ($data > 0) {
    $data = '1';
} else {
    $data = '0';
}
echo $data;
