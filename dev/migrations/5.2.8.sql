-- Amit 26-Dec-2023 version 5.2.8
UPDATE `system_config` SET `value` = '5.2.8' WHERE `system_config`.`name` = 'sc_version';

-- Jeyabanu 26-Dec-2023
CREATE TABLE `temp_mail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `test_type` varchar(25) DEFAULT NULL,
  `samples` varchar(256) DEFAULT NULL,
  `to_mail` varchar(255) DEFAULT NULL,
  `report_email` varchar(256) DEFAULT NULL,
  `subject` varchar(200) DEFAULT NULL,
  `text_message` varchar(255) DEFAULT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  `status` varchar(11) DEFAULT NULL,
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Amit 27-Dec-2023
UPDATE form_vl
SET patient_art_no = REPLACE(patient_art_no, 'string', '')
WHERE patient_art_no like '%string';

-- Jeyabanu 29-Dec-2023
INSERT INTO `privileges` (`privilege_id`, `resource_id`, `privilege_name`, `shared_privileges`, `display_name`, `display_order`, `show_mode`) VALUES (NULL, 'generic-results', '/generic-tests/results/email-results.php', '[\"/vl/results/email-results.php\", \"/vl/results/email-results-confirm.php\"\r\n]', 'Email Test Result', NULL, 'always');

INSERT INTO `s_app_menu` (`id`, `module`, `sub_module`, `is_header`, `display_text`, `link`, `inner_pages`, `show_mode`, `icon`, `has_children`, `additional_class_names`, `parent_id`, `display_order`, `status`, `updated_datetime`) VALUES (NULL, 'generic-tests', NULL, 'no', 'E-mail Test Result', '/generic-tests/results/email-results.php', NULL, 'always', 'fa-solid fa-caret-right', 'no', 'allMenu vlResultMailMenu', '62', '177', 'active', CURRENT_TIMESTAMP);


-- Amit 08-Jan-2024
-- Adding these again

ALTER TABLE `audit_form_vl` DROP `is_adherance_poor`;
ALTER TABLE `audit_form_vl` DROP `number_of_enhanced_sessions`;
ALTER TABLE `audit_form_vl` DROP `patient_tb`;
ALTER TABLE `audit_form_vl` DROP `patient_tb_yes`;
ALTER TABLE `audit_form_vl` DROP `patient_drugs_transmission`;
ALTER TABLE `audit_form_vl` DROP `patient_receiving_therapy`;
ALTER TABLE `audit_form_vl` DROP `patient_art_date`;
ALTER TABLE `audit_form_vl` DROP `consultation`;
ALTER TABLE `audit_form_vl` DROP `first_viral_load`;
ALTER TABLE `audit_form_vl` DROP `sample_processed`;
ALTER TABLE `audit_form_vl` DROP `collection_type`;
ALTER TABLE `audit_form_vl` DROP `collection_site`;
ALTER TABLE `audit_form_vl` DROP `requesting_vl_service_sector`;
ALTER TABLE `audit_form_vl` DROP `requesting_category`;
ALTER TABLE `audit_form_vl` DROP `requesting_professional_number`;

