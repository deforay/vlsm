<?php
ob_start();
include('../includes/MysqliDb.php');
include('../includes/General.php');
$general = new Deforay_Commons_General();
$tableName = "vl_request_form";
if(isset($_GET['q']) && $_GET['q']!= ''){
    try {
    //vl instance id
    $vlInstanceQuery ="SELECT vlsm_instance_id FROM vl_instance";
    $vlInstanceResult = $db->rawQuery($vlInstanceQuery);
    $vlInstanceId = $vlInstanceResult[0]['vlsm_instance_id'];
    //import request/result country
    $formQuery ="SELECT value FROM global_config where name='vl_form'";
    $formResult = $db->rawQuery($formQuery);
    $country = $formResult[0]['value'];
    $rgz = gzinflate(base64_decode($_GET['q']));
    $rjson = json_decode($rgz);
      for($i=0;$i<count($rjson);$i++){
        //facility section
        if($rjson[0] !='' && $rjson[0]!= null){
           $clinicQuery = 'select facility_id from facility_details where facility_code = "'.$rjson[0].'"';
           $clinicResult = $db->rawQuery($clinicQuery);
        }else if($rjson[1] !='' && $rjson[1]!= null){
          $clinicQuery = 'select facility_id from facility_details where facility_name = "'.$rjson[1].'"';
          $clinicResult = $db->rawQuery($clinicQuery);
        }
        if(isset($clinicResult[0]['facility_id'])){
            $data['facility_id'] = $clinicResult[0]['facility_id'];
        }else{
            $clinicData = array(
              'facility_name'=>$rjson[1],
              'facility_code'=>$rjson[0],
              'vlsm_instance_id'=>$vlInstanceId,
              'other_id'=>null,
              'contact_person'=>null,
              'facility_mobile_numbers'=>$rjson[13],
              'address'=>null,
              'country'=>$country,
              'facility_state'=>$rjson[2],
              'facility_district'=>$rjson[3],
              'facility_hub_name'=>$rjson[4],
              'latitude'=>null,
              'longitude'=>null,
              'facility_emails'=>$rjson[14],
              'facility_type'=>1,
              'status'=>'active'
            );
            $id = $db->insert('facility_details',$clinicData);
            $data['facility_id'] = $id;
        }
        $data['facility_sample_id'] = $rjson[5];
        $data['request_clinician_name'] = $rjson[6];
        $data['request_clinician_phone_number'] = $rjson[7];
        $data['facility_support_partner'] = $rjson[8];
        $data['physician_name'] = $rjson[9];
        $data['date_test_ordered_by_physician'] = $rjson[10];
        $data['sample_collection_date'] = $rjson[11];
        $data['sample_collected_by'] = $rjson[12];
        $data['test_urgency'] = $rjson[15];
        //patient section
        $data['patient_art_no'] = $rjson[16];
        $data['patient_anc_no'] = $rjson[17];
        $data['patient_nationality'] = $rjson[18];
        $data['patient_other_id'] = $rjson[19];
        $data['patient_first_name'] = $rjson[20];
        $data['patient_last_name'] = $rjson[21];
        $data['patient_dob'] = $rjson[22];
        $data['patient_gender'] = $rjson[23];
        $data['patient_age_in_years'] = $rjson[24];
        $data['patient_age_in_months'] = $rjson[25];
        $data['consent_to_receive_sms'] = $rjson[26];
        $data['patient_mobile_number'] = $rjson[27];
        $data['patient_location'] = $rjson[28];
        $data['vl_focal_person'] = $rjson[29];
        $data['vl_focal_person_phone_number'] = $rjson[30];
        $data['patient_address'] = $rjson[31];
        //treatment section
        $data['is_patient_new'] = $rjson[32];
        $data['patient_art_date'] = $rjson[33];
        $data['reason_for_vl_testing'] = $rjson[34];
        $data['is_patient_pregnant'] = $rjson[35];
        $data['is_patient_breastfeeding'] = $rjson[36];
        $data['pregnancy_trimester'] = $rjson[37];
        $data['date_of_initiation_of_current_regimen'] = $rjson[38];
        $data['last_vl_date_routine'] = $rjson[39];
        $data['last_vl_result_routine'] = $rjson[40];
        if(trim($rjson[41])!=''){
            $specimenTypeQuery = 'select sample_id from r_sample_type where sample_name = "'.$rjson[41].'"';
            $specimenResult = $db->rawQuery($specimenTypeQuery);
            if(isset($specimenResult[0]['sample_id'])){
              $data['last_vl_sample_type_routine'] = $specimenResult[0]['sample_id'];
            }else{
              $sampleTypeData = array(
                'sample_name'=>$rjson[41],
                'status'=>'active'
              );
              $id = $db->insert('r_sample_type',$sampleTypeData);
              $data['last_vl_sample_type_routine'] = $id;
            }
        }
        $data['last_vl_date_failure_ac'] = $rjson[42];
        $data['last_vl_result_failure_ac'] = $rjson[43];
        if(trim($rjson[44])!=''){
            $specimenTypeQuery = 'select sample_id from r_sample_type where sample_name = "'.$rjson[44].'"';
            $specimenResult = $db->rawQuery($specimenTypeQuery);
            if(isset($specimenResult[0]['sample_id'])){
              $data['last_vl_sample_type_failure_ac'] = $specimenResult[0]['sample_id'];
            }else{
              $sampleTypeData = array(
                'sample_name'=>(string)$xml->treatment->treatment_last_vl_sample_type_failure_ac,
                'status'=>'active'
              );
              $id = $db->insert('r_sample_type',$sampleTypeData);
              $data['last_vl_sample_type_failure_ac'] = $id;
            }
        }
        $data['last_vl_date_failure'] = $rjson[45];
        $data['last_vl_result_failure'] = $rjson[46];
        if(trim($rjson[47])!=''){
          $specimenTypeQuery = 'select sample_id from r_sample_type where sample_name = "'.$rjson[47].'"';
          $specimenResult = $db->rawQuery($specimenTypeQuery);
          if(isset($specimenResult[0]['sample_id'])){
            $data['last_vl_sample_type_failure'] = $specimenResult[0]['sample_id'];
          }else{
            $sampleTypeData = array(
              'sample_name'=>(string)$xml->treatment->treatment_last_vl_sample_type_failure,
              'status'=>'active'
            );
            $id = $db->insert('r_sample_type',$sampleTypeData);
            $data['last_vl_sample_type_failure'] = $id;
          }
        }
        $data['has_patient_changed_regimen'] = $rjson[48];
        $data['reason_for_regimen_change'] = $rjson[49];
        $data['regimen_change_date'] = $rjson[50];
        $data['arv_adherance_percentage'] = $rjson[51];
        $data['is_adherance_poor'] = $rjson[52];
        $data['last_vl_result_in_log'] = $rjson[53];
        $data['vl_test_number'] = $rjson[54];
        $data['number_of_enhanced_sessions'] = $rjson[55];
        //sample section
        $data['sample_code'] = $rjson[56];
        $data['serial_no'] = $rjson[56];
        if(trim($rjson[57])!=''){
          $specimenTypeQuery = 'select sample_id from r_sample_type where sample_name = "'.$rjson[57].'"';
          $specimenResult = $db->rawQuery($specimenTypeQuery);
          if(isset($specimenResult[0]['sample_id'])){
            $data['sample_type'] = $specimenResult[0]['sample_id'];
          }else{
            $sampleTypeData = array(
              'sample_name'=>$rjson[57],
              'status'=>'active'
            );
            $id = $db->insert('r_sample_type',$sampleTypeData);
            $data['sample_type'] = $id;
          }
        }
        $data['is_sample_rejected'] = $rjson[58];
        if(trim($rjson[59])!=''){
            $clinicQuery = 'select facility_id from facility_details where facility_name = "'.$rjson[59].'"';
            $clinicResult = $db->rawQuery($clinicQuery);
            if(isset($clinicResult[0]['facility_id'])){
              $data['sample_rejection_facility'] = $clinicResult[0]['facility_id'];
            }else{
              $clinicData = array(
                'facility_name'=>$rjson[59],
                'facility_code'=>null,
                'vlsm_instance_id'=>$vlInstanceId,
                'other_id'=>null,
                'contact_person'=>null,
                'facility_mobile_numbers'=>null,
                'address'=>null,
                'country'=>$country,
                'facility_state'=>null,
                'facility_district'=>null,
                'facility_hub_name'=>null,
                'latitude'=>null,
                'longitude'=>null,
                'facility_emails'=>null,
                'facility_type'=>1,
                'status'=>'active'
              );
              $id = $db->insert('facility_details',$clinicData);
              $data['sample_rejection_facility'] = $id;
            }
        }
        if(trim($rjson[60]) !=''){
            $rejectionReasonQuery = 'select rejection_reason_id from r_sample_rejection_reasons where rejection_reason_name = "'.$rjson[60].'"';
            $rejectionReasonQueryResult = $db->rawQuery($rejectionReasonQuery);
            if(isset($rejectionReasonQueryResult[0]['rejection_reason_id'])){
              $data['reason_for_sample_rejection'] = $rejectionReasonQueryResult[0]['rejection_reason_id'];
            }else{
              $rejectionReasonData = array(
                'rejection_reason_name'=>$rjson[60],
                'rejection_reason_status'=>'active'
              );
              $id = $db->insert('r_sample_rejection_reasons',$rejectionReasonData);
              $data['reason_for_sample_rejection'] = $id;
            }
        }
        $data['plasma_conservation_temperature'] = $rjson[61];
        $data['plasma_conservation_duration'] = $rjson[62];
        $data['vl_test_platform'] = $rjson[63];
        if(trim($rjson[64]) !=''){
            $statusQuery = 'select status_id from r_sample_status where status_name = "'.$rjson[64].'"';
            $statusResult = $db->rawQuery($statusQuery);
            if(isset($statusResult[0]['status_id'])){
              $data['result_status'] = $statusResult[0]['status_id'];
            }else{
              $statusData = array(
                'status_name'=>$rjson[64]
              );
              $id = $db->insert('r_sample_status',$statusData);
              $data['result_status'] = $id;
            }
        }
        //viral load lab section
        if(trim($rjson[65]) !=''){
            $clinicQuery = 'select facility_id from facility_details where facility_code = "'.$rjson[65].'"';
            $clinicResult = $db->rawQuery($clinicQuery);
        }else if(trim($rjson[66]) !=''){
            $clinicQuery = 'select facility_id from facility_details where facility_name = "'.$rjson[66].'"';
            $clinicResult = $db->rawQuery($clinicQuery);
        }
        if(isset($clinicResult[0]['facility_id'])){
            $data['lab_id'] = $clinicResult[0]['facility_id'];
        }else{
          $clinicData = array(
            'facility_name'=>$rjson[66],
            'facility_code'=>$rjson[65],
            'vlsm_instance_id'=>$vlInstanceId,
            'other_id'=>null,
            'contact_person'=>null,
            'facility_mobile_numbers'=>null,
            'address'=>null,
            'country'=>$country,
            'facility_state'=>null,
            'facility_district'=>null,
            'facility_hub_name'=>null,
            'latitude'=>null,
            'longitude'=>null,
            'facility_emails'=>null,
            'facility_type'=>2,
            'status'=>'active'
          );
          $id = $db->insert('facility_details',$clinicData);
          $data['lab_id'] = $id;
        }
        $data['lab_contact_person'] = $rjson[67];
        $data['lab_phone_number'] = $rjson[68];
        $data['sample_received_at_vl_lab_datetime'] = $rjson[70];
        $data['sample_tested_datetime'] = $rjson[71];
        if(trim($rjson[72]) !=''){
          $batchQuery = 'select batch_id from batch_details where batch_code = "'.$rjson[72].'"';
          $batchResult = $db->rawQuery($batchQuery);
          if(isset($batchResult[0]['batch_id'])){
            $data['sample_batch_id'] = $batchResult[0]['batch_id'];
          }else{
            $batchData = array(
              'machine'=>0,
              'batch_code'=>$rjson[72],
              'request_created_datetime'=>$general->getDateTime()
            );
            $id = $db->insert('batch_details',$batchData);
            $data['sample_batch_id'] = $id;
          }
        }
        $data['result_dispatched_datetime'] = $rjson[73];
        //sample result section
        $data['result_value_log'] = $rjson[74];
        $data['result_value_absolute'] = $rjson[75];
        $data['result_value_text'] = $rjson[76];
        $data['result'] = $rjson[77];
        $data['approver_comments'] = $rjson[78];
        if(trim($rjson[79]) !=''){
            $userQuery = 'select user_id from user_details where user_name = "'.$rjson[79].'"';
            $userResult = $db->rawQuery($userQuery);
            if(isset($userResult[0]['user_id'])){
              $data['result_reviewed_by'] = $userResult[0]['user_id'];
            }else{
              $userData = array(
                'user_name'=>$rjson[79],
                'email'=>NULL,
                'phone_number'=>NULL,
                'login_id'=>NULL,
                'password'=>NULL,
                'role_id'=>2,
                'status'=>NULL
                );
              $id = $db->insert('user_details',$userData);
              $data['result_reviewed_by'] = $id;
            }
        }
        $data['result_reviewed_datetime'] = $rjson[80];
        if(trim($rjson[81]) !=''){
          $userQuery = 'select user_id from user_details where user_name = "'.$rjson[81].'"';
          $userResult = $db->rawQuery($userQuery);
          if(isset($userResult[0]['user_id'])){
            $data['result_approved_by'] = $userResult[0]['user_id'];
          }else{
            $userData = array(
              'user_name'=>$rjson[81],
              'email'=>NULL,
              'phone_number'=>NULL,
              'login_id'=>NULL,
              'password'=>NULL,
              'role_id'=>2,
              'status'=>NULL
              );
            $id = $db->insert('user_details',$userData);
            $data['result_approved_by'] = $id;
          }
        }
        $data['result_approved_datetime'] = $rjson[82];
        $data['result_printed_datetime'] = $rjson[83];
        $data['result_sms_sent_datetime'] = $rjson[84];
        //general section
        $data['vlsm_instance_id'] = $vlInstanceId;
        if(trim($rjson[86]) !=''){
            $formQuery = 'select vlsm_country_id from form_details where form_name = "'.$rjson[86].'"';
            $formResult = $db->rawQuery($formQuery);
            if(isset($formResult[0]['vlsm_country_id'])){
              $data['vlsm_country_id'] = $formResult[0]['vlsm_country_id'];
            }else{
              $formData = array(
                'form_name'=>$rjson[86]
              );
              $id = $db->insert('form_details',$formData);
              $data['vlsm_country_id'] = $id;
            }
        }else{
          $data['vlsm_country_id'] = $country;
        }
        $data['is_request_mail_sent'] = $rjson[87];
        $data['is_result_mail_sent'] = $rjson[88];
        $data['is_result_sms_sent'] = $rjson[89];
        $data['manual_result_entry'] = $rjson[90];
        if(trim($rjson[91]) !=''){
            $userQuery = 'select user_id from user_details where user_name = "'.$rjson[91].'"';
            $userResult = $db->rawQuery($userQuery);
            if(isset($userResult[0]['user_id'])){
              $data['request_created_by'] = $userResult[0]['user_id'];
            }else{
              $userData = array(
                'user_name'=>$rjson[91],
                'email'=>NULL,
                'phone_number'=>NULL,
                'login_id'=>NULL,
                'password'=>NULL,
                'role_id'=>2,
                'status'=>NULL
                );
              $id = $db->insert('user_details',$userData);
              $data['request_created_by'] = $id;
            }
        }
        $data['request_created_datetime'] = $rjson[92];
        if(trim($rjson[93]) !=''){
          $userQuery = 'select user_id from user_details where user_name = "'.$rjson[93].'"';
          $userResult = $db->rawQuery($userQuery);
          if(isset($userResult[0]['user_id'])){
            $data['last_modified_by'] = $userResult[0]['user_id'];
          }else{
            $userData = array(
              'user_name'=>$rjson[93],
              'email'=>NULL,
              'phone_number'=>NULL,
              'login_id'=>NULL,
              'password'=>NULL,
              'role_id'=>2,
              'status'=>NULL
              );
            $id = $db->insert('user_details',$userData);
            $data['last_modified_by'] = $id;
          }
        }
        $data['last_modified_datetime'] = $rjson[94];
        $data['import_machine_file_name'] = $rjson[95];
        $sampleQuery = 'select vl_sample_id from vl_request_form where sample_code = "'.$rjson[56].'"';
        $sampleResult = $db->rawQuery($sampleQuery);
        if(isset($sampleResult[0]['vl_sample_id'])){
          $db=$db->where('sample_code',$rjson[56]);
          $db->update($tableName,$data);
        }else{
          $db->insert($tableName,$data);
        }
      }
    header("location:/vl-request/import.php?q=");
    }catch (Exception $exc) {
      error_log($exc->getMessage());
      error_log($exc->getTraceAsString());
    }
}