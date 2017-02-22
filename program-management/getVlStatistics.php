<?php
session_start();
include('../includes/MysqliDb.php');
include('../General.php');
$general=new Deforay_Commons_General();
$tableName="vl_request_form";
$primaryKey="vl_sample_id";
$configQuery ="SELECT * from global_config where name='vl_form'";
$configResult=$db->query($configQuery);
$country = $configResult[0]['value'];
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
        */
        
        $aColumns = array('state','district','facility_name','facility_code',"DATE_FORMAT(vl.sample_collection_date,'%d-%b-%Y')",'comments');
        $orderColumns = array('state','district','facility_name','sample_collection_date','sample_collection_date','sample_collection_date','sample_collection_date','sample_collection_date','sample_collection_date','sample_collection_date','sample_collection_date','sample_collection_date','sample_collection_date','sample_collection_date','sample_collection_date','sample_collection_date','sample_collection_date','comments');
        
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
	$sQuery="SELECT vl.vl_sample_id,vl.facility_id,f.state,f.district,f.facility_name FROM vl_request_form as vl INNER JOIN facility_details as f ON f.facility_id=vl.facility_id";
	$start_date = '';
	$end_date = '';
	if(isset($_POST['sampleCollectionDate']) && trim($_POST['sampleCollectionDate'])!= ''){
	   $s_t_date = explode("to", $_POST['sampleCollectionDate']);
	   if (isset($s_t_date[0]) && trim($s_t_date[0]) != "") {
	     $start_date = $general->dateFormat(trim($s_t_date[0]));
	   }
	   if (isset($s_t_date[1]) && trim($s_t_date[1]) != "") {
	     $end_date = $general->dateFormat(trim($s_t_date[1]));
	   }
	}
	
	$tWhere = 'where ';
	if(isset($sWhere) && trim($sWhere)!= ''){
	  $sWhere=' where '.$sWhere;
	  $sWhere = $sWhere.' AND vl.form_id = '.$country;
	  $tWhere = $tWhere.'vl.form_id = '.$country;
	}else{
	  $sWhere=' where '.$sWhere;
	  $sWhere = $sWhere.'vl.form_id = '.$country;
	  $tWhere = $tWhere.'vl.form_id = '.$country;
	}
	
	if(isset($_POST['sampleCollectionDate']) && trim($_POST['sampleCollectionDate'])!= ''){
	    if (trim($start_date) == trim($end_date)) {
	      $sWhere = $sWhere.' AND DATE(vl.sample_collection_date) = "'.$start_date.'"';
	      $tWhere = $tWhere.' AND DATE(vl.sample_collection_date) = "'.$start_date.'"';
	    }else{
	      $sWhere = $sWhere.' AND DATE(vl.sample_collection_date) >= "'.$start_date.'" AND DATE(vl.sample_collection_date) <= "'.$end_date.'"';
	      $tWhere = $tWhere.' AND DATE(vl.sample_collection_date) >= "'.$start_date.'" AND DATE(vl.sample_collection_date) <= "'.$end_date.'"';
	    }
        }if(isset($_POST['lab']) && trim($_POST['lab'])!= ''){
	    $sWhere = $sWhere." AND vl.lab_id IN (".$_POST['lab'].")";
	    $tWhere = $tWhere." AND vl.lab_id IN (".$_POST['lab'].")";
	}
	
	$sQuery = $sQuery.' '.$sWhere;
	$sQuery = $sQuery.' GROUP BY vl.facility_id';
	$_SESSION['vlStatisticsQuery']=$sQuery;
        if (isset($sOrder) && $sOrder != "") {
            $sOrder = preg_replace('/(\v|\s)+/', ' ', $sOrder);
            $sQuery = $sQuery.' order by '.$sOrder;
        }
        
        if (isset($sLimit) && isset($sOffset)) {
            $sQuery = $sQuery.' LIMIT '.$sOffset.','. $sLimit;
        }
	
	//die($sQuery);
        $sResult = $db->rawQuery($sQuery);
        /* Data set length after filtering */
        
        $aResultFilterTotal =$db->rawQuery("SELECT vl.vl_sample_id FROM vl_request_form as vl INNER JOIN facility_details as f ON f.facility_id=vl.facility_id $sWhere GROUP BY vl.facility_id order by $sOrder");
        $iFilteredTotal = count($aResultFilterTotal);
        /* Total data set length */
        $aResultTotal =  $db->rawQuery("select vl.vl_sample_id FROM vl_request_form as vl INNER JOIN facility_details as f ON f.facility_id=vl.facility_id $tWhere GROUP BY vl.facility_id");
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
	
        
        foreach ($sResult as $aRow) {
	    //No. of tests per facility & calculate age
	    $totalQuery = 'SELECT vl_sample_id,patient_dob,gender,is_patient_pregnant,is_patient_breastfeeding,result FROM vl_request_form as vl where vl.facility_id = '.$aRow['facility_id'].' AND vl.form_id = '.$country;
	    $totalResult = $db->rawQuery($totalQuery);
	    $lte14n1000 = array();
	    $lte14ngt1000 = array();
	    $gt14mnlte1000 = array();
	    $gt14mn1000 = array();
	    $gt14fnlte1000 = array();
	    $gt14fn1000 = array();
	    $isPatientPergnantrbfeedingnlte1000 = array();
	    $isPatientPergnantrbfeedingngt1000 = array();
	    $unknownxnlte1000 = array();
	    $unknownxngt1000 = array();
	    $lte1000total = array();
	    $gt1000total = array();
	    foreach($totalResult as $tRow){
		if(trim($tRow['result'])!= '' && $tRow['result']!= NULL && $tRow['result']!= 'Target Not Detected' && $tRow['result'] <= 1000){
		    $lte1000total[] = $tRow['vl_sample_id'];
		}else if(trim($tRow['result'])!= '' && $tRow['result']!= NULL && $tRow['result']!= 'Target Not Detected' && $tRow['result'] > 1000){
		   $gt1000total[] = $tRow['vl_sample_id'];
		}
		if($tRow['patient_dob']!= NULL && $tRow['patient_dob']!= '' && $tRow['patient_dob']!= '0000-00-00'){
		    $age = floor((time() - strtotime($tRow['patient_dob'])) / 31556926);
		    if($age > 14){
			if($tRow['gender'] == 'male' && trim($tRow['result'])!= '' && $tRow['result']!= NULL && $tRow['result']!= 'Target Not Detected' && $tRow['result'] <= 1000){
			    $gt14mnlte1000[] = $tRow['vl_sample_id'];
			}else if($tRow['gender'] == 'male' && trim($tRow['result'])!= '' && $tRow['result']!= NULL && $tRow['result']!= 'Target Not Detected' && $tRow['result'] > 1000){
			    $gt14mn1000[] = $tRow['vl_sample_id'];
			}else if($tRow['gender'] == 'female' && trim($tRow['result'])!= '' && $tRow['result']!= NULL && $tRow['result']!= 'Target Not Detected' && $tRow['result'] <= 1000){
			    if(($tRow['is_patient_pregnant']!= NULL && $tRow['is_patient_pregnant']!= '' && $tRow['is_patient_pregnant'] == 'yes') || ($tRow['is_patient_breastfeeding']!= NULL && $tRow['is_patient_breastfeeding']!= '' && $tRow['is_patient_breastfeeding'] == 'yes')){
				$isPatientPergnantrbfeedingnlte1000[] = $tRow['vl_sample_id'];
			    }
			   $gt14fnlte1000[] = $tRow['vl_sample_id'];
			}else if($tRow['gender'] == 'female' && trim($tRow['result'])!= '' && $tRow['result']!= NULL && $tRow['result']!= 'Target Not Detected' && $tRow['result'] > 1000){
			    if(($tRow['is_patient_pregnant']!= NULL && $tRow['is_patient_pregnant']!= '' && $tRow['is_patient_pregnant'] == 'yes') || ($tRow['is_patient_breastfeeding']!= NULL && $tRow['is_patient_breastfeeding']!= '' && $tRow['is_patient_breastfeeding'] == 'yes')){
				$isPatientPergnantrbfeedingngt1000[] = $tRow['vl_sample_id'];
			    }
			   $gt14fn1000[] = $tRow['vl_sample_id'];
			}
		    }else{
			if(trim($tRow['result'])!= '' && $tRow['result']!= NULL && $tRow['result']!= 'Target Not Detected' && $tRow['result'] <= 1000){
			   $lte14n1000[] = $tRow['vl_sample_id'];
			}else if(trim($tRow['result'])!= '' && $tRow['result']!= NULL && $tRow['result']!= 'Target Not Detected' && $tRow['result'] > 1000){
			  $lte14ngt1000[] = $tRow['vl_sample_id'];
			}
		    }
		}else{
		    if(trim($tRow['result'])!= '' && $tRow['result']!= NULL && $tRow['result']!= 'Target Not Detected' && $tRow['result'] <= 1000){
		        $unknownxnlte1000[] = $tRow['vl_sample_id'];
		    }else if(trim($tRow['result'])!= '' && $tRow['result']!= NULL && $tRow['result']!= 'Target Not Detected' && $tRow['result'] > 1000){
			$unknownxngt1000[] = $tRow['vl_sample_id'];
		    }
		}
	    }
	    //No. of rejections
	    $rejectionQuery = 'SELECT vl_sample_id FROM vl_request_form as vl where vl.facility_id = '.$aRow['facility_id'].' AND vl.form_id = '.$country.' AND ((vl.rejection IS NOT NULL AND vl.rejection!= "") OR (vl.sample_rejection_reason IS NOT NULL AND vl.sample_rejection_reason!= ""))';
	    $rejectionResult = $db->rawQuery($rejectionQuery);
	    $row = array();
            $row[] = ucwords($aRow['state']);
            $row[] = ucwords($aRow['district']);
            $row[] = ucwords($aRow['facility_name']);
            $row[] = '';
            $row[] = count($rejectionResult);
            $row[] = count($lte14n1000);
            $row[] = count($lte14ngt1000);
            $row[] = count($gt14mnlte1000);
            $row[] = count($gt14mn1000);
            $row[] = count($gt14fnlte1000);
            $row[] = count($gt14fn1000);
            $row[] = count($isPatientPergnantrbfeedingnlte1000);
            $row[] = count($isPatientPergnantrbfeedingngt1000);
            $row[] = count($unknownxnlte1000);
            $row[] = count($unknownxngt1000);
            $row[] = count($lte1000total);
            $row[] = count($gt1000total);
            $row[] = count($totalResult);
            $row[] = '';
            $output['aaData'][] = $row;
        }
        
    echo json_encode($output);
?>