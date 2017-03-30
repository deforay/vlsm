<?php
ob_start();
session_start();
include('../includes/MysqliDb.php');
include('../General.php');
$general=new Deforay_Commons_General();
$tableName="temp_sample_report";
$tableName1="vl_request_form";
$tableName2="hold_sample_report";
try {
    $cSampleQuery="SELECT * FROM global_config";
    $cSampleResult=$db->query($cSampleQuery);
    $arr = array();
    // now we create an associative array so that we can easily create view variables
    for ($i = 0; $i < sizeof($cSampleResult); $i++) {
      $arr[$cSampleResult[$i]['name']] = $cSampleResult[$i]['value'];
    }
    $instanceQuery="SELECT * FROM vl_instance";
    $instanceResult=$db->query($instanceQuery);
    $result ='';
    $id= explode(",",$_POST['value']);
    $status= explode(",",$_POST['status']);
    if($_POST['value']!=''){
    for($i=0;$i<count($id);$i++){
            $sQuery="SELECT * FROM temp_sample_report where temp_sample_id='".$id[$i]."'";
            $rResult = $db->rawQuery($sQuery);
            
            if(isset($rResult[0]['comments']) && $rResult[0]['comments'] != ""){
                $comments = $rResult[0]['comments'] ;//
                
                if($_POST['comments'] != ""){
                    $comments .=" - " .$_POST['comments'];
                }
            }else{
                $comments = $_POST['comments'];
            }
            
            
            $data=array(
                        'lab_name'=>$rResult[0]['lab_name'],
                        'lab_contact_person'=>$rResult[0]['lab_contact_person'],
                        'lab_phone_no'=>$rResult[0]['lab_phone_no'],
                        'date_sample_received_at_testing_lab'=>$rResult[0]['date_sample_received_at_testing_lab'],
                        'lab_tested_date'=>$rResult[0]['lab_tested_date'],
                        'date_results_dispatched'=>$rResult[0]['date_results_dispatched'],
                        'result_reviewed_date'=>$rResult[0]['result_reviewed_date'],
                        'result_reviewed_by'=>$_POST['reviewedBy'],
                        'vl_test_platform'=>$rResult[0]['vl_test_platform'],
                        'comments'=>$comments,
                        'log_value'=>$rResult[0]['log_value'],
                        'absolute_value'=>$rResult[0]['absolute_value'],
                        'text_value'=>$rResult[0]['text_value'],
                        'absolute_decimal_value'=>$rResult[0]['absolute_decimal_value'],
                        'result'=>$rResult[0]['result'],
                        'lab_tested_date'=>$rResult[0]['lab_tested_date'],
                        'lab_id'=>$rResult[0]['lab_id'],
                        'file_name'=>$rResult[0]['file_name'],
                        'result_coming_from'=>'report'
                    );
            if($status[$i]=='1'){
                $data['result_reviewed_by']=$_POST['reviewedBy'];
               $data['facility_id']=$rResult[0]['facility_id'];
               $data['sample_code']=$rResult[0]['sample_code'];
               $data['batch_code']=$rResult[0]['batch_code'];
                $data['modified_by']=$rResult[0]['result_reviewed_by'];
                $data['modified_on']=$general->getDateTime();               
               $data['result_status']=$status[$i];
               $data['import_batch_tracking']=$_SESSION['controllertrack'];
               $result = $db->insert($tableName2,$data);
            }else{
                $data['created_by']=$rResult[0]['result_reviewed_by'];
                $data['created_on']=$general->getDateTime();
                $data['modified_by']=$rResult[0]['result_reviewed_by'];
                $data['modified_on']=$general->getDateTime();
                $data['result_approved_by']=$_POST['appBy'];
                $data['result_approved_on']=$general->getDateTime();
                $sampleVal = $rResult[0]['sample_code'];
                if($rResult[0]['absolute_value']!=''){
                    $data['result'] = $rResult[0]['absolute_value'];
                }else if($rResult[0]['log_value']!=''){
                    $data['result'] = $rResult[0]['log_value'];
                }else if($rResult[0]['text_value']!=''){
                    $data['result'] = $rResult[0]['text_value'];
                }
                //get bacth code
                $bquery="select * from batch_details where batch_code='".$rResult[0]['batch_code']."'";
                $bvlResult=$db->rawQuery($bquery);
                if($bvlResult){
                    $data['batch_id'] = $bvlResult[0]['batch_id'];
                }else{
                    $batchResult = $db->insert('batch_details',array('batch_code'=>$rResult[0]['batch_code'],'batch_code_key'=>$rResult[0]['batch_code_key'],'sent_mail'=>'no','created_on'=>$general->getDateTime()));
                    $data['batch_id'] = $db->getInsertId();
                }
                $query="select vl_sample_id,result from vl_request_form where sample_code='".$sampleVal."'";
                $vlResult=$db->rawQuery($query);
                $data['result_status']=$_POST['status'];
                $data['serial_no']=$rResult[0]['sample_code'];
                if(count($vlResult)>0){
                    $data['form_id']=$arr['vl_form'];
                    $db=$db->where('sample_code',$rResult[0]['sample_code']);
                    $result=$db->update($tableName1,$data);
                }else{
                    $data['sample_code']=$rResult[0]['sample_code'];
                    $data['form_id']=$arr['vl_form'];
                    $data['vl_instance_id'] = $instanceResult[0]['vl_instance_id'];
                    $db->insert($tableName1,$data);
                }
            }
            $db=$db->where('temp_sample_id',$id[$i]);
            $result=$db->delete($tableName);
    }
        if (!file_exists('../uploads'. DIRECTORY_SEPARATOR . "import-result". DIRECTORY_SEPARATOR . $rResult[0]['file_name'])) {
            copy('../temporary'. DIRECTORY_SEPARATOR ."import-result". DIRECTORY_SEPARATOR.$rResult[0]['file_name'], '../uploads'. DIRECTORY_SEPARATOR ."import-result" . DIRECTORY_SEPARATOR . $rResult[0]['file_name']);
        }
    }
    //get all accepted data result
    $accQuery="SELECT * FROM temp_sample_report where status='7'";
    $accResult = $db->rawQuery($accQuery);
    if($accResult){
    for($i = 0;$i<count($accResult);$i++){
        $data=array(
                        'lab_name'=>$accResult[$i]['lab_name'],
                        'lab_contact_person'=>$accResult[$i]['lab_contact_person'],
                        'lab_phone_no'=>$accResult[$i]['lab_phone_no'],
                        'date_sample_received_at_testing_lab'=>$accResult[$i]['date_sample_received_at_testing_lab'],
                        'lab_tested_date'=>$accResult[$i]['lab_tested_date'],
                        'date_results_dispatched'=>$accResult[$i]['date_results_dispatched'],
                        'result_reviewed_date'=>$accResult[$i]['result_reviewed_date'],
                        'result_reviewed_by'=>$_POST['reviewedBy'],
                        'comments'=>$_POST['comments'],
                        'log_value'=>$accResult[$i]['log_value'],
                        'absolute_value'=>$accResult[$i]['absolute_value'],
                        'text_value'=>$accResult[$i]['text_value'],
                        'absolute_decimal_value'=>$accResult[$i]['absolute_decimal_value'],
                        'result'=>$accResult[$i]['result'],
                        'lab_tested_date'=>$accResult[$i]['lab_tested_date'],
                        'lab_id'=>$accResult[$i]['lab_id'],
                        'created_by'=>$accResult[$i]['result_reviewed_by'],
                        'created_on'=>$general->getDateTime(),
                        'modified_on'=>$general->getDateTime(),
                        'result_approved_by'=>$_POST['appBy'],
                        'result_approved_on'=>$general->getDateTime(),
                        'file_name'=>$accResult[$i]['file_name'],
                        'result_coming_from'=>'report',
                        'result_status'=>'7',
                        'vl_test_platform'=>$accResult[$i]['vl_test_platform'],
                    );
                if($accResult[$i]['absolute_value']!=''){
                    $data['result'] = $accResult[$i]['absolute_value'];
                }else if($accResult[$i]['log_value']!=''){
                    $data['result'] = $accResult[$i]['log_value'];
                }else if($accResult[$i]['text_value']!=''){
                    $data['result'] = $accResult[$i]['text_value'];
                }
            //get bacth code
                $bquery="select * from batch_details where batch_code='".$accResult[$i]['batch_code']."'";
                $bvlResult=$db->rawQuery($bquery);
                if($bvlResult){
                    $data['batch_id'] = $bvlResult[0]['batch_id'];
                }else{
                    $batchResult = $db->insert('batch_details',array('batch_code'=>$accResult[$i]['batch_code'],'batch_code_key'=>$accResult[$i]['batch_code_key'],'sent_mail'=>'no','created_on'=>$general->getDateTime()));
                    $data['batch_id'] = $db->getInsertId();
                }
                $db=$db->where('sample_code',$accResult[$i]['sample_code']);
                $result=$db->update($tableName1,$data);
                if (!file_exists('../uploads'. DIRECTORY_SEPARATOR . "import-result". DIRECTORY_SEPARATOR . $accResult[$i]['file_name'])) {
                    copy('../temporary'. DIRECTORY_SEPARATOR ."import-result" . DIRECTORY_SEPARATOR . $accResult[$i]['file_name'], '../uploads'. DIRECTORY_SEPARATOR ."import-result" . DIRECTORY_SEPARATOR . $accResult[$i]['file_name']);
                }
                $db=$db->where('temp_sample_id',$accResult[$i]['temp_sample_id']);
                $result=$db->delete($tableName);
        
    }
    }
    
    $stQuery="SELECT * FROM temp_sample_report where sample_type='s'";
    $stResult = $db->rawQuery($stQuery);
    if($stResult){
    }else{
        $result = "vlPrintResult.php";
    }
}
catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}
echo $result;