<?php
session_start();
ob_start();
include('../includes/MysqliDb.php');
include('../General.php');
$general=new Deforay_Commons_General();
$tableName="vl_request_form";
$tableName1="activity_log";
$vlTestReasonTable="r_vl_test_reasons";
try {
     $status = 6;
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
     if($_POST['vlResult']!=''){
          $_POST['result'] = $_POST['vlResult'];
     }
     $instanceId = '';
    if(isset($_SESSION['instanceId'])){
          $instanceId = $_SESSION['instanceId'];
    }
    if($_POST['testingPlatform']!=''){
        $platForm = explode("##",$_POST['testingPlatform']);
        $_POST['testingPlatform'] = $platForm[0];
    }
    
     $vldata=array(
          'vl_instance_id'=>$instanceId,
          'form_id'=>'4',
          'serial_no'=>(isset($_POST['sampleCode']) && $_POST['sampleCode']!='' ? $_POST['sampleCode'] :  NULL) ,
          'sample_code'=>(isset($_POST['sampleCode']) && $_POST['sampleCode']!='' ? $_POST['sampleCode'] :  NULL),
          'facility_id'=>(isset($_POST['clinicName']) && $_POST['clinicName']!='' ? $_POST['clinicName'] :  NULL),
          'lab_contact_person'=>(isset($_POST['clinicianName']) && $_POST['clinicianName']!='' ? $_POST['clinicianName'] :  NULL),
          'sample_collection_date'=>$_POST['sampleCollectionDate'],
          'patient_name'=>(isset($_POST['patientFname']) && $_POST['patientFname']!='' ? $_POST['patientFname'] :  NULL),
          'surname'=>(isset($_POST['surName']) && $_POST['surName']!='' ? $_POST['surName'] :  NULL),
          'gender'=>(isset($_POST['gender']) && $_POST['gender']!='' ? $_POST['gender'] :  NULL),
          'patient_dob'=>$_POST['dob'],
          'age_in_yrs'=>(isset($_POST['ageInYears']) && $_POST['ageInYears']!='' ? $_POST['ageInYears'] :  NULL),
          'age_in_mnts'=>(isset($_POST['ageInMonths']) && $_POST['ageInMonths']!='' ? $_POST['ageInMonths'] :  NULL),
          'is_patient_pregnant'=>(isset($_POST['patientPregnant']) && $_POST['patientPregnant']!='' ? $_POST['patientPregnant'] :  NULL),
          'is_patient_breastfeeding'=>(isset($_POST['breastfeeding']) && $_POST['breastfeeding']!='' ? $_POST['breastfeeding'] :  NULL),
          'art_no'=>(isset($_POST['patientArtNo']) && $_POST['patientArtNo']!='' ? $_POST['patientArtNo'] :  NULL),
          'current_regimen'=>(isset($_POST['artRegimen']) && $_POST['artRegimen']!='' ? $_POST['artRegimen'] :  NULL),
          'date_of_initiation_of_current_regimen'=>$_POST['dateOfArtInitiation'],
          'patient_phone_number'=>(isset($_POST['patientPhoneNumber']) && $_POST['patientPhoneNumber']!='' ? $_POST['patientPhoneNumber'] :  NULL),
          'last_viral_load_date'=>$_POST['lastViralLoadTestDate'],
          'last_viral_load_result'=>(isset($_POST['lastViralLoadResult']) && $_POST['lastViralLoadResult']!='' ? $_POST['lastViralLoadResult'] :  NULL),
          'vl_test_reason'=>(isset($_POST['vlTestReason']) && $_POST['vlTestReason']!='' ? $_POST['vlTestReason'] :  NULL),
          'sample_id'=>(isset($_POST['specimenType']) && $_POST['specimenType']!='' ? $_POST['specimenType'] :  NULL),
          'lab_tested_date'=>$_POST['sampleTestingDateAtLab'],
          'absolute_value'=>(isset($_POST['vlResult']) && $_POST['vlResult']!='' ? $_POST['vlResult'] :  NULL),
          'result'=>(isset($_POST['result']) && $_POST['result']!='' ? $_POST['result'] :  NULL),
          'comments'=>(isset($_POST['labComments']) && trim($_POST['labComments'])!='' ? trim($_POST['labComments']) :  NULL),
          'date_sample_received_at_testing_lab'=>$_POST['sampleReceivedDate'],
          'vl_test_platform'=>$_POST['testingPlatform'],
          'rejection'=>(isset($_POST['noResult']) && $_POST['noResult']!='' ? $_POST['noResult'] :  NULL),
          'test_methods'=>(isset($_POST['testMethods']) && $_POST['testMethods']!='' ? $_POST['testMethods'] :  NULL),
          'enhance_session'=>(isset($_POST['enhanceSession']) && $_POST['enhanceSession']!='' ? $_POST['enhanceSession'] :  NULL),
          'poor_adherence'=>(isset($_POST['poorAdherence']) && $_POST['poorAdherence']!='' ? $_POST['poorAdherence'] :  NULL),
          'result_approved_by'=>(isset($_POST['approvedBy']) && $_POST['approvedBy']!='' ? $_POST['approvedBy'] :  NULL),
          'status'=>$status,
          'created_by'=>$_SESSION['userId'],
          'created_on'=>$general->getDateTime(),
          'modified_by'=>$_SESSION['userId'],
          'modified_on'=>$general->getDateTime(),
          'result_coming_from'=>'manual'
        );
     //print_r($vldata);die;
          $id=$db->insert($tableName,$vldata);
          if($id>0){
          $_SESSION['alertMsg']="VL request added successfully";
          //Add event log
          $eventType = 'add-vl-request-zam';
          $action = ucwords($_SESSION['userName']).' added a new request data with the sample code '.$_POST['sampleCode'];
          $resource = 'vl-request-zam';
          $data=array(
          'event_type'=>$eventType,
          'action'=>$action,
          'resource'=>$resource,
          'date_time'=>$general->getDateTime()
          );
          $db->insert($tableName1,$data);
          if(isset($_POST['saveNext']) && $_POST['saveNext']=='next'){
                header("location:addVlRequest.php");
          }else{
                header("location:vlRequest.php");
          }
          }else{
               $_SESSION['alertMsg']="Please try again later";
          }
  
} catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}