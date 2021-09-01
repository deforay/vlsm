<?php

// this file is included in /hepatitis/interop/dhis2/hepatitis-receive.php

$counter = 0;

$data[] = "lastUpdatedDuration=60000m";
$data[] = "ou=WItSrsnhLXI"; // Rwanda
$data[] = "ouMode=DESCENDANTS";
$data[] = "program=nZRqRmZvdJd";
$data[] = "fields=attributes[attribute,code,value],enrollments[*],orgUnit,trackedEntityInstance";
$data[] = "paging=false";

$url = "/api/trackedEntityInstances.json";

$response = $dhis2->get($url, $data);
//echo($response);die;
$response = json_decode($response, true);

//echo "<pre>";var_dump($response);"</pre>";die;

$dhis2GenderOptions = array('1' => 'male', '2' => 'female');
$dhis2SocialCategoryOptions = array('1' => 'A', '2' => 'B', '3' => 'C', '4' => 'D');
$dhis2VlTestReasonOptions = array('Initial Viral Load Test' => 'Initial HBV VL', 'HBV Follow-up Test' => 'Follow up HBV VL', 'SVR12 HCV Viral Load Test' => 'SVR12 HCV VL');

$attributesDataElementMapping = [
    'BrnMehToEvL' => 'external_sample_code', //dhis2 case id
    //'BrnMehToEvL' => 'patient_id',
    'AMW9BiX1by6' => 'patient_province',
    'DAnpMxi3WXw' => 'patient_district',
    //'' => 'patient_city',
    'bJWVdRUHuJE' => 'patient_occupation',
    'VEmNYrYrHd5' => 'patient_marital_status',
    'uj19ud2MGLp' => 'patient_phone_number',
    //'' => 'patient_insurance',
    'OQFenB9rqYX' => 'patient_name',
    'zGTHEMwHv5K' => 'patient_dob',
    'FAjryqDCKk4' => 'social_category',
    'fK0WSCeiocf' => 'patient_gender',
    //'' => 'patient_nationality'
];




$eventsDataElementMapping = [
    'Qu4LXThGcZa' => 'sample_collection_date',
    'kpRGgnpBg0o' => 'hbsag_result',
    'bZz6gdQ8VKK' => 'anti_hcv_result',
    'Ggd5bSi74kC' => 'hbv_vl_count',
    'KqH0EkWPGvR' => 'hcv_vl_count',
    'nLywSrtrjT3' => 'hepatitis_test_type',
    'SaHBNmmUcqd' => 'lab_id',
    'mXzNFIK76ah' => 'reason_for_vl_test'
];


// echo ("<h5>...</h5>");
// echo ("<h5>...</h5>");
// echo ("<h3>Total trackers received from DHIS2 : " . count($response['trackedEntityInstances']) . "</h3>");

foreach ($response['trackedEntityInstances'] as $tracker) {

    //if ($tracker['enrollments'][0]['status'] == 'COMPLETED') continue;

    //     echo "<pre>";
    //     var_dump($tracker);
    //     echo "</pre>";
    //    continue;

    $formData = array();
    $facility = $tracker['orgUnit'];
    $formData['source_of_request'] = "dhis2-" . $tracker['trackedEntityInstance'];
    $formData['source_data_dump'] = json_encode($tracker);

    foreach ($tracker['attributes'] as $trackerAttr) {
        if (empty($attributesDataElementMapping[$trackerAttr['attribute']])) continue;
        //echo $attributesDataElementMapping[$trackerAttr['attribute']] . "%%%%%%%" . $trackerAttr['value'] . PHP_EOL . PHP_EOL;
        $formData[$attributesDataElementMapping[$trackerAttr['attribute']]] = $trackerAttr['value'];
    }



    //$formData['patient_province'] = $_SESSION['DHIS2_HEP_PROVINCES'][$formData['patient_province']];
    //$formData['patient_district'] = $_SESSION['DHIS2_HEP_DISTRICTS'][$formData['patient_district']];

    //echo "<pre>";var_dump(array_keys($tracker['enrollments']));echo "</pre>";;
    //echo "<pre>";var_dump(($tracker['enrollments']));echo "</pre>";
    foreach ($tracker['enrollments'] as $allEnrollments) {

        $enrollmentDate = explode("T", $allEnrollments['enrollmentDate']);
        $enrollmentDate = $enrollmentDate[0];

        foreach ($allEnrollments['events'] as $enrollmentEvent) {


            foreach ($enrollmentEvent['dataValues'] as $dV) {
                if (empty($eventsDataElementMapping[$dV['dataElement']])) continue;
                // echo "<h1>". $eventsDataElementMapping[$dV['dataElement']] . "</h1>";
                //echo "<pre>"; var_dump($dV['dataElement']);echo "</pre>";
                //echo "<pre>"; var_dump($dV['value']);echo "</pre>";
                //echo "<h1>". $eventsDataElementMapping[$dV['value']] . "</h1>";
                //echo "<pre>"; var_dump($dV);echo "</pre>";
                $formData[$eventsDataElementMapping[$dV['dataElement']]] = $dV['value'];
                //echo "<h1>". $formData[$eventsDataElementMapping[$dV['dataElement']]] . "</h1>";

            }
        }
    }

    $db->where("test_reason_name", $formData['reason_for_hepatitis_test']);
    $reason = $db->getOne("r_hepatitis_test_reasons");
    $formData['reason_for_hepatitis_test'] = $reason['test_reason_id'];

    if (!empty($formData['patient_nationality'])) {

        $db->where("iso3", $formData['patient_nationality']);
        $country = $db->getOne("r_countries");
        $formData['patient_nationality'] = $country['id'];
    }

    $db->where("facility_name", $formData['lab_id']);
    $lab = $db->getOne("facility_details");
    // echo "<pre>";var_dump($formData['lab_id']);echo "</pre>";
    // echo "<pre>";var_dump($lab);echo "</pre>";
    $formData['lab_id'] = $lab['facility_id'];

    $db->where("other_id", $facility);
    $db->orWhere("other_id", $facility);
    $fac = $db->getOne("facility_details");
    $formData['facility_id'] =  $fac['facility_id'];

    $db->where("province_name", $fac['facility_state']);
    $prov = $db->getOne("province_details");

    $formData['province_id'] = !empty($prov['province_id']) ? $prov['province_id'] : 1;


    $formData['specimen_type'] = 1; // Always Whole Blood
    $formData['result_status'] = 6;


    $formData['social_category'] = (!empty($formData['social_category']) ? $dhis2SocialCategoryOptions[$formData['social_category']] : null);
    $formData['patient_gender'] = (!empty($formData['patient_gender']) ? $dhis2GenderOptions[$formData['patient_gender']] : null);
    //$formData['specimen_quality'] = (!empty($formData['specimen_quality']) ? strtolower($formData['specimen_quality']) : null);

    $formData['reason_for_vl_test'] = (!empty($formData['reason_for_vl_test']) ?  $dhis2VlTestReasonOptions[$_SESSION['DHIS2_VL_TEST_REASONS'][$formData['reason_for_vl_test']]] : null);

    $formData['sample_collection_date'] = (!empty($formData['sample_collection_date']) ?  $formData['sample_collection_date'] : $enrollmentDate);
    $formData['reason_for_hepatitis_test'] = (!empty($formData['reason_for_hepatitis_test']) ?  $formData['reason_for_hepatitis_test'] : "Suspect");
    if (isset($formData['hepatitis_test_type']) && stripos($formData['hepatitis_test_type'], "hbv") === FALSE) {
        $formData['hepatitis_test_type'] = "HBV";
    } else {
        $formData['hepatitis_test_type'] = "HCV";
    }

    // echo "<pre>";
    // var_dump($formData);
    // continue;

    $general = new \Vlsm\Models\General($db);
    $hepatitisModel = new \Vlsm\Models\Hepatitis($db);


    $db->where("source_of_request", $formData['source_of_request']);
    $hepatitisData = $db->getOne("form_hepatitis");




    if (empty($hepatitisData) || empty($hepatitisData['hepatitis_id'])) {
        $sampleJson = $hepatitisModel->generateHepatitisSampleCode($formData['hepatitis_test_type'], null, $general->humanDateFormat($formData['sample_collection_date']));

        $sampleData = json_decode($sampleJson, true);

        $formData['sample_code'] = $sampleData['sampleCode'];
        $formData['sample_code_format'] = $sampleData['sampleCodeFormat'];
        $formData['sample_code_key'] = $sampleData['sampleCodeKey'];

        $formData['request_created_by'] = 1;
        $formData['request_created_datetime'] = $general->getDateTime();

        $instanceResult = $db->rawQueryOne("SELECT vlsm_instance_id, instance_facility_name FROM s_vlsm_instance");

        $formData['vlsm_instance_id'] = $instanceResult['vlsm_instance_id'];
        $formData['vlsm_country_id'] = 7; // RWANDA
        $formData['last_modified_datetime'] = $general->getDateTime();
        //echo "<pre>";var_dump($formData);echo "</pre>";
        $id = $db->insert("form_hepatitis", $formData);
        if ($id != false) {
            $counter++;
        }
    } else {
        $db = $db->where('hepatitis_id', $hepatitisData['hepatitis_id']);
        //echo "<pre>";var_dump($formData);echo "</pre>";
        $id = $db->update("form_hepatitis", $formData);
        if ($id != false) {
            $counter++;
        }
    }
    // echo "<pre>";
    // var_dump($formData);
    // echo "</pre>";
}

$response = array('received' => count($response['trackedEntityInstances']), 'processed' => $counter);

echo (json_encode($response));
