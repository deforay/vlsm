<?php


use App\Interop\Dhis2;
use App\Registries\ContainerRegistry;
use App\Services\CommonService;
use App\Services\DatabaseService;

$interopConfig = require(APPLICATION_PATH . '/../configs/config.interop.php');

$dhis2 = new Dhis2(
    $interopConfig['DHIS2']['url'],
    $interopConfig['DHIS2']['user'],
    $interopConfig['DHIS2']['password']
);


/** @var DatabaseService $db */
$db = ContainerRegistry::get(DatabaseService::class);

/** @var CommonService $general */
$general = ContainerRegistry::get(CommonService::class);

$instanceId = $general->getInstanceId();

$arr = $general->getGlobalConfig();
$sarr = $general->getSystemConfig();


$fileArray = array(
    1 => 'forms/init-southsudan.php'
);


require($fileArray[$arr['vl_form']]);
