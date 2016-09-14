<?php
ob_start();
session_start();
include('./includes/MysqliDb.php');
//include('header.php');
include ('./includes/PHPExcel.php');
include('General.php');
$confFileName=base64_decode($_POST['machineName']);

include("import-configs".DIRECTORY_SEPARATOR.$confFileName);

//$query="select treament_id,sample_code from vl_request_form";
//$vlResult=$db->rawQuery($query);
$configQuery="SELECT * from global_config";
    $configResult=$db->query($configQuery);
    $arr = array();
    // now we create an associative array so that we can easily create view variables
    for ($i = 0; $i < sizeof($configResult); $i++) {
      $arr[$configResult[$i]['name']] = $configResult[$i]['value'];
    }
$general=new Deforay_Commons_General();

$tableName="temp_sample_report";

try {
        //$configId=base64_decode($_POST['machineName']);
        //$query="SELECT * FROM import_config where status='active' AND config_id=".$configId;
        //$cResult = $db->rawQuery($query);
        
            
            //$sampleIdCol=$cResult[0]['sample_id_col'];
            //$sampleIdRow=$cResult[0]['sample_id_row'];   
            $db->delete('temp_sample_report');
            //set session for controller track id in hold_sample_record table
            $cQuery="select MAX(import_batch_tracking) FROM hold_sample_report";
            $cResult=$db->query($cQuery);
            //print_r($sResult[0]['MAX(treament_id)']);die;
            if($cResult[0]['MAX(import_batch_tracking)']!=''){
             $maxId = $cResult[0]['MAX(import_batch_tracking)']+1;
            }else{
             $maxId = 1;
            }
            $_SESSION['controllertrack'] = $maxId;
            
            if(isset($_POST['sampleReceivedDate']) && trim($_POST['sampleReceivedDate'])!=""){
                $sampleDate = explode(" ",$_POST['sampleReceivedDate']);
                $_POST['sampleReceivedDate']=$general->dateFormat($sampleDate[0])." ".$sampleDate[1];
            }
            if(isset($_POST['testingDate']) && trim($_POST['testingDate'])!=""){
                $testDate = explode(" ",$_POST['testingDate']);
                $_POST['testingDate']=$general->dateFormat($testDate[0])." ".$testDate[1];
            }
            
            if(isset($_POST['dispatchedDate']) && trim($_POST['dispatchedDate'])!=""){
                $dispatchDate = explode(" ",$_POST['dispatchedDate']);
                $_POST['dispatchedDate']=$general->dateFormat($dispatchDate[0])." ".$dispatchDate[1];
            }
            
            if(isset($_POST['reviewedDate']) && trim($_POST['reviewedDate'])!=""){
                $reviewDate = explode(" ",$_POST['reviewedDate']);
                $_POST['reviewedDate']=$general->dateFormat($reviewDate[0])." ".$reviewDate[1];
            }
            
            $allowedExtensions = array('xls', 'xlsx', 'csv');
            $fileName = preg_replace('/[^A-Za-z0-9.]/', '-', $_FILES['resultFile']['name']);
            $fileName = str_replace(" ", "-", $fileName);
            $ranNumber = str_pad(rand(0, pow(10, 6)-1), 6, '0', STR_PAD_LEFT);
            $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $fileName =$ranNumber.".".$extension;
    
            if (!file_exists('uploads'. DIRECTORY_SEPARATOR . "import-result") && !is_dir('uploads'. DIRECTORY_SEPARATOR."import-result")) {
                mkdir('uploads'. DIRECTORY_SEPARATOR."import-result");
            }
            if (move_uploaded_file($_FILES['resultFile']['tmp_name'], 'uploads'. DIRECTORY_SEPARATOR ."import-result" . DIRECTORY_SEPARATOR . $fileName)) {
               
               
                $objPHPExcel = \PHPExcel_IOFactory::load('uploads'. DIRECTORY_SEPARATOR ."import-result" . DIRECTORY_SEPARATOR . $fileName);
                $sheetData = $objPHPExcel->getActiveSheet();
                
                
                //$sheetData = $objPHPExcel->getActiveSheet()->toArray(null, true, true, true);
                //$count = count($sheetData);
                $m=0;
                foreach($sheetData->getRowIterator() as $rKey=>$row){
                    if($rKey < 2) continue;
                    
                    $absDecimalVal="";
                    $absVal="";
                    $logVal="";
                    $txtVal="";
                    $resultFlag="";
                    $testingDate="";
                    foreach($row->getCellIterator() as $key => $cell)
                    {
                        $cellName = $sheetData->getCellByColumnAndRow($key,$rKey)->getColumn();
                        $cellRow = $sheetData->getCellByColumnAndRow($key,$rKey)->getRow();
                                                
                        fetchValuesFromFile($sampleVal,$logVal,$absVal,$txtVal,$absDecimalVal,$resultFlag,$testingDate,$sampleType,$batchCode,$rKey,$cellName,$cell);
                    }
                    //echo $cellRow;
                    //if($sampleVal!=''){
                    //check sample exist
                    //$sampleTypeQuery="SELECT * FROM r_sample_type where sample_name='".$sampleType."' AND form_identification='".$arr['vl_form']."'";
                    //$sampleTypeResult = $db->rawQuery($sampleTypeQuery);
                    //if($sampleTypeResult){
                     //$sampleType =  $sampleTypeResult[0]['sample_id'];
                    //}else{
                        //$sampleData=array('sample_name'=>$sampleType,'form_identification'=>$arr['vl_form']);
                        //$db->insert('r_sample_type',$sampleData);
                        //$lastId = $db->getInsertId();
                        //$sampleType = $lastId;
                    //}
                    
                    $data=array(
                        'lab_id'=>$_POST['labId'],
                        'lab_name'=>$_POST['labName'],
                        'lab_contact_person'=>$_POST['labContactPerson'],
                        'lab_phone_no'=>$_POST['labPhoneNo'],
                        'date_sample_received_at_testing_lab'=>$_POST['sampleReceivedDate'],
                        'lab_tested_date'=>$_POST['testingDate'],
                        'date_results_dispatched'=>$_POST['dispatchedDate'],
                        'result_reviewed_date'=>$_POST['reviewedDate'],
                        'result_reviewed_by'=>$_SESSION['userId'],
                        'comments'=>$_POST['comments'],
                        'sample_code'=>$sampleVal,
                        'batch_code'=>$batchCode,
                        'sample_type'=>$sampleType,
                        'log_value'=>$logVal,
                        'absolute_value'=>$absVal,
                        'text_value'=>$txtVal,
                        'absolute_decimal_value'=>$absDecimalVal,
                        'result'=>$resultFlag,
                        'lab_tested_date'=>$testingDate,
                        'status'=>'6'
                    );
                    
                    $query="select facility_id,treament_id,result,log_value,absolute_value,text_value,absolute_decimal_value from vl_request_form where sample_code='".$sampleVal."'";
                    $vlResult=$db->rawQuery($query);
                    if($vlResult){
                        if($vlResult[0]['log_value']!='' || $vlResult[0]['absolute_value']!='' || $vlResult[0]['text_value']!='' || $vlResult[0]['absolute_decimal_value']!=''){
                            $data['sample_details'] = 'Already Result Exist';
                        }
                        $data['facility_id'] = $vlResult[0]['facility_id'];
                    }else{
                        $data['sample_details'] = 'New Sample';
                    }
                    if($sampleVal!='' || $batchCode!='' || $sampleType!='' || $logVal!='' || $absVal!='' || $absDecimalVal!=''){
                    $db->insert($tableName,$data);
                    }
                    //if(isset($vlResult[$m]['sample_code'])){
                    //$db=$db->where('sample_code',$sampleVal);
                    ////$db=$db->where('sample_code',$vlResult[$m]['sample_code']);
                    //$id=$db->update($tableName,$data);
                    //}
                   // }
                    $m++;
                }
            }
            
        $_SESSION['alertMsg']="Imported results successfully";
        header("location:vlResultUnApproval.php");
  
} catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}