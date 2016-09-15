<?php
session_start();
include('./includes/MysqliDb.php');
include('General.php');
$general=new Deforay_Commons_General();
$tableName="temp_sample_report";
$primaryKey="temp_sample_id";
$tsQuery="SELECT * FROM testing_status";
$tsResult = $db->rawQuery($tsQuery);
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
        */
        
        $aColumns = array('','tsr.sample_code','tsr.batch_code','tsr.lab_name','tsr.sample_type');
        $orderColumns = array('','tsr.sample_code','tsr.batch_code','tsr.lab_name','tsr.sample_type');
        
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
	$sQuery="SELECT * FROM temp_sample_report as tsr INNER JOIN testing_status as ts ON ts.status_id=tsr.status";
	
        //echo $sQuery;die;
	
	if (isset($sWhere) && $sWhere != "") {
        $sWhere=' where '.$sWhere;
	}
	$sQuery = $sQuery.' '.$sWhere;
        if (isset($sOrder) && $sOrder != "") {
            $sOrder = preg_replace('/(\v|\s)+/', ' ', $sOrder);
            $sQuery = $sQuery.' order by '.$sOrder;
        }else{
	    $sOrder = 'temp_sample_id ASC';
	    $sQuery = $sQuery.' order by '.$sOrder;
	}
        //echo $sQuery;die;
        if (isset($sLimit) && isset($sOffset)) {
            $sQuery = $sQuery.' LIMIT '.$sOffset.','. $sLimit;
        }
        $rResult = $db->rawQuery($sQuery);
        /* Data set length after filtering */
        
        $aResultFilterTotal =$db->rawQuery("SELECT * FROM temp_sample_report as tsr INNER JOIN testing_status as ts ON ts.status_id=tsr.status $sWhere order by $sOrder");
        $iFilteredTotal = count($aResultFilterTotal);

        /* Total data set length */
        $aResultTotal =  $db->rawQuery("select COUNT(temp_sample_id) as total FROM temp_sample_report");
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
	    if(isset($aRow['absolute_value']) && trim($aRow['absolute_value'])!= ''){
		$vlResult = $aRow['absolute_value'];
	    }elseif(isset($aRow['log_value']) && trim($aRow['log_value'])!= ''){
		$vlResult = $aRow['log_value'];
	    }elseif(isset($aRow['text_value']) && trim($aRow['text_value'])!= ''){
		$vlResult = $aRow['text_value'];
	    }
            $row = array();
	    if($aRow['sample_type']=='s' || $aRow['sample_type']=='S'){
		
		if($aRow['sample_details']=='Already Result Exist')
            {
                $color = '<span style="color:#86c0c8;font-weight:bold;"><i class="fa fa-exclamation"></i></span>';
            }
	    if($aRow['sample_details']=='New Sample')
            {
		$color = '<span style="color:#e8000b;font-weight:bold;"><i class="fa fa-exclamation"></i></span>';
            }
	    if($aRow['sample_details']=='')
            {
		$color = '<span style="color:#337ab7;font-weight:bold;"><i class="fa fa-exclamation"></i></span>';
            }
		$row[]='<input type="checkbox" name="chk[]" class="checkTests" id="chk' . $aRow['temp_sample_id'] . '"  value="' . $aRow['temp_sample_id'] . '" onclick="toggleTest(this);"  />';
		$status = '<select class="form-control" style="" name="status" id="'.$aRow['temp_sample_id'].'" title="Please select status" onchange="updateStatus(this.id,this.value)">
				<option value="">--select--</option>
				<option value="7" '.($aRow['status']=="7" ? "selected=selected" : "").'>Accepted</option>
				<option value="1" '.($aRow['status']=="1" ? "selected=selected" : "").'>Hold</option>
				<option value="4" '.($aRow['status']=="4"  ? "selected=selected" : "").'>Rejected</option>
			</select><br><br>';
	    }else{
		$color = '<span style="color:#7d8388;font-weight:bold;"><i class="fa fa-exclamation"></i></span>';
		$row[] = '';
		$status = '';
	    }
	    $row[] = $aRow['sample_code'].$color;
	    $row[] = $aRow['batch_code'];
	    $row[] = ucwords($aRow['lab_name']);
	    $row[] = ucwords($aRow['sample_type']);
	    $row[] = $vlResult;
	    $row[] = $status;
	    
	    $output['aaData'][] = $row;
        }
        
        echo json_encode($output);
?>