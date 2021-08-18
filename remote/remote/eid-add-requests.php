<?php
//this file is get the data from remote db
$apiResult = json_decode(file_get_contents('php://input'), true);
include(dirname(__FILE__) . "/../../startup.php");
$general = new \Vlsm\Models\General($db);

if ($apiResult['module'] == 'eid') {

    $removeKeys = array(
        'eid_id',
        'sample_batch_id',
    );
    $eidData = array();
    if (!empty($apiResult['data']) && is_array($apiResult['data']) && count($apiResult['data']) > 0) {
        $allColumns = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS where TABLE_SCHEMA = '" . $systemConfig['dbName'] . "' AND table_name='eid_form'";
        $allColResult = $db->rawQuery($allColumns);
        $columnList = array_map('current', $allColResult);
        $columnList = array_diff($columnList, $removeKeys);
        foreach ($apiResult['data']['eidData'] as $key => $labData) {
            $request = array();
            $eidId = $labData['eid_id'];
            foreach ($columnList as $colName) {
                if (isset($labData[$colName])) {
                    $request[$colName] = $labData[$colName];
                } else {
                    $request[$colName] = null;
                }
            }

            $request['last_modified_datetime'] = $general->getDateTime();
            //check exist remote
            $sampleCode = (isset($labData['remote_sample_code']) && $labData['remote_sample_code'] != "") ? $labData['remote_sample_code'] : $labData['sample_code'];
            $exsvlQuery = "SELECT eid_id,sample_code FROM eid_form AS vl WHERE (remote_sample_code='" . $sampleCode . "' OR sample_code='" . $sampleCode . "')";

            $exsvlResult = $db->query($exsvlQuery);
            if ($exsvlResult) {

                $dataToUpdate = array();
                $dataToUpdate['sample_package_code'] = $request['sample_package_code'];
                $dataToUpdate['sample_package_id'] = $request['sample_package_id'];

                $db = $db->where('eid_id', $exsvlResult[0]['eid_id']);
                $db->update('eid_form', $dataToUpdate);
                $updateId  = $exsvlResult[0]['eid_id'];
                $insertId = $exsvlResult[0]['eid_id'];
                if ($updateId > 0) {
                    $eidData['update'][] = (isset($labData['remote_sample_code']) && $labData['remote_sample_code'] != "") ? $labData['remote_sample_code'] : $labData['sample_code'];
                }
            } else {
                if (!empty($request['sample_collection_date'])) {
                    $request['request_created_by'] = 0;
                    $request['last_modified_by'] = 0;
                    $request['request_created_datetime'] = $general->getDateTime();
                    $request['data_sync'] = 0;
                    $db->insert('eid_form', $request);
                    $insertId = $db->getInsertId();
                    if ($insertId > 0) {
                        $eidData['insert'][] = (isset($labData['remote_sample_code']) && $labData['remote_sample_code'] != "") ? $labData['remote_sample_code'] : $labData['sample_code'];
                    }
                }
            }
        }
    }
    echo json_encode($eidData);
}
