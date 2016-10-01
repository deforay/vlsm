<?php
session_start();
ob_start();
include('./includes/MysqliDb.php');
include('General.php');
$general=new Deforay_Commons_General();

$tableName="vl_request_form";
$tableName1="activity_log";
try {
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
     $_POST['result'] = '';
     if($_POST['vlResult']!=''){
          $_POST['result'] = $_POST['vlResult'];
     }else if($_POST['vlLog']!=''){
          $_POST['result'] = $_POST['vlLog'];
     }else if($_POST['textValue']!=''){
          $_POST['result'] = $_POST['textValue'];
     }
     if(!isset($_POST['noResult'])){
          $_POST['noResult'] = '';
     }
     if(!isset($_POST['patientPhoneNumber'])){
          $_POST['patientPhoneNumber'] = '';
     }
     
     $vldata=array(
          'urgency'=>$_POST['urgency'],
          'serial_no'=>$_POST['serialNo'],
          'sample_code'=>$_POST['serialNo'],
          'facility_id'=>$_POST['clinicName'],
          //'sample_code'=>$_POST['sampleCode'],
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
          //'drug_substitution'=>$_POST['drugSubstitution'],
          'lab_no'=>$_POST['labNo'],
          'lab_id'=>$_POST['labId'],
          'vl_test_platform'=>$_POST['testingPlatform'],
          'sample_id'=>$_POST['specimenType'],
          'sample_testing_date'=>$_POST['sampleTestingDateAtLab'],
          'absolute_value'=>$_POST['vlResult'],
          'log_value'=>$_POST['vlLog'],
          'result'=>$_POST['result'],
          'comments'=>$_POST['labComments'],
          'result_reviewed_by'=>$_POST['reviewedBy'],
          'date_sample_received_at_testing_lab'=>$_POST['sampleReceivedDate'],
          'rejection'=>$_POST['noResult'],
          'modified_on'=>$general->getDateTime()
        );
          if(isset($_POST['approvedBy'])){
            $vldata['result_approved_by'] = $_POST['approvedBy'];
          } 
          //print_r($vldata);die;
          $db=$db->where('vl_sample_id',$_POST['treamentId']);
          $id = $db->update($tableName,$vldata);
          if($id>0){
          $_SESSION['alertMsg']="VL request updated successfully";
          //Add event log
          $eventType = 'update-vl-request-zm';
          $action = ucwords($_SESSION['userName']).' updated a request data with the sample code '.$_POST['serialNo'];
          $resource = 'vl-request-zm';
          $data=array(
          'event_type'=>$eventType,
          'action'=>$action,
          'resource'=>$resource,
          'date_time'=>$general->getDateTime()
          );
          $db->insert($tableName1,$data);
          }else{
               $_SESSION['alertMsg']="Please try again later";
          }
          header("location:vlRequest.php");
    
  
} catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}