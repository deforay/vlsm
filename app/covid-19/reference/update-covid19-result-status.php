<?php
ob_start();



$general = new \Vlsm\Models\General();
$tableName = "r_covid19_results";
try {
    $id = explode(",", $_POST['id']);
    for ($i = 0; $i < count($id); $i++) {
        $status = array(
            'status' => $_POST['status'],
            'updated_datetime'     =>  $db->now(),
        );
        $db = $db->where('result_id', $id[$i]);
        $db->update($tableName, $status);
        $result = $id[$i];
    }
} catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}
echo $result;