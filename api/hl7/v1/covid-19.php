<?php

    use Aranyasen\HL7\Message;
    use Aranyasen\HL7\Segment;
    use Aranyasen\HL7\Segments\PID;
    use Aranyasen\HL7\Segments\OBX;
    use Aranyasen\HL7\Messages\ACK;
use Aranyasen\HL7\Segments\MSH;

if($type[1] == 'RES'){
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
            lt_u_d.user_name as labTechnician,
            rs.rejection_reason_name,
            r_f_s.funding_source_name,
            c.iso_name as nationality,
            c.iso2 as country_code1,
            c.iso3 as country_code2,
            r_i_p.i_partner_name 
            
            FROM form_covid19 as vl 
            
            LEFT JOIN r_countries as c ON vl.patient_nationality=c.id
            LEFT JOIN facility_details as f ON vl.facility_id=f.facility_id 
            LEFT JOIN facility_details as l_f ON vl.lab_id=l_f.facility_id 
            LEFT JOIN r_sample_status as ts ON ts.status_id=vl.result_status 
            LEFT JOIN batch_details as b ON b.batch_id=vl.sample_batch_id 
            LEFT JOIN user_details as u_d ON u_d.user_id=vl.result_reviewed_by 
            LEFT JOIN user_details as a_u_d ON a_u_d.user_id=vl.result_approved_by 
            LEFT JOIN user_details as lt_u_d ON lt_u_d.user_id=vl.lab_technician 
            LEFT JOIN r_covid19_test_reasons as rtr ON rtr.test_reason_id=vl.reason_for_covid19_test 
            LEFT JOIN r_covid19_sample_type as rst ON rst.sample_id=vl.specimen_type 
            LEFT JOIN r_covid19_sample_rejection_reasons as rs ON rs.rejection_reason_id=vl.reason_for_sample_rejection 
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
            $check = (in_array($row['patient_gender'], array("female", "male", "other"))) ? $row['patient_gender'] : "other";
            $sex = strtoupper(substr($check, 0, 1));
            $pid = new PID();
            $pid->setPatientID($row['patient_id']);
            $pid->setPatientName($row['patient_name']);
            $pid->setMothersMaidenName([$row['patient_name'], $row['patient_surname']]);
            $pid->setDateTimeOfBirth($row['patient_dob']);
            $pid->setSex($sex);
            $pid->setPatientAddress($row['patient_address']);
            $pid->setCountryCode($row['patient_district']);
            $pid->setPhoneNumberHome($row['patient_phone_number']);
            $pid->setSSNNumber($row['external_sample_code']);
            $pid->setNationality($row['nationality']);
            $msg->setSegment($pid, 1);
            /* Sample Information */
            $spm = new Segment('SPM');
            $spm->setField(2, $row['sample_code']);
            $spm->setField(4, $row['sample_name']);
            $spm->setField(10, $row['facility_name']);
            $spm->setField(12, $row['is_sample_collected']);
            $spm->setField(17, $row['sample_collection_date']);
            $spm->setField(18, $row['sample_received_at_vl_lab_datetime']);
            $spm->setField(21, $row['reason_for_sample_rejection']);
            $spm->setField(24, $row['sample_condition']);
            $spm->setField(26, $row['test_number']);
            $msg->setSegment($spm, 2);
            /* OBR Section */
            $obr = new Segment('OBR');
            $obr->setField(1, $row['status_name']);
            $obr->setField(5, $row['priority_status']);
            $obr->setField(6, $row['request_created_datetime']);
            $obr->setField(14, $row['sample_received_at_hub_datetime']);
            $obr->setField(15, $row['source_of_alert']);
            $obr->setField(25, $row['result_status']);
            $obr->setField(26, $row['result']);
            $msg->setSegment($obr, 3);
            /* Clinic Custom Fields Information Details */
            $zci = new Segment('ZCI');
            $zci->setField(1, $row['is_sample_post_mortem']);
            $zci->setField(2, $row['number_of_days_sick']);
            $zci->setField(3, $row['date_of_symptom_onset']);
            $zci->setField(4, $row['date_of_initial_consultation']);
            $zci->setField(5, $row['fever_temp']);
            $zci->setField(6, $row['medical_history']);
            $zci->setField(7, $row['recent_hospitalization']);
            $zci->setField(8, $row['temperature_measurement_method']);
            $zci->setField(9, $row['respiratory_rate']);
            $zci->setField(10, $row['oxygen_saturation']);
            $zci->setField(11, $row['other_diseases']);
            $msg->setSegment($zci, 4);
            /* Patient Custom Fields Information Details */
            $zpi = new Segment('ZPI');
            $zpi->setField(1, $row['patient_occupation']);
            $zpi->setField(2, $row['patient_city']);
            $zpi->setField(3, $row['patient_province']);
            $zpi->setField(4, $row['patient_age']);
            $zpi->setField(5, $row['is_patient_pregnant']);
            $zpi->setField(6, $row['does_patient_smoke']);
            $zpi->setField(7, $row['patient_lives_with_children']);
            $zpi->setField(8, $row['patient_cares_for_children']);
            $zpi->setField(9, $row['close_contacts']);
            $zpi->setField(10, $row['contact_with_confirmed_case']);
            $msg->setSegment($zpi, 5);
            /* Airline Information Details */
            $zai = new Segment('ZAI');
            $zai->setField(1, $row['patient_passport_number']);
            $zai->setField(2, $row['flight_airline']);
            $zai->setField(3, $row['flight_seat_no']);
            $zai->setField(4, $row['flight_arrival_datetime']);
            $zai->setField(5, $row['flight_airport_of_departure']);
            $zai->setField(6, $row['flight_transit']);
            $zai->setField(7, $row['reason_of_visit']);
            $zai->setField(8, $row['has_recent_travel_history']);
            $zai->setField(9, $row['travel_country_names']);
            $zai->setField(10, $row['travel_return_date']);
            $msg->setSegment($zai, 6);
            /*  System Variables Details */
            $zsv = new Segment('ZSV');
            $zsv->setField(1, $row['is_result_authorised']);
            $zsv->setField(2, $row['authorized_by']);
            $zsv->setField(3, $row['authorized_on']);
            $zsv->setField(4, $row['rejection_on']);
            $zsv->setField(5, $row['request_created_datetime']);
            $msg->setSegment($zsv, 7);
            /*  Observation Details */
            $obx = new OBX;
            $obx->setObservationValue($row['result']);
            $msg->setSegment($obx, 8);

            $response[] = $msg->toString(true);
        }
        // No data found
        if (!$rowData) {
            $response = array(
                'status' => 'failed',
                'timestamp' => time(),
                'error' => 'No matching data',
                'data' => $response

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
            'data' => $response
        );

        http_response_code(200);
        echo json_encode($payload);
        exit(0);
    }

    if($type[1] == 'REQ'){
        $msg = new Message($hl7Msg);
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
            $name = $pid->getField(6);
            $data['patientId'] = $pid->getField(1);
            $data['firstName'] = $name[0];
            $data['lastName'] = $name[1];
            $data['patientDob'] = $pid->getField(7);
            $data['patientGender'] = $gender;
            $data['patientAddress'] = $pid->getField(11);
            $data['patientDistrict'] = $pid->getField(12);
            $data['patientPhoneNumber'] = $pid->getField(13);
            $data['patientNationality'] = $pid->getField(28);
        }
        /* Sample Information */
        if ($msg->hasSegment('SPM')) {
            $spm = $msg->getSegmentByIndex(2);
            $data['sampleCode'] = $spm->getField(2);
            // $data['sample_name'] = $spm->getField(4);
            $data['isSampleCollected'] = $spm->getField(12);
            $data['sampleCollectionDate'] = $spm->getField(17);
            $data['sampleReceivedDate'] = $spm->getField(18);
            $data['sampleRejectionReason'] = $spm->getField(21);
            $data['sampleCondition'] = $spm->getField(24);
            $data['testNumber'] = $spm->getField(26);
            // die($spm->getField(10));
            $facilityDetails = $facilityDb->getFacilityByName($spm->getField(10));
            if (!empty($facilityDetails[0]) && $facilityDetails[0] != "") {
                $data['facilityId'] = $facilityDetails[0]['facility_id'];
                $data['provinceCode'] = $facilityDetails[0]['province_code'];
            }
            if ($spm->getField(4) != "" && !empty($spm->getField(4))) {
                $c19Details = $c19Db->getCovid19SampleTypesByName($spm->getField(4));
                $data['specimenType'] = $c19Details[0]['sample_id'];
            }
        }
        /* OBR Section */
        if ($msg->hasSegment('OBR')) {
            $obr = $msg->getSegmentByIndex(3);
            $data['priorityStatus'] = $obr->getField(5);
            $data['sample_received_at_hub_datetime'] = $obr->getField(14);
            $data['sourceOfAlertPOE'] = $obr->getField(15);
            $data['result_status'] = $obr->getField(25);
            $data['result'] = $obr->getField(26);
        }

        /* Clinic Custom Fields Information Details */
        if ($msg->hasSegment('ZCI')) {
            $zci = $msg->getSegmentByIndex(4);
            $data['isSamplePostMortem'] = $zci->getField(1);
            $data['numberOfDaysSick'] = $zci->getField(2);
            $data['dateOfSymptomOnset'] = $zci->getField(3);
            $data['dateOfInitialConsultation'] = $zci->getField(4);
            $data['feverTemp'] = $zci->getField(5);
            $data['medicalHistory'] = $zci->getField(6);
            $data['recentHospitalization'] = $zci->getField(7);
            $data['temperatureMeasurementMethod'] = $zci->getField(8);
            $data['respiratoryRate'] = $zci->getField(9);
            $data['oxygenSaturation'] = $zci->getField(10);
            $data['otherDiseases'] = $zci->getField(11);
        }
        /* Patient Custom Fields Information Details */
        if ($msg->hasSegment('ZPI')) {
            $zpi = $msg->getSegmentByIndex(5);
            $data['patientOccupation'] = $zpi->getField(1);
            $data['patientCity'] = $zpi->getField(2);
            $data['patientProvince'] = $zpi->getField(3);
            $data['patientAge'] = $zpi->getField(4);
            $data['isPatientPregnant'] = $zpi->getField(5);
            $data['doesPatientSmoke'] = $zpi->getField(6);
            $data['patientLivesWithChildren'] = $zpi->getField(7);
            $data['patientCaresForChildren'] = $zpi->getField(8);
            $data['closeContacts'] = $zpi->getField(9);
            $data['contactWithConfirmedCase'] = $zpi->getField(10);
        }
        /* Airline Information Details */
        if ($msg->hasSegment('ZAI')) {
            $zai = $msg->getSegmentByIndex(6);
            $data['patientPassportNumber'] = $zai->getField(1);
            $data['airline'] = $zai->getField(2);
            $data['seatNo'] = $zai->getField(3);
            $data['arrivalDateTime'] = $zai->getField(4);
            $data['airportOfDeparture'] = $zai->getField(5);
            $data['transit'] = $zai->getField(6);
            $data['reasonOfVisit'] = $zai->getField(7);
            $data['hasRecentTravelHistory'] = $zai->getField(8);
            $data['countryName'] = $zai->getField(9);
            $data['returnDate'] = $zai->getField(10);
        }

        $data['formId'] = $general->getGlobalConfig('vl_form');
        $sQuery = "SELECT vlsm_instance_id from s_vlsm_instance";
        $rowData = $db->rawQuery($sQuery);
        $data['instanceId'] = $rowData[0]['vlsm_instance_id'];
        // print_r($data);die;
        $sampleFrom = '';

        $data['api'] = "yes";
        $data['hl7'] = "yes";
        $_POST = $data;

        include_once(APPLICATION_PATH . '/covid-19/requests/insert-sample.php');
        include_once(APPLICATION_PATH . '/covid-19/requests/covid-19-add-request-helper.php');
        if ($id > 0) {
            $msh = new MSH();
            $msh->setMessageType(["COVID19", "REQ"]);
            $ack = new ACK($msg, $msh);
            $returnString = $ack->toString(true);
            echo $returnString;
            /* if (strpos($returnString, 'MSH') === false) {
                echo "Failed to send HL7 to 'IP' => $ip, 'Port' => $port";
            }
            $msa = $ack->getSegmentsByName('MSA')[0];
            $ackCode = $msa->getAcknowledgementCode();
            print_r($ackCode);die;
            if ($ackCode === 'A') {
                echo "Recieved ACK from remote\n";
            } else {
                echo "Recieved NACK from remote\n";
                echo "Error text: " . $msa->getTextMessage();
            } */
        }
    }