<?php

use App\Exceptions\SystemException;
use App\Registries\AppRegistry;
use App\Registries\ContainerRegistry;
use App\Services\GenericTestsService;

/** @var GenericTestsService $genericTestsService */
$genericTestsService = ContainerRegistry::get(GenericTestsService::class);

// Sanitized values from $request object
/** @var Laminas\Diactoros\ServerRequest $request */
$request = AppRegistry::get('request');
$_POST = $request->getParsedBody();

$provinceCode = $_POST['pName'] ?? $_POST['provinceCode'] ?? null;
$sampleCollectionDate = $_POST['sampleCollectionDate'] ?? $_POST['sDate'] ?? null;
$testType = $_POST['testType'] ?? null;

try {
    if (empty($sampleCollectionDate) || empty($testType)) {
        echo json_encode([]);
    } else {
        $sampleCodeParams = [];
        $sampleCodeParams['sampleCollectionDate'] = $sampleCollectionDate;
        $sampleCodeParams['provinceCode'] = $provinceCode;
        $sampleCodeParams['testType'] = $testType;
        $sampleCodeParams['insertOperation'] = false;
        echo $genericTestsService->getSampleCode($sampleCodeParams);
    }
} catch (Exception | SystemException $exception) {
    error_log("Error while generating Sample Code : " . $exception->getMessage());
}
