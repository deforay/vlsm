<?php

require_once(__DIR__ . "/../../../../startup.php");

if (php_sapi_name() !== 'cli' && !isset($_SESSION['userId'])) {
    http_response_code(403);
    exit(0);
}

$general = new \App\Models\General();

$instanceResult = $db->rawQueryOne("SELECT vlsm_instance_id, instance_facility_name FROM s_vlsm_instance");
$instanceId = $instanceResult['vlsm_instance_id'];

$arr = $general->getGlobalConfig();
$sarr = $general->getSystemConfig();


$fileArray = array(
    7 => 'forms/rwanda/send-rwanda.php'
);


require($fileArray[$arr['vl_form']]);

