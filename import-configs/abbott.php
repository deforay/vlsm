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
     
     //echo "<br><br><br><br>$sampleCode ".$logVal."<br>";
     //echo "$sampleCode ".$absVal."<br>";
     //echo "$sampleCode ".$txtVal."<br>";
     //echo "$sampleCode ".$sampleType;
     //
    
}