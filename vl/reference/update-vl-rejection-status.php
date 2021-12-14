<?php
ob_start();
#require_once('../../startup.php');


$general = new \Vlsm\Models\General();
$tableName = "r_vl_sample_rejection_reasons";
try {
    $id = explode(",", $_POST['id']);
    for ($i = 0; $i < count($id); $i++) {
        $status = array(
            'rejection_reason_status' => $_POST['status'],
            'updated_datetime'     =>  $general->getDateTime(),
        );
        $db = $db->where('rejection_reason_id', $id[$i]);
        $db->update($tableName, $status);
        $result = $id[$i];
    }
} catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}
echo $result;
