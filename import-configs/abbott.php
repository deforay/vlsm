<?php

$skipTillRow = 2;
$orderNumberColumn = "C";//mandatory column. Used to check when to stop looping

//function fetchValuesFromFile(&$sampleVal,&$logVal,&$absVal,&$txtVal,&$absDecimalVal,&$resultFlag,&$testingDate,&$sampleType,&$batchCode,$rKey,$cellName,$cell){
function fetchValuesFromFile(&$sampleCode,&$logVal,&$absVal,&$txtVal,&$absDecimalVal,&$resultFlag,&$testingDate,&$sampleType,&$batchCode,$sheetData){
     
     $sampleIdCol=1;
     $resultCol=5;
     $txtValCol=6;
     $sampleTypeCol = 2;
     $batchCodeVal = '';
     $flagCol = 10;
                
     $sampleCode = $sheetData[$sampleIdCol];
     
     if(strpos($sheetData[$resultCol], 'Log (Copies / mL)') !== false){
          $logVal = str_replace("Log (Copies / mL)", "", $sheetData[$resultCol]);
          $logVal = str_replace(",", ".", $logVal);
     }else if(strpos($sheetData[$resultCol], 'Copies / mL') !== false){
          $absVal = str_replace("Copies / mL", "", $sheetData[$resultCol]);
     }else{
          
          if($sheetData[$resultCol] == "" || $sheetData[$resultCol] == null){
               //$txtVal =  $sheetData[$flagCol];
               $txtVal =  "Failed";
               $resultFlag = $sheetData[$flagCol];
          }else{
               $txtVal = $sheetData[$resultCol+1];
               $resultFlag = "";
               $absVal = "";
               $logVal = "";
          }
          
     }
     
     
     $sampleType = $sheetData[$sampleTypeCol];
     if($sampleType == 'Patient'){
          $sampleType = 'S';
     }
     preg_match_all('!\d+!', $absVal, $absDecimalVal);
     $absVal=$absDecimalVal = implode("",$absDecimalVal[0]);
     $batchCode = "";
    
}





$configQuery  = "SELECT * from global_config";
$configResult = $db->query($configQuery);
$arr          = array();
for ($i = 0; $i < sizeof($configResult); $i++) {
    $arr[$configResult[$i]['name']] = $configResult[$i]['value'];
}

$general = new Deforay_Commons_General();

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
        
       
        
        $bquery    = "select MAX(batch_code_key) from batch_details";
        $bvlResult = $db->rawQuery($bquery);
        if ($bvlResult[0]['MAX(batch_code_key)'] != '' && $bvlResult[0]['MAX(batch_code_key)'] != NULL) {
            $maxBatchCodeKey = $bvlResult[0]['MAX(batch_code_key)'] + 1;
            $maxBatchCodeKey = "00" . $maxBatchCodeKey;
        } else {
            $maxBatchCodeKey = '001';
        }
        
        $newBatchCode = date('Ymd') . $maxBatchCodeKey;
        
        
        $m           = 1;
        $skipTillRow = 23;
        
        $sampleIdCol   = 1;
        $resultCol     = 5;
        $txtValCol     = 6;
        $sampleTypeCol = 2;
        $batchCodeVal  = "";
        $flagCol       = 10;
        $testDateCol   = 11;
        
        if (($handle = fopen('../temporary'. DIRECTORY_SEPARATOR ."import-result" . DIRECTORY_SEPARATOR . $fileName, "r")) !== FALSE) {
          
          while (($row = fgetcsv($handle, 1000, "\t")) !== FALSE) {
            
            $m++;
            if ($m < $skipTillRow)
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
            
            if (strpos($row[$resultCol], 'Log (Copies / mL)') !== false) {
                $logVal = str_replace("Log (Copies / mL)", "", $row[$resultCol]);
                $logVal = str_replace(",", ".", $logVal);
            } else if (strpos($row[$resultCol], 'Copies / mL') !== false) {
                $absVal = str_replace("Copies / mL", "", $row[$resultCol]);
                preg_match_all('!\d+!', $absVal, $absDecimalVal);
                $absVal = $absDecimalVal = implode("", $absDecimalVal[0]);
            } else {
                if ($row[$resultCol] == "" || $row[$resultCol] == null) {
                    $txtVal     = "Failed";
                    $resultFlag = $row[$flagCol];
                } else {
                    $txtVal     = $row[$flagCol];
                    $resultFlag = $row[$flagCol];
                    $absVal     = "";
                    $logVal     = "";
                }
            }
            
            $sampleType = $row[$sampleTypeCol];
            if ($sampleType == 'Patient') {
                $sampleType = 'S';
            }
            
            $batchCode = "";
            
            // Date time in the provided Abbott Sample file is in this format : 11/23/2016 2:22:35 PM
            $testingDate = DateTime::createFromFormat('m/d/Y g:i:s A', $row[$testDateCol])->format('Y-m-d H:i');
            
            if ($sampleCode == "")
                continue;
            
            if (!isset($infoFromFile[$sampleCode])) {
                $infoFromFile[$sampleCode] = array(
                    "sampleCode" => $sampleCode,
                    "logVal" => trim($logVal),
                    "txtVal" => $txtVal,
                    "resultFlag" => $resultFlag,
                    "testingDate" => $testingDate,
                    "sampleType" => $sampleType,
                    "batchCode" => $batchCode
                );
            } else {
                $infoFromFile[$sampleCode]['absVal']        = $absVal;
                $infoFromFile[$sampleCode]['absDecimalVal'] = $absDecimalVal;
            }
            
            //$m++;
        }
        }
        
        /*
         * OK, so the reason why we are putting the information into an array ($infoFromFile)
         * is because the Abbott data has same sample ID repeated in two rows, with one row
         * giving log and another giving abs value. So we create the $infoFromFile array to
         * ensure we get both log and abs value for the given sample
         */
         
        foreach ($infoFromFile as $sampleCode => $d) {
            if ($sampleCode == "")
                continue;
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
                'status' => '6',
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
                    $data['sample_details'] = 'Result exists already';
                } else {
                    $data['status'] = '7';
                }
                $data['facility_id'] = $vlResult[0]['facility_id'];
            } else {
                $data['sample_details'] = 'New Sample';
            }
            //echo "<pre>";var_dump($data);echo "</pre>";continue;
            if ($sampleCode != '' || $batchCode != '' || $sampleType != '' || $logVal != '' || $absVal != '' || $absDecimalVal != '') {
                $id = $db->insert("temp_sample_report", $data);
            }
        }
    }
    //die;
    $_SESSION['alertMsg'] = "Imported results successfully";
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