<?php

// this file is included in /covid-19/interop/dhis2/covid-19-init.php

$dhis2 = new \Vlsm\Interop\Dhis2(DHIS2_URL, DHIS2_USER, DHIS2_PASSWORD);
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





// // Adding Facilities - We will only run this once

// $data[] = "filter=level:eq:5";
// $data[] = "paging=false";
// $data[] = "fields=id,level,name,path,coordinates[id,name,parent]";

// $url = "/api/organisationUnits.json";

// $response = $dhis2->get($url, $data);
// $response = json_decode($response, true);

// foreach ($response['organisationUnits'] as $facility) {

//     $db->where("other_id", $facility['id']);
//     $db->orWhere("facility_name", $facility['name']);
//     $facilityResult = $db->getOne("facility_details");



//     $facilityData = array(
//         'facility_name' => $facility['name'],
//         'vlsm_instance_id' => $instanceId,
//         'other_id' => $facility['id'],
//         'facility_type' => 1,
//         'test_type' => 'covid19',
//         'updated_datetime' => $general->getDateTime(),
//         'status' => 'active'
//     );
//     $updateColumns = array("other_id", "updated_datetime");
//     $lastInsertId = "facility_id";
//     $db->onDuplicate($updateColumns, $lastInsertId);
//     $id = $db->insert('facility_details', $facilityData);
// }
