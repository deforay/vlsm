<?php

use App\Services\DatabaseService;
use App\Utilities\DateUtility;
use App\Registries\ContainerRegistry;

require_once(__DIR__ . '/../bootstrap.php');

/** @var DatabaseService $db */
$db = ContainerRegistry::get('db');

/* Save Province / State details to geolocation table */
$query = "SELECT DISTINCT facility_state
            FROM facility_details
            WHERE facility_state not in
                (SELECT geo_name FROM geographical_divisions WHERE geo_parent = 0)
                ORDER BY facility_state ASC";
$provinceResult = $db->rawQuery($query);
foreach ($provinceResult as $p) {
    $exist = $db->rawQueryOne("SELECT geo_id, geo_name
                                FROM geographical_divisions WHERE (geo_name = '?')", $p['facility_state']);
    if ($exist) {
        $db->where("geo_name", $p['facility_state']);
        $db->where("geo_parent = 0");
        $db->update(
            'geographical_divisions',
            [
                "geo_name"          => $p['facility_state'],
                "geo_status"        => "active",
                "created_on"        => DateUtility::getCurrentDateTime(),
                "updated_datetime"  => DateUtility::getCurrentDateTime()
            ]
        );
        $lastInsertId = $exist['geo_id'];
    } else {
        $lastInsertId = $db->insert(
            "geographical_divisions",
            [
                "geo_name"          => $p['facility_state'],
                "geo_parent"        => "0",
                "geo_status"        => "active",
                "created_on"        => DateUtility::getCurrentDateTime(),
                "updated_datetime"  => DateUtility::getCurrentDateTime()
            ]
        );
    }

    /* Update back to the facility_state_id */
    $db->where("facility_state", $p['facility_state']);
    $db->update(
        "facility_details",
        [
            "facility_state_id" => $lastInsertId,
            "updated_datetime"  => DateUtility::getCurrentDateTime()
        ]
    );
}

/* Save County / District details to geolocation table */
$query = "SELECT DISTINCT facility_state_id, facility_district
                FROM facility_details
                WHERE facility_district not in (SELECT geo_name
                                                FROM geographical_divisions
                                                WHERE geo_parent != 0)
                ORDER BY facility_district ASC";
$districtResult = $db->rawQuery($query);
foreach ($districtResult as $d) {
    $exist = $db->rawQueryOne("SELECT geo_name
                                FROM geographical_divisions
                                WHERE (geo_name = '?')", $d['facility_district']);
    if ($exist) {
        $db->where("geo_name", $exist['facility_district']);
        $db->where("geo_parent != 0");
        $db->update('geographical_divisions', [
            "geo_name"          => $d['facility_district'],
            "geo_parent"        => $d['facility_state_id'],
            "geo_status"        => "active",
            "created_on"        => DateUtility::getCurrentDateTime(),
            "updated_datetime"  => DateUtility::getCurrentDateTime()
        ]);
        $lastInsertId = $exist['geo_id'];
    } else {
        $lastInsertId = $db->insert("geographical_divisions", [
            "geo_name"          => $d['facility_district'],
            "geo_parent"        => $d['facility_state_id'],
            "geo_status"        => "active",
            "created_on"        => DateUtility::getCurrentDateTime(),
            "updated_datetime"  => DateUtility::getCurrentDateTime()
        ]);
    }

    /* Update back to the facility_district_id */
    $db->where("facility_district", $d['facility_district']);
    $db->update(
        "facility_details",
        [
            "facility_district_id" => $lastInsertId,
            "updated_datetime"  => DateUtility::getCurrentDateTime()
        ]
    );
}