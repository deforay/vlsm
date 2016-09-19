<?php
session_start();
include('./includes/MysqliDb.php');
include('General.php');
$general=new Deforay_Commons_General();
$tableName="vl_request_form";
$primaryKey="treament_id";

        /* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
        */
        
        $aColumns = array('vl.sample_code','b.batch_code','vl.art_no','vl.patient_name','f.facility_name','s.sample_name','vl.absolute_value','vl.log_value','vl.text_value','ts.status_name');
        $orderColumns = array('vl.sample_code','b.batch_code','vl.art_no','vl.patient_name','f.facility_name','s.sample_name','vl.result','ts.status_name');
        
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
	$sQuery="SELECT vl.*,f.*,s.sample_name,b.*,ts.*,acd.art_code,rst.sample_name as routineSampleName,fst.sample_name as failureSampleName,sst.sample_name as suspectedSampleName,u_d.user_name as reportedBy,a_u_d.user_name as approvedBy FROM vl_request_form as vl LEFT JOIN facility_details as f ON vl.facility_id=f.facility_id LEFT JOIN r_sample_type as s ON s.sample_id=vl.sample_id INNER JOIN testing_status as ts ON ts.status_id=vl.status LEFT JOIN r_art_code_details as acd ON acd.art_id=vl.current_regimen LEFT JOIN r_sample_type as rst ON rst.sample_id=vl.routine_monitoring_sample_type LEFT JOIN r_sample_type as fst ON fst.sample_id=vl.vl_treatment_failure_adherence_counseling_sample_type LEFT JOIN r_sample_type as sst ON sst.sample_id=vl.suspected_treatment_failure_sample_type LEFT JOIN batch_details as b ON b.batch_id=vl.batch_id LEFT JOIN user_details as u_d ON u_d.user_id=vl.result_reviewed_by LEFT JOIN user_details as a_u_d ON a_u_d.user_id=vl.result_approved_by";
	//$sQuery="SELECT vl.treament_id,vl.facility_id,vl.sample_code,vl.patient_name,vl.result,f.facility_name,f.facility_code,vl.art_no,s.sample_name,b.batch_code,vl.batch_id,vl.status FROM vl_request_form as vl INNER JOIN facility_details as f ON vl.facility_id=f.facility_id INNER JOIN r_sample_type as s ON s.sample_id=vl.sample_id LEFT JOIN batch_details as b ON b.batch_id=vl.batch_id";
	
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
		$dWhere = '';
		// Only approved results can be printed
		if(isset($_POST['vlPrint']) && $_POST['vlPrint']=='print'){
		    if(trim($sWhere)!= ''){
		        $sWhere = $sWhere." AND vl.status =7 ";
		    }else{
		       $sWhere = "WHERE vl.status =7 ";
		    }
		    $dWhere = "WHERE status = 7";
		}else{
		    if(trim($sWhere)!= ''){
		        $sWhere = $sWhere." AND vl.status =1 ";
		    }else{
		        $sWhere = "WHERE vl.status =1 ";
		    }
		    $dWhere = "WHERE status = 1";
		}
		$sQuery = $sQuery.' '.$sWhere;
		$_SESSION['vlResultQuery']=$sQuery;
		//echo $_SESSION['vlResultQuery'];die;
		
        if (isset($sOrder) && $sOrder != "") {
            $sOrder = preg_replace('/(\v|\s)+/', ' ', $sOrder);
            $sQuery = $sQuery.' order by '.$sOrder;
        }
        
        if (isset($sLimit) && isset($sOffset)) {
            $sQuery = $sQuery.' LIMIT '.$sOffset.','. $sLimit;
        }
		
	//die($sQuery);
	$_SESSION['vlRequestSearchResultQuery'] = $sQuery;
	//die($sQuery);
        $rResult = $db->rawQuery($sQuery);
        /* Data set length after filtering */
        
        $aResultFilterTotal =$db->rawQuery("SELECT * FROM vl_request_form as vl LEFT JOIN facility_details as f ON vl.facility_id=f.facility_id  LEFT JOIN r_sample_type as s ON s.sample_id=vl.sample_id INNER JOIN testing_status as ts ON ts.status_id=vl.status LEFT JOIN batch_details as b ON b.batch_id=vl.batch_id $sWhere order by $sOrder");
        $iFilteredTotal = count($aResultFilterTotal);

        /* Total data set length */
        $aResultTotal =  $db->rawQuery("select COUNT(treament_id) as total FROM vl_request_form $dWhere");
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
	    $vlResult = '';
	    if(isset($aRow['absolute_value']) && trim($aRow['absolute_value'])!= ''){
		$vlResult = $aRow['absolute_value'];
	    }elseif(isset($aRow['log_value']) && trim($aRow['log_value'])!= ''){
		$vlResult = $aRow['log_value'];
	    }elseif(isset($aRow['text_value']) && trim($aRow['text_value'])!= ''){
		$vlResult = $aRow['text_value'];
	    }
            $row = array();
            $row[] = $aRow['sample_code'];
            $row[] = $aRow['batch_code'];
            $row[] = $aRow['art_no'];
            $row[] = ucwords($aRow['patient_name']).' '.ucwords($aRow['surname']);
	    $row[] = ucwords($aRow['facility_name']);
            $row[] = ucwords($aRow['sample_name']);
            $row[] = $vlResult;
            $row[] = ucwords($aRow['status_name']);
	    
	    if(isset($_POST['vlPrint']) && $_POST['vlPrint']=='print'){
		$row[] = '<a href="javascript:void(0);" class="btn btn-primary btn-xs" style="margin-right: 2px;" title="View" onclick="convertResultToPdf('.$aRow['treament_id'].');"><i class="fa fa-print"> Print</i></a>';
	    }else{
            //$row[] = '<a href="javascript:void(0);" class="btn btn-success btn-xs" style="margin-right: 2px;" title="Result" onclick="showModal(\'updateVlResult.php?id=' . base64_encode($aRow['treament_id']) . '\',900,520);"><i class="fa fa-pencil-square-o"></i> Enter Result</a>
            //         <a href="javascript:void(0);" class="btn btn-primary btn-xs" style="margin-right: 2px;" title="View" onclick="convertResultToPdf('.$aRow['treament_id'].');"><i class="fa fa-file-text"> Result PDF</i></a>';
	    $row[] = '<a href="updateVlTestResult.php?id=' . base64_encode($aRow['treament_id']) . '" class="btn btn-success btn-xs" style="margin-right: 2px;" title="Result"><i class="fa fa-pencil-square-o"></i> Enter Result</a>
                      <a href="javascript:void(0);" class="btn btn-primary btn-xs" style="margin-right: 2px;" title="View" onclick="convertResultToPdf('.$aRow['treament_id'].');"><i class="fa fa-file-text"> Result PDF</i></a>';
	    }
           
            $output['aaData'][] = $row;
        }
        
        echo json_encode($output);
?>