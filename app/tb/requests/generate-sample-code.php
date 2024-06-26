<?php

use App\Services\TbService;
use App\Utilities\DateUtility;
use App\Registries\AppRegistry;
use App\Registries\ContainerRegistry;


/** @var TbService $tbService */
$tbService = ContainerRegistry::get(TbService::class);

// Sanitized values from $request object
/** @var Laminas\Diactoros\ServerRequest $request */
$request = AppRegistry::get('request');
$_POST = _sanitizeInput($request->getParsedBody());


$provinceCode = $_POST['provinceCode'] ?? $_POST['pName'] ?? null;
$sampleCollectionDate = $_POST['sampleCollectionDate'] ?? $_POST['sDate'] ?? null;
try {
    if (empty($sampleCollectionDate) || DateUtility::isDateValid($sampleCollectionDate) === false) {
        echo json_encode([]);
    } else {

        $sampleCodeParams = [];
        $sampleCodeParams['sampleCollectionDate'] = $sampleCollectionDate;
        $sampleCodeParams['provinceCode'] = $provinceCode;
        $sampleCodeParams['insertOperation'] = false;
        echo $tbService->getSampleCode($sampleCodeParams);
    }
} catch (Throwable $exception) {
    error_log("Error while generating Sample ID : " . $exception->getMessage());
}
