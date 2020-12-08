<?php
if (session_status() == PHP_SESSION_NONE) {
     session_start();
}
#require_once('../../startup.php');  
// print_r("Prasath");die;
$general = new \Vlsm\Models\General($db);
$facilitiesDb = new \Vlsm\Models\Facilities($db);
$facilityMap = $facilitiesDb->getFacilityMap($_SESSION['userId']);

$formId = $general->getGlobalConfig('vl_form');

//system config
$systemConfigQuery = "SELECT * from system_config";
$systemConfigResult = $db->query($systemConfigQuery);
$sarr = array();
// now we create an associative array so that we can easily create view variables
for ($i = 0; $i < sizeof($systemConfigResult); $i++) {
     $sarr[$systemConfigResult[$i]['name']] = $systemConfigResult[$i]['value'];
}
$general = new \Vlsm\Models\General($db);
$tableName = "vl_request_form";
$primaryKey = "vl_sample_id";
/* Array of database columns which should be read and sent back to DataTables. Use a space where
* you want to insert a non-database field (for example a counter or static image)
*/

$aColumns = array('vl.sample_tested_datetime', 'f.facility_name');
$orderColumns = array('vl.sample_tested_datetime', 'f.facility_name');

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

$sQuery = "SELECT  DATE_FORMAT(DATE(vl.sample_tested_datetime), '%b-%Y') as monthrange, f.facility_id, f.facility_name, vl.is_sample_rejected,vl.sample_tested_datetime,vl.sample_collection_date, tl.monthly_target, 
SUM(CASE WHEN (is_sample_rejected IS NOT NULL AND is_sample_rejected LIKE 'yes%') THEN 1 ELSE 0 END) as totalRejected, 
SUM(CASE WHEN (sample_tested_datetime IS NULL AND sample_collection_date IS NOT NULL) THEN 1 ELSE 0 END) as totalReceived, 
SUM(CASE WHEN (sample_collection_date IS NOT NULL) THEN 1 ELSE 0 END) as totalCollected FROM testing_labs as tl INNER JOIN eid_form as vl ON vl.lab_id=tl.facility_id LEFT JOIN facility_details as f ON vl.facility_id=f.facility_id  ";
// vl_request_form
//   health_facilities
//echo $sQuery;die;
$start_date = '';
$end_date = '';
if (isset($_POST['sampleCollectionDate']) && trim($_POST['sampleCollectionDate']) != '') {
     $s_c_date = explode(" to ", $_POST['sampleCollectionDate']);
     //print_r($s_c_date);die;
     if (isset($s_c_date[0]) && trim($s_c_date[0]) != "") {
          $start_date = trim($s_c_date[0]) . "-01";
     }
     if (isset($s_c_date[1]) && trim($s_c_date[1]) != "") {
          $end_date = trim($s_c_date[1]) . "-31";
     }
}
$sTestDate = '';
$eTestDate = '';
if (isset($_POST['sampleTestDate']) && trim($_POST['sampleTestDate']) != '') {
     $s_t_date = explode("to", $_POST['sampleTestDate']);
     if (isset($s_t_date[0]) && trim($s_t_date[0]) != "") {
          $sTestDate = $general->dateFormat(trim($s_t_date[0]));
     }
     if (isset($s_t_date[1]) && trim($s_t_date[1]) != "") {
          $eTestDate = $general->dateFormat(trim($s_t_date[1]));
     }
}

if (isset($sWhere) && $sWhere != "") {
     $sWhere = ' where ' . $sWhere;

     // if (isset($_POST['sampleCollectionDate']) && trim($_POST['sampleCollectionDate']) != '') {
     //      if (trim($start_date) == trim($end_date)) {
     //           $sWhere = $sWhere . ' AND DATE(vl.sample_collection_date) = "' . $start_date . '"';
     //      } else {
     //           $sWhere = $sWhere . ' AND DATE(vl.sample_collection_date) >= "' . $start_date . '" AND DATE(vl.sample_collection_date) <= "' . $end_date . '"';
     //      }
     // }
     if (isset($_POST['sampleTestDate']) && trim($_POST['sampleTestDate']) != '') {
          if (trim($sTestDate) == trim($eTestDate)) {
               $sWhere = $sWhere . ' AND DATE(vl.sample_tested_datetime) = "' . $sTestDate . '"';
          } else {
               $sWhere = $sWhere . ' AND DATE(vl.sample_tested_datetime) >= "' . $sTestDate . '" AND DATE(vl.sample_tested_datetime) <= "' . $eTestDate . '"';
          }
     }

     // if (isset($_POST['facilityName']) && $_POST['facilityName'] != '') {
     //      $sWhere = $sWhere . ' AND vl.lab_id = "' . $_POST['facilityName'] . '"';
     // }

     // if (isset($_POST['district']) && trim($_POST['district']) != '') {
     //      $sWhere = $sWhere . " AND f.facility_district LIKE '%" . $_POST['district'] . "%' ";
     // }
     // if (isset($_POST['state']) && trim($_POST['state']) != '') {
     //      $sWhere = $sWhere . " AND f.facility_state LIKE '%" . $_POST['state'] . "%' ";
     // }
} else {
     // if (isset($_POST['sampleCollectionDate']) && trim($_POST['sampleCollectionDate']) != '') {
     //      if (trim($start_date) == trim($end_date)) {
     //           $sWhere = 'where DATE(vl.sample_collection_date) = "' . $start_date . '"';
     //      } else {
     //           $setWhr = ' where ';
     //           $sWhere = ' where DATE(vl.sample_collection_date) >= "' . $start_date . '" AND DATE(vl.sample_collection_date) <= "' . $end_date . '"';
     //      }
     // }
     if (isset($_POST['facilityName']) && trim($_POST['facilityName']) != '') {
          $fac = explode(',', $_POST['facilityName']);
          $out = '';
          // print_r($fac);die;
          for($s=0; $s < count($fac); $s++)
          {
               if($out)
                    $out = $out.',"'.$fac[$s].'"';
               else
                    $out = '("'.$fac[$s].'"';
          }
          $out = $out.')';
          if (isset($setWhr)) {
               $sWhere = $sWhere . ' AND vl.lab_id IN ' . $out . '';
          } else {
               $setWhr = 'where';
               $sWhere = ' where ' . $sWhere;
               $sWhere = $sWhere . ' vl.lab_id IN ' . $out . '';
          }
     }
     if (isset($_POST['district']) && trim($_POST['district']) != '') {
          if (isset($setWhr)) {
               $sWhere = $sWhere . " AND f.facility_district LIKE '%" . $_POST['district'] . "%' ";
          } else {
               $setWhr = 'where';
               $sWhere = ' where ' . $sWhere;
               $sWhere = $sWhere . " f.facility_district LIKE '%" . $_POST['district'] . "%' ";
          }
     }
     if (isset($_POST['state']) && trim($_POST['state']) != '') {
          if (isset($setWhr)) {
               $sWhere = $sWhere . " AND f.facility_state LIKE '%" . $_POST['state'] . "%' ";
          } else {
               $sWhere = ' where ' . $sWhere;
               $sWhere = $sWhere . " f.facility_state LIKE '%" . $_POST['state'] . "%' ";
          }
     }
     if (isset($_POST['sampleTestDate']) && trim($_POST['sampleTestDate']) != '') {
          if (isset($setWhr)) {
               $sWhere = $sWhere . ' AND DATE(vl.sample_tested_datetime) >= "' . $sTestDate . '" AND DATE(vl.sample_tested_datetime) <= "' . $eTestDate . '"';
          } else {
               $setWhr = 'where';
               $sWhere = ' where ' . $sWhere;
               $sWhere = $sWhere . ' DATE(vl.sample_tested_datetime) >= "' . $sTestDate . '" AND DATE(vl.sample_tested_datetime) <= "' . $eTestDate . '"';
          }
     }
}
if ($sWhere != '') {
     $sWhere = $sWhere . ' AND vl.result!="" AND vl.vlsm_country_id="' . $formId . '" AND vl.result_status!=9';
} else {
     $sWhere = $sWhere . ' where vl.result!="" AND vl.vlsm_country_id="' . $formId . '" AND vl.result_status!=9';
}

if (!empty($facilityMap)) {
     $sWhere .= " AND vl.facility_id IN ($facilityMap) ";
}
$sWhere .= " AND tl.test_type = 'eid'";


$sQuery = $sQuery . ' ' . $sWhere .' GROUP BY f.facility_id, YEAR(vl.sample_tested_datetime), MONTH(vl.sample_tested_datetime)';
// $sQuery = $sQuery.' '. "group by DATE_FORMAT(DATE(vl.sample_tested_datetime), '%b-%Y');";
// echo $sQuery;die;
$_SESSION['eidMonitoringThresholdReportQuery'] = $sQuery;

// if (isset($sOrder) && $sOrder != "") {
//      $sOrder = preg_replace('/(\v|\s)+/', ' ', $sOrder);
//      $sQuery = $sQuery . ' order by ' . $sOrder;
// }

// if (isset($sLimit) && isset($sOffset)) {
//      $sQuery = $sQuery . ' LIMIT ' . $sOffset . ',' . $sLimit;
// }
//die($sQuery);
$rResult = $db->rawQuery($sQuery);
// print_r($sQuery);die;
/* Data set length after filtering */

$aResultFilterTotal = $db->rawQuery($sQuery);
$iFilteredTotal = count($aResultFilterTotal);

/* Total data set length */
$aResultTotal =  $db->rawQuery($sQuery);
// $aResultTotal = $countResult->fetch_row();
$iTotal = count($aResultTotal);

/*
          * Output
          */
$output = array(
     "sEcho" => intval($_POST['sEcho']),
     "iTotalRecords" => $iTotal,
     "iTotalDisplayRecords" => $iFilteredTotal,
     "aaData" => array()
);

// foreach ($rResult as $aRow) {

//      if($aRow['monthly_target'] > $aRow['total'])
//      {
//           $row = array();
//           $row[] = ucwords($aRow['facility_name']);
//           $row[] = $aRow['monthrange'];
//           $row[] = $aRow['total'];
//           $output['aaData'][] = $row;
//      }
// }
// echo json_encode($output);
// print_r($rResult);die;
/* $res = array();
foreach ($rResult as $aRow) {
     $row = array();
     if( isset($res[$aRow['facility_id']]))
     {
          if(isset($res[$aRow['facility_id']][$aRow['monthrange']]))
          {
               if(trim($aRow['is_sample_rejected'])  == 'yes')
                    $row['totalRejected'] = $res[$aRow['facility_id']][$aRow['monthrange']]['totalRejected']  + 1;
               else
                    $row['totalRejected'] = $res[$aRow['facility_id']][$aRow['monthrange']]['totalRejected'];
               if(trim($aRow['sample_tested_datetime'])  == NULL  && trim($aRow['sample_collection_date']) != '')
                    $row['totalReceived'] = $res[$aRow['facility_id']][$aRow['monthrange']]['totalReceived']  + 1;
               else
                    $row['totalReceived'] = $res[$aRow['facility_id']][$aRow['monthrange']]['totalReceived'];
               $row['facility_name'] = ucwords($aRow['facility_name']);
               $row['monthrange'] = $aRow['monthrange'];
                $row['monthly_target'] = $aRow['monthly_target'];
               $row['totalCollected'] = $res[$aRow['facility_id']][$aRow['monthrange']]['totalCollected']  + 1;
               $res[$aRow['facility_id']][$aRow['monthrange']] = $row;
          }
          else
          {
               if(trim($aRow['is_sample_rejected'])  == 'yes')
                    $row['totalRejected'] = $res[$aRow['facility_id']][$aRow['monthrange']]['totalRejected']  + 1;
               else
                    $row['totalRejected'] = 0;
               if(trim($aRow['sample_tested_datetime'])  == NULL  && trim($aRow['sample_collection_date']) != '')
                    $row['totalReceived'] = $res[$aRow['facility_id']][$aRow['monthrange']]['totalReceived']  + 1;
               else
                    $row['totalReceived'] = 0;
               $row['facility_name'] = ucwords($aRow['facility_name']);
               $row['monthrange'] = $aRow['monthrange'];
                $row['monthly_target'] = $aRow['monthly_target'];
               $row['totalCollected'] = $res[$aRow['facility_id']][$aRow['monthrange']]['totalCollected']  + 1;
               $res[$aRow['facility_id']][$aRow['monthrange']] = $row;
          }
     }
     else
     {
          if(trim($aRow['is_sample_rejected'])  == 'yes')
                    $row['totalRejected'] = $res[$aRow['facility_id']][$aRow['monthrange']]['totalRejected']  + 1;
               else
                    $row['totalRejected'] = 0;
          if(trim($aRow['sample_tested_datetime'])  == NULL  && trim($aRow['sample_collection_date']) != '')
                    $row['totalReceived'] = $res[$aRow['facility_id']][$aRow['monthrange']]['totalReceived']  + 1;
               else
                    $row['totalReceived'] = 0;
          $row['facility_name'] = ucwords($aRow['facility_name']);
          $row['monthrange'] = $aRow['monthrange'];
           $row['monthly_target'] = $aRow['monthly_target'];
          $row['totalCollected'] = 1;
          $res[$aRow['facility_id']][$aRow['monthrange']] = $row;
     }
} */
// print_r($res);die;
$cnt = 0;
foreach($rResult as $rowData)
{
     // foreach($resultData as $rowData)
     // {
          if($_POST['targetType'] == 1)
          {

               if($rowData['monthly_target'] > $rowData['totalCollected'])
               { 
                    $cnt++;
                    $data = array();
                    $data[] = ucwords($rowData['facility_name']);
                    $data[] = $rowData['monthrange'];
                    $data[] = $rowData['totalReceived'];
                    $data[] = $rowData['totalRejected'];
                    $data[] = $rowData['totalCollected'];
                    $data[] = $rowData['monthly_target'];
                    // print_r($data);die;
                    $output['aaData'][] = $data;
              }
          }
          else if($_POST['targetType'] == 2)
          {

               if($rowData['monthly_target'] < $rowData['totalCollected'])
               { 
                    $cnt++;
                    $data = array();
                    $data[] = ucwords($rowData['facility_name']);
                    $data[] = $rowData['monthrange'];
                    $data[] = $rowData['totalReceived'];
                    $data[] = $rowData['totalRejected'];
                    $data[] = $rowData['totalCollected'];
                    $data[] = $rowData['monthly_target'];
                    // print_r($data);die;
                    $output['aaData'][] = $data;
              }
          }
          else if($_POST['targetType'] == 3)
          {
                    $cnt++;
                    $data = array();
                    $data[] = ucwords($rowData['facility_name']);
                    $data[] = $rowData['monthrange'];
                    $data[] = $rowData['totalReceived'];
                    $data[] = $rowData['totalRejected'];
                    $data[] = $rowData['totalCollected'];
                    $data[] = $rowData['monthly_target'];
                    // print_r($data);die;
                    $output['aaData'][] = $data;
          }
     // }

}   
$output['iTotalDisplayRecords'] = $cnt;
$output['iTotalRecords'] = $cnt;
// $output['aaData'] = $data;
echo json_encode($output);
