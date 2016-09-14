<?php

function fetchValuesFromFile(&$sampleVal,&$logVal,&$absVal,&$txtVal,&$absDecimalVal,&$resultFlag,&$testingDate,&$sampleType,&$batchCode,$rKey,$cellName,$cell){
           
     $sampleIdCol='E';
     $sampleIdRow='2';
     $logValCol='I';
     $logValRow='2';
     $absValCol='';
     $absValRow='';
     $txtValCol='';
     $txtValRow='';
     $testingDateCol='AC';
     $testingDateRow='2';
     $logAndAbsoluteValInSameCol='yes';
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
     
    if($logValCol==$cellName){
        if($rKey>=$logValRow){
            if(trim($cell->getCalculatedValue())!=""){
                $resVal=explode("(",$cell->getCalculatedValue());
                if(count($resVal)==2){
                    $absVal=trim($resVal[0]);
                    
                    $expAbsVal=explode("E",$absVal);
                    if(count($expAbsVal)==2){
                         $multipleVal=substr($expAbsVal[1],1);
                         $absDecimalVal=$expAbsVal[0]*pow(10,$multipleVal);
                    }
                    $logVal=substr(trim($resVal[1]),0,-1);
                }else{
                    $txtVal=trim($cell->getCalculatedValue());
                    if($txtVal=='Invalid'){
                        $resultFlag=trim($txtVal);
                    }
                }
            }
            
        }
    }
}