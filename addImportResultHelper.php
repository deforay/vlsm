<?php
ob_start();
include('./includes/MysqliDb.php');
include('header.php');
include ('./includes/PHPExcel.php');
include('General.php');
$confFileName=base64_decode($_POST['machineName']);
include($confFileName.'.php');
$general=new Deforay_Commons_General();

$tableName="vl_request_form";


try {
    
    if(isset($_POST['machineName'])){
        //$configId=base64_decode($_POST['machineName']);
        //$query="SELECT * FROM import_config where status='active' AND config_id=".$configId;
        //$cResult = $db->rawQuery($query);
        $confResult=$myConf->getConfigurationVal();
        
        if(count($confResult)>0){
            $sampleIdCol=$confResult['sampleIdCol'];
            $sampleIdRow=$confResult['sampleIdRow'];
            $logValCol=$confResult['logValueCol'];
            $logValRow=$confResult['logValueRow'];
            $absValCol=$confResult['absoluteValueCol'];
            $absValRow=$confResult['absoluteValueRow'];
            $txtValCol=$confResult['textValueCol'];
            $txtValRow=$confResult['textValueRow'];
            $seperator=$confResult['seperator'];
            $logAndAbsoluteValInSameCol=$confResult['logAndAbsoluteValSameColumn'];
            
            //$sampleIdCol=$cResult[0]['sample_id_col'];
            //$sampleIdRow=$cResult[0]['sample_id_row'];
                        
            if(isset($_POST['sampleReceivedDate']) && trim($_POST['sampleReceivedDate'])!=""){
                $_POST['sampleReceivedDate']=$general->dateFormat($_POST['sampleReceivedDate']);
            }
            if(isset($_POST['testingDate']) && trim($_POST['testingDate'])!=""){
                $_POST['testingDate']=$general->dateFormat($_POST['testingDate']);
            }
            
            if(isset($_POST['dispatchedDate']) && trim($_POST['dispatchedDate'])!=""){
                $_POST['dispatchedDate']=$general->dateFormat($_POST['dispatchedDate']);
            }
            
            if(isset($_POST['reviewedDate']) && trim($_POST['reviewedDate'])!=""){
                $_POST['reviewedDate']=$general->dateFormat($_POST['reviewedDate']);
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
                
                foreach($sheetData->getRowIterator() as $rKey=>$row)
                {
                    $absVal="";
                    $logVal="";
                    $txtVal="";
                    $resultFlag="";
                    foreach($row->getCellIterator() as $key => $cell)
                    {
                        $cellName = $sheetData->getCellByColumnAndRow($key,$rKey)->getColumn();
                        //$columnLetter = PHPExcel_Cell::stringFromColumnIndex($key);
                        if($sampleIdCol==$cellName){
                            if($rKey>=$sampleIdRow){
                                $sampleVal=$cell->getCalculatedValue();
                            }
                        }
                        
                        if($logAndAbsoluteValInSameCol=='yes'){
                            if($logValCol==$cellName){
                                if($rKey>=$logValRow){
                                    if(trim($cell->getCalculatedValue())!=""){
                                        $resVal=explode("(",$cell->getCalculatedValue());
                                        if(count($resVal)==2){
                                            $absVal=trim($resVal[0]);
                                            $logVal=substr(trim($resVal[1]),0,-1);
                                        }else{
                                            $txtVal=trim($cell->getCalculatedValue());
                                            if($txtVal=='Invalid' || $txtVal=='Valid'){
                                                $resultFlag=trim($txtVal);
                                            }
                                        }
                                    }
                                    
                                }
                            }
                        }else{
                            if($logValCol==$cellName){
                                if($rKey>=$logValRow){
                                    $logVal=trim($cell->getCalculatedValue());
                                }
                            }
                            
                            if($absValCol==$cellName){
                                if($rKey>=$absValRow){
                                    $absVal=trim($cell->getCalculatedValue());
                                }
                            }
                            
                            if($txtValCol==$cellName){
                                if($rKey>=$txtValRow){
                                    $txtVal=trim($cell->getCalculatedValue());
                                    if($txtVal=='Invalid' || $txtVal=='Valid'){
                                        $resultFlag=trim($txtVal);
                                    }
                                }
                            }
                        }
                    }
                    //echo $sampleVal."<br/>";
                    //echo $absVal."<br/>";
                    
                    $data=array(
                        'lab_name'=>$_POST['labName'],
                        'lab_contact_person'=>$_POST['labContactPerson'],
                        'lab_phone_no'=>$_POST['labPhoneNo'],
                        'date_sample_received_at_testing_lab'=>$_POST['sampleReceivedDate'],
                        'lab_tested_date'=>$_POST['testingDate'],
                        'date_results_dispatched'=>$_POST['dispatchedDate'],
                        'result_reviewed_date'=>$_POST['reviewedDate'],
                        'result_reviewed_by'=>$_POST['reviewedBy'],
                        'comments'=>$_POST['comments'],
                        'log_value'=>$logVal,
                        'absolute_value'=>$absVal,
                        'text_value'=>$txtVal,
                        'result'=>$resultFlag
                    );
                    
                    $db=$db->where('sample_code',$sampleVal);
                    $id=$db->update($tableName,$data);
                }
            }
        }
        $db->insert($tableName,$data);    
    
        $_SESSION['alertMsg']="Import result details added successfully";
    }
    header("location:index.php");
  
} catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}