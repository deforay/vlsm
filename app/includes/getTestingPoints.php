<?php

use App\Services\FacilitiesService;
use App\Registries\ContainerRegistry;
use App\Services\CommonService;

/** @var MysqliDb $db */
$db = ContainerRegistry::get('db');

/** @var CommonService $general */
$general = ContainerRegistry::get(CommonService::class);

/** @var FacilitiesService $facilitiesService */
$facilitiesService = ContainerRegistry::get(FacilitiesService::class);

// Sanitize values before using them below
$_POST = array_map('htmlspecialchars', $_POST);

$labId = !empty($_POST['labId']) ? $_POST['labId'] : null;
$selectedTestingPoint = !empty($_POST['selectedTestingPoint']) ? $_POST['selectedTestingPoint'] : null;
$response = "";

$testingPoints = $facilitiesService->getTestingPoints($labId);
/* Set index as value for testing point JSON */
$testingPointsList = [];
if (isset($testingPoints) && !empty($testingPoints)) {
  foreach ($testingPoints as $val) {
    $testingPointsList[$val] = $val;
  }
}
if (!empty($testingPointsList)) {
  $response = $general->generateSelectOptions($testingPointsList, $selectedTestingPoint, "-- Select --");
}

echo $response;
