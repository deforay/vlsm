<?php
ob_start();
session_start();
include('../includes/MysqliDb.php');
include ('../includes/PHPExcel.php');
include('../General.php');
$general=new Deforay_Commons_General();

try {
    if(isset($_FILES['resultFile']['name']) && $_FILES['resultFile']['name'] != ''){
        $allowedExtensions = array('xml');
        $fileName = preg_replace('/[^A-Za-z0-9.]/', '-', $_FILES['resultFile']['name']);
        $fileName = str_replace(" ", "-", $fileName);
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        if(in_array($extension, $allowedExtensions)) {
          $xml=simplexml_load_file($_FILES['resultFile']['tmp_name']);
          $result = json_encode($xml);
          //Convert the JSON string back into an array.
          $result = json_decode($result, true);
          
          if(count($result['vl_request_form'])>0){
            foreach ($result['vl_request_form'] as $val){
                $data = array();
              
                if(isset($val['sample_code']) && count($val['sample_code'])>0){
                    $data['sample_code']=$val['sample_code'];
                }
              
                if(isset($val['vl_instance_id']) && count($val['vl_instance_id'])>0){
                  $data['vl_instance_id']=$val['vl_instance_id'];
                }
              
                if(isset($val['serial_no']) && count($val['serial_no'])>0){
                  $data['serial_no']=$val['serial_no'];
                }
              
                if(isset($val['facility_name']) && count($val['facility_name'])>0){
                  $clinicQuery = 'select facility_id from facility_details where facility_name = "'.$val['facility_name'].'"';
                  $clinicResult = $db->rawQuery($clinicQuery);
                  if(isset($clinicResult[0]['facility_id'])){
                    $data['facility_id'] = $clinicResult[0]['facility_id'];
                  }
                  else{
                    $facilityCode=NULL;
                    $facility_contact_person=NULL;
                    $facility_phone_number=NULL;
                    $facility_address=NULL;
                    $facility_country=NULL;
                    $facility_state=NULL;
                    $facility_district=NULL;
                    $facility_hub_name=NULL;
                    $facility_other_id=NULL;
                    $facility_longitude=NULL;
                    $facility_latitude=NULL;
                    $facility_email=NULL;
                    if(isset($val['facility_code']) && count($val['facility_code'])>0){
                        $facilityCode=$val['facility_code'];
                    }
                    if(isset($val['facility_contact_person']) && count($val['facility_contact_person'])>0){
                        $facility_contact_person=$val['facility_contact_person'];
                    }
                    if(isset($val['facility_phone_number']) && count($val['facility_phone_number'])>0){
                        $facility_phone_number=$val['facility_phone_number'];
                    }
                    if(isset($val['facility_address']) && count($val['facility_address'])>0){
                        $facility_address=$val['facility_address'];
                    }
                    if(isset($val['facility_country']) && count($val['facility_country'])>0){
                        $facility_country=$val['facility_country'];
                    }
                    if(isset($val['facility_state']) && count($val['facility_state'])>0){
                        $facility_state=$val['facility_state'];
                    }
                    if(isset($val['facility_district']) && count($val['facility_district'])>0){
                        $facility_district=$val['facility_district'];
                    }
                    if(isset($val['facility_hub_name']) && count($val['facility_hub_name'])>0){
                        $facility_hub_name=$val['facility_hub_name'];
                    }
                    if(isset($val['other_id']) && count($val['other_id'])>0){
                        $facility_other_id=$val['other_id'];
                    }
                    if(isset($val['facility_longitude']) && count($val['facility_longitude'])>0){
                        $facility_longitude=$val['facility_longitude'];
                    }
                    if(isset($val['facility_latitude']) && count($val['facility_latitude'])>0){
                        $facility_latitude=$val['facility_latitude'];
                    }
                    if(isset($val['facility_email']) && count($val['facility_email'])>0){
                        $facility_email=$val['facility_email'];
                    }
                    $clinicData = array(
                      'facility_name'=>$val['facility_name'],
                      'facility_code'=>$facilityCode,
                      'other_id'=>$facility_other_id,
                      'contact_person'=>$facility_contact_person,
                      'phone_number'=>$facility_phone_number,
                      'address'=>$facility_address,
                      'country'=>$facility_country,
                      'state'=>$facility_state,
                      'district'=>$facility_district,
                      'hub_name'=>$facility_hub_name,
                      'latitude'=>$facility_latitude,
                      'longitude'=>$facility_longitude,
                      'email'=>$facility_email,
                      'facility_type'=>1,
                      'status'=>'active'
                    );
                    $id = $db->insert('facility_details',$clinicData);
                    $data['facility_id'] = $id;
                  }
                }
              
                if(isset($val['sample_type']) && count($val['sample_type'])>0){
                    $data['sample_id'] = NULL;
                    $specimenTypeQuery = 'select sample_id from r_sample_type where sample_name = "'.$val['sample_type'].'"';
                    $specimenResult = $db->rawQuery($specimenTypeQuery);
                    if(isset($specimenResult[0]['sample_id'])){
                       $data['sample_id'] = $specimenResult[0]['sample_id'];
                    }else{
                       $sampleTypeData = array(
                                         'sample_name'=>$val['sample_type'],
                                         'status'=>'active'
                                      );
                       $id = $db->insert('r_sample_type',$sampleTypeData);
                       $data['sample_id'] = $id;
                    }
                }
              
                if(isset($val['testing_status']) && count($val['testing_status'])>0){
                    $data['status'] = NULL;
                    $statusQuery = 'select status_id from testing_status where status_name = "'.$val['testing_status'].'" OR status_name = "'.strtolower($val['testing_status']).'"';
                    $statusResult = $db->rawQuery($statusQuery);
                    if(isset($statusResult[0]['status_id'])){
                       $data['status'] = $statusResult[0]['status_id'];
                    }else{
                      $tStatusData = array(
                                         'status_name'=>$val['testing_status']
                                      );
                      $id = $db->insert('testing_status',$tStatusData);
                      $data['status'] = $id;
                    }
                }
              
                if(isset($val['nation_identifier']) && count($val['nation_identifier'])>0){
                  $data['nation_identifier'] = $val['nation_identifier'];
                }
                
                if(isset($val['batch_code']) && count($val['batch_code'])>0){
                    $data['lab_id'] = NULL;
                    $batch_code_key=NULL;
                    $batch_status=NULL;
                    
                    $batchQuery = 'select batch_id from batch_details where batch_code = "'.$val['batch_code'].'"';
                    $batchResult = $db->rawQuery($batchQuery);
                    if(isset($batchResult[0]['batch_id'])){
                     $data['batch_id'] = $batchResult[0]['batch_id'];
                    }else{
                        if(isset($val['batch_code_key']) && count($val['batch_code_key'])>0){
                            $batch_code_key = $val['batch_code_key'];
                        }
                        if(isset($val['batch_status']) && count($val['batch_status'])>0){
                          $batch_status = $val['batch_status'];
                        }
                        $batchData = array(
                                        'batch_code'=>$val['batch_code'],
                                        'batch_code_key'=>$batch_code_key,
                                        'batch_status'=>$batch_status
                                    );
                       $id = $db->insert('batch_details',$batchData);
                       $data['batch_id'] = $id;
                    }
                }
                
                
                if(isset($val['urgency']) && count($val['urgency'])>0){
                  $data['urgency'] = strtolower($val['urgency']);
                }
                if(isset($val['patient_name']) && count($val['patient_name'])>0){
                  $data['patient_name'] = $val['patient_name'];
                }
                if(isset($val['surname']) && count($val['surname'])>0){
                  $data['surname'] = $val['surname'];
                }
                if(isset($val['art_no']) && count($val['art_no'])>0){
                  $data['art_no'] = $val['art_no'];
                }
                if(isset($val['patient_dob']) && count($val['patient_dob'])>0){
                  $data['patient_dob'] = $val['patient_dob'];
                }
                if(isset($val['gender']) && count($val['gender'])>0){
                  $data['gender'] = $val['gender'];
                }
                if(isset($val['patient_phone_number']) && count($val['patient_phone_number'])>0){
                  $data['patient_phone_number'] = $val['patient_phone_number'];
                }
                if(isset($val['location']) && count($val['location'])>0){
                  $data['location'] = $val['location'];
                }
                if(isset($val['patient_art_date']) && count($val['patient_art_date'])>0){
                  $data['patient_art_date'] = $val['patient_art_date'];
                }
                if(isset($val['sample_collection_date']) && count($val['sample_collection_date'])>0){
                  $data['sample_collection_date'] = $val['sample_collection_date'];
                }
                if(isset($val['is_patient_new']) && count($val['is_patient_new'])>0){
                  $data['is_patient_new'] = $val['is_patient_new'];
                }
                if(isset($val['treatment_initiation']) && count($val['treatment_initiation'])>0){
                  $data['treatment_initiation'] = $val['treatment_initiation'];
                }
                if(isset($val['current_regimen']) && count($val['current_regimen'])>0){
                  $data['current_regimen'] = $val['current_regimen'];
                }
                if(isset($val['date_of_initiation_of_current_regimen']) && count($val['date_of_initiation_of_current_regimen'])>0){
                  $data['date_of_initiation_of_current_regimen'] = $val['date_of_initiation_of_current_regimen'];
                }
                if(isset($val['is_patient_pregnant']) && count($val['is_patient_pregnant'])>0){
                  $data['is_patient_pregnant'] = $val['is_patient_pregnant'];
                }
                if(isset($val['is_patient_breastfeeding']) && count($val['is_patient_breastfeeding'])>0){
                  $data['is_patient_breastfeeding'] = $val['is_patient_breastfeeding'];
                }
                if(isset($val['trimestre']) && count($val['trimestre'])>0){
                  $data['trimestre'] = $val['trimestre'];
                }
                if(isset($val['arv_adherence']) && count($val['arv_adherence'])>0){
                  $data['arv_adherence'] = $val['arv_adherence'];
                }
                if(isset($val['patient_receive_sms']) && count($val['patient_receive_sms'])>0){
                  $data['patient_receive_sms'] = $val['patient_receive_sms'];
                }
                if(isset($val['viral_load_indication']) && count($val['viral_load_indication'])>0){
                  $data['viral_load_indication'] = $val['viral_load_indication'];
                }
                if(isset($val['enhance_session']) && count($val['enhance_session'])>0){
                  $data['enhance_session'] = $val['enhance_session'];
                }
                if(isset($val['routine_monitoring_last_vl_date']) && count($val['routine_monitoring_last_vl_date'])>0){
                  $data['routine_monitoring_last_vl_date'] = $val['routine_monitoring_last_vl_date'];
                }
                if(isset($val['routine_monitoring_sample_type']) && count($val['routine_monitoring_sample_type'])>0){
                  $data['routine_monitoring_sample_type'] = $val['routine_monitoring_sample_type'];
                }
                if(isset($val['vl_treatment_failure_adherence_counseling_last_vl_date']) && count($val['vl_treatment_failure_adherence_counseling_last_vl_date'])>0){
                  $data['vl_treatment_failure_adherence_counseling_last_vl_date'] = $val['vl_treatment_failure_adherence_counseling_last_vl_date'];
                }
                if(isset($val['vl_treatment_failure_adherence_counseling_value']) && count($val['vl_treatment_failure_adherence_counseling_value'])>0){
                  $data['vl_treatment_failure_adherence_counseling_value'] = $val['vl_treatment_failure_adherence_counseling_value'];
                }
                if(isset($val['vl_treatment_failure_adherence_counseling_sample_type']) && count($val['vl_treatment_failure_adherence_counseling_sample_type'])>0){
                  $data['vl_treatment_failure_adherence_counseling_sample_type'] = $val['vl_treatment_failure_adherence_counseling_sample_type'];
                }
                if(isset($val['suspected_treatment_failure_last_vl_date']) && count($val['suspected_treatment_failure_last_vl_date'])>0){
                  $data['suspected_treatment_failure_last_vl_date'] = $val['suspected_treatment_failure_last_vl_date'];
                }
                if(isset($val['suspected_treatment_failure_value']) && count($val['suspected_treatment_failure_value'])>0){
                  $data['suspected_treatment_failure_value'] = $val['suspected_treatment_failure_value'];
                }
                if(isset($val['suspected_treatment_failure_sample_type']) && count($val['suspected_treatment_failure_sample_type'])>0){
                  $data['suspected_treatment_failure_sample_type'] = $val['suspected_treatment_failure_sample_type'];
                }
                if(isset($val['switch_to_tdf_last_vl_date']) && count($val['switch_to_tdf_last_vl_date'])>0){
                  $data['switch_to_tdf_last_vl_date'] = $val['switch_to_tdf_last_vl_date'];
                }
                if(isset($val['switch_to_tdf_value']) && count($val['switch_to_tdf_value'])>0){
                  $data['switch_to_tdf_value'] = $val['switch_to_tdf_value'];
                }
                if(isset($val['switch_to_tdf_sample_type']) && count($val['switch_to_tdf_sample_type'])>0){
                  $data['switch_to_tdf_sample_type'] = $val['switch_to_tdf_sample_type'];
                }
                if(isset($val['missing_last_vl_date']) && count($val['missing_last_vl_date'])>0){
                  $data['missing_last_vl_date'] = $val['missing_last_vl_date'];
                }
                if(isset($val['missing_value']) && count($val['missing_value'])>0){
                  $data['missing_value'] = $val['missing_value'];
                }
                if(isset($val['missing_sample_type']) && count($val['missing_sample_type'])>0){
                  $data['missing_sample_type'] = $val['missing_sample_type'];
                }
                if(isset($val['request_clinician']) && count($val['request_clinician'])>0){
                  $data['request_clinician'] = $val['request_clinician'];
                }
                if(isset($val['clinician_ph_no']) && count($val['clinician_ph_no'])>0){
                  $data['clinician_ph_no'] = $val['clinician_ph_no'];
                }
                if(isset($val['sample_testing_date']) && count($val['sample_testing_date'])>0){
                  $data['sample_testing_date'] = $val['sample_testing_date'];
                }
                if(isset($val['vl_focal_person']) && count($val['vl_focal_person'])>0){
                  $data['vl_focal_person'] = $val['vl_focal_person'];
                }
                if(isset($val['focal_person_phone_number']) && count($val['focal_person_phone_number'])>0){
                  $data['focal_person_phone_number'] = $val['focal_person_phone_number'];
                }
                if(isset($val['email_for_HF']) && count($val['email_for_HF'])>0){
                  $data['email_for_HF'] = $val['email_for_HF'];
                }
                if(isset($val['date_sample_received_at_testing_lab']) && count($val['date_sample_received_at_testing_lab'])>0){
                  $data['date_sample_received_at_testing_lab'] = $val['date_sample_received_at_testing_lab'];
                }
                if(isset($val['date_results_dispatched']) && count($val['date_results_dispatched'])>0){
                  $data['date_results_dispatched'] = $val['date_results_dispatched'];
                }
                if(isset($val['rejection']) && count($val['rejection'])>0){
                  $data['rejection'] = $val['rejection'];
                }
                if(isset($val['sample_rejection_facility']) && count($val['sample_rejection_facility'])>0){
                  $data['sample_rejection_facility'] = $val['sample_rejection_facility'];
                }
              
                if(isset($val['sample_rejection_reason']) && count($val['sample_rejection_reason'])>0){
                    $rrQuery = 'select rejection_reason_id from r_sample_rejection_reasons where rejection_reason_name = "'.$val['sample_rejection_reason'].'" or rejection_reason_name = "'.strtolower($val['sample_rejection_reason']).'"';
                    $rrResult = $db->rawQuery($rrQuery);
                    if(isset($rrResult[0]['rejection_reason_id'])){
                       $data['sample_rejection_reason'] = $rrResult[0]['rejection_reason_id'];
                    }else{
                        $rrData = array(
                                        'rejection_reason_name'=>$val['sample_rejection_reason'],
                                        'rejection_reason_status'=>'active'
                                );
                        $id = $db->insert('r_sample_rejection_reasons',$rrData);
                        $data['sample_rejection_reason'] = $id;
                    }
                }
              
                if(isset($val['other_id']) && count($val['other_id'])>0){
                  $data['other_id'] = $val['other_id'];
                }
                if(isset($val['age_in_yrs']) && count($val['age_in_yrs'])>0){
                  $data['age_in_yrs'] = $val['age_in_yrs'];
                }
                if(isset($val['age_in_mnts']) && count($val['age_in_mnts'])>0){
                  $data['age_in_mnts'] = $val['age_in_mnts'];
                }
                if(isset($val['treatment_initiated_date']) && count($val['treatment_initiated_date'])>0){
                  $data['treatment_initiated_date'] = $val['treatment_initiated_date'];
                }
                if(isset($val['arc_no']) && count($val['arc_no'])>0){
                  $data['arc_no'] = $val['arc_no'];
                }
                if(isset($val['treatment_details']) && count($val['treatment_details'])>0){
                  $data['treatment_details'] = $val['treatment_details'];
                }
              
                if(isset($val['lab_name']) && count($val['lab_name'])>0){
                  $data['lab_id'] = NULL;
                  $labQuery = 'select facility_id from facility_details where facility_name = "'.$val['lab_name'].'"';
                  $labResult = $db->rawQuery($labQuery);
                  if(isset($labResult[0]['facility_id'])){
                     $data['lab_id'] = $labResult[0]['facility_id'];
                  }else{
                     $labData = array(
                                       'facility_name'=>$val['lab_name'],
                                       'facility_type'=>2,
                                       'status'=>'active'
                                   );
                     $id = $db->insert('facility_details',$labData);
                     $data['lab_id'] = $id;
                  }
                }
              
                if(isset($val['lab_no']) && count($val['lab_no'])>0){
                  $data['lab_no'] = $val['lab_no'];
                }
                if(isset($val['lab_contact_person']) && count($val['lab_contact_person'])>0){
                  $data['lab_contact_person'] = $val['lab_contact_person'];
                }
                if(isset($val['lab_phone_no']) && count($val['lab_phone_no'])>0){
                  $data['lab_phone_no'] = $val['lab_phone_no'];
                }
                if(isset($val['lab_tested_date']) && count($val['lab_tested_date'])>0){
                  $data['lab_tested_date'] = $val['lab_tested_date'];
                }
                if(isset($val['justification']) && count($val['justification'])>0){
                  $data['justification'] = $val['justification'];
                }
                if(isset($val['log_value']) && count($val['log_value'])>0){
                  $data['log_value'] = $val['log_value'];
                }
                if(isset($val['absolute_value']) && count($val['absolute_value'])>0){
                  $data['absolute_value'] = $val['absolute_value'];
                }
                if(isset($val['text_value']) && count($val['text_value'])>0){
                  $data['text_value'] = $val['text_value'];
                }
                if(isset($val['result']) && count($val['result'])>0){
                  $data['result'] = $val['result'];
                }
                if(isset($val['comments']) && count($val['comments'])>0){
                  $data['comments'] = $val['comments'];
                }
                if(isset($val['result_reviewed_date']) && count($val['result_reviewed_date'])>0){
                  $data['result_reviewed_date'] = $val['result_reviewed_date'];
                }
                if(isset($val['test_methods']) && count($val['test_methods'])>0){
                  $data['test_methods'] = $val['test_methods'];
                }
                if(isset($val['contact_complete_status']) && count($val['contact_complete_status'])>0){
                  $data['contact_complete_status'] = $val['contact_complete_status'];
                }
                if(isset($val['last_viral_load_date']) && count($val['last_viral_load_date'])>0){
                  $data['last_viral_load_date'] = $val['last_viral_load_date'];
                }
                if(isset($val['last_viral_load_result']) && count($val['last_viral_load_result'])>0){
                  $data['last_viral_load_result'] = $val['last_viral_load_result'];
                }
                if(isset($val['viral_load_log']) && count($val['viral_load_log'])>0){
                  $data['viral_load_log'] = $val['viral_load_log'];
                }
                if(isset($val['vl_test_reason']) && count($val['vl_test_reason'])>0){
                  $data['vl_test_reason'] = $val['vl_test_reason'];
                }
                if(isset($val['drug_substitution']) && count($val['drug_substitution'])>0){
                  $data['drug_substitution'] = $val['drug_substitution'];
                }
                if(isset($val['vl_test_platform']) && count($val['vl_test_platform'])>0){
                  $data['vl_test_platform'] = $val['vl_test_platform'];
                }
                if(isset($val['support_partner']) && count($val['support_partner'])>0){
                  $data['support_partner'] = $val['support_partner'];
                }
                if(isset($val['has_patient_changed_regimen']) && count($val['has_patient_changed_regimen'])>0){
                  $data['has_patient_changed_regimen'] = $val['has_patient_changed_regimen'];
                }
                if(isset($val['reason_for_regimen_change']) && count($val['reason_for_regimen_change'])>0){
                  $data['reason_for_regimen_change'] = $val['reason_for_regimen_change'];
                }
                if(isset($val['date_of_regimen_changed']) && count($val['date_of_regimen_changed'])>0){
                  $data['date_of_regimen_changed'] = $val['date_of_regimen_changed'];
                }
                if(isset($val['plasma_conservation_temperature']) && count($val['plasma_conservation_temperature'])>0){
                  $data['plasma_conservation_temperature'] = $val['plasma_conservation_temperature'];
                }
                if(isset($val['duration_of_conservation']) && count($val['duration_of_conservation'])>0){
                  $data['duration_of_conservation'] = $val['duration_of_conservation'];
                }
                if(isset($val['date_of_demand']) && count($val['date_of_demand'])>0){
                  $data['date_of_demand'] = $val['date_of_demand'];
                }
                if(isset($val['viral_load_no']) && count($val['viral_load_no'])>0){
                  $data['viral_load_no'] = $val['viral_load_no'];
                }
                if(isset($val['date_dispatched_from_clinic_to_lab']) && count($val['date_dispatched_from_clinic_to_lab'])>0){
                  $data['date_dispatched_from_clinic_to_lab'] = $val['date_dispatched_from_clinic_to_lab'];
                }
                if(isset($val['date_of_completion_of_viral_load']) && count($val['date_of_completion_of_viral_load'])>0){
                  $data['date_of_completion_of_viral_load'] = $val['date_of_completion_of_viral_load'];
                }
                if(isset($val['date_result_printed']) && count($val['date_result_printed'])>0){
                  $data['date_result_printed'] = $val['date_result_printed'];
                }
                if(isset($val['result_coming_from']) && count($val['result_coming_from'])>0){
                  $data['result_coming_from'] = $val['result_coming_from'];
                }
                
                $sampleQuery = 'select vl_sample_id from vl_request_form where sample_code = "'.$data['sample_code'].'"';
                $sampleResult = $db->rawQuery($sampleQuery);
                if(isset($sampleResult[0]['vl_sample_id'])){
                    $db=$db->where('vl_sample_id',$sampleResult[0]['vl_sample_id']);
                    $db->update('vl_request_form',$data);
                }else{
                    $data['created_by'] = $_SESSION['userId'];
                    $data['created_on'] = $general->getDateTime();
                    $db->insert('vl_request_form',$data);
                }
              }
            }
            
            $_SESSION['alertMsg']="Test Request Imported successfully";
            //header("location:vlRequest.php");
            header("location:../vl-print/vlTestResult.php");
        }else{
            $_SESSION['alertMsg']="Invalid file format..";
            header("location:addImportXmlTestRequest.php");
        }
    }else{
         $_SESSION['alertMsg']="Unable to import..Please check all the fields";
         header("location:addImportXmlTestRequest.php");
    }
   
}catch(Exception $exc){
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}