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
  //export request/result country
  $formQuery ="SELECT value FROM global_config where name='vl_form'";
  $formResult = $db->rawQuery($formQuery);
  $country = $formResult[0]['value'];
  //vl instance id
  $vlInstanceQuery ="SELECT vlsm_instance_id FROM vl_instance";
  $vlInstanceResult = $db->rawQuery($vlInstanceQuery);
  $vlInstanceId = $vlInstanceResult[0]['vlsm_instance_id'];
  //get synced path
  $configQuery ="SELECT value FROM global_config where name='sync_path'";
  $configResult = $db->rawQuery($configQuery);
  $vlResult = array();
  if(isset($param) && $param == 'request'){
    $vlQuery="SELECT vl.*,f.*,ts.*,s.*,b.batch_code,rby.user_name as resultReviewedBy,aby.user_name as resultApprovedBy,cby.user_name as requestCreatedBy,lmby.user_name as lastModifiedBy,r_f.facility_name as rejectionFacility,r_r_r.rejection_reason_name as rejectionReason,r_s_r.sample_name as routineSampleType,r_s_ac.sample_name as acSampleType,r_s_f.sample_name as failureSampleType,form.form_name from vl_request_form as vl INNER JOIN facility_details as f ON vl.facility_id=f.facility_id INNER JOIN r_sample_status as ts ON ts.status_id=vl.result_status LEFT JOIN r_sample_type as s ON s.sample_id=vl.sample_type LEFT JOIN batch_details as b ON b.batch_id=vl.sample_batch_id LEFT JOIN user_details as rby ON rby.user_id = vl.result_reviewed_by LEFT JOIN user_details as aby ON aby.user_id = vl.result_approved_by INNER JOIN user_details as cby ON cby.user_id = vl.request_created_by LEFT JOIN user_details as lmby ON lmby.user_id = vl.last_modified_by LEFT JOIN facility_details as r_f ON r_f.facility_id = vl.sample_rejection_facility LEFT JOIN r_sample_rejection_reasons as r_r_r ON r_r_r.rejection_reason_id = vl.reason_for_sample_rejection LEFT JOIN r_sample_type as r_s_r ON r_s_r.sample_id = vl.last_vl_sample_type_routine LEFT JOIN r_sample_type as r_s_ac ON r_s_ac.sample_id = vl.last_vl_sample_type_failure_ac LEFT JOIN r_sample_type as r_s_f ON r_s_f.sample_id = vl.last_vl_sample_type_failure LEFT JOIN form_details as form ON form.vlsm_country_id = vl.vlsm_country_id WHERE vl.vlsm_country_id = $country AND (vl.reason_for_sample_rejection = '' OR vl.reason_for_sample_rejection IS NULL OR vl.reason_for_sample_rejection = 0) AND (vl.result = '' OR vl.result IS NULL) AND vl.test_request_export = 0";
    $vlResult = $db->rawQuery($vlQuery);
  }else if(isset($param) && $param == 'result'){
    $vlQuery="SELECT vl.*,f.*,ts.*,s.*,b.batch_code,rby.user_name as resultReviewedBy,aby.user_name as resultApprovedBy,cby.user_name as requestCreatedBy,lmby.user_name as lastModifiedBy,r_f.facility_name as rejectionFacility,r_r_r.rejection_reason_name as rejectionReason,r_s_r.sample_name as routineSampleType,r_s_ac.sample_name as acSampleType,r_s_f.sample_name as failureSampleType,form.form_name from vl_request_form as vl INNER JOIN facility_details as f ON vl.facility_id=f.facility_id INNER JOIN r_sample_status as ts ON ts.status_id=vl.result_status LEFT JOIN r_sample_type as s ON s.sample_id=vl.sample_type LEFT JOIN batch_details as b ON b.batch_id=vl.sample_batch_id LEFT JOIN user_details as rby ON rby.user_id = vl.result_reviewed_by LEFT JOIN user_details as aby ON aby.user_id = vl.result_approved_by INNER JOIN user_details as cby ON cby.user_id = vl.request_created_by LEFT JOIN user_details as lmby ON lmby.user_id = vl.last_modified_by LEFT JOIN facility_details as r_f ON r_f.facility_id = vl.sample_rejection_facility LEFT JOIN r_sample_rejection_reasons as r_r_r ON r_r_r.rejection_reason_id = vl.reason_for_sample_rejection LEFT JOIN r_sample_type as r_s_r ON r_s_r.sample_id = vl.last_vl_sample_type_routine LEFT JOIN r_sample_type as r_s_ac ON r_s_ac.sample_id = vl.last_vl_sample_type_failure_ac LEFT JOIN r_sample_type as r_s_f ON r_s_f.sample_id = vl.last_vl_sample_type_failure LEFT JOIN form_details as form ON form.vlsm_country_id = vl.vlsm_country_id WHERE vl.vlsm_country_id = $country AND (vl.result!= '' AND vl.result IS NOT NULL) AND vl.test_result_export = 0";
    $vlResult = $db->rawQuery($vlQuery);
  }
  if(count($vlResult) >0){
    if(isset($configResult[0]['value']) && trim($configResult[0]['value'])!= '' && file_exists($configResult[0]['value'])){
         if(!file_exists($configResult[0]['value'] . DIRECTORY_SEPARATOR . "request")){
              mkdir($configResult[0]['value'] . DIRECTORY_SEPARATOR . "request");
         } if(!file_exists($configResult[0]['value'] . DIRECTORY_SEPARATOR . "result")){
              mkdir($configResult[0]['value'] . DIRECTORY_SEPARATOR . "result");
         }
         if(!file_exists($configResult[0]['value'] . DIRECTORY_SEPARATOR . "request" . DIRECTORY_SEPARATOR . "new")){
            mkdir($configResult[0]['value'] . DIRECTORY_SEPARATOR . "request" . DIRECTORY_SEPARATOR . "new");  
         }if(!file_exists($configResult[0]['value'] . DIRECTORY_SEPARATOR . "result" . DIRECTORY_SEPARATOR . "new")){
            mkdir($configResult[0]['value'] . DIRECTORY_SEPARATOR . "result" . DIRECTORY_SEPARATOR . "new");  
         }
         if(!file_exists($configResult[0]['value'] . DIRECTORY_SEPARATOR . "request" . DIRECTORY_SEPARATOR . "synced")){
            mkdir($configResult[0]['value'] . DIRECTORY_SEPARATOR . "request" . DIRECTORY_SEPARATOR . "synced"); 
         }if(!file_exists($configResult[0]['value'] . DIRECTORY_SEPARATOR . "result" . DIRECTORY_SEPARATOR . "synced")){
            mkdir($configResult[0]['value'] . DIRECTORY_SEPARATOR . "result" . DIRECTORY_SEPARATOR . "synced"); 
         }
         if(!file_exists($configResult[0]['value'] . DIRECTORY_SEPARATOR . "request" . DIRECTORY_SEPARATOR . "error")){
            mkdir($configResult[0]['value'] . DIRECTORY_SEPARATOR . "request" . DIRECTORY_SEPARATOR . "error"); 
         }if(!file_exists($configResult[0]['value'] . DIRECTORY_SEPARATOR . "result" . DIRECTORY_SEPARATOR . "error")){
            mkdir($configResult[0]['value'] . DIRECTORY_SEPARATOR . "result" . DIRECTORY_SEPARATOR . "error"); 
         }
        foreach($vlResult as $vl){
          //xml file creation start
          $xmlData = '';
          $xmlData.= "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
          $xmlData.="<vlsm>\n";
            $xmlData.="<facility>";
              $xmlData.="<facility_id>".$vl['facility_code']."</facility_id>\n";
              $xmlData.="<facility_name>".$vl['facility_name']."</facility_name>\n";
              $xmlData.="<facility_state>".$vl['facility_state']."</facility_state>\n";
              $xmlData.="<facility_district>".$vl['facility_district']."</facility_district>\n";
              $xmlData.="<facility_hub_name>".$vl['facility_hub_name']."</facility_hub_name>\n";
              $xmlData.="<facility_sample_id>".$vl['facility_sample_id']."</facility_sample_id>\n";
              $xmlData.="<facility_request_clinician_name>".$vl['request_clinician_name']."</facility_request_clinician_name>\n";
              $xmlData.="<facility_request_clinician_phone_number>".$vl['request_clinician_phone_number']."</facility_request_clinician_phone_number>\n";
              $xmlData.="<facility_support_partner>".$vl['facility_support_partner']."</facility_support_partner>\n";
              $xmlData.="<facility_physician_name>".$vl['physician_name']."</facility_physician_name>\n";
              $xmlData.="<facility_date_test_ordered_by_physician>".$vl['date_test_ordered_by_physician']."</facility_date_test_ordered_by_physician>\n";
              $xmlData.="<facility_sample_collection_date>".$vl['sample_collection_date']."</facility_sample_collection_date>\n";
              $xmlData.="<facility_sample_collected_by>".$vl['sample_collected_by']."</facility_sample_collected_by>\n";
              $xmlData.="<facility_mobile_numbers>".$vl['facility_mobile_numbers']."</facility_mobile_numbers>\n";
              $xmlData.="<facility_emails>".$vl['facility_emails']."</facility_emails>\n";
              $xmlData.="<facility_test_urgency>".$vl['test_urgency']."</facility_test_urgency>\n";
            $xmlData.="</facility>";
            $xmlData.="<patient>";
              $xmlData.="<patient_art_no>".$vl['patient_art_no']."</patient_art_no>\n";
              $xmlData.="<patient_anc_no>".$vl['patient_anc_no']."</patient_anc_no>\n";
              $xmlData.="<patient_nationality>".$vl['patient_nationality']."</patient_nationality>\n";
              $xmlData.="<patient_other_id>".$vl['patient_other_id']."</patient_other_id>\n";
              $xmlData.="<patient_first_name>".$vl['patient_first_name']."</patient_first_name>\n";
              $xmlData.="<patient_last_name>".$vl['patient_last_name']."</patient_last_name>\n";
              $xmlData.="<patient_dob>".$vl['patient_dob']."</patient_dob>\n";
              $xmlData.="<patient_gender>".$vl['patient_gender']."</patient_gender>\n";
              $xmlData.="<patient_age_in_years>".$vl['patient_age_in_years']."</patient_age_in_years>\n";
              $xmlData.="<patient_age_in_months>".$vl['patient_age_in_months']."</patient_age_in_months>\n";
              $xmlData.="<patient_consent_to_receive_sms>".$vl['consent_to_receive_sms']."</patient_consent_to_receive_sms>\n";
              $xmlData.="<patient_mobile_number>".$vl['patient_mobile_number']."</patient_mobile_number>\n";
              $xmlData.="<patient_location>".$vl['patient_location']."</patient_location>\n";
              $xmlData.="<patient_vl_focal_person>".$vl['vl_focal_person']."</patient_vl_focal_person>\n";
              $xmlData.="<patient_vl_focal_person_phone_number>".$vl['vl_focal_person_phone_number']."</patient_vl_focal_person_phone_number>\n";
              $xmlData.="<patient_address>".$vl['patient_address']."</patient_address>\n";
            $xmlData.="</patient>";
            $xmlData.="<treatment>";
              $routineSampleType = (isset($vl['routineSampleType']))?$vl['routineSampleType']:'';
              $acSampleType = (isset($vl['acSampleType']))?$vl['acSampleType']:'';
              $failureSampleType = (isset($vl['failureSampleType']))?$vl['failureSampleType']:'';
              $xmlData.="<treatment_is_patient_new>".$vl['is_patient_new']."</treatment_is_patient_new>\n";
              $xmlData.="<treatment_patient_art_date>".$vl['patient_art_date']."</treatment_patient_art_date>\n";
              $xmlData.="<treatment_reason_for_vl_testing>".$vl['reason_for_vl_testing']."</treatment_reason_for_vl_testing>\n";
              $xmlData.="<treatment_is_patient_pregnant>".$vl['is_patient_pregnant']."</treatment_is_patient_pregnant>\n";
              $xmlData.="<treatment_is_patient_breastfeeding>".$vl['is_patient_breastfeeding']."</treatment_is_patient_breastfeeding>\n";
              $xmlData.="<treatment_pregnancy_trimester>".$vl['pregnancy_trimester']."</treatment_pregnancy_trimester>\n";
              $xmlData.="<treatment_date_of_initiation_of_current_regimen>".$vl['date_of_initiation_of_current_regimen']."</treatment_date_of_initiation_of_current_regimen>\n";
              $xmlData.="<treatment_last_vl_date_routine>".$vl['last_vl_date_routine']."</treatment_last_vl_date_routine>\n";
              $xmlData.="<treatment_last_vl_result_routine>".$vl['last_vl_result_routine']."</treatment_last_vl_result_routine>\n";
              $xmlData.="<treatment_last_vl_sample_type_routine>".$routineSampleType."</treatment_last_vl_sample_type_routine>\n";
              $xmlData.="<treatment_last_vl_date_failure_ac>".$vl['last_vl_date_failure_ac']."</treatment_last_vl_date_failure_ac>\n";
              $xmlData.="<treatment_last_vl_result_failure_ac>".$vl['last_vl_result_failure_ac']."</treatment_last_vl_result_failure_ac>\n";
              $xmlData.="<treatment_last_vl_sample_type_failure_ac>".$acSampleType."</treatment_last_vl_sample_type_failure_ac>\n";
              $xmlData.="<treatment_last_vl_date_failure>".$vl['last_vl_date_failure']."</treatment_last_vl_date_failure>\n";
              $xmlData.="<treatment_last_vl_result_failure>".$vl['last_vl_result_failure']."</treatment_last_vl_result_failure>\n";
              $xmlData.="<treatment_last_vl_sample_type_failure>".$failureSampleType."</treatment_last_vl_sample_type_failure>\n";
              $xmlData.="<treatment_has_patient_changed_regimen>".$vl['has_patient_changed_regimen']."</treatment_has_patient_changed_regimen>\n";
              $xmlData.="<treatment_reason_for_regimen_change>".$vl['reason_for_regimen_change']."</treatment_reason_for_regimen_change>\n";
              $xmlData.="<treatment_regimen_change_date>".$vl['regimen_change_date']."</treatment_regimen_change_date>\n";
              $xmlData.="<treatment_arv_adherance_percentage>".$vl['arv_adherance_percentage']."</treatment_arv_adherance_percentage>\n";
              $xmlData.="<treatment_is_adherance_poor>".$vl['is_adherance_poor']."</treatment_is_adherance_poor>\n";
              $xmlData.="<treatment_last_vl_result_in_log>".$vl['last_vl_result_in_log']."</treatment_last_vl_result_in_log>\n";
              $xmlData.="<treatment_vl_test_number>".$vl['vl_test_number']."</treatment_vl_test_number>\n";
              $xmlData.="<treatment_number_of_enhanced_sessions>".$vl['number_of_enhanced_sessions']."</treatment_number_of_enhanced_sessions>\n";
            $xmlData.="</treatment>";
            $xmlData.="<sample>";
              $sampleType = (isset($vl['sample_name']))?$vl['sample_name']:'';
              $rejectionFacility = (isset($vl['rejectionFacility']))?$vl['rejectionFacility']:'';
              $rejectionReason = (isset($vl['rejectionReason']))?$vl['rejectionReason']:'';
              $xmlData.="<sample_code>".$vl['sample_code']."</sample_code>\n";
              $xmlData.="<sample_type>".$sampleType."</sample_type>\n";
              $xmlData.="<sample_is_sample_rejected>".$vl['is_sample_rejected']."</sample_is_sample_rejected>\n";
              $xmlData.="<sample_rejection_facility>".$rejectionFacility."</sample_rejection_facility>\n";
              $xmlData.="<sample_reason_for_sample_rejection>".$rejectionReason."</sample_reason_for_sample_rejection>\n";
              $xmlData.="<sample_plasma_conservation_temperature>".$vl['plasma_conservation_temperature']."</sample_plasma_conservation_temperature>\n";
              $xmlData.="<sample_plasma_conservation_duration>".$vl['plasma_conservation_duration']."</sample_plasma_conservation_duration>\n";
              $xmlData.="<sample_vl_test_platform>".$vl['vl_test_platform']."</sample_vl_test_platform>\n";
              $xmlData.="<sample_result_status>".$vl['status_name']."</sample_result_status>\n";
            $xmlData.="</sample>";
            $xmlData.="<viral_load_lab>";
              $batchCode = (isset($vl['batch_code']))?$vl['batch_code']:'';
              if(trim($vl['lab_id'])!= '' && $vl['lab_id'] >0){
                $fQuery="SELECT * FROM facility_details WHERE facility_type ='2' AND facility_id='".$vl['lab_id']."'";
                $fResult = $db->query($fQuery);
                if(count($fResult)> 0){
                  $xmlData.="<viral_load_lab_id>".$fResult[0]['facility_code']."</viral_load_lab_id>\n";
                  $xmlData.="<viral_load_lab_name>".$fResult[0]['facility_name']."</viral_load_lab_name>\n";
                  $xmlData.="<viral_load_lab_contact_person>".$vl['lab_contact_person']."</viral_load_lab_contact_person>\n";
                  $xmlData.="<viral_load_lab_phone_number>".$vl['lab_phone_number']."</viral_load_lab_phone_number>\n";
                  $xmlData.="<viral_load_lab_email_id>".$fResult[0]['facility_emails']."</viral_load_lab_email_id>\n";
                  $xmlData.="<viral_load_lab_sample_received_at_vl_lab_datetime>".$vl['sample_received_at_vl_lab_datetime']."</viral_load_lab_sample_received_at_vl_lab_datetime>\n";
                  $xmlData.="<viral_load_lab_sample_tested_datetime>".$vl['sample_tested_datetime']."</viral_load_lab_sample_tested_datetime>\n";
                  $xmlData.="<viral_load_lab_sample_batch_id>".$batchCode."</viral_load_lab_sample_batch_id>\n";
                  $xmlData.="<viral_load_lab_result_dispatched_datetime>".$vl['result_dispatched_datetime']."</viral_load_lab_result_dispatched_datetime>\n";
                }else{
                  $xmlData.="<viral_load_lab_id></viral_load_lab_id>\n";
                  $xmlData.="<viral_load_lab_name></viral_load_lab_name>\n";
                  $xmlData.="<viral_load_lab_contact_person></viral_load_lab_contact_person>\n";
                  $xmlData.="<viral_load_lab_phone_number></viral_load_lab_phone_number>\n";
                  $xmlData.="<viral_load_lab_email_id></viral_load_lab_email_id>\n";
                  $xmlData.="<viral_load_lab_sample_received_at_vl_lab_datetime></viral_load_lab_sample_received_at_vl_lab_datetime>\n";
                  $xmlData.="<viral_load_lab_sample_tested_datetime></viral_load_lab_sample_tested_datetime>\n";
                  $xmlData.="<viral_load_lab_sample_batch_id></viral_load_lab_sample_batch_id>\n";
                  $xmlData.="<viral_load_lab_result_dispatched_datetime></viral_load_lab_result_dispatched_datetime>\n";
                }
              }else{
                $xmlData.="<viral_load_lab_id></viral_load_lab_id>\n";
                $xmlData.="<viral_load_lab_name></viral_load_lab_name>\n";
                $xmlData.="<viral_load_lab_contact_person></viral_load_lab_contact_person>\n";
                $xmlData.="<viral_load_lab_phone_number></viral_load_lab_phone_number>\n";
                $xmlData.="<viral_load_lab_email_id></viral_load_lab_email_id>\n";
                $xmlData.="<viral_load_lab_sample_received_at_vl_lab_datetime></viral_load_lab_sample_received_at_vl_lab_datetime>\n";
                $xmlData.="<viral_load_lab_sample_tested_datetime></viral_load_lab_sample_tested_datetime>\n";
                $xmlData.="<viral_load_lab_sample_batch_id></viral_load_lab_sample_batch_id>\n";
                $xmlData.="<viral_load_lab_result_dispatched_datetime></viral_load_lab_result_dispatched_datetime>\n";
              }
            $xmlData.="</viral_load_lab>";
            $xmlData.="<sample_result>";
              $resultReviewedBy = (isset($vl['resultReviewedBy']))?$vl['resultReviewedBy']:'';
              $resultApprovedBy = (isset($vl['resultApprovedBy']))?$vl['resultApprovedBy']:'';
              $xmlData.="<sample_result_value_log>".$vl['result_value_log']."</sample_result_value_log>\n";
              $xmlData.="<sample_result_value_absolute>".$vl['result_value_absolute']."</sample_result_value_absolute>\n";
              $xmlData.="<sample_result_value_text>".$vl['result_value_text']."</sample_result_value_text>\n";
              $xmlData.="<sample_result_value>".$vl['result']."</sample_result_value>\n";
              $xmlData.="<sample_result_approver_comments>".$vl['approver_comments']."</sample_result_approver_comments>\n";
              $xmlData.="<sample_result_reviewed_by>".$resultReviewedBy."</sample_result_reviewed_by>\n";
              $xmlData.="<sample_result_reviewed_datetime>".$vl['result_reviewed_datetime']."</sample_result_reviewed_datetime>\n";
              $xmlData.="<sample_result_approved_by>".$resultApprovedBy."</sample_result_approved_by>\n";
              $xmlData.="<sample_result_approved_datetime>".$vl['result_approved_datetime']."</sample_result_approved_datetime>\n";
              $xmlData.="<sample_result_printed_datetime>".$vl['result_printed_datetime']."</sample_result_printed_datetime>\n";
              $xmlData.="<sample_result_sms_sent_datetime>".$vl['result_sms_sent_datetime']."</sample_result_sms_sent_datetime>\n";
            $xmlData.="</sample_result>";
            $xmlData.="<general>";
              $requestCreatedBy = (isset($vl['requestCreatedBy']))?$vl['requestCreatedBy']:'';
              $lastModifiedBy = (isset($vl['lastModifiedBy']))?$vl['lastModifiedBy']:'';
              $vlCountry = (isset($vl['form_name']))?$vl['form_name']:$country;
              $xmlData.="<general_vlsm_instance_id>".$vlInstanceId."</general_vlsm_instance_id>\n";
              $xmlData.="<general_vlsm_country_id>".$vlCountry."</general_vlsm_country_id>\n";
              $xmlData.="<general_is_request_mail_sent>".$vl['is_request_mail_sent']."</general_is_request_mail_sent>\n";
              $xmlData.="<general_is_result_mail_sent>".$vl['is_result_mail_sent']."</general_is_result_mail_sent>\n";
              $xmlData.="<general_is_result_sms_sent>".$vl['is_result_sms_sent']."</general_is_result_sms_sent>\n";
              $xmlData.="<general_manual_result_entry>".$vl['manual_result_entry']."</general_manual_result_entry>\n";
              $xmlData.="<general_request_created_by>".$requestCreatedBy."</general_request_created_by>\n";
              $xmlData.="<general_request_created_datetime>".$vl['request_created_datetime']."</general_request_created_datetime>\n";
              $xmlData.="<general_last_modified_by>".$lastModifiedBy."</general_last_modified_by>\n";
              $xmlData.="<general_last_modified_datetime>".$vl['last_modified_datetime']."</general_last_modified_datetime>\n";
              $xmlData.="<general_import_machine_file_name>".$vl['import_machine_file_name']."</general_import_machine_file_name>\n";
            $xmlData.="</general>";
          $xmlData.="</vlsm>\n";
          //xml file creation end
          //xml data encryption
          // hash
          $key = hash('sha256', $secret_key);
          // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
          $iv = substr(hash('sha256', $secret_iv), 0, 16);
          $crypttext = base64_encode(openssl_encrypt($xmlData, "AES-256-CBC", $key, 0, $iv));
          $fileName = $vl['sample_code'].'.xml';
          if(isset($param) && $param == 'request'){
            $fp = fopen($configResult[0]['value'] . DIRECTORY_SEPARATOR . "request" . DIRECTORY_SEPARATOR . "new". DIRECTORY_SEPARATOR. $fileName, 'w+');
            fwrite($fp, $crypttext);
            fclose($fp);
            //update test request export flag
            $db=$db->where('sample_code',$vl['sample_code']);
            $db->update($tableName,array('result_status'=>8,'test_request_export'=>1));
          }else if(isset($param) && $param == 'result'){
            $fp = fopen($configResult[0]['value'] . DIRECTORY_SEPARATOR . "result" . DIRECTORY_SEPARATOR . "new". DIRECTORY_SEPARATOR. $fileName, 'w+');
            fwrite($fp, $crypttext);
            fclose($fp);
            //update test result export flag
            $db=$db->where('sample_code',$vl['sample_code']);
            $db->update($tableName,array('test_result_export'=>1));
          }
        }
    }
  }
  //sync vl request import status
  if(isset($param) && $param == 'request'){
    if(file_exists($configResult[0]['value'] . DIRECTORY_SEPARATOR . "request" . DIRECTORY_SEPARATOR . "synced")){
      $files = scandir($configResult[0]['value'] . DIRECTORY_SEPARATOR . "request" . DIRECTORY_SEPARATOR . "synced");
      foreach($files as $file) {
         if(in_array($file, array(".",".."))) continue;
         $sampleCode = explode(".",$file);
         $vlQuery="SELECT sample_code FROM vl_request_form as vl WHERE vl.sample_code = '".$sampleCode[0]."' AND vl.test_request_import = 0";
         $vlResult = $db->rawQuery($vlQuery);
         if(isset($vlResult[0]['sample_code'])){
            $db=$db->where('sample_code',$vlResult[0]['sample_code']);
            $db->update($tableName,array('test_request_import'=>1));
         }
      }
    }
  }
}catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}