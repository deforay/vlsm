<?php

require(__DIR__ . "/../includes/MysqliDb.php");
require(__DIR__ . "/../General.php");

$interfacedb = new MysqliDb($iHOST, $iUSER, $iPASSWORD, $iDBNAME, $iPORT);

$general=new General($db);
//get the value from interfacing DB

$interfaceQuery="SELECT * from orders where result_status = 1 and lims_sync_status=0";
$interfaceInfo=$interfacedb->query($interfaceQuery);
if(count($interfaceInfo)>0)
{
    foreach($interfaceInfo as $key=>$result)
    {
        $vlQuery="SELECT vl_sample_id from vl_request_form where sample_code = '".$result['test_id']."'";
        $vlInfo=$db->query($vlQuery);
        if(isset($vlInfo[0]['vl_sample_id'])){
            //set result in result fields
            if(trim($result['results'])!=""){
                $absDecimalVal = NULL;
                $absVal = NULL;
                $logVal = NULL;
                $txtVal = NULL;
                $resVal=explode("(",$result['results']);
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
                    $txtVal=trim($result['results']);
                }
            }

            $data = array(
                        'result_approved_by'=>$result['tested_by'],
                        'result_approved_datetime'=>$result['authorised_date_time'],
                        'sample_tested_datetime'=>$result['result_accepted_date_time'],
                        'result_value_log'=>$logVal,
                        'result_value_absolute'=>$absVal,
                        'result_value_absolute_decimal'=>$absDecimalVal,
                        'result_value_text'=>$txtVal,
                        );

                        if ($absVal != "") {
                            $data['result'] = $absVal;
                        } else if ($logVal != "") {
                            $data['result'] = $logVal;
                        } else if ($txtVal != "") {
                            $data['result'] = $txtVal;
                        } else {
                            $data['result'] = NULL;
                        }
            
                        $db=$db->where('vl_sample_id',$vlInfo[0]['vl_sample_id']);
                        $vlUpdateId = $db->update('vl_request_form',$data);
                        if($vlUpdateId){
                            $interfaceData = array(
                                                    'lims_sync_status'=>1,
                                                    'lims_sync_date_time'=>date('Y-m-d H:i:s')
                                                    );
                                                    $interfacedb=$interfacedb->where('id',$result['id']);
                                                    $interfaceUpdateId = $interfacedb->update('orders',$interfaceData);

                        }
        }
    }
}
?>