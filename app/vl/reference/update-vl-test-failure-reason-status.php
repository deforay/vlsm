<?php

use App\Registries\ContainerRegistry;
use App\Services\CommonService;



/** @var MysqliDb $db */
/** @var CommonService $general */
$general = \App\Registries\ContainerRegistry::get(CommonService::class);
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
