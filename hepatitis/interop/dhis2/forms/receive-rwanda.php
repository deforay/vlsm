<?php

// this file is included in /covid-19/interop/dhis2/covid-19-receive.php

// echo ("<h5>...</h5>");
// echo ("<h5>...</h5>");
// echo ("<h3>Successfully connected to DHIS2</h3>");

// https://his.rbc.gov.rw/hepatitis/covid19southsudan/api/trackedEntityInstances.json?programStartDate=2020-04-01&programEndDate=2021-04-02&ou=OV9zi20DDXP&ouMode=DESCENDANTS&program=uYjxkTbwRNf&fields=attributes[attribute,code,value],enrollments[*],orgUnit,trackedEntityInstance&paging=false

$counter = 0;

$data[] = "programStartDate=2021-06-01";
$data[] = "programEndDate=2021-06-30";
$data[] = "ou=Hjw70Lodtf2"; // Rwanda
$data[] = "ouMode=DESCENDANTS";
$data[] = "program=LEhPhsbgfFB";
$data[] = "fields=attributes[attribute,code,value],enrollments[*],orgUnit,trackedEntityInstance";
$data[] = "paging=false";

$url = "/api/trackedEntityInstances.json";

$response = $dhis2->get($url, $data);

echo($response);die;

$response = json_decode($response, true);

// district list - https://his.rbc.gov.rw/hepatitis/api/optionSets/HGTWO3xvXRX.json?fields=name,options[:all]
// province list - https://his.rbc.gov.rw/hepatitis/api/optionSets/LqaKTLJFf4H.json?fields=name,options[:all]
// social status - https://his.rbc.gov.rw/hepatitis/api/optionSets/cNhaGfDzbUc.json?fields=name,options[:all]
// test type - https://his.rbc.gov.rw/hepatitis/api/optionSets/uELLf8Z2Fi0.json?fields=name,options[:all]
// gender - https://his.rbc.gov.rw/hepatitis/api/optionSets/zfJUnSL44Eg.json?fields=name,options[:all]
// Testing Labs - https://his.rbc.gov.rw/hepatitis/api/optionSets/qrroYEzTQd3.json?fields=name,options[:all]


$dhis2GenderOptions = array('1' => 'Male', '2' => 'female');

$attributesDataElementMapping = [
    //'' => 'external_sample_code', //dhis2 case id
    'zinPXXTrSmA' => 'patient_id',
    'JtuGgGPsSuZ' => 'patient_province',
    'zf3xIdu7n8v' => 'patient_district',
    'HASxqY0HKma' => 'patient_city',
    'qYpyifGg6Yi' => 'patient_occupation',
    'EEAIP0aO4aR' => 'patient_marital_status',
    'iUkIkQbkxI1' => 'patient_phone_number',
    'BzEcIK9udqH' => 'patient_insurance',
    'p2e195R27TO' => 'patient_name',
    'odAu29pqSvh' => 'patient_dob',
    'DP8JyLEof33' => 'social_category',
    'IeduuuWaWa4' => 'patient_gender',
    'bVXK3FxmU1L' => 'patient_nationality'
];




$eventsDataElementMapping = [
    'qoqX33PK82y' => 'sample_collection_date',
    'Di17rUJDIWZ' => 'hbv_vl_count',
    'Oem0BXNDPWL' => 'hcv_vl_count',
    'Mpc3ftVuSvK' => 'hepatitis_test_type',
    'DMQSNcqWRvI' => 'lab_id'
];


// echo ("<h5>...</h5>");
// echo ("<h5>...</h5>");
// echo ("<h3>Total trackers received from DHIS2 : " . count($response['trackedEntityInstances']) . "</h3>");

foreach ($response['trackedEntityInstances'] as $tracker) {

    if ($tracker['enrollments'][0]['status'] == 'COMPLETED') continue;

    $formData = array();
    $facility = $tracker['orgUnit'];
    $formData['source_of_request'] = "dhis2-" . $tracker['trackedEntityInstance'];
    $formData['source_data_dump'] = json_encode($tracker);

    foreach ($tracker['attributes'] as $trackerAttr) {
        if (empty($attributesDataElementMapping[$trackerAttr['attribute']])) continue;
        //echo $attributesDataElementMapping[$trackerAttr['attribute']] . "%%%%%%%" . $trackerAttr['value'] . PHP_EOL . PHP_EOL;
        $formData[$attributesDataElementMapping[$trackerAttr['attribute']]] = $trackerAttr['value'];
    }




    foreach ($tracker['enrollments'] as $allEnrollments) {


        //if($allEnrollments['status'] != 'ACTIVE') var_dump($tracker); die('RONA');

        $enrollmentDate = explode("T", $allEnrollments['enrollmentDate']);
        $enrollmentDate = $enrollmentDate[0];
        foreach ($allEnrollments['events'] as $enrollmentEvent) {

            // iR8O4hSLHnu, CTdzCeTbYay
            //if ($enrollmentEvent['programStage'] == 'CTdzCeTbYay') {



            foreach ($enrollmentEvent['dataValues'] as $dV) {
                if (empty($eventsDataElementMapping[$dV['dataElement']])) continue;
                //echo $eventsDataElementMapping[$dV['dataElement']] . "======" . $dV['value'] . PHP_EOL . PHP_EOL;
                $formData[$eventsDataElementMapping[$dV['dataElement']]] = $dV['value'];
            }
            //}
        }
        //die;
    }

    $formData['sample_collection_date'] = (!empty($formData['sample_collection_date']) ?  $formData['sample_collection_date'] : $enrollmentDate);
    $formData['reason_for_covid19_test'] = (!empty($formData['reason_for_covid19_test']) ?  $formData['reason_for_covid19_test'] : "Suspect");


    $db->where("test_reason_name", $formData['reason_for_covid19_test']);
    $reason = $db->getOne("r_covid19_test_reasons");
    $formData['reason_for_covid19_test'] = $reason['test_reason_id'];

    $db->where("iso3", $formData['patient_nationality']);
    $country = $db->getOne("r_countries");
    $formData['patient_nationality'] = $country['id'];

    $db->where("facility_name", $formData['lab_id']);
    $lab = $db->getOne("facility_details");
    $formData['lab_id'] = $lab['facility_id'];

    $db->where("other_id", $facility);
    $fac = $db->getOne("facility_details");
    $formData['facility_id'] =  $fac['facility_id'];

    $db->where("province_name", $fac['facility_state']);
    $prov = $db->getOne("province_details");

    $formData['province_id'] = !empty($prov['province_id']) ? $prov['province_id'] : 1;


    $db->where("sample_name", $formData['specimen_type']);
    $sampleType = $db->getOne("r_hepatitis_sample_type");
    $formData['specimen_type'] = $sampleType['sample_id'];



    $formData['result_status'] = 6;


    $formData['patient_gender'] = (!empty($formData['patient_gender']) ? strtolower($formData['patient_gender']) : null);
    if (!empty($formData['specimen_quality'])) {
        $formData['specimen_quality'] =  strtolower($formData['specimen_quality']);
    }

    $general = new \Vlsm\Models\General($db);
    $covid19Model = new \Vlsm\Models\Covid19($db);


    $db->where("source_of_request", $formData['source_of_request']);
    $covid19Data = $db->getOne("form_covid19");

    $formData['last_modified_datetime'] = $general->getDateTime();

    if (empty($covid19Data) || empty($covid19Data['covid19_id'])) {
        $sampleJson = $covid19Model->generateCovid19SampleCode(null, $general->humanDateFormat($formData['sample_collection_date']), null, $formData['province_id']);

        $sampleData = json_decode($sampleJson, true);

        $formData['sample_code'] = $sampleData['sampleCode'];
        $formData['sample_code_format'] = $sampleData['sampleCodeFormat'];
        $formData['sample_code_key'] = $sampleData['sampleCodeKey'];

        $formData['request_created_by'] = 1;
        $formData['request_created_datetime'] = $general->getDateTime();

        $instanceResult = $db->rawQueryOne("SELECT vlsm_instance_id, instance_facility_name FROM s_vlsm_instance");

        $formData['vlsm_instance_id'] = $instanceResult['vlsm_instance_id'];
        $formData['vlsm_country_id'] = 1;
        $id = $db->insert("form_covid19", $formData);
        if ($id != false) {
            $counter++;
        }
    } else {
        $db = $db->where('covid19_id', $covid19Data['covid19_id']);
        $id = $db->update("form_covid19", $formData);
        if ($id != false) {
            $counter++;
        }
    }
    
}

$response = array('received' => count($response['trackedEntityInstances']), 'processed' => $counter);

echo(json_encode($response));
