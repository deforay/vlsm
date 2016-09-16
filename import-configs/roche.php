<?php

function fetchValuesFromFile(&$sampleVal,&$logVal,&$absVal,&$txtVal,&$absDecimalVal,&$resultFlag,&$testingDate,&$sampleType,&$batchCode,$rKey,$cellName,$cell){
           
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
                }else{
                    $txtVal=trim($cell->getCalculatedValue());
                }
            }
            
        }
    }
}