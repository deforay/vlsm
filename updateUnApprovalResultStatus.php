<?php
ob_start();
session_start();
include('./includes/MysqliDb.php');
include('General.php');
$general=new Deforay_Commons_General();
$tableName="temp_sample_report";
$tableName1="vl_request_form";
$tableName2="hold_sample_report";
try {
    $id= explode(",",$_POST['value']);
    
    for($i=0;$i<count($id);$i++){
            $sQuery="SELECT * FROM temp_sample_report where temp_sample_id='".$id[$i]."'";
            $rResult = $db->rawQuery($sQuery);
            $data=array(
                        'lab_name'=>$rResult[0]['lab_name'],
                        'lab_contact_person'=>$rResult[0]['lab_contact_person'],
                        'lab_phone_no'=>$rResult[0]['lab_phone_no'],
                        'date_sample_received_at_testing_lab'=>$rResult[0]['date_sample_received_at_testing_lab'],
                        'lab_tested_date'=>$rResult[0]['lab_tested_date'],
                        'date_results_dispatched'=>$rResult[0]['date_results_dispatched'],
                        'result_reviewed_date'=>$rResult[0]['result_reviewed_date'],
                        'result_reviewed_by'=>$rResult[0]['result_reviewed_by'],
                        'comments'=>$rResult[0]['comments'],
                        'log_value'=>$rResult[0]['log_value'],
                        'absolute_value'=>$rResult[0]['absolute_value'],
                        'text_value'=>$rResult[0]['text_value'],
                        'absolute_decimal_value'=>$rResult[0]['absolute_decimal_value'],
                        'result'=>$rResult[0]['result'],
                        'lab_tested_date'=>$rResult[0]['lab_tested_date'],
                    );
            if($_POST['status']=='1'){
                $data['result_reviewed_by']=$rResult[0]['result_reviewed_by'];
               $data['facility_id']=$rResult[0]['facility_id'];
               $data['lab_id']=$rResult[0]['lab_id'];
               $data['sample_code']=$rResult[0]['sample_code'];
               $data['status']=$_POST['status'];
               $data['import_batch_tracking']=$_SESSION['controllertrack'];
               $result = $db->insert('hold_sample_report',$data);
            }else{
            $data['created_by']=$rResult[0]['result_reviewed_by'];
            $sampleVal = $rResult[0]['sample_code'];
            $query="select treament_id,result from vl_request_form where sample_code='".$sampleVal."'";
            $vlResult=$db->rawQuery($query);
            $data['status']=$_POST['status'];
            if(count($vlResult)>0){
                $db=$db->where('sample_code',$rResult[0]['sample_code']);
                $result=$db->update('vl_request_form',$data);
            }else{
                $data['sample_code']=$rResult[0]['sample_code'];
                $db->insert($tableName1,$data);
            }
            }
            $db=$db->where('temp_sample_id',$id[$i]);
            $result=$db->delete($tableName);
    }
}
catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}
echo $result;