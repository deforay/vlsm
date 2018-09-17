<?php

include('../includes/MysqliDb.php');

$newDir = __DIR__. DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'roche' . DIRECTORY_SEPARATOR . 'new';
$importedDir = __DIR__. DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'roche' . DIRECTORY_SEPARATOR . 'imported';

if (is_dir($newDir)) {
    $orderLine = 2;
    $resultLine = 3;
    $orderLineIdentifier = "O|1|";
    $resultLineIdentifier = "R|1|";
    $viralLoadTestIdentifier = "^^^HI2CAP96";

    $it = new FilesystemIterator($newDir);
    foreach ($it as $fileinfo) {
    
        if(in_array($fileinfo->getFilename(), array(".DS_Store", "Thumbs.db")))  continue;
        
        $txtFile    = file_get_contents_utf8($newDir.DIRECTORY_SEPARATOR.$fileinfo->getFilename());
        $rows       = explode("\n", $txtFile);

        $resultRow = explode('|', $rows[$resultLine]);
    
        if($resultRow[2] == $viralLoadTestIdentifier){
        
            $orderRow  = explode('|', $rows[$orderLine]);
            $headerRow = explode('|', $rows[0]);
            $dateTime  = $headerRow[count($headerRow)-1];
            $sampleId  = $orderRow[2];
            $result    = $resultRow[3];
            
            $response = updateResult($db, $sampleId, $result,$dateTime);
            
            if($response !== false){
               rename($newDir.DIRECTORY_SEPARATOR.$fileinfo->getFilename(), $importedDir.DIRECTORY_SEPARATOR.$fileinfo->getFilename());
            }
        
        }
    }
}







// FUNCTION to fetch file contents
function file_get_contents_utf8($fn) {
     $content = file_get_contents($fn);
      return mb_convert_encoding($content, 'UTF-8',
          mb_detect_encoding($content, 'UTF-8, ISO-8859-1', true));
}



function updateResult($db, $sampleId, $result, $dateTime){
    
        $logVal = $absDecimalVal = $absVal = null;
        if(trim($result)!=""){
            $resVal=explode("(",$result);
            if(count($resVal)==2){
                if (strpos("<", $resVal[0]) !== false) {
                    $resVal[0] = str_replace("<","",$resVal[0]);
                    $absDecimalVal=(float) trim($resVal[0]);
                    $absVal= "< " . (float) trim($resVal[0]);
                } else if (strpos(">", $resVal[0]) !== false) {
                    $resVal[0] = str_replace(">","",$resVal[0]);
                    $absDecimalVal=(float) trim($resVal[0]);
                    $absVal= "> " . (float) trim($resVal[0]);
                } else{
                    $absVal= (float) trim($resVal[0]);
                    $absDecimalVal=(float) trim($resVal[0]);
                }
                
                $logVal=substr(trim($resVal[1]),0,-1);
                if($logVal == "1.30" || $logVal == "1.3"){
                   $absDecimalVal = 20;
                   $absVal = "< 20";
                }
                
            }else{
                $txtVal=trim($result);
                if($txtVal=='Invalid'){
                    $resultFlag=trim($txtVal);
                }
            }
        }
    
        //echo $dateTime;die;
        $date = DateTime::createFromFormat('YmdHis', $dateTime);
        $testingDate = $date->format('Y-m-d H:i:s');    
    
        $data = array(
            //'lab_id' => $labId,
            'vl_test_platform' => "Roche",
            'result_value_log' => $logVal,
            'sample_code' => $sampleId,
            //'sample_type' => $sampleType,
            'result_value_absolute' => $absVal,
            'result_value_text' => $txtVal,
            'result_value_absolute_decimal' => $absDecimalVal,
            'sample_tested_datetime' => $testingDate,
            'result_status' => '6',
            'import_machine_file_name' => null,
            'approver_comments' => $resultFlag
        );
        
        //echo "<pre>";var_dump($data);continue;
        if ($absVal != "") {
            $data['result'] = $absVal;
        } else if ($logVal != "") {
            $data['result'] = $logVal;
        } else if ($txtVal != "") {
            $data['result'] = $txtVal;
        } else {
            $data['result'] = "";
        }    
    
        var_dump($data);
    
        $db=$db->where('sample_code',$sampleId);
        return $db->update('vl_request_form',$data);
   
}


