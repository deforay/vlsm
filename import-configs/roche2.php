<?php

$skipTillRow = 2;
$orderNumberColumn = "C";//mandatory column. Used to check when to stop looping

//function fetchValuesFromFile(&$sampleVal,&$logVal,&$absVal,&$txtVal,&$absDecimalVal,&$resultFlag,&$testingDate,&$sampleType,&$batchCode,$rKey,$cellName,$cell){
function fetchValuesFromFile(&$sampleVal,&$logVal,&$absVal,&$txtVal,&$absDecimalVal,&$resultFlag,&$testingDate,&$sampleType,&$batchCode,$key,$rKey,$cellName,$cell,$sheetData){
           
     $sampleIdCol='C';
     $sampleIdRow='2';
     $logValCol='';
     $logValRow='';
     $absValCol='F';
     $absValRow='';
     $txtValCol='';
     $txtValRow='';
     $testingDateCol='J';
     $testingDateRow='2';
     $logAndAbsoluteValInSameCol='';
     $sampleTypeVal = 'A';
     $batchCodeVal = '';
     $flagCol = 'G';
     //$flagRow = '2';
                
    
     if($sampleIdCol==$cellName){
        if($rKey>=$sampleIdRow){
            $sampleVal=$cell->getCalculatedValue();
        }
     }
     
     if($sampleTypeVal==$cellName){
        if($rKey>=$sampleIdRow){
            $sampleType=$cell->getCalculatedValue();
        }
     }
     if($batchCodeVal==$cellName){
        if($rKey>=$sampleIdRow){
            $batchCode=$cell->getCalculatedValue();
        }
     }
     
     if($flagCol==$cellName){
        if($rKey>=$sampleIdRow){
            $resultFlag=$cell->getCalculatedValue();
        }
     }
     
     
     if($testingDateCol==$cellName){
        if($rKey>=$testingDateRow){
          $cellDt = $sheetData->getCell($cellName.$rKey);
          $testingDate= $cellDt->getValue();
          $resVal=explode(" ",$cellDt->getValue());
          //print_r($resVal);die;
          $testingDate=str_replace("/","-",$resVal[0]);
          if(PHPExcel_Shared_Date::isDateTime($cellDt)) {
               $testingDate = date("Y-m-d",PHPExcel_Shared_Date::ExcelToPHP($testingDate));
          }
        }
     }
     
    if($absValCol==$cellName){
        if($rKey>=$absValRow){
            if(trim($cell->getCalculatedValue())!=""){
                $resVal=(int)$cell->getCalculatedValue();
                if($resVal > 0){
                    $absVal=trim(str_replace("cp/ml","",$cell->getCalculatedValue()));
                    $logVal=floor(log10($absVal));
                    $txtVal="";
                }else{
                    $absVal="";
                    $logVal="";
                    //check signs and cp/ml
                    $eTxtVal=trim($cell->getCalculatedValue());
                    $stReplace = str_replace("cp/ml","",$eTxtVal);
                    $resVal=(int)$stReplace;
                    if($resVal > 0){
                    $absVal=trim($stReplace);
                    $logVal=floor(log10($absVal));
                    $txtVal="";
                    }else{
                         $absVal="";
                         $logVal="";
                         $txtVal=trim($stReplace);
                    }
                }
            }
            
        }
    }
}