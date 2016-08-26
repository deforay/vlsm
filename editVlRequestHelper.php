<?php
ob_start();
session_start();
include('./includes/MysqliDb.php');
//include('header.php');
include('General.php');

$general=new Deforay_Commons_General();

$tableName="vl_request_form";
$treamentId=(int) base64_decode($_POST['treamentId']);
try {
     //var_dump($_POST);die;
     if(isset($_POST['artNo']) && trim($_POST['artNo'])!="" && $treamentId>0){
          if(!isset($_POST['facilityId']) || trim($_POST['facilityId'])==""){
               $data=array(
                 'facility_name'=>$_POST['facilityName'],
                 //'facility_code'=>$_POST['facilityCode'],
                 //'country'=>$_POST['country'],
                 'state'=>$_POST['state'],
                 'hub_name'=>$_POST['hubName'],
                 'district'=>$_POST['district'],
                 'status'=>'active'
               );
          
               $_POST['facilityId']=$db->insert('facility_details',$data);
          }
          
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
          
          if(isset($_POST['switchToTDFLastVLDate']) && trim($_POST['switchToTDFLastVLDate'])!=""){
               $_POST['switchToTDFLastVLDate']=$general->dateFormat($_POST['switchToTDFLastVLDate']);  
          }
          if(isset($_POST['missingLastVLDate']) && trim($_POST['missingLastVLDate'])!=""){
               $_POST['missingLastVLDate']=$general->dateFormat($_POST['missingLastVLDate']);  
          }
          
          if(isset($_POST['requestDate']) && trim($_POST['requestDate'])!=""){
               $_POST['requestDate']=$general->dateFormat($_POST['requestDate']);  
          }
     
          if(isset($_POST['sampleReceivedOn']) && trim($_POST['sampleReceivedOn'])!=""){
               $_POST['sampleReceivedOn']=$general->dateFormat($_POST['sampleReceivedOn']);  
          }
          
          if(isset($_POST['sampleTestedOn']) && trim($_POST['sampleTestedOn'])!=""){
               $_POST['sampleTestedOn']=$general->dateFormat($_POST['sampleTestedOn']);  
          }
          
          if(isset($_POST['resultDispatchedOn']) && trim($_POST['resultDispatchedOn'])!=""){
               $_POST['resultDispatchedOn']=$general->dateFormat($_POST['resultDispatchedOn']);  
          }
          
          if(isset($_POST['reviewedOn']) && trim($_POST['reviewedOn'])!=""){
               $_POST['reviewedOn']=$general->dateFormat($_POST['reviewedOn']);  
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
     
     $vldata=array(
          'facility_id'=>$_POST['facilityId'],
          'sample_code'=>$_POST['sampleCode'],
          'art_no'=>$_POST['artNo'],
          'patient_name'=>$_POST['patientName'],
          'patient_dob'=>$_POST['dob'],
          'other_id'=>$_POST['otrId'],
          'age_in_yrs'=>$_POST['ageInYrs'],
          'age_in_mnts'=>$_POST['ageInMtns'],
          'gender'=>$_POST['gender'],
          'patient_phone_number'=>$_POST['patientPhoneNumber'],
          'location'=>$_POST['patientLocation'],
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
          'patient_receive_sms'=>$_POST['receiveSms'],
          'routine_monitoring_last_vl_date'=>$_POST['rmTestingLastVLDate'],
          'routine_monitoring_value'=>$_POST['rmTestingVlValue'],
          'routine_monitoring_sample_type'=>$_POST['rmTestingSampleType'],
          'vl_treatment_failure_adherence_counseling_last_vl_date'=>$_POST['repeatTestingLastVLDate'],
          'vl_treatment_failure_adherence_counseling_value'=>$_POST['repeatTestingVlValue'],
          'vl_treatment_failure_adherence_counseling_sample_type'=>$_POST['repeatTestingSampleType'],
          'suspected_treatment_failure_last_vl_date'=>$_POST['suspendTreatmentLastVLDate'],
          'suspected_treatment_failure_value'=>$_POST['suspendTreatmentVlValue'],
          'suspected_treatment_failure_sample_type'=>$_POST['suspendTreatmentSampleType'],
          'switch_to_tdf_last_vl_date'=>$_POST['switchToTDFLastVLDate'],
          'switch_to_tdf_value'=>$_POST['switchToTDFVlValue'],
          'switch_to_tdf_sample_type'=>$_POST['switchToTDFSampleType'],
          'missing_last_vl_date'=>$_POST['missingLastVLDate'],
          'missing_value'=>$_POST['missingVlValue'],
          'missing_sample_type'=>$_POST['missingSampleType'],
          'request_clinician'=>$_POST['requestClinician'],
          'clinician_ph_no'=>$_POST['clinicianPhone'],
          'request_date'=>$_POST['requestDate'],
          'vl_focal_person'=>$_POST['vlFocalPerson'],
          'focal_person_phone_number'=>$_POST['vlPhoneNumber'],
          'email_for_HF'=>$_POST['emailHf'],
          'lab_name'=>$_POST['labName'],
          'lab_contact_person'=>$_POST['labContactPerson'],
          'lab_phone_no'=>$_POST['labPhoneNo'],
          'date_sample_received_at_testing_lab'=>$_POST['sampleReceivedOn'],
          'lab_tested_date'=>$_POST['sampleTestedOn'],
          'date_results_dispatched'=>$_POST['resultDispatchedOn'],
          'result_reviewed_by'=>$_POST['reviewedBy'],
          'result_reviewed_date'=>$_POST['reviewedOn'],
          'justification'=>$_POST['justification'],
          'log_value'=>$_POST['logValue'],
          'absolute_value'=>$_POST['absoluteValue'],
          'text_value'=>$_POST['textValue'],
          'result'=>$_POST['result'],
          'comments'=>$_POST['comments'],
          'status'=>$_POST['status'],
          'rejection'=>$_POST['rejection']
        );
          
          $db=$db->where('treament_id',$treamentId);
          $db->update($tableName,$vldata);
          
          $_SESSION['alertMsg']="VL request updated successfully";
     }
    
   
    header("location:vlRequest.php"); 
  
} catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}