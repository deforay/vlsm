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
        
        $aColumns = array('facility_state','facility_district','facility_name','facility_code',"DATE_FORMAT(vl.sample_tested_datetime,'%d-%b-%Y')",'vl.approver_comments');
        $orderColumns = array('facility_state','facility_district','facility_name','sample_tested_datetime','','','','','','','','','','','','','','','vl.approver_comments');
        
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
	$sQuery="SELECT
	
		vl.facility_id,f.facility_code,f.facility_state,f.facility_district,f.facility_name,
		
		SUM(CASE
			WHEN (result_status = 4) THEN 1
		             ELSE 0
		           END) AS rejections,
		SUM(CASE 
			WHEN (patient_age_in_years <= 14 AND (result <= 1000 OR result ='Target Not Detected')) THEN 1
		             ELSE 0
		           END) AS lt14lt1000, 
		SUM(CASE 
             WHEN (patient_age_in_years <= 14 AND result > 1000) THEN 1
             ELSE 0
           END) AS lt14gt1000,
		SUM(CASE 
             WHEN (patient_age_in_years > 14 AND (patient_gender != '' AND patient_gender is not NULL AND patient_gender ='male') AND (result <= 1000 OR result ='Target Not Detected')) THEN 1
             ELSE 0
           END) AS gt14lt1000M,
		SUM(CASE 
             WHEN (patient_age_in_years > 14 AND (patient_gender != '' AND patient_gender is not NULL AND patient_gender ='male') AND result > 1000) THEN 1
             ELSE 0
           END) AS gt14gt1000M,
		SUM(CASE 
             WHEN (patient_age_in_years > 14 AND (patient_gender != '' AND patient_gender is not NULL AND patient_gender ='female') AND (result <= 1000 OR result ='Target Not Detected')) THEN 1
             ELSE 0
           END) AS gt14lt1000F,
		SUM(CASE 
             WHEN (patient_age_in_years > 14 AND (patient_gender != '' AND patient_gender is not NULL AND patient_gender ='female') AND result > 1000) THEN 1
             ELSE 0
           END) AS gt14gt1000F,	
		SUM(CASE 
             WHEN ((is_patient_pregnant ='yes') OR (is_patient_breastfeeding ='yes') AND (result <= 1000 OR result ='Target Not Detected')) THEN 1
             ELSE 0
           END) AS preglt1000,	
		SUM(CASE 
             WHEN ((is_patient_pregnant ='yes') OR (is_patient_breastfeeding ='yes') AND result > 1000) THEN 1
             ELSE 0
           END) AS preggt1000,           	           	
		SUM(CASE 
             WHEN (((patient_age_in_years = '' OR patient_age_in_years is NULL) OR (patient_gender = '' OR patient_gender is NULL)) AND (result <= 1000 OR result ='Target Not Detected')) THEN 1
             ELSE 0
           END) AS ult1000, 
		SUM(CASE 
             WHEN (((patient_age_in_years = '' OR patient_age_in_years is NULL) OR (patient_gender = '' OR patient_gender is NULL)) AND result > 1000) THEN 1
             ELSE 0
           END) AS ugt1000,               
		SUM(CASE 
             WHEN ((result <= 1000 OR result ='Target Not Detected')) THEN 1
             ELSE 0
           END) AS totalLessThan1000,     
		SUM(CASE 
             WHEN ((result > 1000)) THEN 1
             ELSE 0
           END) AS totalGreaterThan1000,
		COUNT(result) as total
		FROM vl_request_form as vl RIGHT JOIN facility_details as f ON f.facility_id=vl.facility_id";
	$start_date = '';
	$end_date = '';
	if(isset($_POST['sampleTestDate']) && trim($_POST['sampleTestDate'])!= ''){
	   $s_t_date = explode("to", $_POST['sampleTestDate']);
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
		  $sWhere = $sWhere.' AND vl.vlsm_country_id = '.$country;
		  $tWhere = $tWhere.'vl.vlsm_country_id = '.$country;
		}else{
		  $sWhere=' where '.$sWhere;
		  $sWhere = $sWhere.'vl.vlsm_country_id = '.$country;
		  $tWhere = $tWhere.'vl.vlsm_country_id = '.$country;
		}
		
		if(isset($_POST['sampleTestDate']) && trim($_POST['sampleTestDate'])!= ''){
			if (trim($start_date) == trim($end_date)) {
			  $sWhere = $sWhere.' AND DATE(vl.sample_tested_datetime) = "'.$start_date.'"';
			  $tWhere = $tWhere.' AND DATE(vl.sample_tested_datetime) = "'.$start_date.'"';
			}else{
			  $sWhere = $sWhere.' AND DATE(vl.sample_tested_datetime) >= "'.$start_date.'" AND DATE(vl.sample_tested_datetime) <= "'.$end_date.'"';
			  $tWhere = $tWhere.' AND DATE(vl.sample_tested_datetime) >= "'.$start_date.'" AND DATE(vl.sample_tested_datetime) <= "'.$end_date.'"';
			}
		}
		if(isset($_POST['lab']) && trim($_POST['lab'])!= ''){
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
	    //echo $sQuery;die;
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
	
        if(count($sResult) > 0){
	    foreach ($sResult as $aRow) {
		$row = array();
		$row[] = ucwords($aRow['facility_state']);
		$row[] = ucwords($aRow['facility_district']);
		$row[] = ucwords($aRow['facility_name']);
		$row[] = $aRow['facility_code'];
		$row[] = $aRow['rejections'];
		$row[] = $aRow['lt14lt1000'];
		$row[] = $aRow['lt14gt1000'];
		$row[] = $aRow['gt14lt1000M'];
		$row[] = $aRow['gt14gt1000M'];			
		$row[] = $aRow['gt14lt1000F'];
		$row[] = $aRow['gt14gt1000F'];
		$row[] = $aRow['preglt1000'];
		$row[] = $aRow['preggt1000'];
		$row[] = $aRow['ult1000'];
		$row[] = $aRow['ugt1000'];
		$row[] = $aRow['totalLessThan1000'];
		$row[] = $aRow['totalGreaterThan1000'];
		$row[] = $aRow['total'];
		$output['aaData'][] = $row;
	    }
	}else{
	    $row = array();
	    $row[] = '-';
	    $row[] = '-';
	    $row[] = '-';
	    $row[] = '-';
	    $row[] = 0;
	    $row[] = 0;
	    $row[] = 0;
	    $row[] = 0;
	    $row[] = 0;			
	    $row[] = 0;
	    $row[] = 0;
	    $row[] = 0;
	    $row[] = 0;
	    $row[] = 0;
	    $row[] = 0;
	    $row[] = 0;
	    $row[] = 0;
	    $row[] = 0;
	    $output['aaData'][] = $row;
	}
        
    echo json_encode($output);
?>