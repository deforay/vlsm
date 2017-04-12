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
     if(isset($configResult[0]['value']) && trim($configResult[0]['value']) == 'yes'){
          $status = 7;
     }
     //set lab no
     $start_date = date('Y-m-01');
     $end_date = date('Y-m-31');
     $labvlQuery='select MAX(lab_code) FROM vl_request_form as vl where vl.vlsm_country_id="2" AND DATE(vl.request_created_datetime) >= "'.$start_date.'" AND DATE(vl.request_created_datetime) <= "'.$end_date.'"';
     $labvlResult = $db->rawQuery($labvlQuery);
     if($labvlResult[0]['MAX(lab_code)']!='' && $labvlResult[0]['MAX(lab_code)']!=NULL){
     $_POST['labNo'] = $labvlResult[0]['MAX(lab_code)']+1;
    }else{
     $_POST['labNo'] = '1';
    }
     
     //var_dump($_POST);die;
     if(isset($_POST['sampleCollectionDate']) && trim($_POST['sampleCollectionDate'])!=""){
          $sampleDate = explode(" ",$_POST['sampleCollectionDate']);
          $_POST['sampleCollectionDate']=$general->dateFormat($sampleDate[0])." ".$sampleDate[1];
     }
     
     if(isset($_POST['sampleReceivedDate']) && trim($_POST['sampleReceivedDate'])!=""){
          $sampleReceivedDate = explode(" ",$_POST['sampleReceivedDate']);
          $_POST['sampleReceivedDate']=$general->dateFormat($sampleReceivedDate[0])." ".$sampleReceivedDate[1];
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
          $sampleTestingDateLab = explode(" ",$_POST['sampleTestingDateAtLab']);
          $_POST['sampleTestingDateAtLab']=$general->dateFormat($sampleTestingDateLab[0])." ".$sampleTestingDateLab[1];  
     }
    
     if(isset($_POST['newArtRegimen']) && trim($_POST['newArtRegimen'])!=""){
          $data=array(
            'art_code'=>$_POST['newArtRegimen'],
            'nation_identifier'=>'zmb'
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
     if(!isset($_POST['noResult'])){
          $_POST['noResult'] = '';
          $_POST['rejectionReason'] = '';
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
          'test_urgency'=>(isset($_POST['urgency']) && $_POST['urgency']!='' ? $_POST['urgency'] :  NULL),
          'vlsm_instance_id'=>$instanceId,
          'sample_code_format'=>(isset($_POST['sampleCodeFormat']) && $_POST['sampleCodeFormat']!='' ? $_POST['sampleCodeFormat'] :  NULL),
          'sample_code_key'=>(isset($_POST['sampleCodeKey']) && $_POST['sampleCodeKey']!='' ? $_POST['sampleCodeKey'] :  NULL),
          'vlsm_country_id'=>'2',
          'serial_no'=>(isset($_POST['serialNo']) && $_POST['serialNo']!='' ? $_POST['serialNo'] :  NULL),
          'sample_code'=>(isset($_POST['serialNo']) && $_POST['serialNo']!='' ? $_POST['serialNo'] :  NULL),
          'facility_id'=>(isset($_POST['clinicName']) && $_POST['clinicName']!='' ? $_POST['clinicName'] :  NULL),
          //'sample_code'=>$_POST['sampleCode'],
          'request_clinician_name'=>(isset($_POST['clinicianName']) && $_POST['clinicianName']!='' ? $_POST['clinicianName'] :  NULL),
          'sample_collection_date'=>(isset($_POST['sampleCollectionDate']) && $_POST['sampleCollectionDate']!='' ? $_POST['sampleCollectionDate'] :  NULL),
          'sample_collected_by'=>(isset($_POST['collectedBy']) && $_POST['collectedBy']!='' ? $_POST['collectedBy'] :  NULL),
          'patient_first_name'=>(isset($_POST['patientFname']) && $_POST['patientFname']!='' ? $_POST['patientFname'] :  NULL),
          'patient_last_name'=>(isset($_POST['surName']) && $_POST['surName']!='' ? $_POST['surName'] :  NULL),
          'patient_gender'=>(isset($_POST['gender']) && $_POST['gender']!='' ? $_POST['gender'] :  NULL),
          'patient_dob'=>(isset($_POST['dob']) && $_POST['dob']!='' ? $_POST['dob'] :  NULL),
          'patient_age_in_years'=>(isset($_POST['ageInYears']) && $_POST['ageInYears']!='' ? $_POST['ageInYears'] :  NULL),
          'patient_age_in_months'=>(isset($_POST['ageInMonths']) && $_POST['ageInMonths']!='' ? $_POST['ageInMonths'] :  NULL),
          'is_patient_pregnant'=>(isset($_POST['patientPregnant']) && $_POST['patientPregnant']!='' ? $_POST['patientPregnant'] :  NULL),
          'is_patient_breastfeeding'=>(isset($_POST['breastfeeding']) && $_POST['breastfeeding']!='' ? $_POST['breastfeeding'] :  NULL),
          'patient_art_no'=>(isset($_POST['patientArtNo']) && $_POST['patientArtNo']!='' ? $_POST['patientArtNo'] :  NULL),
          'current_regimen'=>(isset($_POST['artRegimen']) && $_POST['artRegimen']!='' ? $_POST['artRegimen'] :  NULL),
          'date_of_initiation_of_current_regimen'=>(isset($_POST['dateOfArtInitiation']) && $_POST['dateOfArtInitiation']!='' ? $_POST['dateOfArtInitiation'] :  NULL),
          'consent_to_receive_sms'=>(isset($_POST['receiveSms']) && $_POST['receiveSms']!='' ? $_POST['receiveSms'] :  NULL),
          'patient_mobile_number'=>(isset($_POST['patientPhoneNumber']) && $_POST['patientPhoneNumber']!='' ? $_POST['patientPhoneNumber'] :  NULL),
          'last_viral_load_date'=>(isset($_POST['lastViralLoadTestDate']) && $_POST['lastViralLoadTestDate']!='' ? $_POST['lastViralLoadTestDate'] :  NULL),
          'last_viral_load_result'=>(isset($_POST['lastViralLoadResult']) && $_POST['lastViralLoadResult']!='' ? $_POST['lastViralLoadResult'] :  NULL),
          'last_vl_result_in_log'=>(isset($_POST['viralLoadLog']) && $_POST['viralLoadLog']!='' ? $_POST['viralLoadLog'] :  NULL),
          'reason_for_vl_testing'=>(isset($_POST['vlTestReason']) && $_POST['vlTestReason']!='' ? $_POST['vlTestReason'] :  NULL),
          //'drug_substitution'=>$_POST['drugSubstitution'],
          'lab_code'=>(isset($_POST['labNo']) && $_POST['labNo']!='' ? $_POST['labNo'] :  NULL),
          'lab_id'=>(isset($_POST['labId']) && $_POST['labId']!='' ? $_POST['labId'] :  NULL),
          'vl_test_platform'=>(isset($_POST['testingPlatform']) && $_POST['testingPlatform']!='' ? $_POST['testingPlatform'] :  NULL),
          'sample_type'=>(isset($_POST['specimenType']) && $_POST['specimenType']!='' ? $_POST['specimenType'] :  NULL),
          'sample_tested_datetime'=>(isset($_POST['sampleTestingDateAtLab']) && $_POST['sampleTestingDateAtLab']!='' ? $_POST['sampleTestingDateAtLab'] :  NULL),
          'reason_for_sample_rejection'=>(isset($_POST['rejectionReason']) && $_POST['rejectionReason']!='' ? $_POST['rejectionReason'] :  NULL),
          'result_value_absolute'=>(isset($_POST['vlResult']) && $_POST['vlResult']!='' ? $_POST['vlResult'] :  NULL),
          'result'=>(isset($_POST['result']) && $_POST['result']!='' ? $_POST['result'] :  NULL),
          'result_value_log'=>(isset($_POST['vlLog']) && $_POST['vlLog']!='' ? $_POST['vlLog'] :  NULL),
          'approver_comments'=>(isset($_POST['labComments']) && $_POST['labComments']!='' ? $_POST['labComments'] :  NULL),
          'sample_received_at_vl_lab_datetime'=>(isset($_POST['sampleReceivedDate']) && $_POST['sampleReceivedDate']!='' ? $_POST['sampleReceivedDate'] :  NULL),
          'is_sample_rejected'=>(isset($_POST['noResult']) && $_POST['noResult']!='' ? $_POST['noResult'] :  NULL),
          'result_reviewed_by'=>(isset($_POST['reviewedBy']) && $_POST['reviewedBy']!='' ? $_POST['reviewedBy'] :  NULL),
          'result_approved_by'=>(isset($_POST['approvedBy']) && $_POST['approvedBy']!='' ? $_POST['approvedBy'] :  NULL),
          'result_status'=>$status,
          'request_created_by'=>$_SESSION['userId'],
          'request_created_datetime'=>$general->getDateTime(),
          'last_modified_by'=>$_SESSION['userId'],
          'last_modified_datetime'=>$general->getDateTime(),
          'manual_result_entry'=>'yes'
        );
         //print_r($vldata);die;
          $id=$db->insert($tableName,$vldata);
          //echo $id;die;
          if($id>0){
          $_SESSION['alertMsg']="VL request added successfully";
          //Add event log
          $eventType = 'add-vl-request-zm';
          $action = ucwords($_SESSION['userName']).' added a new request data with the sample code '.$_POST['serialNo'];
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