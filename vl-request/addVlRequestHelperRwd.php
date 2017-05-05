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
    
    if(isset($_POST['regimenInitiatedOn']) && trim($_POST['regimenInitiatedOn'])!=""){
       $_POST['regimenInitiatedOn']=$general->dateFormat($_POST['regimenInitiatedOn']);  
    }else{
       $_POST['regimenInitiatedOn'] = NULL;
    }
    
    if(isset($_POST['newArtRegimen']) && trim($_POST['newArtRegimen'])!=""){
         $data=array(
           'art_code'=>$_POST['newArtRegimen'],
           'nation_identifier'=>'rwd',
           'parent_art'=>'7'
         );
         $result=$db->insert('r_art_code_details',$data);
         $_POST['artRegimen'] = $_POST['newArtRegimen'];
    }
    //update facility code
    if(trim($_POST['fCode'])!=''){
       $fData = array('facility_code'=>$_POST['fCode']);
       $db=$db->where('facility_id',$_POST['fName']);
       $id=$db->update($fDetails,$fData);
    }
    //update facility emails
    if(trim($_POST['emailHf'])!=''){
       $fData = array('facility_emails'=>$_POST['emailHf']);
       $db=$db->where('facility_id',$_POST['fName']);
       $id=$db->update($fDetails,$fData);
    }
    if(isset($_POST['gender']) && trim($_POST['gender'])=='male'){
       $_POST['patientPregnant']='';
       $_POST['breastfeeding']='';
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
    if(isset($_POST['sampleReceivedOn']) && trim($_POST['sampleReceivedOn'])!=""){
        $sampleReceivedDateLab = explode(" ",$_POST['sampleReceivedOn']);
        $_POST['sampleReceivedOn']=$general->dateFormat($sampleReceivedDateLab[0])." ".$sampleReceivedDateLab[1];  
    }else{
        $_POST['sampleReceivedOn'] = NULL;
    }
    if(isset($_POST['sampleTestingDateAtLab']) && trim($_POST['sampleTestingDateAtLab'])!=""){
        $sampleTestingDateAtLab = explode(" ",$_POST['sampleTestingDateAtLab']);
        $_POST['sampleTestingDateAtLab']=$general->dateFormat($sampleTestingDateAtLab[0])." ".$sampleTestingDateAtLab[1];  
    }else{
        $_POST['sampleTestingDateAtLab'] = NULL;
    }
    if(isset($_POST['resultDispatchedOn']) && trim($_POST['resultDispatchedOn'])!=""){
        $resultDispatchedOn = explode(" ",$_POST['resultDispatchedOn']);
        $_POST['resultDispatchedOn']=$general->dateFormat($resultDispatchedOn[0])." ".$resultDispatchedOn[1];  
    }else{
        $_POST['resultDispatchedOn'] = NULL;
    }
    $_POST['result'] = '';
    if(isset($_POST['vlResult']) && trim($_POST['vlResult']) != ''){
        $_POST['result'] = $_POST['vlResult'];
    }
    $vldata=array(
          'vlsm_instance_id'=>$instanceId,
          'vlsm_country_id'=>'7',
          'serial_no'=>(isset($_POST['sampleCode']) && $_POST['sampleCode']!='') ? $_POST['sampleCode'] :  NULL ,
          'sample_code'=>(isset($_POST['sampleCode']) && $_POST['sampleCode']!='') ? $_POST['sampleCode'] :  NULL,
          'sample_code_format'=>(isset($_POST['sampleCodeFormat']) && $_POST['sampleCodeFormat']!='') ? $_POST['sampleCodeFormat'] :  NULL,
          'sample_code_key'=>(isset($_POST['sampleCodeKey']) && $_POST['sampleCodeKey']!='') ? $_POST['sampleCodeKey'] :  NULL,
          'facility_id'=>(isset($_POST['fName']) && $_POST['fName']!='') ? $_POST['fName'] :  NULL,
          'sample_collection_date'=>$_POST['sampleCollectionDate'],
          'patient_first_name'=>(isset($_POST['patientFirstName']) && $_POST['patientFirstName']!='') ? $_POST['patientFirstName'] :  NULL,
          'patient_gender'=>(isset($_POST['gender']) && $_POST['gender']!='') ? $_POST['gender'] :  NULL,
          'patient_dob'=>$_POST['dob'],
          'patient_age_in_years'=>(isset($_POST['ageInYears']) && $_POST['ageInYears']!='') ? $_POST['ageInYears'] :  NULL,
          'patient_age_in_months'=>(isset($_POST['ageInMonths']) && $_POST['ageInMonths']!='') ? $_POST['ageInMonths'] :  NULL,
          'is_patient_pregnant'=>(isset($_POST['patientPregnant']) && $_POST['patientPregnant']!='') ? $_POST['patientPregnant'] :  NULL,
          'is_patient_breastfeeding'=>(isset($_POST['breastfeeding']) && $_POST['breastfeeding']!='') ? $_POST['breastfeeding'] :  NULL,
          'patient_art_no'=>(isset($_POST['artNo']) && $_POST['artNo']!='') ? $_POST['artNo'] :  NULL,
          'treatment_initiated_date'=>$_POST['dateOfArtInitiation'],
          'current_regimen'=>(isset($_POST['artRegimen']) && $_POST['artRegimen']!='') ? $_POST['artRegimen'] :  NULL,
          'date_of_initiation_of_current_regimen'=>$_POST['regimenInitiatedOn'],
          'patient_mobile_number'=>(isset($_POST['patientPhoneNumber']) && $_POST['patientPhoneNumber']!='') ? $_POST['patientPhoneNumber'] :  NULL,
          'sample_type'=>(isset($_POST['specimenType']) && $_POST['specimenType']!='') ? $_POST['specimenType'] :  NULL,
          'arv_adherance_percentage'=>(isset($_POST['arvAdherence']) && $_POST['arvAdherence']!='') ? $_POST['arvAdherence'] :  NULL,
          'reason_for_vl_testing'=>(isset($_POST['stViralTesting']))?$_POST['stViralTesting']:NULL,
          'last_vl_date_routine'=>(isset($_POST['rmTestingLastVLDate']) && $_POST['rmTestingLastVLDate']!='') ? $general->dateFormat($_POST['rmTestingLastVLDate']) :  NULL,
          'last_vl_result_routine'=>(isset($_POST['rmTestingVlValue']) && $_POST['rmTestingVlValue']!='') ? $_POST['rmTestingVlValue'] :  NULL,
          'last_vl_date_failure_ac'=>(isset($_POST['repeatTestingLastVLDate']) && $_POST['repeatTestingLastVLDate']!='') ? $general->dateFormat($_POST['repeatTestingLastVLDate']) :  NULL,
          'last_vl_result_failure_ac'=>(isset($_POST['repeatTestingVlValue']) && $_POST['repeatTestingVlValue']!='') ? $_POST['repeatTestingVlValue'] :  NULL,
          'last_vl_date_failure'=>(isset($_POST['suspendTreatmentLastVLDate']) && $_POST['suspendTreatmentLastVLDate']!='') ? $general->dateFormat($_POST['suspendTreatmentLastVLDate']) :  NULL,
          'last_vl_result_failure'=>(isset($_POST['suspendTreatmentVlValue']) && $_POST['suspendTreatmentVlValue']!='') ? $_POST['suspendTreatmentVlValue'] :  NULL,
          'request_clinician_name'=>(isset($_POST['reqClinician']) && $_POST['reqClinician']!='') ? $_POST['reqClinician'] :  NULL,
          'request_clinician_phone_number'=>(isset($_POST['reqClinicianPhoneNumber']) && $_POST['reqClinicianPhoneNumber']!='') ? $_POST['reqClinicianPhoneNumber'] :  NULL,
          'test_requested_on'=>(isset($_POST['requestDate']) && $_POST['requestDate']!='') ? $general->dateFormat($_POST['requestDate']) :  NULL,
          'vl_focal_person'=>(isset($_POST['vlFocalPerson']) && $_POST['vlFocalPerson']!='') ? $_POST['vlFocalPerson'] :  NULL,
          'vl_focal_person_phone_number'=>(isset($_POST['vlFocalPersonPhoneNumber']) && $_POST['vlFocalPersonPhoneNumber']!='') ? $_POST['vlFocalPersonPhoneNumber'] :  NULL,
          'lab_id'=>(isset($_POST['labId']) && $_POST['labId']!='') ? $_POST['labId'] :  NULL,
          'vl_test_platform'=>$testingPlatform,
          'test_methods'=>(isset($_POST['testMethods']) && $_POST['testMethods']!='') ? $_POST['testMethods'] :  NULL,
          'sample_received_at_vl_lab_datetime'=>$_POST['sampleReceivedOn'],
          'sample_tested_datetime'=>$_POST['sampleTestingDateAtLab'],
          'result_dispatched_datetime'=>$_POST['resultDispatchedOn'],
          'is_sample_rejected'=>(isset($_POST['noResult']) && $_POST['noResult']!='') ? $_POST['noResult'] :  NULL,
          'reason_for_sample_rejection'=>(isset($_POST['rejectionReason']) && $_POST['rejectionReason']!='') ? $_POST['rejectionReason'] :  NULL,
          'result_value_absolute'=>(isset($_POST['vlResult']) && $_POST['vlResult']!='') ? $_POST['vlResult'] :  NULL,
          'result'=>(isset($_POST['result']) && $_POST['result']!='') ? $_POST['result'] :  NULL,
          'result_approved_by'=>(isset($_POST['approvedBy']) && $_POST['approvedBy']!='') ? $_POST['approvedBy'] :  NULL,
          'approver_comments'=>(isset($_POST['labComments']) && trim($_POST['labComments'])!='') ? trim($_POST['labComments']) :  NULL,
          'result_status'=>$status,
          'request_created_by'=>$_SESSION['userId'],
          'request_created_datetime'=>$general->getDateTime(),
          'last_modified_by'=>$_SESSION['userId'],
          'last_modified_datetime'=>$general->getDateTime(),
          'manual_result_entry'=>'yes'
        );
      //echo "<pre>";var_dump($vldata);die;
        $id=$db->insert($tableName,$vldata);
        if($id>0){
             $_SESSION['alertMsg']="VL request added successfully";
             //Add event log
             $eventType = 'add-vl-request-rwd';
             $action = ucwords($_SESSION['userName']).' added a new request data with the sample code '.$_POST['sampleCode'];
             $resource = 'vl-request-rwd';
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