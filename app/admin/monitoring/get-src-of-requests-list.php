<?php

use App\Registries\ContainerRegistry;
use App\Services\CommonService;
use App\Services\DatabaseService;

/** @var DatabaseService $db */
$db = ContainerRegistry::get(DatabaseService::class);

/** @var CommonService $general */
$general = ContainerRegistry::get(CommonService::class);


$sources = array(
    'vlsm' => 'VLSM',
    'vlsts' => 'STS',
    'app' => 'Tablet',
    'api' => 'API',
    'dhis2' => 'DHIS2'
);


$table = "form_vl";
$testType = 'vl';

if (!empty($_POST['testType'])) {
    $testType = $_POST['testType'];
}

if (isset($testType) && $testType == 'vl') {
    $table = "form_vl";
}
if (isset($testType) && $testType == 'eid') {
    $table = "form_eid";
}
if (isset($testType) && $testType == 'covid19') {
    $table = "form_covid19";
}
if (isset($testType) && $testType == 'hepatitis') {
    $table = "form_hepatitis";
}
if (isset($testType) && $testType == 'tb') {
    $table = "form_tb";
}
$sQuery = "SELECT DISTINCT source_of_request from $table WHERE source_of_request is not null and source_of_request not like '' ORDER BY source_of_request";
$result = $db->rawQuery($sQuery);
$option = "<option value=''>--All--</option>";
foreach ($result as $row) {
    if (!empty($row['source_of_request'])) {
        $displayText = (!empty($sources[$row['source_of_request']])) ? $sources[$row['source_of_request']] : strtoupper((string) $row['source_of_request']);
        $option .= "<option value='" . $row['source_of_request'] . "'>" . $displayText . "</option>";
    }
}
echo $option;
