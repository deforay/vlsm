<?php
ob_start();
require_once('../startup.php'); 
require_once(APPLICATION_PATH.'/header.php');
require_once(APPLICATION_PATH.'/models/General.php');
//global config
$cSampleQuery="SELECT * FROM global_config";
$cSampleResult=$db->query($cSampleQuery);
$arr = array();
// now we create an associative array so that we can easily create view variables
for ($i = 0; $i < sizeof($cSampleResult); $i++) {
  $arr[$cSampleResult[$i]['name']] = $cSampleResult[$i]['value'];
}

$importedBy = $_SESSION['userId'];

$import_decided = (isset($arr['import_non_matching_sample']) && $arr['import_non_matching_sample'] == 'no')?'INNER JOIN':'LEFT JOIN';


$tQuery = "select module from temp_sample_import where imported_by ='" . $_SESSION['userId'] . "' limit 0,1";

$tResult = $db->rawQueryOne($tQuery);
$module = $tResult['module'];

$general = new General($db);

if($module == 'vl'){
  require_once('import-stats-vl.php');
}else if($module == 'eid'){
  require_once('import-stats-eid.php');
}



require_once(APPLICATION_PATH.'/footer.php');