<?php
ob_start();
include('../includes/MysqliDb.php');
include('../includes/General.php');
$general = new Deforay_Commons_General();
$tableName = "vl_request_form";
$secret_key = "25c6c7ff35b9979b151f2136cd13b0ff";
$secret_iv = "po43pokdfgpo3k4y";
if(PHP_SAPI === 'cli'){
  $param = $argv[1];
}else{
  $param = $_GET['type'];
}
try {
  //import request/result country
  $formQuery ="SELECT value FROM global_config where name='vl_form'";
  $formResult = $db->rawQuery($formQuery);
  $country = $formResult[0]['value'];
  //vl instance id
  $vlInstanceQuery ="SELECT vlsm_instance_id FROM s_vlsm_instance";
  $vlInstanceResult = $db->rawQuery($vlInstanceQuery);
  $vlInstanceId = $vlInstanceResult[0]['vlsm_instance_id'];
  //get synced path
  $configQuery ="SELECT value FROM global_config where name='sync_path'";
  $configResult = $db->rawQuery($configQuery);
  if(isset($configResult[0]['value']) && trim($configResult[0]['value'])!= '' && file_exists($configResult[0]['value'])){
    $files = array();
    if(isset($param) && $param == 'request'){
      if(file_exists($configResult[0]['value'] . DIRECTORY_SEPARATOR . "request" . DIRECTORY_SEPARATOR . "new")){
        $files = scandir($configResult[0]['value'] . DIRECTORY_SEPARATOR . "request" . DIRECTORY_SEPARATOR . "new");
      }
    }else if(isset($param) && $param == 'result'){
      if(file_exists($configResult[0]['value'] . DIRECTORY_SEPARATOR . "result" . DIRECTORY_SEPARATOR . "new")){
        $files = scandir($configResult[0]['value'] . DIRECTORY_SEPARATOR . "result" . DIRECTORY_SEPARATOR . "new");
      }
    }
    if(count($files) >2){
      // hash
      $key = hash('sha256', $secret_key);
      // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
      $iv = substr(hash('sha256', $secret_iv), 0, 16);
      foreach($files as $file) {
        if (in_array($file, array(".",".."))) continue;
        if(isset($param) && $param == 'request'){
          //xml request data decryption
          $crypttext = file_get_contents($configResult[0]['value'] . DIRECTORY_SEPARATOR . "request" . DIRECTORY_SEPARATOR . "new" . DIRECTORY_SEPARATOR . $file);
          $decrypttext = trim(openssl_decrypt(base64_decode($crypttext), "AES-256-CBC", $key, 0, $iv));
          $fp = fopen($configResult[0]['value'] . DIRECTORY_SEPARATOR . "request" . DIRECTORY_SEPARATOR . "new". DIRECTORY_SEPARATOR. $file, 'w+');
          fwrite($fp, $decrypttext);
          fclose($fp);
          //schema validation
          $xml = new DOMDocument();
          $xml->load($configResult[0]['value'] . DIRECTORY_SEPARATOR . "request" . DIRECTORY_SEPARATOR . "new". DIRECTORY_SEPARATOR. $file);
          if(!$xml->schemaValidate(realpath(dirname(__FILE__)). DIRECTORY_SEPARATOR .'validate'. DIRECTORY_SEPARATOR . $country.'.xsd')) {
            $crypttext = base64_encode(openssl_encrypt($decrypttext, "AES-256-CBC", $key, 0, $iv));
            $fp = fopen($configResult[0]['value'] . DIRECTORY_SEPARATOR . "request" . DIRECTORY_SEPARATOR . "new". DIRECTORY_SEPARATOR. $file, 'w+');
            fwrite($fp, $crypttext);
            fclose($fp);
            copy($configResult[0]['value'] . DIRECTORY_SEPARATOR . "request" . DIRECTORY_SEPARATOR . "new" . DIRECTORY_SEPARATOR . $file,$configResult[0]['value'] . DIRECTORY_SEPARATOR . "request" . DIRECTORY_SEPARATOR . "error" . DIRECTORY_SEPARATOR . $file);
            unlink($configResult[0]['value'] . DIRECTORY_SEPARATOR . "request" . DIRECTORY_SEPARATOR . "new" . DIRECTORY_SEPARATOR . $file);
            break;
          }else{
            $crypttext = base64_encode(openssl_encrypt($decrypttext, "AES-256-CBC", $key, 0, $iv));
            $fp = fopen($configResult[0]['value'] . DIRECTORY_SEPARATOR . "request" . DIRECTORY_SEPARATOR . "new". DIRECTORY_SEPARATOR. $file, 'w+');
            fwrite($fp, $crypttext);
            fclose($fp);
          }
        }else if(isset($param) && $param == 'result'){
          //xml result data decryption
          $crypttext = file_get_contents($configResult[0]['value'] . DIRECTORY_SEPARATOR . "result" . DIRECTORY_SEPARATOR . "new" . DIRECTORY_SEPARATOR . $file);
          $decrypttext = openssl_decrypt(base64_decode($crypttext), "AES-256-CBC", $key, 0, $iv);
          $fp = fopen($configResult[0]['value'] . DIRECTORY_SEPARATOR . "result" . DIRECTORY_SEPARATOR . "new". DIRECTORY_SEPARATOR. $file, 'w+');
          fwrite($fp, $decrypttext);
          fclose($fp);
          //schema validation
          $xml = new DOMDocument();
          $xml->load($configResult[0]['value'] . DIRECTORY_SEPARATOR . "result" . DIRECTORY_SEPARATOR . "new". DIRECTORY_SEPARATOR. $file);
          if(!$xml->schemaValidate(realpath(dirname(__FILE__)). DIRECTORY_SEPARATOR .'validate'. DIRECTORY_SEPARATOR . $country.'.xsd')) {
            $crypttext = base64_encode(openssl_encrypt($decrypttext, "AES-256-CBC", $key, 0, $iv));
            $fp = fopen($configResult[0]['value'] . DIRECTORY_SEPARATOR . "result" . DIRECTORY_SEPARATOR . "new". DIRECTORY_SEPARATOR. $file, 'w+');
            fwrite($fp, $crypttext);
            fclose($fp);
            copy($configResult[0]['value'] . DIRECTORY_SEPARATOR . "result" . DIRECTORY_SEPARATOR . "new" . DIRECTORY_SEPARATOR . $file,$configResult[0]['value'] . DIRECTORY_SEPARATOR . "result" . DIRECTORY_SEPARATOR . "error" . DIRECTORY_SEPARATOR . $file);
            unlink($configResult[0]['value'] . DIRECTORY_SEPARATOR . "result" . DIRECTORY_SEPARATOR . "new" . DIRECTORY_SEPARATOR . $file);
            break;
          }else{
            $crypttext = base64_encode(openssl_encrypt($decrypttext, "AES-256-CBC", $key, 0, $iv));
            $fp = fopen($configResult[0]['value'] . DIRECTORY_SEPARATOR . "result" . DIRECTORY_SEPARATOR . "new". DIRECTORY_SEPARATOR. $file, 'w+');
            fwrite($fp, $crypttext);
            fclose($fp);
          }
        }
        $xml = new SimpleXMLElement($decrypttext);
        //facility section
        if(count($xml->facility) >0){
          if(isset($xml->facility->facility_id) && (string)$xml->facility->facility_id !=''){
            $clinicQuery = 'select facility_id from facility_details where facility_code = "'.(string)$xml->facility->facility_id.'"';
            $clinicResult = $db->rawQuery($clinicQuery);
          }else if(isset($xml->facility->facility_name) && (string)$xml->facility->facility_name !=''){
            $clinicQuery = 'select facility_id from facility_details where facility_name = "'.(string)$xml->facility->facility_name.'"';
            $clinicResult = $db->rawQuery($clinicQuery);
          }
          if(isset($xml->facility->facility_state) && (string)$xml->facility->facility_state !=''){
            $provinceQuery = "SELECT * from province_details where province_name='".(string)$xml->facility->facility_state."'";
            $provinceInfo=$db->query($provinceQuery);
            if(!$provinceInfo || count($provinceInfo) == 0){
              $db->insert('province_details',array('province_name'=>(string)$xml->facility->facility_state));
            }
          }
          if(isset($clinicResult[0]['facility_id'])){
            $data['facility_id'] = $clinicResult[0]['facility_id'];
          }else{
            if((string)$xml->facility->facility_id !='' || (string)$xml->facility->facility_name !=''){
              $clinicData = array(
                'facility_name'=>(isset($xml->facility->facility_name))?(string)$xml->facility->facility_name:null,
                'facility_code'=>(isset($xml->facility->facility_id))?(string)$xml->facility->facility_id:null,
                'vlsm_instance_id'=>$vlInstanceId,
                'other_id'=>null,
                'contact_person'=>null,
                'facility_mobile_numbers'=>(isset($xml->facility->facility_mobile_numbers))?(string)$xml->facility->facility_mobile_numbers:null,
                'address'=>null,
                'country'=>$country,
                'facility_state'=>(isset($xml->facility->facility_state))?(string)$xml->facility->facility_state:null,
                'facility_district'=>(isset($xml->facility->facility_district))?(string)$xml->facility->facility_district:null,
                'facility_hub_name'=>(isset($xml->facility->facility_hub_name))?(string)$xml->facility->facility_hub_name:null,
                'latitude'=>null,
                'longitude'=>null,
                'facility_emails'=>(isset($xml->facility->facility_emails))?(string)$xml->facility->facility_emails:null,
                'facility_type'=>1,
                'status'=>'active'
              );
              $id = $db->insert('facility_details',$clinicData);
              $data['facility_id'] = $id;
            }
          }
          $data['facility_sample_id'] = (isset($xml->facility->facility_sample_id))?(string)$xml->facility->facility_sample_id:null;
          $data['request_clinician_name'] = (isset($xml->facility->facility_request_clinician_name))?(string)$xml->facility->facility_request_clinician_name:null;
          $data['request_clinician_phone_number'] = (isset($xml->facility->facility_request_clinician_phone_number))?(string)$xml->facility->facility_request_clinician_phone_number:null;
          $data['facility_support_partner'] = (isset($xml->facility->facility_support_partner))?(string)$xml->facility->facility_support_partner:null;
          $data['physician_name'] = (isset($xml->facility->facility_physician_name))?(string)$xml->facility->facility_physician_name:null;
          $data['date_test_ordered_by_physician'] = (isset($xml->facility->facility_date_test_ordered_by_physician) && (string)$xml->facility->facility_date_test_ordered_by_physician !='' && (string)$xml->facility->facility_date_test_ordered_by_physician !='0000-00-00')?(string)$xml->facility->facility_date_test_ordered_by_physician:null;
          $data['sample_collection_date'] = (isset($xml->facility->facility_sample_collection_date))?(string)$xml->facility->facility_sample_collection_date:null;
          $data['sample_collected_by'] = (isset($xml->facility->facility_sample_collected_by))?(string)$xml->facility->facility_sample_collected_by:null;
          $data['test_urgency'] = (isset($xml->facility->facility_test_urgency))?(string)$xml->facility->facility_test_urgency:null;
        }
        //patient section
        if(count($xml->patient) >0){
          $data['patient_art_no'] = (isset($xml->patient->patient_art_no))?(string)$xml->patient->patient_art_no:null;
          $data['patient_anc_no'] = (isset($xml->patient->patient_anc_no))?(string)$xml->patient->patient_anc_no:null;
          $data['patient_nationality'] = (isset($xml->patient->patient_nationality))?(string)$xml->patient->patient_nationality:null;
          $data['patient_other_id'] = (isset($xml->patient->patient_other_id))?(string)$xml->patient->patient_other_id:null;
          $data['patient_first_name'] = (isset($xml->patient->patient_first_name))?(string)$xml->patient->patient_first_name:null;
          $data['patient_last_name'] = (isset($xml->patient->patient_last_name))?(string)$xml->patient->patient_last_name:null;
          $data['patient_dob'] = (isset($xml->patient->patient_dob) && (string)$xml->patient->patient_dob !='' && (string)$xml->patient->patient_dob !='0000-00-00')?(string)$xml->patient->patient_dob:null;
          $data['patient_gender'] = (isset($xml->patient->patient_gender))?(string)$xml->patient->patient_gender:null;
          $data['patient_age_in_years'] = (isset($xml->patient->patient_age_in_years))?(string)$xml->patient->patient_age_in_years:null;
          $data['patient_age_in_months'] = (isset($xml->patient->patient_age_in_months))?(string)$xml->patient->patient_age_in_months:null;
          $data['consent_to_receive_sms'] = (isset($xml->patient->patient_consent_to_receive_sms))?(string)$xml->patient->patient_consent_to_receive_sms:null;
          $data['patient_mobile_number'] = (isset($xml->patient->patient_mobile_number))?(string)$xml->patient->patient_mobile_number:null;
          $data['patient_location'] = (isset($xml->patient->patient_location))?(string)$xml->patient->patient_location:null;
          $data['vl_focal_person'] = (isset($xml->patient->patient_vl_focal_person))?(string)$xml->patient->patient_vl_focal_person:null;
          $data['vl_focal_person_phone_number'] = (isset($xml->patient->patient_vl_focal_person_phone_number))?(string)$xml->patient->patient_vl_focal_person_phone_number:null;
          $data['patient_address'] = (isset($xml->patient->patient_address))?(string)$xml->patient->patient_address:null;
        }
        //treatment section
        if(count($xml->treatment) >0){
          $data['is_patient_new'] = (isset($xml->treatment->treatment_is_patient_new))?(string)$xml->treatment->treatment_is_patient_new:null;
          $data['patient_art_date'] = (isset($xml->treatment->treatment_patient_art_date) && (string)$xml->treatment->treatment_patient_art_date !='' && (string)$xml->treatment->treatment_patient_art_date !='0000-00-00')?(string)$xml->treatment->treatment_patient_art_date:null;
          $data['reason_for_vl_testing'] = (isset($xml->treatment->treatment_reason_for_vl_testing))?(string)$xml->treatment->treatment_reason_for_vl_testing:null;
          $data['is_patient_pregnant'] = (isset($xml->treatment->treatment_is_patient_pregnant))?(string)$xml->treatment->treatment_is_patient_pregnant:null;
          $data['is_patient_breastfeeding'] = (isset($xml->treatment->treatment_is_patient_breastfeeding))?(string)$xml->treatment->treatment_is_patient_breastfeeding:null;
          $data['pregnancy_trimester'] = (isset($xml->treatment->treatment_pregnancy_trimester))?(string)$xml->treatment->treatment_pregnancy_trimester:null;
          $data['date_of_initiation_of_current_regimen'] = (isset($xml->treatment->treatment_date_of_initiation_of_current_regimen) && (string)$xml->treatment->treatment_date_of_initiation_of_current_regimen !='' && (string)$xml->treatment->treatment_date_of_initiation_of_current_regimen !='0000-00-00')?(string)$xml->treatment->treatment_date_of_initiation_of_current_regimen:null;
          $data['last_vl_date_routine'] = (isset($xml->treatment->treatment_last_vl_date_routine) && (string)$xml->treatment->treatment_last_vl_date_routine !='' && (string)$xml->treatment->treatment_last_vl_date_routine !='0000-00-00')?(string)$xml->treatment->treatment_last_vl_date_routine:null;
          $data['last_vl_result_routine'] = (isset($xml->treatment->treatment_last_vl_result_routine))?(string)$xml->treatment->treatment_last_vl_result_routine:null;
          if(isset($xml->treatment->treatment_last_vl_sample_type_routine) && (string)$xml->treatment->treatment_last_vl_sample_type_routine !=''){
            $specimenTypeQuery = 'select sample_id from r_sample_type where sample_name = "'.(string)$xml->treatment->treatment_last_vl_sample_type_routine.'"';
            $specimenResult = $db->rawQuery($specimenTypeQuery);
            if(isset($specimenResult[0]['sample_id'])){
              $data['last_vl_sample_type_routine'] = $specimenResult[0]['sample_id'];
            }else{
              $sampleTypeData = array(
                'sample_name'=>(string)$xml->treatment->treatment_last_vl_sample_type_routine,
                'status'=>'active'
              );
              $id = $db->insert('r_sample_type',$sampleTypeData);
              $data['last_vl_sample_type_routine'] = $id;
            }
          }
          $data['last_vl_date_failure_ac'] = (isset($xml->treatment->treatment_last_vl_date_failure_ac) && (string)$xml->treatment->treatment_last_vl_date_failure_ac !='' && (string)$xml->treatment->treatment_last_vl_date_failure_ac !='0000-00-00')?(string)$xml->treatment->treatment_last_vl_date_failure_ac:null;
          $data['last_vl_result_failure_ac'] = (isset($xml->treatment->treatment_last_vl_result_failure_ac))?(string)$xml->treatment->treatment_last_vl_result_failure_ac:null;
          if(isset($xml->treatment->treatment_last_vl_sample_type_failure_ac) && (string)$xml->treatment->treatment_last_vl_sample_type_failure_ac !=''){
            $specimenTypeQuery = 'select sample_id from r_sample_type where sample_name = "'.(string)$xml->treatment->treatment_last_vl_sample_type_failure_ac.'"';
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
          $data['last_vl_date_failure'] = (isset($xml->treatment->treatment_last_vl_date_failure) && (string)$xml->treatment->treatment_last_vl_date_failure !='' && (string)$xml->treatment->treatment_last_vl_date_failure !='0000-00-00')?(string)$xml->treatment->treatment_last_vl_date_failure:null;
          $data['last_vl_result_failure'] = (isset($xml->treatment->treatment_last_vl_result_failure))?(string)$xml->treatment->treatment_last_vl_result_failure:null;
          if(isset($xml->treatment->treatment_last_vl_sample_type_failure) && (string)$xml->treatment->treatment_last_vl_sample_type_failure !=''){
            $specimenTypeQuery = 'select sample_id from r_sample_type where sample_name = "'.(string)$xml->treatment->treatment_last_vl_sample_type_failure.'"';
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
          $data['has_patient_changed_regimen'] = (isset($xml->treatment->treatment_has_patient_changed_regimen))?(string)$xml->treatment->treatment_has_patient_changed_regimen:null;
          $data['reason_for_regimen_change'] = (isset($xml->treatment->treatment_reason_for_regimen_change))?(string)$xml->treatment->treatment_reason_for_regimen_change:null;
          $data['regimen_change_date'] = (isset($xml->treatment->treatment_regimen_change_date) && (string)$xml->treatment->treatment_regimen_change_date !='' && (string)$xml->treatment->treatment_regimen_change_date !='0000-00-00')?(string)$xml->treatment->treatment_regimen_change_date:null;
          $data['arv_adherance_percentage'] = (isset($xml->treatment->treatment_arv_adherance_percentage))?(string)$xml->treatment->treatment_arv_adherance_percentage:null;
          $data['is_adherance_poor'] = (isset($xml->treatment->treatment_is_adherance_poor))?(string)$xml->treatment->treatment_is_adherance_poor:null;
          $data['last_vl_result_in_log'] = (isset($xml->treatment->treatment_last_vl_result_in_log))?(string)$xml->treatment->treatment_last_vl_result_in_log:null;
          $data['vl_test_number'] = (isset($xml->treatment->treatment_vl_test_number))?(string)$xml->treatment->treatment_vl_test_number:null;
          $data['number_of_enhanced_sessions'] = (isset($xml->treatment->treatment_number_of_enhanced_sessions))?(string)$xml->treatment->treatment_number_of_enhanced_sessions:null;
        }
        //sample section
        if(count($xml->sample) >0){
          $data['sample_code'] = (isset($xml->sample->sample_code))?(string)$xml->sample->sample_code:null;
          $data['serial_no'] = (isset($xml->sample->sample_code))?(string)$xml->sample->sample_code:null;
          if(isset($xml->sample->sample_type) && (string)$xml->sample->sample_type !=''){
            $specimenTypeQuery = 'select sample_id from r_sample_type where sample_name = "'.(string)$xml->sample->sample_type.'"';
            $specimenResult = $db->rawQuery($specimenTypeQuery);
            if(isset($specimenResult[0]['sample_id'])){
              $data['sample_type'] = $specimenResult[0]['sample_id'];
            }else{
              $sampleTypeData = array(
                'sample_name'=>(string)$xml->sample->sample_type,
                'status'=>'active'
              );
              $id = $db->insert('r_sample_type',$sampleTypeData);
              $data['sample_type'] = $id;
            }
          }
          $data['is_sample_rejected'] = (isset($xml->sample->sample_is_sample_rejected))?(string)$xml->sample->sample_is_sample_rejected:null;
          if(isset($xml->sample->sample_rejection_facility) && (string)$xml->sample->sample_rejection_facility !=''){
            $rejectionClinicQuery = 'select facility_id from facility_details where facility_name = "'.(string)$xml->sample->sample_rejection_facility.'"';
            $rejectionClinicResult = $db->rawQuery($rejectionClinicQuery);
            if(isset($rejectionClinicResult[0]['facility_id'])){
              $data['sample_rejection_facility'] = $rejectionClinicResult[0]['facility_id'];
            }else{
              $clinicData = array(
                'facility_name'=>(isset($xml->sample->sample_rejection_facility))?(string)$xml->sample->sample_rejection_facility:null,
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
          if(isset($xml->sample->sample_reason_for_sample_rejection) && (string)$xml->sample->sample_reason_for_sample_rejection !=''){
            $rejectionReasonQuery = 'select rejection_reason_id from r_sample_rejection_reasons where rejection_reason_name = "'.(string)$xml->sample->sample_reason_for_sample_rejection.'"';
            $rejectionReasonQueryResult = $db->rawQuery($rejectionReasonQuery);
            if(isset($rejectionReasonQueryResult[0]['rejection_reason_id'])){
              $data['reason_for_sample_rejection'] = $rejectionReasonQueryResult[0]['rejection_reason_id'];
            }else{
              $rejectionReasonData = array(
                'rejection_reason_name'=>(string)$xml->sample->sample_reason_for_sample_rejection,
                'rejection_reason_status'=>'active'
              );
              $id = $db->insert('r_sample_rejection_reasons',$rejectionReasonData);
              $data['reason_for_sample_rejection'] = $id;
            }
          }
          $data['plasma_conservation_temperature'] = (isset($xml->sample->sample_plasma_conservation_temperature))?(string)$xml->sample->sample_plasma_conservation_temperature:null;
          $data['plasma_conservation_duration'] = (isset($xml->sample->sample_plasma_conservation_duration))?(string)$xml->sample->sample_plasma_conservation_duration:null;
          $data['vl_test_platform'] = (isset($xml->sample->sample_vl_test_platform))?(string)$xml->sample->sample_vl_test_platform:null;
          if(isset($xml->sample->sample_result_status) && (string)$xml->sample->sample_result_status !=''){
            $statusQuery = 'select status_id from r_sample_status where status_name = "'.(string)$xml->sample->sample_result_status.'"';
            $statusResult = $db->rawQuery($statusQuery);
            if(isset($statusResult[0]['status_id'])){
              $data['result_status'] = $statusResult[0]['status_id'];
            }else{
              $statusData = array(
                'status_name'=>(string)$xml->sample->sample_result_status
              );
              $id = $db->insert('r_sample_status',$statusData);
              $data['result_status'] = $id;
            }
          }
        }
        //viral load lab section
        if(count($xml->viral_load_lab) >0){
          if(isset($xml->viral_load_lab->viral_load_lab_id) && (string)$xml->viral_load_lab->viral_load_lab_id !=''){
            $labQuery = 'select facility_id from facility_details where facility_code = "'.(string)$xml->viral_load_lab->viral_load_lab_id.'"';
            $labResult = $db->rawQuery($labQuery);
          }else if(isset($xml->viral_load_lab->viral_load_lab_name) && (string)$xml->viral_load_lab->viral_load_lab_name !=''){
            $labQuery = 'select facility_id from facility_details where facility_name = "'.(string)$xml->viral_load_lab->viral_load_lab_name.'"';
            $labResult = $db->rawQuery($labQuery);
          }
          if(isset($labResult[0]['facility_id'])){
            $data['lab_id'] = $labResult[0]['facility_id'];
          }else{
            if((string)$xml->viral_load_lab->viral_load_lab_id !='' || (string)$xml->viral_load_lab->viral_load_lab_name !=''){
              $labData = array(
                'facility_name'=>(isset($xml->viral_load_lab->viral_load_lab_name))?(string)$xml->viral_load_lab->viral_load_lab_name:null,
                'facility_code'=>(isset($xml->viral_load_lab->viral_load_lab_id))?(string)$xml->viral_load_lab->viral_load_lab_id:null,
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
                'facility_emails'=>(isset($xml->viral_load_lab->viral_load_lab_email_id))?(string)$xml->viral_load_lab->viral_load_lab_email_id:null,
                'facility_type'=>2,
                'status'=>'active'
              );
              $id = $db->insert('facility_details',$labData);
              $data['lab_id'] = $id;
            }
          }
        //$data['lab_name'] = (isset($xml->viral_load_lab->viral_load_lab_name))?(string)$xml->viral_load_lab->viral_load_lab_name:null;
          $data['lab_contact_person'] = (isset($xml->viral_load_lab->viral_load_lab_contact_person))?(string)$xml->viral_load_lab->viral_load_lab_contact_person:null;
          $data['lab_phone_number'] = (isset($xml->viral_load_lab->viral_load_lab_phone_number))?(string)$xml->viral_load_lab->viral_load_lab_phone_number:null;
          $data['sample_received_at_vl_lab_datetime'] = (isset($xml->viral_load_lab->viral_load_lab_sample_received_at_vl_lab_datetime))?(string)$xml->viral_load_lab->viral_load_lab_sample_received_at_vl_lab_datetime:null;
          $data['sample_tested_datetime'] = (isset($xml->viral_load_lab->viral_load_lab_sample_tested_datetime))?(string)$xml->viral_load_lab->viral_load_lab_sample_tested_datetime:null;
          if(isset($xml->viral_load_lab->viral_load_lab_sample_batch_id) && (string)$xml->viral_load_lab->viral_load_lab_sample_batch_id !=''){
            $batchQuery = 'select batch_id from batch_details where batch_code = "'.(string)$xml->viral_load_lab->viral_load_lab_sample_batch_id.'"';
            $batchResult = $db->rawQuery($batchQuery);
            if(isset($batchResult[0]['batch_id'])){
              $data['sample_batch_id'] = $batchResult[0]['batch_id'];
            }else{
              $batchData = array(
                'machine'=>0,
                'batch_code'=>(string)$xml->viral_load_lab->viral_load_lab_sample_batch_id,
                'request_created_datetime'=>$general->getDateTime()
              );
              $id = $db->insert('batch_details',$batchData);
              $data['sample_batch_id'] = $id;
            }
          }
          $data['result_dispatched_datetime'] = (isset($xml->viral_load_lab->viral_load_lab_result_dispatched_datetime))?(string)$xml->viral_load_lab->viral_load_lab_result_dispatched_datetime:null;
        }
        //sample result section
        if(count($xml->sample_result) >0){
          $data['result_value_log'] = (isset($xml->sample_result->sample_result_value_log))?(string)$xml->sample_result->sample_result_value_log:null;
          $data['result_value_absolute'] = (isset($xml->sample_result->sample_result_value_absolute))?(string)$xml->sample_result->sample_result_value_absolute:null;
          $data['result_value_text'] = (isset($xml->sample_result->sample_result_value_text))?(string)$xml->sample_result->sample_result_value_text:null;
          $data['result'] = (isset($xml->sample_result->sample_result_value))?(string)$xml->sample_result->sample_result_value:null;
          $data['approver_comments'] = (isset($xml->sample_result->sample_result_approver_comments))?(string)$xml->sample_result->sample_result_approver_comments:null;
          if(isset($xml->sample_result->sample_result_reviewed_by) && (string)$xml->sample_result->sample_result_reviewed_by!= ''){
            $userQuery = 'select user_id from user_details where user_name = "'.(string)$xml->sample_result->sample_result_reviewed_by.'"';
            $userResult = $db->rawQuery($userQuery);
            if(isset($userResult[0]['user_id'])){
              $data['result_reviewed_by'] = $userResult[0]['user_id'];
            }else{
              $userData = array(
                'user_name'=>(string)$xml->sample_result->sample_result_reviewed_by,
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
          $data['result_reviewed_datetime'] = (isset($xml->sample_result->sample_result_reviewed_datetime) && (string)$xml->sample_result->sample_result_reviewed_datetime !='' && (string)$xml->sample_result->sample_result_reviewed_datetime !='0000-00-00 00:00:00')?(string)$xml->sample_result->sample_result_reviewed_datetime:null;
          if(isset($xml->sample_result->sample_result_approved_by) && (string)$xml->sample_result->sample_result_approved_by!= ''){
            $userQuery = 'select user_id from user_details where user_name = "'.(string)$xml->sample_result->sample_result_approved_by.'"';
            $userResult = $db->rawQuery($userQuery);
            if(isset($userResult[0]['user_id'])){
              $data['result_approved_by'] = $userResult[0]['user_id'];
            }else{
              $userData = array(
                'user_name'=>(string)$xml->sample_result->sample_result_approved_by,
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
          $data['result_approved_datetime'] = (isset($xml->sample_result->sample_result_approved_datetime) && (string)$xml->sample_result->sample_result_approved_datetime !='' && (string)$xml->sample_result->sample_result_approved_datetime !='0000-00-00 00:00:00')?(string)$xml->sample_result->sample_result_approved_datetime:null;
          $data['result_printed_datetime'] = (isset($xml->sample_result->sample_result_printed_datetime) && (string)$xml->sample_result->sample_result_printed_datetime !='' && (string)$xml->sample_result->sample_result_printed_datetime !='0000-00-00 00:00:00')?(string)$xml->sample_result->sample_result_printed_datetime:null;
          $data['result_sms_sent_datetime'] = (isset($xml->sample_result->sample_result_sms_sent_datetime) && (string)$xml->sample_result->sample_result_sms_sent_datetime !='' && (string)$xml->sample_result->sample_result_sms_sent_datetime !='0000-00-00 00:00:00')?(string)$xml->sample_result->sample_result_sms_sent_datetime:null;
        }
        //general section
        if(count($xml->general) >0){
          $data['vlsm_instance_id'] = $vlInstanceId;
          if(isset($xml->general->general_vlsm_country_id) && (string)$xml->general->general_vlsm_country_id!= ''){
            $formQuery = 'select vlsm_country_id from form_details where form_name = "'.(string)$xml->general->general_vlsm_country_id.'"';
            $formResult = $db->rawQuery($formQuery);
            if(isset($formResult[0]['vlsm_country_id'])){
              $data['vlsm_country_id'] = $formResult[0]['vlsm_country_id'];
            }else{
              $formData = array(
                'form_name'=>(string)$xml->general->general_vlsm_country_id
              );
              $id = $db->insert('form_details',$formData);
              $data['vlsm_country_id'] = $id;
            }
          }else{
            $data['vlsm_country_id'] = $country;
          }
          $data['is_request_mail_sent'] = (isset($xml->general->general_is_request_mail_sent))?(string)$xml->general->general_is_request_mail_sent:null;
          $data['is_result_mail_sent'] = (isset($xml->general->general_is_result_mail_sent))?(string)$xml->general->general_is_result_mail_sent:null;
          $data['is_result_sms_sent'] = (isset($xml->general->general_is_result_sms_sent))?(string)$xml->general->general_is_result_sms_sent:null;
          $data['manual_result_entry'] = (isset($xml->general->general_manual_result_entry))?(string)$xml->general->general_manual_result_entry:null;
          if(isset($xml->general->general_request_created_by) && (string)$xml->general->general_request_created_by!= ''){
            $userQuery = 'select user_id from user_details where user_name = "'.(string)$xml->general->general_request_created_by.'"';
            $userResult = $db->rawQuery($userQuery);
            if(isset($userResult[0]['user_id'])){
              $data['request_created_by'] = $userResult[0]['user_id'];
            }else{
              $userData = array(
                'user_name'=>(string)$xml->general->general_request_created_by,
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
          $data['request_created_datetime'] = (isset($xml->general->general_request_created_datetime))?(string)$xml->general->general_request_created_datetime:null;
          if(isset($xml->general->general_last_modified_by) && (string)$xml->general->general_last_modified_by!= ''){
            $userQuery = 'select user_id from user_details where user_name = "'.(string)$xml->general->general_last_modified_by.'"';
            $userResult = $db->rawQuery($userQuery);
            if(isset($userResult[0]['user_id'])){
              $data['last_modified_by'] = $userResult[0]['user_id'];
            }else{
              $userData = array(
                'user_name'=>(string)$xml->general->general_last_modified_by,
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
          $data['last_modified_datetime'] = (isset($xml->general->general_last_modified_datetime))?(string)$xml->general->general_last_modified_datetime:null;
          $data['import_machine_file_name'] = (isset($xml->general->general_import_machine_file_name))?(string)$xml->general->general_import_machine_file_name:null;
        }
        //request/result flag set
        if(isset($param) && $param == 'request'){
          $data['test_request_import'] = 1;
        }if(isset($param) && $param == 'result'){
          $data['test_result_import'] = 1;
        }
        //update request source
        if(isset($param) && $param == 'result'){
          $data['source'] = (isset($xml->general->general_vlsm_instance_id))?(string)$xml->general->general_vlsm_instance_id:null;
        }
        $sampleQuery = 'select vl_sample_id from vl_request_form where sample_code = "'.(string)$xml->sample->sample_code.'"';
        $sampleResult = $db->rawQuery($sampleQuery);
        if(isset($sampleResult[0]['vl_sample_id'])){
          $db=$db->where('sample_code',(string)$xml->sample->sample_code);
          $db->update($tableName,$data);
          //move file to respective sync folder
          if(isset($param) && $param == 'result'){
            if(file_exists($configResult[0]['value'] . DIRECTORY_SEPARATOR . "result" . DIRECTORY_SEPARATOR . "synced" . DIRECTORY_SEPARATOR . (string)$xml->sample->sample_code.'.xml')){
            unlink($configResult[0]['value'] . DIRECTORY_SEPARATOR . "result" . DIRECTORY_SEPARATOR . "synced" . DIRECTORY_SEPARATOR . (string)$xml->sample->sample_code.'.xml');
            }
            copy($configResult[0]['value'] . DIRECTORY_SEPARATOR . "result" . DIRECTORY_SEPARATOR . "new" . DIRECTORY_SEPARATOR . (string)$xml->sample->sample_code.'.xml',$configResult[0]['value'] . DIRECTORY_SEPARATOR . "result" . DIRECTORY_SEPARATOR . "synced" . DIRECTORY_SEPARATOR . (string)$xml->sample->sample_code.'.xml');
            unlink($configResult[0]['value'] . DIRECTORY_SEPARATOR . "result" . DIRECTORY_SEPARATOR . "new" . DIRECTORY_SEPARATOR . (string)$xml->sample->sample_code.'.xml');
          }else if(isset($param) && $param == 'request'){
            if(file_exists($configResult[0]['value'] . DIRECTORY_SEPARATOR . "request" . DIRECTORY_SEPARATOR . "synced" . DIRECTORY_SEPARATOR . (string)$xml->sample->sample_code.'.xml')){
            unlink($configResult[0]['value'] . DIRECTORY_SEPARATOR . "request" . DIRECTORY_SEPARATOR . "synced" . DIRECTORY_SEPARATOR . (string)$xml->sample->sample_code.'.xml');
            }
            copy($configResult[0]['value'] . DIRECTORY_SEPARATOR . "request" . DIRECTORY_SEPARATOR . "new" . DIRECTORY_SEPARATOR . (string)$xml->sample->sample_code.'.xml',$configResult[0]['value'] . DIRECTORY_SEPARATOR . "request" . DIRECTORY_SEPARATOR . "synced" . DIRECTORY_SEPARATOR . (string)$xml->sample->sample_code.'.xml');
            unlink($configResult[0]['value'] . DIRECTORY_SEPARATOR . "request" . DIRECTORY_SEPARATOR . "new" . DIRECTORY_SEPARATOR . (string)$xml->sample->sample_code.'.xml');
          }
        }else{
          $id = $db->insert($tableName,$data);
          if($id >0){
            //move file to respective sync folder
            if(isset($param) && $param == 'request'){
              copy($configResult[0]['value'] . DIRECTORY_SEPARATOR . "request" . DIRECTORY_SEPARATOR . "new" . DIRECTORY_SEPARATOR . (string)$xml->sample->sample_code.'.xml',$configResult[0]['value'] . DIRECTORY_SEPARATOR . "request" . DIRECTORY_SEPARATOR . "synced" . DIRECTORY_SEPARATOR . (string)$xml->sample->sample_code.'.xml');
              unlink($configResult[0]['value'] . DIRECTORY_SEPARATOR . "request" . DIRECTORY_SEPARATOR . "new" . DIRECTORY_SEPARATOR . (string)$xml->sample->sample_code.'.xml');
            }else if(isset($param) && $param == 'result'){
              copy($configResult[0]['value'] . DIRECTORY_SEPARATOR . "result" . DIRECTORY_SEPARATOR . "new" . DIRECTORY_SEPARATOR . (string)$xml->sample->sample_code.'.xml',$configResult[0]['value'] . DIRECTORY_SEPARATOR . "result" . DIRECTORY_SEPARATOR . "synced" . DIRECTORY_SEPARATOR . (string)$xml->sample->sample_code.'.xml');
              unlink($configResult[0]['value'] . DIRECTORY_SEPARATOR . "result" . DIRECTORY_SEPARATOR . "new" . DIRECTORY_SEPARATOR . (string)$xml->sample->sample_code.'.xml');
            }
          }else{
            //move file to respective error folder
            if(isset($param) && $param == 'request'){
              copy($configResult[0]['value'] . DIRECTORY_SEPARATOR . "request" . DIRECTORY_SEPARATOR . "new" . DIRECTORY_SEPARATOR . (string)$xml->sample->sample_code.'.xml',$configResult[0]['value'] . DIRECTORY_SEPARATOR . "request" . DIRECTORY_SEPARATOR . "error" . DIRECTORY_SEPARATOR . (string)$xml->sample->sample_code.'.xml');
              unlink($configResult[0]['value'] . DIRECTORY_SEPARATOR . "request" . DIRECTORY_SEPARATOR . "new" . DIRECTORY_SEPARATOR . (string)$xml->sample->sample_code.'.xml');
            }else if(isset($param) && $param == 'result'){
              copy($configResult[0]['value'] . DIRECTORY_SEPARATOR . "result" . DIRECTORY_SEPARATOR . "new" . DIRECTORY_SEPARATOR . (string)$xml->sample->sample_code.'.xml',$configResult[0]['value'] . DIRECTORY_SEPARATOR . "result" . DIRECTORY_SEPARATOR . "error" . DIRECTORY_SEPARATOR . (string)$xml->sample->sample_code.'.xml');
              unlink($configResult[0]['value'] . DIRECTORY_SEPARATOR . "result" . DIRECTORY_SEPARATOR . "new" . DIRECTORY_SEPARATOR . (string)$xml->sample->sample_code.'.xml');
            }
          }
        }
      }
    }
    //sync vl result import status
    if(isset($param) && $param == 'result'){
      if(file_exists($configResult[0]['value'] . DIRECTORY_SEPARATOR . "result" . DIRECTORY_SEPARATOR . "synced")){
        $files = scandir($configResult[0]['value'] . DIRECTORY_SEPARATOR . "result" . DIRECTORY_SEPARATOR . "synced");
        foreach($files as $file) {
           if(in_array($file, array(".",".."))) continue;
           $sampleCode = explode(".",$file);
           $vlQuery="SELECT sample_code FROM vl_request_form as vl WHERE vl.sample_code = '".$sampleCode[0]."' AND vl.test_result_import = 0";
           $vlResult = $db->rawQuery($vlQuery);
           if(isset($vlResult[0]['sample_code'])){
              $db=$db->where('sample_code',$vlResult[0]['sample_code']);
              $db->update($tableName,array('test_result_import'=>1));
           }
        }
      }
    }
  }
}catch (Exception $exc) {
  error_log($exc->getMessage());
  error_log($exc->getTraceAsString());
}