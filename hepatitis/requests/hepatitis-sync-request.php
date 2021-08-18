<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
try {
    $general = new \Vlsm\Models\General($db);
    if (!isset($systemConfig['remoteURL']) || $systemConfig['remoteURL'] == '') {
        echo "Please check your Remote URL";
        die;
    }
    $systemConfig['remoteURL'] = rtrim($systemConfig['remoteURL'], "/");

    $arr = $general->getGlobalConfig();
    $sarr = $general->getSystemConfig();

    //get remote data
    if (empty($sarr['sc_testing_lab_id'])) {
        echo "No Lab ID set in System Config";
        exit(0);
    }

    $request = array();
    $url = $systemConfig['remoteURL'] . '/remote/remote/hepatitis-add-requests.php';

    $sQuery = "SELECT * FROM form_hepatitis as vl WHERE hepatitis_id";
    $hepatitisData = $db->query("SELECT * FROM form_hepatitis as vl WHERE hepatitis_id = " . base64_decode($_POST['hepatitis']));
    if (!empty($hepatitisData) && count($hepatitisData) > 0) {
        $forms = array();
        foreach ($hepatitisData as $row) {
            $forms[] = $row['hepatitis_id'];
        }

        $data = array();
        $data['hepatitisData'] = $hepatitisData;

        $data = array(
            'labName' => $sarr['sc_testing_lab_id'],
            'module' => 'hepatitis',
            'data' => $data,
        );

        //open connection
        $ch = curl_init($url);
        $json_data = json_encode($data);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($json_data)
            )
        );
        // execute post
        $curl_response = curl_exec($ch);

        //close connection
        curl_close($ch);
        $apiData = json_decode($curl_response, true);
        if (isset($apiData) && sizeof($apiData) > 0) {
            $db->where('hepatitis_id', $forms, 'IN');
            if (!$db->update('form_hepatitis', array('data_sync' => 1)))
                error_log('update failed: ' . $db->getLastError());
            echo "Sample synced successfully";
        } else {
            echo "Sample not synced. Please try again later";
        }
    }
} catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}
