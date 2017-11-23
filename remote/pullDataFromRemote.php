<?php
include('../includes/MysqliDb.php');
if(USERTYPE=='vluser'){
//sample type sync
$sTypeQuery = "select * from r_sample_type where data_sync=0";
$sTypeResult = $syncdb->query($sTypeQuery);
if($sTypeResult){
    foreach($sTypeResult as $type){
        $sTypeQuery = "select * from r_sample_type where sample_id=".$type['sample_id'];
        $sTypeLocalResult = $db->query($sTypeQuery);
        $sTypeData = array('sample_name'=>$type['sample_name'],'data_sync'=>1);
        $lastId = 0;
        if($sTypeLocalResult){
            $db = $db->where('sample_id',$type['sample_id']);
            $lastId = $db->update('r_sample_type',$sTypeData);
        }else{
            $db->insert('r_sample_type',$sTypeData);
            $lastId = $db->getInsertId();
        }
        if($lastId>0){
            $syncdb = $syncdb->where('sample_id',$type['sample_id']);
            $id = $syncdb->update('r_sample_type',array('data_sync'=>1));
        }
    }
}
//art code sync
$artCodeQuery = "select * from r_art_code_details where data_sync=0";
$artCodeResult = $syncdb->query($artCodeQuery);
if($artCodeResult){
    foreach($artCodeResult as $artCode){
        $artCodeQuery = "select * from r_art_code_details where art_id=".$artCode['art_id'];
        $artCodeLocalResult = $db->query($artCodeQuery);
        $artCodeData = array('art_code'=>$artCode['art_code'],'parent_art'=>$artCode['parent_art'],
                             'headings'=>$artCode['headings'],'nation_identifier'=>$artCode['nation_identifier'],
                             'art_status'=>$artCode['art_status'],'data_sync'=>1);
        $lastId = 0;
        if($artCodeLocalResult){
            $db = $db->where('art_id',$artCode['art_id']);
            $lastId = $db->update('r_art_code_details',$artCodeData);
        }else{
            $db->insert('r_art_code_details',$artCodeData);
            $lastId = $db->getInsertId();
        }
        if($lastId>0){
            $syncdb = $syncdb->where('art_id',$artCode['art_id']);
            $id = $syncdb->update('r_art_code_details',array('data_sync'=>1));
        }
    }
}
//rejection reason sync
$rejectQuery = "select * from r_sample_rejection_reasons where data_sync=0";
$rejectResult = $syncdb->query($rejectQuery);
if($rejectResult){
    foreach($rejectResult as $reason){
        $rejectQuery = "select * from r_sample_rejection_reasons where rejection_reason_id=".$reason['rejection_reason_id'];
        $rejectLocalResult = $db->query($rejectQuery);
        $rejectResultData = array('rejection_reason_name'=>$reason['rejection_reason_name'],'rejection_type'=>$reason['rejection_type'],
                             'rejection_reason_status'=>$reason['rejection_reason_status'],'rejection_reason_code'=>$reason['rejection_reason_code'],
                             'data_sync'=>1);
        $lastId = 0;
        if($rejectLocalResult){
            $db = $db->where('rejection_reason_id',$reason['rejection_reason_id']);
            $lastId = $db->update('r_sample_rejection_reasons',$rejectResultData);
        }else{
            $db->insert('r_sample_rejection_reasons',$rejectResultData);
            $lastId = $db->getInsertId();
        }
        if($lastId>0){
            $syncdb = $syncdb->where('rejection_reason_id',$reason['rejection_reason_id']);
            $id = $syncdb->update('r_sample_rejection_reasons',array('data_sync'=>1));
        }
    }
}
//prvince data sync
$provinceQuery = "select * from province_details where data_sync=0";
$provinceResult = $syncdb->query($provinceQuery);
if($provinceResult){
    foreach($provinceResult as $province){
        $provinceQuery = "select * from province_details where province_id=".$province['province_id'];
        $provinceLocalResult = $db->query($provinceQuery);
        $provinceData = array('province_name'=>$province['province_name'],'province_code'=>$province['province_code'],'data_sync'=>1);
        $lastId = 0;
        if($provinceLocalResult){
            $db = $db->where('province_id',$province['province_id']);
            $lastId = $db->update('province_details',$provinceData);
        }else{
            $db->insert('province_details',$provinceData);
            $lastId = $db->getInsertId();
        }
        if($lastId>0){
            $syncdb = $syncdb->where('province_id',$province['province_id']);
            $id = $syncdb->update('province_details',array('data_sync'=>1));
        }
    }
}
//facility data sync
$facilityQuery = "select * from facility_details where data_sync=0";
$facilityResult = $syncdb->query($facilityQuery);
//vlsm instance id
$instanceQuery = "select vlsm_instance_id from s_vlsm_instance";
$instanceResult = $syncdb->query($instanceQuery);
if($facilityResult){
    foreach($facilityResult as $facility){
        $facilityQuery = "select * from facility_details where facility_id=".$facility['facility_id'];
        $facilityLocalResult = $db->query($facilityQuery);
        $facilityData = array('vlsm_instance_id'=>$instanceResult[0]['vlsm_instance_id'],'facility_name'=>$facility['facility_name'],'facility_code'=>$facility['facility_code'],
                              'other_id'=>$facility['other_id'],'facility_emails'=>$facility['facility_emails'],
                              'report_email'=>$facility['report_email'],'contact_person'=>$facility['contact_person'],
                              'facility_mobile_numbers'=>$facility['facility_mobile_numbers'],'address'=>$facility['address'],
                              'country'=>$facility['country'],'facility_state'=>$facility['facility_state'],
                              'facility_district'=>$facility['facility_district'],'facility_hub_name'=>$facility['facility_hub_name'],
                              'latitude'=>$facility['latitude'],'longitude'=>$facility['longitude'],'facility_type'=>$facility['facility_type'],
                              'status'=>$facility['status'],'data_sync'=>1);
        $lastId = 0;
        if($facilityLocalResult){
            $db = $db->where('facility_id',$facility['facility_id']);
            $lastId = $db->update('facility_details',$facilityData);
        }else{
            $db->insert('facility_details',$facilityData);
            $lastId = $db->getInsertId();
        }
        if($lastId>0){
            $syncdb = $syncdb->where('facility_id',$facility['facility_id']);
            $id = $syncdb->update('facility_details',array('data_sync'=>1));
        }
    }
}
}
?>