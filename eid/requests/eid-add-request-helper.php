<?php
ob_start();
session_start();
include_once '../../startup.php';
include_once APPLICATION_PATH . '/includes/MysqliDb.php';
include_once(APPLICATION_PATH . '/models/General.php');
$general = new General($db);

// echo "<pre>";
// var_dump($_POST);die;


$tableName="eid_form";
$tableName1="activity_log";

try {
    //system config
    $systemConfigQuery ="SELECT * from system_config";
    $systemConfigResult=$db->query($systemConfigQuery);
    $sarr = array();
    // now we create an associative array so that we can easily create view variables
    for ($i = 0; $i < sizeof($systemConfigResult); $i++) {
      $sarr[$systemConfigResult[$i]['name']] = $systemConfigResult[$i]['value'];
    }
    $instanceId = '';
    if(isset($_SESSION['instanceId'])){
        $instanceId = $_SESSION['instanceId'];
    }

    if(isset($_POST['sampleCollectionDate']) && trim($_POST['sampleCollectionDate'])!=""){
        $sampleCollectionDate = explode(" ",$_POST['sampleCollectionDate']);
        $_POST['sampleCollectionDate']=$general->dateFormat($sampleCollectionDate[0])." ".$sampleCollectionDate[1];
    }else{
        $_POST['sampleCollectionDate'] = NULL;
    }
    
    //Set sample received date
    if(isset($_POST['sampleReceivedDate']) && trim($_POST['sampleReceivedDate'])!=""){
        $sampleReceivedDate = explode(" ",$_POST['sampleReceivedDate']);
        $_POST['sampleReceivedDate']=$general->dateFormat($sampleReceivedDate[0])." ".$sampleReceivedDate[1];
    }else{
        $_POST['sampleReceivedDate'] = NULL;
    }
   
    if(isset($_POST['sampleTestedDateTime']) && trim($_POST['sampleTestedDateTime'])!=""){
      $sampleTestedDate = explode(" ",$_POST['sampleTestedDateTime']);
      $_POST['sampleTestedDateTime']=$general->dateFormat($sampleTestedDate[0])." ".$sampleTestedDate[1];
    }else{
      $_POST['sampleTestedDateTime'] = NULL;
    }
   
    if(isset($_POST['rapidtestDate']) && trim($_POST['rapidtestDate'])!=""){
      $rapidtestDate = explode(" ",$_POST['rapidtestDate']);
      $_POST['rapidtestDate']=$general->dateFormat($rapidtestDate[0])." ".$rapidtestDate[1];
    }else{
      $_POST['rapidtestDate'] = NULL;
    }
   
    if(isset($_POST['childDob']) && trim($_POST['childDob'])!=""){
      $childDob = explode(" ",$_POST['childDob']);
      $_POST['childDob']=$general->dateFormat($childDob[0])." ".$childDob[1];
    }else{
      $_POST['childDob'] = NULL;
    }
   
    if(isset($_POST['mothersDob']) && trim($_POST['mothersDob'])!=""){
      $mothersDob = explode(" ",$_POST['mothersDob']);
      $_POST['mothersDob']=$general->dateFormat($mothersDob[0])." ".$mothersDob[1];
    }else{
      $_POST['mothersDob'] = NULL;
    }
    
    if(!isset($_POST['sampleCode']) || trim($_POST['sampleCode'])== ''){
      $_POST['sampleCode'] = NULL;
    }
    
    if($sarr['user_type']=='remoteuser'){
        $sampleCode = 'remote_sample_code';
        $sampleCodeKey = 'remote_sample_code_key';
    }else{
        $sampleCode = 'sample_code';
        $sampleCodeKey = 'sample_code_key';
    }
    

    if(isset($_POST['motherViralLoadCopiesPerMl']) && $_POST['motherViralLoadCopiesPerMl'] != ""){
      $motherVlResult = $_POST['motherViralLoadCopiesPerMl'];
    }else if(isset($_POST['motherViralLoadText']) && $_POST['motherViralLoadText'] != ""){
      $motherVlResult = $_POST['motherViralLoadText'];
    }else{
      $motherVlResult = null;
    }




    $eidData=array(
                  'vlsm_instance_id'=>$instanceId,
                  'vlsm_country_id'=>3,
                  'sample_code_key'=>$_POST['sampleCodeKey'],
                  'sample_code_format'=>$_POST['sampleCodeFormat'],
                  'facility_id'=>$_POST['facilityId'],
                  'implementation_partner'=>$_POST['implementingPartner'],
                  'funding_source'=>$_POST['fundingSource'],
                  'mother_id'=>$_POST['mothersId'],
                  'mother_name'=>$_POST['mothersName'],
                  'mother_dob'=>$_POST['mothersDob'],
                  'mother_marital_status'=>$_POST['mothersMaritalStatus'],
                  'mother_treatment'=>implode(",",$_POST['motherTreatment']),
                  'mother_treatment_other'=>$_POST['motherTreatmentOther'],                  
                  'child_id'=>$_POST['childId'],
                  'child_name'=>$_POST['childName'],
                  'child_dob'=>$_POST['childDob'],
                  'child_gender'=>$_POST['childGender'],
                  'child_age'=>$_POST['childAge'],
                  'child_treatment'=>implode(",",$_POST['childTreatment']),
                  'mother_cd4'=>$_POST['mothercd4'],
                  'mother_vl_result'=>$motherVlResult,
                  'has_infant_stopped_breastfeeding'=>$_POST['hasInfantStoppedBreastfeeding'],
                  'age_breastfeeding_stopped_in_months'=>$_POST['ageBreastfeedingStopped'],
                  //'facility_id'=>$_POST['isInfantStillBeingBreastfed'],
                  'choice_of_feeding'=>$_POST['choiceOfFeeding'],
                  'is_cotrimoxazole_being_administered_to_the_infant'=>$_POST['isCotrimoxazoleBeingAdministered'],
                  'sample_collection_date'=>$_POST['sampleCollectionDate'],
                  'sample_requestor_phone'=>$_POST['sampleRequestorPhone'],
                  'sample_requestor_name'=>$_POST['sampleRequestorName'],
                  'rapid_test_performed'=>$_POST['rapidTestPerformed'],
                  'rapid_test_date'=>$_POST['rapidtestDate'],
                  'rapid_test_result'=>$_POST['rapidTestResult'],
                  'sample_received_at_vl_lab_datetime'=>$_POST['sampleReceivedDate'],
                  'sample_tested_datetime'=>$_POST['sampleTestedDateTime'],
                  'is_sample_rejected'=>$_POST['isSampleRejected'],
                  'result'=>$_POST['result'],
                  'result_status'=>6,
                  'reason_for_sample_rejection'=>$_POST['sampleRejectionReason'],
                  'request_created_by'=>$_SESSION['userId'],
                  'request_created_datetime'=>$general->getDateTime(),
                  'sample_registered_at_lab'=>$general->getDateTime(),
                  'last_modified_by'=>$_SESSION['userId'],
                  'last_modified_datetime'=>$general->getDateTime()
                );

                //echo "<pre>";
                //var_dump($eidData);die;

                if(isset($_POST['eidSampleId']) && $_POST['eidSampleId']!=''){
                    $db=$db->where('eid_id',$_POST['eidSampleId']);
                    $id=$db->update($tableName,$eidData);
                }

    if($id>0){
        $_SESSION['alertMsg']="EID request added successfully";
        //Add event log
        $eventType = 'add-eid-request-drc';
        $action = ucwords($_SESSION['userName']).' added a new EID request data with the sample id '.$_POST['eidSampleId'];
        $resource = 'eid-request-drc';

        $general->activityLog($eventType,$action,$resource);

        // $data=array(
        // 'event_type'=>$eventType,
        // 'action'=>$action,
        // 'resource'=>$resource,
        // 'date_time'=>$general->getDateTime()
        // );
        // $db->insert($tableName1,$data);

    }else{
        $_SESSION['alertMsg']="Please try again later";
    }
    header("location:/eid/requests/eid-requests.php");
}catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}
