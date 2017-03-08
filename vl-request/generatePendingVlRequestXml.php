<?php
session_start();
ob_start();
include('../includes/MysqliDb.php');
include ('../includes/PHPExcel.php');
include('../General.php');
$general=new Deforay_Commons_General();
//get other config details
$geQuery="SELECT * FROM other_config WHERE type = 'request'";
$geResult = $db->rawQuery($geQuery);
$mailconf = array();
foreach($geResult as $row){
   $mailconf[$row['name']] = $row['value'];
}
$xmlData = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
$xmlData.="<vl_request>\n";
$filedGroup = array();
if(isset($mailconf['rq_field']) && trim($mailconf['rq_field'])!= ''){
   $filedGroup = explode(",",$mailconf['rq_field']);
   $headings = $filedGroup;
   //Set query and values
   //$xmlData .="";
   //echo $_SESSION['vlRequestSearchResultQuery'];
   //die;
   $sampleResult = $db->rawQuery($_SESSION['vlRequestSearchResultQuery']);
      $output = array();
      foreach($sampleResult as $sample){
         $xmlData.="<vl_request_form>\n";
         $row = array();
         $xmlData.="<sample_code>".$sample['sample_code']."</sample_code>\n";
         $xmlData.="<vl_instance_id>".$sample['vl_instance_id']."</vl_instance_id>\n";
         $xmlData.="<serial_no>".$sample['serial_no']."</serial_no>\n";
         
         $xmlData.="<facility_name>".$sample['facility_name']."</facility_name>\n";
         $xmlData.="<facility_code>".$sample['facility_code']."</facility_code>\n";
         $xmlData.="<facility_contact_person>".$sample['contact_person']."</facility_contact_person>\n";
         $xmlData.="<facility_phone_number>".$sample['phone_number']."</facility_phone_number>\n";
         $xmlData.="<facility_address>".$sample['address']."</facility_address>\n";
         $xmlData.="<facility_country>".$sample['country']."</facility_country>\n";
         $xmlData.="<facility_state>".$sample['state']."</facility_state>\n";
         $xmlData.="<facility_district>".$sample['district']."</facility_district>\n";
         $xmlData.="<facility_hub_name>".$sample['hub_name']."</facility_hub_name>\n";
         $xmlData.="<facility_other_id>".$sample['other_id']."</facility_other_id>\n";
         $xmlData.="<facility_latitude>".$sample['latitude']."</facility_latitude>\n";
         $xmlData.="<facility_longitude>".$sample['longitude']."</facility_longitude>\n";
         $xmlData.="<facility_email>".$sample['email']."</facility_email>\n";
         
         $xmlData.="<sample_type>".$sample['sample_name']."</sample_type>\n";
         $xmlData.="<testing_status>".$sample['status_name']."</testing_status>\n";
         $xmlData.="<art_code>".$sample['art_code']."</art_code>\n";
         $xmlData.="<nation_identifier>".$sample['nation_identifier']."</nation_identifier>\n";
         
         $xmlData.="<batch_code>".$sample['batch_code']."</batch_code>\n";
         $xmlData.="<batch_code_key>".$sample['batch_code_key']."</batch_code_key>\n";
         $xmlData.="<batch_status>".$sample['batch_status']."</batch_status>\n";
         
         $xmlData.="<urgency>".$sample['urgency']."</urgency>\n";
         $xmlData.="<patient_name>".$sample['patient_name']."</patient_name>\n";
         $xmlData.="<surname>".$sample['surname']."</surname>\n";
         $xmlData.="<art_no>".$sample['art_no']."</art_no>\n";
         $xmlData.="<patient_dob>".$sample['patient_dob']."</patient_dob>\n";
         $xmlData.="<gender>".$sample['gender']."</gender>\n";
         $xmlData.="<patient_phone_number>".$sample['patient_phone_number']."</patient_phone_number>\n";
         $xmlData.="<location>".$sample['location']."</location>\n";
         $xmlData.="<patient_art_date>".$sample['patient_art_date']."</patient_art_date>\n";
         $xmlData.="<sample_collection_date>".$sample['sample_collection_date']."</sample_collection_date>\n";
         $xmlData.="<is_patient_new>".$sample['is_patient_new']."</is_patient_new>\n";
         $xmlData.="<treatment_initiation>".$sample['treatment_initiation']."</treatment_initiation>\n";
         $xmlData.="<current_regimen>".$sample['current_regimen']."</current_regimen>\n";
         $xmlData.="<date_of_initiation_of_current_regimen>".$sample['date_of_initiation_of_current_regimen']."</date_of_initiation_of_current_regimen>\n";
         $xmlData.="<is_patient_pregnant>".$sample['is_patient_pregnant']."</is_patient_pregnant>\n";
         $xmlData.="<is_patient_breastfeeding>".$sample['is_patient_breastfeeding']."</is_patient_breastfeeding>\n";
         $xmlData.="<trimestre>".$sample['trimestre']."</trimestre>\n";
         $xmlData.="<arv_adherence>".$sample['arv_adherence']."</arv_adherence>\n";
         $xmlData.="<poor_adherence>".$sample['poor_adherence']."</poor_adherence>\n";
         $xmlData.="<patient_receive_sms>".$sample['patient_receive_sms']."</patient_receive_sms>\n";
         $xmlData.="<viral_load_indication>".$sample['viral_load_indication']."</viral_load_indication>\n";
         $xmlData.="<enhance_session>".$sample['enhance_session']."</enhance_session>\n";
         $xmlData.="<routine_monitoring_last_vl_date>".$sample['routine_monitoring_last_vl_date']."</routine_monitoring_last_vl_date>\n";
         $xmlData.="<routine_monitoring_value>".$sample['routine_monitoring_value']."</routine_monitoring_value>\n";
         $xmlData.="<routine_monitoring_sample_type>".$sample['routine_monitoring_sample_type']."</routine_monitoring_sample_type>\n";
         $xmlData.="<vl_treatment_failure_adherence_counseling_last_vl_date>".$sample['vl_treatment_failure_adherence_counseling_last_vl_date']."</vl_treatment_failure_adherence_counseling_last_vl_date>\n";
         $xmlData.="<vl_treatment_failure_adherence_counseling_value>".$sample['vl_treatment_failure_adherence_counseling_value']."</vl_treatment_failure_adherence_counseling_value>\n";
         $xmlData.="<vl_treatment_failure_adherence_counseling_sample_type>".$sample['vl_treatment_failure_adherence_counseling_sample_type']."</vl_treatment_failure_adherence_counseling_sample_type>\n";
         $xmlData.="<suspected_treatment_failure_last_vl_date>".$sample['suspected_treatment_failure_last_vl_date']."</suspected_treatment_failure_last_vl_date>\n";
         $xmlData.="<suspected_treatment_failure_value>".$sample['suspected_treatment_failure_value']."</suspected_treatment_failure_value>\n";
         $xmlData.="<suspected_treatment_failure_sample_type>".$sample['suspected_treatment_failure_sample_type']."</suspected_treatment_failure_sample_type>\n";
         $xmlData.="<switch_to_tdf_last_vl_date>".$sample['switch_to_tdf_last_vl_date']."</switch_to_tdf_last_vl_date>\n";
         $xmlData.="<switch_to_tdf_value>".$sample['switch_to_tdf_value']."</switch_to_tdf_value>\n";
         $xmlData.="<switch_to_tdf_sample_type>".$sample['switch_to_tdf_sample_type']."</switch_to_tdf_sample_type>\n";
         $xmlData.="<missing_last_vl_date>".$sample['missing_last_vl_date']."</missing_last_vl_date>\n";
         $xmlData.="<missing_value>".$sample['missing_value']."</missing_value>\n";
         $xmlData.="<missing_sample_type>".$sample['missing_sample_type']."</missing_sample_type>\n";
         $xmlData.="<request_clinician>".$sample['request_clinician']."</request_clinician>\n";
         $xmlData.="<clinician_ph_no>".$sample['clinician_ph_no']."</clinician_ph_no>\n";
         $xmlData.="<sample_testing_date>".$sample['sample_testing_date']."</sample_testing_date>\n";
         $xmlData.="<vl_focal_person>".$sample['vl_focal_person']."</vl_focal_person>\n";
         $xmlData.="<focal_person_phone_number>".$sample['focal_person_phone_number']."</focal_person_phone_number>\n";
         $xmlData.="<email_for_HF>".$sample['email_for_HF']."</email_for_HF>\n";
         $xmlData.="<date_sample_received_at_testing_lab>".$sample['date_sample_received_at_testing_lab']."</date_sample_received_at_testing_lab>\n";
         $xmlData.="<date_results_dispatched>".$sample['date_results_dispatched']."</date_results_dispatched>\n";
         $xmlData.="<rejection>".$sample['rejection']."</rejection>\n";
         $xmlData.="<sample_rejection_facility>".$sample['sample_rejection_facility']."</sample_rejection_facility>\n";
         $xmlData.="<sample_rejection_reason>".$sample['sample_rejection_reason']."</sample_rejection_reason>\n";
         $xmlData.="<other_id>".$sample['other_id']."</other_id>\n";
         $xmlData.="<age_in_yrs>".$sample['age_in_yrs']."</age_in_yrs>\n";
         $xmlData.="<age_in_mnts>".$sample['age_in_mnts']."</age_in_mnts>\n";
         $xmlData.="<treatment_initiated_date>".$sample['treatment_initiated_date']."</treatment_initiated_date>\n";
         $xmlData.="<arc_no>".$sample['arc_no']."</arc_no>\n";
         $xmlData.="<treatment_details>".$sample['treatment_details']."</treatment_details>\n";
         if(isset($sample['lab_id']) && trim($sample['lab_id'])!=""){
            $fQuery="SELECT * FROM facility_details WHERE facility_type ='2' AND facility_id='".$sample['lab_id']."'";
            $fResult = $db->query($fQuery);
            $xmlData.="<lab_name>".$fResult[0]['facility_name']."</lab_name>\n";
            $xmlData.="<lab_no>".$fResult[0]['facility_code']."</lab_no>\n";
            $xmlData.="<lab_contact_person>".$fResult[0]['contact_person']."</lab_contact_person>\n";
            $xmlData.="<lab_phone_no>".$fResult[0]['phone_number']."</lab_phone_no>\n";
            $xmlData.="<lab_country>".$fResult[0]['country']."</lab_country>\n";
            $xmlData.="<lab_state>".$fResult[0]['state']."</lab_state>\n";
            $xmlData.="<lab_district>".$fResult[0]['district']."</lab_district>\n";
         }else{
            $xmlData.="<lab_name>".$sample['lab_name']."</lab_name>\n";
            //$xmlData.="<lab_id>".$sample['lab_id']."</lab_id>\n";
            $xmlData.="<lab_no>".$sample['lab_no']."</lab_no>\n";
            $xmlData.="<lab_contact_person>".$sample['lab_contact_person']."</lab_contact_person>\n";
            $xmlData.="<lab_phone_no>".$sample['lab_phone_no']."</lab_phone_no>\n";
         }
         
         $xmlData.="<lab_tested_date>".$sample['lab_tested_date']."</lab_tested_date>\n";
         $xmlData.="<justification>".$sample['justification']."</justification>\n";
         $xmlData.="<log_value>".$sample['log_value']."</log_value>\n";
         $xmlData.="<absolute_value>".$sample['absolute_value']."</absolute_value>\n";
         $xmlData.="<text_value>".$sample['text_value']."</text_value>\n";
         $xmlData.="<result>".$sample['result']."</result>\n";
         $xmlData.="<comments>".$sample['comments']."</comments>\n";
         $xmlData.="<result_reviewed_date>".$sample['result_reviewed_date']."</result_reviewed_date>\n";
         $xmlData.="<test_methods>".$sample['test_methods']."</test_methods>\n";
         $xmlData.="<contact_complete_status>".$sample['contact_complete_status']."</contact_complete_status>\n";
         $xmlData.="<last_viral_load_date>".$sample['last_viral_load_date']."</last_viral_load_date>\n";
         $xmlData.="<last_viral_load_result>".$sample['last_viral_load_result']."</last_viral_load_result>\n";
         $xmlData.="<viral_load_log>".$sample['viral_load_log']."</viral_load_log>\n";
         $xmlData.="<vl_test_reason>".$sample['vl_test_reason']."</vl_test_reason>\n";
         $xmlData.="<drug_substitution>".$sample['drug_substitution']."</drug_substitution>\n";
         $xmlData.="<vl_test_platform>".$sample['vl_test_platform']."</vl_test_platform>\n";
         $xmlData.="<support_partner>".$sample['support_partner']."</support_partner>\n";
         $xmlData.="<has_patient_changed_regimen>".$sample['has_patient_changed_regimen']."</has_patient_changed_regimen>\n";
         $xmlData.="<reason_for_regimen_change>".$sample['reason_for_regimen_change']."</reason_for_regimen_change>\n";
         $xmlData.="<date_of_regimen_changed>".$sample['date_of_regimen_changed']."</date_of_regimen_changed>\n";
         $xmlData.="<plasma_conservation_temperature>".$sample['plasma_conservation_temperature']."</plasma_conservation_temperature>\n";
         $xmlData.="<duration_of_conservation>".$sample['duration_of_conservation']."</duration_of_conservation>\n";
         $xmlData.="<date_of_demand>".$sample['date_of_demand']."</date_of_demand>\n";
         $xmlData.="<viral_load_no>".$sample['viral_load_no']."</viral_load_no>\n";
         $xmlData.="<date_dispatched_from_clinic_to_lab>".$sample['date_dispatched_from_clinic_to_lab']."</date_dispatched_from_clinic_to_lab>\n";
         $xmlData.="<date_of_completion_of_viral_load>".$sample['date_of_completion_of_viral_load']."</date_of_completion_of_viral_load>\n";
         $xmlData.="<date_result_printed>".$sample['date_result_printed']."</date_result_printed>\n";
         $xmlData.="<result_coming_from>".$sample['result_coming_from']."</result_coming_from>\n";
         
         //$row[] = $sample['sample_code'];
         //$xmlData .="<".$field.">".$fieldValue."</".$field.">\n";
        //$output[] = $row;
        $xmlData.="</vl_request_form>\n";
      }
      $xmlData .="</vl_request>";
      
      
      $pathFront=realpath('../temporary');
      //echo $xmlData;
     // die;
      $xml = new SimpleXMLElement($xmlData);
      $fileName = 'vl-test-request-' . date('d-M-Y-H-i-s') . '.xml';
      $fp = fopen($pathFront. DIRECTORY_SEPARATOR.$fileName, 'w+');
      fwrite($fp, $xmlData);
      fclose($fp);
      //Header('Content-type: text/xml');
      //header('Content-Disposition: attachment; filename="'.$filename.'"');
      //echo $xml->asXML();
      
      //file_put_contents($pathFront. DIRECTORY_SEPARATOR.$filename, $xmlData);
      //echo $filename;
      
      
      //header('Content-Description: File Transfer');
      //header("Content-type: text/xml");
      //header('Content-Disposition: attachment; filename=' . basename($pathFront. DIRECTORY_SEPARATOR.$fileName));
      //header('Expires: 0');
      //header('Cache-Control: must-revalidate');
      //header('Pragma: public');
      //header('Content-Length: ' . filesize($pathFront. DIRECTORY_SEPARATOR .$fileName));
      //header('Content-Length: ' . filesize('backup/' . $date.'.zip'));
      //readfile($pathFront. DIRECTORY_SEPARATOR .$fileName);
      echo $fileName;
      
     
}else{
    echo $filename = '';
}