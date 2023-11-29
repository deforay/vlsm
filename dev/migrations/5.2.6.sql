-- Amit 22-Nov-2023 version 5.2.6
UPDATE `system_config` SET `value` = '5.2.6' WHERE `system_config`.`name` = 'sc_version';

-- Jeyabanu 24-Nov-2023
ALTER TABLE `patients` CHANGE `patient_code_key` `patient_code_key` INT NULL DEFAULT NULL;

-- Jeyabanu 27-Nov-2023
INSERT INTO `global_config` (`display_name`, `name`, `value`, `category`, `remote_sync_needed`, `updated_on`, `updated_by`, `status`) VALUES ('CSV Delimiter', 'default_csv_delimiter', ',', 'general', 'no', NULL, NULL, 'active'), ('CSV Enclosure', 'default_csv_enclosure', '"', 'general', 'no', NULL, NULL, 'active');

-- Thana 28-Nov-2023
ALTER TABLE `generic_test_result_units_map` ADD FOREIGN KEY (`test_type_id`) REFERENCES `r_test_types`(`test_type_id`) ON DELETE RESTRICT ON UPDATE RESTRICT; ALTER TABLE `generic_test_result_units_map` ADD FOREIGN KEY (`unit_id`) REFERENCES `r_generic_test_result_units`(`unit_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;
ALTER TABLE `patients` ADD `is_encrypted` VARCHAR(256) NULL DEFAULT NULL AFTER `patient_code`;
ALTER TABLE `generic_test_results` ADD `sub_test_name` VARCHAR(256) NULL DEFAULT NULL AFTER `facility_id`;
ALTER TABLE `form_generic` ADD `sub_tests` TEXT NULL DEFAULT NULL AFTER `test_type`;
ALTER TABLE `generic_test_results` ADD `final_result_unit` VARCHAR(256) NULL DEFAULT NULL AFTER `sub_test_name`, ADD `result_type` VARCHAR(256) NULL DEFAULT NULL AFTER `final_result_unit`;
ALTER TABLE `generic_test_results` ADD `final_result` VARCHAR(256) NULL DEFAULT NULL AFTER `result`;



-- Amit 28-Nov-2023
-- DROP TABLE IF EXISTS `sequence_counter`;
CREATE TABLE IF NOT EXISTS sequence_counter (
    test_type VARCHAR(255),
    year INT,
    code_type VARCHAR(255) COMMENT 'sample_code or remote_sample_code',
    max_sequence_number INT,
    PRIMARY KEY (test_type, year, code_type)
);


INSERT INTO sequence_counter (test_type, year, code_type, max_sequence_number)
SELECT 'vl' AS test_type, YEAR(sample_collection_date) AS year, 'sample_code' AS code_type, MAX(sample_code_key) AS max_sequence_number
FROM form_vl
GROUP BY YEAR(sample_collection_date)
HAVING MAX(sample_code_key) IS NOT NULL
ON DUPLICATE KEY UPDATE
max_sequence_number = GREATEST(VALUES(max_sequence_number), max_sequence_number);


INSERT INTO sequence_counter (test_type, year, code_type, max_sequence_number)
SELECT 'vl' AS test_type, YEAR(sample_collection_date) AS year, 'remote_sample_code' AS code_type, MAX(remote_sample_code_key) AS max_sequence_number
FROM form_vl
GROUP BY YEAR(sample_collection_date)
HAVING MAX(remote_sample_code_key) IS NOT NULL
ON DUPLICATE KEY UPDATE
max_sequence_number = GREATEST(VALUES(max_sequence_number), max_sequence_number);


INSERT INTO sequence_counter (test_type, year, code_type, max_sequence_number)
SELECT 'eid' AS test_type, YEAR(sample_collection_date) AS year, 'sample_code' AS code_type, MAX(sample_code_key) AS max_sequence_number
FROM form_eid
GROUP BY YEAR(sample_collection_date)
HAVING MAX(sample_code_key) IS NOT NULL
ON DUPLICATE KEY UPDATE
max_sequence_number = GREATEST(VALUES(max_sequence_number), max_sequence_number);


INSERT INTO sequence_counter (test_type, year, code_type, max_sequence_number)
SELECT 'eid' AS test_type, YEAR(sample_collection_date) AS year, 'remote_sample_code' AS code_type, MAX(remote_sample_code_key) AS max_sequence_number
FROM form_eid
GROUP BY YEAR(sample_collection_date)
HAVING MAX(remote_sample_code_key) IS NOT NULL
ON DUPLICATE KEY UPDATE
max_sequence_number = GREATEST(VALUES(max_sequence_number), max_sequence_number);

INSERT INTO sequence_counter (test_type, year, code_type, max_sequence_number)
SELECT 'tb' AS test_type, YEAR(sample_collection_date) AS year, 'sample_code' AS code_type, MAX(sample_code_key) AS max_sequence_number
FROM form_tb
GROUP BY YEAR(sample_collection_date)
HAVING MAX(sample_code_key) IS NOT NULL
ON DUPLICATE KEY UPDATE
max_sequence_number = GREATEST(VALUES(max_sequence_number), max_sequence_number);

INSERT INTO sequence_counter (test_type, year, code_type, max_sequence_number)
SELECT 'tb' AS test_type, YEAR(sample_collection_date) AS year, 'remote_sample_code' AS code_type, MAX(remote_sample_code_key) AS max_sequence_number
FROM form_tb
GROUP BY YEAR(sample_collection_date)
HAVING MAX(remote_sample_code_key) IS NOT NULL
ON DUPLICATE KEY UPDATE
max_sequence_number = GREATEST(VALUES(max_sequence_number), max_sequence_number);

INSERT INTO sequence_counter (test_type, year, code_type, max_sequence_number)
SELECT 'covid19' AS test_type, YEAR(sample_collection_date) AS year, 'sample_code' AS code_type, MAX(sample_code_key) AS max_sequence_number
FROM form_covid19
GROUP BY YEAR(sample_collection_date)
HAVING MAX(sample_code_key) IS NOT NULL
ON DUPLICATE KEY UPDATE
max_sequence_number = GREATEST(VALUES(max_sequence_number), max_sequence_number);

INSERT INTO sequence_counter (test_type, year, code_type, max_sequence_number)
SELECT 'covid19' AS test_type, YEAR(sample_collection_date) AS year, 'remote_sample_code' AS code_type, MAX(remote_sample_code_key) AS max_sequence_number
FROM form_covid19
GROUP BY YEAR(sample_collection_date)
HAVING MAX(remote_sample_code_key) IS NOT NULL
ON DUPLICATE KEY UPDATE
max_sequence_number = GREATEST(VALUES(max_sequence_number), max_sequence_number);

INSERT INTO sequence_counter (test_type, year, code_type, max_sequence_number)
SELECT 'hepatitis' AS test_type, YEAR(sample_collection_date) AS year, 'sample_code' AS code_type, MAX(sample_code_key) AS max_sequence_number
FROM form_hepatitis
GROUP BY YEAR(sample_collection_date)
HAVING MAX(sample_code_key) IS NOT NULL
ON DUPLICATE KEY UPDATE
max_sequence_number = GREATEST(VALUES(max_sequence_number), max_sequence_number);

INSERT INTO sequence_counter (test_type, year, code_type, max_sequence_number)
SELECT 'hepatitis' AS test_type, YEAR(sample_collection_date) AS year, 'remote_sample_code' AS code_type, MAX(remote_sample_code_key) AS max_sequence_number
FROM form_hepatitis
GROUP BY YEAR(sample_collection_date)
HAVING MAX(remote_sample_code_key) IS NOT NULL
ON DUPLICATE KEY UPDATE
max_sequence_number = GREATEST(VALUES(max_sequence_number), max_sequence_number);

INSERT INTO sequence_counter (test_type, year, code_type, max_sequence_number)
SELECT test_short_code, YEAR(sample_collection_date) AS year, 'sample_code' AS code_type, MAX(sample_code_key) AS max_sequence_number
FROM form_generic
INNER JOIN r_test_types ON r_test_types.test_type_id = form_generic.test_type
GROUP BY test_short_code, YEAR(sample_collection_date)
HAVING MAX(sample_code_key) IS NOT NULL
ON DUPLICATE KEY UPDATE
max_sequence_number = GREATEST(VALUES(max_sequence_number), max_sequence_number);


INSERT INTO sequence_counter (test_type, year, code_type, max_sequence_number)
SELECT test_short_code, YEAR(sample_collection_date) AS year, 'remote_sample_code' AS code_type, MAX(remote_sample_code_key) AS max_sequence_number
FROM form_generic
INNER JOIN r_test_types ON r_test_types.test_type_id = form_generic.test_type
GROUP BY test_short_code, YEAR(sample_collection_date)
HAVING MAX(remote_sample_code_key) IS NOT NULL
ON DUPLICATE KEY UPDATE
max_sequence_number = GREATEST(VALUES(max_sequence_number), max_sequence_number);



-- Amit 29-Nov-2023
ALTER TABLE `s_vlsm_instance` ADD `last_interface_sync` DATETIME NULL DEFAULT NULL AFTER `last_remote_reference_data_sync`;
ALTER TABLE `form_tb` CHANGE `sample_received_at_vl_lab_datetime` `sample_received_at_lab_datetime` DATETIME NULL DEFAULT NULL;
ALTER TABLE `audit_form_tb` CHANGE `sample_received_at_vl_lab_datetime` `sample_received_at_lab_datetime` DATETIME NULL DEFAULT NULL;
-- UPDATE `patients` SET `patient_code_key` = NULL WHERE  `patient_code_key` = 0;
-- ALTER TABLE `patients` CHANGE `patient_code` `system_patient_code` VARCHAR(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL;
-- UPDATE `patients` set `patient_code` = `system_patient_code`;
-- UPDATE `patients` SET `patient_code_prefix` = 'P' WHERE  `patient_code_prefix` IS NULL OR  `patient_code_prefix` like '';
-- ALTER TABLE `patients` ADD `patient_code` VARCHAR(256) NULL DEFAULT NULL AFTER `system_patient_code`;
-- ALTER TABLE `patients` ADD UNIQUE(`patient_code`, `patient_gender`, `patient_dob`);

RENAME TABLE `patients` TO `patients_old`;
CREATE TABLE `patients` (
 `system_patient_code` varchar(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
 `is_encrypted` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
 `patient_code_prefix` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
 `patient_code_key` int DEFAULT NULL,
 `patient_code` varchar(256) COLLATE utf8mb4_general_ci DEFAULT NULL,
 `patient_first_name` text COLLATE utf8mb4_general_ci,
 `patient_middle_name` text COLLATE utf8mb4_general_ci,
 `patient_last_name` text COLLATE utf8mb4_general_ci,
 `patient_gender` varchar(256) COLLATE utf8mb4_general_ci DEFAULT NULL,
 `patient_phone_number` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
 `patient_age_in_years` int DEFAULT NULL,
 `patient_age_in_months` int DEFAULT NULL,
 `patient_dob` date DEFAULT NULL,
 `patient_address` text COLLATE utf8mb4_general_ci,
 `is_patient_pregnant` varchar(10) COLLATE utf8mb4_general_ci DEFAULT NULL,
 `is_patient_breastfeeding` varchar(10) COLLATE utf8mb4_general_ci DEFAULT NULL,
 `patient_province` int DEFAULT NULL,
 `patient_district` int DEFAULT NULL,
 `status` varchar(11) COLLATE utf8mb4_general_ci DEFAULT NULL,
 `patient_registered_on` datetime DEFAULT NULL,
 `patient_registered_by` text COLLATE utf8mb4_general_ci,
 `updated_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY (`system_patient_code`),
 UNIQUE KEY `patient_code_prefix` (`patient_code_prefix`,`patient_code_key`),
 UNIQUE KEY `single_patient` (`patient_code`,`patient_gender`,`patient_dob`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
