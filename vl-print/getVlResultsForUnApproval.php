<?php
session_start();
include('../includes/MysqliDb.php');
include('../General.php');
$general=new Deforay_Commons_General();
$tableName="temp_sample_report";
$primaryKey="temp_sample_id";
$tsQuery="SELECT * FROM r_sample_status";
$tsResult = $db->rawQuery($tsQuery);
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
        */
        
        $aColumns = array('tsr.sample_code',"DATE_FORMAT(vl.sample_collection_date,'%d-%b-%Y H:i')","DATE_FORMAT(tsr.sample_tested_datetime,'%d-%b-%Y')",'fd.facility_name','rsrr.rejection_reason_name','tsr.sample_type','tsr.result','ts.status_name');
        $orderColumns = array('tsr.sample_code','vl.sample_collection_date','tsr.sample_tested_datetime','fd.facility_name','rsrr.rejection_reason_name','tsr.sample_type','tsr.result','ts.status_name');
        
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
	$sWhereSub = "";
        if (isset($_POST['sSearch']) && $_POST['sSearch'] != "") {
            $searchArray = explode(" ", $_POST['sSearch']);
            foreach ($searchArray as $search) {
                if ($sWhereSub == "") {
                    $sWhereSub .= "(";
                } else {
                    $sWhereSub .= " AND (";
                }
                $colSize = count($aColumns);
                
                for ($i = 0; $i < $colSize; $i++) {
                    if ($i < $colSize - 1) {
                        $sWhereSub .= $aColumns[$i] . " LIKE '%" . ($search ) . "%' OR ";
                    } else {
                        $sWhereSub .= $aColumns[$i] . " LIKE '%" . ($search ) . "%' ";
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
	$sQuery="SELECT tsr.temp_sample_id,tsr.sample_code,tsr.sample_details,tsr.result_value_absolute,tsr.result_value_log,tsr.result_value_text,vl.sample_collection_date,tsr.sample_tested_datetime,tsr.lot_number,tsr.lot_expiration_date,tsr.batch_code,fd.facility_name,rsrr.rejection_reason_name,tsr.sample_type,tsr.result,tsr.result_status,ts.status_name FROM temp_sample_report as tsr LEFT JOIN vl_request_form as vl ON vl.sample_code=tsr.sample_code LEFT JOIN facility_details as fd ON fd.facility_id=vl.facility_id LEFT JOIN r_sample_rejection_reasons as rsrr ON rsrr.rejection_reason_id=vl.reason_for_sample_rejection INNER JOIN r_sample_status as ts ON ts.status_id=tsr.result_status";
	$sOrder = 'temp_sample_id ASC';
    //echo $sQuery;die;
	
	if (isset($sWhere) && $sWhere != "") {
        $sWhere=' where temp_sample_status=0 AND '.$sWhere;
	}else{
		$sWhere = ' where temp_sample_status=0';
	}
	$sQuery = $sQuery.' '.$sWhere;
        if (isset($sOrder) && $sOrder != "") {
            $sOrder = preg_replace('/(\v|\s)+/', ' ', $sOrder);
            $sQuery = $sQuery.' order by '.$sOrder;
        }else{
	    
	    $sQuery = $sQuery.' order by '.$sOrder;
	}
        //echo $sQuery;die;
        if (isset($sLimit) && isset($sOffset)) {
            $sQuery = $sQuery.' LIMIT '.$sOffset.','. $sLimit;
        }
        $rResult = $db->rawQuery($sQuery);
        /* Data set length after filtering */
        
        $aResultFilterTotal =$db->rawQuery("SELECT tsr.temp_sample_id,tsr.sample_details,tsr.sample_code,tsr.result_value_absolute,tsr.result_value_log,tsr.result_value_text,vl.sample_collection_date,tsr.sample_tested_datetime,fd.facility_name,rsrr.rejection_reason_name,tsr.sample_type,tsr.result,tsr.result_status,ts.status_name FROM temp_sample_report as tsr LEFT JOIN vl_request_form as vl ON vl.sample_code=tsr.sample_code LEFT JOIN facility_details as fd ON fd.facility_id=vl.facility_id LEFT JOIN r_sample_rejection_reasons as rsrr ON rsrr.rejection_reason_id=vl.reason_for_sample_rejection INNER JOIN r_sample_status as ts ON ts.status_id=tsr.result_status $sWhere order by $sOrder");
        $iFilteredTotal = count($aResultFilterTotal);

        /* Total data set length */
        $aResultTotal =  $db->rawQuery("select COUNT(temp_sample_id) as total FROM temp_sample_report where temp_sample_status=0");
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
			$rsDetails = '';
			//$aRow['sample_code'] = 'Control';
			$color = '';
			$status ='';
			if(isset($aRow['sample_collection_date']) && trim($aRow['sample_collection_date'])!= '' && $aRow['sample_collection_date']!= '0000-00-00 00:00:00'){
			   $xplodDate = explode(" ",$aRow['sample_collection_date']);
			   $aRow['sample_collection_date'] = $general->humanDateFormat($xplodDate[0]);
			}else{
			   $aRow['sample_collection_date'] = '';
			}
			if(isset($aRow['sample_tested_datetime']) && trim($aRow['sample_tested_datetime'])!= '' && $aRow['sample_tested_datetime']!= '0000-00-00 00:00:00'){
			   $xplodDate = explode(" ",$aRow['sample_tested_datetime']);
			   $aRow['sample_tested_datetime'] = $general->humanDateFormat($xplodDate[0])." ".$xplodDate[1];
			}else{
			   $aRow['sample_tested_datetime'] = '';
			}
            $row = array();
			if($aRow['sample_type']=='s' || $aRow['sample_type']=='S'){
				if($aRow['sample_details']== 'Result exists already'){
					$rsDetails = 'Existing Result';
					$color = '<span style="color:#86c0c8;font-weight:bold;"><i class="fa fa-exclamation"></i></span>';
				}
				if($aRow['sample_details']=='New Sample'){
					$rsDetails = 'Unknown Sample';
					$color = '<span style="color:#e8000b;font-weight:bold;"><i class="fa fa-exclamation"></i></span>';
				}
				//if($aRow['sample_details']==''){
				else{
					$rsDetails = 'Result for Sample';
					$color = '<span style="color:#337ab7;font-weight:bold;"><i class="fa fa-exclamation"></i></span>';
				}
				//}
				//$row[]='<input type="checkbox" name="chk[]" class="checkTests" id="chk' . $aRow['temp_sample_id'] . '"  value="' . $aRow['temp_sample_id'] . '" onclick="toggleTest(this);"  />';
				$status = '<select class="form-control" style="" name="status[]" id="'.$aRow['temp_sample_id'].'" title="Please select status" onchange="toggleTest(this)">
					<option value="">-- Select --</option>
					<option value="7" '.($aRow['result_status']=="7" ? "selected=selected" : "").'>Accepted</option>
					<option value="1" '.($aRow['result_status']=="1" ? "selected=selected" : "").'>Hold</option>
					<option value="4" '.($aRow['result_status']=="4"  ? "selected=selected" : "").'>Rejected</option>
					</select><br><br>';
			}
			$samCode = "'".$aRow['sample_code']."'";
			$batchCode = "'".$aRow['batch_code']."'";
			$row[] = '<input style="width:90%;" type="text" name="sampleCode" id="sampleCode'.$aRow['temp_sample_id'].'" title="'.$rsDetails.'" value="'.$aRow['sample_code'].'" onchange="updateSampleCode(this,'.$samCode.','.$aRow['temp_sample_id'].');"/>'.$color;
			$row[] = $aRow['sample_collection_date'];
			$row[] = $aRow['sample_tested_datetime'];
			$row[] = $aRow['facility_name'];
			$row[] = '<input style="width:90%;" type="text" name="batchCode" id="batchCode'.$aRow['temp_sample_id'].'" value="'.$aRow['batch_code'].'" onchange="updateBatchCode(this,'.$batchCode.','.$aRow['temp_sample_id'].');"/>';
			$row[] = $aRow['lot_number'];
			$row[] = $general->humanDateFormat($aRow['lot_expiration_date']);
			$row[] = $aRow['rejection_reason_name'];
			$row[] = ucwords($aRow['sample_type']);
			$row[] = $aRow['result'];
			$row[] = $status;
	    
			$output['aaData'][] = $row;
        }
        
        echo json_encode($output);
?>