<?php
session_start();
ob_start();
include('./includes/MysqliDb.php');
include('General.php');
$general=new Deforay_Commons_General();
$tableName="vl_request_form";
$tableName1="activity_log";
try {
    
    $vldata=array(
                  'form_id'=>3,
                  'facility_id'=>$_POST['clinicName'],
                  'service'=>$_POST['service'],
                  'request_clinician'=>$_POST['clinicianName'],
                  'clinician_ph_no'=>$_POST['clinicanTelephone'],
                  'support_partner'=>$_POST['supportPartner']
                );
    $id=$db->insert($tableName,$vldata);
    header("location:addVlRequest.php");
}catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}