<?php
session_start();
ob_start();
include('../includes/MysqliDb.php');
include('../General.php');
$general=new Deforay_Commons_General();

$tableName="vl_request_form";
$tableName1="activity_log";
try {
     //var_dump($_POST);die;
     if(isset($_POST['dob']) && trim($_POST['dob'])!=""){
          $_POST['dob']=$general->dateFormat($_POST['dob']);  
     }
     
     if(isset($_POST['sampleCollectionDate']) && trim($_POST['sampleCollectionDate'])!=""){
          $sampleDate = explode(" ",$_POST['sampleCollectionDate']);
          $_POST['sampleCollectionDate']=$general->dateFormat($sampleDate[0])." ".$sampleDate[1];
     }
     
     if(isset($_POST['regimenInitiatedOn']) && trim($_POST['regimenInitiatedOn'])!=""){
          $_POST['regimenInitiatedOn']=$general->dateFormat($_POST['regimenInitiatedOn']);  
     }
     
     if(isset($_POST['treatmentInitiatiatedOn']) && trim($_POST['treatmentInitiatiatedOn'])!=""){
          $_POST['treatmentInitiatiatedOn']=$general->dateFormat($_POST['treatmentInitiatiatedOn']);  
     }
     
     if(isset($_POST['rmTestingLastVLDate']) && trim($_POST['rmTestingLastVLDate'])!=""){
          $_POST['rmTestingLastVLDate']=$general->dateFormat($_POST['rmTestingLastVLDate']);  
     }
     
     if(isset($_POST['repeatTestingLastVLDate']) && trim($_POST['repeatTestingLastVLDate'])!=""){
          $_POST['repeatTestingLastVLDate']=$general->dateFormat($_POST['repeatTestingLastVLDate']);  
     }
     
     if(isset($_POST['suspendTreatmentLastVLDate']) && trim($_POST['suspendTreatmentLastVLDate'])!=""){
          $_POST['suspendTreatmentLastVLDate']=$general->dateFormat($_POST['suspendTreatmentLastVLDate']);  
     }
     //if(isset($_POST['switchToTDFLastVLDate']) && trim($_POST['switchToTDFLastVLDate'])!=""){
     //     $_POST['switchToTDFLastVLDate']=$general->dateFormat($_POST['switchToTDFLastVLDate']);  
     //}
     //if(isset($_POST['missingLastVLDate']) && trim($_POST['missingLastVLDate'])!=""){
     //     $_POST['missingLastVLDate']=$general->dateFormat($_POST['missingLastVLDate']);  
     //}
     if(isset($_POST['artnoDate']) && trim($_POST['artnoDate'])!=''){
          $artDate = explode("-",$_POST['artnoDate']);
          if(count($artDate)>2){
               $_POST['artnoDate']=$general->dateFormat($_POST['artnoDate']);
          }else{
               $_POST['artnoDate']=$general->dateFormat("01-".$_POST['artnoDate']); 
          }
     }
     
     if(isset($_POST['requestDate']) && trim($_POST['requestDate'])!=""){
          $_POST['requestDate']=$general->dateFormat($_POST['requestDate']);  
     }
     
     if(isset($_POST['sampleReceivedOn']) && trim($_POST['sampleReceivedOn'])!=""){
          $sampleReceiveDate = explode(" ",$_POST['sampleReceivedOn']);
          $_POST['sampleReceivedOn']=$general->dateFormat($sampleReceiveDate[0])." ".$sampleReceiveDate[1];
     }
     
     if(isset($_POST['sampleTestedOn']) && trim($_POST['sampleTestedOn'])!=""){
          $sampletestDate = explode(" ",$_POST['sampleTestedOn']);
          $_POST['sampleTestedOn']=$general->dateFormat($sampletestDate[0])." ".$sampletestDate[1];
     }
     
     if(isset($_POST['resultDispatchedOn']) && trim($_POST['resultDispatchedOn'])!=""){
          $sampleDispatchDate = explode(" ",$_POST['resultDispatchedOn']);
          $_POST['resultDispatchedOn']=$general->dateFormat($sampleDispatchDate[0])." ".$sampleDispatchDate[1];
     }
     
     if(isset($_POST['reviewedOn']) && trim($_POST['reviewedOn'])!=""){
          $sampleReviewDate = explode(" ",$_POST['reviewedOn']);
          $_POST['reviewedOn']=$general->dateFormat($sampleReviewDate[0])." ".$sampleReviewDate[1];
     }
     

     if(isset($_POST['artNo']) && isset($_POST['sampleCode']) && trim($_POST['artNo'])!=""){
         if(!isset($_POST['facilityId']) || trim($_POST['facilityId'])==""){
            if(trim($_POST['facilityName'])!= ''){
               $data=array(
                 'facility_name'=>$_POST['facilityName'],
                 //'facility_code'=>$_POST['facilityCode'],
                 //'country'=>$_POST['country'],
                 'facility_emails'=>$_POST['emailHf'],
                 'facility_state'=>$_POST['state'],
                 'facility_hub_name'=>$_POST['hubName'],
                 'facility_district'=>$_POST['district'],
                 'facility_type'=>1,
                 'status'=>'active'
               );
               $_POST['facilityId']=$db->insert('facility_details',$data);
            }
         }
    
          if(!isset($_POST['patientPregnant']) || trim($_POST['patientPregnant'])==''){
             $_POST['patientPregnant']='';
          }
          if(!isset($_POST['breastfeeding']) || trim($_POST['breastfeeding'])==''){
             $_POST['breastfeeding']='';
          }
          if(!isset($_POST['receiveSms']) || trim($_POST['receiveSms'])==''){
             $_POST['receiveSms']='';
          }
          if(!isset($_POST['gender']) || trim($_POST['gender'])==''){
             $_POST['gender']='';
          }
          if(isset($_POST['gender']) && trim($_POST['gender'])=='male'){
               $_POST['patientPregnant']='';
               $_POST['breastfeeding']='';
          }
          $vldata=array(
               'facility_id'=>$_POST['facilityId'],
               'sample_code'=>$_POST['sampleCode'],
               'test_urgency'=>$_POST['urgency'],
               'patient_art_no'=>$_POST['artNo'],
               'patient_first_name'=>$_POST['patientName'],
               'patient_dob'=>$_POST['dob'],
               'patient_other_id'=>$_POST['otrId'],
               'patient_age_in_years'=>$_POST['ageInYrs'],
               'patient_age_in_months'=>$_POST['ageInMtns'],
               'patient_gender'=>$_POST['gender'],
               'patient_mobile_number'=>$_POST['patientPhoneNumber'],
               'patient_location'=>$_POST['patientLocation'],
               'patient_art_date'=>$_POST['artnoDate'],
               'sample_collection_date'=>$_POST['sampleCollectionDate'],
               'sample_type'=>$_POST['sampleType'],
               'treatment_initiation'=>$_POST['treatPeriod'],
               'treatment_initiated_date'=>$_POST['treatmentInitiatiatedOn'],
               'current_regimen'=>$_POST['currentRegimen'],
               'date_of_initiation_of_current_regimen'=>$_POST['regimenInitiatedOn'],
               'treatment_details'=>$_POST['treatmentDetails'],
               'is_patient_pregnant'=>$_POST['patientPregnant'],
               'patient_anc_no'=>$_POST['arcNo'],
               'is_patient_breastfeeding'=>$_POST['breastfeeding'],
               'arv_adherance_percentage'=>$_POST['arvAdherence'],
               'consent_to_receive_sms'=>$_POST['receiveSms'],
               'reason_for_vl_testing'=>(isset($_POST['stViralTesting']))?$_POST['stViralTesting']:null,
               'number_of_enhanced_sessions'=>$_POST['enhanceSession'],
               'last_vl_date_routine'=>$_POST['rmTestingLastVLDate'],
               'last_vl_result_routine'=>$_POST['rmTestingVlValue'],
               'last_vl_sample_type_routine'=>$_POST['rmTestingSampleType'],
               'last_vl_date_failure_ac'=>$_POST['repeatTestingLastVLDate'],
               'last_vl_result_failure_ac'=>$_POST['repeatTestingVlValue'],
               'last_vl_sample_type_failure_ac'=>$_POST['repeatTestingSampleType'],
               'last_vl_date_failure'=>$_POST['suspendTreatmentLastVLDate'],
               'last_vl_result_failure'=>$_POST['suspendTreatmentVlValue'],
               'last_vl_sample_type_failure'=>$_POST['suspendTreatmentSampleType'],
               'request_clinician_name'=>$_POST['requestClinician'],
               'request_clinician_phone_number'=>$_POST['clinicianPhone'],
          //'sample_tested_datetime'=>$_POST['requestDate'],
               'vl_focal_person'=>$_POST['vlFocalPerson'],
               'vl_focal_person_phone_number'=>$_POST['vlPhoneNumber'],
               'lab_name'=>$_POST['labName'],
               'lab_contact_person'=>$_POST['labContactPerson'],
          'lab_phone_number'=>$_POST['labPhoneNo'],
          'sample_received_at_vl_lab_datetime'=>$_POST['sampleReceivedOn'],
          'sample_tested_datetime'=>$_POST['sampleTestedOn'],
          'result_dispatched_datetime'=>$_POST['resultDispatchedOn'],
               'result_reviewed_by'=>$_SESSION['userId'],
          'result_reviewed_datetime'=>$_POST['reviewedOn'],
               'test_methods'=>$_POST['testMethods'],
          'result_value_log'=>$_POST['logValue'],
          'result_value_absolute'=>$_POST['absoluteValue'],
          'result_value_text'=>$_POST['textValue'],
               'result'=>$_POST['result'],
          'approver_comments'=>$_POST['comments'],
               'result_status'=>$_POST['status'],
               'is_sample_rejected'=>$_POST['rejection'],
               'sample_rejection_facility'=>$_POST['rejectionFacility'],
               'reason_for_sample_rejection'=>$_POST['rejectionReason'],
          'request_created_by'=>$_SESSION['userId'],
          'request_created_datetime'=>$general->getDateTime(),
          'last_modified_by'=>$_SESSION['userId'],
          'last_modified_datetime'=>$general->getDateTime()
             );
             //print_r($vldata);die;
               $id=$db->insert($tableName,$vldata);
               $_SESSION['alertMsg']="VL request added successfully";
               //Add event log
               $eventType = 'add-vl-request';
               $action = ucwords($_SESSION['userName']).' added a new request data with the sample code '.$_POST['sampleCode'];
               $resource = 'vl-request';
               $data=array(
               'event_type'=>$eventType,
               'action'=>$action,
               'resource'=>$resource,
               'date_time'=>$general->getDateTime()
               );
               $db->insert($tableName1,$data);
     }
    if(isset($_POST['saveNext']) && $_POST['saveNext']=='next'){
      header("location:addVlRequest.php");
    }else{
      header("location:vlRequest.php"); 
    }
    
  
} catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}