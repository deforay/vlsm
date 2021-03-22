<?php

use Aranyasen\HL7\Message;
use Aranyasen\HL7\Segment;
use Aranyasen\HL7\Segments\PID;
use Aranyasen\HL7\Segments\OBX;
use Aranyasen\HL7\Messages\ACK;
use Aranyasen\HL7\Segments\MSH;

if ($type[1] == 'RES') {
    $sQuery = "SELECT 
            vl.*,
            rtr.test_reason_name,
            b.batch_code,
            ts.status_name,
            rst.sample_name,
            f.facility_name,
            l_f.facility_name as labName,
            f.facility_code,
            f.facility_state,
            f.facility_district,
            u_d.user_name as reviewedBy,
            a_u_d.user_name as approvedBy,
            r_c_b.user_name as reqCreatedBy,
            lt_u_d.user_name as labTechnician,
            rs.rejection_reason_name,
            r_f_s.funding_source_name,
            r_i_p.i_partner_name 
            
            FROM eid_form as vl 
            
            LEFT JOIN facility_details as f ON vl.facility_id=f.facility_id 
            LEFT JOIN facility_details as l_f ON vl.lab_id=l_f.facility_id 
            LEFT JOIN r_sample_status as ts ON ts.status_id=vl.result_status 
            LEFT JOIN batch_details as b ON b.batch_id=vl.sample_batch_id 
            LEFT JOIN user_details as u_d ON u_d.user_id=vl.result_reviewed_by 
            LEFT JOIN user_details as a_u_d ON a_u_d.user_id=vl.result_approved_by 
            LEFT JOIN user_details as r_c_b ON a_u_d.user_id=vl.tested_by 
            LEFT JOIN user_details as lt_u_d ON lt_u_d.user_id=vl.lab_technician 
            LEFT JOIN r_eid_test_reasons as rtr ON rtr.test_reason_id=vl.reason_for_eid_test 
            LEFT JOIN r_eid_sample_type as rst ON rst.sample_id=vl.specimen_type 
            LEFT JOIN r_eid_sample_rejection_reasons as rs ON rs.rejection_reason_id=vl.reason_for_sample_rejection 
            LEFT JOIN r_funding_sources as r_f_s ON r_f_s.funding_source_id=vl.funding_source 
            LEFT JOIN r_implementation_partners as r_i_p ON r_i_p.i_partner_id=vl.implementing_partner";


    if (!empty($search[1])) {
        $date = $search[1];
        $sQuery .= " AND DATE(sample_collection_date) between '$date[0]' AND '$date[1]' ";
    }

    if (!empty($search[2])) {
        $specimen = implode("','", $search[2]);
        $sQuery .= " AND rst.sample_name IN ('" . $specimen . "') ";
    }

    if (!empty($search[3])) {
        $facilities = implode("','", $search[3]);
        $sQuery .= " AND f.facility_name IN ('" . $facilities . "') ";
    }

    if (!empty($search[4])) {
        $labs = implode("','", $search[4]);
        $sQuery .= " AND l_f.facility_name IN ('" . $labs . "') ";
    }

    if (!empty($search[5])) {
        $sQuery .= " AND vl.is_sample_rejected ='" . $search[5] . "' ";
    }

    if (!empty($search[6]) && $search[6] == "yes") {
        $sQuery .= " AND (vl.sample_tested_datetime != null AND vl.sample_tested_datetime not like '') ";
    } else {
        $sQuery .= " AND (vl.sample_tested_datetime == null OR vl.sample_tested_datetime like '') ";
    }
    // die($sQuery);
    $rowData = $db->rawQuery($sQuery);
    foreach ($rowData as $row) {
        /* Patient Information */
        $check = (in_array($row['child_gender'], array("female", "male", "other"))) ? $row['child_gender'] : "other";
        $sex = strtoupper(substr($check, 0, 1));
        $pid = new PID();
        $pid->setPatientID($row['child_id']);
        $pid->setPatientName($row['child_name']);
        $pid->setMothersMaidenName($row['mother_name']);
        $pid->setDateTimeOfBirth($row['child_dob']);
        $pid->setSex($sex);
        $pid->setPhoneNumberHome($row['caretaker_address']);
        $msg->setSegment($pid, 1);
        /* Sample Information */
        $spm = new Segment('SPM');
        $spm->setField(2, $row['sample_code']);
        $spm->setField(4, $row['sample_name']);
        $spm->setField(10, $row['facility_name']);
        $spm->setField(17, $row['sample_collection_date']);
        $spm->setField(18, $row['sample_received_at_vl_lab_datetime']);
        $spm->setField(21, $row['rejection_reason_name']);
        $msg->setSegment($spm, 2);
        /* OBR Section */
        $obr = new Segment('OBR');
        $obr->setField(6, $row['request_created_datetime']);
        $obr->setField(10, ['COLLECT', $row['reqCreatedBy']]);
        $obr->setField(14, $row['sample_received_at_hub_datetime']);
        $obr->setField(15, $row['funding_source_name']);
        $obr->setField(16, ['', '', $row['i_partner_name'], '', '', '']);
        $obr->setField(25, $row['status_name']);
        $obr->setField(26, $row['result']);
        $obr->setField(33, [$row['sample_requestor_name'], '']);
        $msg->setSegment($obr, 3);
        /* Patient Custom Fields Information Details */
        $zpi = new Segment('ZPI');
        $zpi->setField(1, $row['child_age']);
        $zpi->setField(2, $row['child_treatment']);
        $zpi->setField(3, $row['mother_id']);
        $msg->setSegment($zpi, 4);
        /* Infant and Mother's Health Information Details */
        $zim = new Segment('ZIM');
        $zim->setField(1, $row['mother_hiv_status']);
        $zim->setField(2, $row['mother_treatment']);
        $zim->setField(3, $row['mother_treatment_initiation_date']);
        $zim->setField(4, $row['rapid_test_performed']);
        $zim->setField(5, $row['rapid_test_date']);
        $zim->setField(6, $row['rapid_test_result']);
        $zim->setField(7, $row['has_infant_stopped_breastfeeding']);
        $zim->setField(8, $row['age_breastfeeding_stopped_in_months']);
        $zim->setField(9, $row['pcr_test_performed_before']);
        $zim->setField(10, $row['previous_pcr_result']);
        $zim->setField(11, $row['last_pcr_date']);
        $zim->setField(12, $row['reason_for_pcr']);
        $msg->setSegment($zim, 5);
        /*  System Variables Details */
        $zsv = new Segment('ZSV');
        $zsv->setField(2, $row['approvedBy']);
        $zsv->setField(3, $row['result_approved_datetime']);
        $msg->setSegment($zsv, 6);
        /*  Observation Details */
        $obx = new OBX;
        $obx->setObservationValue($row['result']);
        $msg->setSegment($obx, 7);

        $hl7Data .= $msg->toString(true);
    }
    // No data found
    /* if (!$rowData) {
        $response = array(
            'status' => 'failed',
            'timestamp' => time(),
            'error' => 'No matching data',
            'data' => $hl7Data

        );
        // if (isset($user['token-updated']) && $user['token-updated'] == true) {
        //     $response['token'] = $user['newToken'];
        // }
        http_response_code(200);
        echo json_encode($response);
        exit(0);
    }

    $payload = array(
        'status' => 'success',
        'timestamp' => time(),
        'data' => $hl7Data
    );
    // print_r($hl7Data);die;
    http_response_code(200);
    echo json_encode($payload);
    exit(0); */
    echo $hl7Data;die;
    http_response_code(200);
    exit(0);
}

if ($type[1] == 'REQ') {
    // echo "<pre>";print_r($msg->getSegmentsByName('PID'));die;
    /* Patient Information */
    if ($msg->hasSegment('PID')) {
        $pid = $msg->getSegmentByIndex(1);
        if ($pid->getField(8) == "F") {
            $gender = "female";
        } else if ($pid->getField(8) == "M") {
            $gender = "male";
        } else if ($pid->getField(8) == "O") {
            $gender = "other";
        }
        $data['childId'] = $pid->getField(2);
        $data['childName'] = $pid->getField(5);
        $data['mothersName'] = $pid->getField(6);
        $data['childDob'] = $pid->getField(7);
        $data['childGender'] = $gender;
        $data['caretakerPhoneNumber'] = $pid->getField(13);
    }
    /* Sample Information */
    if ($msg->hasSegment('SPM')) {
        $spm = $msg->getSegmentByIndex(2);
        $data['sampleCode'] = $spm->getField(2);
        $data['sampleCollectionDate'] = $spm->getField(17);
        $data['sampleReceivedDate'] = $spm->getField(18);
        if ($spm->getField(1) != "" && !empty($spm->getField(1))) {
            $respondID = $general->getValueByName($spm->getField(1), 'rejection_reason_name', 'r_vl_sample_rejection_reasons', 'rejection_reason_id');
            $data['sampleRejectionReason'] = $respondID;
        }
        $facilityDetails = $facilityDb->getFacilityByName($spm->getField(10));
        if (!empty($facilityDetails[0]) && $facilityDetails[0] != "") {
            $data['facilityId'] = $facilityDetails[0]['facility_id'];
            $data['provinceId'] = $facilityDetails[0]['province_id'];
            $data['provinceCode'] = $facilityDetails[0]['province_code'];
        }
        if ($spm->getField(4) != "" && !empty($spm->getField(4))) {
            $vlSampleDetails = $vlDb->getVlSampleTypesByName($spm->getField(4));
            $data['specimenType'] = $vlSampleDetails[0]['sample_id'];
        }
    }
    /* OBR Section */
    if ($msg->hasSegment('OBR')) {
        $obr = $msg->getSegmentByIndex(3);
        if ($obr->getField(25) != "" && !empty($obr->getField(25))) {
            $vlResultStatus = $general->getValueByName($obr->getField(25), 'status_name', 'r_sample_status', 'status_id');
            $data['result_status'] = $vlResultStatus;
        }else{
            $data['result_status'] = null;
        }
        $reqBy = $obr->getField(10);
        if (isset($reqBy[1]) != "" && !empty($reqBy[1])) {
            $vlResultStatus = $general->getValueByName($reqBy[1], 'user_name', 'user_details', 'user_id');
            $data['testedBy'] = $vlResultStatus;
        }else{
            $data['testedBy'] = null;
        }
        // $data['priorityStatus'] = $obr->getField(5);
        $data['sampleReceivedAtHubOn'] = $obr->getField(14);
        if ($obr->getField(15) != "" && !empty($obr->getField(15))) {
            $vlResultStatus = $general->getValueByName($obr->getField(15), 'funding_source_name', 'r_funding_sources', 'funding_source_id');
            $data['fundingSource'] = $vlResultStatus;
        } else{
            $data['fundingSource'] = null;
        }
        if ($spm->getField(16) != "" && !empty($spm->getField(16))) {
            $respondID = $general->getValueByName($spm->getField(16), 'i_partner_name', 'r_implementation_partners', 'i_partner_id');
            $data['implementingPartner'] = $respondID;
        }
        $data['result'] = $obr->getField(26);
        $sampleRequestorName = $obr->getField(33);
        $data['sampleRequestorName'] = $sampleRequestorName[1];
    }
    /* Patient Custom Fields Information Details */
    if ($msg->hasSegment('ZPI')) {
        $zpi = $msg->getSegmentByIndex(4);
        $data['childAge'] = $zpi->getField(1);
        $data['childTreatment'] = $zpi->getField(2);
        $data['mothersId'] = $zpi->getField(3);
    }
    /*  Infant and Mother's Health Information Details */
    if ($msg->hasSegment('ZIM')) {
        $zim = $msg->getSegmentByIndex(5);
        $data['mothersHIVStatus'] = $zim->getField(1);
        $data['motherTreatment'] = $zim->getField(2);
        $data['motherTreatmentInitiationDate'] = $zim->getField(3);
        $data['rapidTestPerformed'] = $zim->getField(4);
        $data['rapidtestDate'] = $zim->getField(5);
        $data['rapidTestResult'] = $zim->getField(6);
        $data['hasInfantStoppedBreastfeeding'] = $zim->getField(7);
        $data['ageBreastfeedingStopped'] = $zim->getField(8);
        $data['pcrTestPerformedBefore'] = $zim->getField(9);
        $data['prePcrTestResult'] = $zim->getField(10);
        $data['previousPCRTestDate'] = $zim->getField(11);
        $data['pcrTestReason'] = $zim->getField(12);
    }

    $data['formId']= $data['countryId'] = $general->getGlobalConfig('vl_form');
    $sQuery = "SELECT vlsm_instance_id from s_vlsm_instance";
    $rowData = $db->rawQuery($sQuery);
    $data['instanceId'] = $rowData[0]['vlsm_instance_id'];
    // print_r($data);die;
    $sampleFrom = '';

    $data['api'] = "yes";
    $data['hl7'] = "yes";
    $_POST = $data;
    include_once(APPLICATION_PATH . '/eid/requests/insert-sample.php');
    include_once(APPLICATION_PATH . '/eid/requests/eid-add-request-helper.php');
    if ($id > 0) {
        $msh = new MSH();
        $msh->setMessageType(["EID", "REQ"]);
        $ack = new ACK($msg, $msh);
        $returnString = $ack->toString(true);
        echo $returnString;
    }
}
