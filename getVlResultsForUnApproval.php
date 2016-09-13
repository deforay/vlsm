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
        }
        
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
	    if($aRow['sample_code']!=''){
		if($aRow['sample_details']=='Already Result Exist')
            {
                $row['DT_RowClass'] = "exist";
            }
	    if($aRow['sample_details']=='New Sample')
            {
                $row['DT_RowClass'] = "new-add";
            }
	    if($aRow['sample_details']=='')
            {
                $row['DT_RowClass'] = "no-result";
            }
		$row[]='<input type="checkbox" name="chk[]" class="checkTests" id="chk' . $aRow['temp_sample_id'] . '"  value="' . $aRow['temp_sample_id'] . '" onclick="toggleTest(this);"  />';
		$status = '<select class="form-control" style="" name="status" id="'.$aRow['temp_sample_id'].'" title="Please select status" onchange="updateStatus(this.id,this.value)">
				<option value=""> -- Select -- </option>
				<option value="1" '.($aRow['status']=="1" ? "selected=selected" : "").'>Waiting</option>
				<option value="2" '.($aRow['status']=="2" ? "selected=selected" : "").'>Lost</option>
				<option value="3" '.($aRow['status']=="3"  ? "selected=selected" : "").'>Sample Reordered</option>
				<option value="4" '.($aRow['status']=="4"  ? "selected=selected" : "").'>Canceled</option>
				<option value="5" '.($aRow['status']=="5" ? "selected=selected" : "").'>Invalid</option>
				<option value="6" '.($aRow['status']=="6" ? "selected=selected" : "").'>Awaiting Clinic Approval</option>
				<option value="7" '.($aRow['status']=="7" ? "selected=selected" : "").'>Received and Approved</option>
			</select><br><br>';
	    }else{
		$row['DT_RowClass'] = "empty-sample";
		$row[] = '';
		$status = '';
	    }
	    
	    $row[] = $aRow['sample_code'];
	    $row[] = $aRow['batch_code'];
	    $row[] = ucwords($aRow['lab_name']);
	    $row[] = ucwords($aRow['sample_type']);
	    $row[] = $vlResult;
	    $row[] = $status;
	    
	    $output['aaData'][] = $row;
        }
        
        echo json_encode($output);
?>