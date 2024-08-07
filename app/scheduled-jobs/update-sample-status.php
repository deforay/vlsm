#!/usr/bin/env php
<?php

// only run from command line
if (php_sapi_name() !== 'cli') {
    exit(0);
}

require_once(__DIR__ . "/../../bootstrap.php");

use App\Registries\ContainerRegistry;
use App\Services\CommonService;
use App\Services\DatabaseService;

/** @var DatabaseService $db */
$db = ContainerRegistry::get(DatabaseService::class);

/** @var CommonService $general */
$general = ContainerRegistry::get(CommonService::class);

$primaryKey = [
    "vl" => "vl_sample_id",
    "eid" => "eid_id",
    "covid19" => "covid19_id",
    "hepatitis" => "hepatitis_id",
    "tb" => "tb_id",
    "generic-tests" => "sample_id"
];

$tableName = [
    "vl" => "form_vl",
    "eid" => "form_eid",
    "covid19" => "form_covid19",
    "hepatitis" => "form_hepatitis",
    "generic-tests" => "form_generic"
];

foreach (SYSTEM_CONFIG['modules'] as $module => $status) {
    if ($status) {
        //FAILED SAMPLES
        if ($module === 'vl') {
            $db->where("(result LIKE 'fail%' OR result = 'failed' OR result LIKE 'err%' OR result LIKE 'error')");
            $db->where("result_status != " . SAMPLE_STATUS\REJECTED); // not rejected
            $db->where("result_status != " . SAMPLE_STATUS\TEST_FAILED); // not already in failed status
            $db->update(
                $tableName[$module],
                [
                    "result_status" => SAMPLE_STATUS\TEST_FAILED
                ]
            );
        }

        //EXPIRING SAMPLES
        $expiryDays = $general->getGlobalConfig($module . '_sample_expiry_after_days');
        if (empty($expiryDays)) {
            $expiryDays = 365 * 2; // by default, we consider samples more than 2 years as expired
        }
        $db->where("result_status != " . SAMPLE_STATUS\REJECTED); // not rejected
        $db->where("result_status != " . SAMPLE_STATUS\ACCEPTED); // not approved
        $db->where("result_status != " . SAMPLE_STATUS\EXPIRED); // not expired
        $db->where("(DATEDIFF(CURRENT_DATE, `sample_collection_date`)) > " . $expiryDays);
        $db->update(
            $tableName[$module],
            [
                "result_status" => SAMPLE_STATUS\EXPIRED,
                "locked" => "yes"
            ]
        );


        //LOCKING SAMPLES
        $lockExpiryDays = $general->getGlobalConfig($module . '_sample_lock_after_days');
        if ($lockExpiryDays != null && $lockExpiryDays >= 0) {
            $db->where("(result_status = " . SAMPLE_STATUS\REJECTED . " OR result_status = " . SAMPLE_STATUS\ACCEPTED . ")"); // Samples that are Accepted, Rejected
            $db->where("locked NOT LIKE 'yes'");
            $db->where("(DATEDIFF(CURRENT_DATE, `last_modified_datetime`)) > " . $lockExpiryDays);
            $db->update($tableName[$module], array("locked" => "yes"));
        }
    }
}
