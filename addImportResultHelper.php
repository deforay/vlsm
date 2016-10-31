<?php
ob_start();
session_start();
include('./includes/MysqliDb.php');
//include('header.php');
include ('./includes/PHPExcel.php');
include('General.php');
$confFileName=base64_decode($_POST['machineName']);

include("import-configs".DIRECTORY_SEPARATOR.$confFileName);

//$query="select vl_sample_id,sample_code from vl_request_form";
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
$tableName1="activity_log";
$tableName2="log_result_updates";
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
            //print_r($sResult[0]['MAX(vl_sample_id)']);die;
            if($cResult[0]['MAX(import_batch_tracking)']!=''){
             $maxId = $cResult[0]['MAX(import_batch_tracking)']+1;
            }else{
             $maxId = 1;
            }
            $_SESSION['controllertrack'] = $maxId;
            
            /*
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
            */
            
            
            $allowedExtensions = array('xls', 'xlsx', 'csv');
            $fileName = preg_replace('/[^A-Za-z0-9.]/', '-', $_FILES['resultFile']['name']);
            $fileName = str_replace(" ", "-", $fileName);
            $ranNumber = str_pad(rand(0, pow(10, 6)-1), 6, '0', STR_PAD_LEFT);
            $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $fileName =$ranNumber.".".$extension;
    
                
    
    
    
            if (!file_exists('temporary'. DIRECTORY_SEPARATOR . "import-result") && !is_dir('temporary'. DIRECTORY_SEPARATOR."import-result")) {
                mkdir('temporary'. DIRECTORY_SEPARATOR."import-result");
            }
            if (move_uploaded_file($_FILES['resultFile']['tmp_name'], 'temporary'. DIRECTORY_SEPARATOR ."import-result" . DIRECTORY_SEPARATOR . $fileName)) {
               
               


            
            $file_info = new finfo(FILEINFO_MIME);	// object oriented approach!
            $mime_type = $file_info->buffer(file_get_contents('temporary'. DIRECTORY_SEPARATOR ."import-result" . DIRECTORY_SEPARATOR . $fileName));  // e.g. gives "image/jpeg"
            
           

                 
                 
                  $objPHPExcel = \PHPExcel_IOFactory::load('temporary'. DIRECTORY_SEPARATOR ."import-result" . DIRECTORY_SEPARATOR . $fileName);
                  $sheetData = $objPHPExcel->getActiveSheet();
                  
                  $bquery="select MAX(batch_code_key) from batch_details";
                  $bvlResult=$db->rawQuery($bquery);
                  if($bvlResult[0]['MAX(batch_code_key)']!='' && $bvlResult[0]['MAX(batch_code_key)']!=NULL){
                     $maxBatchCodeKey = $bvlResult[0]['MAX(batch_code_key)']+1;
                     $maxBatchCodeKey = "00".$maxBatchCodeKey;
                  }else{
                     $maxBatchCodeKey = '001';
                  }
                  $newBatchCode = date('Ymd').$maxBatchCodeKey;           
           
              
              if (strpos($mime_type, 'text/plain') !== false) {
                   
                   

                   
                  $infoFromFile = array();
                  $testDateRow = "";
                  $skip = 23;
                  
                                      
                   $row = 1;
                   if (($handle = fopen('temporary'. DIRECTORY_SEPARATOR ."import-result" . DIRECTORY_SEPARATOR . $fileName, "r")) !== FALSE) {
                       while (($sheetData = fgetcsv($handle, 1000, "\t")) !== FALSE) {
                           $num = count($sheetData);
                           //echo "<p> $num fields in line $row: <br /></p>\n";
                           
                           
                           $row++;
                           
                           if($row < $skip) continue;
                           //echo "<pre>";print_r($data);echo "</pre>"; continue;
                           //for ($c=0; $c < $num; $c++) {
                           //    echo $data[$c] . "<br />\n";
                           //}
                           
                          $sampleCode = "";
                          $batchCode = "";
                          $sampleType = "";
                          $absDecimalVal="";
                          $absVal="";
                          $logVal="";
                          $txtVal="";
                          $resultFlag="";
                          $testingDate="";
                          
                           
                           
                           fetchValuesFromFile($sampleCode,$logVal,$absVal,$txtVal,$absDecimalVal,$resultFlag,$testingDate,$sampleType,$batchCode,$sheetData);
                           
                           if(!isset($infoFromFile[$sampleCode])){
                            $infoFromFile[$sampleCode] = array(
                                                              
                                                              "sampleCode" => $sampleCode,
                                                              "absVal" => $absVal,
                                                              "txtVal" => $txtVal,
                                                              "absDecimalVal" => $absDecimalVal,
                                                              "resultFlag" => $resultFlag,
                                                              "testingDate" => $testingDate,
                                                              "sampleType" => $sampleType,
                                                              "batchCode" => $batchCode
                              
                                                               );
                           }else{
                             $infoFromFile[$sampleCode]['logVal'] = $logVal;
                           }
                           
                           
                           
                       }
                       fclose($handle);
                   }                 
                   
                   //echo "<pre>";
                   //var_dump($infoFromFile);
                   //die;
                   //
                   foreach($infoFromFile as $sampleCode => $d){
                    
                    $data=array(
                          'lab_id'=>base64_decode($_POST['labId']),
                          //'vl_test_platform'=>$_POST['machineName'],
                          'vl_test_platform'=>$_POST['vltestPlatform'],
                          'result_reviewed_by'=>$_SESSION['userId'],
                          'sample_code'=>$d['sampleCode'],
                          'log_value'=>$d['logVal'],
                          'sample_type'=>$d['sampleType'],
                          'absolute_value'=>$d['absVal'],
                          'text_value'=>$d['txtVal'],
                          'absolute_decimal_value'=>$d['absDecimalVal'],
                          //'result'=>$resultFlag,
                          'lab_tested_date'=>$testingDate,
                          'status'=>'6',
                          'file_name'=>$fileName,
                          'comments'=>$d['resultFlag']
                      );
                      
                      
                      if($d['absVal'] != ""){
                        $data['result'] = $d['absVal'];
                      }else if($d['logVal'] != ""){
                        $data['result'] = $d['logVal'];
                      }else if($d['txtVal'] != ""){
                        $data['result'] = $d['txtVal'];
                      }else{
                        $data['result'] = "";
                      }
                      
                      if($batchCode==''){
                          $data['batch_code']=$newBatchCode;
                          $data['batch_code_key']=$maxBatchCodeKey;
                      }else{
                          $data['batch_code']=$batchCode;
                      }
                      
                      $query="select facility_id,vl_sample_id,result,log_value,absolute_value,text_value,absolute_decimal_value from vl_request_form where sample_code='".$sampleCode."'";
                      $vlResult=$db->rawQuery($query);
                      if($vlResult && $sampleCode!=''){
                          if($vlResult[0]['log_value']!='' || $vlResult[0]['absolute_value']!='' || $vlResult[0]['text_value']!='' || $vlResult[0]['absolute_decimal_value']!=''){
                              $data['sample_details'] = 'Result exists already';
                          }else{
                              $data['status'] = '7';
                          }
                          $data['facility_id'] = $vlResult[0]['facility_id'];
                      }else{
                          $data['sample_details'] = 'New Sample';
                      }
                      
                      
                      if($sampleCode!='' || $batchCode!='' || $sampleType!='' || $logVal!='' || $absVal!='' || $absDecimalVal!=''){
                        $id = $db->insert($tableName,$data);
                      }                   
                   }
                   
                  //die;
                   
              }else{
              
                  //$sheetData = $objPHPExcel->getActiveSheet()->toArray(null, true, true, true);
                  //$count = count($sheetData);
                  $m=0;
                  foreach($sheetData->getRowIterator() as $rKey=>$row){
                      if($rKey < $skipTillRow) continue;
                      if($sheetData->getCell($orderNumberColumn.$rKey)->getValue() == "") break;
                      $sampleCode = "";
                      $batchCode = "";
                      $sampleType = "";
                      $absDecimalVal="";
                      $absVal="";
                      $logVal="";
                      $txtVal="";
                      $resultFlag="";
                      $testingDate="";
                      foreach($row->getCellIterator() as $key => $cell){
                          $cellName = $sheetData->getCellByColumnAndRow($key,$rKey)->getColumn();
                          $cellRow = $sheetData->getCellByColumnAndRow($key,$rKey)->getRow();
                                                  
                          fetchValuesFromFile($sampleCode,$logVal,$absVal,$txtVal,$absDecimalVal,$resultFlag,$testingDate,$sampleType,$batchCode,$key,$rKey,$cellName,$cell,$sheetData);
                      }
                      
                      //echo $cellRow;
                      $data=array(
                          'lab_id'=>base64_decode($_POST['labId']),
                          //'vl_test_platform'=>$_POST['machineName'],
                          'vl_test_platform'=>$_POST['vltestPlatform'],
                          'result_reviewed_by'=>$_SESSION['userId'],
                          'sample_code'=>$sampleCode,
                          'log_value'=>$logVal,
                          'sample_type'=>$sampleType,
                          'absolute_value'=>$absVal,
                          'text_value'=>$txtVal,
                          'absolute_decimal_value'=>$absDecimalVal,
                          //'result'=>$resultFlag,
                          'lab_tested_date'=>$testingDate,
                          'status'=>'6',
                          'file_name'=>$fileName,
                          'comments'=>$resultFlag
                      );
                      
                      
                      if($absVal != ""){
                        $data['result'] = $absVal;
                      }else if($logVal != ""){
                        $data['result'] = $logVal;
                      }else if($txtVal != ""){
                        $data['result'] = $txtVal;
                      }else{
                        $data['result'] = "";
                      }
                      
                      if($batchCode==''){
                          $data['batch_code']=$newBatchCode;
                          $data['batch_code_key']=$maxBatchCodeKey;
                      }else{
                          $data['batch_code']=$batchCode;
                      }
                      
                      $query="select facility_id,vl_sample_id,result,log_value,absolute_value,text_value,absolute_decimal_value from vl_request_form where sample_code='".$sampleCode."'";
                      $vlResult=$db->rawQuery($query);
                      if($vlResult && $sampleCode!=''){
                          if($vlResult[0]['log_value']!='' || $vlResult[0]['absolute_value']!='' || $vlResult[0]['text_value']!='' || $vlResult[0]['absolute_decimal_value']!=''){
                              $data['sample_details'] = 'Result exists already';
                          }else{
                              $data['status'] = '7';
                          }
                          $data['facility_id'] = $vlResult[0]['facility_id'];
                      }else{
                          $data['sample_details'] = 'New Sample';
                      }
                      
                      if($sampleCode!='' || $batchCode!='' || $sampleType!='' || $logVal!='' || $absVal!='' || $absDecimalVal!=''){
                        $id = $db->insert($tableName,$data);
                      }
                      //if(isset($vlResult[$m]['sample_code'])){
                      //$db=$db->where('sample_code',$sampleCode);
                      ////$db=$db->where('sample_code',$vlResult[$m]['sample_code']);
                      //$id=$db->update($tableName,$data);
                      //}
                      $m++;
                  }
              }
            }
            
        $_SESSION['alertMsg']="Imported results successfully";
        //Add event log
        $eventType = 'import';
        $action = ucwords($_SESSION['userName']).' imported a new test result with the sample code '.$sampleCode;
        $resource = 'import-result';
        $data=array(
        'event_type'=>$eventType,
        'action'=>$action,
        'resource'=>$resource,
        'date_time'=>$general->getDateTime()
        );
        $db->insert($tableName1,$data);
        //Add update result log
        $data=array(
        'user_id'=>$_SESSION['userId'],
        'vl_sample_id'=>$id,
        'updated_on'=>$general->getDateTime()
        );
        $db->insert($tableName2,$data);
        header("location:vlResultUnApproval.php");
  
} catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}