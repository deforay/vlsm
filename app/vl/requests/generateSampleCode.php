<?php

use App\Services\VlService;
use App\Registries\AppRegistry;
use App\Utilities\LoggerUtility;
use App\Exceptions\SystemException;
use App\Registries\ContainerRegistry;

/** @var VlService $vlService */
$vlService = ContainerRegistry::get(VlService::class);

// Sanitized values from $request object
/** @var Laminas\Diactoros\ServerRequest $request */
$request = AppRegistry::get('request');
$_POST = _sanitizeInput($request->getParsedBody());

$provinceCode = $_POST['provinceCode'] ?? $_POST['pName'] ?? null;
$sampleCollectionDate = $_POST['sampleCollectionDate'] ?? $_POST['sDate'] ?? null;
try {
  if (empty($sampleCollectionDate)) {
    echo json_encode([]);
  } else {
    $sampleCodeParams = [];
    $sampleCodeParams['sampleCollectionDate'] = $sampleCollectionDate;
    $sampleCodeParams['provinceCode'] = $provinceCode;
    $sampleCodeParams['insertOperation'] = false;
    echo $vlService->getSampleCode($sampleCodeParams);
  }
} catch (Throwable $exception) {
  LoggerUtility::log('error', "Error while generating Sample Code : " . $exception->getMessage());
}
