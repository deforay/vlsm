<?php
ob_start();
session_start();
include('../includes/MysqliDb.php');
include('../General.php');
$general=new Deforay_Commons_General();
$tableName="temp_sample_report";
$tableName1="vl_request_form";
$tableName2="hold_sample_report";
//print_r($_POST);die;
try {
    $cSampleQuery="SELECT * FROM global_config";
    $cSampleResult=$db->query($cSampleQuery);
    $arr = array();
    $printSampleCode = array();
    // now we create an associative array so that we can easily create view variables
    for ($i = 0; $i < sizeof($cSampleResult); $i++) {
      $arr[$cSampleResult[$i]['name']] = $cSampleResult[$i]['value'];
    }
    $import_decided = (isset($arr['import_non_matching_sample']) && $arr['import_non_matching_sample'] == 'no')?'INNER JOIN':'LEFT JOIN';
    $instanceQuery="SELECT * FROM vl_instance";
    $instanceResult=$db->query($instanceQuery);
    $result ='';
    $id= explode(",",$_POST['value']);
    $status= explode(",",$_POST['status']);
    if($_POST['value']!=''){
        for($i=0;$i<count($id);$i++){
            $sQuery="SELECT * FROM temp_sample_report where temp_sample_id='".$id[$i]."'";
            $rResult = $db->rawQuery($sQuery);
            
            if(isset($rResult[0]['approver_comments']) && $rResult[0]['approver_comments'] != ""){
                $comments = $rResult[0]['approver_comments'] ;//
                if($_POST['comments'] != ""){
                    $comments .=" - " .$_POST['comments'];
                }
            }else{
                $comments = $_POST['comments'];
            }
            
            $data=array(
                        'lab_name'=>$rResult[0]['lab_name'],
                        'lab_contact_person'=>$rResult[0]['lab_contact_person'],
                        'lab_phone_number'=>$rResult[0]['lab_phone_number'],
                        'sample_received_at_vl_lab_datetime'=>$rResult[0]['sample_received_at_vl_lab_datetime'],
                        //'sample_tested_datetime'=>$rResult[0]['sample_tested_datetime'],
                        'result_dispatched_datetime'=>$rResult[0]['result_dispatched_datetime'],
                        'result_reviewed_datetime'=>$rResult[0]['result_reviewed_datetime'],
                        'result_reviewed_by'=>$_POST['reviewedBy'],
                        'vl_test_platform'=>$rResult[0]['vl_test_platform'],
                        'import_machine_name'=>$rResult[0]['import_machine_name'],
                        'approver_comments'=>$comments,
                        'lot_number'=>$rResult[0]['lot_number'],
                        'lot_expiration_date'=>$rResult[0]['lot_expiration_date'],
                        'result_value_log'=>$rResult[0]['result_value_log'],
                        'result_value_absolute'=>$rResult[0]['result_value_absolute'],
                        'result_value_text'=>$rResult[0]['result_value_text'],
                        'result_value_absolute_decimal'=>$rResult[0]['result_value_absolute_decimal'],
                        'result'=>$rResult[0]['result'],
                        'sample_tested_datetime'=>$rResult[0]['sample_tested_datetime'],
                        'lab_id'=>$rResult[0]['lab_id'],
                        'import_machine_file_name'=>$rResult[0]['import_machine_file_name'],
                        'manual_result_entry'=>'no'
                    );
            if($status[$i]=='1'){
                $data['result_reviewed_by']=$_POST['reviewedBy'];
               $data['facility_id']=$rResult[0]['facility_id'];
               $data['sample_code']=$rResult[0]['sample_code'];
               $data['batch_code']=$rResult[0]['batch_code'];
                //$data['last_modified_by']=$rResult[0]['result_reviewed_by'];
                //$data['last_modified_datetime']=$general->getDateTime();               
               $data['status']=$status[$i];
               $data['import_batch_tracking']=$_SESSION['controllertrack'];
               $result = $db->insert($tableName2,$data);
            }else{
                $data['request_created_by']=$rResult[0]['result_reviewed_by'];
                $data['request_created_datetime']=$general->getDateTime();
                $data['last_modified_by']=$rResult[0]['result_reviewed_by'];
                $data['last_modified_datetime']=$general->getDateTime();
                $data['result_approved_by']=$_POST['appBy'];
                $data['result_approved_datetime']=$general->getDateTime();
                $sampleVal = $rResult[0]['sample_code'];
                if($rResult[0]['result_value_absolute']!=''){
                    $data['result'] = $rResult[0]['result_value_absolute'];
                }else if($rResult[0]['result_value_log']!=''){
                    $data['result'] = $rResult[0]['result_value_log'];
                }else if($rResult[0]['result_value_text']!=''){
                    $data['result'] = $rResult[0]['result_value_text'];
                }
                //get bacth code
                $bquery="select * from batch_details where batch_code='".$rResult[0]['batch_code']."'";
                $bvlResult=$db->rawQuery($bquery);
                if($bvlResult){
                    $data['sample_batch_id'] = $bvlResult[0]['batch_id'];
                }else{
                    $batchResult = $db->insert('batch_details',array('batch_code'=>$rResult[0]['batch_code'],'batch_code_key'=>$rResult[0]['batch_code_key'],'sent_mail'=>'no','request_created_datetime'=>$general->getDateTime()));
                    $data['sample_batch_id'] = $db->getInsertId();
                }
                $query="select vl_sample_id,result from vl_request_form where sample_code='".$sampleVal."'";
                $vlResult=$db->rawQuery($query);
                $data['result_status']=$status[$i];
                $data['serial_no']=$rResult[0]['sample_code'];
                if(count($vlResult)>0){
                    $data['vlsm_country_id']=$arr['vl_form'];
                    $db=$db->where('sample_code',$rResult[0]['sample_code']);
                    $result=$db->update($tableName1,$data);
                }else{
                    $data['sample_code']=$rResult[0]['sample_code'];
                    $data['vlsm_country_id']=$arr['vl_form'];
                    $data['vlsm_instance_id'] = $instanceResult[0]['vlsm_instance_id'];
                    $db->insert($tableName1,$data);
                }
                $printSampleCode[] = "'".$rResult[0]['sample_code']."'";
            }
            
            $db=$db->where('temp_sample_id',$id[$i]);
            $result=$db->update($tableName,array('temp_sample_status'=>1));
        }
        if(!file_exists('../uploads'. DIRECTORY_SEPARATOR . "import-result". DIRECTORY_SEPARATOR . $rResult[0]['import_machine_file_name'])) {
            copy('../temporary'. DIRECTORY_SEPARATOR ."import-result". DIRECTORY_SEPARATOR. $rResult[0]['import_machine_file_name'], '../uploads'. DIRECTORY_SEPARATOR ."import-result" . DIRECTORY_SEPARATOR . $rResult[0]['import_machine_file_name']);
        }
    }
    //get all accepted data result
    $accQuery="SELECT * FROM temp_sample_report as tsr $import_decided vl_request_form as vl ON vl.sample_code=tsr.sample_code where tsr.result_status='7'";
    $accResult = $db->rawQuery($accQuery);
    if($accResult){
        for($i = 0;$i<count($accResult);$i++){
            $data=array(
                            'lab_name'=>$accResult[$i]['lab_name'],
                            'lab_contact_person'=>$accResult[$i]['lab_contact_person'],
                            'lab_phone_number'=>$accResult[$i]['lab_phone_number'],
                            'sample_received_at_vl_lab_datetime'=>$accResult[$i]['sample_received_at_vl_lab_datetime'],
                            //'sample_tested_datetime'=>$accResult[$i]['sample_tested_datetime'],
                            'result_dispatched_datetime'=>$accResult[$i]['result_dispatched_datetime'],
                            'result_reviewed_datetime'=>$accResult[$i]['result_reviewed_datetime'],
                            'result_reviewed_by'=>$_POST['reviewedBy'],
                            'approver_comments'=>$_POST['comments'],
                            'lot_number'=>$accResult[$i]['lot_number'],
                            'lot_expiration_date'=>$accResult[$i]['lot_expiration_date'],
                            'result_value_log'=>$accResult[$i]['result_value_log'],
                            'result_value_absolute'=>$accResult[$i]['result_value_absolute'],
                            'result_value_text'=>$accResult[$i]['result_value_text'],
                            'result_value_absolute_decimal'=>$accResult[$i]['result_value_absolute_decimal'],
                            'result'=>$accResult[$i]['result'],
                            'sample_tested_datetime'=>$accResult[$i]['sample_tested_datetime'],
                            'lab_id'=>$accResult[$i]['lab_id'],
                            'request_created_by'=>$accResult[$i]['result_reviewed_by'],
                            'request_created_datetime'=>$general->getDateTime(),
                            'last_modified_datetime'=>$general->getDateTime(),
                            'result_approved_by'=>$_POST['appBy'],
                            'result_approved_datetime'=>$general->getDateTime(),
                            'import_machine_file_name'=>$accResult[$i]['import_machine_file_name'],
                            'manual_result_entry'=>'no',
                            'result_status'=>'7',
                            'vl_test_platform'=>$accResult[$i]['vl_test_platform'],
                            'import_machine_name'=>$accResult[$i]['import_machine_name'],
                        );
                    if($accResult[$i]['result_value_absolute']!=''){
                        $data['result'] = $accResult[$i]['result_value_absolute'];
                    }else if($accResult[$i]['result_value_log']!=''){
                        $data['result'] = $accResult[$i]['result_value_log'];
                    }else if($accResult[$i]['result_value_text']!=''){
                        $data['result'] = $accResult[$i]['result_value_text'];
                    }
                //get bacth code
                    $bquery="select * from batch_details where batch_code='".$accResult[$i]['batch_code']."'";
                    $bvlResult=$db->rawQuery($bquery);
                    if($bvlResult){
                        $data['sample_batch_id'] = $bvlResult[0]['batch_id'];
                    }else{
                        $batchResult = $db->insert('batch_details',array('batch_code'=>$accResult[$i]['batch_code'],'batch_code_key'=>$accResult[$i]['batch_code_key'],'sent_mail'=>'no','request_created_datetime'=>$general->getDateTime()));
                        $data['sample_batch_id'] = $db->getInsertId();
                    }
                    $db=$db->where('sample_code',$accResult[$i]['sample_code']);
                    $result=$db->update($tableName1,$data);
                    $printSampleCode[] = "'".$accResult[$i]['sample_code']."'";
                    if (!file_exists('../uploads'. DIRECTORY_SEPARATOR . "import-result". DIRECTORY_SEPARATOR . $accResult[$i]['import_machine_file_name'])) {
                        copy('../temporary'. DIRECTORY_SEPARATOR ."import-result" . DIRECTORY_SEPARATOR . $accResult[$i]['import_machine_file_name'], '../uploads'. DIRECTORY_SEPARATOR ."import-result" . DIRECTORY_SEPARATOR . $accResult[$i]['import_machine_file_name']);
                    }
                    $db=$db->where('temp_sample_id',$accResult[$i]['temp_sample_id']);
                    $result=$db->update($tableName,array('temp_sample_status'=>1));
        }
    }
    $sCode = implode( ', ', $printSampleCode);
    $samplePrintQuery = "SELECT vl.*,s.sample_name,b.*,ts.*,f.facility_name,l_f.facility_name as labName,f.facility_code,f.facility_state,f.facility_district,acd.art_code,rst.sample_name as routineSampleName,fst.sample_name as failureSampleName,sst.sample_name as suspectedSampleName,u_d.user_name as reviewedBy,a_u_d.user_name as approvedBy ,rs.rejection_reason_name FROM vl_request_form as vl LEFT JOIN facility_details as f ON vl.facility_id=f.facility_id LEFT JOIN facility_details as l_f ON vl.lab_id=l_f.facility_id LEFT JOIN r_sample_type as s ON s.sample_id=vl.sample_type INNER JOIN r_sample_status as ts ON ts.status_id=vl.result_status LEFT JOIN r_art_code_details as acd ON acd.art_id=vl.current_regimen LEFT JOIN r_sample_type as rst ON rst.sample_id=vl.last_vl_sample_type_routine LEFT JOIN r_sample_type as fst ON fst.sample_id=vl.last_vl_sample_type_failure_ac LEFT JOIN r_sample_type as sst ON sst.sample_id=vl.last_vl_sample_type_failure LEFT JOIN batch_details as b ON b.batch_id=vl.sample_batch_id LEFT JOIN user_details as u_d ON u_d.user_id=vl.result_reviewed_by LEFT JOIN user_details as a_u_d ON a_u_d.user_id=vl.result_approved_by LEFT JOIN r_sample_rejection_reasons as rs ON rs.rejection_reason_id=vl.reason_for_sample_rejection";    
    $samplePrintQuery .= ' where vl.sample_code IN ( ' . $sCode . ')'; // Append to condition
    $_SESSION['vlRequestSearchResultQuery'] = $samplePrintQuery;
    $stQuery="SELECT * FROM temp_sample_report as tsr $import_decided vl_request_form as vl ON vl.sample_code=tsr.sample_code where sample_type='s'";
    $stResult = $db->rawQuery($stQuery);
    if(!$stResult){
        $result = "importedStatistics.php";
    }
   echo $result;
}
catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}