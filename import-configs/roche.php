<?php

try {
    $db->delete('temp_sample_report');
    //set session for controller track id in hold_sample_record table
    $cQuery  = "select MAX(import_batch_tracking) FROM hold_sample_report";
    $cResult = $db->query($cQuery);
    if ($cResult[0]['MAX(import_batch_tracking)'] != '') {
        $maxId = $cResult[0]['MAX(import_batch_tracking)'] + 1;
    } else {
        $maxId = 1;
    }
    $_SESSION['controllertrack'] = $maxId;
    
    $allowedExtensions = array(
        'xls',
        'xlsx',
        'csv'
    );
    $fileName          = preg_replace('/[^A-Za-z0-9.]/', '-', $_FILES['resultFile']['name']);
    $fileName          = str_replace(" ", "-", $fileName);
    $ranNumber         = str_pad(rand(0, pow(10, 6) - 1), 6, '0', STR_PAD_LEFT);
    $extension         = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $fileName          = $ranNumber . "." . $extension;
    
    
    if (!file_exists('../temporary' . DIRECTORY_SEPARATOR . "import-result") && !is_dir('../temporary' . DIRECTORY_SEPARATOR . "import-result")) {
        mkdir('../temporary' . DIRECTORY_SEPARATOR . "import-result");
    }
    if (move_uploaded_file($_FILES['resultFile']['tmp_name'], '../temporary' . DIRECTORY_SEPARATOR . "import-result" . DIRECTORY_SEPARATOR . $fileName)) {
        //$file_info = new finfo(FILEINFO_MIME); // object oriented approach!
        //$mime_type = $file_info->buffer(file_get_contents('../temporary' . DIRECTORY_SEPARATOR . "import-result" . DIRECTORY_SEPARATOR . $fileName)); // e.g. gives "image/jpeg"

        $objPHPExcel = \PHPExcel_IOFactory::load('../temporary' . DIRECTORY_SEPARATOR . "import-result" . DIRECTORY_SEPARATOR . $fileName);
        $sheetData   = $objPHPExcel->getActiveSheet();
        
        $bquery    = "select MAX(batch_code_key) from batch_details";
        $bvlResult = $db->rawQuery($bquery);
        if ($bvlResult[0]['MAX(batch_code_key)'] != '' && $bvlResult[0]['MAX(batch_code_key)'] != NULL) {
            $maxBatchCodeKey = $bvlResult[0]['MAX(batch_code_key)'] + 1;
            $maxBatchCodeKey = "00" . $maxBatchCodeKey;
        } else {
            $maxBatchCodeKey = '001';
        }
        
        $newBatchCode = date('Ymd') . $maxBatchCodeKey;
        
          $sheetData   = $sheetData->toArray(null, true, true, true);
          $m           = 0;
          $skipTillRow = 2;
        
          $sampleIdCol='E';
          $sampleIdRow='2';
          $logValCol='';
          $logValRow='';
          $absValCol='I';
          $absValRow='2';
          $txtValCol='';
          $txtValRow='';
          $testingDateCol='AC';
          $testingDateRow='2';
          $logAndAbsoluteValInSameCol='no';
          $sampleTypeCol = 'F';
          $batchCodeCol = 'G';
          $flagCol = 'K';
          //$flagRow = '2';
        
        foreach ($sheetData as $rowIndex => $row) {
            
          if ($rowIndex < $skipTillRow)
              continue;
          
          $sampleCode    = "";
          $batchCode     = "";
          $sampleType    = "";
          $absDecimalVal = "";
          $absVal        = "";
          $logVal        = "";
          $txtVal        = "";
          $resultFlag    = "";
          $testingDate   = "";
           
         
          $sampleCode = $row[$sampleIdCol];
          $sampleType = $row[$sampleTypeCol];
          
          $batchCode = $row[$batchCodeCol];
          $resultFlag = $row[$flagCol];
          
          /*$d=explode(" ",$row[$testingDateCol]);
          //$testingDate=str_replace("/","-",$d[0],$checked);
          //$testingDate = date("Y-m-d H:i:s", PHPExcel_Shared_Date::ExcelToPHP($testingDate));
          $dt=explode("/",$d[0]);
          if(count($dt) > 1){
            // Date time in the provided Roche Sample file is in this format : 2016/09/20 12:22:03
            $testingDate = DateTime::createFromFormat('Y/m/d H:i:s', $row[$testingDateCol])->format('Y-m-d H:i');
          }else{
            // Date time in the provided Roche Sample file is in this format : 20-09-16 12:22
            $testingDate = DateTime::createFromFormat('d-m-y H:i', $row[$testingDateCol])->format('Y-m-d H:i');
          }  */
          
          
          $testingDate = date('Y-m-d H:i', strtotime($row[$testingDateCol]));
          
          if(trim($row[$absValCol])!=""){
              $resVal=(float)$row[$absValCol];
              if($resVal > 0){
                  $absVal=trim($row[$absValCol]);
                  $absDecimalVal = $resVal;
                  $logVal=round(log10($absDecimalVal),4);
                  $txtVal="";
              }else{
                  $absDecimalVal = $absVal="";
                  $logVal="";                       
                  $txtVal=trim($row[$absValCol]);
              }
          }
            
            
          if ($sampleCode == "")
              continue;
            

          $infoFromFile[$sampleCode] = array(
              "sampleCode" => $sampleCode,
              "logVal" => trim($logVal),
              "absVal" => $absVal,
              "absDecimalVal" => $absDecimalVal,
              "txtVal" => $txtVal,
              "resultFlag" => $resultFlag,
              "testingDate" => $testingDate,
              "sampleType" => $sampleType,
              "batchCode" => $batchCode
          );
            
            
            $m++;
        }
        
        foreach ($infoFromFile as $sampleCode => $d) {
            
            $data = array(
                'lab_id' => base64_decode($_POST['labId']),
                'vl_test_platform' => $_POST['vltestPlatform'],
                'result_reviewed_by' => $_SESSION['userId'],
                'sample_code' => $d['sampleCode'],
                'result_value_log' => $d['logVal'],
                'sample_type' => $d['sampleType'],
                'result_value_absolute' => $d['absVal'],
                'result_value_text' => $d['txtVal'],
                'result_value_absolute_decimal' => $d['absDecimalVal'],
                'sample_tested_datetime' => $testingDate,
                'result_status' => '6',
                'import_machine_file_name' => $fileName,
                'approver_comments' => $d['resultFlag']
            );
            
            
            if ($d['absVal'] != "") {
                $data['result'] = $d['absVal'];
            } else if ($d['logVal'] != "") {
                $data['result'] = $d['logVal'];
            } else if ($d['txtVal'] != "") {
                $data['result'] = $d['txtVal'];
            } else {
                $data['result'] = "";
            }
            
            if ($batchCode == '') {
                $data['batch_code']     = $newBatchCode;
                $data['batch_code_key'] = $maxBatchCodeKey;
            } else {
                $data['batch_code'] = $batchCode;
            }
            
            $query    = "select facility_id,vl_sample_id,result,result_value_log,result_value_absolute,result_value_text,result_value_absolute_decimal from vl_request_form where sample_code='" . $sampleCode . "'";
            $vlResult = $db->rawQuery($query);
            if ($vlResult && $sampleCode != '') {
                if ($vlResult[0]['result_value_log'] != '' || $vlResult[0]['result_value_absolute'] != '' || $vlResult[0]['result_value_text'] != '' || $vlResult[0]['result_value_absolute_decimal'] != '') {
                    $data['sample_details'] = 'Result already exists';
                } else {
                    $data['result_status'] = '7';
                }
                $data['facility_id'] = $vlResult[0]['facility_id'];
            } else {
                $data['sample_details'] = 'New Sample';
            }
            //echo "<pre>";var_dump($data);echo "</pre>";continue;
            if ($sampleCode != '' || $batchCode != '' || $sampleType != '' || $logVal != '' || $absVal != '' || $absDecimalVal != '') {
                $data['result_imported_datetime'] = $general->getDateTime();
                $id = $db->insert("temp_sample_report", $data);
            }
        }
    }

    $_SESSION['alertMsg'] = "Results imported successfully";
    //Add event log
    $eventType            = 'import';
    $action               = ucwords($_SESSION['userName']) . ' imported a new test result with the sample code ' . $sampleCode;
    $resource             = 'import-result';
    $data                 = array(
        'event_type' => $eventType,
        'action' => $action,
        'resource' => $resource,
        'date_time' => $general->getDateTime()
    );
    $db->insert("activity_log", $data);
    
    //new log for update in result
    $data = array(
        'user_id' => $_SESSION['userId'],
        'vl_sample_id' => $id,
        'updated_on' => $general->getDateTime()
    );
    $db->insert("log_result_updates", $data);
    
    header("location:../vl-print/vlResultUnApproval.php");
    
}
catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}