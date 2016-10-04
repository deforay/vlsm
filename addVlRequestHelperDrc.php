<?php
session_start();
ob_start();
include('./includes/MysqliDb.php');
include('General.php');
$general=new Deforay_Commons_General();
$tableName="vl_request_form";
$tableName1="activity_log";
try {
    $instanceId = '';
    if(isset($_SESSION['instanceId'])){
        $instanceId = $_SESSION['instanceId'];
    }
    //Set dob
    if(isset($_POST['dob']) && trim($_POST['dob'])!=""){
        $_POST['dob']=$general->dateFormat($_POST['dob']);  
    }
    //Set gender
    if(!isset($_POST['gender']) || trim($_POST['gender'])==''){
        $_POST['gender']='';
    }
    //Ser ARV initiation date
    if(isset($_POST['dateOfArtInitiation']) && trim($_POST['dateOfArtInitiation'])!=""){
        $_POST['dateOfArtInitiation']=$general->dateFormat($_POST['dateOfArtInitiation']);  
    }
    //Set ARV current regimen
    if(isset($_POST['newArtRegimen']) && trim($_POST['newArtRegimen'])!=""){
          $data=array(
            'art_code'=>$_POST['newArtRegimen'],
            'nation_identifier'=>'drc'
          );
          
          $result=$db->insert('r_art_code_details',$data);
          $_POST['artRegimen'] = $_POST['newArtRegimen'];
    }
    //Regimen change section
    if(!isset($_POST['hasChangedRegimen']) || trim($_POST['hasChangedRegimen'])==''){
        $_POST['hasChangedRegimen']='';
        $_POST['reasonForArvRegimenChange']='';
        $_POST['dateOfArvRegimenChange']='';
    }
    if(trim($_POST['hasChangedRegimen']) == "no"){
        $_POST['reasonForArvRegimenChange']='';
        $_POST['dateOfArvRegimenChange']='';
    }else if(trim($_POST['hasChangedRegimen']) == "yes"){
        if(isset($_POST['dateOfArvRegimenChange']) && trim($_POST['dateOfArvRegimenChange'])!=""){
          $_POST['dateOfArvRegimenChange']=$general->dateFormat($_POST['dateOfArvRegimenChange']);  
        }
    }
    //Set VL Test reason
    if(isset($_POST['vlTestReason']) && trim($_POST['vlTestReason'])!=""){
        if(trim($_POST['vlTestReason']) == 'other'){
            if(isset($_POST['newVlTestReason']) && trim($_POST['newVlTestReason'])!=""){
                $data=array(
                'test_reason_name'=>$_POST['newVlTestReason'],
                'test_reason_status'=>'active'
                );
                $id=$db->insert('r_vl_test_reasons',$data);
                $_POST['vlTestReason'] = $id;
            }else{
                $_POST['vlTestReason'] = '';
            }
        }
    }else{
        $_POST['vlTestReason'] = '';
    }
   //Set last VL test date
    if(isset($_POST['lastViralLoadTestDate']) && trim($_POST['lastViralLoadTestDate'])!=""){
        $_POST['lastViralLoadTestDate']=$general->dateFormat($_POST['lastViralLoadTestDate']);  
    }
    //Sample type section
    if(isset($_POST['specimenType']) && trim($_POST['specimenType'])!=""){
        if(trim($_POST['specimenType'])!= 2){
            $_POST['conservationTemperature'] = '';
            $_POST['durationOfConservation'] = '';
        }
    }else{
        $_POST['conservationTemperature'] = '';
        $_POST['durationOfConservation'] = '';
    }
    //Set sample received date
    if(isset($_POST['sampleReceivedDate']) && trim($_POST['sampleReceivedDate'])!=""){
        $sampleReceivedDate = explode(" ",$_POST['sampleReceivedDate']);
        $_POST['sampleReceivedDate']=$general->dateFormat($sampleReceivedDate[0])." ".$sampleReceivedDate[1];
    }
    //Set sample rejection reason
    if(isset($_POST['status']) && trim($_POST['status']) != ''){
        if($_POST['status'] == 4){
            if(trim($_POST['rejectionReason']) == "other" && trim($_POST['newRejectionReason']!= '')){
                $data=array(
                'rejection_reason_name'=>$_POST['newRejectionReason'],
                'rejection_reason_status'=>'active'
                );
                $id=$db->insert('r_sample_rejection_reasons',$data);
                $_POST['rejectionReason'] = $id;
            }else{
                $_POST['rejectionReason'] = '';
            }
        }elseif($_POST['status'] == 7){
            $_POST['rejectionReason'] = '';
        }
    }else{
        $_POST['rejectionReason'] = '';
    }
    //Set sample testing date
    if(isset($_POST['sampleTestingDateAtLab']) && trim($_POST['sampleTestingDateAtLab'])!=""){
        $_POST['sampleTestingDateAtLab']=$general->dateFormat($_POST['sampleTestingDateAtLab']);  
    }
    $vldata=array(
                  'vl_instance_id'=>$instanceId,
                  'form_id'=>3,
                  'facility_id'=>$_POST['clinicName'],
                  'service'=>$_POST['service'],
                  'request_clinician'=>$_POST['clinicianName'],
                  'clinician_ph_no'=>$_POST['clinicanTelephone'],
                  'support_partner'=>$_POST['supportPartner'],
                  'patient_dob'=>$_POST['dob'],
                  'age_in_yrs'=>$_POST['ageInYears'],
                  'age_in_mnts'=>$_POST['ageInMonths'],
                  'gender'=>$_POST['gender'],
                  'art_no'=>$_POST['patientArtNo'],
                  'date_of_initiation_of_current_regimen'=>$_POST['dateOfArtInitiation'],
                  'current_regimen'=>$_POST['artRegimen'],
                  'has_patient_changed_regimen'=>$_POST['hasChangedRegimen'],
                  'reason_for_regimen_change'=>$_POST['reasonForArvRegimenChange'],
                  'date_of_regimen_changed'=>$_POST['dateOfArvRegimenChange'],
                  'vl_test_reason'=>$_POST['vlTestReason'],
                  'last_viral_load_result'=>$_POST['lastViralLoadResult'],
                  'last_viral_load_date'=>$_POST['lastViralLoadTestDate'],
                  'sample_id'=>$_POST['specimenType'],
                  'plasma_conservation_temperature'=>$_POST['conservationTemperature'],
                  'duration_of_conservation'=>$_POST['durationOfConservation'],
                  'date_sample_received_at_testing_lab'=>$_POST['sampleReceivedDate'],
                  'status'=>$_POST['status'],
                  'sample_rejection_reason'=>$_POST['rejectionReason'],
                  'lab_no'=>$_POST['labNo'],
                  'lab_tested_date'=>$_POST['sampleTestingDateAtLab'],
                  'vl_test_platform'=>$_POST['testingPlatform'],
                  'result'=>$_POST['vlResult'],
                  'created_by'=>$_SESSION['userId'],
                  'created_on'=>$general->getDateTime(),
                  'modified_by'=>$_SESSION['userId'],
                  'modified_on'=>$general->getDateTime()
                );
    $id=$db->insert($tableName,$vldata);
    if($id>0){
        $_SESSION['alertMsg']="VL request added successfully";
        //Add event log
        $eventType = 'add-vl-request-drc';
        $action = ucwords($_SESSION['userName']).' added a new request data with the patient code '.$_POST['patientArtNo'];
        $resource = 'vl-request-drc';
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
}catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}