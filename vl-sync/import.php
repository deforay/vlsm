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
  //import request/result xml
  $formQuery ="SELECT value FROM global_config where name='vl_form'";
  $formResult = $db->rawQuery($formQuery);
  $country = $formResult[0]['value'];
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
      foreach($files as $file) {
        if (in_array($file, array(".",".."))) continue;
        if(isset($param) && $param == 'request'){
          $xml = new DOMDocument();
          $xml->load($configResult[0]['value'] . DIRECTORY_SEPARATOR . "request" . DIRECTORY_SEPARATOR . "new" . DIRECTORY_SEPARATOR . $file);
          if(!$xml->schemaValidate('validate.xsd')) {
            copy($configResult[0]['value'] . DIRECTORY_SEPARATOR . "request" . DIRECTORY_SEPARATOR . "new" . DIRECTORY_SEPARATOR . $file,$configResult[0]['value'] . DIRECTORY_SEPARATOR . "request" . DIRECTORY_SEPARATOR . "error" . DIRECTORY_SEPARATOR . $file);
            unlink($configResult[0]['value'] . DIRECTORY_SEPARATOR . "request" . DIRECTORY_SEPARATOR . "new" . DIRECTORY_SEPARATOR . $file);
            break;
          }
          $xmlFile = file_get_contents($configResult[0]['value'] . DIRECTORY_SEPARATOR . "request" . DIRECTORY_SEPARATOR . "new" . DIRECTORY_SEPARATOR . $file);
        }else if(isset($param) && $param == 'result'){
          $xml = new DOMDocument();
          $xml->load($configResult[0]['value'] . DIRECTORY_SEPARATOR . "result" . DIRECTORY_SEPARATOR . "new" . DIRECTORY_SEPARATOR . $file);
          if(!$xml->schemaValidate('validate.xsd')) {
            copy($configResult[0]['value'] . DIRECTORY_SEPARATOR . "result" . DIRECTORY_SEPARATOR . "new" . DIRECTORY_SEPARATOR . $file,$configResult[0]['value'] . DIRECTORY_SEPARATOR . "result" . DIRECTORY_SEPARATOR . "error" . DIRECTORY_SEPARATOR . $file);
            unlink($configResult[0]['value'] . DIRECTORY_SEPARATOR . "result" . DIRECTORY_SEPARATOR . "new" . DIRECTORY_SEPARATOR . $file);
            break;
          }
          $xmlFile = file_get_contents($configResult[0]['value'] . DIRECTORY_SEPARATOR . "result" . DIRECTORY_SEPARATOR . "new" . DIRECTORY_SEPARATOR . $file);  
        }
        $xml = new SimpleXMLElement($xmlFile);
        //facility section
        if(count($xml->facility) >0){
          if(isset($xml->facility->facility_name)){
            $clinicQuery = 'select facility_id from facility_details where facility_name = "'.(string)$xml->facility->facility_name.'"';
            $clinicResult = $db->rawQuery($clinicQuery);
            if(isset($clinicResult[0]['facility_id'])){
              $data['facility_id'] = $clinicResult[0]['facility_id'];
            }else{
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
              if(isset($xml->facility->facility_code)){
                $facilityCode = (string)$xml->facility->facility_code;
              }if(isset($xml->facility->facility_contact_person)){
                $facility_contact_person=(string)$xml->facility->facility_contact_person;
              }if(isset($xml->facility->facility_phone_number)){
                $facility_phone_number=(string)$xml->facility->facility_phone_number;
              }if(isset($xml->facility->facility_address)){
                $facility_address=(string)$xml->facility->facility_address;
              }if(isset($xml->facility->facility_country)){
                $facility_country=(string)$xml->facility->facility_country;
              }if(isset($xml->facility->facility_state)){
                $facility_state=(string)$xml->facility->facility_state;
              }if(isset($xml->facility->facility_district)){
                $facility_district=(string)$xml->facility->facility_district;
              }if(isset($xml->facility->facility_hub_name)){
                $facility_hub_name=(string)$xml->facility->facility_hub_name;
              }if(isset($xml->facility->other_id)){
                $facility_other_id=(string)$xml->facility->other_id;
              }if(isset($xml->facility->facility_longitude)){
                $facility_longitude=(string)$xml->facility->facility_longitude;
              }if(isset($xml->facility->facility_latitude)){
                $facility_latitude=(string)$xml->facility->facility_latitude;
              }if(isset($xml->facility->facility_email)){
                $facility_email=(string)$xml->facility->facility_email;
              }
              $clinicData = array(
                'facility_name'=>(string)$xml->facility->facility_name,
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
        }
        //patient section
        if(count($xml->patient) >0){
          if(isset($xml->patient->patient_name)){
            $data['patient_name']=(string)$xml->patient->patient_name;
          }if(isset($xml->patient->surname)){
            $data['surname']=(string)$xml->patient->surname;
          }if(isset($xml->patient->art_no)){
            $data['art_no']=(string)$xml->patient->art_no;
          }if(isset($xml->patient->patient_dob)){
            $data['patient_dob']=(string)$xml->patient->patient_dob;
          }if(isset($xml->patient->gender)){
            $data['gender']=(string)$xml->patient->gender;
          }if(isset($xml->patient->patient_phone_number)){
            $data['patient_phone_number']=(string)$xml->patient->patient_phone_number;
          }if(isset($xml->patient->location)){
            $data['location']=(string)$xml->patient->location;
          }if(isset($xml->patient->patient_art_date)){
            $data['patient_art_date']=(string)$xml->patient->patient_art_date;
          }if(isset($xml->patient->vl_test_reason)){
            $data['vl_test_reason']=(string)$xml->patient->vl_test_reason;
          }if(isset($xml->patient->is_patient_new)){
            $data['is_patient_new']=(string)$xml->patient->is_patient_new;
          }if(isset($xml->patient->treatment_initiation)){
            $data['treatment_initiation']=(string)$xml->patient->treatment_initiation;
          }if(isset($xml->patient->current_regimen)){
            $data['current_regimen']=(string)$xml->patient->current_regimen;
          }if(isset($xml->patient->date_of_initiation_of_current_regimen)){
            $data['date_of_initiation_of_current_regimen']=(string)$xml->patient->date_of_initiation_of_current_regimen;
          }if(isset($xml->patient->is_patient_pregnant)){
            $data['is_patient_pregnant']=(string)$xml->patient->is_patient_pregnant;
          }if(isset($xml->patient->is_patient_breastfeeding)){
            $data['is_patient_breastfeeding']=(string)$xml->patient->is_patient_breastfeeding;
          }if(isset($xml->patient->trimestre)){
            $data['trimestre']=(string)$xml->patient->trimestre;
          }if(isset($xml->patient->arv_adherence)){
            $data['arv_adherence']=(string)$xml->patient->arv_adherence;
          }if(isset($xml->patient->poor_adherence)){
            $data['poor_adherence']=(string)$xml->patient->poor_adherence;
          }if(isset($xml->patient->patient_receive_sms)){
            $data['patient_receive_sms']=(string)$xml->patient->patient_receive_sms;
          }if(isset($xml->patient->viral_load_indication)){
            $data['viral_load_indication']=(string)$xml->patient->viral_load_indication;
          }if(isset($xml->patient->enhance_session)){
            $data['enhance_session']=(string)$xml->patient->enhance_session;
          }if(isset($xml->patient->routine_monitoring_last_vl_date)){
            $data['routine_monitoring_last_vl_date']=(string)$xml->patient->routine_monitoring_last_vl_date;
          }if(isset($xml->patient->routine_monitoring_value)){
            $data['routine_monitoring_value']=(string)$xml->patient->routine_monitoring_value;
          }if(isset($xml->patient->routine_monitoring_value)){
            $data['routine_monitoring_sample_type']=(string)$xml->patient->routine_monitoring_sample_type;
          }if(isset($xml->patient->vl_treatment_failure_adherence_counseling_last_vl_date)){
            $data['vl_treatment_failure_adherence_counseling_last_vl_date']=(string)$xml->patient->vl_treatment_failure_adherence_counseling_last_vl_date;
          }if(isset($xml->patient->vl_treatment_failure_adherence_counseling_value)){
            $data['vl_treatment_failure_adherence_counseling_value']=(string)$xml->patient->vl_treatment_failure_adherence_counseling_value;
          }if(isset($xml->patient->vl_treatment_failure_adherence_counseling_sample_type)){
            $data['vl_treatment_failure_adherence_counseling_sample_type']=(string)$xml->patient->vl_treatment_failure_adherence_counseling_sample_type;
          }if(isset($xml->patient->suspected_treatment_failure_last_vl_date)){
            $data['suspected_treatment_failure_last_vl_date']=(string)$xml->patient->suspected_treatment_failure_last_vl_date;
          }if(isset($xml->patient->suspected_treatment_failure_value)){
            $data['suspected_treatment_failure_value']=(string)$xml->patient->suspected_treatment_failure_value;
          }if(isset($xml->patient->suspected_treatment_failure_sample_type)){
            $data['suspected_treatment_failure_sample_type']=(string)$xml->patient->suspected_treatment_failure_sample_type;
          }if(isset($xml->patient->switch_to_tdf_last_vl_date)){
            $data['switch_to_tdf_last_vl_date']=(string)$xml->patient->switch_to_tdf_last_vl_date;
          }if(isset($xml->patient->switch_to_tdf_value)){
            $data['switch_to_tdf_value']=(string)$xml->patient->switch_to_tdf_value;
          }if(isset($xml->patient->switch_to_tdf_sample_type)){
            $data['switch_to_tdf_sample_type']=(string)$xml->patient->switch_to_tdf_sample_type;
          }if(isset($xml->patient->missing_last_vl_date)){
            $data['missing_last_vl_date']=(string)$xml->patient->missing_last_vl_date;
          }if(isset($xml->patient->missing_value)){
            $data['missing_value']=(string)$xml->patient->missing_value;
          }if(isset($xml->patient->missing_sample_type)){
            $data['missing_sample_type']=(string)$xml->patient->missing_sample_type;
          }if(isset($xml->patient->request_clinician)){
            $data['request_clinician']=(string)$xml->patient->request_clinician;
          }if(isset($xml->patient->clinician_ph_no)){
            $data['clinician_ph_no']=(string)$xml->patient->clinician_ph_no;
          }if(isset($xml->patient->vl_focal_person)){
            $data['vl_focal_person']=(string)$xml->patient->vl_focal_person;
          }if(isset($xml->patient->focal_person_phone_number)){
            $data['focal_person_phone_number']=(string)$xml->patient->focal_person_phone_number;
          }if(isset($xml->patient->email_for_HF)){
            $data['email_for_HF']=(string)$xml->patient->email_for_HF;
          }if(isset($xml->patient->date_sample_received_at_testing_lab)){
            $data['date_sample_received_at_testing_lab']=(string)$xml->patient->date_sample_received_at_testing_lab;
          }if(isset($xml->patient->date_results_dispatched)){
            $data['date_results_dispatched']=(string)$xml->patient->date_results_dispatched;
          }if(isset($xml->patient->rejection)){
            $data['rejection']=(string)$xml->patient->rejection;
          }if(isset($xml->patient->sample_rejection_facility)){
            $data['sample_rejection_facility']=(string)$xml->patient->sample_rejection_facility;
          }if(isset($xml->patient->sample_rejection_reason)){
            $data['sample_rejection_reason']=(string)$xml->patient->sample_rejection_reason;
          }if(isset($xml->patient->age_in_yrs)){
            $data['age_in_yrs']=(string)$xml->patient->age_in_yrs;
          }if(isset($xml->patient->age_in_mnts)){
            $data['age_in_mnts']=(string)$xml->patient->age_in_mnts;
          }if(isset($xml->patient->treatment_initiated_date)){
            $data['treatment_initiated_date']=(string)$xml->patient->treatment_initiated_date;
          }if(isset($xml->patient->arc_no)){
            $data['arc_no']=(string)$xml->patient->arc_no;
          }if(isset($xml->patient->treatment_details)){
            $data['treatment_details']=(string)$xml->patient->treatment_details;
          }if(isset($xml->patient->drug_substitution)){
            $data['drug_substitution']=(string)$xml->patient->drug_substitution;
          }if(isset($xml->patient->collected_by)){
            $data['collected_by']=(string)$xml->patient->collected_by;
          }if(isset($xml->patient->support_partner)){
            $data['support_partner']=(string)$xml->patient->support_partner;
          }if(isset($xml->patient->has_patient_changed_regimen)){
            $data['has_patient_changed_regimen']=(string)$xml->patient->has_patient_changed_regimen;
          }if(isset($xml->patient->reason_for_regimen_change)){
            $data['reason_for_regimen_change']=(string)$xml->patient->reason_for_regimen_change;
          }if(isset($xml->patient->date_of_regimen_changed)){
            $data['date_of_regimen_changed']=(string)$xml->patient->date_of_regimen_changed;
          }
        }
        //sample section
        if(count($xml->sample) >0){
          if(isset($xml->sample->sample_collection_date)){
            $data['sample_collection_date']=(string)$xml->sample->sample_collection_date;
          }if(isset($xml->sample->date_of_demand)){
            $data['date_of_demand']=(string)$xml->sample->date_of_demand;
          }if(isset($xml->sample->sample_type)){
              $data['sample_id'] = NULL;
              $specimenTypeQuery = 'select sample_id from r_sample_type where sample_name = "'.(string)$xml->sample->sample_type.'"';
              $specimenResult = $db->rawQuery($specimenTypeQuery);
              if(isset($specimenResult[0]['sample_id'])){
                 $data['sample_id'] = $specimenResult[0]['sample_id'];
              }else{
                 $sampleTypeData = array(
                    'sample_name'=>(string)$xml->sample->sample_type,
                    'status'=>'active'
                 );
                 $id = $db->insert('r_sample_type',$sampleTypeData);
                 $data['sample_id'] = $id;
              }
          }if(isset($xml->sample->plasma_conservation_temperature)){
            $data['plasma_conservation_temperature']=(string)$xml->sample->plasma_conservation_temperature;
          }if(isset($xml->sample->duration_of_conservation)){
            $data['duration_of_conservation']=(string)$xml->sample->duration_of_conservation;
          }if(isset($xml->sample->viral_load_no)){
            $data['viral_load_no']=(string)$xml->sample->viral_load_no;
          }if(isset($xml->sample->vl_test_platform)){
            $data['vl_test_platform']=(string)$xml->sample->vl_test_platform;
          }if(isset($xml->sample->testing_status)){
            $data['status'] = NULL;
            $statusQuery = 'select status_id from testing_status where status_name = "'.(string)$xml->sample->testing_status.'" OR status_name = "'.strtolower((string)$xml->sample->testing_status).'"';
            $statusResult = $db->rawQuery($statusQuery);
            if(isset($statusResult[0]['status_id'])){
              $data['status'] = $statusResult[0]['status_id'];
            }else{
              $tStatusData = array(
                'status_name'=>(string)$xml->sample->testing_status
              );
              $id = $db->insert('testing_status',$tStatusData);
              $data['status'] = $id;
            }
          }
        }
        //lab section
        if(count($xml->lab) >0){
          $data['lab_id'] = NULL;
          if(isset($xml->lab->lab_name)){
            $labQuery = 'select facility_id from facility_details where facility_name = "'.(string)$xml->lab->lab_name.'"';
            $labResult = $db->rawQuery($labQuery);
            if(isset($labResult[0]['facility_id'])){
               $data['lab_id'] = $labResult[0]['facility_id'];
            }else{
               $labData = array(
                  'facility_name'=>(string)$xml->lab->lab_name,
                  'facility_type'=>2,
                  'status'=>'active'
                );
               $id = $db->insert('facility_details',$labData);
               $data['lab_id'] = $id;
            }
          }if(isset($xml->lab->lab_no)){
            $data['lab_no']=(string)$xml->lab->lab_no;
          }if(isset($xml->lab->lab_contact_person)){
            $data['lab_contact_person']=(string)$xml->lab->lab_contact_person;
          }if(isset($xml->lab->lab_contact_person)){
            $data['lab_phone_no']=(string)$xml->lab->lab_phone_no;
          }
        }
        //result section
        if(count($xml->result) >0){
          //result approved by
          if(isset($xml->result->result_approved_by) && (string)$xml->result->result_approved_by!= ''){
            //get role
            $roleId = 0;
            if(isset($xml->result->result_approved_by_role) && (string)$xml->result->result_approved_by_role != ''){
              $roleQuery = 'select role_id from roles where role_name = "'.(string)$xml->result->result_approved_by_role.'"';
              $roleResult = $db->rawQuery($roleQuery);
              if(isset($roleResult[0]['role_id'])){
                $roleId = $roleResult[0]['role_id'];
              }else{
                $roleData = array(
                  'role_name'=>(string)$xml->result->result_approved_by_role,
                  'role_code'=>NULL,
                  'status'=>NULL,
                  'landing_page'=>NULL
                );
                $id = $db->insert('roles',$roleData);
                $roleId = $id;
              }
            }
            //set approved by info.
            $userQuery = 'select user_id from user_details where user_name = "'.(string)$xml->result->result_approved_by.'"';
            $userResult = $db->rawQuery($userQuery);
            if(isset($userResult[0]['user_id'])){
              $userId = $userResult[0]['user_id'];
            }else{
              $approvedByData = array(
                  'user_name'=>(string)$xml->result->result_approved_by,
                  'email'=>NULL,
                  'phone_number'=>NULL,
                  'login_id'=>NULL,
                  'password'=>NULL,
                  'role_id'=>$roleId,
                  'status'=>NULL
                );
              $id = $db->insert('user_details',$approvedByData);
              $userId = $id;
            }
            $data['result_approved_by']= $userId;
            $data['result_approved_on']= (string)$xml->result->result_approved_on;
          }
          //result reviewed by
          if(isset($xml->result->result_reviewed_by) && (string)$xml->result->result_reviewed_by!= ''){
            //get role
            $roleId = 0;
            if(isset($xml->result->result_reviewed_by_role) && (string)$xml->result->result_reviewed_by_role != ''){
              $roleQuery = 'select role_id from roles where role_name = "'.(string)$xml->result->result_reviewed_by_role.'"';
              $roleResult = $db->rawQuery($roleQuery);
              if(isset($roleResult[0]['role_id'])){
                $roleId = $roleResult[0]['role_id'];
              }else{
                $roleData = array(
                  'role_name'=>(string)$xml->result->result_reviewed_by_role,
                  'role_code'=>NULL,
                  'status'=>NULL,
                  'landing_page'=>NULL
                );
                $id = $db->insert('roles',$roleData);
                $roleId = $id;
              }
            }
            //set reviewed by info.
            $userQuery = 'select user_id from user_details where user_name = "'.(string)$xml->result->result_reviewed_by.'"';
            $userResult = $db->rawQuery($userQuery);
            if(isset($userResult[0]['user_id'])){
              $userId = $userResult[0]['user_id'];
            }else{
              $reviewedByData = array(
                  'user_name'=>(string)$xml->result->result_reviewed_by,
                  'email'=>NULL,
                  'phone_number'=>NULL,
                  'login_id'=>NULL,
                  'password'=>NULL,
                  'role_id'=>$roleId,
                  'status'=>NULL
                );
              $id = $db->insert('user_details',$reviewedByData);
              $userId = $id;
            }
            $data['result_reviewed_by']= $userId;
            $data['result_reviewed_date']= (string)$xml->result->result_reviewed_date;
          }
          if(isset($xml->result->log_value)){
            $data['log_value']=(string)$xml->result->log_value;
          }if(isset($xml->result->absolute_value)){
            $data['absolute_value']=(string)$xml->result->absolute_value;
          }if(isset($xml->result->absolute_decimal_value)){
            $data['absolute_decimal_value']=(string)$xml->result->absolute_decimal_value;
          }if(isset($xml->result->text_value)){
            $data['text_value']=(string)$xml->result->text_value;
          }if(isset($xml->result->result)){
            $data['result']=(string)$xml->result->result;
          }if(isset($xml->result->comments)){
            $data['comments']=(string)$xml->result->comments;
          }if(isset($xml->result->justification)){
            $data['justification']=(string)$xml->result->justification;
          }if(isset($xml->result->test_methods)){
            $data['test_methods']=(string)$xml->result->test_methods;
          }if(isset($xml->result->contact_complete_status)){
            $data['contact_complete_status']=(string)$xml->result->contact_complete_status;
          }if(isset($xml->result->last_viral_load_date)){
            $data['last_viral_load_date']=(string)$xml->result->last_viral_load_date;
          }if(isset($xml->result->last_viral_load_result)){
            $data['last_viral_load_result']=(string)$xml->result->last_viral_load_result;
          }if(isset($xml->result->viral_load_log)){
            $data['viral_load_log']=(string)$xml->result->viral_load_log;
          }
        }
        //instance
        if(isset($xml->instance)){
          $data['vl_instance_id']=(string)$xml->instance;
        }
        //general
        if(count($xml->general) > 0){
          if(isset($xml->general->form_id)){
            $data['form_id']=(string)$xml->general->form_id;
          }else{
            $data['form_id']= $country;
          }
          //created by
          if(isset($xml->general->created_by) && (string)$xml->general->created_by != ''){
            //get role
            $roleId = 0;
            if(isset($xml->general->created_by_role) && (string)$xml->general->created_by_role!= ''){
              $roleQuery = 'select role_id from roles where role_name = "'.(string)$xml->general->created_by_role.'"';
              $roleResult = $db->rawQuery($roleQuery);
              if(isset($roleResult[0]['role_id'])){
                $roleId = $roleResult[0]['role_id'];
              }else{
                $roleData = array(
                  'role_name'=>(string)$xml->general->created_by_role,
                  'role_code'=>NULL,
                  'status'=>NULL,
                  'landing_page'=>NULL
                );
                $id = $db->insert('roles',$roleData);
                $roleId = $id;
              }
            }
            //set created by info.
            $userQuery = 'select user_id from user_details where user_name = "'.(string)$xml->general->created_by.'"';
            $userResult = $db->rawQuery($userQuery);
            if(isset($userResult[0]['user_id'])){
              $userId = $userResult[0]['user_id'];
            }else{
              $createdByData = array(
                  'user_name'=>(string)$xml->general->created_by,
                  'email'=>NULL,
                  'phone_number'=>NULL,
                  'login_id'=>NULL,
                  'password'=>NULL,
                  'role_id'=>$roleId,
                  'status'=>NULL
                );
              $id = $db->insert('user_details',$createdByData);
              $userId = $id;
            }
            $data['created_by']= $userId;
            $data['created_on']= (string)$xml->general->date_generated;
          }
          //modified by
          if(isset($xml->general->modified_by) && (string)$xml->general->modified_by!= ''){
            //get role
            $roleId = 0;
            if(isset($xml->general->modified_by_role) && (string)$xml->general->modified_by_role!= ''){
              $roleQuery = 'select role_id from roles where role_name = "'.(string)$xml->general->modified_by_role.'"';
              $roleResult = $db->rawQuery($roleQuery);
              if(isset($roleResult[0]['role_id'])){
                $roleId = $roleResult[0]['role_id'];
              }else{
                $roleData = array(
                  'role_name'=>(string)$xml->general->modified_by_role,
                  'role_code'=>NULL,
                  'status'=>NULL,
                  'landing_page'=>NULL
                );
                $id = $db->insert('roles',$roleData);
                $roleId = $id;
              }
            }
            //set modified by info.
            $userQuery = 'select user_id from user_details where user_name = "'.(string)$xml->general->modified_by.'"';
            $userResult = $db->rawQuery($userQuery);
            if(isset($userResult[0]['user_id'])){
              $userId = $userResult[0]['user_id'];
            }else{
              $modifiedByData = array(
                  'user_name'=>(string)$xml->general->modified_by,
                  'email'=>NULL,
                  'phone_number'=>NULL,
                  'login_id'=>NULL,
                  'password'=>NULL,
                  'role_id'=>$roleId,
                  'status'=>NULL
                );
              $id = $db->insert('user_details',$modifiedByData);
              $userId = $id;
            }
            $data['modified_by']= $userId;
            $data['modified_on']= (string)$xml->general->date_modified;
          }
          if(isset($xml->general->batch_code)){
            $batch_code_key=NULL;
            $batch_status=NULL;
            $batchQuery = 'select batch_id from batch_details where batch_code = "'.(string)$xml->general->batch_code.'"';
            $batchResult = $db->rawQuery($batchQuery);
            if(isset($batchResult[0]['batch_id'])){
              $data['batch_id'] = $batchResult[0]['batch_id'];
            }else{
              if(isset($xml->general->batch_code_key)){
                $batch_code_key = (string)$xml->general->batch_code_key;
              }
              if(isset($xml->general->batch_status)){
                $batch_status = (string)$xml->general->batch_status;
              }
              $batchData = array(
                  'machine'=>(string)$xml->general->machine_id,
                  'batch_code'=>(string)$xml->general->batch_code,
                  'batch_code_key'=>$batch_code_key,
                  'batch_status'=>$batch_status
              );
             $id = $db->insert('batch_details',$batchData);
             $data['batch_id'] = $id;
            }
          }
          if(isset($xml->general->urgency)) {
            $data['urgency']=(string)$xml->general->urgency;
          }if(isset($xml->general->sample_code)) {
            $data['sample_code']=(string)$xml->general->sample_code;
          }if(isset($xml->general->sample_code_key)) {
            $data['sample_code_key']=(string)$xml->general->sample_code_key;
          }if(isset($xml->general->sample_code_format)) {
            $data['sample_code_format']=(string)$xml->general->sample_code_format;
          }if(isset($xml->general->serial_no)) {
            $data['serial_no']=(string)$xml->general->serial_no;
          }if(isset($xml->general->date_dispatched_from_clinic_to_lab)) {
            $data['date_dispatched_from_clinic_to_lab']=(string)$xml->general->date_dispatched_from_clinic_to_lab;
          }if(isset($xml->general->date_of_completion_of_viral_load)) {
            $data['date_of_completion_of_viral_load']=(string)$xml->general->date_of_completion_of_viral_load;
          }if(isset($xml->general->lab_tested_date)) {
            $data['lab_tested_date']=(string)$xml->general->lab_tested_date;
          }if(isset($xml->general->date_result_printed)) {
            $data['date_result_printed']=(string)$xml->general->date_result_printed;
          }if(isset($xml->general->request_mail_sent)) {
            $data['request_mail_sent']=(string)$xml->general->request_mail_sent;
          }if(isset($xml->general->result_mail_sent)) {
            $data['result_mail_sent']=(string)$xml->general->result_mail_sent;
          }if(isset($xml->general->test_request_export)) {
            $data['test_request_export']=(string)$xml->general->test_request_export;
          }if(isset($xml->general->test_request_import)) {
            $data['test_request_import']=(string)$xml->general->test_request_import;
          }if(isset($xml->general->test_result_export)) {
            $data['test_result_export']=(string)$xml->general->test_result_export;
          }if(isset($xml->general->test_result_import)) {
            $data['test_result_import']=(string)$xml->general->test_result_import;
          }if(isset($xml->general->result_coming_from)) {
            $data['result_coming_from']=(string)$xml->general->result_coming_from;
          }
          //request/result flag set
          if(isset($param) && $param == 'request'){
            $data['test_request_import'] = 1;
          }if(isset($param) && $param == 'result'){
            $data['test_result_import'] = 1;
          }
        }
        $sampleQuery = 'select vl_sample_id from vl_request_form where sample_code = "'.(string)$xml->general->sample_code.'"';
        $sampleResult = $db->rawQuery($sampleQuery);
        if(isset($sampleResult[0]['vl_sample_id'])){
          if(isset($param) && $param == 'result'){
            $db=$db->where('sample_code',(string)$xml->general->sample_code);
            $db->update($tableName,$data);
           //move updated new xml file
            copy($configResult[0]['value'] . DIRECTORY_SEPARATOR . "result" . DIRECTORY_SEPARATOR . "new" . DIRECTORY_SEPARATOR . (string)$xml->general->sample_code.'.xml',$configResult[0]['value'] . DIRECTORY_SEPARATOR . "result" . DIRECTORY_SEPARATOR . "synced" . DIRECTORY_SEPARATOR . (string)$xml->general->sample_code.'.xml');
            unlink($configResult[0]['value'] . DIRECTORY_SEPARATOR . "result" . DIRECTORY_SEPARATOR . "new" . DIRECTORY_SEPARATOR . (string)$xml->general->sample_code.'.xml');
          }else if(isset($param) && $param == 'request'){
            copy($configResult[0]['value'] . DIRECTORY_SEPARATOR . "request" . DIRECTORY_SEPARATOR . "new" . DIRECTORY_SEPARATOR . (string)$xml->general->sample_code.'.xml',$configResult[0]['value'] . DIRECTORY_SEPARATOR . "request" . DIRECTORY_SEPARATOR . "error" . DIRECTORY_SEPARATOR . (string)$xml->general->sample_code.'.xml');
          }
        }else{
          if(isset($param) && $param == 'request'){
             $id = $db->insert($tableName,$data);
             //update xml node element
              //$info = simplexml_load_file($configResult[0]['value'] . DIRECTORY_SEPARATOR . "request" . DIRECTORY_SEPARATOR . "new" . DIRECTORY_SEPARATOR . $file);
              //$info->vl_request_form->test_request_import = 1;
              //$info->asXML($configResult[0]['value'] . DIRECTORY_SEPARATOR . "request" . DIRECTORY_SEPARATOR . "new" . DIRECTORY_SEPARATOR . $file);
             //move updated new xml file
             if($id >0){
               copy($configResult[0]['value'] . DIRECTORY_SEPARATOR . "request" . DIRECTORY_SEPARATOR . "new" . DIRECTORY_SEPARATOR . (string)$xml->general->sample_code.'.xml',$configResult[0]['value'] . DIRECTORY_SEPARATOR . "request" . DIRECTORY_SEPARATOR . "synced" . DIRECTORY_SEPARATOR . (string)$xml->general->sample_code.'.xml');
               unlink($configResult[0]['value'] . DIRECTORY_SEPARATOR . "request" . DIRECTORY_SEPARATOR . "new" . DIRECTORY_SEPARATOR . (string)$xml->general->sample_code.'.xml');
             }else{
               copy($configResult[0]['value'] . DIRECTORY_SEPARATOR . "request" . DIRECTORY_SEPARATOR . "new" . DIRECTORY_SEPARATOR . (string)$xml->general->sample_code.'.xml',$configResult[0]['value'] . DIRECTORY_SEPARATOR . "request" . DIRECTORY_SEPARATOR . "error" . DIRECTORY_SEPARATOR . (string)$xml->general->sample_code.'.xml');
             }
          }else if(isset($param) && $param == 'result'){
             copy($configResult[0]['value'] . DIRECTORY_SEPARATOR . "result" . DIRECTORY_SEPARATOR . "new" . DIRECTORY_SEPARATOR . (string)$xml->general->sample_code.'.xml',$configResult[0]['value'] . DIRECTORY_SEPARATOR . "result" . DIRECTORY_SEPARATOR . "error" . DIRECTORY_SEPARATOR . (string)$xml->general->sample_code.'.xml');
             unlink($configResult[0]['value'] . DIRECTORY_SEPARATOR . "result" . DIRECTORY_SEPARATOR . "new" . DIRECTORY_SEPARATOR . (string)$xml->general->sample_code.'.xml');
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