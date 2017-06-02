<?php
ob_start();
include('../header.php');
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
    //get qr content
    $qrVal = explode(',',$_GET['q']);
     for($i=0;$i<count($qrVal);$i++){
        //facility section
        if($qrVal[0] !='' && $qrVal[0]!= null){
           $clinicQuery = 'select facility_id from facility_details where facility_code = "'.$qrVal[0].'"';
           $clinicResult = $db->rawQuery($clinicQuery);
        }else if($qrVal[1] !='' && $qrVal[1]!= null){
          $clinicQuery = 'select facility_id from facility_details where facility_name = "'.$qrVal[1].'"';
          $clinicResult = $db->rawQuery($clinicQuery);
        }
        if(trim($qrVal[2]) != '' && $qrVal[2]!= null){
            $provinceQuery="SELECT * from province_details where province_name='".$qrVal[2]."'";
            $provinceInfo=$db->query($provinceQuery);
            if(!isset($provinceInfo) || count($provinceInfo) == 0){
                $db->insert('province_details',array('province_name'=>$qrVal[2]));
            }
        }
        if(isset($clinicResult[0]['facility_id'])){
            $data['facility_id'] = $clinicResult[0]['facility_id'];
        }else{
            if(($qrVal[0] !='' && $qrVal[0]!= null) || ($qrVal[1] !='' && $qrVal[1]!= null)){
                $clinicData = array(
                  'facility_name'=>$qrVal[1],
                  'facility_code'=>$qrVal[0],
                  'vlsm_instance_id'=>$vlInstanceId,
                  'other_id'=>null,
                  'contact_person'=>null,
                  'facility_mobile_numbers'=>$qrVal[13],
                  'address'=>null,
                  'country'=>$country,
                  'facility_state'=>$qrVal[2],
                  'facility_district'=>$qrVal[3],
                  'facility_hub_name'=>$qrVal[4],
                  'latitude'=>null,
                  'longitude'=>null,
                  'facility_emails'=>$qrVal[14],
                  'facility_type'=>1,
                  'status'=>'active'
                );
                $id = $db->insert('facility_details',$clinicData);
                $data['facility_id'] = $id;
            }
        }
        $data['facility_sample_id'] = $qrVal[5];
        $data['request_clinician_name'] = $qrVal[6];
        $data['request_clinician_phone_number'] = $qrVal[7];
        $data['facility_support_partner'] = $qrVal[8];
        $data['physician_name'] = $qrVal[9];
        $data['date_test_ordered_by_physician'] = $qrVal[10];
        $data['sample_collection_date'] = $qrVal[11];
        $data['sample_collected_by'] = $qrVal[12];
        $data['test_urgency'] = $qrVal[15];
        //patient section
        $data['patient_art_no'] = $qrVal[16];
        $data['patient_anc_no'] = $qrVal[17];
        $data['patient_nationality'] = $qrVal[18];
        $data['patient_other_id'] = $qrVal[19];
        $data['patient_first_name'] = $qrVal[20];
        $data['patient_last_name'] = $qrVal[21];
        $data['patient_dob'] = $qrVal[22];
        $data['patient_gender'] = $qrVal[23];
        $data['patient_age_in_years'] = $qrVal[24];
        $data['patient_age_in_months'] = $qrVal[25];
        $data['consent_to_receive_sms'] = $qrVal[26];
        $data['patient_mobile_number'] = $qrVal[27];
        $data['patient_location'] = $qrVal[28];
        $data['vl_focal_person'] = $qrVal[29];
        $data['vl_focal_person_phone_number'] = $qrVal[30];
        $data['patient_address'] = $qrVal[31];
        //treatment section
        $data['is_patient_new'] = $qrVal[32];
        $data['patient_art_date'] = $qrVal[33];
        $data['reason_for_vl_testing'] = $qrVal[34];
        $data['is_patient_pregnant'] = $qrVal[35];
        $data['is_patient_breastfeeding'] = $qrVal[36];
        $data['pregnancy_trimester'] = $qrVal[37];
        $data['date_of_initiation_of_current_regimen'] = $qrVal[38];
        $data['last_vl_date_routine'] = $qrVal[39];
        $data['last_vl_result_routine'] = $qrVal[40];
        if(trim($qrVal[41])!=''){
            $specimenTypeQuery = 'select sample_id from r_sample_type where sample_name = "'.$qrVal[41].'"';
            $specimenResult = $db->rawQuery($specimenTypeQuery);
            if(isset($specimenResult[0]['sample_id'])){
              $data['last_vl_sample_type_routine'] = $specimenResult[0]['sample_id'];
            }else{
              $sampleTypeData = array(
                'sample_name'=>$qrVal[41],
                'status'=>'active'
              );
              $id = $db->insert('r_sample_type',$sampleTypeData);
              $data['last_vl_sample_type_routine'] = $id;
            }
        }
        $data['last_vl_date_failure_ac'] = $qrVal[42];
        $data['last_vl_result_failure_ac'] = $qrVal[43];
        if(trim($qrVal[44])!=''){
            $specimenTypeQuery = 'select sample_id from r_sample_type where sample_name = "'.$qrVal[44].'"';
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
        $data['last_vl_date_failure'] = $qrVal[45];
        $data['last_vl_result_failure'] = $qrVal[46];
        if(trim($qrVal[47])!=''){
          $specimenTypeQuery = 'select sample_id from r_sample_type where sample_name = "'.$qrVal[47].'"';
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
        $data['has_patient_changed_regimen'] = $qrVal[48];
        $data['reason_for_regimen_change'] = $qrVal[49];
        $data['regimen_change_date'] = $qrVal[50];
        $data['arv_adherance_percentage'] = $qrVal[51];
        $data['is_adherance_poor'] = $qrVal[52];
        $data['last_vl_result_in_log'] = $qrVal[53];
        $data['vl_test_number'] = $qrVal[54];
        $data['number_of_enhanced_sessions'] = $qrVal[55];
        //sample section
        $data['sample_code'] = $qrVal[56];
        $data['serial_no'] = $qrVal[56];
        if(trim($qrVal[57])!=''){
          $specimenTypeQuery = 'select sample_id from r_sample_type where sample_name = "'.$qrVal[57].'"';
          $specimenResult = $db->rawQuery($specimenTypeQuery);
          if(isset($specimenResult[0]['sample_id'])){
            $data['sample_type'] = $specimenResult[0]['sample_id'];
          }else{
            $sampleTypeData = array(
              'sample_name'=>$qrVal[57],
              'status'=>'active'
            );
            $id = $db->insert('r_sample_type',$sampleTypeData);
            $data['sample_type'] = $id;
          }
        }
        $data['is_sample_rejected'] = $qrVal[58];
        if(trim($qrVal[59])!=''){
            $rejectionClinicQuery = 'select facility_id from facility_details where facility_name = "'.$qrVal[59].'"';
            $rejectionClinicResult = $db->rawQuery($rejectionClinicQuery);
            if(isset($rejectionClinicResult[0]['facility_id'])){
              $data['sample_rejection_facility'] = $rejectionClinicResult[0]['facility_id'];
            }else{
              $clinicData = array(
                'facility_name'=>$qrVal[59],
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
        if(trim($qrVal[60]) !=''){
            $rejectionReasonQuery = 'select rejection_reason_id from r_sample_rejection_reasons where rejection_reason_name = "'.$qrVal[60].'"';
            $rejectionReasonQueryResult = $db->rawQuery($rejectionReasonQuery);
            if(isset($rejectionReasonQueryResult[0]['rejection_reason_id'])){
              $data['reason_for_sample_rejection'] = $rejectionReasonQueryResult[0]['rejection_reason_id'];
            }else{
              $rejectionReasonData = array(
                'rejection_reason_name'=>$qrVal[60],
                'rejection_reason_status'=>'active'
              );
              $id = $db->insert('r_sample_rejection_reasons',$rejectionReasonData);
              $data['reason_for_sample_rejection'] = $id;
            }
        }
        $data['plasma_conservation_temperature'] = $qrVal[61];
        $data['plasma_conservation_duration'] = $qrVal[62];
        $data['vl_test_platform'] = $qrVal[63];
        if(trim($qrVal[64]) !=''){
            $statusQuery = 'select status_id from r_sample_status where status_name = "'.$qrVal[64].'"';
            $statusResult = $db->rawQuery($statusQuery);
            if(isset($statusResult[0]['status_id'])){
              $data['result_status'] = $statusResult[0]['status_id'];
            }else{
              $statusData = array(
                'status_name'=>$qrVal[64]
              );
              $id = $db->insert('r_sample_status',$statusData);
              $data['result_status'] = $id;
            }
        }
        //viral load lab section
        if(trim($qrVal[65]) !=''){
            $labQuery = 'select facility_id from facility_details where facility_code = "'.$qrVal[65].'"';
            $labResult = $db->rawQuery($labQuery);
        }else if(trim($qrVal[66]) !=''){
            $labQuery = 'select facility_id from facility_details where facility_name = "'.$qrVal[66].'"';
            $labResult = $db->rawQuery($labQuery);
        }
        if(isset($labResult[0]['facility_id'])){
            $data['lab_id'] = $labResult[0]['facility_id'];
        }else{
          if(trim($qrVal[65]) !='' || trim($qrVal[66]) !=''){
            $labData = array(
              'facility_name'=>$qrVal[66],
              'facility_code'=>$qrVal[65],
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
            $id = $db->insert('facility_details',$labData);
            $data['lab_id'] = $id;
          }
        }
        $data['lab_contact_person'] = $qrVal[67];
        $data['lab_phone_number'] = $qrVal[68];
        $data['sample_received_at_vl_lab_datetime'] = $qrVal[70];
        $data['sample_tested_datetime'] = $qrVal[71];
        if(trim($qrVal[72]) !=''){
          $batchQuery = 'select batch_id from batch_details where batch_code = "'.$qrVal[72].'"';
          $batchResult = $db->rawQuery($batchQuery);
          if(isset($batchResult[0]['batch_id'])){
            $data['sample_batch_id'] = $batchResult[0]['batch_id'];
          }else{
            $batchData = array(
              'machine'=>0,
              'batch_code'=>$qrVal[72],
              'request_created_datetime'=>$general->getDateTime()
            );
            $id = $db->insert('batch_details',$batchData);
            $data['sample_batch_id'] = $id;
          }
        }
        $data['result_dispatched_datetime'] = $qrVal[73];
        //sample result section
        $data['result_value_log'] = $qrVal[74];
        $data['result_value_absolute'] = $qrVal[75];
        $data['result_value_text'] = $qrVal[76];
        $data['result'] = $qrVal[77];
        $data['approver_comments'] = $qrVal[78];
        if(trim($qrVal[79]) !=''){
            $userQuery = 'select user_id from user_details where user_name = "'.$qrVal[79].'"';
            $userResult = $db->rawQuery($userQuery);
            if(isset($userResult[0]['user_id'])){
              $data['result_reviewed_by'] = $userResult[0]['user_id'];
            }else{
              $userData = array(
                'user_name'=>$qrVal[79],
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
        $data['result_reviewed_datetime'] = $qrVal[80];
        if(trim($qrVal[81]) !=''){
          $userQuery = 'select user_id from user_details where user_name = "'.$qrVal[81].'"';
          $userResult = $db->rawQuery($userQuery);
          if(isset($userResult[0]['user_id'])){
            $data['result_approved_by'] = $userResult[0]['user_id'];
          }else{
            $userData = array(
              'user_name'=>$qrVal[81],
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
        $data['result_approved_datetime'] = $qrVal[82];
        $data['result_printed_datetime'] = $qrVal[83];
        $data['result_sms_sent_datetime'] = $qrVal[84];
        //general section
        $data['vlsm_instance_id'] = $vlInstanceId;
        if(trim($qrVal[86]) !=''){
            $formQuery = 'select vlsm_country_id from form_details where form_name = "'.$qrVal[86].'"';
            $formResult = $db->rawQuery($formQuery);
            if(isset($formResult[0]['vlsm_country_id'])){
              $data['vlsm_country_id'] = $formResult[0]['vlsm_country_id'];
            }else{
              $formData = array(
                'form_name'=>$qrVal[86]
              );
              $id = $db->insert('form_details',$formData);
              $data['vlsm_country_id'] = $id;
            }
        }else{
          $data['vlsm_country_id'] = $country;
        }
        $data['is_request_mail_sent'] = $qrVal[87];
        $data['is_result_mail_sent'] = $qrVal[88];
        $data['is_result_sms_sent'] = $qrVal[89];
        $data['manual_result_entry'] = $qrVal[90];
        if(trim($qrVal[91]) !=''){
            $userQuery = 'select user_id from user_details where user_name = "'.$qrVal[91].'"';
            $userResult = $db->rawQuery($userQuery);
            if(isset($userResult[0]['user_id'])){
              $data['request_created_by'] = $userResult[0]['user_id'];
            }else{
              $userData = array(
                'user_name'=>$qrVal[91],
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
        $data['request_created_datetime'] = $qrVal[92];
        if(trim($qrVal[93]) !=''){
          $userQuery = 'select user_id from user_details where user_name = "'.$qrVal[93].'"';
          $userResult = $db->rawQuery($userQuery);
          if(isset($userResult[0]['user_id'])){
            $data['last_modified_by'] = $userResult[0]['user_id'];
          }else{
            $userData = array(
              'user_name'=>$qrVal[93],
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
        $data['last_modified_datetime'] = $qrVal[94];
        $data['import_machine_file_name'] = $qrVal[95];
        $sampleQuery = 'select vl_sample_id from vl_request_form where sample_code = "'.$qrVal[56].'"';
        $sampleResult = $db->rawQuery($sampleQuery);
        if(isset($sampleResult[0]['vl_sample_id'])){
          $db=$db->where('sample_code',$qrVal[56]);
          $db->update($tableName,$data);
        }else{
          $db->insert($tableName,$data);
        }
     }
     $_SESSION['alertMsg'] = 'VL Request data successfully imported';
     header("location:/qr-code/readQRCode.php?q=&action=next");
    }catch (Exception $exc) {
      error_log($exc->getMessage());
      error_log($exc->getTraceAsString());
    }
}
?>
<div class="content-wrapper" style="min-height: 347px;">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <blockquote>
        <h3><i class="fa fa-hand-o-right" aria-hidden="true"></i> Please connect your QR code scanner with the computer and then scan the QR code image.</h3>
      </blockquote>
    </section>
</div>
<?php
 include('../footer.php');
?>