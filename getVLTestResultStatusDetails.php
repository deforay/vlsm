<?php
session_start();
include('./includes/MysqliDb.php');
include('General.php');
$general=new Deforay_Commons_General();
$tableName="vl_request_form";
$primaryKey="vl_sample_id";

        /* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
        */
        $aColumns = array('vl.serial_no',"DATE_FORMAT(vl.sample_collection_date,'%d-%b-%Y')",'b.batch_code','vl.art_no','vl.patient_name','f.facility_name','f.state','f.district','s.sample_name','vl.result');
        $orderColumns = array('vl.serial_no','vl.sample_collection_date','b.batch_code','vl.art_no','vl.patient_name','f.facility_name','f.state','f.district','s.sample_name','vl.result');
        
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
	$sQuery="SELECT * FROM vl_request_form as vl INNER JOIN testing_status as ts ON ts.status_id=vl.status LEFT JOIN facility_details as f ON vl.facility_id=f.facility_id LEFT JOIN r_sample_type as s ON s.sample_id=vl.sample_id LEFT JOIN r_art_code_details as art ON vl.current_regimen=art.art_id LEFT JOIN batch_details as b ON b.batch_id=vl.batch_id";
	$start_date = '';
	$end_date = '';
	if(isset($_POST['sampleCollectionDate']) && trim($_POST['sampleCollectionDate'])!= ''){
	   $s_c_date = explode("to", $_POST['sampleCollectionDate']);
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
	
        if (isset($sWhere) && $sWhere != "") {
	    $sWhere = $sWhere.' AND vl.status = "'.$_POST['status'].'"';
	}else{
	    $sWhere =' WHERE vl.status = "'.$_POST['status'].'"';
	}
	
	$sQuery = $sQuery.' '.$sWhere;
	$sQuery = $sQuery." ORDER BY vl.modified_on DESC";
        if (isset($sOrder) && $sOrder != "") {
            $sOrder = preg_replace('/(\v|\s)+/', ' ', $sOrder);
            $sQuery = $sQuery.",".$sOrder;
        }
        
        if (isset($sLimit) && isset($sOffset)) {
            $sQuery = $sQuery.' LIMIT '.$sOffset.','. $sLimit;
        }
        
        $rResult = $db->rawQuery($sQuery);
        /* Data set length after filtering */
        $aResultFilterTotal =$db->rawQuery("SELECT vl.vl_sample_id,vl.facility_id,vl.patient_name,vl.result,f.facility_name,f.facility_code,vl.art_no,s.sample_name,b.batch_code,vl.batch_id,ts.status_name FROM vl_request_form as vl INNER JOIN testing_status as ts ON ts.status_id=vl.status LEFT JOIN facility_details as f ON vl.facility_id=f.facility_id  LEFT JOIN r_sample_type as s ON s.sample_id=vl.sample_id LEFT JOIN batch_details as b ON b.batch_id=vl.batch_id $sWhere  ORDER BY vl.modified_on DESC, $sOrder");
        $iFilteredTotal = count($aResultFilterTotal);

        /* Total data set length */
        $aResultTotal =  $db->rawQuery("select COUNT(vl_sample_id) as total FROM vl_request_form where status = ".$_POST['status']);
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
        
        foreach ($rResult as $aRow) {
	    if(isset($aRow['sample_collection_date']) && trim($aRow['sample_collection_date'])!= '' && $aRow['sample_collection_date']!= '0000-00-00 00:00:00'){
	       $xplodDate = explode(" ",$aRow['sample_collection_date']);
	       $aRow['sample_collection_date'] = $general->humanDateFormat($xplodDate[0]);
	    }else{
	       $aRow['sample_collection_date'] = '';
	    }
            $row = array();
	    $row[] = $aRow['serial_no'];
	    $row[] = $aRow['sample_collection_date'];
	    $row[] = $aRow['batch_code'];
	    $row[] = $aRow['art_no'];
            $row[] = ucwords($aRow['patient_name']).' '.ucwords($aRow['surname']);
	    $row[] = ucwords($aRow['facility_name']);
	    $row[] = ucwords($aRow['state']);
	    $row[] = ucwords($aRow['district']);
            $row[] = ucwords($aRow['sample_name']);
            $row[] = $aRow['result'];
            $output['aaData'][] = $row;
        }
        
        echo json_encode($output);
?>