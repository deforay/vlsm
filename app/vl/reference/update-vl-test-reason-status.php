<?php
ob_start();



$general = new \Vlsm\Models\General();
$tableName = "r_vl_test_reasons";
try {
    $id = explode(",", $_POST['id']);
    for ($i = 0; $i < count($id); $i++) {
        $status = array(
            'test_reason_status' => $_POST['status'],
            'updated_datetime'     =>  $db->now(),
        );
        $db = $db->where('test_reason_id', $id[$i]);
        $db->update($tableName, $status);
        $result = $id[$i];
    }
} catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}
echo $result;