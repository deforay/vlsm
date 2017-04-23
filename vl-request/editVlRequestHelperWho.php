<?php
session_start();
ob_start();
include('../includes/MysqliDb.php');
include('../General.php');
$general=new Deforay_Commons_General();
$tableName="vl_request_form";
$tableName1="activity_log";
$vlTestReasonTable="r_vl_test_reasons";
$fDetails="facility_details";
try {
     //var_dump($_POST);die;
     if(isset($_POST['sampleCollectionDate']) && trim($_POST['sampleCollectionDate'])!=""){
          $sampleDate = explode(" ",$_POST['sampleCollectionDate']);
          $_POST['sampleCollectionDate']=$general->dateFormat($sampleDate[0])." ".$sampleDate[1];
     }else{
         $_POST['sampleCollectionDate'] = NULL;
     }
     
     if(isset($_POST['dob']) && trim($_POST['dob'])!=""){
          $_POST['dob']=$general->dateFormat($_POST['dob']);  
     }else{
        $_POST['dob'] = NULL;
     }
     
     if(isset($_POST['dateOfArtInitiation']) && trim($_POST['dateOfArtInitiation'])!=""){
          $_POST['dateOfArtInitiation']=$general->dateFormat($_POST['dateOfArtInitiation']);  
     }else{
        $_POST['dateOfArtInitiation'] = NULL;
     }
     
     if(isset($_POST['newArtRegimen']) && trim($_POST['newArtRegimen'])!=""){
          $data=array(
            'art_code'=>$_POST['newArtRegimen'],
            'nation_identifier'=>'who',
            'parent_art'=>'6'
          );
          $result=$db->insert('r_art_code_details',$data);
          $_POST['artRegimen'] = $_POST['newArtRegimen'];
     }
     //update facility code
     if($_POST['fCode']!=''){
          $fData = array('facility_code'=>$_POST['fCode']);
          $db=$db->where('facility_id',$_POST['fName']);
          $id=$db->update($fDetails,$fData);
     }
     if(isset($_POST['gender']) && trim($_POST['gender'])=='male'){
          $_POST['breastfeeding']='';
     }
     $instanceId = '';
    if(isset($_SESSION['instanceId'])){
          $instanceId = $_SESSION['instanceId'];
    }
    
    //$_POST['result'] = '';
    if($_POST['rmTestingVlValue']!=''){
     $_POST['result'] = $_POST['rmTestingVlValue']; 
    }
    if($_POST['repeatTestingVlValue']!=''){
     $_POST['result'] = $_POST['repeatTestingVlValue']; 
    }
    if($_POST['suspendTreatmentVlValue']!=''){
     $_POST['result'] = $_POST['suspendTreatmentVlValue']; 
    }
    
     $vldata=array(
          'vlsm_instance_id'=>$instanceId,
          'vlsm_country_id'=>'6',
          'serial_no'=>(isset($_POST['sampleCode']) && $_POST['sampleCode']!='' ? $_POST['sampleCode'] :  NULL) ,
          'sample_code'=>(isset($_POST['sampleCode']) && $_POST['sampleCode']!='' ? $_POST['sampleCode'] :  NULL),
          'patient_other_id'=>(isset($_POST['uniqueId']) && $_POST['uniqueId']!='' ? $_POST['uniqueId'] :  NULL),
          'facility_id'=>(isset($_POST['fName']) && $_POST['fName']!='' ? $_POST['fName'] :  NULL),
          'sample_collection_date'=>$_POST['sampleCollectionDate'],
          'patient_gender'=>(isset($_POST['gender']) && $_POST['gender']!='' ? $_POST['gender'] :  NULL),
          'patient_dob'=>$_POST['dob'],
          'patient_age_in_years'=>(isset($_POST['ageInYears']) && $_POST['ageInYears']!='' ? $_POST['ageInYears'] :  NULL),
          'patient_age_in_months'=>(isset($_POST['ageInMonths']) && $_POST['ageInMonths']!='' ? $_POST['ageInMonths'] :  NULL),
          'is_patient_breastfeeding'=>(isset($_POST['breastfeeding']) && $_POST['breastfeeding']!='' ? $_POST['breastfeeding'] :  NULL),
          'patient_art_no'=>(isset($_POST['artNo']) && $_POST['artNo']!='' ? $_POST['artNo'] :  NULL),
          'current_regimen'=>(isset($_POST['artRegimen']) && $_POST['artRegimen']!='' ? $_POST['artRegimen'] :  NULL),
          'date_of_initiation_of_current_regimen'=>$_POST['dateOfArtInitiation'],
          'patient_mobile_number'=>(isset($_POST['patientPhoneNumber']) && $_POST['patientPhoneNumber']!='' ? $_POST['patientPhoneNumber'] :  NULL),
          //'last_viral_load_date'=>$_POST['lastViralLoadTestDate'],
          //'last_viral_load_result'=>(isset($_POST['lastViralLoadResult']) && $_POST['lastViralLoadResult']!='' ? $_POST['lastViralLoadResult'] :  NULL),
          'sample_type'=>(isset($_POST['specimenType']) && $_POST['specimenType']!='' ? $_POST['specimenType'] :  NULL),
          'arv_adherance_percentage'=>(isset($_POST['arvAdherence']) && $_POST['arvAdherence']!='' ? $_POST['arvAdherence'] :  NULL),
          //'result'=>(isset($_POST['result']) && $_POST['result']!='' ? $_POST['result'] :  NULL),
          'last_vl_date_routine'=>(isset($_POST['rmTestingLastVLDate']) && $_POST['rmTestingLastVLDate']!='' ? $general->dateFormat($_POST['rmTestingLastVLDate']) :  NULL),
          'last_vl_result_routine'=>(isset($_POST['rmTestingVlValue']) && $_POST['rmTestingVlValue']!='' ? $_POST['rmTestingVlValue'] :  NULL),
          'last_vl_date_failure_ac'=>(isset($_POST['repeatTestingLastVLDate']) && $_POST['repeatTestingLastVLDate']!='' ? $general->dateFormat($_POST['repeatTestingLastVLDate']) :  NULL),
          'last_vl_result_failure_ac'=>(isset($_POST['repeatTestingVlValue']) && $_POST['repeatTestingVlValue']!='' ? $_POST['repeatTestingVlValue'] :  NULL),
          'last_vl_date_failure'=>(isset($_POST['suspendTreatmentLastVLDate']) && $_POST['suspendTreatmentLastVLDate']!='' ? $general->dateFormat($_POST['suspendTreatmentLastVLDate']) :  NULL),
          'last_vl_result_failure'=>(isset($_POST['suspendTreatmentVlValue']) && $_POST['suspendTreatmentVlValue']!='' ? $_POST['suspendTreatmentVlValue'] :  NULL),
          'patient_receiving_therapy'=>(isset($_POST['theraphy']) && $_POST['theraphy']!='' ? $_POST['theraphy'] :  NULL),
          'patient_drugs_transmission'=>(isset($_POST['drugTransmission']) && $_POST['drugTransmission']!='' ? $_POST['drugTransmission'] :  NULL),
          'patient_tb'=>(isset($_POST['patientTB']) && $_POST['patientTB']!='' ? $_POST['patientTB'] :  NULL),
          'patient_tb_yes'=>(isset($_POST['patientTBActive']) && $_POST['patientTBActive']!='' ? $_POST['patientTBActive'] :  NULL),
          'request_clinician_name'=>(isset($_POST['reqClinician']) && $_POST['reqClinician']!='' ? $_POST['reqClinician'] :  NULL),
          'reason_for_sample_rejection'=>(isset($_POST['rejectionReason']) && $_POST['rejectionReason']!='' ? $_POST['rejectionReason'] :  NULL),
          'test_requested_on'=>(isset($_POST['requestDate']) && $_POST['requestDate']!='' ? $general->dateFormat($_POST['requestDate']) :  NULL),
          'last_modified_by'=>$_SESSION['userId'],
          'last_modified_datetime'=>$general->getDateTime(),
          'manual_result_entry'=>'yes'
        );
     //echo "<pre>";var_dump($vldata);die;
          $db=$db->where('vl_sample_id',$_POST['vlSampleId']);
          $id=$db->update($tableName,$vldata);
          if($id>0){
               $_SESSION['alertMsg']="VL request updated successfully";
               //Add event log
               $eventType = 'update-vl-request-who';
               $action = ucwords($_SESSION['userName']).' updated a request data with the sample code '.$_POST['uniqueId'];
               $resource = 'vl-request-who';
               $data=array(
               'event_type'=>$eventType,
               'action'=>$action,
               'resource'=>$resource,
               'date_time'=>$general->getDateTime()
               );
               $db->insert($tableName1,$data);
               header("location:vlRequest.php");
          }else{
               $_SESSION['alertMsg']="Please try again later";
          }
  
} catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}