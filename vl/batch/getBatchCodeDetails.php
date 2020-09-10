<?php
#require_once('../../startup.php');
include_once(APPLICATION_PATH . '/includes/MysqliDb.php');
//include_once(APPLICATION_PATH . '/models/General.php');
$tableName = "batch_details";
$primaryKey = "batch_id";
$configQuery = "SELECT `value` FROM global_config WHERE name ='vl_form'";
$configResult = $db->query($configQuery);

if (isset($_POST['type']) && $_POST['type'] == 'vl') {
    $refTable = "vl_request_form";
    $refPrimaryColumn = "vl_sample_id";
    $editFileName = 'editBatch.php';
    $editPositionFileName = 'editBatchControlsPosition.php';
} else if (isset($_POST['type']) && $_POST['type'] == 'eid') {
    $refTable = "eid_form";
    $refPrimaryColumn = "eid_id";
    $editFileName = 'eid-edit-batch.php';
    $editPositionFileName = 'eid-edit-batch-position.php';
} else if (isset($_POST['type']) && $_POST['type'] == 'covid19') {
    $refTable = "form_covid19";
    $refPrimaryColumn = "covid19_id";
    $editFileName = 'covid-19-edit-batch.php';
    $editPositionFileName = 'covid-19-edit-batch-position.php';
}


$general = new \Vlsm\Models\General($db);
/* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
        */

$aColumns = array('b.batch_code', "DATE_FORMAT(b.request_created_datetime,'%d-%b-%Y %H:%i:%s')");
$orderColumns = array('b.batch_code', '', '', '', '', '', 'b.request_created_datetime');

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
				" . ($_POST['sSortDir_' . $i]) . ", ";
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
                $sWhereSub .= $aColumns[$i] . " LIKE '%" . ($search) . "%' OR ";
            } else {
                $sWhereSub .= $aColumns[$i] . " LIKE '%" . ($search) . "%' ";
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

$sQuery = "SELECT b.request_created_datetime ,b.batch_code, b.batch_id,count(vl.sample_code) as sample_code FROM $refTable vl right join batch_details b on vl.sample_batch_id = b.batch_id";
if (isset($sWhere) && $sWhere != "") {
    $sWhere = ' where ' . $sWhere;
    $sWhere = $sWhere . 'AND vl.vlsm_country_id ="' . $configResult[0]['value'] . '"';
} else {
    $sWhere = ' where vl.vlsm_country_id ="' . $configResult[0]['value'] . '"';
}
$sQuery = $sQuery . ' ' . $sWhere;
$sQuery = $sQuery . ' group by b.batch_id';
if (isset($sOrder) && $sOrder != "") {
    $sOrder = preg_replace('/(\v|\s)+/', ' ', $sOrder);
    $sQuery = $sQuery . ' order by ' . $sOrder;
}

if (isset($sLimit) && isset($sOffset)) {
    $sQuery = $sQuery . ' LIMIT ' . $sOffset . ',' . $sLimit;
}
//die($sQuery);
//echo $sQuery;die;
$rResult = $db->rawQuery($sQuery);
// print_r($rResult);
/* Data set length after filtering */

$aResultFilterTotal = $db->rawQuery("SELECT b.request_created_datetime, b.batch_code, b.batch_id,count(vl.sample_code) as sample_code from $refTable vl right join batch_details b on vl.sample_batch_id = b.batch_id  $sWhere group by b.batch_id order by $sOrder");
$iFilteredTotal = count($aResultFilterTotal);

/* Total data set length */
$aResultTotal =  $db->rawQuery("SELECT b.request_created_datetime, b.batch_code, b.batch_id,count(vl.sample_code) as sample_code from $refTable vl right join batch_details b on vl.sample_batch_id = b.batch_id where vl.vlsm_country_id ='" . $configResult[0]['value'] . "' group by b.batch_id");
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
$batch = false;
if (isset($_SESSION['privileges']) && (in_array($editFileName, $_SESSION['privileges']))) {
    $batch = true;
}

foreach ($rResult as $aRow) {
    $humanDate = "";
    if (trim($aRow['request_created_datetime']) != "" && $aRow['request_created_datetime'] != '0000-00-00 00:00:00') {
        $date = $aRow['request_created_datetime'];
        $humanDate =  date("d-M-Y H:i:s", strtotime($date));
    }
    //get no. of samplesa have result.
    $noOfSampleHaveResult = "select count(vl.sample_code) as no_of_sample_have_result from vl_request_form as  vl where vl.sample_batch_id='" . $aRow['batch_id'] . "' and vl.result!=''";
    $noOfSampleHaveResultCount = $db->rawQuery($noOfSampleHaveResult);
    //get no. of sample tested.
    $noOfSampleTested = "select count(vl.sample_code) as no_of_sample_tested from vl_request_form as  vl where vl.sample_batch_id='" . $aRow['batch_id'] . "' and vl.result_status=7";
    $noOfSampleResultCount = $db->rawQuery($noOfSampleTested);
    //error_log($noOfSampleTested);
    //get no. of sample tested low level.
    $noOfSampleLowTested = "select count(vl.sample_code) as no_of_sample_low_tested from vl_request_form as  vl where vl.sample_batch_id='" . $aRow['batch_id'] . "' AND vl.result < 1000";
    $noOfSampleLowResultCount = $db->rawQuery($noOfSampleLowTested);
    //get no. of sample tested high level.
    $noOfSampleHighTested = "select count(vl.sample_code) as no_of_sample_high_tested from vl_request_form as  vl where vl.sample_batch_id='" . $aRow['batch_id'] . "' AND vl.result > 1000";
    $noOfSampleHighResultCount = $db->rawQuery($noOfSampleHighTested);
    //get no. of sample tested high level.
    $noOfSampleLastDateTested = "select max(vl.sample_tested_datetime) as last_tested_date from vl_request_form as  vl where vl.sample_batch_id='" . $aRow['batch_id'] . "'";
    $noOfSampleLastDateTested = $db->rawQuery($noOfSampleLastDateTested);

    $row = array();
    $printBarcode = '<a href="/vl/batch/generateBarcode.php?id=' . base64_encode($aRow['batch_id']) . '&type=' . $_POST['type'] . '" target="_blank" class="btn btn-info btn-xs" style="margin-right: 2px;" title="Print bar code"><i class="fa fa-barcode"> Print Barcode</i></a>';
    $printQrcode = '<a href="javascript:void(0);" class="btn btn-info btn-xs" style="margin-right: 2px;" title="Print qr code" onclick="generateQRcode(\'' . base64_encode($aRow['batch_id']) . '\');"><i class="fa fa-qrcode"> Print QR code</i></a>';
    $editPosition = '<a href="' . $editPositionFileName . '?id=' . base64_encode($aRow['batch_id']) . '" class="btn btn-default btn-xs" style="margin-right: 2px;margin-top:6px;" title="Edit Position"><i class="fa fa-sort-numeric-desc"> Edit Position</i></a>';

    $deleteBatch = '';
    if ($aRow['sample_code'] == 0 || $noOfSampleHaveResultCount[0]['no_of_sample_have_result'] == 0) {
        $deleteBatch = '<a href="javascript:void(0);" class="btn btn-danger btn-xs" style="margin-right: 2px;margin-top:6px;" title="" onclick="deleteBatchCode(\'' . base64_encode($aRow['batch_id']) . '\',\'' . $aRow['batch_code'] . '\');"><i class="fa fa-times"> Delete</i></a>';
    }

    $date = '';
    if ($noOfSampleLastDateTested[0]['last_tested_date'] != '0000-00-00 00:00:00' && $noOfSampleLastDateTested[0]['last_tested_date'] != null) {
        $exp = explode(" ", $noOfSampleLastDateTested[0]['last_tested_date']);
        $date = $general->humanDateFormat($exp[0]);
    }
    $row[] = ucwords($aRow['batch_code']);
    $row[] = $aRow['sample_code'];
    $row[] = $noOfSampleResultCount[0]['no_of_sample_tested'];
    $row[] = $noOfSampleLowResultCount[0]['no_of_sample_low_tested'];
    $row[] = $noOfSampleHighResultCount[0]['no_of_sample_high_tested'];
    $row[] = $date;
    $row[] = $humanDate;
    //    $row[] = '<select class="form-control" name="status" id=' . $aRow['batch_id'] . ' title="Please select status" onchange="updateStatus(this.id,this.value)">
    //		    <option value="pending" ' . ($aRow['batch_status'] == "pending" ? "selected=selected" : "") . '>Pending</option>
    //		    <option value="completed" ' . ($aRow['batch_status'] == "completed" ? "selected=selected" : "") . '>Completed</option>
    //	    </select>';
    if (isset($_POST['fromSource']) && $_POST['fromSource'] == 'qr') {
        $row[] = $printQrcode;
    } else {
        if ($batch) {
            $row[] = '<a href="' . $editFileName . '?id=' . base64_encode($aRow['batch_id']) . '" class="btn btn-primary btn-xs" style="margin-right: 2px;" title="Edit"><i class="fa fa-pencil"> Edit</i></a>&nbsp;' . $printBarcode . '&nbsp;' . $editPosition . '&nbsp;' . $deleteBatch;
        }
    }
    $output['aaData'][] = $row;
}
echo json_encode($output);
