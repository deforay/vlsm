<?php
session_start();
ob_start();
include('../includes/MysqliDb.php');
include('../includes/General.php');
$general = new Deforay_Commons_General();
$tableName = "vl_request_form";
if(PHP_SAPI === 'cli'){
  $param = $argv[1];
}else{
  $param = $_GET['type'];
}
try {
  //export request/result xml
  $formQuery ="SELECT value FROM global_config where name='vl_form'";
  $formResult = $db->rawQuery($formQuery);
  $country = $formResult[0]['value'];
  $configQuery ="SELECT value FROM global_config where name='sync_path'";
  $configResult = $db->rawQuery($configQuery);
  $vlResult = array();
  if(isset($param) && $param == 'request'){
    $vlQuery="SELECT vl.*,f.*,ts.*,s.*,art.*,b.batch_id,b.machine,b.batch_code,b.batch_code_key,b.batch_status,cby.user_name as createdBy,cbyr.role_name as createdByRole,mby.user_name as modifiedBy,mbyr.role_name as modifiedByRole,appby.user_name as approvedBy,appbyr.role_name as approvedByRole,revby.user_name as reviewedBy,revbyr.role_name as reviewedByRole FROM vl_request_form as vl INNER JOIN facility_details as f ON vl.facility_id=f.facility_id INNER JOIN testing_status as ts ON ts.status_id=vl.status LEFT JOIN r_sample_type as s ON s.sample_id=vl.sample_id LEFT JOIN r_art_code_details as art ON vl.current_regimen=art.art_id LEFT JOIN batch_details as b ON b.batch_id=vl.batch_id INNER JOIN user_details as cby ON cby.user_id = vl.created_by INNER JOIN roles as cbyr ON cbyr.role_id = cby.role_id LEFT JOIN user_details as mby ON mby.user_id = vl.modified_by LEFT JOIN roles as mbyr ON mbyr.role_id = mby.role_id LEFT JOIN user_details as appby ON appby.user_id = vl.result_approved_by LEFT JOIN roles as appbyr ON appbyr.role_id = appby.role_id LEFT JOIN user_details as revby ON revby.user_id = vl.result_reviewed_by LEFT JOIN roles as revbyr ON revbyr.role_id = revby.role_id WHERE vl.form_id = $country AND vl.test_request_export = 0";
    $vlResult = $db->rawQuery($vlQuery);
  }else if(isset($param) && $param == 'result'){
    $vlQuery="SELECT vl.*,f.*,ts.*,s.*,art.*,b.batch_id,b.machine,b.batch_code,b.batch_code_key,b.batch_status,cby.user_name as createdBy,cbyr.role_name as createdByRole,mby.user_name as modifiedBy,mbyr.role_name as modifiedByRole,appby.user_name as approvedBy,appbyr.role_name as approvedByRole,revby.user_name as reviewedBy,revbyr.role_name as reviewedByRole FROM vl_request_form as vl INNER JOIN facility_details as f ON vl.facility_id=f.facility_id INNER JOIN testing_status as ts ON ts.status_id=vl.status LEFT JOIN r_sample_type as s ON s.sample_id=vl.sample_id LEFT JOIN r_art_code_details as art ON vl.current_regimen=art.art_id LEFT JOIN batch_details as b ON b.batch_id=vl.batch_id INNER JOIN user_details as cby ON cby.user_id = vl.created_by INNER JOIN roles as cbyr ON cbyr.role_id = cby.role_id LEFT JOIN user_details as mby ON mby.user_id = vl.modified_by LEFT JOIN roles as mbyr ON mbyr.role_id = mby.role_id LEFT JOIN user_details as appby ON appby.user_id = vl.result_approved_by LEFT JOIN roles as appbyr ON appbyr.role_id = appby.role_id LEFT JOIN user_details as revby ON revby.user_id = vl.result_reviewed_by LEFT JOIN roles as revbyr ON revbyr.role_id = revby.role_id WHERE vl.form_id = $country AND (vl.result!= '' AND vl.result IS NOT NULL) AND vl.test_result_export = 0";
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
          $machine = '';
          $modifiedBy = '';
          $modifiedByRole = '';
          $approvedBy = '';
          $approvedByRole = '';
          $reviewedBy = '';
          $reviewedByRole = '';
          if(isset($vl['machine']) && $vl['machine'] >0){
            $machine = $vl['machine'];
          }if(isset($param) && $param == 'request'){
            $vl['test_request_export'] = 1;
          }if(isset($param) && $param == 'result'){
            $vl['test_result_export'] = 1;
          }if(isset($vl['modifiedBy'])){
            $modifiedBy = $vl['modifiedBy'];
          }if(isset($vl['modifiedByRole'])){
            $modifiedByRole = $vl['modifiedByRole'];
          }if(isset($vl['approvedBy'])){
            $approvedBy = $vl['approvedBy'];
          }if(isset($vl['approvedByRole'])){
            $approvedByRole = $vl['approvedByRole'];
          }if(isset($vl['reviewedBy'])){
            $reviewedBy = $vl['reviewedBy'];
          }if(isset($vl['reviewedByRole'])){
            $reviewedByRole = $vl['reviewedByRole'];
          }
          $xmlData = '';
          $xmlData.= "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
          $xmlData.="<vlsm>\n";
            $xmlData.="<facility>";
              $xmlData.="<facility_name>".$vl['facility_name']."</facility_name>\n";
              $xmlData.="<facility_code>".$vl['facility_code']."</facility_code>\n";
              $xmlData.="<facility_contact_person>".$vl['contact_person']."</facility_contact_person>\n";
              $xmlData.="<facility_phone_number>".$vl['phone_number']."</facility_phone_number>\n";
              $xmlData.="<facility_address>".$vl['address']."</facility_address>\n";
              $xmlData.="<facility_country>".$vl['country']."</facility_country>\n";
              $xmlData.="<facility_state>".$vl['state']."</facility_state>\n";
              $xmlData.="<facility_district>".$vl['district']."</facility_district>\n";
              $xmlData.="<facility_hub_name>".$vl['hub_name']."</facility_hub_name>\n";
              $xmlData.="<facility_other_id>".$vl['other_id']."</facility_other_id>\n";
              $xmlData.="<facility_latitude>".$vl['latitude']."</facility_latitude>\n";
              $xmlData.="<facility_longitude>".$vl['longitude']."</facility_longitude>\n";
              $xmlData.="<facility_email>".$vl['email']."</facility_email>\n";
            $xmlData.="</facility>";
            $xmlData.="<patient>";
              $xmlData.="<art_code>".$vl['art_code']."</art_code>\n";
              $xmlData.="<patient_name>".$vl['patient_name']."</patient_name>\n";
              $xmlData.="<surname>".$vl['surname']."</surname>\n";
              $xmlData.="<art_no>".$vl['art_no']."</art_no>\n";
              $xmlData.="<patient_dob>".$vl['patient_dob']."</patient_dob>\n";
              $xmlData.="<gender>".$vl['gender']."</gender>\n";
              $xmlData.="<patient_phone_number>".$vl['patient_phone_number']."</patient_phone_number>\n";
              $xmlData.="<location>".$vl['location']."</location>\n";
              $xmlData.="<patient_art_date>".$vl['patient_art_date']."</patient_art_date>\n";
              $xmlData.="<vl_test_reason>".$vl['vl_test_reason']."</vl_test_reason>\n";
              $xmlData.="<is_patient_new>".$vl['is_patient_new']."</is_patient_new>\n";
              $xmlData.="<treatment_initiation>".$vl['treatment_initiation']."</treatment_initiation>\n";
              $xmlData.="<current_regimen>".$vl['current_regimen']."</current_regimen>\n";
              $xmlData.="<date_of_initiation_of_current_regimen>".$vl['date_of_initiation_of_current_regimen']."</date_of_initiation_of_current_regimen>\n";
              $xmlData.="<is_patient_pregnant>".$vl['is_patient_pregnant']."</is_patient_pregnant>\n";
              $xmlData.="<is_patient_breastfeeding>".$vl['is_patient_breastfeeding']."</is_patient_breastfeeding>\n";
              $xmlData.="<trimestre>".$vl['trimestre']."</trimestre>\n";
              $xmlData.="<arv_adherence>".$vl['arv_adherence']."</arv_adherence>\n";
              $xmlData.="<poor_adherence>".$vl['poor_adherence']."</poor_adherence>\n";
              $xmlData.="<patient_receive_sms>".$vl['patient_receive_sms']."</patient_receive_sms>\n";
              $xmlData.="<viral_load_indication>".$vl['viral_load_indication']."</viral_load_indication>\n";
              $xmlData.="<enhance_session>".$vl['enhance_session']."</enhance_session>\n";
              $xmlData.="<routine_monitoring_last_vl_date>".$vl['routine_monitoring_last_vl_date']."</routine_monitoring_last_vl_date>\n";
              $xmlData.="<routine_monitoring_value>".$vl['routine_monitoring_value']."</routine_monitoring_value>\n";
              $xmlData.="<routine_monitoring_sample_type>".$vl['routine_monitoring_sample_type']."</routine_monitoring_sample_type>\n";
              $xmlData.="<vl_treatment_failure_adherence_counseling_last_vl_date>".$vl['vl_treatment_failure_adherence_counseling_last_vl_date']."</vl_treatment_failure_adherence_counseling_last_vl_date>\n";
              $xmlData.="<vl_treatment_failure_adherence_counseling_value>".$vl['vl_treatment_failure_adherence_counseling_value']."</vl_treatment_failure_adherence_counseling_value>\n";
              $xmlData.="<vl_treatment_failure_adherence_counseling_sample_type>".$vl['vl_treatment_failure_adherence_counseling_sample_type']."</vl_treatment_failure_adherence_counseling_sample_type>\n";
              $xmlData.="<suspected_treatment_failure_last_vl_date>".$vl['suspected_treatment_failure_last_vl_date']."</suspected_treatment_failure_last_vl_date>\n";
              $xmlData.="<suspected_treatment_failure_value>".$vl['suspected_treatment_failure_value']."</suspected_treatment_failure_value>\n";
              $xmlData.="<suspected_treatment_failure_sample_type>".$vl['suspected_treatment_failure_sample_type']."</suspected_treatment_failure_sample_type>\n";
              $xmlData.="<switch_to_tdf_last_vl_date>".$vl['switch_to_tdf_last_vl_date']."</switch_to_tdf_last_vl_date>\n";
              $xmlData.="<switch_to_tdf_value>".$vl['switch_to_tdf_value']."</switch_to_tdf_value>\n";
              $xmlData.="<switch_to_tdf_sample_type>".$vl['switch_to_tdf_sample_type']."</switch_to_tdf_sample_type>\n";
              $xmlData.="<missing_last_vl_date>".$vl['missing_last_vl_date']."</missing_last_vl_date>\n";
              $xmlData.="<missing_value>".$vl['missing_value']."</missing_value>\n";
              $xmlData.="<missing_sample_type>".$vl['missing_sample_type']."</missing_sample_type>\n";
              $xmlData.="<request_clinician>".$vl['request_clinician']."</request_clinician>\n";
              $xmlData.="<clinician_ph_no>".$vl['clinician_ph_no']."</clinician_ph_no>\n";
              $xmlData.="<vl_focal_person>".$vl['vl_focal_person']."</vl_focal_person>\n";
              $xmlData.="<focal_person_phone_number>".$vl['focal_person_phone_number']."</focal_person_phone_number>\n";
              $xmlData.="<email_for_HF>".$vl['email_for_HF']."</email_for_HF>\n";
              $xmlData.="<date_sample_received_at_testing_lab>".$vl['date_sample_received_at_testing_lab']."</date_sample_received_at_testing_lab>\n";
              $xmlData.="<date_results_dispatched>".$vl['date_results_dispatched']."</date_results_dispatched>\n";
              $xmlData.="<rejection>".$vl['rejection']."</rejection>\n";
              $xmlData.="<sample_rejection_facility>".$vl['sample_rejection_facility']."</sample_rejection_facility>\n";
              $xmlData.="<sample_rejection_reason>".$vl['sample_rejection_reason']."</sample_rejection_reason>\n";
              $xmlData.="<age_in_yrs>".$vl['age_in_yrs']."</age_in_yrs>\n";
              $xmlData.="<age_in_mnts>".$vl['age_in_mnts']."</age_in_mnts>\n";
              $xmlData.="<treatment_initiated_date>".$vl['treatment_initiated_date']."</treatment_initiated_date>\n";
              $xmlData.="<arc_no>".$vl['arc_no']."</arc_no>\n";
              $xmlData.="<treatment_details>".$vl['treatment_details']."</treatment_details>\n";
              $xmlData.="<drug_substitution>".$vl['drug_substitution']."</drug_substitution>\n";
              $xmlData.="<collected_by>".$vl['collected_by']."</collected_by>\n";
              $xmlData.="<support_partner>".$vl['support_partner']."</support_partner>\n";
              $xmlData.="<has_patient_changed_regimen>".$vl['has_patient_changed_regimen']."</has_patient_changed_regimen>\n";
              $xmlData.="<reason_for_regimen_change>".$vl['reason_for_regimen_change']."</reason_for_regimen_change>\n";
              $xmlData.="<date_of_regimen_changed>".$vl['date_of_regimen_changed']."</date_of_regimen_changed>\n";
            $xmlData.="</patient>";
            $xmlData.="<sample>";
              $xmlData.="<sample_collection_date>".$vl['sample_collection_date']."</sample_collection_date>\n";
              $xmlData.="<date_of_demand>".$vl['date_of_demand']."</date_of_demand>\n";
              $xmlData.="<sample_type>".$vl['sample_name']."</sample_type>\n";
              $xmlData.="<plasma_conservation_temperature>".$vl['plasma_conservation_temperature']."</plasma_conservation_temperature>\n";
              $xmlData.="<duration_of_conservation>".$vl['duration_of_conservation']."</duration_of_conservation>\n";
              $xmlData.="<viral_load_no>".$vl['viral_load_no']."</viral_load_no>\n";
              $xmlData.="<vl_test_platform>".$vl['vl_test_platform']."</vl_test_platform>\n";
              $xmlData.="<testing_status>".$vl['status_name']."</testing_status>\n";
            $xmlData.="</sample>";
            $xmlData.="<lab>";
            if(isset($vl['lab_id']) && trim($vl['lab_id'])!= ""){
              $fQuery="SELECT * FROM facility_details WHERE facility_type ='2' AND facility_id='".$vl['lab_id']."'";
              $fResult = $db->query($fQuery);
              $xmlData.="<lab_name>".$fResult[0]['facility_name']."</lab_name>\n";
              $xmlData.="<lab_no>".$fResult[0]['facility_code']."</lab_no>\n";
              $xmlData.="<lab_contact_person>".$fResult[0]['contact_person']."</lab_contact_person>\n";
              $xmlData.="<lab_phone_no>".$fResult[0]['phone_number']."</lab_phone_no>\n";
              $xmlData.="<lab_country>".$fResult[0]['country']."</lab_country>\n";
              $xmlData.="<lab_state>".$fResult[0]['state']."</lab_state>\n";
              $xmlData.="<lab_district>".$fResult[0]['district']."</lab_district>\n";
            }else{
              $xmlData.="<lab_name>".$vl['lab_name']."</lab_name>\n";
              $xmlData.="<lab_no>".$vl['lab_no']."</lab_no>\n";
              $xmlData.="<lab_contact_person>".$vl['lab_contact_person']."</lab_contact_person>\n";
              $xmlData.="<lab_phone_no>".$vl['lab_phone_no']."</lab_phone_no>\n";
            }
            $xmlData.="</lab>";
            $xmlData.="<result>";
              $xmlData.="<log_value>".$vl['log_value']."</log_value>\n";
              $xmlData.="<absolute_value>".$vl['absolute_value']."</absolute_value>\n";
              $xmlData.="<absolute_decimal_value>".$vl['absolute_decimal_value']."</absolute_decimal_value>\n";
              $xmlData.="<text_value>".$vl['text_value']."</text_value>\n";
              $xmlData.="<result>".$vl['result']."</result>\n";
              $xmlData.="<comments>".$vl['comments']."</comments>\n";
              $xmlData.="<justification>".$vl['justification']."</justification>\n";
              $xmlData.="<result_approved_by>".$approvedBy."</result_approved_by>\n";
              $xmlData.="<result_approved_by_role>".$approvedByRole."</result_approved_by_role>\n";
              $xmlData.="<result_approved_on>".$vl['result_approved_on']."</result_approved_on>\n";
              $xmlData.="<result_reviewed_by>".$reviewedBy."</result_reviewed_by>\n";
              $xmlData.="<result_reviewed_by_role>".$reviewedByRole."</result_reviewed_by_role>\n";
              $xmlData.="<result_reviewed_date>".$vl['result_reviewed_date']."</result_reviewed_date>\n";
              $xmlData.="<test_methods>".$vl['test_methods']."</test_methods>\n";
              $xmlData.="<contact_complete_status>".$vl['contact_complete_status']."</contact_complete_status>\n";
              $xmlData.="<last_viral_load_date>".$vl['last_viral_load_date']."</last_viral_load_date>\n";
              $xmlData.="<last_viral_load_result>".$vl['last_viral_load_result']."</last_viral_load_result>\n";
              $xmlData.="<viral_load_log>".$vl['viral_load_log']."</viral_load_log>\n";
            $xmlData.="</result>";
            $xmlData.="<action>".$param."</action>";
            $xmlData.="<instance>".$vl['vl_instance_id']."</instance>";
            $xmlData.="<general>";
              $xmlData.="<form_id>".$country."</form_id>\n";
              $xmlData.="<serial_no>".$vl['serial_no']."</serial_no>\n";
              $xmlData.="<urgency>".$vl['urgency']."</urgency>\n";
              $xmlData.="<sample_code>".$vl['sample_code']."</sample_code>\n";
              $xmlData.="<sample_code_key>".$vl['sample_code_key']."</sample_code_key>\n";
              $xmlData.="<sample_code_format>".$vl['sample_code_format']."</sample_code_format>\n";
              $xmlData.="<machine_id>".$machine."</machine_id>";
              $xmlData.="<batch_code>".$vl['batch_code']."</batch_code>\n";
              $xmlData.="<batch_code_key>".$vl['batch_code_key']."</batch_code_key>\n";
              $xmlData.="<batch_status>".$vl['batch_status']."</batch_status>\n";
              $xmlData.="<date_dispatched_from_clinic_to_lab>".$vl['date_dispatched_from_clinic_to_lab']."</date_dispatched_from_clinic_to_lab>\n";
              $xmlData.="<date_of_completion_of_viral_load>".$vl['date_of_completion_of_viral_load']."</date_of_completion_of_viral_load>\n";
              $xmlData.="<lab_tested_date>".$vl['lab_tested_date']."</lab_tested_date>\n";
              $xmlData.="<date_result_printed>".$vl['date_result_printed']."</date_result_printed>\n";
              $xmlData.="<request_mail_sent>".$vl['request_mail_sent']."</request_mail_sent>\n";
              $xmlData.="<result_mail_sent>".$vl['result_mail_sent']."</result_mail_sent>\n";
              $xmlData.="<test_request_export>".$vl['test_request_export']."</test_request_export>\n";
              $xmlData.="<test_request_import>".$vl['test_request_import']."</test_request_import>\n";
              $xmlData.="<test_result_export>".$vl['test_result_export']."</test_result_export>\n";
              $xmlData.="<test_result_import>".$vl['test_result_import']."</test_result_import>\n";
              $xmlData.="<result_coming_from>".$vl['result_coming_from']."</result_coming_from>\n";
              $xmlData.="<created_by>".$vl['createdBy']."</created_by>\n";
              $xmlData.="<created_by_role>".$vl['createdByRole']."</created_by_role>\n";
              $xmlData.="<date_generated>".$vl['created_on']."</date_generated>";
              $xmlData.="<modified_by>".$modifiedBy."</modified_by>\n";
              $xmlData.="<modified_by_role>".$modifiedByRole."</modified_by_role>\n";
              $xmlData.="<date_modified>".$vl['modified_on']."</date_modified>";
            $xmlData.="</general>";
          $xmlData .="</vlsm>";
          //xml data encryption
          $key = "14365914278904829744952407287067";
          $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
          $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
          $crypttext = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $xmlData, MCRYPT_MODE_ECB, $iv);
          //xml file creation end
          $fileName = $vl['sample_code'].'.xml';
          if(isset($param) && $param == 'request'){
            $fp = fopen($configResult[0]['value'] . DIRECTORY_SEPARATOR . "request" . DIRECTORY_SEPARATOR . "new". DIRECTORY_SEPARATOR. $fileName, 'w+');
            fwrite($fp, $crypttext);
            fclose($fp);
            //update test request export flag
            $db=$db->where('sample_code',$vl['sample_code']);
            $db->update($tableName,array('test_request_export'=>1));
          }else if(isset($param) && $param == 'result'){
            $fp = fopen($configResult[0]['value'] . DIRECTORY_SEPARATOR . "result" . DIRECTORY_SEPARATOR . "new". DIRECTORY_SEPARATOR. $fileName, 'w+');
            fwrite($fp, $crypttext);
            fclose($fp);
            //update test request export flag
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