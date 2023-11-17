#!/usr/bin/env php
<?php

// error_reporting(E_ALL);
// ini_set('display_errors', 1);

use App\Registries\ContainerRegistry;

require_once(__DIR__ . '/../bootstrap.php');

/** @var MysqliDb $db */
$db = ContainerRegistry::get('db');

$auditTables = [
    'form_vl' => 'audit_form_vl',
    'form_eid' => 'audit_form_eid',
    'form_covid19' => 'audit_form_covid19',
    'form_tb' => 'audit_form_tb',
    'form_hepatitis' => 'audit_form_hepatitis',
    'form_generic' => 'audit_form_generic',
];

// Get the mysqli connection from the MySQLiDb instance
$mysqli = $db->mysqli();
foreach ($auditTables as $formTable => $auditTable) {
    // Check if the audit table exists
    $auditTableEscaped = $db->escape($auditTable);
    $auditTableExists = $db->rawQuery("SHOW TABLES LIKE '$auditTableEscaped'");

    if (!$auditTableExists) {
        echo "Table $auditTable does not exist. Creating...\n";
        $createTableQuery = "CREATE TABLE `$auditTable` SELECT * from `$formTable` WHERE 1=0;";
        $modifyTableQuery = "
            ALTER TABLE `$auditTable`
            MODIFY COLUMN `sample_id` int(11) NOT NULL,
            ENGINE = MyISAM,
            ADD `action` VARCHAR(8) DEFAULT 'insert' FIRST,
            ADD `revision` INT(6) NOT NULL AUTO_INCREMENT AFTER `action`,
            ADD `dt_datetime` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `revision`,
            ADD PRIMARY KEY (`sample_id`, `revision`);
        ";

        // Execute the create and modify table queries
        $db->rawQuery($createTableQuery);
        $db->rawQuery($modifyTableQuery);

        // Set up the triggers
        $triggers = [
            "{$formTable}_data__ai" => "AFTER INSERT",
            "{$formTable}_data__au" => "AFTER UPDATE",
            "{$formTable}_data__bd" => "BEFORE DELETE",
        ];

        foreach ($triggers as $triggerName => $timing) {
            $action = strtolower(str_replace($formTable . '_data__', '', $triggerName));
            $triggerQuery = "
                DROP TRIGGER IF EXISTS `$triggerName`;
                CREATE TRIGGER `$triggerName` $timing ON `$formTable` FOR EACH ROW
                INSERT INTO `$auditTable` SELECT '$action', NULL, NOW(), d.*
                FROM `$formTable` AS d WHERE d.sample_id = " . ($action === 'delete' ? "OLD" : "NEW") . ".sample_id;
            ";

            // Execute the DROP TRIGGER and CREATE TRIGGER statements directly
            if (!$mysqli->multi_query($triggerQuery)) {
                echo "Error executing trigger statement for $triggerName: " . $mysqli->error . "\n";
                do {
                    if ($res = $mysqli->store_result()) {
                        $res->free();
                    }
                } while ($mysqli->more_results() && $mysqli->next_result());
            } else {
                echo "Trigger $triggerName created successfully.\n";
                // Make sure to clear results to be ready for the next statement
                while ($mysqli->next_result()) {
                }
            }
        }

        echo "Audit table $auditTable and triggers created successfully.\n";
    } else {
        // Query to find the missing columns
        $query = "
            SELECT COLUMN_NAME, COLUMN_TYPE
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = '{$db->escape(SYSTEM_CONFIG['database']['db'])}'
            AND TABLE_NAME = '{$db->escape($formTable)}'
            AND COLUMN_NAME NOT IN (
                SELECT COLUMN_NAME
                FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = '{$db->escape(SYSTEM_CONFIG['database']['db'])}'
                AND TABLE_NAME = '{$db->escape($auditTable)}'
            )
            ORDER BY COLUMN_NAME;
            ";

        $columns = $db->rawQuery($query);

        if ($db->getLastErrno()) {
            echo "Error checking columns for $formTable and $auditTable: " . $db->getLastError() . "\n";
            continue;
        }

        if (empty($columns)) {
            echo "All columns for $formTable exist in $auditTable.\n";
            continue;
        }

        // Loop through the result and generate ALTER TABLE statements
        foreach ($columns as $column) {
            $alterQuery = "ALTER TABLE `$auditTable` ADD `" . $column['COLUMN_NAME'] . "` " . $column['COLUMN_TYPE'] . ";";

            if (!$db->rawQuery($alterQuery)) {
                echo "Error altering $auditTable for column " . $column['COLUMN_NAME'] . ": " . $db->getLastError() . "\n";
            } else {
                echo "Column " . $column['COLUMN_NAME'] . " added to $auditTable successfully.\n";
            }
        }
    }
}
