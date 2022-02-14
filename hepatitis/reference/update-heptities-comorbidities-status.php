<?php
ob_start();



$general = new \Vlsm\Models\General();
$tableName = "r_hepatitis_comorbidities";
try {
    $id = explode(",", $_POST['id']);
    for ($i = 0; $i < count($id); $i++) {
        $status = array(
            'comorbidity_status' => $_POST['status'],
            'updated_datetime'     =>  $general->getDateTime(),
        );
        $db = $db->where('comorbidity_id', $id[$i]);
        $db->update($tableName, $status);
        $result = $id[$i];
    }
} catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}
echo $result;