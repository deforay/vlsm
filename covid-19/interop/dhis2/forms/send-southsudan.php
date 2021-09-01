<?php

// this file is included in /covid-19/interop/dhis2/covid-19-send.php



// echo ("<h5>...</h5>");
// echo ("<h5>...</h5>");
// echo ("<h1>Successfully connected to DHIS2</h1>");

use Vlsm\Models\Users;
$users = new Users($db);


//get facility map id
$query = "SELECT * FROM form_covid19 WHERE source_of_request LIKE 'dhis%' AND result_sent_to_source LIKE 'pending'";
$formResults = $db->rawQuery($query);
$attributesDataElementMapping = [
  'HAZ7VQ730yn' => 'external_sample_code', //dhis2 case id
  'yCWkkKtr6vd' => 'source_of_alert',
  'he05i8FUwu3' => 'patient_id',
  'sB1IHYu2xQT' => 'patient_name',
  'tIlOLmSOBGs' => 'patient_surname',
  'NI0QRzJvQ0k' => 'patient_dob',
  'Rv8WM2mTuS5' => 'patient_age',
  'oindugucx72' => 'patient_gender',
  'qlYg7fundnJ' => 'patient_nationality'
];

$eventsDataElementMapping = [
  'Q98LhagGLFj' => 'sample_collection_date',
  'H3UJlHuglGv' => 'reason_for_covid19_test',
  'b4PEeF4OOwc' => 'covid19_test_platform',
  'P61FWjSAjjA' => 'sample_condition',
  'bujqZ6Dqn4m' => 'lab_id',
  'kL7PTi4lRSl' => 'specimen_type',
  'pxPdKaS9CqF' => 'sample_received_datetime',
  'Cl2I1H6Y3oj' => 'sample_tested_datetime',
  'f5HxreMlOWP' => 'result'
];
$counter = 0;
foreach ($formResults as $row) {

  $trackedEntityInstance = str_replace("dhis2-", "", $row['source_of_request']);



  $urlData = array();
  $urlData[] = "fields=attributes[attribute,code,value],enrollments[*],orgUnit,trackedEntityInstance";
  $urlData[] = "paging=false";

  $url = "/api/trackedEntityInstances/$trackedEntityInstance";

  $teResponse = $dhis2->get($url, $urlData);

  $teResponse = json_decode($teResponse, true);

  if ($teResponse['enrollments'][0]['status'] == 'COMPLETED') continue;

  $strategy = null;
  $eventId = null;
  foreach ($teResponse['enrollments'][0]['events'] as $teEvent) {
    if ($teEvent['programStage'] == 'CTdzCeTbYay') {
      $eventId = ($teEvent['event']);
      $strategy = 'update';
      break;
    };
  }
  $facQuery = "SELECT facility_id, facility_name, other_id from facility_details where facility_id = " . $row['facility_id'];

  $facResult = $db->rawQueryOne($facQuery);



  // $eventApi = $dhis2->get("/api/events?trackedEntityInstance=$trackedEntityInstance&programStage=CTdzCeTbYay&paging=false");

  // $eventApi = (json_decode($eventApi, true));

  // $strategy = null;
  // if (!empty($eventApi['events'])) {
  //   $eventId = ($eventApi['events'][0]['event']);
  //   $strategy = 'update';
  // }

  if (empty($eventId)) {
    $idGeneratorApi = $dhis2->get("/api/system/id?limit=1");
    $idResponse = (json_decode($idGeneratorApi, true));
    $eventId = $idResponse['codes'][0];
  }

  $approver = $users->getUserInfo($row['result_approved_by'], 'user_name');
  $tester = $users->getUserInfo($row['tested_by'], 'user_name');
  $payload = '{
      "event": "' . $eventId . '",
      "eventDate":"2020-02-02",
      "program": "nZRqRmZvdJd",
      "orgUnit": "' . $facResult['other_id'] . '",
      "programStage": "CTdzCeTbYay",
      "status": "ACTIVE",
      "trackedEntityInstance": "' . $trackedEntityInstance . '",
      "dataValues": [
        {
          "dataElement": "b4PEeF4OOwc",
          "value": "' . $row['covid19_test_platform'] . '",
          "providedElsewhere":false
        },
        {
          "dataElement": "ZLEOP9JHZ5c",
          "value": "' . $row['sample_tested_datetime'] . '",
          "providedElsewhere":false
        },
        {
          "dataElement": "ovY6E8BSdto",
          "value": "' . ucwords($row['result']) . '",
          "providedElsewhere":false
        },
        {
          "dataElement": "mJFhS108OdO",
          "value": "' . $approver['user_name'] . '",
          "providedElsewhere":false
        },
        {
          "dataElement": "S0dl5jidUnW",
          "value": "' . $tester['user_name'] . '",
          "providedElsewhere":false
        }
      ]
    }';

  // echo "<pre>";
  // echo $payload;
  // echo "</pre>";
  $urlParams = array();
  if ($strategy == 'update') {
    $urlParams[] = "mergeMode=REPLACE";
    $urlParams[] = "strategy=UPDATE";
  }
  $response = $dhis2->post("/api/33/events/", $payload, $urlParams);

  // echo "<pre>";
  // echo ($response);
  // echo "</pre>";

  $updateData = array('result_sent_to_source' => 'sent');
  $db = $db->where('covid19_id', $row['covid19_id']);
  $db->update('form_covid19', $updateData);
  $counter++;
}




// echo ("<h5>...</h5>");
// echo ("<h5>...</h5>");


// echo ("<h1>Total records processed and result sent to DHIS2 : " . $counter . "</h1>");


$response = array('processed' => $counter);

echo (json_encode($response));