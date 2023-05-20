<?php

use App\Registries\ContainerRegistry;
use App\Services\CommonService;
use App\Utilities\DateUtility;


// Sanitize values before using them below
$_POST = array_map('htmlspecialchars', $_POST);


/** @var MysqliDb $db */
$db = ContainerRegistry::get('db');

/** @var CommonService $general */
$general = ContainerRegistry::get(CommonService::class);

$tableName = "vl_contact_notes";

try {
    $result = '';
    if (isset($_POST['notes']) && trim($_POST['notes']) != "") {
        $data = array(
            'contact_notes' => $_POST['notes'],
            'treament_contact_id' => $_POST['treamentId'],
            'collected_on' => DateUtility::isoDateFormat($_POST['dateVal']),
            'added_on' => DateUtility::getCurrentDateTime()
        );
        //print_r($data);die;
        $result = $db->insert($tableName, $data);
    }
} catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}
echo $result;
