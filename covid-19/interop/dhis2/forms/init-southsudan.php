<?php

// this file is included in /covid-19/interop/dhis2/covid-19-init.php


// Getting all Testing Labs 

$data[] = "fields=options[id,name]";
$data[] = "paging=false";

$url = "/api/optionSets/fsHj2ZG3iHJ.json";

$response = $dhis2->get($url, $data);

$response = json_decode($response, true);

foreach ($response['options'] as $lab) {



    $db->where("other_id", $lab['id']);
    $facility = $db->getOne("facility_details");


    if (empty($facility)) {
        $facilityData = array(
            'facility_name' => $lab['name'],
            'vlsm_instance_id' => $instanceId,
            'other_id' => $lab['id'],
            'facility_type' => 2,
            'test_type' => 'covid19',
            'updated_datetime' => $general->getDateTime(),
            'status' => 'active'
        );
        $id = $db->insert('facility_details', $facilityData);

        $dataTest = array(
            'test_type' => 'covid19',
            'facility_id' => $id,
            'monthly_target' => null,
            'suppressed_monthly_target' => null,
            "updated_datetime" => $general->getDateTime()
        );
        $db->insert('testing_labs', $dataTest);
    }
}
