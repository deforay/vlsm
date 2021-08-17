<?php

// this file is included in /hepatitis/interop/dhis2/hepatitis-send.php

use Vlsm\Models\Users;

$users = new Users($db);


//get facility map id
$query = "SELECT * FROM form_hepatitis WHERE source_of_request LIKE 'dhis%' AND result_sent_to_source LIKE 'pending'";
$formResults = $db->rawQuery($query);
$counter = 0;
foreach ($formResults as $row) {

  $trackedEntityInstance = str_replace("dhis2-", "", $row['source_of_request']);

  $urlData = array();
  $urlData[] = "fields=attributes[attribute,code,value],enrollments[*],orgUnit,trackedEntityInstance";
  $urlData[] = "paging=false";

  $url = "/api/trackedEntityInstances/$trackedEntityInstance";

  $teResponse = $dhis2->get($url, $urlData);

  $teResponse = json_decode($teResponse, true);

  // echo "<pre>";
  // var_dump($teResponse['enrollments'][0]['events']);
  // echo "</pre>";
  // continue;
  // echo "<pre>";
  // var_dump($teResponse);
  // echo "</pre>";
  // continue;

  if ($teResponse['enrollments'][0]['status'] == 'COMPLETED') continue;

  $orgUnitId = "Hjw70Lodtf2";
  if (!empty($row['facility_id'])) {
    $facQuery = "SELECT facility_id, facility_name, other_id from facility_details where facility_id = " . $row['facility_id'];
    $facResult = $db->rawQueryOne($facQuery);
    $orgUnitId = $facResult['other_id'];
  }


  $strategy = null;
  $eventId = null;
  $eventPayload = array();
  $eventDate = date("Y-m-d");
  $payload = array();
  foreach ($teResponse['enrollments'][0]['events'] as $eventPayload) {
    if ($eventPayload['programStage'] == 'KPBuhvFV5bK') {
      $dataValues = array();
      if ($row['hepatitis_test_type'] == 'HCV') {
        $dataValues['Oem0BXNDPWL'] = $row['hcv_vl_count'];
      } else {
        $dataValues['Di17rUJDIWZ']  = $row['hbv_vl_count'];
      }
    } else if ($eventPayload['programStage'] == 'WAyPhFAJLdv') {
      $dataValues = array();
      $dataValues['LblBnouUMJE']  = $row['hbv_vl_count'];
    } else if ($eventPayload['programStage'] == 'qiqz1esOFKV') {
      $dataValues = array();
      $dataValues['wVmsNyyPWT0'] = $row['hcv_vl_count'];
    }

    if (!empty($dataValues)) {
      $eventPayload = $dhis2->addDataValuesToEventPayload($eventPayload, $dataValues);
      $payload = json_encode($eventPayload);
      // echo "<br><br><pre>";
      // var_dump($payload);
      // echo "</pre>";
      $urlParams = array();
      $urlParams[] = "mergeMode=REPLACE";
      $urlParams[] = "strategy=UPDATE";
      $response = $dhis2->post("/api/33/events/", $payload, $urlParams);
      // echo "<br><br><pre>";
      // var_dump($response);
      // echo "</pre>";
    }
  }
  $updateData = array('result_sent_to_source' => 'sent');
  $db = $db->where('hepatitis_id', $row['hepatitis_id']);
  $db->update('form_hepatitis', $updateData);
  $counter++;
}




// echo ("<h5>...</h5>");
// echo ("<h5>...</h5>");


// echo ("<h1>Total records processed and result sent to DHIS2 : " . $counter . "</h1>");


$response = array('processed' => $counter);

echo (json_encode($response));
