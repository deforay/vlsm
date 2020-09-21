<?php

$general = new \Vlsm\Models\General($db);
$facilitiesDb = new \Vlsm\Models\Facilities($db);

$testingPoints = $facilitiesDb->getTestingPoints($labId);

$labId = !empty($_POST['labId']) ? $_POST['labId'] : null;
$selectedTestingPoint = !empty($_POST['selectedTestingPoint']) ? $_POST['selectedTestingPoint'] : null;
$response = "";

$testingPoints = $facilitiesDb->getTestingPoints($labId);
if (!empty($testingPoints)) {
  $response = $general->generateSelectOptions($testingPoints, $selectedTestingPoint, "-- Select --");
}

echo $response;
