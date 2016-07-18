<?php
ob_start();
include('./includes/MysqliDb.php');
include('header.php');


$tableName="vl_request_form";


try {
   // var_dump($_POST);die;
   
    if(isset($_POST['artNo']) && trim($_POST['artNo'])!=""){
         if(!isset($_POST['facilityId']) || trim($_POST['facilityId'])==""){
        $data=array(
        'facility_name'=>$_POST['facilityName'],
        'facility_code'=>$_POST['facilityCode'],
        //'phone_number'=>$_POST['phoneNo'],
     //   'address'=>$_POST['address'],
        'country'=>$_POST['country'],
        'state'=>$_POST['state'],
        'hub_name'=>$_POST['hubName'],
        'status'=>'active'
        );
        //print_r($data);die;
        $_POST['facilityId']=$db->insert('facility_details',$data);    
    }
    if(!isset($_POST['patientPregnant']) || $_POST['patientPregnant']==''){
        $_POST['patientPregnant']='';
    }
     if(!isset($_POST['breastfeeding']) || $_POST['breastfeeding']==''){
        $_POST['breastfeeding']='';
    }
    if(!isset($_POST['gender']) || $_POST['gender']==''){
        $_POST['gender']='';
    }
        $vldata=array(
          'facility_id'=>$_POST['facilityId'],   
        'art_no'=>$_POST['artNo'],
        'patient_name'=>$_POST['patientName'],
        //'phone_number'=>$_POST['phoneNo'],
     //   'address'=>$_POST['address'],
        'patient_dob'=>$_POST['dob'],
        'other_id'=>$_POST['otrId'],
        'age_in_yrs'=>$_POST['ageInYrs'],
         'age_in_mnts'=>$_POST['ageInMtns'],
        'gender'=>$_POST['gender'],
        'patient_phone_number'=>$_POST['patientPhoneNumber'],
        'sample_collection_date'=>$_POST['sampleDate'],
        'sample_id'=>$_POST['sampleType'],
        'treatment_initiation'=>$_POST['treatPeriod'],
        'treatment_initiated_date'=>$_POST['treatmentInitiatiatedOn'],
        'current_regimen'=>$_POST['currentRegimen'],
        'date_of_initiation_of_current_regimen'=>$_POST['regimenInitiatedOn'],
        'treatmentDetails'=>$_POST['treatmentDetails'],
        'is_patient_pregnant'=>$_POST['patientPregnant'],
        'arc_no'=>$_POST['arcNo'],
        'is_patient_breastfeeding'=>$_POST['breastfeeding'],
        'arv_adherence'=>$_POST['arvAdherence'],
        'routine_monitoring_last_vl_date'=>$_POST['RmTestingLastVLDate'],
       'routine_monitoring_value'=>$_POST['RmTestingLastValue'],
       'routine_monitoring_sample_type'=>$_POST['RmTestingSampleType'],
       'vl_treatment_failure_adherence_counseling_last_vl_date'=>$_POST['RepeatTestingLastVLDate'],
       'vl_treatment_failure_adherence_counseling_value'=>$_POST['RepeatTestingVlValue'],
       'vl_treatment_failure_adherence_counseling_sample_type'=>$_POST['RepeatTestingSampleType'],
       'suspected_treatment_failure_last_vl_date'=>$_POST['suspendTreatmentLastVLDate'],
       'suspected_treatment_failure_value'=>$_POST['suspendTreatmentVlValue'],
       'suspected_treatment_failure_sample_type'=>$_POST['suspendTreatmentSampleType'],
       'request_clinician'=>$_POST['requestClinician'],
       'clinician_ph_no'=>$_POST['clinicianPhone'],
       'request_date'=>$_POST['requestDate'],
       'vl_focal_person'=>$_POST['vlFocalPerson'],
       'focal_person_phone_number'=>$_POST['VLPhoneNumber'],
       'email_for_HF'=>$_POST['emailHf'],
       'date_sample_received_at_testing_lab'=>$_POST['sampleReceivedOn'],
       'date_results_dispatched'=>$_POST['despachedOn'],
       'rejection'=>$_POST['rejection'],
        );
    $id=$db->insert($tableName,$vldata);
   
        $_SESSION['alertMsg']="VL request added successfully";
    }
    
    header("location:facilities.php");
  
} catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}