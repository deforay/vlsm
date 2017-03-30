<?php
session_start();
include('../includes/MysqliDb.php');
include('../General.php');
$formConfigQuery ="SELECT * from global_config where name='vl_form'";
$configResult=$db->query($formConfigQuery);
$arr = array();
// now we create an associative array so that we can easily create view variables
for ($i = 0; $i < sizeof($configResult); $i++) {
  $arr[$configResult[$i]['name']] = $configResult[$i]['value'];
}
$general=new Deforay_Commons_General();
$tableName="vl_request_form";
$primaryKey="vl_sample_id";

        /* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
        */
        
        $aColumns = array('vl.sample_code',"DATE_FORMAT(vl.sample_collection_date,'%d-%b-%Y')",'b.batch_code','vl.patient_art_no','vl.patient_first_name','f.facility_name','f.facility_code','s.sample_name','vl.result',"DATE_FORMAT(vl.modified_on,'%d-%b-%Y')",'ts.status_name');
        $orderColumns = array('vl.sample_code','vl.sample_collection_date','b.batch_code','vl.patient_art_no','vl.patient_first_name','f.facility_name','f.facility_code','s.sample_name','vl.result','vl.modified_on','ts.status_name');
        
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
				 	" . ( $_POST['sSortDir_' . $i] ) . ", ";
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
            foreach ($searchArray as $search) {
                if ($sWhere == "") {
                    $sWhere .= "(";
                } else {
                    $sWhere .= " AND (";
                }
                $colSize = count($aColumns);
                
                for ($i = 0; $i < $colSize; $i++) {
                    if ($i < $colSize - 1) {
                        $sWhere .= $aColumns[$i] . " LIKE '%" . ($search ) . "%' OR ";
                    } else {
                        $sWhere .= $aColumns[$i] . " LIKE '%" . ($search ) . "%' ";
                    }
                }
                $sWhere .= ")";
            }
            //$sWhere .= $sWhereSub;
        }else{
	    //$sWhere = "vl.result_status = 6";
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
        //$sQuery="SELECT vl.vl_sample_id,vl.facility_id,vl.patient_name,f.facility_name,f.facility_code,art.art_code,s.sample_name FROM vl_request_form as vl INNER JOIN facility_details as f ON vl.facility_id=f.facility_id  INNER JOIN r_art_code_details as art ON vl.current_regimen=art.art_id INNER JOIN r_sample_type as s ON s.sample_id=vl.sample_id";
	$sQuery="SELECT * FROM vl_request_form as vl LEFT JOIN facility_details as f ON vl.facility_id=f.facility_id LEFT JOIN r_sample_type as s ON s.sample_id=vl.sample_type INNER JOIN testing_status as ts ON ts.status_id=vl.result_status LEFT JOIN r_art_code_details as art ON vl.current_regimen=art.art_id LEFT JOIN batch_details as b ON b.batch_id=vl.batch_id";
	
        //echo $sQuery;die;
	$start_date = '';
	$end_date = '';
	if(isset($_POST['sampleCollectionDate']) && trim($_POST['sampleCollectionDate'])!= ''){
	   $s_c_date = explode("to", $_POST['sampleCollectionDate']);
	   //print_r($s_c_date);die;
	   if (isset($s_c_date[0]) && trim($s_c_date[0]) != "") {
	     $start_date = $general->dateFormat(trim($s_c_date[0]));
	   }
	   if (isset($s_c_date[1]) && trim($s_c_date[1]) != "") {
	     $end_date = $general->dateFormat(trim($s_c_date[1]));
	   }
	}
	  
	if (isset($sWhere) && $sWhere != "") {
            $sWhere=' where '.$sWhere;
	    //$sQuery = $sQuery.' '.$sWhere;
	    if(isset($_POST['batchCode']) && trim($_POST['batchCode'])!= ''){
	        $sWhere = $sWhere.' AND b.batch_code LIKE "%'.$_POST['batchCode'].'%"';
	    }
	    if(isset($_POST['sampleCollectionDate']) && trim($_POST['sampleCollectionDate'])!= ''){
			if (trim($start_date) == trim($end_date)) {
				$sWhere = $sWhere.' AND DATE(vl.sample_collection_date) = "'.$start_date.'"';
			}else{
			   $sWhere = $sWhere.' AND DATE(vl.sample_collection_date) >= "'.$start_date.'" AND DATE(vl.sample_collection_date) <= "'.$end_date.'"';
			}
        }
		if(isset($_POST['sampleType']) && $_POST['sampleType']!=''){
			$sWhere = $sWhere.' AND s.sample_id = "'.$_POST['sampleType'].'"';
		}
		if(isset($_POST['facilityName']) && $_POST['facilityName']!=''){
			$sWhere = $sWhere.' AND f.facility_id = "'.$_POST['facilityName'].'"';
		}
	}else{
	    if(isset($_POST['batchCode']) && trim($_POST['batchCode'])!= ''){
		$setWhr = 'where';
		$sWhere=' where '.$sWhere;
	        $sWhere = $sWhere.' b.batch_code = "'.$_POST['batchCode'].'"';
	    }
	    if(isset($_POST['sampleCollectionDate']) && trim($_POST['sampleCollectionDate'])!= ''){
		if(isset($setWhr)){
		    if (trim($start_date) == trim($end_date)) {
		     if(isset($_POST['batchCode']) && trim($_POST['batchCode'])!= ''){
		        $sWhere = $sWhere.' AND DATE(vl.sample_collection_date) = "'.$start_date.'"';
		     }else{
			$sWhere=' where '.$sWhere;
			$sWhere = $sWhere.' DATE(vl.sample_collection_date) = "'.$start_date.'"';
		     }
		    }
		}else{
		    $setWhr = 'where';
		    $sWhere=' where '.$sWhere;
		    $sWhere = $sWhere.' DATE(vl.sample_collection_date) >= "'.$start_date.'" AND DATE(vl.sample_collection_date) <= "'.$end_date.'"';
		}
	    }
	    if(isset($_POST['sampleType']) && trim($_POST['sampleType'])!= ''){
		if(isset($setWhr)){
		    $sWhere = $sWhere.' AND s.sample_id = "'.$_POST['sampleType'].'"';
		}else{
		$setWhr = 'where';
		$sWhere=' where '.$sWhere;
	        $sWhere = $sWhere.' s.sample_id = "'.$_POST['sampleType'].'"';
		}
	    }
	    if(isset($_POST['facilityName']) && trim($_POST['facilityName'])!= ''){
		if(isset($setWhr)){
		    $sWhere = $sWhere.' AND f.facility_id = "'.$_POST['facilityName'].'"';
		}else{
		$sWhere=' where '.$sWhere;
	        $sWhere = $sWhere.' f.facility_id = "'.$_POST['facilityName'].'"';
		}
	    }
	}
	if($sWhere!=''){
	    $sWhere = $sWhere.' AND vl.form_id="'.$arr['vl_form'].'"';
	}else{
	    $sWhere = $sWhere.' where vl.form_id="'.$arr['vl_form'].'"';
	}
	$sQuery = $sQuery.' '.$sWhere;
	//echo $sQuery;die;
	//echo $sQuery;die;
        if (isset($sOrder) && $sOrder != "") {
            $sOrder = preg_replace('/(\v|\s)+/', ' ', $sOrder);
            $sQuery = $sQuery.' order by '.$sOrder;
        }
        
        if (isset($sLimit) && isset($sOffset)) {
            $sQuery = $sQuery.' LIMIT '.$sOffset.','. $sLimit;
        }
       //die($sQuery);
      // echo $sQuery;
        $_SESSION['vlRequestSearchResultQuery'] = $sQuery;
        $rResult = $db->rawQuery($sQuery);
       // print_r($rResult);
        /* Data set length after filtering */
        
        $aResultFilterTotal =$db->rawQuery("SELECT vl.vl_sample_id,vl.facility_id,vl.patient_first_name,vl.result,f.facility_name,f.facility_code,vl.patient_art_no,s.sample_name,b.batch_code,vl.batch_id,ts.status_name FROM vl_request_form as vl LEFT JOIN facility_details as f ON vl.facility_id=f.facility_id  LEFT JOIN r_sample_type as s ON s.sample_id=vl.sample_type INNER JOIN testing_status as ts ON ts.status_id=vl.result_status LEFT JOIN batch_details as b ON b.batch_id=vl.batch_id $sWhere order by $sOrder");
        $iFilteredTotal = count($aResultFilterTotal);

        /* Total data set length */
        $aResultTotal =  $db->rawQuery("select COUNT(vl_sample_id) as total FROM vl_request_form where form_id='".$arr['vl_form']."'");
       // $aResultTotal = $countResult->fetch_row();
       //print_r($aResultTotal);
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
		$vlRequest = false;
		$vlView = false;
		if(isset($_SESSION['privileges']) && (in_array("editVlRequest.php", $_SESSION['privileges']))){
		    $vlRequest = true;
		}
		if(isset($_SESSION['privileges']) && (in_array("viewVlRequest.php", $_SESSION['privileges']))){
		    $vlView = true;
		}
			
        foreach ($rResult as $aRow) {
	    if(isset($aRow['sample_collection_date']) && trim($aRow['sample_collection_date'])!= '' && $aRow['sample_collection_date']!= '0000-00-00 00:00:00'){
		    $xplodDate = explode(" ",$aRow['sample_collection_date']);
		    $aRow['sample_collection_date'] = $general->humanDateFormat($xplodDate[0]);
	    }else{
		    $aRow['sample_collection_date'] = '';
	    }
	  
	    $status = '<select class="form-control" style="" name="status[]" id="'.$aRow['vl_sample_id'].'" title="Please select status" onchange="updateStatus(this)">
 				<option value="">-- Select --</option>
				<option value="7" '.($aRow['status_id']=="7" ? "selected=selected" : "").'>Accepted</option>
 				<option value="4" '.($aRow['status_id']=="4"  ? "selected=selected" : "").'>Rejected</option>
 				<option value="2" '.($aRow['status_id']=="2"  ? "selected=selected" : "").'>Lost</option>
 			</select><br><br>';
			
			$row = array();
			$row[]='<input type="checkbox" name="chk[]" class="checkTests" id="chk' . $aRow['vl_sample_id'] . '"  value="' . $aRow['vl_sample_id'] . '" onclick="toggleTest(this);"  />';
			$row[] = $aRow['sample_code'];
			$row[] = $aRow['sample_collection_date'];
			$row[] = $aRow['batch_code'];
			$row[] = $aRow['patient_art_no'];
			$row[] = ucwords($aRow['patient_first_name']." ".$aRow['patient_last_name']);
			$row[] = ucwords($aRow['facility_name']);
			$row[] = ucwords($aRow['sample_name']);
			$row[] = $aRow['result'];
			if(isset($aRow['modified_on']) && trim($aRow['modified_on'])!= '' && $aRow['modified_on']!= '0000-00-00 00:00:00'){
			   $xplodDate = explode(" ",$aRow['modified_on']);
			   $aRow['modified_on'] = $general->humanDateFormat($xplodDate[0])." ".$xplodDate[1];
			}else{
			   $aRow['modified_on'] = '';
			}			
			$row[] = $aRow['modified_on'];			
			$row[] = $status;
			//$row[] = '<a href="updateVlTestResult.php?id=' . base64_encode($aRow['vl_sample_id']) . '" class="btn btn-success btn-xs" style="margin-right: 2px;" title="Result"><i class="fa fa-pencil-square-o"></i> Result</a>';
			
			$output['aaData'][] = $row;
        }
        
        echo json_encode($output);
?>