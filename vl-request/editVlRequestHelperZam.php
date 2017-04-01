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
     if(isset($_POST['sampleCollectionDate']) && trim($_POST['sampleCollectionDate'])!=""){
          $sampleDate = explode(" ",$_POST['sampleCollectionDate']);
          $_POST['sampleCollectionDate']=$general->dateFormat($sampleDate[0])." ".$sampleDate[1];
     }else{
         $_POST['sampleCollectionDate'] = NULL;
     }
     
     if(isset($_POST['sampleReceivedDate']) && trim($_POST['sampleReceivedDate'])!=""){
          $sampleReceivedDate = explode(" ",$_POST['sampleReceivedDate']);
          $_POST['sampleReceivedDate']=$general->dateFormat($sampleReceivedDate[0])." ".$sampleReceivedDate[1];
     }else{
        $_POST['sampleReceivedDate'] = NULL;
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
     
     if(isset($_POST['lastViralLoadTestDate']) && trim($_POST['lastViralLoadTestDate'])!=""){
          $_POST['lastViralLoadTestDate']=$general->dateFormat($_POST['lastViralLoadTestDate']);  
     }else{
        $_POST['lastViralLoadTestDate'] = NULL;
     }
     if(isset($_POST['sampleTestingDateAtLab']) && trim($_POST['sampleTestingDateAtLab'])!=""){
          $sampleTestingDateLab = explode(" ",$_POST['sampleTestingDateAtLab']);
          $_POST['sampleTestingDateAtLab']=$general->dateFormat($sampleTestingDateLab[0])." ".$sampleTestingDateLab[1];  
     }else{
        $_POST['sampleTestingDateAtLab'] = NULL;
     }
    
     if(isset($_POST['newArtRegimen']) && trim($_POST['newArtRegimen'])!=""){
          $data=array(
            'art_code'=>$_POST['newArtRegimen'],
            'nation_identifier'=>'zam',
            'parent_art'=>'4'
          );
          $result=$db->insert('r_art_code_details',$data);
          $_POST['artRegimen'] = $_POST['newArtRegimen'];
     }
     if(isset($_POST['newVlTestReason']) && trim($_POST['newVlTestReason'])!=""){
          $data=array(
            'test_reason_name'=>$_POST['newVlTestReason'],
            'test_reason_status'=>'active'
          );
          $result=$db->insert('r_vl_test_reasons',$data);
          $_POST['vlTestReason'] = $_POST['newVlTestReason'];
     }
     
     if(isset($_POST['gender']) && trim($_POST['gender'])=='male'){
          $_POST['patientPregnant']='';
          $_POST['breastfeeding']='';
     }
     $_POST['result'] = '';
     if(isset($_POST['vlResult']) && trim($_POST['vlResult'])!= ''){
          $_POST['result'] = $_POST['vlResult'];
     }
     $instanceId = '';
    if(isset($_SESSION['instanceId'])){
          $instanceId = $_SESSION['instanceId'];
    }
    $testingPlatform = '';
    if(isset($_POST['testingPlatform']) && trim($_POST['testingPlatform'])!=''){
        $platForm = explode("##",$_POST['testingPlatform']);
        $testingPlatform = $platForm[0];
    }
    
     $vldata=array(
          'serial_no'=>(isset($_POST['sampleCode']) && $_POST['sampleCode']!='' ? $_POST['sampleCode'] :  NULL) ,
          'sample_code'=>(isset($_POST['sampleCode']) && $_POST['sampleCode']!='' ? $_POST['sampleCode'] :  NULL),
          'facility_id'=>(isset($_POST['clinicName']) && $_POST['clinicName']!='' ? $_POST['clinicName'] :  NULL),
          'lab_contact_person'=>(isset($_POST['clinicianName']) && $_POST['clinicianName']!='' ? $_POST['clinicianName'] :  NULL),
          'sample_collection_date'=>$_POST['sampleCollectionDate'],
          'patient_first_name'=>(isset($_POST['patientFname']) && $_POST['patientFname']!='' ? $_POST['patientFname'] :  NULL),
          'patient_last_name'=>(isset($_POST['surName']) && $_POST['surName']!='' ? $_POST['surName'] :  NULL),
          'patient_gender'=>(isset($_POST['gender']) && $_POST['gender']!='' ? $_POST['gender'] :  NULL),
          'patient_dob'=>$_POST['dob'],
          'patient_age_in_years'=>(isset($_POST['ageInYears']) && $_POST['ageInYears']!='' ? $_POST['ageInYears'] :  NULL),
          'patient_age_in_months'=>(isset($_POST['ageInMonths']) && $_POST['ageInMonths']!='' ? $_POST['ageInMonths'] :  NULL),
          'is_patient_pregnant'=>(isset($_POST['patientPregnant']) && $_POST['patientPregnant']!='' ? $_POST['patientPregnant'] :  NULL),
          'is_patient_breastfeeding'=>(isset($_POST['breastfeeding']) && $_POST['breastfeeding']!='' ? $_POST['breastfeeding'] :  NULL),
          'patient_art_no'=>(isset($_POST['patientArtNo']) && $_POST['patientArtNo']!='' ? $_POST['patientArtNo'] :  NULL),
          'current_regimen'=>(isset($_POST['artRegimen']) && $_POST['artRegimen']!='' ? $_POST['artRegimen'] :  NULL),
          'date_of_initiation_of_current_regimen'=>$_POST['dateOfArtInitiation'],
          'patient_mobile_number'=>(isset($_POST['patientPhoneNumber']) && $_POST['patientPhoneNumber']!='' ? $_POST['patientPhoneNumber'] :  NULL),
          'last_viral_load_date'=>$_POST['lastViralLoadTestDate'],
          'last_viral_load_result'=>(isset($_POST['lastViralLoadResult']) && $_POST['lastViralLoadResult']!='' ? $_POST['lastViralLoadResult'] :  NULL),
          'reason_for_vl_testing'=>(isset($_POST['vlTestReason']) && $_POST['vlTestReason']!='' ? $_POST['vlTestReason'] :  NULL),
          'sample_type'=>(isset($_POST['specimenType']) && $_POST['specimenType']!='' ? $_POST['specimenType'] :  NULL),
          'sample_tested_datetime'=>$_POST['sampleTestingDateAtLab'],
          'result_value_absolute'=>(isset($_POST['vlResult']) && $_POST['vlResult']!='' ? $_POST['vlResult'] :  NULL),
          'result'=>(isset($_POST['result']) && $_POST['result']!='' ? $_POST['result'] :  NULL),
          'approver_comments'=>(isset($_POST['labComments']) && trim($_POST['labComments'])!='' ? trim($_POST['labComments']) :  NULL),
          'sample_received_at_vl_lab_datetime'=>$_POST['sampleReceivedDate'],
          'vl_test_platform'=>$testingPlatform,
          'is_sample_rejected'=>(isset($_POST['noResult']) && $_POST['noResult']!='' ? $_POST['noResult'] :  NULL),
          'reason_for_sample_rejection'=>(isset($_POST['rejectionReason']) && $_POST['rejectionReason']!='' ? $_POST['rejectionReason'] :  NULL),
          'test_methods'=>(isset($_POST['testMethods']) && $_POST['testMethods']!='' ? $_POST['testMethods'] :  NULL),
          'lab_id'=>(isset($_POST['labId']) && $_POST['labId']!='' ? $_POST['labId'] :  NULL),
          'number_of_enhanced_sessions'=>(isset($_POST['enhanceSession']) && $_POST['enhanceSession']!='' ? $_POST['enhanceSession'] :  NULL),
          'is_adherance_poor'=>(isset($_POST['poorAdherence']) && $_POST['poorAdherence']!='' ? $_POST['poorAdherence'] :  NULL),
          'result_approved_by'=>(isset($_POST['approvedBy']) && $_POST['approvedBy']!='' ? $_POST['approvedBy'] :  NULL),
          'last_modified_datetime'=>$general->getDateTime(),
          'manual_result_entry'=>'yes'
        );
     //print_r($vldata);die;
     $db=$db->where('vl_sample_id',$_POST['vlSampleId']);
     $id = $db->update($tableName,$vldata);
     if($id>0){
          $_SESSION['alertMsg']="VL request updated successfully";
          //Add event log
          $eventType = 'update-vl-request-zam';
          $action = ucwords($_SESSION['userName']).' updated a request data with the sample code '.$_POST['sampleCode'];
          $resource = 'vl-request-zam';
          $data=array(
          'event_type'=>$eventType,
          'action'=>$action,
          'resource'=>$resource,
          'date_time'=>$general->getDateTime()
          );
          $db->insert($tableName1,$data);
          header("location:vlRequest.php");
     }else{
          header("location:vlRequest.php");
          $_SESSION['alertMsg']="Please try again later";
     }
          
} catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}