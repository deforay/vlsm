<?php
//update common table from remote to lab db
require_once(dirname(__FILE__) . "/../../startup.php");
require_once(APPLICATION_PATH . '/includes/MysqliDb.php');
require_once(APPLICATION_PATH . '/models/General.php');

if (!isset($systemConfig['remoteURL']) || $systemConfig['remoteURL'] == '') {
    echo "Please check if the Remote URL is set." . PHP_EOL;
    exit(0);
}

$systemConfig['remoteURL'] = rtrim($systemConfig['remoteURL'], "/");

$general = new General($db);
$globalConfigQuery = "SELECT * FROM system_config";
$configResult = $db->query($globalConfigQuery);


$provinceLastModified = $general->getLastModifiedDateTime('province_details');
$facilityLastModified = $general->getLastModifiedDateTime('facility_details');

$data = array(
    'provinceLastModified' => $provinceLastModified,
    'facilityLastModified' => $facilityLastModified,
    "Key" => "vlsm-get-remote",
);

if (isset($systemConfig['modules']['vl']) && $systemConfig['modules']['vl'] == true) {
    $data['vlArtCodesLastModified'] = $general->getLastModifiedDateTime('r_art_code_details');
    $data['vlRejectionReasonsLastModified'] = $general->getLastModifiedDateTime('r_sample_rejection_reasons');
    $data['vlSampleTypesLastModified'] = $general->getLastModifiedDateTime('r_vl_sample_type');
}

if (isset($systemConfig['modules']['eid']) && $systemConfig['modules']['eid'] == true) {
    $data['eidRejectionReasonsLastModified'] = $general->getLastModifiedDateTime('r_eid_sample_rejection_reasons');
    $data['eidSampleTypesLastModified'] = $general->getLastModifiedDateTime('r_eid_sample_type');
}


if (isset($systemConfig['modules']['covid19']) && $systemConfig['modules']['covid19'] == true) {
    $data['covid19RejectionReasonsLastModified'] = $general->getLastModifiedDateTime('r_covid19_sample_rejection_reasons');
    $data['covid19SampleTypesLastModified'] = $general->getLastModifiedDateTime('r_covid19_sample_type');
    $data['covid19ComorbiditiesLastModified'] = $general->getLastModifiedDateTime('r_covid19_comorbidities');
    $data['covid19ResultsLastModified'] = $general->getLastModifiedDateTime('r_covid19_results');
    $data['covid19SymptomsLastModified'] = $general->getLastModifiedDateTime('r_covid19_symptoms');
    $data['covid19ReasonForTestingLastModified'] = $general->getLastModifiedDateTime('r_covid19_test_reasons');
}

$url = $systemConfig['remoteURL'] . '/remote/remote/commonData.php';

$ch = curl_init($url);
$json_data = json_encode($data);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'Content-Length: ' . strlen($json_data)
));
// execute post
$curl_response = curl_exec($ch);
//close connection
curl_close($ch);
$result = json_decode($curl_response, true);

//update or insert sample type
if (!empty($result['vlSampleTypes']) && count($result['vlSampleTypes']) > 0) {
    // making all local rows inactive 
    // this way any additional rows in local that are not on remote
    // become inactive.

    //$db->update('r_vl_sample_type',array('status'=>'inactive'));    

    foreach ($result['vlSampleTypes'] as $type) {
        $sTypeQuery = "SELECT sample_id FROM r_vl_sample_type WHERE sample_id=" . $type['sample_id'];
        $sTypeLocalResult = $db->query($sTypeQuery);
        $sTypeData = array('sample_name' => $type['sample_name'], 'status' => $type['status'], 'data_sync' => 1);
        $lastId = 0;
        if ($sTypeLocalResult) {
            $db = $db->where('sample_id', $type['sample_id']);
            $lastId = $db->update('r_vl_sample_type', $sTypeData);
        } else {
            $sTypeData['sample_id'] = $type['sample_id'];
            $db->insert('r_vl_sample_type', $sTypeData);
            $lastId = $db->getInsertId();
        }
    }
}

//update or insert art code deatils
if (!empty($result['vlArtCodes']) && count($result['vlArtCodes']) > 0) {
    // making all local rows inactive 
    // this way any additional rows in local that are not on remote
    // become inactive.

    //$db->update('r_art_code_details',array('art_status'=>'inactive'));    

    foreach ($result['vlArtCodes'] as $artCode) {
        $artCodeQuery = "SELECT art_id FROM r_art_code_details WHERE art_id=" . $artCode['art_id'];
        $artCodeLocalResult = $db->query($artCodeQuery);
        $artCodeData = array(
            'art_code' => $artCode['art_code'],
            'parent_art' => $artCode['parent_art'],
            'headings' => $artCode['headings'],
            'nation_identifier' => $artCode['nation_identifier'],
            'art_status' => $artCode['art_status'],
            'data_sync' => 1,
            'updated_datetime' => $artCode['updated_datetime']
        );
        $lastId = 0;
        if ($artCodeLocalResult) {
            $db = $db->where('art_id', $artCode['art_id']);
            $lastId = $db->update('r_art_code_details', $artCodeData);
        } else {
            $artCodeData['art_id'] = $artCode['art_id'];
            $db->insert('r_art_code_details', $artCodeData);
            $lastId = $db->getInsertId();
        }
    }
}

//update or insert rejected reason
if (!empty($result['vlRejectionReasons']) && count($result['vlRejectionReasonss']) > 0) {

    // making all local rows inactive 
    // this way any additional rows in local that are not on remote
    // become inactive.

    //$db->update('r_sample_rejection_reasons',array('rejection_reason_status'=>'inactive'));    

    foreach ($result['vlRejectionReasons'] as $reason) {
        $rejectQuery = "SELECT rejection_reason_id FROM r_sample_rejection_reasons WHERE rejection_reason_id=" . $reason['rejection_reason_id'];
        $rejectLocalResult = $db->query($rejectQuery);
        $rejectResultData = array(
            'rejection_reason_name' => $reason['rejection_reason_name'],
            'rejection_type' => $reason['rejection_type'],
            'rejection_reason_status' => $reason['rejection_reason_status'],
            'rejection_reason_code' => $reason['rejection_reason_code'],
            'data_sync' => 1,
            'updated_datetime' => $reason['updated_datetime']
        );
        $lastId = 0;
        if ($rejectLocalResult) {
            $db = $db->where('rejection_reason_id', $reason['rejection_reason_id']);
            $lastId = $db->update('r_sample_rejection_reasons', $rejectResultData);
        } else {
            $rejectResultData['rejection_reason_id'] = $reason['rejection_reason_id'];
            $db->insert('r_sample_rejection_reasons', $rejectResultData);
            $lastId = $db->getInsertId();
        }
    }
}

//update or insert rejected reason
if (!empty($result['eidRejectionReasons']) && count($result['eidRejectionReasons']) > 0) {

    // making all local rows inactive 
    // this way any additional rows in local that are not on remote
    // become inactive.

    //$db->update('r_eid_sample_rejection_reasons',array('rejection_reason_status'=>'inactive'));    

    foreach ($result['eidRejectionReasons'] as $reason) {
        $rejectQuery = "SELECT rejection_reason_id FROM r_eid_sample_rejection_reasons WHERE rejection_reason_id=" . $reason['rejection_reason_id'];
        $rejectLocalResult = $db->query($rejectQuery);
        $rejectResultData = array(
            'rejection_reason_name' => $reason['rejection_reason_name'],
            'rejection_type' => $reason['rejection_type'],
            'rejection_reason_status' => $reason['rejection_reason_status'],
            'rejection_reason_code' => $reason['rejection_reason_code'],
            'data_sync' => 1, 'updated_datetime' => $reason['updated_datetime']
        );
        $lastId = 0;
        if ($rejectLocalResult) {
            $db = $db->where('rejection_reason_id', $reason['rejection_reason_id']);
            $lastId = $db->update('r_eid_sample_rejection_reasons', $rejectResultData);
        } else {
            $rejectResultData['rejection_reason_id'] = $reason['rejection_reason_id'];
            $db->insert('r_eid_sample_rejection_reasons', $rejectResultData);
            $lastId = $db->getInsertId();
        }
    }
}

//update or insert province
if (!empty($result['province']) && count($result['province']) > 0) {

    foreach ($result['province'] as $province) {
        $provinceQuery = "SELECT province_id FROM province_details WHERE province_id=" . $province['province_id'];
        $provinceLocalResult = $db->query($provinceQuery);
        $provinceData = array(
            'province_name' => $province['province_name'],
            'province_code' => $province['province_code'],
            'data_sync' => 1,
            'updated_datetime' => $general->getDateTime()
        );
        $lastId = 0;
        if ($provinceLocalResult) {
            $db = $db->where('province_id', $province['province_id']);
            $lastId = $db->update('province_details', $provinceData);
        } else {
            $provinceData['province_id'] = $province['province_id'];
            $db->insert('province_details', $provinceData);
            $lastId = $db->getInsertId();
        }
    }
}

//update or insert facility data

if (!empty($result['facilities']) && count($result['facilities']) > 0) {

    // making all local rows inactive 
    // this way any additional rows in local that are not on remote
    // become inactive.

    //$db->update('facility_details',array('status'=>'inactive'));

    $instanceId = $db->getValue('s_vlsm_instance', 'vlsm_instance_id');

    foreach ($result['facilities'] as $facility) {
        $facilityQuery = "SELECT facility_id FROM facility_details WHERE facility_id=" . $facility['facility_id'];
        $facilityLocalResult = $db->query($facilityQuery);
        $facilityData = array(
            'vlsm_instance_id' => $instanceId,
            'facility_name' => $facility['facility_name'],
            'facility_code' => $facility['facility_code'],
            'other_id' => $facility['other_id'],
            'facility_emails' => $facility['facility_emails'],
            'report_email' => $facility['report_email'],
            'contact_person' => $facility['contact_person'],
            'facility_mobile_numbers' => $facility['facility_mobile_numbers'],
            'address' => $facility['address'],
            'country' => $facility['country'],
            'facility_state' => $facility['facility_state'],
            'facility_district' => $facility['facility_district'],
            'facility_hub_name' => $facility['facility_hub_name'],
            'latitude' => $facility['latitude'],
            'longitude' => $facility['longitude'],
            'facility_type' => $facility['facility_type'],
            'status' => $facility['status'],
            'data_sync' => 1,
            'updated_datetime' => $facility['updated_datetime']
        );
        $lastId = 0;
        if ($facilityLocalResult) {
            $db = $db->where('facility_id', $facility['facility_id']);
            $lastId = $db->update('facility_details', $facilityData);
        } else {
            $facilityData['facility_id'] = $facility['facility_id'];
            $db->insert('facility_details', $facilityData);
            $lastId = $db->getInsertId();
        }
    }
}
