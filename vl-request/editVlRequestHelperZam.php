<?php
session_start();
ob_start();
include('../includes/MysqliDb.php');
include('../General.php');
$general=new Deforay_Commons_General();
$tableName="vl_request_form";
$tableName1="activity_log";
try {
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
     if(isset($_POST['sampleTestingDateAtLab']) && trim($_POST['sampleTestingDateAtLab'])!=""){
          $sampleTestingDateLab = explode(" ",$_POST['sampleTestingDateAtLab']);
          $_POST['sampleTestingDateAtLab']=$general->dateFormat($sampleTestingDateLab[0])." ".$sampleTestingDateLab[1];  
     }else{
        $_POST['sampleTestingDateAtLab'] = NULL;
     }
    
     if(isset($_POST['newArtRegimen']) && trim($_POST['newArtRegimen'])!=""){
          $data=array(
            'art_code'=>$_POST['newArtRegimen'],
            'nation_identifier'=>'zam',
            'parent_art'=>'4'
          );
          $result=$db->insert('r_art_code_details',$data);
          $_POST['artRegimen'] = $_POST['newArtRegimen'];
     }
     if(isset($_POST['newVlTestReason']) && trim($_POST['newVlTestReason'])!=""){
          $data=array(
            'test_reason_name'=>$_POST['newVlTestReason'],
            'test_reason_status'=>'active'
          );
          $result=$db->insert('r_vl_test_reasons',$data);
          $_POST['vlTestReason'] = $_POST['newVlTestReason'];
     }
     
     if(isset($_POST['gender']) && trim($_POST['gender'])=='male'){
          $_POST['patientPregnant']='';
          $_POST['breastfeeding']='';
     }
     $_POST['result'] = '';
     if($_POST['vlResult']!=''){
          $_POST['result'] = $_POST['vlResult'];
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
          'serial_no'=>(isset($_POST['sampleCode']) && $_POST['sampleCode']!='' ? $_POST['sampleCode'] :  NULL) ,
          'sample_code'=>(isset($_POST['sampleCode']) && $_POST['sampleCode']!='' ? $_POST['sampleCode'] :  NULL),
          'facility_id'=>(isset($_POST['clinicName']) && $_POST['clinicName']!='' ? $_POST['clinicName'] :  NULL),
          'lab_contact_person'=>(isset($_POST['clinicianName']) && $_POST['clinicianName']!='' ? $_POST['clinicianName'] :  NULL),
          'sample_collection_date'=>$_POST['sampleCollectionDate'],
          'patient_name'=>(isset($_POST['patientFname']) && $_POST['patientFname']!='' ? $_POST['patientFname'] :  NULL),
          'surname'=>(isset($_POST['surName']) && $_POST['surName']!='' ? $_POST['surName'] :  NULL),
          'gender'=>(isset($_POST['gender']) && $_POST['gender']!='' ? $_POST['gender'] :  NULL),
          'patient_dob'=>$_POST['dob'],
          'age_in_yrs'=>(isset($_POST['ageInYears']) && $_POST['ageInYears']!='' ? $_POST['ageInYears'] :  NULL),
          'age_in_mnts'=>(isset($_POST['ageInMonths']) && $_POST['ageInMonths']!='' ? $_POST['ageInMonths'] :  NULL),
          'is_patient_pregnant'=>(isset($_POST['patientPregnant']) && $_POST['patientPregnant']!='' ? $_POST['patientPregnant'] :  NULL),
          'is_patient_breastfeeding'=>(isset($_POST['breastfeeding']) && $_POST['breastfeeding']!='' ? $_POST['breastfeeding'] :  NULL),
          'art_no'=>(isset($_POST['patientArtNo']) && $_POST['patientArtNo']!='' ? $_POST['patientArtNo'] :  NULL),
          'current_regimen'=>(isset($_POST['artRegimen']) && $_POST['artRegimen']!='' ? $_POST['artRegimen'] :  NULL),
          'date_of_initiation_of_current_regimen'=>$_POST['dateOfArtInitiation'],
          'patient_phone_number'=>(isset($_POST['patientPhoneNumber']) && $_POST['patientPhoneNumber']!='' ? $_POST['patientPhoneNumber'] :  NULL),
          'last_viral_load_date'=>$_POST['lastViralLoadTestDate'],
          'last_viral_load_result'=>(isset($_POST['lastViralLoadResult']) && $_POST['lastViralLoadResult']!='' ? $_POST['lastViralLoadResult'] :  NULL),
          'vl_test_reason'=>(isset($_POST['vlTestReason']) && $_POST['vlTestReason']!='' ? $_POST['vlTestReason'] :  NULL),
          'sample_id'=>(isset($_POST['specimenType']) && $_POST['specimenType']!='' ? $_POST['specimenType'] :  NULL),
          'lab_tested_date'=>$_POST['sampleTestingDateAtLab'],
          'absolute_value'=>(isset($_POST['vlResult']) && $_POST['vlResult']!='' ? $_POST['vlResult'] :  NULL),
          'result'=>(isset($_POST['result']) && $_POST['result']!='' ? $_POST['result'] :  NULL),
          'comments'=>(isset($_POST['labComments']) && trim($_POST['labComments'])!='' ? trim($_POST['labComments']) :  NULL),
          'date_sample_received_at_testing_lab'=>$_POST['sampleReceivedDate'],
          'vl_test_platform'=>$_POST['testingPlatform'],
          'rejection'=>(isset($_POST['noResult']) && $_POST['noResult']!='' ? $_POST['noResult'] :  NULL),
          'sample_rejection_reason'=>(isset($_POST['rejectionReason']) && $_POST['rejectionReason']!='' ? $_POST['rejectionReason'] :  NULL),
          'test_methods'=>(isset($_POST['testMethods']) && $_POST['testMethods']!='' ? $_POST['testMethods'] :  NULL),
          'enhance_session'=>(isset($_POST['enhanceSession']) && $_POST['enhanceSession']!='' ? $_POST['enhanceSession'] :  NULL),
          'poor_adherence'=>(isset($_POST['poorAdherence']) && $_POST['poorAdherence']!='' ? $_POST['poorAdherence'] :  NULL),
          'result_approved_by'=>(isset($_POST['approvedBy']) && $_POST['approvedBy']!='' ? $_POST['approvedBy'] :  NULL),
          'modified_on'=>$general->getDateTime(),
          'result_coming_from'=>'manual'
        );
     //print_r($vldata);die;
     $db=$db->where('vl_sample_id',$_POST['vlSampleId']);
     $id = $db->update($tableName,$vldata);
     if($id>0){
          $_SESSION['alertMsg']="VL request updated successfully";
          //Update request xml
          $configQuery ="SELECT value FROM global_config where name='sync_path'";
          $configResult = $db->rawQuery($configQuery);
          if(!file_exists($configResult[0]['value'] . DIRECTORY_SEPARATOR . "request")){
               mkdir($configResult[0]['value'] . DIRECTORY_SEPARATOR . "request");
          }
          if(!file_exists($configResult[0]['value'] . DIRECTORY_SEPARATOR . "request" . DIRECTORY_SEPARATOR . "new")){
             mkdir($configResult[0]['value'] . DIRECTORY_SEPARATOR . "request" . DIRECTORY_SEPARATOR . "new");  
          }
          if(!file_exists($configResult[0]['value'] . DIRECTORY_SEPARATOR . "request" . DIRECTORY_SEPARATOR . "synced")){
             mkdir($configResult[0]['value'] . DIRECTORY_SEPARATOR . "request" . DIRECTORY_SEPARATOR . "synced"); 
          }
          if(!file_exists($configResult[0]['value'] . DIRECTORY_SEPARATOR . "request" . DIRECTORY_SEPARATOR . "error")){
             mkdir($configResult[0]['value'] . DIRECTORY_SEPARATOR . "request" . DIRECTORY_SEPARATOR . "error"); 
          }
          $files = scandir($configResult[0]['value'] . DIRECTORY_SEPARATOR . "request" . DIRECTORY_SEPARATOR . "new");
          foreach($files as $file ) {
               if(count($files) >2){
                    if (in_array($file, array(".",".."))) continue;
                    $xmlFile = file_get_contents($configResult[0]['value'] . DIRECTORY_SEPARATOR . "request" . DIRECTORY_SEPARATOR . "new" . DIRECTORY_SEPARATOR . $file);
                    $xml = new SimpleXMLElement($xmlFile);
                    $result = json_encode($xml);
                    //Convert the JSON string back into an array.
                    $result = json_decode($result, true);
                    $dataFile = false;
                    foreach($result as $vlData){
                         if($_POST['sampleCode'] == $vlData['sample_code']){
                              $dataFile = true;
                              unlink($configResult[0]['value'] . DIRECTORY_SEPARATOR . "request" . DIRECTORY_SEPARATOR . "new" . DIRECTORY_SEPARATOR . $file);
                         }
                    }
               }
               //Add or Updated request xml
               $vlQuery="SELECT * FROM vl_request_form as vl LEFT JOIN facility_details as f ON vl.facility_id=f.facility_id LEFT JOIN r_sample_type as s ON s.sample_id=vl.sample_id INNER JOIN testing_status as ts ON ts.status_id=vl.status LEFT JOIN r_art_code_details as art ON vl.current_regimen=art.art_id LEFT JOIN batch_details as b ON b.batch_id=vl.batch_id WHERE vl.sample_code = '".$_POST['sampleCode']."'";
               $vlResult = $db->rawQuery($vlQuery);
               $xmlData = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
                $xmlData.="<vl_request>\n";
                $xmlData.="<vl_request_form>\n";
                $xmlData.="<sample_code>".$vlResult[0]['sample_code']."</sample_code>\n";
                $xmlData.="<vl_instance_id>".$vlResult[0]['vl_instance_id']."</vl_instance_id>\n";
                $xmlData.="<serial_no>".$vlResult[0]['serial_no']."</serial_no>\n";
                
                $xmlData.="<facility_name>".$vlResult[0]['facility_name']."</facility_name>\n";
                $xmlData.="<facility_code>".$vlResult[0]['facility_code']."</facility_code>\n";
                $xmlData.="<facility_contact_person>".$vlResult[0]['contact_person']."</facility_contact_person>\n";
                $xmlData.="<facility_phone_number>".$vlResult[0]['phone_number']."</facility_phone_number>\n";
                $xmlData.="<facility_address>".$vlResult[0]['address']."</facility_address>\n";
                $xmlData.="<facility_country>".$vlResult[0]['country']."</facility_country>\n";
                $xmlData.="<facility_state>".$vlResult[0]['state']."</facility_state>\n";
                $xmlData.="<facility_district>".$vlResult[0]['district']."</facility_district>\n";
                $xmlData.="<facility_hub_name>".$vlResult[0]['hub_name']."</facility_hub_name>\n";
                $xmlData.="<facility_other_id>".$vlResult[0]['other_id']."</facility_other_id>\n";
                $xmlData.="<facility_latitude>".$vlResult[0]['latitude']."</facility_latitude>\n";
                $xmlData.="<facility_longitude>".$vlResult[0]['longitude']."</facility_longitude>\n";
                $xmlData.="<facility_email>".$vlResult[0]['email']."</facility_email>\n";
                
                $xmlData.="<sample_type>".$vlResult[0]['sample_name']."</sample_type>\n";
                $xmlData.="<testing_status>".$vlResult[0]['status_name']."</testing_status>\n";
                $xmlData.="<art_code>".$vlResult[0]['art_code']."</art_code>\n";
                $xmlData.="<nation_identifier>".$vlResult[0]['nation_identifier']."</nation_identifier>\n";
                
                $xmlData.="<batch_code>".$vlResult[0]['batch_code']."</batch_code>\n";
                $xmlData.="<batch_code_key>".$vlResult[0]['batch_code_key']."</batch_code_key>\n";
                $xmlData.="<batch_status>".$vlResult[0]['batch_status']."</batch_status>\n";
                
                $xmlData.="<urgency>".$vlResult[0]['urgency']."</urgency>\n";
                $xmlData.="<patient_name>".$vlResult[0]['patient_name']."</patient_name>\n";
                $xmlData.="<surname>".$vlResult[0]['surname']."</surname>\n";
                $xmlData.="<art_no>".$vlResult[0]['art_no']."</art_no>\n";
                $xmlData.="<patient_dob>".$vlResult[0]['patient_dob']."</patient_dob>\n";
                $xmlData.="<gender>".$vlResult[0]['gender']."</gender>\n";
                $xmlData.="<patient_phone_number>".$vlResult[0]['patient_phone_number']."</patient_phone_number>\n";
                $xmlData.="<location>".$vlResult[0]['location']."</location>\n";
                $xmlData.="<patient_art_date>".$vlResult[0]['patient_art_date']."</patient_art_date>\n";
                $xmlData.="<sample_collection_date>".$vlResult[0]['sample_collection_date']."</sample_collection_date>\n";
                $xmlData.="<is_patient_new>".$vlResult[0]['is_patient_new']."</is_patient_new>\n";
                $xmlData.="<treatment_initiation>".$vlResult[0]['treatment_initiation']."</treatment_initiation>\n";
                $xmlData.="<current_regimen>".$vlResult[0]['current_regimen']."</current_regimen>\n";
                $xmlData.="<date_of_initiation_of_current_regimen>".$vlResult[0]['date_of_initiation_of_current_regimen']."</date_of_initiation_of_current_regimen>\n";
                $xmlData.="<is_patient_pregnant>".$vlResult[0]['is_patient_pregnant']."</is_patient_pregnant>\n";
                $xmlData.="<is_patient_breastfeeding>".$vlResult[0]['is_patient_breastfeeding']."</is_patient_breastfeeding>\n";
                $xmlData.="<trimestre>".$vlResult[0]['trimestre']."</trimestre>\n";
                $xmlData.="<arv_adherence>".$vlResult[0]['arv_adherence']."</arv_adherence>\n";
                $xmlData.="<poor_adherence>".$vlResult[0]['poor_adherence']."</poor_adherence>\n";
                $xmlData.="<patient_receive_sms>".$vlResult[0]['patient_receive_sms']."</patient_receive_sms>\n";
                $xmlData.="<viral_load_indication>".$vlResult[0]['viral_load_indication']."</viral_load_indication>\n";
                $xmlData.="<enhance_session>".$vlResult[0]['enhance_session']."</enhance_session>\n";
                $xmlData.="<routine_monitoring_last_vl_date>".$vlResult[0]['routine_monitoring_last_vl_date']."</routine_monitoring_last_vl_date>\n";
                $xmlData.="<routine_monitoring_value>".$vlResult[0]['routine_monitoring_value']."</routine_monitoring_value>\n";
                $xmlData.="<routine_monitoring_sample_type>".$vlResult[0]['routine_monitoring_sample_type']."</routine_monitoring_sample_type>\n";
                $xmlData.="<vl_treatment_failure_adherence_counseling_last_vl_date>".$vlResult[0]['vl_treatment_failure_adherence_counseling_last_vl_date']."</vl_treatment_failure_adherence_counseling_last_vl_date>\n";
                $xmlData.="<vl_treatment_failure_adherence_counseling_value>".$vlResult[0]['vl_treatment_failure_adherence_counseling_value']."</vl_treatment_failure_adherence_counseling_value>\n";
                $xmlData.="<vl_treatment_failure_adherence_counseling_sample_type>".$vlResult[0]['vl_treatment_failure_adherence_counseling_sample_type']."</vl_treatment_failure_adherence_counseling_sample_type>\n";
                $xmlData.="<suspected_treatment_failure_last_vl_date>".$vlResult[0]['suspected_treatment_failure_last_vl_date']."</suspected_treatment_failure_last_vl_date>\n";
                $xmlData.="<suspected_treatment_failure_value>".$vlResult[0]['suspected_treatment_failure_value']."</suspected_treatment_failure_value>\n";
                $xmlData.="<suspected_treatment_failure_sample_type>".$vlResult[0]['suspected_treatment_failure_sample_type']."</suspected_treatment_failure_sample_type>\n";
                $xmlData.="<switch_to_tdf_last_vl_date>".$vlResult[0]['switch_to_tdf_last_vl_date']."</switch_to_tdf_last_vl_date>\n";
                $xmlData.="<switch_to_tdf_value>".$vlResult[0]['switch_to_tdf_value']."</switch_to_tdf_value>\n";
                $xmlData.="<switch_to_tdf_sample_type>".$vlResult[0]['switch_to_tdf_sample_type']."</switch_to_tdf_sample_type>\n";
                $xmlData.="<missing_last_vl_date>".$vlResult[0]['missing_last_vl_date']."</missing_last_vl_date>\n";
                $xmlData.="<missing_value>".$vlResult[0]['missing_value']."</missing_value>\n";
                $xmlData.="<missing_sample_type>".$vlResult[0]['missing_sample_type']."</missing_sample_type>\n";
                $xmlData.="<request_clinician>".$vlResult[0]['request_clinician']."</request_clinician>\n";
                $xmlData.="<clinician_ph_no>".$vlResult[0]['clinician_ph_no']."</clinician_ph_no>\n";
                $xmlData.="<sample_testing_date>".$vlResult[0]['sample_testing_date']."</sample_testing_date>\n";
                $xmlData.="<vl_focal_person>".$vlResult[0]['vl_focal_person']."</vl_focal_person>\n";
                $xmlData.="<focal_person_phone_number>".$vlResult[0]['focal_person_phone_number']."</focal_person_phone_number>\n";
                $xmlData.="<email_for_HF>".$vlResult[0]['email_for_HF']."</email_for_HF>\n";
                $xmlData.="<date_sample_received_at_testing_lab>".$vlResult[0]['date_sample_received_at_testing_lab']."</date_sample_received_at_testing_lab>\n";
                $xmlData.="<date_results_dispatched>".$vlResult[0]['date_results_dispatched']."</date_results_dispatched>\n";
                $xmlData.="<rejection>".$vlResult[0]['rejection']."</rejection>\n";
                $xmlData.="<sample_rejection_facility>".$vlResult[0]['sample_rejection_facility']."</sample_rejection_facility>\n";
                $xmlData.="<sample_rejection_reason>".$vlResult[0]['sample_rejection_reason']."</sample_rejection_reason>\n";
                $xmlData.="<other_id>".$vlResult[0]['other_id']."</other_id>\n";
                $xmlData.="<age_in_yrs>".$vlResult[0]['age_in_yrs']."</age_in_yrs>\n";
                $xmlData.="<age_in_mnts>".$vlResult[0]['age_in_mnts']."</age_in_mnts>\n";
                $xmlData.="<treatment_initiated_date>".$vlResult[0]['treatment_initiated_date']."</treatment_initiated_date>\n";
                $xmlData.="<arc_no>".$vlResult[0]['arc_no']."</arc_no>\n";
                $xmlData.="<treatment_details>".$vlResult[0]['treatment_details']."</treatment_details>\n";
               if(isset($vlResult[0]['lab_id']) && trim($vlResult[0]['lab_id'])!=""){
                    $fQuery="SELECT * FROM facility_details WHERE facility_type ='2' AND facility_id='".$vlResult[0]['lab_id']."'";
                    $fResult = $db->query($fQuery);
                    $xmlData.="<lab_name>".$fResult[0]['facility_name']."</lab_name>\n";
                    $xmlData.="<lab_no>".$fResult[0]['facility_code']."</lab_no>\n";
                    $xmlData.="<lab_contact_person>".$fResult[0]['contact_person']."</lab_contact_person>\n";
                    $xmlData.="<lab_phone_no>".$fResult[0]['phone_number']."</lab_phone_no>\n";
                    $xmlData.="<lab_country>".$fResult[0]['country']."</lab_country>\n";
                    $xmlData.="<lab_state>".$fResult[0]['state']."</lab_state>\n";
                    $xmlData.="<lab_district>".$fResult[0]['district']."</lab_district>\n";
                 }else{
                    $xmlData.="<lab_name>".$vlResult[0]['lab_name']."</lab_name>\n";
                    //$xmlData.="<lab_id>".$vlResult[0]['lab_id']."</lab_id>\n";
                    $xmlData.="<lab_no>".$vlResult[0]['lab_no']."</lab_no>\n";
                    $xmlData.="<lab_contact_person>".$vlResult[0]['lab_contact_person']."</lab_contact_person>\n";
                    $xmlData.="<lab_phone_no>".$vlResult[0]['lab_phone_no']."</lab_phone_no>\n";
               }
                $xmlData.="<lab_tested_date>".$vlResult[0]['lab_tested_date']."</lab_tested_date>\n";
                $xmlData.="<justification>".$vlResult[0]['justification']."</justification>\n";
                $xmlData.="<log_value>".$vlResult[0]['log_value']."</log_value>\n";
                $xmlData.="<absolute_value>".$vlResult[0]['absolute_value']."</absolute_value>\n";
                $xmlData.="<text_value>".$vlResult[0]['text_value']."</text_value>\n";
                $xmlData.="<result>".$vlResult[0]['result']."</result>\n";
                $xmlData.="<comments>".$vlResult[0]['comments']."</comments>\n";
                $xmlData.="<result_reviewed_date>".$vlResult[0]['result_reviewed_date']."</result_reviewed_date>\n";
                $xmlData.="<test_methods>".$vlResult[0]['test_methods']."</test_methods>\n";
                $xmlData.="<contact_complete_status>".$vlResult[0]['contact_complete_status']."</contact_complete_status>\n";
                $xmlData.="<last_viral_load_date>".$vlResult[0]['last_viral_load_date']."</last_viral_load_date>\n";
                $xmlData.="<last_viral_load_result>".$vlResult[0]['last_viral_load_result']."</last_viral_load_result>\n";
                $xmlData.="<viral_load_log>".$vlResult[0]['viral_load_log']."</viral_load_log>\n";
                $xmlData.="<vl_test_reason>".$vlResult[0]['vl_test_reason']."</vl_test_reason>\n";
                $xmlData.="<drug_substitution>".$vlResult[0]['drug_substitution']."</drug_substitution>\n";
                $xmlData.="<vl_test_platform>".$vlResult[0]['vl_test_platform']."</vl_test_platform>\n";
                $xmlData.="<support_partner>".$vlResult[0]['support_partner']."</support_partner>\n";
                $xmlData.="<has_patient_changed_regimen>".$vlResult[0]['has_patient_changed_regimen']."</has_patient_changed_regimen>\n";
                $xmlData.="<reason_for_regimen_change>".$vlResult[0]['reason_for_regimen_change']."</reason_for_regimen_change>\n";
                $xmlData.="<date_of_regimen_changed>".$vlResult[0]['date_of_regimen_changed']."</date_of_regimen_changed>\n";
                $xmlData.="<plasma_conservation_temperature>".$vlResult[0]['plasma_conservation_temperature']."</plasma_conservation_temperature>\n";
                $xmlData.="<duration_of_conservation>".$vlResult[0]['duration_of_conservation']."</duration_of_conservation>\n";
                $xmlData.="<date_of_demand>".$vlResult[0]['date_of_demand']."</date_of_demand>\n";
                $xmlData.="<viral_load_no>".$vlResult[0]['viral_load_no']."</viral_load_no>\n";
                $xmlData.="<date_dispatched_from_clinic_to_lab>".$vlResult[0]['date_dispatched_from_clinic_to_lab']."</date_dispatched_from_clinic_to_lab>\n";
                $xmlData.="<date_of_completion_of_viral_load>".$vlResult[0]['date_of_completion_of_viral_load']."</date_of_completion_of_viral_load>\n";
                $xmlData.="<date_result_printed>".$vlResult[0]['date_result_printed']."</date_result_printed>\n";
                $xmlData.="<result_coming_from>".$vlResult[0]['result_coming_from']."</result_coming_from>\n";
                $xmlData.="</vl_request_form>\n";
                $xmlData .="</vl_request>";
               //echo $xmlData;
               // die;
               $fileName = 'vl-test-request-' . date('d-M-Y-H-i-s') . '.xml';
               $configQuery ="SELECT value FROM global_config where name='sync_path'";
               $configResult = $db->rawQuery($configQuery);
               if(isset($configResult[0]['value']) && trim($configResult[0]['value'])!= '' && file_exists($configResult[0]['value'])){
                    if(!file_exists($configResult[0]['value'] . DIRECTORY_SEPARATOR . "request")){
                         mkdir($configResult[0]['value'] . DIRECTORY_SEPARATOR . "request");
                    }
                    if(!file_exists($configResult[0]['value'] . DIRECTORY_SEPARATOR . "request" . DIRECTORY_SEPARATOR . "new")){
                       mkdir($configResult[0]['value'] . DIRECTORY_SEPARATOR . "request" . DIRECTORY_SEPARATOR . "new");  
                    }
                    if(!file_exists($configResult[0]['value'] . DIRECTORY_SEPARATOR . "request" . DIRECTORY_SEPARATOR . "synced")){
                       mkdir($configResult[0]['value'] . DIRECTORY_SEPARATOR . "request" . DIRECTORY_SEPARATOR . "synced"); 
                    }
                    if(!file_exists($configResult[0]['value'] . DIRECTORY_SEPARATOR . "request" . DIRECTORY_SEPARATOR . "error")){
                       mkdir($configResult[0]['value'] . DIRECTORY_SEPARATOR . "request" . DIRECTORY_SEPARATOR . "error"); 
                    }
                    
                    $fp = fopen($configResult[0]['value'] . DIRECTORY_SEPARATOR . "request" . DIRECTORY_SEPARATOR . "new". DIRECTORY_SEPARATOR. $fileName, 'w+');
                    fwrite($fp, $xmlData);
                    fclose($fp);
               }
          }
          //Add event log
          $eventType = 'update-vl-request-zam';
          $action = ucwords($_SESSION['userName']).' updated a request data with the sample code '.$_POST['sampleCode'];
          $resource = 'vl-request-zam';
          $data=array(
          'event_type'=>$eventType,
          'action'=>$action,
          'resource'=>$resource,
          'date_time'=>$general->getDateTime()
          );
          $db->insert($tableName1,$data);
          header("location:vlRequest.php");
     }else{
          header("location:vlRequest.php");
          $_SESSION['alertMsg']="Please try again later";
     }
          
} catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}