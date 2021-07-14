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
    if (empty($sarr['lab_name'])) {
        echo "No Lab ID set in System Config";
        exit(0);
    }

    $request = array();
    $url = $systemConfig['remoteURL'] . '/remote/remote/covid-19-add-requests.php';

    $sQuery = "SELECT * FROM form_covid19 as vl WHERE covid19_id";
    $c19Data = $db->query("SELECT * FROM form_covid19 as vl WHERE covid19_id = " . base64_decode($_POST['c19']));
    if (!empty($c19Data) && count($c19Data) > 0) {
        $forms = array();
        foreach ($c19Data as $row) {
            $forms[] = $row['covid19_id'];
        }

        $covid19Obj = new \Vlsm\Models\Covid19($db);
        $symptoms = $covid19Obj->getCovid19SymptomsByFormId($forms);
        $comorbidities = $covid19Obj->getCovid19ComorbiditiesByFormId($forms);
        $testResults = $covid19Obj->getCovid19TestsByFormId($forms);

        $data = array();
        $data['c19Data'] = $c19Data;
        $data['symptoms'] = $symptoms;
        $data['comorbidities'] = $comorbidities;
        $data['testResults'] = $testResults;
        
        $db->where('covid19_id', $forms, 'IN');
        if (!$db->update('form_covid19', array('data_sync' => 1)))
            error_log('update failed: ' . $db->getLastError());

        $data = array(
            'labName' => $sarr['lab_name'],
            'module' => 'covid19',
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
        if(isset($apiData) && sizeof($apiData) > 0){
            echo "Sample Synced to remote.";
        }else{
            echo "Sample Not Synced please try again later.";
        }
    }
} catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}
