<?php

function fetchValuesFromFile(&$sampleVal,&$logVal,&$absVal,&$txtVal,&$resultFlag,&$testingDate,$rKey,$cellName,$cell){
           
     $sampleIdCol='C';
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
                
    
     if($sampleIdCol==$cellName){
        if($rKey>=$sampleIdRow){
            $sampleVal=$cell->getCalculatedValue();
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