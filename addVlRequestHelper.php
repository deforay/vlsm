<?php
ob_start();
include('./includes/MysqliDb.php');
include('header.php');
include('General.php');

$general=new Deforay_Commons_General();

$tableName="vl_request_form";

try {
     //var_dump($_POST);die;
     if(isset($_POST['dob']) && trim($_POST['dob'])!=""){
          $_POST['dob']=$general->dateFormat($_POST['dob']);  
     }
     
     if(isset($_POST['sampleCollectionDate']) && trim($_POST['sampleCollectionDate'])!=""){
          $_POST['sampleCollectionDate']=$general->dateFormat($_POST['sampleCollectionDate']);  
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
     
     if(isset($_POST['requestDate']) && trim($_POST['requestDate'])!=""){
          $_POST['requestDate']=$general->dateFormat($_POST['requestDate']);  
     }

     if(isset($_POST['sampleReceivedOn']) && trim($_POST['sampleReceivedOn'])!=""){
          $_POST['sampleReceivedOn']=$general->dateFormat($_POST['sampleReceivedOn']);  
     }
     
     if(isset($_POST['despachedOn']) && trim($_POST['despachedOn'])!=""){
          $_POST['despachedOn']=$general->dateFormat($_POST['despachedOn']);  
     }

     if(isset($_POST['artNo']) && trim($_POST['artNo'])!=""){
         if(!isset($_POST['facilityId']) || trim($_POST['facilityId'])==""){
          $data=array(
            'facility_name'=>$_POST['facilityName'],
            'facility_code'=>$_POST['facilityCode'],
            'country'=>$_POST['country'],
            'state'=>$_POST['state'],
            'hub_name'=>$_POST['hubName'],
            'status'=>'active'
          );
          
          $_POST['facilityId']=$db->insert('facility_details',$data);
        
    }
    
     if(!isset($_POST['patientPregnant']) || trim($_POST['patientPregnant'])==''){
        $_POST['patientPregnant']='';
     }
     if(!isset($_POST['breastfeeding']) || trim($_POST['breastfeeding'])==''){
        $_POST['breastfeeding']='';
     }
     if(!isset($_POST['gender']) || trim($_POST['gender'])==''){
        $_POST['gender']='';
     }
     
     $vldata=array(
          'facility_id'=>$_POST['facilityId'],   
          'art_no'=>$_POST['artNo'],
          'patient_name'=>$_POST['patientName'],
          'patient_dob'=>$_POST['dob'],
          'other_id'=>$_POST['otrId'],
          'age_in_yrs'=>$_POST['ageInYrs'],
          'age_in_mnts'=>$_POST['ageInMtns'],
          'gender'=>$_POST['gender'],
          'patient_phone_number'=>$_POST['patientPhoneNumber'],
          'sample_collection_date'=>$_POST['sampleCollectionDate'],
          'sample_id'=>$_POST['sampleType'],
          'treatment_initiation'=>$_POST['treatPeriod'],
          'treatment_initiated_date'=>$_POST['treatmentInitiatiatedOn'],
          'current_regimen'=>$_POST['currentRegimen'],
          'date_of_initiation_of_current_regimen'=>$_POST['regimenInitiatedOn'],
          'treatment_details'=>$_POST['treatmentDetails'],
          'is_patient_pregnant'=>$_POST['patientPregnant'],
          'arc_no'=>$_POST['arcNo'],
          'is_patient_breastfeeding'=>$_POST['breastfeeding'],
          'arv_adherence'=>$_POST['arvAdherence'],
          'routine_monitoring_last_vl_date'=>$_POST['rmTestingLastVLDate'],
          'routine_monitoring_value'=>$_POST['rmTestingVlValue'],
          'routine_monitoring_sample_type'=>$_POST['rmTestingSampleType'],
          'vl_treatment_failure_adherence_counseling_last_vl_date'=>$_POST['repeatTestingLastVLDate'],
          'vl_treatment_failure_adherence_counseling_value'=>$_POST['repeatTestingVlValue'],
          'vl_treatment_failure_adherence_counseling_sample_type'=>$_POST['repeatTestingSampleType'],
          'suspected_treatment_failure_last_vl_date'=>$_POST['suspendTreatmentLastVLDate'],
          'suspected_treatment_failure_value'=>$_POST['suspendTreatmentVlValue'],
          'suspected_treatment_failure_sample_type'=>$_POST['suspendTreatmentSampleType'],
          'request_clinician'=>$_POST['requestClinician'],
          'clinician_ph_no'=>$_POST['clinicianPhone'],
          'request_date'=>$_POST['requestDate'],
          'vl_focal_person'=>$_POST['vlFocalPerson'],
          'focal_person_phone_number'=>$_POST['vlPhoneNumber'],
          'email_for_HF'=>$_POST['emailHf'],
          'date_sample_received_at_testing_lab'=>$_POST['sampleReceivedOn'],
          'date_results_dispatched'=>$_POST['despachedOn'],
          'rejection'=>$_POST['rejection'],
          'created_by'=>$_SESSION['userId'],
          'created_on'=>$general->getDateTime()
        );
        
          $id=$db->insert($tableName,$vldata);
          $_SESSION['alertMsg']="VL request added successfully";
    }
    
    header("location:vlRequest.php");
  
} catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}