<?php

ini_set('memory_limit', -1);

require_once(__DIR__ . "/../../startup.php");




$general = new \Vlsm\Models\General($db);
$lastUpdate = null;
$output = array();

$tndArray = array(
    'target not detected',
    'tnd',
    '<20',
    '< 20',
    '<40',
    '< 40',
    '< 800',
    '<800',
    'bdl',
    'below detection limit',
    'suppressed',
);

try {


    $instanceUpdateOn = $db->getValue('s_vlsm_instance', 'vl_last_dash_sync');

    if (!empty($instanceUpdateOn)) {
        $db->where('last_modified_datetime', $instanceUpdateOn, ">=");
    }

    $db->orderBy("last_modified_datetime", "ASC");

    $rResult = $db->get('vl_request_form', 5000);

    if (empty($rResult)) {
        exit(0);
    }

    $lastUpdate = $rResult[count($rResult) - 1]['last_modified_datetime'];

    foreach ($rResult as $aRow) {

        $VLAnalysisResult = $aRow['result_value_absolute'];

        if (in_array(strtolower($aRow['result_value_text']), $tndArray)) {
            $VLAnalysisResult = 20;
        }

        if (empty($VLAnalysisResult)) {
            $DashVL_Abs = 0;
            $DashVL_AnalysisResult = NULL;
        } else if ($VLAnalysisResult < 1000) {
            $DashVL_AnalysisResult = 'Suppressed';
            $DashVL_Abs = $VLAnalysisResult;
        } else if ($VLAnalysisResult >= 1000) {
            $DashVL_AnalysisResult = 'Not Suppressed';
            $DashVL_Abs = $VLAnalysisResult;
        }
        if (!empty($aRow['remote_sample_code'])) {
            if (!empty($aRow['sample_code'])) {
                $aRow['sample_code']      = $aRow['remote_sample_code'] . '-' . $aRow['sample_code'];
            } else {
                $aRow['sample_code']      = $aRow['remote_sample_code'];
            }
        }

        $aRow['DashVL_Abs'] = $DashVL_Abs;
        $aRow['DashVL_AnalysisResult'] = $DashVL_AnalysisResult;
        $output[] = $aRow;
    }

    $currentDate = date('d-m-y-h-i-s');


    $filename = 'export-vl-result-' . $currentDate . '.json';
    $fp = fopen(TEMP_PATH . DIRECTORY_SEPARATOR . $filename, 'w');
    fwrite($fp, json_encode($output));
    fclose($fp);


    $vldashboardUrl = $general->getGlobalConfig('vldashboard_url');
    $vldashboardUrl = rtrim($vldashboardUrl, "/");


    //$vldashboardUrl = "http://vldashboard";

    $apiUrl = $vldashboardUrl . "/api/vlsm";
    //error_log($apiUrl);
    //$apiUrl.="?key_identity=XXX&key_credential=YYY";


    $data = [];
    $data['api-version'] = 'v2';
    $data['vlFile'] = new CURLFile(TEMP_PATH . DIRECTORY_SEPARATOR . $filename, 'application/json', $filename);

    $options = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS => $data,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_HTTPHEADER => ['Content-Type: multipart/form-data']
    ];

    $ch = curl_init($apiUrl);
    curl_setopt_array($ch, $options);
    $result = curl_exec($ch);
    curl_close($ch);

    //var_dump($result);
    $deResult = json_decode($result, true);

    if (isset($deResult['status']) && trim($deResult['status']) == 'success') {
        $data = array(
            'vl_last_dash_sync' => (!empty($lastUpdate) ? $lastUpdate : $general->getDateTime())
        );
        
        $db->update('s_vlsm_instance', $data);
    }
    $general->removeDirectory(TEMP_PATH . DIRECTORY_SEPARATOR . $filename);
} catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}
