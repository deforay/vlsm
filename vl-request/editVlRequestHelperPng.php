<?php
session_start();
ob_start();
require_once('../startup.php');  include_once(APPLICATION_PATH.'/includes/MysqliDb.php');
include_once(APPLICATION_PATH.'/models/General.php');
$general=new General($db);
$tableName="vl_request_form";
$tableName1="activity_log";
$vlTestReasonTable="r_vl_test_reasons";
try {
     $validateField = array($_POST['sampleCode'],$_POST['collectionDate']);
     $chkValidation = $general->checkMandatoryFields($validateField);
     if($chkValidation){
          $_SESSION['alertMsg']="Please enter all mandatory fields to save the test request";
          header("location:editVlRequest.php?id=".base64_encode($_POST['vlSampleId']));
          die;
     }
     $configQuery="SELECT * from global_config";
     $configResult=$db->query($configQuery);
     $arr = array();
     // now we create an associative array so that we can easily create view variables
     for ($i = 0; $i < sizeof($configResult); $i++) {
          $arr[$configResult[$i]['name']] = $configResult[$i]['value'];
     }
     //system config
     $systemConfigQuery ="SELECT * from system_config";
     $systemConfigResult=$db->query($systemConfigQuery);
     $sarr = array();
     // now we create an associative array so that we can easily create view variables
     for ($i = 0; $i < sizeof($systemConfigResult); $i++) {
          $sarr[$systemConfigResult[$i]['name']] = $systemConfigResult[$i]['value'];
     }

     if(isset($_POST['dob']) && trim($_POST['dob'])!=""){
          $_POST['dob']=$general->dateFormat($_POST['dob']);
     }else{
          $_POST['dob'] = NULL;
     }

     if(isset($_POST['collectionDate']) && trim($_POST['collectionDate'])!=""){
          $sampleDate = explode(" ",$_POST['collectionDate']);
          $_POST['collectionDate']=$general->dateFormat($sampleDate[0])." ".$sampleDate[1];
     }else{
          $_POST['collectionDate'] = NULL;
     }
     if(isset($_POST['failedTestDate']) && trim($_POST['failedTestDate'])!=""){
          $failedtestDate = explode(" ",$_POST['failedTestDate']);
          $_POST['failedTestDate']=$general->dateFormat($failedtestDate[0])." ".$failedtestDate[1];
     }else{
          $_POST['failedTestDate'] = NULL;
     }

     if(isset($_POST['regStartDate']) && trim($_POST['regStartDate'])!=""){
          $_POST['regStartDate']=$general->dateFormat($_POST['regStartDate']);
     }else{
          $_POST['regStartDate'] = NULL;
     }

     if(isset($_POST['receivedDate']) && trim($_POST['receivedDate'])!=""){
          $sampleReceivedDate = explode(" ",$_POST['receivedDate']);
          $_POST['receivedDate']=$general->dateFormat($sampleReceivedDate[0])." ".$sampleReceivedDate[1];
     }else{
          $_POST['receivedDate'] = NULL;
     }
     if(isset($_POST['testDate']) && trim($_POST['testDate'])!=""){
          $sampletestDate = explode(" ",$_POST['testDate']);
          $_POST['testDate']=$general->dateFormat($sampletestDate[0])." ".$sampletestDate[1];
     }else{
          $_POST['testDate'] = NULL;
     }
     if(isset($_POST['cdDate']) && trim($_POST['cdDate'])!=""){
          $_POST['cdDate']=$general->dateFormat($_POST['cdDate']);
     }else{
          $_POST['cdDate'] = NULL;
     }
     if(isset($_POST['qcDate']) && trim($_POST['qcDate'])!=""){
          $_POST['qcDate']=$general->dateFormat($_POST['qcDate']);
     }else{
          $_POST['qcDate'] = NULL;
     }
     if(isset($_POST['reportDate']) && trim($_POST['reportDate'])!=""){
          $_POST['reportDate']=$general->dateFormat($_POST['reportDate']);
     }else{
          $_POST['reportDate'] = NULL;
     }
     if(isset($_POST['clinicDate']) && trim($_POST['clinicDate'])!=""){
          $_POST['clinicDate']=$general->dateFormat($_POST['clinicDate']);
     }else{
          $_POST['clinicDate'] = NULL;
     }

     if($_POST['testingTech']!=''){
          $platForm = explode("##",$_POST['testingTech']);
          $_POST['testingTech'] = $platForm[0];
     }
     if($_POST['failedTestingTech']!=''){
          $platForm = explode("##",$_POST['failedTestingTech']);
          $_POST['failedTestingTech'] = $platForm[0];
     }
     if(isset($_POST['newArtRegimen']) && trim($_POST['newArtRegimen'])!=""){
          $data=array(
               'art_code'=>$_POST['newArtRegimen'],
               'parent_art'=>'5',
               'nation_identifier'=>'png'
          );
          $result=$db->insert('r_art_code_details',$data);
          $_POST['currentRegimen'] = $_POST['newArtRegimen'];
     }
     if(isset($_POST['sampleQuality']) && trim($_POST['sampleQuality']) == 'no'){
          $_POST['rejectionReason'] = NULL;
     }
     if(isset($_POST['sampleQuality']) && trim($_POST['sampleQuality']) == 'yes'){
          $_POST['vlResult'] = NULL;
     }

     $reasonForTestField = NULL;
     if(isset($_POST['reasonForTest']) && $_POST['reasonForTest']!=''){

          $reasonQuery ="SELECT test_reason_id FROM r_vl_test_reasons where test_reason_name='".$_POST['reasonForTest']."'";
          $reasonResult = $db->rawQuery($reasonQuery);
          if(isset($reasonResult[0]['test_reason_id']) && $reasonResult[0]['test_reason_id']!=''){
               $reasonForTestField = $reasonResult[0]['test_reason_id'];
          }else{
               $data=array(
                    'test_reason_name'=>$_POST['reasonForTest'],
                    'test_reason_status'=>'active'
               );
               $id=$db->insert('r_vl_test_reasons',$data);
               $reasonForTestField = $id;
          }
     }

     $vldata=array(
          //'sample_code'=>(isset($_POST['sampleCode']) && $_POST['sampleCode']!='') ? $_POST['sampleCode'] :  NULL,
          //'serial_no'=>(isset($_POST['sampleCode']) && $_POST['sampleCode']!='') ? $_POST['sampleCode'] :  NULL,
          'facility_id'=>(isset($_POST['clinicName']) && trim($_POST['clinicName'])!='') ? $_POST['clinicName'] :  NULL,
          //'ward'=>(isset($_POST['wardData']) && $_POST['wardData']!='' ? $_POST['wardData'] :  NULL),
          'patient_art_no'=>(isset($_POST['patientARTNo']) && trim($_POST['patientARTNo'])!='') ? $_POST['patientARTNo'] :  NULL,
          'request_clinician_name'=>(isset($_POST['officerName']) && $_POST['officerName']!='' ? $_POST['officerName'] :  NULL),
          'lab_phone_number'=>(isset($_POST['telephone']) && $_POST['telephone']!='' ? $_POST['telephone'] :  NULL),
          //'patient_first_name'=>(isset($_POST['patientFname']) && $_POST['patientFname']!='' ? $_POST['patientFname'] :  NULL),
          //'patient_last_name'=>(isset($_POST['surName']) && $_POST['surName']!='' ? $_POST['surName'] :  NULL),
          'patient_gender'=>(isset($_POST['gender']) && $_POST['gender']!='' ? $_POST['gender'] :  NULL),
          'patient_dob'=>$_POST['dob'],
          'patient_age_in_years'=>(isset($_POST['ageInYears']) && $_POST['ageInYears']!='') ? $_POST['ageInYears'] :  NULL,
          'patient_age_in_months'=>(isset($_POST['ageInMonths']) && $_POST['ageInMonths']!='') ? $_POST['ageInMonths'] :  NULL,
          'line_of_treatment'=>(isset($_POST['artLine']) && $_POST['artLine']!='')? $_POST['artLine'] :  NULL,
          'current_regimen'=>(isset($_POST['currentRegimen']) && $_POST['currentRegimen']!='') ? $_POST['currentRegimen'] :  NULL,
          'date_of_initiation_of_current_regimen'=>$_POST['regStartDate'],
          'art_cd_cells'=>(isset($_POST['cdCells']) && $_POST['cdCells']!='' ? $_POST['cdCells'] :  NULL),
          'art_cd_date'=>$_POST['cdDate'],
          'who_clinical_stage'=>(isset($_POST['clinicalStage']) && $_POST['clinicalStage']!='' ? $_POST['clinicalStage'] :  NULL),
          //'reason_testing_png'=>(isset($_POST['reasonForTest']) && $_POST['reasonForTest']!='' ? $_POST['reasonForTest'] :  NULL),
          'reason_for_vl_testing'=>$reasonForTestField,
          'reason_for_vl_testing_other'=>(isset($_POST['reason']) && $_POST['reason']!='' ? $_POST['reason'] :  NULL),
          'sample_to_transport'=>(isset($_POST['typeOfSample']) && $_POST['typeOfSample']!='' ? $_POST['typeOfSample'] :  NULL),
          'whole_blood_ml'=>(isset($_POST['wholeBloodOne']) && $_POST['wholeBloodOne']!='' ? $_POST['wholeBloodOne'] :  NULL),
          'whole_blood_vial'=>(isset($_POST['wholeBloodTwo']) && $_POST['wholeBloodTwo']!='' ? $_POST['wholeBloodTwo'] :  NULL),
          'plasma_ml'=>(isset($_POST['plasmaOne']) && $_POST['plasmaOne']!='' ? $_POST['plasmaOne'] :  NULL),
          'plasma_vial'=>(isset($_POST['plasmaTwo']) && $_POST['plasmaTwo']!='' ? $_POST['plasmaTwo'] :  NULL),
          'plasma_process_time'=>(isset($_POST['processTime']) && $_POST['processTime']!='' ? $_POST['processTime'] :  NULL),
          'plasma_process_tech'=>(isset($_POST['processTech']) && $_POST['processTech']!='' ? $_POST['processTech'] :  NULL),
          'is_sample_rejected'=>(isset($_POST['sampleQuality']) && $_POST['sampleQuality']!='' ? $_POST['sampleQuality'] :  NULL),
          'reason_for_sample_rejection'=>(isset($_POST['rejectionReason']) && $_POST['rejectionReason']!='' ? $_POST['rejectionReason'] :  NULL),
          'batch_quality'=>(isset($_POST['batchQuality']) && $_POST['batchQuality']!='' ? $_POST['batchQuality'] :  NULL),
          'sample_test_quality'=>(isset($_POST['testQuality']) && $_POST['testQuality']!='' ? $_POST['testQuality'] :  NULL),
          'sample_batch_id'=>(isset($_POST['batchNo']) && $_POST['batchNo']!='' ? $_POST['batchNo'] :  NULL),
          'failed_test_date'=>$_POST['failedTestDate'],
          'failed_test_tech'=>(isset($_POST['failedTestingTech']) && $_POST['failedTestingTech']!='') ? $_POST['failedTestingTech'] :  NULL,
          'failed_vl_result'=>(isset($_POST['failedvlResult']) && $_POST['failedvlResult']!='' ? $_POST['failedvlResult'] :  NULL),
          'failed_batch_quality'=>(isset($_POST['failedbatchQuality']) && $_POST['failedbatchQuality']!='' ? $_POST['failedbatchQuality'] :  NULL),
          'failed_sample_test_quality'=>(isset($_POST['failedtestQuality']) && $_POST['failedtestQuality']!='' ? $_POST['failedtestQuality'] :  NULL),
          'failed_batch_id'=>(isset($_POST['failedbatchNo']) && $_POST['failedbatchNo']!='' ? $_POST['failedbatchNo'] :  NULL),
          'sample_collection_date'=>$_POST['collectionDate'],
          'sample_collected_by'=>(isset($_POST['collectedBy']) && $_POST['collectedBy']!='' ? $_POST['collectedBy'] :  NULL),
          'lab_id'=>(isset($_POST['laboratoryId']) && $_POST['laboratoryId']!='' ? $_POST['laboratoryId'] :  NULL),
          'sample_type'=>(isset($_POST['sampleType']) && $_POST['sampleType']!='' ? $_POST['sampleType'] :  NULL),
          'sample_received_at_vl_lab_datetime'=>$_POST['receivedDate'],
          'tech_name_png'=>(isset($_POST['techName']) && trim($_POST['techName'])!='')? $_POST['techName'] :  NULL,
          'sample_tested_datetime'=>(isset($_POST['testDate']) && $_POST['testDate']!='' ? $_POST['testDate'] :  NULL),
          //'last_viral_load_result'=>(isset($_POST['vlResult']) && $_POST['vlResult']!='' ? $_POST['vlResult'] :  NULL),
          'vl_test_platform'=>(isset($_POST['testingTech']) && trim($_POST['testingTech'])!='') ? $_POST['testingTech'] :  NULL,
          'cphl_vl_result'=>(isset($_POST['cphlvlResult']) && $_POST['cphlvlResult']!='' ? $_POST['cphlvlResult'] :  NULL),
          'result'=>(isset($_POST['finalViralResult']) && trim($_POST['finalViralResult'])!='') ? $_POST['finalViralResult'] :  NULL,
          'qc_tech_name'=>(isset($_POST['qcTechName']) && $_POST['qcTechName']!='' ? $_POST['qcTechName'] :  NULL),
          'qc_tech_sign'=>(isset($_POST['qcTechSign']) && $_POST['qcTechSign']!='' ? $_POST['qcTechSign'] :  NULL),
          'qc_date'=>$_POST['qcDate'],
          'clinic_date'=>$_POST['clinicDate'],
          'report_date'=>$_POST['reportDate'],
          'last_modified_datetime'=>$general->getDateTime(),
     );
     if($sarr['user_type']=='remoteuser'){
          $vldata['remote_sample_code'] = (isset($_POST['sampleCode']) && $_POST['sampleCode']!='') ? $_POST['sampleCode'] :  NULL;
     }else {
          if($_POST['sampleCodeCol']!=''){
               $vldata['sample_code'] = (isset($_POST['sampleCodeCol']) && $_POST['sampleCodeCol']!='') ? $_POST['sampleCodeCol'] :  NULL;
               $vldata['serial_no'] = (isset($_POST['sampleCodeCol']) && $_POST['sampleCodeCol']!='') ? $_POST['sampleCodeCol'] :  NULL;
          }else{
               //update sample code generation
               $sExpDT = explode(" ",$_POST['sampleCollectionDate']);
               $sExpDate = explode("-",$sExpDT[0]);
               $start_date = date($sExpDate[0].'-01-01')." ".'00:00:00';
               $end_date = date($sExpDate[0].'-12-31')." ".'23:59:59';
               $mnthYr = substr($sExpDate[0],-2);
               if($arr['sample_code']=='MMYY'){
                    $mnthYr = $sExpDate[1].substr($sExpDate[0],-2);
               }else if($arr['sample_code']=='YY'){
                    $mnthYr = substr($sExpDate[0],-2);
               }
               $auto = substr($sExpDate[0],-2).$sExpDate[1].$sExpDate[2];

               $svlQuery='SELECT sample_code_key FROM vl_request_form as vl WHERE DATE(vl.sample_collection_date) >= "'.$start_date.'" AND DATE(vl.sample_collection_date) <= "'.$end_date.'" AND sample_code!="" ORDER BY sample_code_key DESC LIMIT 1';
               $svlResult=$db->query($svlQuery);
               $prefix = $arr['sample_code_prefix'];
               if(isset($svlResult[0]['sample_code_key']) && $svlResult[0]['sample_code_key']!='' && $svlResult[0]['sample_code_key']!=NULL){
                    $maxId = $svlResult[0]['sample_code_key']+1;
                    $strparam = strlen($maxId);
                    $zeros = substr("000", $strparam);
                    $maxId = $zeros.$maxId;
               }else{
                    $maxId = '001';
               }
               if($arr['sample_code']=='auto'){
                    $vldata['serial_no'] = $auto.$maxId;
                    $vldata['sample_code'] = $auto.$maxId;
                    $vldata['sample_code_key'] = $maxId;
               }else if($arr['sample_code']=='YY' || $arr['sample_code']=='MMYY'){
                    $vldata['serial_no'] = $prefix.$mnthYr.$maxId;
                    $vldata['sample_code'] = $prefix.$mnthYr.$maxId;
                    $vldata['sample_code_format'] = $prefix.$mnthYr;
                    $vldata['sample_code_key'] =  $maxId;
               }
          }
     }

     $vldata['patient_first_name'] = $general->crypto('encrypt',$_POST['patientFname'],$vldata['patient_art_no']);
     $vldata['patient_last_name'] = $general->crypto('encrypt',$_POST['surName'],$vldata['patient_art_no']);


     $db=$db->where('vl_sample_id',$_POST['vlSampleId']);
     $id=$db->update($tableName,$vldata);
     $_SESSION['alertMsg']="VL request updated successfully";
     //Add event log
     $eventType = 'update-vl-request-png';
     $action = ucwords($_SESSION['userName']).' updated a request data with the sample code '.$_POST['sampleCode'];
     $resource = 'vl-request-png';

     $general->activityLog($eventType,$action,$resource);
     
    //  $data=array(
    //       'event_type'=>$eventType,
    //       'action'=>$action,
    //       'resource'=>$resource,
    //       'date_time'=>$general->getDateTime()
    //  );
    //  $db->insert($tableName1,$data);
     header("location:vlRequest.php");
} catch (Exception $exc) {
     error_log($exc->getMessage());
     error_log($exc->getTraceAsString());
}
