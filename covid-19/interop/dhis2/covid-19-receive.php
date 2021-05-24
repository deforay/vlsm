<?php

require_once(APPLICATION_PATH . '/configs/config.interop.php');

$general = new \Vlsm\Models\General($db);
$arr = $general->getGlobalConfig();

$dhis2 = new \Vlsm\Interop\Dhis2(DHIS2_URL, DHIS2_USER, DHIS2_PASSWORD);

$instanceResult = $db->rawQueryOne("SELECT vlsm_instance_id, instance_facility_name FROM s_vlsm_instance");
$instanceId = $instanceResult['vlsm_instance_id'];

$fileArray = array(
    1 => 'forms/receive-southsudan.php'
);

if (file_exists($fileArray[$arr['vl_form']])) {
    require_once($fileArray[$arr['vl_form']]);
}
