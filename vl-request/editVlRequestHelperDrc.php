<?php
session_start();
ob_start();
include('../includes/MysqliDb.php');
include('../General.php');
$general=new Deforay_Commons_General();
$tableName="vl_request_form";
$tableName1="activity_log";
try {
    //Set Date of demand
    if(isset($_POST['dateOfDemand']) && trim($_POST['dateOfDemand'])!=""){
        $_POST['dateOfDemand']=$general->dateFormat($_POST['dateOfDemand']);  
    }else{
        $_POST['dateOfDemand'] = NULL;
    }
    //Set dob
    if(isset($_POST['dob']) && trim($_POST['dob'])!=""){
        $_POST['dob']=$general->dateFormat($_POST['dob']);  
    }else{
        $_POST['dob'] = NULL;
    }
    //Set gender/it's realted values
    if(!isset($_POST['gender']) || trim($_POST['gender'])==''){
        $_POST['gender'] = NULL;
        $_POST['breastfeeding'] = NULL;
        $_POST['patientPregnant'] = NULL;
        $_POST['trimestre'] = NULL;
    }else if($_POST['gender'] == "female"){
        if(!isset($_POST['breastfeeding']) || trim($_POST['breastfeeding'])==""){
            $_POST['breastfeeding'] = NULL;
        } if(!isset($_POST['patientPregnant']) || trim($_POST['patientPregnant'])==""){
            $_POST['patientPregnant'] = NULL;
        } if(!isset($_POST['trimestre']) || trim($_POST['trimestre'])==""){
            $_POST['trimestre'] = NULL;
        }
    }else if($_POST['gender'] == "male"){
        $_POST['breastfeeding'] = NULL;
        $_POST['patientPregnant'] = NULL;
        $_POST['trimestre'] = NULL;
    }
    //Set is patient new
    if(!isset($_POST['isPatientNew']) || trim($_POST['isPatientNew'])==''){
        $_POST['isPatientNew'] = NULL;
        $_POST['dateOfArtInitiation'] = NULL;
    }else if($_POST['isPatientNew'] =="yes"){
        //Ser ARV initiation date
        if(isset($_POST['dateOfArtInitiation']) && trim($_POST['dateOfArtInitiation'])!=""){
            $_POST['dateOfArtInitiation']=$general->dateFormat($_POST['dateOfArtInitiation']);  
        }else{
           $_POST['dateOfArtInitiation'] = NULL; 
        }
    }else if($_POST['isPatientNew'] =="no"){
        $_POST['dateOfArtInitiation'] = NULL; 
    }
    //Set ARV current regimen
    if(isset($_POST['newArtRegimen']) && trim($_POST['newArtRegimen'])!=""){
          $data=array(
            'art_code'=>$_POST['newArtRegimen'],
            'parent_art'=>3,
            'nation_identifier'=>'drc'
          );
          
          $result=$db->insert('r_art_code_details',$data);
          $_POST['artRegimen'] = $_POST['newArtRegimen'];
    }
    //Regimen change section
    if(!isset($_POST['hasChangedRegimen']) || trim($_POST['hasChangedRegimen'])==''){
        $_POST['hasChangedRegimen']=NULL;
        $_POST['reasonForArvRegimenChange']=NULL;
        $_POST['dateOfArvRegimenChange']=NULL;
    }
    if(trim($_POST['hasChangedRegimen']) == "no"){
        $_POST['reasonForArvRegimenChange']=NULL;
        $_POST['dateOfArvRegimenChange']=NULL;
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
                $_POST['vlTestReason'] = NULL;
            }
        }
    }else{
        $_POST['vlTestReason'] = NULL;
    }
    //Set Viral load no.
    if(!isset($_POST['viralLoadNo']) || trim($_POST['viralLoadNo'])==''){
        $_POST['viralLoadNo'] = NULL;
    }
   //Set last VL test date
    if(isset($_POST['lastViralLoadTestDate']) && trim($_POST['lastViralLoadTestDate'])!=""){
        $_POST['lastViralLoadTestDate']=$general->dateFormat($_POST['lastViralLoadTestDate']);  
    }else{
        $_POST['lastViralLoadTestDate'] = NULL;
    }
    //Set sample collection date
    if(isset($_POST['sampleCollectionDate']) && trim($_POST['sampleCollectionDate'])!=""){
        $sampleCollectionDate = explode(" ",$_POST['sampleCollectionDate']);
        $_POST['sampleCollectionDate']=$general->dateFormat($sampleCollectionDate[0])." ".$sampleCollectionDate[1];
    }else{
        $_POST['sampleCollectionDate'] = NULL;
    }
    //Sample type section
    if(isset($_POST['specimenType']) && trim($_POST['specimenType'])!=""){
        if(trim($_POST['specimenType'])!= 2){
            $_POST['conservationTemperature'] = NULL;
            $_POST['durationOfConservation'] = NULL;
        }
    }
    //Set sample received date
    if(isset($_POST['sampleReceivedDate']) && trim($_POST['sampleReceivedDate'])!=""){
        $sampleReceivedDate = explode(" ",$_POST['sampleReceivedDate']);
        $_POST['sampleReceivedDate']=$general->dateFormat($sampleReceivedDate[0])." ".$sampleReceivedDate[1];
    }else{
       $_POST['sampleReceivedDate'] = NULL;
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
            }
        }else{
            $_POST['rejectionReason'] = NULL;
        }
    }
    //Set sample testing date
    if(isset($_POST['sampleTestingDateAtLab']) && trim($_POST['sampleTestingDateAtLab'])!=""){
        $sampleTestedDate = explode(" ",$_POST['sampleTestingDateAtLab']);
        $_POST['sampleTestingDateAtLab']=$general->dateFormat($sampleTestedDate[0])." ".$sampleTestedDate[1];
    }else{
        $_POST['sampleTestingDateAtLab'] = NULL;
    }
    //Set Dispatched From Clinic To Lab Date
    if(isset($_POST['dateDispatchedFromClinicToLab']) && trim($_POST['dateDispatchedFromClinicToLab'])!=""){
        $dispatchedFromClinicToLabDate = explode(" ",$_POST['dateDispatchedFromClinicToLab']);
        $_POST['dateDispatchedFromClinicToLab']=$general->dateFormat($dispatchedFromClinicToLabDate[0])." ".$dispatchedFromClinicToLabDate[1];
    }else{
        $_POST['dateDispatchedFromClinicToLab'] = NULL;
    }
    //Set Date of Completion of Viral Load
    if(isset($_POST['dateOfCompletionOfViralLoad']) && trim($_POST['dateOfCompletionOfViralLoad'])!=""){
        $_POST['dateOfCompletionOfViralLoad']=$general->dateFormat($_POST['dateOfCompletionOfViralLoad']);  
    }else{
        $_POST['dateOfCompletionOfViralLoad'] = NULL;
    }
    if(!isset($_POST['sampleCode']) || trim($_POST['sampleCode'])== ''){
        $_POST['sampleCode'] = NULL;
    }
    $vldata=array(
                  'facility_id'=>$_POST['clinicName'],
                  'request_clinician'=>$_POST['clinicianName'],
                  'clinician_ph_no'=>$_POST['clinicanTelephone'],
                  'support_partner'=>$_POST['supportPartner'],
                  'patient_dob'=>$_POST['dob'],
                  'age_in_yrs'=>$_POST['ageInYears'],
                  'age_in_mnts'=>$_POST['ageInMonths'],
                  'gender'=>$_POST['gender'],
                  'is_patient_breastfeeding'=>$_POST['breastfeeding'],
                  'is_patient_pregnant'=>$_POST['patientPregnant'],
                  'trimestre'=>$_POST['trimestre'],
                  'art_no'=>$_POST['patientArtNo'],
                  'is_patient_new'=>$_POST['isPatientNew'],
                  'date_of_initiation_of_current_regimen'=>$_POST['dateOfArtInitiation'],
                  'current_regimen'=>$_POST['artRegimen'],
                  'has_patient_changed_regimen'=>$_POST['hasChangedRegimen'],
                  'reason_for_regimen_change'=>$_POST['reasonForArvRegimenChange'],
                  'date_of_regimen_changed'=>$_POST['dateOfArvRegimenChange'],
                  'vl_test_reason'=>$_POST['vlTestReason'],
                  'last_viral_load_result'=>$_POST['lastViralLoadResult'],
                  'last_viral_load_date'=>$_POST['lastViralLoadTestDate'],
                  'sample_received_at_vl_lab_datetime'=>$_POST['sampleReceivedDate'],
                  'sample_code'=>$_POST['sampleCode'],
                  'serial_no'=>$_POST['sampleCode'],
                  'sample_tested_datetime'=>$_POST['sampleTestingDateAtLab'],
                  'vl_test_platform'=>$_POST['testingPlatform'],
                  'result_value_log'=>$_POST['vlLog'],
                  'result'=>$_POST['vlResult'],
                  'date_of_demand'=>$_POST['dateOfDemand'],
                  'viral_load_no'=>$_POST['viralLoadNo'],
                  'sample_collection_date'=>$_POST['sampleCollectionDate'],
                  'date_dispatched_from_clinic_to_lab'=>$_POST['dateDispatchedFromClinicToLab'],
                  'result_approved_datetime'=>$_POST['dateOfCompletionOfViralLoad'],
                  'last_modified_by'=>$_SESSION['userId'],
                  'last_modified_datetime'=>$general->getDateTime()
                );
    if(isset($_POST['specimenType']) && trim($_POST['specimenType'])!= ''){
        $vldata['sample_id'] = $_POST['specimenType'];
        $vldata['plasma_conservation_temperature'] = $_POST['conservationTemperature'];
        $vldata['duration_of_conservation'] = $_POST['durationOfConservation'];
    }
    if(isset($_POST['status']) && trim($_POST['status'])!= ''){
        $vldata['status'] = $_POST['status'];
        if(isset($_POST['rejectionReason'])){
            $vldata['sample_rejection_reason'] = $_POST['rejectionReason'];
        }
    }
    //var_dump($vldata);die;
    $db=$db->where('vl_sample_id',$_POST['vlSampleId']);
    $id = $db->update($tableName,$vldata);
    if($id>0){
        if(isset($_POST['rSrc']) && trim($_POST['rSrc']) == "er"){
            $_SESSION['alertMsg']="VL result updated successfully";
            //Add event log
            $eventType = 'update-vl-result-drc';
            $action = ucwords($_SESSION['userName']).' updated a result data with the patient code '.$_POST['patientArtNo'];
            $resource = 'vl-result-drc';
        }else{
            $_SESSION['alertMsg']="VL request updated successfully";
            //Add event log
            $eventType = 'edit-vl-request-drc';
            $action = ucwords($_SESSION['userName']).' updated a request data with the patient code '.$_POST['patientArtNo'];
            $resource = 'vl-request-drc'; 
        }
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
    if(isset($_POST['rSrc']) && trim($_POST['rSrc']) == "er"){
        header("location:../vl-print/vlTestResult.php");
    }else{
        header("location:vlRequest.php");
    }
}catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}