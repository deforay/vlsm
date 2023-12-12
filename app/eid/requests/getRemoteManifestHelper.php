<?php

use App\Registries\AppRegistry;
use App\Services\CommonService;
use App\Services\DatabaseService;
use App\Registries\ContainerRegistry;

/** @var DatabaseService $db */
$db = ContainerRegistry::get(DatabaseService::class);

/** @var CommonService $general */
$general = ContainerRegistry::get(CommonService::class);

// Sanitized values from $request object
/** @var Laminas\Diactoros\ServerRequest $request */
$request = AppRegistry::get('request');
$_POST = $request->getParsedBody();

$sampleData = [];
$sampleQuery = 'SELECT eid_id FROM form_eid
                WHERE sample_code IS NULL AND
                (sample_package_code LIKE ? OR remote_sample_code LIKE ?)';
$sampleResult = $db->rawQuery($sampleQuery, [$_POST['samplePackageCode'], $_POST['samplePackageCode']]);
$sampleData = array_column($sampleResult, 'eid_id');
echo implode(',', $sampleData);
