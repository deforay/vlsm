<?php
session_start();
include('../includes/MysqliDb.php');
include('../General.php');
$tableName="vl_request_form";
$primaryKey="vl_sample_id";
$configQuery="SELECT value FROM global_config WHERE name ='vl_form'";
$configResult=$db->query($configQuery);
$general=new Deforay_Commons_General();
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
        */
        
        $aColumns = array('vl.serial_no',"DATE_FORMAT(vl.request_created_datetime,'%d-%b-%Y %H:%i:%s')");
        $orderColumns = array('vl.serial_no','','','','','vl.request_created_datetime');
		
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
        
        $sQuery="select vl.vl_sample_id,vl.sample_code,vl.sample_tested_datetime,vl.result,vl.request_created_datetime from vl_request_form as vl";
        if (isset($sWhere) && $sWhere != "") {
            $sWhere=' where '.$sWhere;
            $sWhere= $sWhere. 'AND vl.vlsm_country_id ="'.$configResult[0]['value'].'"';
        }else{
            $sWhere=' where vl.vlsm_country_id ="'.$configResult[0]['value'].'"';
        }
        if(isset($_POST['sampleCode']) && trim($_POST['sampleCode'])!='')
        {
            $sWhere.=" AND vl_sample_id IN (".$_POST['sampleCode'].")";
        }
        $sQuery = $sQuery.' '.$sWhere;
        if (isset($sOrder) && $sOrder != "") {
            $sOrder = preg_replace('/(\v|\s)+/', ' ', $sOrder);
            $sQuery = $sQuery.' order by '.$sOrder;
        }
        
        if (isset($sLimit) && isset($sOffset)) {
            $sQuery = $sQuery.' LIMIT '.$sOffset.','. $sLimit;
        }
       //echo $sQuery;die;
        $rResult = $db->rawQuery($sQuery);
        /* Data set length after filtering */
        $aResultFilterTotal =$db->rawQuery("select vl.vl_sample_id,vl.sample_code,vl.sample_tested_datetime,vl.result from vl_request_form vl $sWhere order by $sOrder");
        $iFilteredTotal = count($aResultFilterTotal);

        /* Total data set length */
        $aResultTotal =  $db->rawQuery("select vl.vl_sample_id,vl.sample_code,vl.sample_tested_datetime,vl.result from vl_request_form vl where vl.vlsm_country_id ='".$configResult[0]['value']."'");
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
	    $sampleTestedDate = "";
	    $requestCreatedDate="";
            if($aRow['sample_tested_datetime']!='0000-00-00 00:00:00' && $aRow['sample_tested_datetime']!=null && trim($aRow['sample_tested_datetime'])!= ''){
              $exp = explode(" ",$aRow['sample_tested_datetime']);
              $sampleTestedDate = $general->humanDateFormat($exp[0]);
            }
	    if($aRow['request_created_datetime']!='0000-00-00 00:00:00' && $aRow['request_created_datetime']!= null && trim($aRow['request_created_datetime'])!=""){
              $requestCreatedDate =  date("d-M-Y H:i:s",strtotime($aRow['request_created_datetime']));
            }
	    $row = array();
            $row[] = $aRow['sample_code'];
            $row[] = (trim($sampleTestedDate)!= '')?"Yes":"No";
            $row[] = ($aRow['result']!= null && trim($aRow['result'])!='' && $aRow['result']<1000)?"Yes":"No";
            $row[] = ($aRow['result']!= null && trim($aRow['result'])!='' && $aRow['result']>=1000)?"Yes":"No";
            $row[] = $sampleTestedDate;
            $row[] = $requestCreatedDate;
            $row[] = '<a href="javascript:void(0);" class="btn btn-info btn-xs" style="margin-right: 2px;" title="Print qr code" onclick="generateQRcode(\''.$aRow['vl_sample_id'].'\');"><i class="fa fa-qrcode"> Print QR code</i></a>';
            $output['aaData'][] = $row;
        }
        echo json_encode($output);
?>