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
        
        $aColumns = array('vl.sample_code','b.batch_code','vl.art_no','vl.patient_name','f.facility_name','s.sample_name','vl.result',"DATE_FORMAT(vl.last_modified_datetime,'%d-%b-%Y')",'ts.status_name');
        $orderColumns = array('vl.sample_code','b.batch_code','vl.art_no','vl.patient_name','f.facility_name','s.sample_name','vl.result','vl.last_modified_datetime','ts.status_name');
        
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
	 $sQuery="SELECT vl.*,s.sample_name,b.*,ts.*,f.facility_name,l_f.facility_name as labName,f.facility_code,f.state,f.district,acd.art_code,rst.sample_name as routineSampleName,fst.sample_name as failureSampleName,sst.sample_name as suspectedSampleName,u_d.user_name as reviewedBy,a_u_d.user_name as approvedBy ,rs.rejection_reason_name FROM vl_request_form as vl LEFT JOIN facility_details as f ON vl.facility_id=f.facility_id LEFT JOIN facility_details as l_f ON vl.lab_id=l_f.facility_id LEFT JOIN r_sample_type as s ON s.sample_id=vl.sample_id INNER JOIN r_testing_status as ts ON ts.status_id=vl.status LEFT JOIN r_art_code_details as acd ON acd.art_id=vl.current_regimen LEFT JOIN r_sample_type as rst ON rst.sample_id=vl.routine_monitoring_sample_type LEFT JOIN r_sample_type as fst ON fst.sample_id=vl.vl_treatment_failure_adherence_counseling_sample_type LEFT JOIN r_sample_type as sst ON sst.sample_id=vl.suspected_treatment_failure_sample_type LEFT JOIN batch_details as b ON b.batch_id=vl.sample_batch_id LEFT JOIN user_details as u_d ON u_d.user_id=vl.result_reviewed_by LEFT JOIN user_details as a_u_d ON a_u_d.user_id=vl.result_approved_by LEFT JOIN r_sample_rejection_reasons as rs ON rs.rejection_reason_id=vl.sample_rejection_reason";
	
	//$sQuery="SELECT vl.vl_sample_id,vl.facility_id,vl.sample_code,vl.patient_name,vl.result,f.facility_name,f.facility_code,vl.art_no,s.sample_name,b.batch_code,vl.batch_id,vl.status FROM vl_request_form as vl INNER JOIN facility_details as f ON vl.facility_id=f.facility_id INNER JOIN r_sample_type as s ON s.sample_id=vl.sample_id LEFT JOIN batch_details as b ON b.batch_id=vl.batch_id";
	
//        echo $sQuery;die;
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
			if(isset($_POST['batchCode']) && trim($_POST['batchCode'])!= ''){
				$sWhere = $sWhere.' AND b.batch_code = "'.$_POST['batchCode'].'"';
			}
			if(isset($_POST['sampleCollectionDate']) && trim($_POST['sampleCollectionDate'])!= ''){
				if (trim($start_date) == trim($end_date)) {
					$sWhere = $sWhere.' AND DATE(vl.sample_collection_date) = "'.$start_date.'"';
				}else{
				   $sWhere = $sWhere.' AND DATE(vl.sample_collection_date) >= "'.$start_date.'" AND DATE(vl.sample_collection_date) <= "'.$end_date.'"';
				}
			}
			if(isset($_POST['sampleType']) && trim($_POST['sampleType'])!= ''){
				$sWhere = $sWhere.' AND s.sample_id = "'.$_POST['sampleType'].'"';
			}
			if(isset($_POST['facilityName']) && trim($_POST['facilityName'])!= ''){
				$sWhere = $sWhere.' AND f.facility_id = "'.$_POST['facilityName'].'"';
			}
			if(isset($_POST['status']) && trim($_POST['status'])!= ''){
			    $sWhere = $sWhere.' AND vl.status  IN ('.$_POST['status'].')';
			}
			if(isset($_POST['artNo']) && trim($_POST['artNo'])!= ''){
			    $sWhere = $sWhere." AND vl.art_no LIKE '%" . $_POST['artNo'] . "%' ";
			}
			if(isset($_POST['gender']) && trim($_POST['gender'])!= ''){
			    if(trim($_POST['gender']) == "not_recorded"){
				$sWhere = $sWhere.' AND (vl.gender = "not_recorded" OR vl.gender ="" OR vl.gender IS NULL)';
			    }else{
				$sWhere = $sWhere.' AND vl.gender ="'.$_POST['gender'].'"';
			    }
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
					$setWhr = 'where';
					$sWhere=' where '.$sWhere;
					$sWhere = $sWhere.' f.facility_id = "'.$_POST['facilityName'].'"';
				}
			}
			if(isset($_POST['artNo']) && trim($_POST['artNo'])!= ''){
				if(isset($setWhr)){
					$sWhere = $sWhere.' AND vl.art_no = "'.$_POST['artNo'].'"';
				}else{
					$setWhr = 'where';
					$sWhere=' where '.$sWhere;
					$sWhere = $sWhere.' vl.art_no = "'.$_POST['artNo'].'"';
				}
			}
			if(isset($_POST['status']) && trim($_POST['status'])!= ''){
			    if(isset($setWhr)){
				$sWhere = $sWhere.' AND vl.status IN ('.$_POST['status'].')';
			    }else{
			      $setWhr = 'where';
			      $sWhere=' where '.$sWhere;
			      $sWhere = $sWhere.' vl.status IN ('.$_POST['status'].')';
			    }
		        }
			if(isset($_POST['gender']) && trim($_POST['gender'])!= ''){
			    if(isset($setWhr)){
				if(trim($_POST['gender']) == "not_recorded"){
				  $sWhere = $sWhere.' AND (vl.gender = "not_recorded" OR vl.gender ="" OR vl.gender IS NULL)';
				}else{
				  $sWhere = $sWhere.' AND vl.gender ="'.$_POST['gender'].'"';
				}
			    }else{
			       $sWhere=' where '.$sWhere;
				if(trim($_POST['gender']) == "not_recorded"){
				    $sWhere = $sWhere.' (vl.gender = "not_recorded" OR vl.gender ="" OR vl.gender IS NULL)';
				}else{
				    $sWhere = $sWhere.' vl.gender ="'.$_POST['gender'].'"';
				}
			    }
			}
		}
		$dWhere = '';
		// Only approved results can be printed
		if(isset($_POST['vlPrint']) && $_POST['vlPrint']=='print'){
		  if(trim($_POST['status'])== ''){
		    if(trim($sWhere)!= ''){
		        $sWhere = $sWhere." AND (vl.status =7 OR vl.status=4 OR vl.status=2)";
		    }else{
		       $sWhere = "WHERE (vl.status =7 OR vl.status=4 OR vl.status=2)";
		    }
		  }
		    $sWhere = $sWhere." AND vl.vlsm_country_id='".$arr['vl_form']."'";
		    $dWhere = "WHERE (vl.status =7 OR vl.status=4 OR vl.status=2) AND vl.vlsm_country_id='".$arr['vl_form']."'";
		}else{
		    if(trim($sWhere)!= ''){
		        $sWhere = $sWhere." AND vl.vlsm_country_id='".$arr['vl_form']."'";
		    }else{
		        $sWhere = "WHERE vl.vlsm_country_id='".$arr['vl_form']."'";
		    }
		    $dWhere = "WHERE vl.vlsm_country_id='".$arr['vl_form']."'";
		}
		$sQuery = $sQuery.' '.$sWhere;
		$_SESSION['vlResultQuery']=$sQuery;
		//echo $_SESSION['vlResultQuery'];die;
		
        if (isset($sOrder) && $sOrder != "") {
            $sOrder = preg_replace('/(\v|\s)+/', ' ', $sOrder);
            $sQuery = $sQuery.' order by '.$sOrder;
        }
        $_SESSION['vlRequestSearchResultQuery'] = $sQuery;
        if (isset($sLimit) && isset($sOffset)) {
            $sQuery = $sQuery.' LIMIT '.$sOffset.','. $sLimit;
        }
	
	//die($sQuery);
        $rResult = $db->rawQuery($sQuery);
        /* Data set length after filtering */
        
        $aResultFilterTotal =$db->rawQuery("SELECT * FROM vl_request_form as vl LEFT JOIN facility_details as f ON vl.facility_id=f.facility_id  LEFT JOIN r_sample_type as s ON s.sample_id=vl.sample_id INNER JOIN r_testing_status as ts ON ts.status_id=vl.status LEFT JOIN batch_details as b ON b.batch_id=vl.sample_batch_id $sWhere order by $sOrder");
        $iFilteredTotal = count($aResultFilterTotal);
        /* Total data set length */
        $aResultTotal =  $db->rawQuery("select COUNT(vl_sample_id) as total FROM vl_request_form as vl $dWhere");
       // $aResultTotal = $countResult->fetch_row();
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
            $row = array();
            $row[] = $aRow['sample_code'];
            $row[] = $aRow['batch_code'];
            $row[] = $aRow['art_no'];
            $row[] = ucwords($aRow['patient_name']).' '.ucwords($aRow['surname']);
			$row[] = ucwords($aRow['facility_name']);
            $row[] = ucwords($aRow['sample_name']);
            $row[] = $aRow['result'];
			if(isset($aRow['last_modified_datetime']) && trim($aRow['last_modified_datetime'])!= '' && $aRow['last_modified_datetime']!= '0000-00-00 00:00:00'){
			   $xplodDate = explode(" ",$aRow['last_modified_datetime']);
			   $aRow['last_modified_datetime'] = $general->humanDateFormat($xplodDate[0])." ".$xplodDate[1];
			}else{
			   $aRow['last_modified_datetime'] = '';
			}			
			$row[] = $aRow['last_modified_datetime'];
            $row[] = ucwords($aRow['status_name']);
	    
	    if(isset($_POST['vlPrint']) && $_POST['vlPrint']=='print'){
		$row[] = '<a href="javascript:void(0);" class="btn btn-primary btn-xs" style="margin-right: 2px;" title="View" onclick="convertResultToPdf('.$aRow['vl_sample_id'].');"><i class="fa fa-print"> Print</i></a>';
	    }else{
            //$row[] = '<a href="javascript:void(0);" class="btn btn-success btn-xs" style="margin-right: 2px;" title="Result" onclick="showModal(\'updateVlResult.php?id=' . base64_encode($aRow['vl_sample_id']) . '\',900,520);"><i class="fa fa-pencil-square-o"></i> Enter Result</a>
            //         <a href="javascript:void(0);" class="btn btn-primary btn-xs" style="margin-right: 2px;" title="View" onclick="convertSearchResultToPdf('.$aRow['vl_sample_id'].');"><i class="fa fa-file-text"> Result PDF</i></a>';
	    $row[] = '<a href="updateVlTestResult.php?id=' . base64_encode($aRow['vl_sample_id']) . '" class="btn btn-success btn-xs" style="margin-right: 2px;" title="Result"><i class="fa fa-pencil-square-o"></i> Enter Result</a>
                      ';
	    }
           
            $output['aaData'][] = $row;
        }
        
        echo json_encode($output);
?>