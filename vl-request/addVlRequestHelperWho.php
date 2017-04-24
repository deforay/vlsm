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
     //if($_POST['fCode']!=''){
          $fData = array('facility_code'=>$_POST['fCode']);
          $db=$db->where('facility_id',$_POST['fName']);
          $id=$db->update($fDetails,$fData);
     //}
     
     if(isset($_POST['gender']) && trim($_POST['gender'])=='male'){
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
    if(isset($_POST['sampleTestingDateAtLab']) && trim($_POST['sampleTestingDateAtLab'])!=""){
          $sampleTestingDateLab = explode(" ",$_POST['sampleTestingDateAtLab']);
          $_POST['sampleTestingDateAtLab']=$general->dateFormat($sampleTestingDateLab[0])." ".$sampleTestingDateLab[1];  
    }else{
        $_POST['sampleTestingDateAtLab'] = NULL;
    }
    $_POST['result'] = '';
    if(isset($_POST['vlResult']) && trim($_POST['vlResult']) != ''){
          $_POST['result'] = $_POST['vlResult'];
     }
     $vldata=array(
          'vlsm_instance_id'=>$instanceId,
          'vlsm_country_id'=>'6',
          'serial_no'=>(isset($_POST['sampleCode']) && $_POST['sampleCode']!='' ? $_POST['sampleCode'] :  NULL) ,
          'sample_code'=>(isset($_POST['sampleCode']) && $_POST['sampleCode']!='' ? $_POST['sampleCode'] :  NULL),
          'patient_other_id'=>(isset($_POST['uniqueId']) && $_POST['uniqueId']!='' ? $_POST['uniqueId'] :  NULL),
          'sample_code_format'=>(isset($_POST['sampleCodeFormat']) && $_POST['sampleCodeFormat']!='' ? $_POST['sampleCodeFormat'] :  NULL),
          'sample_code_key'=>(isset($_POST['sampleCodeKey']) && $_POST['sampleCodeKey']!='' ? $_POST['sampleCodeKey'] :  NULL),
          'facility_id'=>(isset($_POST['fName']) && $_POST['fName']!='' ? $_POST['fName'] :  NULL),
          'sample_collection_date'=>$_POST['sampleCollectionDate'],
          'patient_first_name'=>(isset($_POST['patientFirstName']) && $_POST['patientFirstName']!='' ? $_POST['patientFirstName'] :  NULL),
          'patient_middle_name'=>(isset($_POST['patientMiddleName']) && $_POST['patientMiddleName']!='' ? $_POST['patientMiddleName'] :  NULL),
          'patient_last_name'=>(isset($_POST['patientLastName']) && $_POST['patientLastName']!='' ? $_POST['patientLastName'] :  NULL),
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
          'result'=>(isset($_POST['result']) && $_POST['result']!='' ? $_POST['result'] :  NULL),
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
          'reason_for_sample_rejection'=>(isset($_POST['rejectionReason']) && $_POST['rejectionReason']!='') ? $_POST['rejectionReason'] :  NULL,
          'test_requested_on'=>(isset($_POST['requestDate']) && $_POST['requestDate']!='' ? $general->dateFormat($_POST['requestDate']) :  NULL),
          'vl_test_platform'=>$testingPlatform,
          'test_methods'=>(isset($_POST['testMethods']) && $_POST['testMethods']!='') ? $_POST['testMethods'] :  NULL,
          'sample_tested_datetime'=>$_POST['sampleTestingDateAtLab'],
          'result_value_absolute'=>(isset($_POST['vlResult']) && $_POST['vlResult']!='') ? $_POST['vlResult'] :  NULL,
          'result'=>(isset($_POST['result']) && $_POST['result']!='') ? $_POST['result'] :  NULL,
          'lab_id'=>(isset($_POST['labId']) && $_POST['labId']!='') ? $_POST['labId'] :  NULL,
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
               $eventType = 'add-vl-request-who';
               $action = ucwords($_SESSION['userName']).' added a new request data with the sample code '.$_POST['uniqueId'];
               $resource = 'vl-request-who';
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