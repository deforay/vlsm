<?php
//update common table from remote to lab db
include(dirname(__FILE__) . "/../../includes/MysqliDb.php");
include(dirname(__FILE__) . "/../../General.php");
if(!isset($REMOTEURL) || $REMOTEURL=='')
{
    echo "Please check your remote url";
    die;
}
$general=new Deforay_Commons_General();
$globalConfigQuery ="SELECT * from system_config";
$configResult=$db->query($globalConfigQuery);
$arr = array();
// now we create an associative array so that we can easily create view variables
for ($i = 0; $i < sizeof($configResult); $i++) {
    $arr[$configResult[$i]['name']] = $configResult[$i]['value'];
}
//ar code last update time
$artCodeTime = '';$rjtDateTime = '';$provinceDateTime = '';$fDateTime = '';

$artCodeLQuery = "select * from r_art_code_details order by updated_datetime DESC limit 1";
$artCodeLResult = $db->query($artCodeLQuery);
if(isset($artCodeLResult[0]['updated_datetime']) && $artCodeLResult[0]['updated_datetime']!='' && $artCodeLResult[0]['updated_datetime']!=NULL && $artCodeLResult[0]['updated_datetime']!='0000-00-00 00:00:00'){
    $artCodeTime = $artCodeLResult[0]['updated_datetime'];
}
//rejection reason last update time
$rejectLQuery = "select * from r_sample_rejection_reasons order by updated_datetime DESC limit 1";
$rejectLResult = $db->query($rejectLQuery);
if(isset($rejectLResult[0]['updated_datetime']) && $rejectLResult[0]['updated_datetime']!='' && $rejectLResult[0]['updated_datetime']!=NULL && $rejectLResult[0]['updated_datetime']!='0000-00-00 00:00:00'){
    $rjtDateTime = $rejectLResult[0]['updated_datetime'];
}
//prvince data last update time
$provinceLQuery = "select * from province_details order by updated_datetime DESC limit 1";
$provinceLResult = $db->query($provinceLQuery);
if(isset($provinceLResult[0]['updated_datetime']) && $provinceLResult[0]['updated_datetime']!='' && $provinceLResult[0]['updated_datetime']!=NULL && $provinceLResult[0]['updated_datetime']!='0000-00-00 00:00:00'){
    $provinceDateTime = $provinceLResult[0]['updated_datetime'];
}
//facility data last update time
$facilityLQuery = "select * from facility_details order by updated_datetime DESC limit 1";
$facilityLResult = $db->query($facilityLQuery);
if(isset($facilityLResult[0]['updated_datetime']) && $facilityLResult[0]['updated_datetime']!='' && $facilityLResult[0]['updated_datetime']!=NULL && $facilityLResult[0]['updated_datetime']!='0000-00-00 00:00:00'){
    $fDateTime = $facilityLResult[0]['updated_datetime'];
}
$url = $REMOTEURL.'/remote/remote/commonData.php';
$data = array(
    'artCodeUpdateTime'=>$artCodeTime,
    'rjtUpdateTime'=>$rjtDateTime,
    'provinceUpdateTime'=>$provinceDateTime,
    'facilityUpdateTime'=>$fDateTime,
    "Key"=>"vlsm-get-remote",
);
//open connection
$ch = curl_init($url);
$json_data = json_encode($data);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'Content-Length: ' . strlen($json_data))
);
// execute post
$curl_response = curl_exec($ch);
//close connection
curl_close($ch);
$result = json_decode($curl_response, true);

//update or insert sample type
if(count($result['sampleType'])>0){
    foreach($result['sampleType'] as $type){
        $sTypeQuery = "select * from r_sample_type where sample_id=".$type['sample_id'];
        $sTypeLocalResult = $db->query($sTypeQuery);
        $sTypeData = array('sample_name'=>$type['sample_name'],'status'=>$type['status'],'data_sync'=>1);
        $lastId = 0;
        if($sTypeLocalResult){
            $db = $db->where('sample_id',$type['sample_id']);
            $lastId = $db->update('r_sample_type',$sTypeData);
        }else{
            $sTypeData['sample_id'] = $type['sample_id'];
            $db->insert('r_sample_type',$sTypeData);
            $lastId = $db->getInsertId();
        }
    }
}

//update or insert art code deatils
if(count($result['artCode'])>0){
    foreach($result['artCode'] as $artCode){
        $artCodeQuery = "select * from r_art_code_details where art_id=".$artCode['art_id'];
        $artCodeLocalResult = $db->query($artCodeQuery);
        $artCodeData = array('art_code'=>$artCode['art_code'],'parent_art'=>$artCode['parent_art'],
                             'headings'=>$artCode['headings'],'nation_identifier'=>$artCode['nation_identifier'],
                             'art_status'=>$artCode['art_status'],'data_sync'=>1,'updated_datetime'=>$general->getDateTime());
        $lastId = 0;
        if($artCodeLocalResult){
            $db = $db->where('art_id',$artCode['art_id']);
            $lastId = $db->update('r_art_code_details',$artCodeData);
        }else{
            $artCodeData['art_id'] = $artCode['art_id'];
            $db->insert('r_art_code_details',$artCodeData);
            $lastId = $db->getInsertId();
        }
    }
}

//update or insert rejected reason
if(count($result['rejectReason'])>0){
    foreach($result['rejectReason'] as $reason){
        $rejectQuery = "select * from r_sample_rejection_reasons where rejection_reason_id=".$reason['rejection_reason_id'];
        $rejectLocalResult = $db->query($rejectQuery);
        $rejectResultData = array('rejection_reason_name'=>$reason['rejection_reason_name'],'rejection_type'=>$reason['rejection_type'],
                             'rejection_reason_status'=>$reason['rejection_reason_status'],'rejection_reason_code'=>$reason['rejection_reason_code'],
                             'data_sync'=>1,'updated_datetime'=>$general->getDateTime());
        $lastId = 0;
        if($rejectLocalResult){
            $db = $db->where('rejection_reason_id',$reason['rejection_reason_id']);
            $lastId = $db->update('r_sample_rejection_reasons',$rejectResultData);
        }else{
            $rejectResultData['rejection_reason_id'] = $reason['rejection_reason_id'];
            $db->insert('r_sample_rejection_reasons',$rejectResultData);
            $lastId = $db->getInsertId();
        }
    }
}

//update or insert province
if(count($result['province'])>0){
    foreach($result['province'] as $province){
        $provinceQuery = "select * from province_details where province_id=".$province['province_id'];
        $provinceLocalResult = $db->query($provinceQuery);
        $provinceData = array('province_name'=>$province['province_name'],'province_code'=>$province['province_code'],'data_sync'=>1,'updated_datetime'=>$general->getDateTime());
        $lastId = 0;
        if($provinceLocalResult){
            $db = $db->where('province_id',$province['province_id']);
            $lastId = $db->update('province_details',$provinceData);
        }else{
            $provinceData['province_id'] = $province['province_id'];
            $db->insert('province_details',$provinceData);
            $lastId = $db->getInsertId();
        }
    }
}

//update or insert facility data
$instanceQuery = "select vlsm_instance_id from s_vlsm_instance";
$instanceResult = $db->query($instanceQuery);
if(count($result['facilityResult'])>0){
    foreach($result['facilityResult'] as $facility){
        $facilityQuery = "select * from facility_details where facility_id=".$facility['facility_id'];
        $facilityLocalResult = $db->query($facilityQuery);
        $facilityData = array('vlsm_instance_id'=>$instanceResult[0]['vlsm_instance_id'],'facility_name'=>$facility['facility_name'],'facility_code'=>$facility['facility_code'],
                              'other_id'=>$facility['other_id'],'facility_emails'=>$facility['facility_emails'],
                              'report_email'=>$facility['report_email'],'contact_person'=>$facility['contact_person'],
                              'facility_mobile_numbers'=>$facility['facility_mobile_numbers'],'address'=>$facility['address'],
                              'country'=>$facility['country'],'facility_state'=>$facility['facility_state'],
                              'facility_district'=>$facility['facility_district'],'facility_hub_name'=>$facility['facility_hub_name'],
                              'latitude'=>$facility['latitude'],'longitude'=>$facility['longitude'],'facility_type'=>$facility['facility_type'],
                              'status'=>$facility['status'],'data_sync'=>1,'updated_datetime'=>$general->getDateTime());
        $lastId = 0;
        if($facilityLocalResult){
            $db = $db->where('facility_id',$facility['facility_id']);
            $lastId = $db->update('facility_details',$facilityData);
        }else{
            $facilityData['facility_id'] = $facility['facility_id'];
            $db->insert('facility_details',$facilityData);
            $lastId = $db->getInsertId();
        }
    }
}
?>