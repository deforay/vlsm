<?php

use App\Registries\ContainerRegistry;
use App\Services\CommonService;



/** @var MysqliDb $db */
$db = ContainerRegistry::get('db');

/** @var CommonService $general */
$general = ContainerRegistry::get(CommonService::class);
$tableName = "r_vl_test_failure_reasons";
$result = 0;
try {
    $status = array(
        'status'            => $_POST['status'],
        'updated_datetime'  =>  $db->now(),
    );
    $db = $db->where('failure_id', $_POST['id']);
    $result = $db->update($tableName, $status);
} catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}
echo $result;
