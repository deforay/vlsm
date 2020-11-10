<?php
if (session_status() == PHP_SESSION_NONE) {
     session_start();
}
#require_once('../../startup.php');  

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
// $sQuery = "SELECT vl.*,s.sample_name,b.*,ts.*,f.facility_name,l_f.facility_name as labName,f.facility_code,f.facility_state,f.facility_district,acd.art_code,rst.sample_name as routineSampleName,fst.sample_name as failureSampleName,sst.sample_name as suspectedSampleName,u_d.user_name as reviewedBy,a_u_d.user_name as approvedBy,rs.rejection_reason_name,tr.test_reason_name FROM vl_request_form as vl LEFT JOIN facility_details as f ON vl.facility_id=f.facility_id LEFT JOIN facility_details as l_f ON vl.lab_id=l_f.facility_id LEFT JOIN r_vl_sample_type as s ON s.sample_id=vl.sample_type INNER JOIN r_sample_status as ts ON ts.status_id=vl.result_status LEFT JOIN r_vl_art_regimen as acd ON acd.art_id=vl.current_regimen LEFT JOIN r_vl_sample_type as rst ON rst.sample_id=vl.last_vl_sample_type_routine LEFT JOIN r_vl_sample_type as fst ON fst.sample_id=vl.last_vl_sample_type_failure_ac  LEFT JOIN r_vl_sample_type as sst ON sst.sample_id=vl.last_vl_sample_type_failure LEFT JOIN batch_details as b ON b.batch_id=vl.sample_batch_id LEFT JOIN user_details as u_d ON u_d.user_id=vl.result_reviewed_by LEFT JOIN user_details as a_u_d ON a_u_d.user_id=vl.result_approved_by LEFT JOIN r_vl_sample_rejection_reasons as rs ON rs.rejection_reason_id=vl.reason_for_sample_rejection LEFT JOIN r_vl_test_reasons as tr ON tr.test_reason_id=vl.reason_for_vl_testing";
// $sQuery = "SELECT count(*) as total, DATE_FORMAT(DATE(vl.sample_tested_datetime), '%b-%Y') as monthrange, f.facility_name, vl.lab_id, hf.monthly_target FROM testing_labs as hf INNER JOIN vl_request_form as vl ON vl.lab_id=hf.facility_id LEFT JOIN facility_details as f ON vl.facility_id=f.facility_id  ";
$sQuery = "SELECT   DATE_FORMAT(DATE(vl.sample_tested_datetime), '%b-%Y') as monthrange, f.*, vl.*, hf.monthly_target FROM testing_labs as hf INNER JOIN vl_request_form as vl ON vl.lab_id=hf.facility_id LEFT JOIN facility_details as f ON vl.facility_id=f.facility_id  ";

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
$sWhere .= " AND hf.test_type = 'vl'";


$sQuery = $sQuery . ' ' . $sWhere;
// $sQuery = $sQuery.' '. "group by DATE_FORMAT(DATE(vl.sample_tested_datetime), '%b-%Y');";
// echo $sQuery;die;
$_SESSION['vlMonitoringThresholdReportQuery'] = $sQuery;

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
                    // "iTotalRecords" => $cnt,
                    // "iTotalDisplayRecords" => $iFilteredTotal,
                    "aaData" => array()
               );
          
// print_r($rResult);die;
$res = array();
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
}
// print_r($res);die;
$cnt = 0;
foreach($res as $resultData)
{
     foreach($resultData as $rowData)
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

}   
$output['iTotalDisplayRecords'] = $cnt;
$output['iTotalRecords'] = $cnt;
// $output['aaData'] = $data;
echo json_encode($output);
