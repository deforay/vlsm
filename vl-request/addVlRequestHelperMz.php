<?php
session_start();
ob_start();
include('../includes/MysqliDb.php');
include('../General.php');
$general=new Deforay_Commons_General();
$tableName="vl_request_form";
$tableName1="activity_log";
try {
     $configQuery ="SELECT value FROM global_config where name='auto_approval'";
     $configResult = $db->rawQuery($configQuery);
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
     if(isset($_POST['dateOfProcessing']) && trim($_POST['dateOfProcessing'])!=""){
          $sampleTestingDateLab = explode(" ",$_POST['dateOfProcessing']);
          $_POST['dateOfProcessing']=$general->dateFormat($sampleTestingDateLab[0])." ".$sampleTestingDateLab[1];  
     }else{
        $_POST['dateOfProcessing'] = NULL;
     }
    
     if(isset($_POST['newArtRegimen']) && trim($_POST['newArtRegimen'])!=""){
          $data=array(
            'art_code'=>$_POST['newArtRegimen'],
            'nation_identifier'=>'mz',
            'parent_art'=>'4'
          );
          $result=$db->insert('r_art_code_details',$data);
          $_POST['artRegimen'] = $_POST['newArtRegimen'];
     }
     
     if(isset($_POST['gender']) && trim($_POST['gender'])=='male'){
          $_POST['patientPregnant']='';
          $_POST['breastfeeding']='';
     }
     $_POST['result'] = '';
     if($_POST['vlResult']!=''){
          $_POST['result'] = $_POST['vlResult'];
     }else if($_POST['vlLog']!=''){
          $_POST['result'] = $_POST['vlLog'];
     }
     $instanceId = '';
     if(isset($_SESSION['instanceId'])){
          $instanceId = $_SESSION['instanceId'];
     }
    
     $vldata=array(
          'vl_instance_id'=>$instanceId,
          'form_id'=>'5',
          'serial_no'=>(isset($_POST['orderNo']) && $_POST['orderNo']!='' ? $_POST['orderNo'] :  NULL) ,
          'sample_code'=>(isset($_POST['labNumber']) && $_POST['labNumber']!='' ? $_POST['labNumber'] :  NULL),
          'facility_id'=>(isset($_POST['clinicName']) && $_POST['clinicName']!='' ? $_POST['clinicName'] :  NULL),
          'lab_contact_person'=>(isset($_POST['technologistName']) && $_POST['technologistName']!='' ? $_POST['technologistName'] :  NULL),
          'sample_collection_date'=>$_POST['sampleCollectionDate'],
          'sample_collected_by'=>(isset($_POST['sampleReceivedBy']) && $_POST['sampleReceivedBy']!='' ? $_POST['sampleReceivedBy'] :  NULL),
          'patient_first_name'=>(isset($_POST['patientFname']) && $_POST['patientFname']!='' ? $_POST['patientFname'] :  NULL),
          'patient_last_name'=>(isset($_POST['surName']) && $_POST['surName']!='' ? $_POST['surName'] :  NULL),
          'patient_gender'=>(isset($_POST['gender']) && $_POST['gender']!='' ? $_POST['gender'] :  NULL),
          'patient_dob'=>$_POST['dob'],
          'patient_age_in_years'=>(isset($_POST['ageInYears']) && $_POST['ageInYears']!='' ? $_POST['ageInYears'] :  NULL),
          'patient_below_five_years'=>(isset($_POST['lessThanFiveYears']) && $_POST['lessThanFiveYears']!='' ? $_POST['lessThanFiveYears'] :  NULL),
          'is_patient_pregnant'=>(isset($_POST['patientPregnant']) && $_POST['patientPregnant']!='' ? $_POST['patientPregnant'] :  NULL),
          'is_patient_breastfeeding'=>(isset($_POST['breastfeeding']) && $_POST['breastfeeding']!='' ? $_POST['breastfeeding'] :  NULL),
          'patient_art_no'=>(isset($_POST['patientNo']) && $_POST['patientNo']!='' ? $_POST['patientNo'] :  NULL),
          'current_regimen'=>(isset($_POST['artRegimen']) && $_POST['artRegimen']!='' ? $_POST['artRegimen'] :  NULL),
          'date_of_initiation_of_current_regimen'=>$_POST['dateOfArtInitiation'],
          'consent_to_receive_sms'=>(isset($_POST['receiveSms']) && $_POST['receiveSms']!='' ? $_POST['receiveSms'] :  NULL),
          'patient_mobile_number'=>(isset($_POST['patientPhoneNumber']) && $_POST['patientPhoneNumber']!='' ? $_POST['patientPhoneNumber'] :  NULL),
          'last_viral_load_date'=>$_POST['lastViralLoadTestDate'],
          'last_viral_load_result'=>(isset($_POST['lastViralLoadResult']) && $_POST['lastViralLoadResult']!='' ? $_POST['lastViralLoadResult'] :  NULL),
          'last_vl_result_in_log'=>(isset($_POST['viralLoadLog']) && $_POST['viralLoadLog']!='' ? $_POST['viralLoadLog'] :  NULL),
          'reason_for_vl_testing'=>(isset($_POST['vlTestReason']) && $_POST['vlTestReason']!='' ? $_POST['vlTestReason'] :  NULL),
          'sample_type'=>(isset($_POST['sampleType']) && $_POST['sampleType']!='' ? $_POST['sampleType'] :  NULL),
          'lab_tested_date'=>$_POST['dateOfProcessing'],
          'reason_for_sample_rejection'=>(isset($_POST['rejectionReason']) && $_POST['rejectionReason']!='' ? $_POST['rejectionReason'] :  NULL),
          'absolute_value'=>(isset($_POST['vlResult']) && $_POST['vlResult']!='' ? $_POST['vlResult'] :  NULL),
          'result'=>(isset($_POST['result']) && $_POST['result']!='' ? $_POST['result'] :  NULL),
          'log_value'=>(isset($_POST['vlLog']) && $_POST['vlLog']!='' ? $_POST['vlLog'] :  NULL),
          'comments'=>(isset($_POST['labComments']) && $_POST['labComments']!='' ? $_POST['labComments'] :  NULL),
          'date_sample_received_at_testing_lab'=>$_POST['sampleReceivedDate'],
          //'result_reviewed_by'=>$_POST['reviewedBy'],
          'result_approved_by'=>(isset($_POST['approvedBy']) && $_POST['approvedBy']!='' ? $_POST['approvedBy'] :  NULL),
          'result_status'=>$status,
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
          $eventType = 'add-vl-request-zm';
          $action = ucwords($_SESSION['userName']).' added a new request data with the sample code '.$_POST['orderNo'];
          $resource = 'vl-request-zm';
          $data=array(
          'event_type'=>$eventType,
          'action'=>$action,
          'resource'=>$resource,
          'date_time'=>$general->getDateTime()
          );
          $db->insert($tableName1,$data);
          if(isset($_POST['saveNext']) && $_POST['saveNext']=='next'){
                $_SESSION['treamentId'] = $id;
                $_SESSION['facilityId'] = $_POST['clinicName'];
                header("location:addVlRequest.php");
          }else{
                $_SESSION['treamentId'] = '';
                $_SESSION['facilityId'] = '';
                unset($_SESSION['treamentId']);
                unset($_SESSION['facilityId']);
                header("location:vlRequest.php");
          }
          }else{
               $_SESSION['alertMsg']="Please try again later";
          }
  
} catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}