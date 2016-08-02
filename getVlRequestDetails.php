<?php
include('./includes/MysqliDb.php');
$tableName="vl_request_form";
$primaryKey="treament_id";

        /* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
        */
        
        $aColumns = array('vl.sample_code','b.batch_code','vl.art_no','vl.patient_name','f.facility_name','f.facility_code','s.sample_name','vl.result');
        
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
                    $sOrder .= $aColumns[intval($_POST['iSortCol_' . $i])] . "
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
        //$sQuery="SELECT vl.treament_id,vl.facility_id,vl.patient_name,f.facility_name,f.facility_code,art.art_code,s.sample_name FROM vl_request_form as vl INNER JOIN facility_details as f ON vl.facility_id=f.facility_id  INNER JOIN r_art_code_details as art ON vl.art_no=art.art_id INNER JOIN r_sample_type as s ON s.sample_id=vl.sample_id";
		$sQuery="SELECT vl.treament_id,vl.facility_id,vl.sample_code,vl.patient_name,vl.result,f.facility_name,f.facility_code,vl.art_no,s.sample_name,b.batch_code,vl.batch_id FROM vl_request_form as vl INNER JOIN facility_details as f ON vl.facility_id=f.facility_id  INNER JOIN r_sample_type as s ON s.sample_id=vl.sample_id LEFT JOIN batch_details as b ON b.batch_id=vl.batch_id";
		
        if (isset($sWhere) && $sWhere != "") {
            $sWhere=' where '.$sWhere;
            $sQuery = $sQuery.' '.$sWhere;
        }
        
        if (isset($sOrder) && $sOrder != "") {
            $sOrder = preg_replace('/(\v|\s)+/', ' ', $sOrder);
            $sQuery = $sQuery.' order by '.$sOrder;
        }
        
        if (isset($sLimit) && isset($sOffset)) {
            $sQuery = $sQuery.' LIMIT '.$sOffset.','. $sLimit;
        }
       //die($sQuery);
      // echo $sQuery;
        $rResult = $db->rawQuery($sQuery);
       // print_r($rResult);
        /* Data set length after filtering */
        
        $aResultFilterTotal =$db->rawQuery("SELECT vl.treament_id,vl.facility_id,vl.patient_name,vl.result,f.facility_name,f.facility_code,vl.art_no,s.sample_name,b.batch_code,vl.batch_id FROM vl_request_form as vl INNER JOIN facility_details as f ON vl.facility_id=f.facility_id  INNER JOIN r_sample_type as s ON s.sample_id=vl.sample_id LEFT JOIN batch_details as b ON b.batch_id=vl.batch_id $sWhere order by $sOrder");
        $iFilteredTotal = count($aResultFilterTotal);

        /* Total data set length */
        $aResultTotal =  $db->rawQuery("select COUNT(treament_id) as total FROM vl_request_form");
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
            $row = array();
	    $row[] = $aRow['sample_code'];
			$row[] = $aRow['batch_code'];
			$row[] = $aRow['art_no'];
            $row[] = ucwords($aRow['patient_name']);
			$row[] = ucwords($aRow['facility_name']);
            $row[] = $aRow['facility_code'];
            $row[] = ucwords($aRow['sample_name']);
            $row[] = ucwords($aRow['result']);
            $row[] = '<a href="editVlRequest.php?id=' . base64_encode($aRow['treament_id']) . '" class="btn btn-primary btn-xs" style="margin-right: 2px;" title="Edit"><i class="fa fa-pencil"> Edit</i></a>
	    <a href="javascript:void(0);" class="btn btn-primary btn-xs" style="margin-right: 2px;" title="View" onclick="convertPdf('.$aRow['treament_id'].');"><i class="fa fa-file-pdf-o"> PDF</i></a>
	    <a href="javascript:void(0);" class="btn btn-success btn-xs" style="margin-right: 2px;" title="Result" onclick="showModal(\'updateVlResult.php?id=' . base64_encode($aRow['treament_id']) . '\',480,380);"> Result</a>';
           
            $output['aaData'][] = $row;
        }
        
        echo json_encode($output);
?>