<?php

$skipTillRow = 19;
$orderNumberColumn = "A";//mandatory column. Used to check when to stop looping

//function fetchValuesFromFile(&$sampleVal,&$logVal,&$absVal,&$txtVal,&$absDecimalVal,&$resultFlag,&$testingDate,&$sampleType,&$batchCode,$rKey,$cellName,$cell){
function fetchValuesFromFile(&$sampleVal,&$logVal,&$absVal,&$txtVal,&$absDecimalVal,&$resultFlag,&$testingDate,&$sampleType,&$batchCode,$key,$rKey,$cellName,$cell,$sheetData){

     $sampleIdCol='B';
     $sampleIdRow='19';
     $logValCol='';
     $logValRow='';
     $absValCol='G';
     $absValRow='19';
     $txtValCol='';
     $txtValRow='';
     $testingDateCol='C';
     $testingDateRow='4';
     $logAndAbsoluteValInSameCol='no';
     $sampleTypeVal = '';
     $batchCodeVal = 'I';
    
     if($sampleIdCol==$cellName){
        if($rKey>=$sampleIdRow){
            $sampleVal=$cell->getCalculatedValue();
        }
     }
            
       if (strpos(strtolower($sampleVal), 'control') == false && (int)$sampleVal > 0 ) {
           $sampleType = "S";
       }else{
            $sampleType = $sampleVal;
       }  
     
     
     //if($sampleTypeVal==$cellName){
     //   if($rKey>=$sampleIdRow){
     //       $sampleType=$cell->getCalculatedValue();
     //   }
     //}
     
     if($batchCodeVal==$cellName){
        if($rKey>=$sampleIdRow){
            $batchCode=$cell->getCalculatedValue();
        }
     }
     
     
    //$resVal=explode(" ",$sheetData->getCell('C6')->getValue());
    //$testingDate=str_replace("/","-",$resVal[0]);
          
    $cellDt = $sheetData->getCell('C6');
    $testingDate= $cellDt->getValue();
    if(PHPExcel_Shared_Date::isDateTime($cellDt)) {
         $testingDate = date("Y-m-d", PHPExcel_Shared_Date::ExcelToPHP($testingDate)); 
    }
    
     
    if($absValCol==$cellName){
        if($rKey>=$absValRow){
            if(trim($cell->getCalculatedValue())!=""){
                $resVal=(int)$cell->getCalculatedValue();
                if($resVal > 0){
                    $absVal=trim($cell->getCalculatedValue());
                    $logVal=floor(log10($absVal));
                    $txtVal="";
                }else if($resVal == "<"){
                    $absVal="0";
                    $logVal="0";                    
                    $txtVal="< 100";
                }
                else{
                    $absVal="";
                    $logVal="";
                    $txtVal="";
                }
            }
            
        }
    }
}