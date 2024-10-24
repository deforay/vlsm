#!/usr/bin/env php
<?php

// only run from command line
if (php_sapi_name() !== 'cli') {
    exit(0);
}

require_once(__DIR__ . "/../../bootstrap.php");

use App\Utilities\DateUtility;
use App\Utilities\LoggerUtility;
use App\Services\DatabaseService;
use App\Exceptions\SystemException;
use App\Registries\ContainerRegistry;

if (
    !isset(SYSTEM_CONFIG['archive']['enabled']) ||
    SYSTEM_CONFIG['archive']['enabled'] === false
) {
    LoggerUtility::logError('Archiving is not enabled. Please enable it in configuration.');
    exit;
}

/** @var DatabaseService $db */
$db = ContainerRegistry::get(DatabaseService::class);

if (empty(SYSTEM_CONFIG['archive'])) {
    echo "No archive database settings in System Config";
    exit(0);
}

if (
    !empty(SYSTEM_CONFIG['archive']) &&
    SYSTEM_CONFIG['archive']['enabled'] === true &&
    !empty(SYSTEM_CONFIG['archive']['database']['host']) &&
    !empty(SYSTEM_CONFIG['archive']['database']['username'])
) {
    $db->addConnection('archive', SYSTEM_CONFIG['archive']['database']);
}

$auditTables = [
    'audit_form_vl',
    'audit_form_eid',
    'audit_form_covid19',
    'audit_form_tb',
    'audit_form_hepatitis',
    'audit_form_generic',
];

// $idField = [
//     'audit_form_vl' => 'vl_sample_id',
//     'audit_form_eid' => 'eid_id',
//     'audit_form_covid19' => 'covid19_id',
//     'audit_form_tb' => 'tb_id',
//     'audit_form_hepatitis' => 'hepatitis_id',
//     'audit_form_generic' => 'sample_id',
// ];

$archiveBeforeDate = DateUtility::getDateBeforeMonths(1);

$mainDbName = SYSTEM_CONFIG['database']['db'];
$archiveDbName = SYSTEM_CONFIG['archive']['database']['db'];

foreach ($auditTables as $auditTable) {

    echo "Archiving data from $mainDbName.$auditTable to $archiveDbName.$auditTable...\n";
    //$sampleIdField = $idField[$auditTable];

    try {

        echo "Checking and preparing table $auditTable in the archive database...\n";

        // Check if the audit table exists
        if (!$db->connection('archive')->tableExists([$auditTable])) {
            // Create the table in the archive database using the structure of the main database table
            $createQuery = "CREATE TABLE $archiveDbName.$auditTable SELECT * FROM $mainDbName.$auditTable WHERE 1=0";
            $db->connection('archive')->rawQuery($createQuery);
            echo "Created table $auditTable in the archive database.\n";
        }

        $limit = 1000;
        $offset = 0;

        do {
            echo "Processing $limit rows starting from offset $offset\n";
            // Select data to be archived
            $db->connection('default')->where('dt_datetime', $archiveBeforeDate, '<');
            $db->connection('default')->orderBy('dt_datetime', 'asc');
            $dataToArchive = $db->connection('default')->get($auditTable, [$offset, $limit]);

            $rowCount = count($dataToArchive);
            echo "Fetched $rowCount rows\n";

            // Check if there is data to archive
            if ($rowCount > 0) {

                // Bulk insert data into the archive database
                $db->connection('archive')->insertMulti($auditTable, $dataToArchive);


                // Constructing conditions for bulk deletion
                $deleteConditions = [];
                foreach ($dataToArchive as $row) {
                    $deleteConditions[] = "(unique_id = '" . $db->escape($row['unique_id']) . "' AND dt_datetime = '" . $db->escape($row['dt_datetime']) . "')";
                }
                $deleteQuery = implode(' OR ', $deleteConditions);

                // Bulk delete archived data from the main database
                $db->connection('default')->rawQuery("DELETE FROM $auditTable WHERE $deleteQuery");

                $offset += $limit;
                echo "Processed $rowCount rows, moving to next batch...\n";
            } else {
                echo "No more data to process in $auditTable.\n";
            }
        } while ($rowCount > 0);

        echo "Archived and deleted data older than 1 month from $auditTable.\n";
    } catch (Exception $e) {
        throw new SystemException($e->getMessage());
    }
}

echo "Archiving process completed.\n";
