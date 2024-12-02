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
$_POST = _sanitizeInput($request->getParsedBody());

$sampleCode = $_POST['samplePackageCode'];

// Query to fetch sample data and number of samples
$sampleQuery = "SELECT tb.tb_id,
                COALESCE(JSON_EXTRACT(tb.form_attributes, '$.manifest.number_of_samples'), 0) AS number_of_samples
                FROM form_tb AS tb
                WHERE tb.sample_package_code IN (?,
                        (SELECT DISTINCT sample_package_code FROM form_tb WHERE remote_sample_code LIKE ?)
                        ) ORDER BY request_created_datetime	DESC";

$sampleResult = $db->rawQuery($sampleQuery, [$sampleCode, $sampleCode]);

// Extract sample IDs and number of samples
$sampleData = array_column($sampleResult, 'tb_id');
$noOfSamples = isset($sampleResult[0]['number_of_samples']) ? (int)$sampleResult[0]['number_of_samples'] : 0;

$count = count($sampleData);
if ($noOfSamples > 0 && $count === $noOfSamples) {
    echo implode(',', $sampleData);
}