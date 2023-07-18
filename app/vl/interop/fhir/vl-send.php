<?php

use App\Registries\ContainerRegistry;
use App\Services\CommonService;

require_once(__DIR__ . "/../../../../bootstrap.php");

if (php_sapi_name() !== 'cli' && !isset($_SESSION['userId'])) {
    http_response_code(403);
    exit(0);
}

/** @var MysqliDb $db */
$db = ContainerRegistry::get('db');

/** @var CommonService $general */
$general = ContainerRegistry::get(CommonService::class);

$instanceId = $general->getInstanceId();

$arr = $general->getGlobalConfig();
$sarr = $general->getSystemConfig();


$fileArray = array(
    7 => 'forms/rwanda/send-rwanda.php'
);


require($fileArray[$arr['vl_form']]);
