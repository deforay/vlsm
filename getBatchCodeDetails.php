<?php
include('./includes/MysqliDb.php');
$tableName="batch_details";
$primaryKey="batch_id";

        /* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
        */
        
        $aColumns = array('b.batch_code',"DATE_FORMAT(b.created_on,'%d-%b-%Y %H:%i:%s')",'b.batch_status');
        $orderColumns = array('b.batch_code','','b.created_on','b.batch_status');
		
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
        
		$sQuery="select b.created_on ,b.batch_status,b.batch_code, b.batch_id,count(vl.sample_code) as sample_code from vl_request_form vl right join batch_details b on vl.batch_id = b.batch_id";
        
        if (isset($sWhere) && $sWhere != "") {
            $sWhere=' where '.$sWhere;
            $sQuery = $sQuery.' '.$sWhere;
        }
        $sQuery = $sQuery.' group by b.batch_id';
        if (isset($sOrder) && $sOrder != "") {
            $sOrder = preg_replace('/(\v|\s)+/', ' ', $sOrder);
            $sQuery = $sQuery.' order by '.$sOrder;
        }
        
        if (isset($sLimit) && isset($sOffset)) {
            $sQuery = $sQuery.' LIMIT '.$sOffset.','. $sLimit;
        }
       //die($sQuery);
       //echo $sQuery;die;
        $rResult = $db->rawQuery($sQuery);
       // print_r($rResult);
        /* Data set length after filtering */
        
        $aResultFilterTotal =$db->rawQuery("select b.created_on,b.batch_status, b.batch_code, b.batch_id,count(vl.sample_code) as sample_code from vl_request_form vl right join batch_details b on vl.batch_id = b.batch_id  $sWhere group by b.batch_id order by $sOrder");
        $iFilteredTotal = count($aResultFilterTotal);

        /* Total data set length */
        $aResultTotal =  $db->rawQuery("select b.created_on,b.batch_status, b.batch_code, b.batch_id,count(vl.sample_code) as sample_code from vl_request_form vl right join batch_details b on vl.batch_id = b.batch_id group by b.batch_id");
       // $aResultTotal = $countResult->fetch_row();
       //print_r($aResultTotal);
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
	
        foreach ($rResult as $aRow) {
			$humanDate="";
			if(trim($aRow['created_on'])!="" && $aRow['created_on']!='0000-00-00 00:00:00'){
				$date = $aRow['created_on'];
				$humanDate =  date("d-M-Y H:i:s",strtotime($date));
			}
        $row = array();
	    $printBarcode='<a href="javascript:void(0);" class="btn btn-info btn-xs" style="margin-right: 2px;" title="View" onclick="generateBarcode(\''.base64_encode($aRow['batch_id']).'\');"><i class="fa fa-file-pdf-o"> Print Barcode</i></a>';
	    $row[] = ucwords($aRow['batch_code']);
	    $row[] = $aRow['sample_code'];
	    $row[] = $humanDate;
	    $row[] = '<select class="form-control" name="status" id=' . $aRow['batch_id'] . ' title="Please select status" onchange="updateStatus(this.id,this.value)">
			    <option value="pending" ' . ($aRow['batch_status'] == "pending" ? "selected=selected" : "") . '>Pending</option>
			    <option value="completed" ' . ($aRow['batch_status'] == "completed" ? "selected=selected" : "") . '>Completed</option>
		    </select>';
	    
            $row[] = '<a href="editBatch.php?id=' . base64_encode($aRow['batch_id']) . '" class="btn btn-primary btn-xs" style="margin-right: 2px;" title="Edit"><i class="fa fa-pencil"> Edit</i></a>'.$printBarcode;
            $output['aaData'][] = $row;
        }
        echo json_encode($output);
?>