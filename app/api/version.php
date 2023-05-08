<?php

use App\Registries\ContainerRegistry;
use App\Services\CommonService;
use App\Utilities\DateUtility;

/** @var MysqliDb $db */
$db = ContainerRegistry::get('db');

/** @var CommonService $general */
$general = ContainerRegistry::get(CommonService::class);

session_unset(); // no need of session in json response
//header('Content-Type: application/json; charset=utf-8');
header('Content-Type: application/json');

if (!empty($_GET['labId']) && !empty($_GET['version'])) {
    $labId = (int) $_GET['labId'];
    $version = $_GET['version'];
    $sql = 'UPDATE facility_details
                SET facility_attributes = JSON_SET(COALESCE(facility_attributes, "{}"), "$.version", ?, "$.lastHeartBeat", ?)
                WHERE facility_id = ?';
    $db->rawQuery($sql, array($version, DateUtility::getCurrentDateTime(), $labId));
}

// return application Version
echo json_encode(['version' => VERSION]);
