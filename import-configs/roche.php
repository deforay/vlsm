<?php

$skipTillRow = 2;
$orderNumberColumn = "C";//mandatory column. Used to check when to stop looping

//function fetchValuesFromFile(&$sampleVal,&$logVal,&$absVal,&$txtVal,&$absDecimalVal,&$resultFlag,&$testingDate,&$sampleType,&$batchCode,$rKey,$cellName,$cell){
function fetchValuesFromFile(&$sampleVal,&$logVal,&$absVal,&$txtVal,&$absDecimalVal,&$resultFlag,&$testingDate,&$sampleType,&$batchCode,$key,$rKey,$cellName,$cell,$sheetData){
           
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
     $sampleTypeVal = 'F';
     $batchCodeVal = 'G';
     $flagCol = 'K';
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
          $resVal=explode(" ",$cell->getCalculatedValue());
          $testingDate=str_replace("/","-",$resVal[0]);
        }
     }
     
    if($absValCol==$cellName){
        if($rKey>=$absValRow){
            if(trim($cell->getCalculatedValue())!=""){
                $resVal=(int)$cell->getCalculatedValue();
                if($resVal > 0){
                    $absVal=trim($cell->getCalculatedValue());
                    $logVal=floor(log10($absVal));
                    $txtVal="";
                }else{
                    $absVa="";
                    $logVal="";                       
                    $txtVal=trim($cell->getCalculatedValue());
                }
            }
            
        }
    }
}