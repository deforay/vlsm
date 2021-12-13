<?php

require_once(__DIR__ . "/../startup.php");

$general = new \Vlsm\Models\General();

$primaryKey = array(
    "vl" => "vl_sample_id",
    "eid" => "eid_id",
    "covid19" => "covid19_id",
    "hepatitis" => "hepatitis_id",
    "tb" => "tb_id"
);
$tableName = array(
    "vl" => "vl_request_form",
    "eid" => "eid_form",
    "covid19" => "form_covid19",
    "hepatitis" => "form_hepatitis",
    "tb" => "form_tb"
);

foreach ($systemConfig['modules'] as $module => $status) {
    if ($status) {
        //EXPIRING SAMPLES
        $expiryDays = $general->getGlobalConfig($module . '_sample_expiry_after_days');
        if (empty($expiryDays)) {
            $expiryDays = 365; // by default we consider samples more than 365 days as expired
        }
        $db->where("result_status != 4"); // not rejected 
        $db->where("result_status != 7"); // not approved
        $db->where("result_status != 10"); // not expired
        $db->where("(DATEDIFF(CURRENT_DATE, `sample_collection_date`)) > " . $expiryDays);
        $db->update($tableName[$module], array("result_status" => 10));


        //LOCKING SAMPLES
        $lockExpiryDays = $general->getGlobalConfig($module . '_sample_lock_after_days');
        if ($lockExpiryDays != null && $lockExpiryDays >= 0) {
            $db->where("(result_status = 7 OR result_status = 4)"); // Samples that are Accepted or Rejected
            $db->where("locked NOT LIKE 'yes'");
            $db->where("(DATEDIFF(CURRENT_DATE, `last_modified_datetime`)) > " . $lockExpiryDays);
            $db->update($tableName[$module], array("locked" => "yes"));
        }
    }
}
