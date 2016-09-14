<?php
session_start();
ob_start();
include('./includes/MysqliDb.php');
include('General.php');
$general=new Deforay_Commons_General();
$tableName="vl_request_form";
$tableName1="activity_log";
try {
     $configQuery ="SELECT value FROM global_config where name='auto_approval'";
     $configResult = $db->rawQuery($configQuery);
     $status = 6;
     if(isset($configResult[0]['value']) && trim($configResult[0]['value']) == 'yes'){
          $status = 7;
     }
     //var_dump($_POST);die;
     if(isset($_POST['sampleCollectionDate']) && trim($_POST['sampleCollectionDate'])!=""){
          $sampleDate = explode(" ",$_POST['sampleCollectionDate']);
          $_POST['sampleCollectionDate']=$general->dateFormat($sampleDate[0])." ".$sampleDate[1];
     }
     if(isset($_POST['dob']) && trim($_POST['dob'])!=""){
          $_POST['dob']=$general->dateFormat($_POST['dob']);  
     }
     
     if(isset($_POST['dateOfArtInitiation']) && trim($_POST['dateOfArtInitiation'])!=""){
          $_POST['dateOfArtInitiation']=$general->dateFormat($_POST['dateOfArtInitiation']);  
     }
     
     if(isset($_POST['lastViralLoadTestDate']) && trim($_POST['lastViralLoadTestDate'])!=""){
          $_POST['lastViralLoadTestDate']=$general->dateFormat($_POST['lastViralLoadTestDate']);  
     }
     if(isset($_POST['sampleTestingDateAtLab']) && trim($_POST['sampleTestingDateAtLab'])!=""){
          $_POST['sampleTestingDateAtLab']=$general->dateFormat($_POST['sampleTestingDateAtLab']);  
     }
     if(isset($_POST['dateOfReceivedStamp']) && trim($_POST['dateOfReceivedStamp'])!=""){
          $_POST['dateOfReceivedStamp']=$general->dateFormat($_POST['dateOfReceivedStamp']);  
     }
    
     if(isset($_POST['newArtRegimen']) && trim($_POST['newArtRegimen'])!=""){
          $data=array(
            'art_code'=>$_POST['newArtRegimen'],
            'nation_identifier'=>'zmb'
          );
          
          $result=$db->insert('r_art_code_details',$data);
          $_POST['artRegimen'] = $_POST['newArtRegimen'];
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
          'urgency'=>$_POST['urgency'],
          'sample_code_format'=>$_POST['sampleCodeFormat'],
          'sample_code_key'=>$_POST['sampleCodeKey'],
          'form_id'=>'2',
          'serial_no'=>$_POST['serialNo'],
          'facility_id'=>$_POST['clinicName'],
          'sample_code'=>$_POST['sampleCode'],
          'lab_contact_person'=>$_POST['clinicianName'],
          'sample_collection_date'=>$_POST['sampleCollectionDate'],
          'collected_by'=>$_POST['collectedBy'],
          'patient_name'=>$_POST['patientFname'],
          'surname'=>$_POST['surName'],
          'gender'=>$_POST['gender'],
          'patient_dob'=>$_POST['dob'],
          'age_in_yrs'=>$_POST['ageInYears'],
          'age_in_mnts'=>$_POST['ageInMonths'],
          'is_patient_pregnant'=>$_POST['patientPregnant'],
          'is_patient_breastfeeding'=>$_POST['breastfeeding'],
          'art_no'=>$_POST['patientArtNo'],
          'current_regimen'=>$_POST['artRegimen'],
          'date_of_initiation_of_current_regimen'=>$_POST['dateOfArtInitiation'],
          'patient_receive_sms'=>$_POST['receiveSms'],
          'patient_phone_number'=>$_POST['patientPhoneNumber'],
          'last_viral_load_date'=>$_POST['lastViralLoadTestDate'],
          'last_viral_load_result'=>$_POST['lastViralLoadResult'],
          'viral_load_log'=>$_POST['viralLoadLog'],
          'vl_test_reason'=>$_POST['vlTestReason'],
          'drug_substitution'=>$_POST['drugSubstitution'],
          'lab_no'=>$_POST['labNo'],
          'lab_id'=>$_POST['labId'],
          'vl_test_platform'=>$_POST['testingPlatform'],
          'sample_id'=>$_POST['specimenType'],
          'sample_testing_date'=>$_POST['sampleTestingDateAtLab'],
          'result'=>$_POST['vlResult'],
          'log_value'=>$_POST['vlLog'],
          'comments'=>$_POST['labCommnets'],
          'date_sample_received_at_testing_lab'=>$_POST['dateOfReceivedStamp'],
          'rejection'=>$_POST['noResult'],
          'result_reviewed_by'=>$_SESSION['userId'],
          'status'=>$status,
          'created_by'=>$_SESSION['userId'],
          'created_on'=>$general->getDateTime(),
          'modified_on'=>$general->getDateTime()
        );
          $id=$db->insert($tableName,$vldata);
          $_SESSION['alertMsg']="VL request added successfully";
          //Add event log
          $eventType = 'add-vl-request-zm';
          $action = ucwords($_SESSION['userName']).' have been added a new request data with the sample code '.$_POST['sampleCode'];
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
          header("location:addVlRequestZm.php");
    }else{
          $_SESSION['treamentId'] = '';
          $_SESSION['facilityId'] = '';
          unset($_SESSION['treamentId']);
          unset($_SESSION['facilityId']);
          header("location:vlRequest.php");
    }
    
  
} catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}