<?php



$formConfigQuery = "SELECT * FROM global_config";
$configResult = $db->query($formConfigQuery);
$gconfig = array();
// now we create an associative array so that we can easily create view variables
for ($i = 0; $i < sizeof($configResult); $i++) {
     $gconfig[$configResult[$i]['name']] = $configResult[$i]['value'];
}
//system config
$systemConfigQuery = "SELECT * FROM system_config";
$systemConfigResult = $db->query($systemConfigQuery);
$sarr = array();
// now we create an associative array so that we can easily create view variables
for ($i = 0; $i < sizeof($systemConfigResult); $i++) {
     $sarr[$systemConfigResult[$i]['name']] = $systemConfigResult[$i]['value'];
}

$general = new \Vlsm\Models\General();
$covid19Results = $general->getCovid19Results();
$tableName = "form_tb";
$primaryKey = "tb_id";

/* Array of database columns which should be read and sent back to DataTables. Use a space where
* you want to insert a non-database field (for example a counter or static image)
*/
$sampleCode = 'sample_code';
$aColumns = array('vl.sample_code', 'vl.remote_sample_code', "DATE_FORMAT(vl.sample_collection_date,'%d-%b-%Y')", 'b.batch_code', 'vl.patient_id', 'CONCAT(vl.patient_name, vl.patient_surname)', 'f.facility_name', 'f.facility_state', 'f.facility_district',  'vl.result', "DATE_FORMAT(vl.last_modified_datetime,'%d-%b-%Y %H:%i:%s')", 'ts.status_name');
$orderColumns = array('vl.sample_code', 'vl.last_modified_datetime', 'vl.sample_collection_date', 'b.batch_code', 'vl.patient_id', 'vl.patient_name', 'f.facility_name', 'f.facility_state', 'f.facility_district',  'vl.result', 'vl.last_modified_datetime', 'ts.status_name');
if ($_SESSION['instanceType'] == 'remoteuser') {
     $sampleCode = 'remote_sample_code';
} else if ($sarr['sc_user_type'] == 'standalone') {
     $aColumns = array('vl.sample_code', "DATE_FORMAT(vl.sample_collection_date,'%d-%b-%Y')", 'b.batch_code', 'vl.patient_id', 'CONCAT(vl.patient_name, vl.patient_surname)', 'f.facility_name', 'f.facility_state', 'f.facility_district',  'vl.result', "DATE_FORMAT(vl.last_modified_datetime,'%d-%b-%Y %H:%i:%s')", 'ts.status_name');
     $orderColumns = array('vl.last_modified_datetime', 'vl.sample_collection_date', 'b.batch_code', 'vl.patient_id', 'vl.patient_name', 'f.facility_name', 'f.facility_state', 'f.facility_district', 'vl.result', 'vl.last_modified_datetime', 'ts.status_name');
}


/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = $primaryKey;

$sTable = $tableName;
/*
* Paging
*/
$sLimit = "";
if (isset($_POST['iDisplayStart']) && $_POST['iDisplayLength'] != '-1') {
     $sOffset = $_POST['iDisplayStart'];
     $sLimit = $_POST['iDisplayLength'];
}

/*
* Ordering
*/

$sOrder = "";
if (isset($_POST['iSortCol_0'])) {
     $sOrder = "";
     for ($i = 0; $i < intval($_POST['iSortingCols']); $i++) {
          if ($_POST['bSortable_' . intval($_POST['iSortCol_' . $i])] == "true") {
               $sOrder .= $orderColumns[intval($_POST['iSortCol_' . $i])] . "
               " . ($_POST['sSortDir_' . $i]) . ", ";
          }
     }
     $sOrder = substr_replace($sOrder, "", -2);
}

/*
* Filtering
* NOTE this does not match the built-in DataTables filtering which does it
* word by word on any field. It's possible to do here, but concerned about efficiency
* on very large tables, and MySQL's regex functionality is very limited
*/

$sWhere = "";
if (isset($_POST['sSearch']) && $_POST['sSearch'] != "") {
     $searchArray = explode(" ", $_POST['sSearch']);
     $sWhereSub = "";
     foreach ($searchArray as $search) {
          if ($sWhereSub == "") {
               $sWhereSub .= "(";
          } else {
               $sWhereSub .= " AND (";
          }
          $colSize = count($aColumns);

          for ($i = 0; $i < $colSize; $i++) {
               if ($i < $colSize - 1) {
                    $sWhereSub .= $aColumns[$i] . " LIKE '%" . ($search) . "%' OR ";
               } else {
                    $sWhereSub .= $aColumns[$i] . " LIKE '%" . ($search) . "%' ";
               }
          }
          $sWhereSub .= ")";
     }
     $sWhere .= $sWhereSub;
}

/* Individual column filtering */
for ($i = 0; $i < count($aColumns); $i++) {
     if (isset($_POST['bSearchable_' . $i]) && $_POST['bSearchable_' . $i] == "true" && $_POST['sSearch_' . $i] != '') {
          if ($sWhere == "") {
               $sWhere .= $aColumns[$i] . " LIKE '%" . ($_POST['sSearch_' . $i]) . "%' ";
          } else {
               $sWhere .= " AND " . $aColumns[$i] . " LIKE '%" . ($_POST['sSearch_' . $i]) . "%' ";
          }
     }
}

/*
* SQL queries
* Get data to display
*/
$aWhere = '';
$sQuery = "SELECT * FROM form_tb as vl    
                    LEFT JOIN facility_details as f ON vl.facility_id=f.facility_id 
                    INNER JOIN r_sample_status as ts ON ts.status_id=vl.result_status 
                    LEFT JOIN batch_details as b ON b.batch_id=vl.sample_batch_id";

if (isset($sWhere) && $sWhere != "") {
     $sWhere = ' where ' . $sWhere;
     if (isset($_POST['samplePackageCode']) && $_POST['samplePackageCode'] != '') {
          $sWhere = $sWhere . ' AND vl.sample_package_code LIKE "%' . $_POST['samplePackageCode'] . '%" OR remote_sample_code LIKE "' . $_POST['samplePackageCode'] . '"';
     }
} else {
     if (isset($_POST['samplePackageCode']) && trim($_POST['samplePackageCode']) != '') {
          $sWhere = ' where ' . $sWhere;
          $sWhere = $sWhere . ' vl.sample_package_code LIKE "%' . $_POST['samplePackageCode'] . '%" OR remote_sample_code LIKE "' . $_POST['samplePackageCode'] . '"';
     }
}
/* if ($sWhere != '') {
     $sWhere = $sWhere . ' AND '. 'vl.vlsm_country_id="' . $gconfig['vl_form'] . '"';
} else {
     $sWhere = $sWhere . ' where '. 'vl.vlsm_country_id="' . $gconfig['vl_form'] . '"';
} */
$sFilter = '';
/* if ($_SESSION['instanceType'] == 'remoteuser') {
     $userfacilityMapQuery = "SELECT GROUP_CONCAT(DISTINCT facility_id ORDER BY facility_id SEPARATOR ',') as facility_id FROM vl_user_facility_map where user_id='" . $_SESSION['userId'] . "'";
     $userfacilityMapresult = $db->rawQuery($userfacilityMapQuery);
     if ($userfacilityMapresult[0]['facility_id'] != null && $userfacilityMapresult[0]['facility_id'] != '') {
          $sWhere = $sWhere . " AND vl.facility_id IN (" . $userfacilityMapresult[0]['facility_id'] . ")  ";
          $sFilter = " AND vl.facility_id IN (" . $userfacilityMapresult[0]['facility_id'] . ") ";
     }
} else {
     $sWhere = $sWhere . ' AND vl.result_status!=9';
     $sFilter = ' AND result_status!=9';
} */
$sQuery = $sQuery . ' ' . $sWhere;
if (isset($sOrder) && $sOrder != "") {
     $sOrder = preg_replace('/(\v|\s)+/', ' ', $sOrder);
     $sQuery = $sQuery . " ORDER BY " . $sOrder;
}
if (isset($sLimit) && isset($sOffset)) {
     $sQuery = $sQuery . ' LIMIT ' . $sOffset . ',' . $sLimit;
}
$rResult = $db->rawQuery($sQuery);
/* Data set length after filtering */
$aResultFilterTotal = $db->rawQuery("SELECT vl.tb_id,vl.facility_id,vl.patient_name,vl.result,f.facility_name,f.facility_code,vl.patient_id,vl.patient_name, vl.patient_surname,b.batch_code,vl.sample_batch_id,ts.status_name FROM form_tb as vl LEFT JOIN facility_details as f ON vl.facility_id=f.facility_id LEFT JOIN r_covid19_sample_type as s ON s.sample_id=vl.specimen_type INNER JOIN r_sample_status as ts ON ts.status_id=vl.result_status LEFT JOIN batch_details as b ON b.batch_id=vl.sample_batch_id $sWhere");
$iFilteredTotal = count($aResultFilterTotal);

/* Total data set length */
$aResultTotal =  $db->rawQuery("select COUNT(tb_id) as total FROM form_tb as vl where vlsm_country_id='" . $gconfig['vl_form'] . "'" . $sFilter);
$iTotal = $aResultTotal[0]['total'];

/*
* Output
*/
$output = array(
     "sEcho" => intval($_POST['sEcho']),
     "iTotalRecords" => $iTotal,
     "iTotalDisplayRecords" => $iFilteredTotal,
     "aaData" => array()
);

foreach ($rResult as $aRow) {

     if (isset($aRow['sample_collection_date']) && trim($aRow['sample_collection_date']) != '' && $aRow['sample_collection_date'] != '0000-00-00 00:00:00') {
          $xplodDate = explode(" ", $aRow['sample_collection_date']);
          $aRow['sample_collection_date'] = $general->humanDateFormat($xplodDate[0]);
     } else {
          $aRow['sample_collection_date'] = '';
     }
     if (isset($aRow['last_modified_datetime']) && trim($aRow['last_modified_datetime']) != '' && $aRow['last_modified_datetime'] != '0000-00-00 00:00:00') {
          $xplodDate = explode(" ", $aRow['last_modified_datetime']);
          $aRow['last_modified_datetime'] = $general->humanDateFormat($xplodDate[0]) . " " . $xplodDate[1];
     } else {
          $aRow['last_modified_datetime'] = '';
     }

     $patientFname = ucwords($general->crypto('decrypt', $aRow['patient_name'], $aRow['patient_id']));
     $patientLname = ucwords($general->crypto('decrypt', $aRow['patient_surname'], $aRow['patient_id']));

     $row = array();
     $row[] = $aRow['sample_code'];
     if ($sarr['sc_user_type'] != 'standalone') {
          $row[] = $aRow['remote_sample_code'];
     }
     $row[] = $aRow['sample_collection_date'];
     $row[] = $aRow['batch_code'];
     $row[] = ucwords($aRow['facility_name']);
     $row[] = $aRow['patient_id'];
     $row[] = $patientFname . " " . $patientLname;
     $row[] = ucwords($aRow['facility_state']);
     $row[] = ucwords($aRow['facility_district']);
     $row[] = $covid19Results[$aRow['result']];;
     $row[] = $aRow['last_modified_datetime'];
     $row[] = ucwords($aRow['status_name']);

     $output['aaData'][] = $row;
}
echo json_encode($output);
