<?php
//this file is get the data from remote db
$apiResult = json_decode(file_get_contents('php://input'), true);
include(dirname(__FILE__) . "/../../startup.php");
$general = new \Vlsm\Models\General($db);

if ($apiResult['module'] == 'vl') {

    $removeKeys = array(
        'vl_sample_id',
        'sample_batch_id',
    );
    $vlData = array();
    if (!empty($apiResult['data']) && is_array($apiResult['data']) && count($apiResult['data']) > 0) {
        $allColumns = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS where TABLE_SCHEMA = '" . $systemConfig['dbName'] . "' AND table_name='vl_request_form'";
        $allColResult = $db->rawQuery($allColumns);
        $columnList = array_map('current', $allColResult);
        $columnList = array_diff($columnList, $removeKeys);
        foreach ($apiResult['data']['vlData'] as $key => $labData) {
            $request = array();
            $vlId = $labData['vl_sample_id'];
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
            $exsvlQuery = "SELECT vl_sample_id,sample_code FROM vl_request_form AS vl WHERE (remote_sample_code='" . $sampleCode . "' OR sample_code='" . $sampleCode . "')";

            $exsvlResult = $db->query($exsvlQuery);
            if ($exsvlResult) {

                $dataToUpdate = array();
                $dataToUpdate['sample_package_code'] = $request['sample_package_code'];
                $dataToUpdate['sample_package_id'] = $request['sample_package_id'];

                $db = $db->where('vl_sample_id', $exsvlResult[0]['vl_sample_id']);
                $db->update('vl_request_form', $dataToUpdate);
                $updatvl  = $exsvlResult[0]['vl_sample_id'];
                $insertId = $exsvlResult[0]['vl_sample_id'];
                if ($updatvl > 0) {
                    $vlData['update'][] = (isset($labData['remote_sample_code']) && $labData['remote_sample_code'] != "") ? $labData['remote_sample_code'] : $labData['sample_code'];
                }
            } else {
                if (!empty($request['sample_collection_date'])) {
                    $request['request_created_by'] = 0;
                    $request['last_modified_by'] = 0;
                    $request['request_created_datetime'] = $general->getDateTime();
                    $request['data_sync'] = 0;
                    $db->insert('vl_request_form', $request);
                    $insertId = $db->getInsertId();
                    if ($insertId > 0) {
                        $vlData['insert'][] = (isset($labData['remote_sample_code']) && $labData['remote_sample_code'] != "") ? $labData['remote_sample_code'] : $labData['sample_code'];
                    }
                }
            }
        }
    }
    echo json_encode($vlData);
}
