

ALTER TABLE vl_request_form DROP FOREIGN KEY vl_request_form_ibfk_2

ALTER TABLE `vl_request_form` CHANGE `art_no` `art_no` VARCHAR( 255 ) NULL DEFAULT NULL ;

--ilahir 28-Jul-2016

ALTER TABLE  `vl_request_form` ADD  `sample_code` VARCHAR( 255 ) NULL DEFAULT NULL AFTER  `facility_id` ,
ADD UNIQUE (
`sample_code`
);

--saravanan 29-jul-2016
ALTER TABLE  `vl_request_form` ADD  `batch_id` VARCHAR( 11 ) NULL DEFAULT NULL AFTER  `facility_id` ;

CREATE TABLE IF NOT EXISTS `batch_details` (
  `batch_id` int(11) NOT NULL AUTO_INCREMENT,
  `batch_code` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`batch_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

ALTER TABLE  `vl_request_form` ADD  `result` VARCHAR( 255 ) NULL DEFAULT NULL ;

--ilahir 04-Aug-2016

ALTER TABLE  `vl_request_form` ADD  `lab_contact_person` VARCHAR( 255 ) NULL DEFAULT NULL AFTER  `treatment_details` ;
ALTER TABLE  `vl_request_form` ADD  `lab_phone_no` VARCHAR( 255 ) NULL DEFAULT NULL AFTER  `lab_contact_person` ;
ALTER TABLE  `vl_request_form` ADD  `lab_sample_received_date` DATE NULL DEFAULT NULL AFTER  `lab_phone_no` ;
ALTER TABLE  `vl_request_form` ADD  `lab_dispatched_date` DATE NULL DEFAULT NULL AFTER  `lab_sample_received_date` ;
ALTER TABLE  `vl_request_form` ADD  `lab_tested_date` DATE NULL DEFAULT NULL AFTER  `lab_sample_received_date` ;
ALTER TABLE  `vl_request_form` ADD  `comments` TEXT NULL DEFAULT NULL ;
ALTER TABLE  `vl_request_form` ADD  `result_reviewed_by` VARCHAR( 255 ) NULL DEFAULT NULL ;
ALTER TABLE  `vl_request_form` ADD  `result_reviewed_date` DATE NULL DEFAULT NULL AFTER  `result_reviewed_by` ;
ALTER TABLE  `vl_request_form` ADD  `status` VARCHAR( 255 ) NULL DEFAULT NULL ;

--Pal 05-08-2016
ALTER TABLE `vl_request_form` ADD `lab_name` VARCHAR(255) NULL DEFAULT NULL AFTER `treatment_details`;
ALTER TABLE `vl_request_form` ADD `justification` VARCHAR(255) NULL DEFAULT NULL AFTER `lab_tested_date`;

--Pal 05-08-2016
CREATE TABLE `global_config` (
  `name` varchar(255) NOT NULL,
  `value` mediumtext
)

INSERT INTO `global_config` (`name`, `value`) VALUES
('logo', '');

--Pal 08-08-2016
INSERT INTO `global_config` (`name`, `value`) VALUES ('header', NULL);

ALTER TABLE `vl_request_form` ADD `log_value` VARCHAR(255) NULL DEFAULT NULL AFTER `justification`, ADD `absolute_value` VARCHAR(255) NULL DEFAULT NULL AFTER `log_value`, ADD `text_value` VARCHAR(255) NULL DEFAULT NULL AFTER `absolute_value`;


ALTER TABLE  `vl_request_form` CHANGE  `status`  `status` INT NOT NULL ;
CREATE TABLE IF NOT EXISTS `r_testing_status` (
  `status_id` int(11) NOT NULL AUTO_INCREMENT,
  `status_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`status_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

--
-- Dumping data for table `r_testing_status`
--

INSERT INTO `r_testing_status` (`status_id`, `status_name`) VALUES
(1, 'waiting'),
(2, 'lost'),
(3, 'sample reordered'),
(4, 'cancel'),
(5, 'invalid');

ALTER TABLE vl_request_form
ADD FOREIGN KEY (status)
REFERENCES r_testing_status(status_id)


--ilahir 09-Aug-2016

CREATE TABLE IF NOT EXISTS `import_config` (
  `config_id` int(11) NOT NULL AUTO_INCREMENT,
  `machine_name` varchar(255) DEFAULT NULL,
  `log_absolute_val_same_col` varchar(100) DEFAULT NULL,
  `sample_id_col` varchar(100) DEFAULT NULL,
  `sample_id_row` varchar(100) DEFAULT NULL,
  `log_val_col` varchar(100) DEFAULT NULL,
  `log_val_row` varchar(100) DEFAULT NULL,
  `absolute_val_col` varchar(100) DEFAULT NULL,
  `absolute_val_row` varchar(100) DEFAULT NULL,
  `text_val_col` varchar(100) DEFAULT NULL,
  `text_val_row` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`config_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2  ;


INSERT INTO `import_config` (`config_id`, `machine_name`, `log_absolute_val_same_col`, `sample_id_col`, `sample_id_row`, `log_val_col`, `log_val_row`, `absolute_val_col`, `absolute_val_row`, `text_val_col`, `text_val_row`) VALUES
(1, 'Machine 1', 'yes', 'E', '1', 'I', '1', '', '', 'I', '1');


--Pal 09-08-2016
ALTER TABLE `import_config` ADD `status` VARCHAR(45) NOT NULL DEFAULT 'active' AFTER `text_val_row`;

--saravanan 09-aug-2016

ALTER TABLE  `facility_details` ADD  `email` VARCHAR( 255 ) NULL DEFAULT NULL AFTER  `facility_code` ,
ADD  `contact_person` VARCHAR( 255 ) NULL DEFAULT NULL AFTER  `email` ;


CREATE TABLE IF NOT EXISTS `facility_type` (
  `facility_type_id` int(11) NOT NULL AUTO_INCREMENT,
  `facility_type_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`facility_type_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `facility_type`
--

INSERT INTO `facility_type` (`facility_type_id`, `facility_type_name`) VALUES
(1, 'clinic'),
(2, 'lab'),
(3, 'hub');

ALTER TABLE  `facility_details` ADD  `facility_type` INT NULL DEFAULT NULL AFTER  `hub_name` ;


ALTER TABLE  `vl_request_form` ADD  `location` VARCHAR( 255 ) NULL DEFAULT NULL AFTER  `patient_mobile_number` ;
--saravaanna10-aug-2016
ALTER TABLE  `batch_details` ADD  `created_on` DATETIME NOT NULL ;
ALTER TABLE  `batch_details` ADD  `batch_status` VARCHAR( 255 ) NOT NULL DEFAULT  'pending' AFTER  `batch_code` ;
INSERT INTO `vl_lab_request`.`global_config` (`name`, `value`) VALUES ('email', 'zfmailexample@gmail.com'), ('password', 'mko09876');

--Pal 12-08-2016
DELETE FROM `global_config` WHERE name ="email"
DELETE FROM `global_config` WHERE name ="password"

CREATE TABLE `other_config` (
  `name` varchar(255) NOT NULL,
  `value` mediumtext
)

INSERT INTO `other_config` (`name`, `value`) VALUES
('email', 'zfmailexample@gmail.com'),
('password', 'mko09876');

--ilahir 12-Aug-2016
ALTER TABLE `batch_details` ADD `sent_mail` VARCHAR(100) NOT NULL DEFAULT 'no' AFTER `batch_status`;
--saravanana 12-aug-2016

CREATE TABLE IF NOT EXISTS `resources` (
  `resource_id` int(11) NOT NULL AUTO_INCREMENT,
  `resource_name` varchar(255) DEFAULT NULL,
  `display_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`resource_id`),
  UNIQUE KEY `resource_name` (`resource_name`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=15 ;

--
-- Dumping data for table `resources`
--

INSERT INTO `resources` (`resource_id`, `resource_name`, `display_name`) VALUES
(1, 'users', 'Manage Users'),
(2, 'facility', 'Manage Facility'),
(3, 'global_config', 'Manage General Config'),
(4, 'import_config', 'Manage Import Config'),
(5, 'other_config', 'Manage Other Config'),
(6, 'vl_test_request', 'Manage Vl Request'),
(7, 'batch', 'Manage Batch'),
(8, 'import_result', 'Manage Import Result'),
(9, 'vl_print_result', 'Manage Print Result'),
(10, 'vl_enter_result', 'Manage Enter Result'),
(11, 'missing_result', 'Manage Missing Result'),
(12, 'export_result', 'Manage Export Result'),
(13, 'home', 'Manage Home Page'),
(14, 'roles', 'Manage Roles');

CREATE TABLE IF NOT EXISTS `privileges` (
  `privilege_id` int(11) NOT NULL AUTO_INCREMENT,
  `resource_id` int(11) NOT NULL,
  `privilege_name` varchar(255) DEFAULT NULL,
  `display_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`privilege_id`),
  KEY `resource_id` (`resource_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=31 ;

--
-- Dumping data for table `privileges`
--

INSERT INTO `privileges` (`privilege_id`, `resource_id`, `privilege_name`, `display_name`) VALUES
(1, 1, 'users.php', 'Access'),
(2, 1, 'addUser.php', 'Add'),
(3, 1, 'editUser.php', 'Edit'),
(4, 2, 'facilities.php', 'Access'),
(5, 2, 'addFacility.php', 'Add'),
(6, 2, 'editFacility.php', 'Edit'),
(7, 3, 'globalConfig.php', 'Access'),
(8, 3, 'editGlobalConfig.php', 'Edit'),
(9, 4, 'importConfig.php', 'Access'),
(10, 4, 'addImportConfig.php', 'Add'),
(11, 4, 'editImportConfig.php', 'Edit'),
(12, 6, 'vlRequest.php', 'Access'),
(13, 6, 'addVlRequest.php', 'Add'),
(14, 6, 'editVlRequest.php', 'Edit'),
(15, 6, 'viewVlRequest.php', 'View Vl Request'),
(16, 7, 'batchcode.php', 'Access'),
(17, 7, 'addBatch.php', 'Add'),
(18, 7, 'editBatch.php', 'Edit'),
(19, 8, 'addImportResult.php', 'Add'),
(20, 9, 'vlPrintResult.php', 'Access'),
(21, 10, 'vlTestResult.php', 'Access'),
(22, 11, 'missingResult.php', 'Access'),
(23, 12, 'vlResult.php', 'Access'),
(24, 13, 'index.php', 'Access'),
(25, 14, 'roles.php', 'Access'),
(26, 14, 'editRole.php', 'Edit'),
(27, 6, 'vlRequestMail.php', 'Email Test Request'),
(28, 5, 'otherConfig.php', 'Manage Other Config'),
(29, 6, 'sendRequestToMail.php', 'Send Request to Mail'),
(30, 5, 'editOtherConfig.php', 'Edit Other Config');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `privileges`
--
ALTER TABLE `privileges`
  ADD CONSTRAINT `privileges_ibfk_1` FOREIGN KEY (`resource_id`) REFERENCES `resources` (`resource_id`);
  
  CREATE TABLE IF NOT EXISTS `roles_privileges_map` (
  `map_id` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` int(11) NOT NULL,
  `privilege_id` int(11) NOT NULL,
  PRIMARY KEY (`map_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=375 ;

--
-- Dumping data for table `roles_privileges_map`
--

INSERT INTO `roles_privileges_map` (`map_id`, `role_id`, `privilege_id`) VALUES
(345, 1, 1),
(346, 1, 2),
(347, 1, 3),
(348, 1, 4),
(349, 1, 5),
(350, 1, 6),
(351, 1, 7),
(352, 1, 8),
(353, 1, 9),
(354, 1, 10),
(355, 1, 11),
(356, 1, 28),
(357, 1, 30),
(358, 1, 12),
(359, 1, 13),
(360, 1, 14),
(361, 1, 15),
(362, 1, 27),
(363, 1, 29),
(364, 1, 16),
(365, 1, 17),
(366, 1, 18),
(367, 1, 19),
(368, 1, 20),
(369, 1, 21),
(370, 1, 22),
(371, 1, 23),
(372, 1, 24),
(373, 1, 25),
(374, 1, 26);


-- Amit Aug 13 2016

ALTER TABLE `import_config` CHANGE `log_absolute_val_same_col` `file_name` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;
ALTER TABLE `other_config` ADD PRIMARY KEY(`name`);
INSERT INTO `r_testing_status` (`status_id`, `status_name`) VALUES (NULL, 'Awaiting Clinic Approval'), (NULL, 'Received and Approved');
INSERT INTO `resources` (`resource_id`, `resource_name`, `display_name`) VALUES (NULL, 'approve_results', 'Approve Imported Results');
INSERT INTO  `privileges` (`privilege_id`, `resource_id`, `privilege_name`, `display_name`) VALUES (NULL, '15', 'access', 'access');

--saravanan 13-aug-2016
INSERT INTO  `privileges` (`privilege_id`, `resource_id`, `privilege_name`, `display_name`) VALUES (NULL, '10', 'updateVlTestResult.php', 'Update Vl Test Result');

--Pal 16-aug-2016
INSERT INTO `global_config` (`name`, `value`) VALUES ('max_no_of_samples_in_a_batch', NULL);

--saravanana 16-aug-2016
INSERT INTO `vl_lab_request`.`resources` (`resource_id`, `resource_name`, `display_name`) VALUES (NULL, 'high_viral_load', 'Manage High Viral Load Result');
INSERT INTO `vl_lab_request`.`privileges` (`privilege_id`, `resource_id`, `privilege_name`, `display_name`) VALUES (NULL, '16', 'highViralLoad.php', 'Access');
INSERT INTO `vl_lab_request`.`privileges` (`privilege_id`, `resource_id`, `privilege_name`, `display_name`) VALUES (NULL, '16', 'addContactNotes.php', 'Manage Contact Notes');
ALTER TABLE  `vl_request_form` ADD  `contact_complete_status` VARCHAR( 255 ) NULL DEFAULT NULL AFTER  `result_reviewed_date` ;

CREATE TABLE IF NOT EXISTS `contact_notes_details` (
  `contact_notes_id` int(11) NOT NULL AUTO_INCREMENT,
  `treament_contact_id` int(11) DEFAULT NULL,
  `contact_notes` text,
  `added_on` datetime DEFAULT NULL,
  PRIMARY KEY (`contact_notes_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--saravanan 17-aug-2016
ALTER TABLE contact_notes_details ADD FOREIGN KEY (treament_contact_id) REFERENCES vl_request_form(treament_id);

ALTER TABLE roles_privileges_map ADD FOREIGN KEY (role_id) REFERENCES roles(role_id);
ALTER TABLE roles_privileges_map ADD FOREIGN KEY (privilege_id) REFERENCES privileges(privilege_id);
ALTER TABLE report_to_mail ADD FOREIGN KEY ( batch_id ) REFERENCES batch_details( batch_id );

--Pal 17th Aug'16--
ALTER TABLE `batch_details` CHANGE `batch_status` `batch_status` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'completed';
--saravanan 18-aug-2016
ALTER TABLE  `contact_notes_details` ADD  `collected_on` DATE NULL DEFAULT NULL AFTER  `contact_notes` ;

--Pal 19th Aug'16--
ALTER TABLE `vl_request_form` CHANGE `result_reviewed_by` `result_reviewed_by` INT(11) NULL DEFAULT NULL;

--saravanan 26-aug-2016
ALTER TABLE  `facility_details` ADD  `district` VARCHAR( 255 ) NULL DEFAULT NULL AFTER  `state` ;
ALTER TABLE  `facility_details` ADD  `other_id` VARCHAR( 255 ) NULL DEFAULT NULL AFTER  `facility_code` ;
ALTER TABLE  `vl_request_form` ADD  `patient_receive_sms` VARCHAR( 255 ) NULL DEFAULT NULL AFTER  `arv_adherance_percentage` ;
ALTER TABLE  `vl_request_form` ADD  `switch_to_tdf_last_vl_date` DATE NULL DEFAULT NULL AFTER  `last_vl_sample_type_failure` ,
ADD  `switch_to_tdf_value` VARCHAR( 255 ) NULL DEFAULT NULL AFTER  `switch_to_tdf_last_vl_date` ,
ADD  `switch_to_tdf_sample_type` INT NULL DEFAULT NULL AFTER  `switch_to_tdf_value` ,
ADD  `missing_last_vl_date` DATE NULL DEFAULT NULL AFTER  `switch_to_tdf_sample_type` ,
ADD  `missing_value` VARCHAR( 255 ) NULL DEFAULT NULL AFTER  `missing_last_vl_date` ,
ADD  `missing_sample_type` INT NULL DEFAULT NULL AFTER  `missing_value` ;

--saravanan 31-aug-2016
ALTER TABLE  `vl_request_form` ADD  `viral_load_indication` VARCHAR( 255 ) NULL DEFAULT NULL AFTER  `patient_receive_sms` ;
ALTER TABLE  `vl_request_form` ADD  `number_of_enhanced_sessions` VARCHAR( 255 ) NULL DEFAULT NULL AFTER  `viral_load_indication` ;
ALTER TABLE  `vl_request_form` ADD  `test_methods` VARCHAR( 255 ) NULL DEFAULT NULL AFTER  `result_reviewed_date` ;
--ilahir 31-Aug-2016

ALTER TABLE  `vl_request_form` ADD  `absolute_decimal_value` VARCHAR( 255 ) NULL DEFAULT NULL AFTER  `text_value` ;

--Pal 31st Aug'16--
ALTER TABLE `global_config` ADD `display_name` VARCHAR(255) NOT NULL FIRST;

CREATE TABLE `global_config` (
  `display_name` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `value` mediumtext
)

INSERT INTO `global_config` (`display_name`, `name`, `value`) VALUES
('Logo', 'logo', ''),
('Header', 'header', 'MINISTRY OF HEALTH\r\nNATIONAL AIDS AND STD CONTROL PROGRAM\r\nINDIVIDUAL VIRAL LOAD RESULT FORM'),
('Max. no of sample in a batch', 'max_no_of_samples_in_a_batch', '20'),
('Do you want to show smiley in the result PDF?', 'show_smiley', 'yes');

INSERT INTO `vl_lab_request`.`global_config` (`display_name`, `name`, `value`) VALUES ('Patient ART No. Date', 'show_date', 'no');

--saravanan 01-sep-2016
ALTER TABLE  `vl_request_form` ADD  `patient_art_date` DATE NULL DEFAULT NULL AFTER  `location` ;
--saravanan 07-sep-2016
ALTER TABLE  `r_art_code_details` ADD  `nation_identifier` VARCHAR( 255 ) NULL DEFAULT NULL ;
INSERT INTO `vl_lab_request`.`r_art_code_details` (`art_id`, `art_code`, `parent_art`, `nation_identifier`) VALUES (NULL, 'AZT/3TC/NVP', '', 'zmb'), (NULL, 'AZT/3TC/EFV', '', 'zmb'), (NULL, 'TDF/3TC/NVP', '', 'zmb'), (NULL, 'AZT/3TC/LPr', '', 'zmb'), (NULL, 'AZT/3TC/ABC', '', 'zmb'), (NULL, 'TDF/3TC/ATVr', '', 'zmb'), (NULL, 'AZT/3TC/ATVr', '', 'zmb'), (NULL, 'ABC/3TC/ATVr', '', 'zmb'), (NULL, 'ABC/3TC/NVP', '', 'zmb'), (NULL, 'ABC/3TC/EFV', '', 'zmb'), (NULL, 'ABC/3TC/LPVr', '', 'zmb');

INSERT INTO `vl_lab_request`.`r_sample_type` (`sample_id`, `sample_name`) VALUES (NULL, 'Venous blood(EDTA)'), (NULL, 'Frozen Plasma');
INSERT INTO `vl_lab_request`.`r_sample_type` (`sample_id`, `sample_name`) VALUES (NULL, 'Venous DBS(EDTA)'), (NULL, 'CAPILLARY DBS');
ALTER TABLE  `r_sample_type` ADD  `form_identification` INT NULL ;
ALTER TABLE  `vl_request_form` ADD  `collected_by` VARCHAR( 255 ) NULL DEFAULT NULL AFTER  `drug_substitution` ;
ALTER TABLE  `vl_request_form` ADD  `serial_no` VARCHAR( 255 ) NULL DEFAULT NULL AFTER  `form_id` ;
--saravanan 08-09-2016
ALTER TABLE  `vl_request_form` ADD  `sample_code_key` VARCHAR( 255 ) NULL DEFAULT NULL AFTER  `batch_id` ,
ADD  `sample_code_format` VARCHAR( 255 ) NULL DEFAULT NULL AFTER  `sample_code_key` ;
INSERT INTO `vl_lab_request`.`privileges` (`privilege_id`, `resource_id`, `privilege_name`, `display_name`) VALUES (NULL, '6', 'editVlRequestZm.php', 'Edit Request (Zm)');
ALTER TABLE  `vl_request_form` ADD  `vl_test_platform` VARCHAR( 255 ) NULL DEFAULT NULL AFTER  `collected_by` ;

INSERT INTO `vl_lab_request`.`privileges` (`privilege_id`, `resource_id`, `privilege_name`, `display_name`) VALUES (NULL, '6', 'viewVlRequestZm.php', 'View VL Request(Zm)');

--saravanan 12-sep-2016
CREATE TABLE IF NOT EXISTS `temp_sample_report` (
  `temp_sample_id` int(11) NOT NULL AUTO_INCREMENT,
  `lab_name` varchar(255) DEFAULT NULL,
  `lab_contact_person` varchar(255) DEFAULT NULL,
  `lab_phone_no` varchar(255) DEFAULT NULL,
  `date_sample_received_at_testing_lab` varchar(255) DEFAULT NULL,
  `lab_tested_date` varchar(255) DEFAULT NULL,
  `date_results_dispatched` varchar(255) DEFAULT NULL,
  `result_reviewed_date` varchar(255) DEFAULT NULL,
  `result_reviewed_by` varchar(255) DEFAULT NULL,
  `comments` varchar(255) DEFAULT NULL,
  `sample_code` varchar(255) DEFAULT NULL,
  `order_number` varchar(255) DEFAULT NULL,
  `log_value` varchar(255) DEFAULT NULL,
  `absolute_value` varchar(255) DEFAULT NULL,
  `text_value` varchar(255) DEFAULT NULL,
  `absolute_decimal_value` varchar(255) DEFAULT NULL,
  `result` varchar(255) DEFAULT NULL,
  `sample_details` varchar(255) DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`temp_sample_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;

INSERT INTO `vl_lab_request`.`privileges` (`privilege_id`, `resource_id`, `privilege_name`, `display_name`) VALUES (NULL, '15', 'vlResultUnApproval.php', 'Un Approve Result');

ALTER TABLE vl_request_form
DROP FOREIGN KEY vl_request_form_ibfk_1;
ALTER TABLE vl_request_form
DROP FOREIGN KEY vl_request_form_ibfk_3;


CREATE TABLE IF NOT EXISTS `r_sample_rejection_reasons` (
  `rejection_reason_id` int(11) NOT NULL AUTO_INCREMENT,
  `rejection_reason_name` varchar(255) DEFAULT NULL,
  `rejection_reason_status` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rejection_reason_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

ALTER TABLE  `vl_request_form` ADD  `sample_rejection_facility` INT NULL DEFAULT NULL AFTER  `rejection` ,
ADD  `sample_rejection_reason` INT NULL DEFAULT NULL AFTER  `sample_rejection_facility` ;

-- Amit 12 Sep 2016
ALTER TABLE `vl_request_form` CHANGE `request_date` `sample_testing_date` DATE NULL DEFAULT NULL;
ALTER TABLE  `temp_sample_report` ADD  `batch_code` VARCHAR( 255 ) NULL DEFAULT NULL AFTER  `sample_code` ,
ADD  `sample_type` VARCHAR( 255 ) NULL DEFAULT NULL AFTER  `batch_code` ;

ALTER TABLE  `temp_sample_report` ADD  `lab_id` INT NULL DEFAULT NULL AFTER  `lab_name` ;
ALTER TABLE  `vl_request_form` ADD  `lab_id` INT NULL DEFAULT NULL AFTER  `lab_name` ;

ALTER TABLE  `vl_request_form` CHANGE  `date_sample_received_at_testing_lab`  `date_sample_received_at_testing_lab` DATETIME NULL DEFAULT NULL ,
CHANGE  `date_results_dispatched`  `date_results_dispatched` DATETIME NULL DEFAULT NULL ,
CHANGE  `lab_tested_date`  `lab_tested_date` DATETIME NULL DEFAULT NULL ,
CHANGE  `result_reviewed_date`  `result_reviewed_date` DATETIME NULL DEFAULT NULL ;
ALTER TABLE  `temp_sample_report` ADD  `facility_id` INT NULL DEFAULT NULL AFTER  `temp_sample_id` ;

-- Pal 13 Sep 2016
INSERT INTO `global_config` (`display_name`, `name`, `value`) VALUES ('Auto Approval', 'auto_approval', 'yes');

--saravana 13-sep-2016
CREATE TABLE IF NOT EXISTS `hold_sample_report` (
  `hold_sample_id` int(11) NOT NULL AUTO_INCREMENT,
  `facility_id` int(11) DEFAULT NULL,
  `lab_name` varchar(255) DEFAULT NULL,
  `lab_id` int(11) DEFAULT NULL,
  `lab_contact_person` varchar(255) DEFAULT NULL,
  `lab_phone_no` varchar(255) DEFAULT NULL,
  `date_sample_received_at_testing_lab` varchar(255) DEFAULT NULL,
  `lab_tested_date` varchar(255) DEFAULT NULL,
  `date_results_dispatched` varchar(255) DEFAULT NULL,
  `result_reviewed_date` varchar(255) DEFAULT NULL,
  `result_reviewed_by` varchar(255) DEFAULT NULL,
  `comments` varchar(255) DEFAULT NULL,
  `sample_code` varchar(255) DEFAULT NULL,
  `batch_code` varchar(255) DEFAULT NULL,
  `sample_type` varchar(255) DEFAULT NULL,
  `order_number` varchar(255) DEFAULT NULL,
  `log_value` varchar(255) DEFAULT NULL,
  `absolute_value` varchar(255) DEFAULT NULL,
  `text_value` varchar(255) DEFAULT NULL,
  `absolute_decimal_value` varchar(255) DEFAULT NULL,
  `result` varchar(255) DEFAULT NULL,
  `sample_details` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`hold_sample_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
ALTER TABLE  `hold_sample_report` ADD  `status` VARCHAR( 255 ) NULL DEFAULT NULL ;

--Pal 14th-Sep'16

CREATE TABLE `activity_log` (
  `log_id` int(11) NOT NULL,
  `event_type` varchar(255) DEFAULT NULL,
  `action` varchar(255) DEFAULT NULL,
  `resource` varchar(255) DEFAULT NULL,
  `date_time` datetime DEFAULT NULL
)

ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`log_id`);
  
ALTER TABLE `activity_log`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
  
--saravanna 14-sep-2016
ALTER TABLE  `hold_sample_report` ADD  `controller_track` INT NULL DEFAULT NULL ;
ALTER TABLE  `hold_sample_report` CHANGE  `controller_track`  `import_batch_tracking` INT( 11 ) NULL DEFAULT NULL ;
ALTER TABLE  `vl_request_form` ADD  `modified_on` DATETIME NULL DEFAULT NULL AFTER  `created_on` ;
ALTER TABLE  `vl_request_form` CHANGE  `lab_no`  `lab_no` INT NULL DEFAULT NULL ;

--saravanan 16-sep-2016
ALTER TABLE  `vl_request_form` ADD  `result_approved_by` INT NULL DEFAULT NULL AFTER  `comments` ,
ADD  `result_approved_on` DATETIME NULL DEFAULT NULL AFTER  `result_approved_by` ;

ALTER TABLE  `batch_details` ADD  `batch_code_key` VARCHAR( 255 ) NULL DEFAULT NULL AFTER  `batch_code` ;
ALTER TABLE  `batch_details` CHANGE  `batch_code`  `batch_code` INT( 11 ) NULL DEFAULT NULL ;

ALTER TABLE  `temp_sample_report` CHANGE  `batch_code_key`  `batch_code_key` INT( 11 ) NULL DEFAULT NULL ;
ALTER TABLE  `temp_sample_report` ADD  `file_name` VARCHAR( 255 ) NULL DEFAULT NULL ;
ALTER TABLE  `vl_request_form` ADD  `file_name` VARCHAR( 255 ) NULL DEFAULT NULL ;
ALTER TABLE  `hold_sample_report` ADD  `file_name` VARCHAR( 255 ) NULL DEFAULT NULL ;

ALTER TABLE  `batch_details` CHANGE  `batch_code_key`  `batch_code_key` VARCHAR( 255 ) NULL DEFAULT NULL ;

--saravanan 20-sep-2016
INSERT INTO `vl_lab_request`.`privileges` (`privilege_id`, `resource_id`, `privilege_name`, `display_name`) VALUES (NULL, '14', 'addRole.php', 'Add');

--Pal 20th-Sep'16
INSERT INTO `vl_lab_request`.`privileges` (`privilege_id`, `resource_id`, `privilege_name`, `display_name`) VALUES (NULL, '11', 'vlTestResultStatus.php', 'VL Test Result Status');

--Pal 21st-Sep'16
INSERT INTO `global_config` (`display_name`, `name`, `value`) VALUES ('Default Time Zone', 'default_time_zone', 'Africa/Harare');


--saravanan 22-sep-2016
ALTER TABLE  `vl_request_form` CHANGE  `result_approved_by`  `result_approved_by` VARCHAR( 255 ) NULL DEFAULT NULL ;
ALTER TABLE  `vl_request_form` CHANGE  `result_reviewed_by`  `result_reviewed_by` VARCHAR( 255 ) NULL DEFAULT NULL ;

--Pal 22nd-Sep'16
ALTER TABLE `vl_request_form` ADD `date_result_printed` DATETIME NULL DEFAULT NULL AFTER `vl_test_platform`;

INSERT INTO `vl_lab_request`.`privileges` (`privilege_id`, `resource_id`, `privilege_name`, `display_name`) VALUES (NULL, '6', 'patientList.php', 'Export Patient List');


-- Amit 24 Sep 2016
ALTER TABLE `vl_request_form` ADD `modified_by` INT NULL DEFAULT NULL AFTER `created_on`;

-- Amit 25 Sep 2016
ALTER TABLE `vl_request_form` CHANGE `treament_id` `vl_sample_id` INT(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `temp_sample_report` ADD `vl_test_platform` VARCHAR(255) NULL AFTER `file_name`;
-- Pal 28 Sep 2016
update global_config set value = 3 where name = "vl_form"
--saravanna 28-sep-2016
ALTER TABLE  `vl_request_form` ADD  `vl_instance_id` VARCHAR( 255 ) NOT NULL AFTER  `vl_sample_id` ;
ALTER TABLE  `facility_details` ADD  `vl_instance_id` VARCHAR( 255 ) NOT NULL AFTER  `facility_code` ;
ALTER TABLE vl_request_form ADD FOREIGN KEY (vl_instance_id) REFERENCES vl_instance(vl_instance_id)
ALTER TABLE facility_details ADD FOREIGN KEY (vl_instance_id) REFERENCES vl_instance(vl_instance_id)

CREATE TABLE IF NOT EXISTS `vl_instance` (
  `vl_instance_id` varchar(255) NOT NULL,
  UNIQUE KEY `vl_instance_id` (`vl_instance_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Pal 30 Sep 2016
ALTER TABLE `vl_request_form` ADD `service` VARCHAR(255) NULL DEFAULT NULL AFTER `vl_test_platform`;

ALTER TABLE `vl_request_form` ADD `support_partner` VARCHAR(255) NULL DEFAULT NULL AFTER `service`;

ALTER TABLE `vl_request_form` ADD `has_patient_changed_regimen` VARCHAR(45) NULL DEFAULT NULL AFTER `support_partner`;

ALTER TABLE `vl_request_form` ADD `reason_for_regimen_change` VARCHAR(255) NULL DEFAULT NULL AFTER `has_patient_changed_regimen`, ADD `regimen_change_date` DATE NULL DEFAULT NULL AFTER `reason_for_regimen_change`;

ALTER TABLE `vl_request_form` ADD `plasma_storage_temperature` FLOAT NULL DEFAULT NULL AFTER `regimen_change_date`;


-- Pal 01 Oct 2016
ALTER TABLE `vl_request_form` CHANGE `age_in_mnts` `age_in_mnts` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;

ALTER TABLE `vl_request_form` ADD `duration_of_conservation` VARCHAR(45) NULL DEFAULT NULL AFTER `plasma_storage_temperature`;

ALTER TABLE `vl_request_form` CHANGE `plasma_storage_temperature` `plasma_conservation_temperature` FLOAT NULL DEFAULT NULL;

--ilahir 01-Oct-2016
ALTER TABLE  `facility_details` ADD  `latitude` VARCHAR( 255 ) NULL DEFAULT NULL AFTER  `hub_name` ,
ADD  `longitude` VARCHAR( 255 ) NULL DEFAULT NULL AFTER  `latitude` ;

--saravanan -01-oct-2016
ALTER TABLE  `vl_request_form` CHANGE  `sample_testing_date`  `sample_testing_date` DATETIME NULL DEFAULT NULL ;

INSERT INTO `r_vl_test_reasons` (`test_reason_id`, `test_reason_name`, `test_reason_status`) VALUES
(1, 'routine VL', 'active'),
(2, 'Confirmation Of Treatment Failure(repeat VL at 3M)', 'active'),
(3, 'clinical failure', 'active'),
(4, 'immunological failure', 'active'),
(5, 'single drug substitution', 'active'),
(6, 'Pregnant Mother', 'active'),
(7, 'Lactating Mother', 'active'),
(8, 'Baseline VL', 'active');

--saravanan 03-oct-2016
ALTER TABLE  `hold_sample_report` ADD  `vl_test_platform` VARCHAR( 255 ) NULL DEFAULT NULL AFTER  `import_batch_tracking` ;

INSERT INTO `vl_lab_request`.`global_config` (`display_name`, `name`, `value`) VALUES ('Sample Code', 'sample_code', 'numeric');

INSERT INTO `vl_lab_request`.`form_details` (`form_id`, `form_name`) VALUES (NULL, 'French Form');

-- Pal 05 Oct 2016
UPDATE `form_details` SET `form_name` = 'DRC Form' WHERE `form_details`.`form_id` = 3;

--saravanan 06-oct-2016
ALTER TABLE  `import_config` ADD  `lower_limt` INT NULL DEFAULT NULL AFTER  `file_name` ,
ADD  `higer_limit` INT NULL DEFAULT NULL AFTER  `lower_limt` ;
ALTER TABLE  `import_config` CHANGE  `lower_limt`  `lower_limit` INT( 11 ) NULL DEFAULT NULL ;
ALTER TABLE  `import_config` CHANGE  `higer_limit`  `higher_limit` INT( 11 ) NULL DEFAULT NULL ;

--saravanan 12-oct-2016
INSERT INTO `vl_lab_request`.`global_config` (`display_name`, `name`, `value`) VALUES ('Same user can Review and Approve', 'user_review_approve', 'yes');


-- Pal 18 Oct 2016
UPDATE `form_details` SET `form_name` = 'South Sudan Form' WHERE `form_details`.`form_id` = 1;

--saraanna 20-oct-2016
ALTER TABLE `roles` ADD `landing_page` VARCHAR(255) NULL DEFAULT NULL AFTER `status`;

--saravanan 24-oct-2016
ALTER TABLE `vl_request_form` ADD `result_coming_from` VARCHAR(255) NULL DEFAULT NULL AFTER `file_name`;
ALTER TABLE `facility_details` ADD `report_email` TEXT NULL DEFAULT NULL AFTER `email`;

-- Pal 24 Oct 2016
ALTER TABLE `vl_request_form` ADD `date_of_demand` DATE NULL DEFAULT NULL AFTER `duration_of_conservation`, ADD `viral_load_no` VARCHAR(45) NULL DEFAULT NULL AFTER `date_of_demand`, ADD `date_dispatched_from_clinic_to_lab` DATETIME NULL DEFAULT NULL AFTER `viral_load_no`;

-- Pal 25 Oct 2016
ALTER TABLE `vl_request_form` ADD `date_of_completion_of_viral_load` DATE NULL DEFAULT NULL AFTER `date_dispatched_from_clinic_to_lab`;

INSERT INTO `global_config` (`display_name`, `name`, `value`) VALUES ('Viral Load Threshold Limit', 'viral_load_threshold_limit', '1000');

--saravanan 25-oct-2016
INSERT INTO `global_config` (`display_name`, `name`, `value`) VALUES ('Number of In-House Controls', 'number_of_in_house_controls', '3'), ('Number of Manufacturer Controls', 'number_of_manufacturer_controls', '3');

--Pal 27-oct-2016
CREATE TABLE `log_result_updates` (
  `result_log_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `vl_sample_id` int(11) NOT NULL,
  `updated_on` datetime DEFAULT NULL
);

ALTER TABLE `log_result_updates`
  ADD PRIMARY KEY (`result_log_id`);
  
ALTER TABLE `log_result_updates`
  MODIFY `result_log_id` int(11) NOT NULL AUTO_INCREMENT;
  
INSERT INTO `privileges` (`privilege_id`, `resource_id`, `privilege_name`, `display_name`) VALUES (NULL, '5', 'editRequestEmailConfig.php', 'Edit Request Email Config');

INSERT INTO `privileges` (`privilege_id`, `resource_id`, `privilege_name`, `display_name`) VALUES (NULL, '5', 'editResultEmailConfig.php', 'Edit Result Email Config');

ALTER TABLE `other_config` CHANGE `value` `value` TEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;

INSERT INTO `other_config` (`name`, `value`) VALUES ('request_email_field', NULL), ('result_email_field', NULL);

ALTER TABLE other_config ADD display_name VARCHAR(255);

UPDATE `other_config` SET `display_name` = 'Email' WHERE `other_config`.`name` = 'email'; UPDATE `other_config` SET `display_name` = 'Password' WHERE `other_config`.`name` = 'password'; UPDATE `other_config` SET `display_name` = 'Result Email Fields' WHERE `other_config`.`name` = 'result_email_field'; UPDATE `other_config` SET `display_name` = 'Request Email Fields' WHERE `other_config`.`name` = 'request_email_field';

--Pal 28-oct-2016
INSERT INTO `global_config` (`display_name`, `name`, `value`) VALUES ('Sample Type', 'sample_type', 'enabled'), ('Testing Status', 'testing_status', 'enabled');

--Pal 31-oct-2016
INSERT INTO `vl_lab_request`.`privileges` (`privilege_id`, `resource_id`, `privilege_name`, `display_name`) VALUES (NULL, '6', 'vlMail.php', 'Request Email');
-- Amit 31 Oct 2016
ALTER TABLE `global_config` ADD PRIMARY KEY(`name`);

--Pal 1st-Nov-2016
ALTER TABLE `vl_request_form` CHANGE `facility_id` `facility_id` INT(11) NULL DEFAULT NULL, CHANGE `sample_id` `sample_id` INT(11) NULL DEFAULT NULL;

UPDATE `privileges` SET `privilege_name` = 'vlRequestMail.php', `display_name` = 'Email Test Request' WHERE `privileges`.`privilege_id` = 44;

INSERT INTO `privileges` (`privilege_id`, `resource_id`, `privilege_name`, `display_name`) VALUES (NULL, '6', 'vlResultMail.php', 'Email Test Result');

ALTER TABLE `vl_request_form` ADD `request_mail_sent` VARCHAR(45) NOT NULL DEFAULT 'no' AFTER `date_result_printed`, ADD `result_mail_sent` VARCHAR(45) NOT NULL DEFAULT 'no' AFTER `request_mail_sent`;

--Pal 4th-Nov-2016
ALTER TABLE `r_sample_type` CHANGE `form_identification` `status` VARCHAR(45) NULL DEFAULT NULL;

--Pal 7th-Nov-2016
ALTER TABLE `import_config` ADD `max_no_of_samples_in_a_batch` INT(11) NOT NULL AFTER `higher_limit`, ADD `number_of_in_house_controls` INT(11) NULL DEFAULT NULL AFTER `max_no_of_samples_in_a_batch`, ADD `number_of_manufacturer_controls` INT(11) NULL DEFAULT NULL AFTER `number_of_in_house_controls`, ADD `number_of_calibrators` INT(11) NULL DEFAULT NULL AFTER `number_of_manufacturer_controls`;

DELETE FROM `global_config` WHERE `global_config`.`name` = \'max_no_of_samples_in_a_batch\'

DELETE FROM `global_config` WHERE `global_config`.`name` = \'number_of_in_house_controls\'

DELETE FROM `global_config` WHERE `global_config`.`name` = \'number_of_manufacturer_controls\'

ALTER TABLE `batch_details` ADD `machine` INT(11) NOT NULL AFTER `batch_id`;

--Pal 8th-Nov-2016
ALTER TABLE `batch_details` ADD `label_order` TEXT NULL DEFAULT NULL AFTER `sent_mail`;

--Pal 9th-Nov-2016
INSERT INTO `privileges` (`privilege_id`, `resource_id`, `privilege_name`, `display_name`) VALUES (NULL, '7', 'addBatchControlsPosition.php', 'Add Controls Position');

INSERT INTO `privileges` (`privilege_id`, `resource_id`, `privilege_name`, `display_name`) VALUES (NULL, '7', 'editBatchControlsPosition.php', 'Edit Controls Position');

--saravanan 24-nov-2016
INSERT INTO `form_details` (`form_id`, `form_name`) VALUES (NULL, 'Mozambique Form');
ALTER TABLE `vl_request_form` ADD `consultation` VARCHAR(255) NULL DEFAULT NULL AFTER `result_coming_from`, ADD `first_line` VARCHAR(255) NULL DEFAULT NULL AFTER `consultation`, ADD `second_line` VARCHAR(255) NULL DEFAULT NULL AFTER `first_line`, ADD `first_viral_load` VARCHAR(255) NULL DEFAULT NULL AFTER `second_line`, ADD `collection_type` VARCHAR(255) NULL DEFAULT NULL AFTER `first_viral_load`, ADD `sample_processed` VARCHAR(255) NULL DEFAULT NULL AFTER `collection_type`;
ALTER TABLE `vl_request_form` ADD `patient_below_five_years` VARCHAR(255) NULL DEFAULT NULL AFTER `patient_dob`;

--Pal 5th-Dec-2016
UPDATE `privileges` SET `privilege_name` = 'testRequestEmailConfig.php', `display_name` = 'Access' WHERE `privileges`.`privilege_id` = 28;

UPDATE `resources` SET `resource_name` = 'test_request_email_config' WHERE `resources`.`resource_id` = 5;

UPDATE `resources` SET `display_name` = 'Manage Test Request Email Config' WHERE `resources`.`resource_id` = 5;

UPDATE `privileges` SET `privilege_name` = 'editTestRequestEmailConfig.php' WHERE `privileges`.`privilege_id` = 43;

UPDATE `privileges` SET `display_name` = 'Edit' WHERE `privileges`.`privilege_id` = 43;

ALTER TABLE `other_config` ADD `type` VARCHAR(45) NULL DEFAULT NULL FIRST;

INSERT INTO `resources` (`resource_id`, `resource_name`, `display_name`) VALUES (NULL, 'test_result_email_config', 'Manage Test Result Email Config');

INSERT INTO `privileges` (`privilege_id`, `resource_id`, `privilege_name`, `display_name`) VALUES (NULL, '17', 'testResultEmailConfig.php', 'Access');

INSERT INTO `privileges` (`privilege_id`, `resource_id`, `privilege_name`, `display_name`) VALUES (NULL, '17', 'editTestResultEmailConfig.php', 'Edit');


--Pal 7th-Dec-2016
ALTER TABLE `vl_request_form` CHANGE `service` `zone` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;

ALTER TABLE `vl_request_form` DROP `zone`;

ALTER TABLE `vl_request_form` ADD `is_patient_new` VARCHAR(45) NULL DEFAULT NULL AFTER `sample_id`;

ALTER TABLE `vl_request_form` ADD `trimestre` INT(11) NULL DEFAULT NULL AFTER `is_patient_breastfeeding`;

--saravanan 21-dec-2016
INSERT INTO `form_details` (`form_id`, `form_name`) VALUES (NULL, 'Zambia Form');
ALTER TABLE `vl_request_form` ADD `is_adherance_poor` VARCHAR(255) NULL DEFAULT NULL AFTER `arv_adherance_percentage`;

--Pal 11-Jan-2017
INSERT INTO `privileges` (`privilege_id`, `resource_id`, `privilege_name`, `display_name`) VALUES (NULL, '6', 'vlRequestMailConfirm.php', 'Email Test Request Confirm');

INSERT INTO `privileges` (`privilege_id`, `resource_id`, `privilege_name`, `display_name`) VALUES (NULL, '6', 'vlResultMailConfirm.php', 'Email Test Result Confirm');

--Pal 13-Jan-2017
INSERT INTO `privileges` (`privilege_id`, `resource_id`, `privilege_name`, `display_name`) VALUES (NULL, '8', 'addImportTestRequest.php', 'Import Test Request');

INSERT INTO `privileges` (`privilege_id`, `resource_id`, `privilege_name`, `display_name`) VALUES (NULL, '6', 'addImportTestRequestResult.php', 'Import Test Request Result');

--Pal 16-Jan-2017
UPDATE `privileges` SET `privilege_name` = 'addImportTestResult.php' WHERE `privileges`.`privilege_id` = 53;

UPDATE `privileges` SET `display_name` = 'Import Test Result' WHERE `privileges`.`privilege_id` = 53;


--saravanan 24-jan-2017
INSERT INTO `form_details` (`form_id`, `form_name`) VALUES (NULL, 'Papua New Guinea');

--saravanan 02-feb-2017
ALTER TABLE `vl_request_form` ADD `ward` VARCHAR(255) NULL DEFAULT NULL AFTER `result_coming_from`, ADD `art_cd_cells` VARCHAR(255) NULL DEFAULT NULL AFTER `ward`, ADD `art_cd_date` DATE NULL DEFAULT NULL AFTER `art_cd_cells`, ADD `who_clinical_stage` VARCHAR(255) NULL DEFAULT NULL AFTER `art_cd_date`, ADD `reason_testing_png` TEXT NULL DEFAULT NULL AFTER `who_clinical_stage`, ADD `tech_name_png` VARCHAR(255) NULL DEFAULT NULL AFTER `reason_testing_png`, ADD `qc_tech_name` VARCHAR(255) NULL DEFAULT NULL AFTER `tech_name_png`, ADD `qc_tech_sign` VARCHAR(255) NULL DEFAULT NULL AFTER `qc_tech_name`, ADD `qc_date` VARCHAR(255) NULL DEFAULT NULL AFTER `qc_tech_sign`;
--saravanan 08-feb-2017
ALTER TABLE `vl_request_form`  ADD `whole_blood_ml` VARCHAR(50) NULL DEFAULT NULL  AFTER `qc_date`,  ADD `whole_blodd_vial` VARCHAR(50) NULL DEFAULT NULL  AFTER `whole_blood_ml`,  ADD `plasma_ml` VARCHAR(50) NULL DEFAULT NULL  AFTER `whole_blodd_vial`,  ADD `plasma_vial` VARCHAR(50) NULL DEFAULT NULL  AFTER `plasma_ml`,  ADD `plasma_process_time` VARCHAR(255) NULL DEFAULT NULL  AFTER `plasma_vial`,  ADD `plasma_process_tech` VARCHAR(255) NULL DEFAULT NULL  AFTER `plasma_process_time`,  ADD `batch_quality` VARCHAR(255) NULL DEFAULT NULL  AFTER `plasma_process_tech`,  ADD `sample_test_quality` VARCHAR(255) NULL DEFAULT NULL  AFTER `batch_quality`,  ADD `failed_test_date` DATETIME NULL DEFAULT NULL  AFTER `sample_test_quality`,  ADD `failed_test_tech` VARCHAR(255) NULL DEFAULT NULL  AFTER `failed_test_date`,  ADD `failed_vl_result` VARCHAR(255) NULL DEFAULT NULL  AFTER `failed_test_tech`,  ADD `failed_batch_quality` VARCHAR(255) NULL DEFAULT NULL  AFTER `failed_vl_result`,  ADD `failed_sample_test_quality` VARCHAR(255) NULL DEFAULT NULL  AFTER `failed_batch_quality`,  ADD `failed_batch_id` VARCHAR(255) NULL DEFAULT NULL  AFTER `failed_sample_test_quality`;

ALTER TABLE `vl_request_form` CHANGE `whole_blodd_vial` `whole_blood_vial` VARCHAR(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;

ALTER TABLE `vl_request_form` ADD `clinic_date` DATE NULL DEFAULT NULL AFTER `failed_batch_id`, ADD `report_date` DATE NULL DEFAULT NULL AFTER `clinic_date`;
ALTER TABLE `vl_request_form` ADD `sample_to_transport` VARCHAR(255) NULL DEFAULT NULL AFTER `report_date`;

--ilahir 20-Feb-2017
INSERT INTO `vl_lab_request`.`privileges` (`privilege_id`, `resource_id`, `privilege_name`, `display_name`) VALUES (NULL, '8', 'addImportXmlTestRequest.php', 'Import Xml Test Request');

--Pal 21-Feb-2017
INSERT INTO `resources` (`resource_id`, `resource_name`, `display_name`) VALUES (NULL, 'monthly_report', 'Monthly Report');

INSERT INTO `privileges` (`privilege_id`, `resource_id`, `privilege_name`, `display_name`) VALUES (NULL, '18', 'monthlyReport.php', 'Access');

UPDATE `resources` SET `resource_name` = 'vl_statistics' WHERE `resources`.`resource_id` = 18;

UPDATE `resources` SET `display_name` = 'Viral Load Statistics' WHERE `resources`.`resource_id` = 18;

UPDATE `privileges` SET `privilege_name` = 'vlStatistics.php' WHERE `privileges`.`privilege_id` = 54;

--saravanan 23-feb-2017
UPDATE `facility_type` SET `facility_type_name` = 'Viral Load Lab' WHERE `facility_type`.`facility_type_id` = 2;

INSERT INTO `facility_type` (`facility_type_id`, `facility_type_name`) VALUES (NULL, 'lab');

INSERT INTO `global_config` (`display_name`, `name`, `value`) VALUES ('Minimum Length', 'min_length', '5'), ('Maximum Lenght', 'max_length', '10');

--Pal 23-Feb-2017
UPDATE `global_config` SET `display_name` = 'Maximum Length' WHERE `global_config`.`name` = 'max_length';

--Pal 23-Feb-2017
UPDATE `resources` SET `resource_name` = 'vl_weekly_report' WHERE `resources`.`resource_id` = 18;

UPDATE `resources` SET `display_name` = 'Viral Load Weekly Report' WHERE `resources`.`resource_id` = 18;

UPDATE `privileges` SET `privilege_name` = 'vlWeeklyReport.php' WHERE `privileges`.`privilege_id` = 54;

--Pal 06-Mar-2017
INSERT INTO `global_config` (`display_name`, `name`, `value`) VALUES ('Download Path', 'download_path', NULL);

UPDATE `global_config` SET `display_name` = 'Sync Path' WHERE `global_config`.`name` = 'download_path';

UPDATE `global_config` SET `name` = 'sync_path' WHERE `global_config`.`name` = 'download_path';

--Pal 08-Mar-2017
ALTER TABLE `vl_request_form` ADD `test_request_export` INT(11) NOT NULL DEFAULT '0' AFTER `result_mail_sent`;

ALTER TABLE `vl_request_form` ADD `test_request_import` INT(11) NOT NULL DEFAULT '0' AFTER `test_request_export`;

ALTER TABLE `vl_request_form` ADD `test_result_export` INT(11) NOT NULL DEFAULT '0' AFTER `test_request_import`, ADD `test_result_import` INT(11) NOT NULL DEFAULT '0' AFTER `test_result_export`;

--Pal 20-Mar-2017
INSERT INTO `global_config` (`display_name`, `name`, `value`) VALUES ('Manager Email', 'manager_email', NULL);

--Pal 29-Mar-2017
ALTER TABLE `facility_details` CHANGE `email` `facility_emails` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;

ALTER TABLE `facility_details` CHANGE `phone_number` `facility_mobile_numbers` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;

ALTER TABLE `vl_request_form` CHANGE `art_no` `patient_art_no` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;

ALTER TABLE `vl_request_form` CHANGE `arc_no` `patient_anc_no` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;

ALTER TABLE `vl_request_form` ADD `patient_nationality` INT(11) NULL DEFAULT NULL AFTER `surname`;

ALTER TABLE `vl_request_form` CHANGE `other_id` `patient_other_id` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;

ALTER TABLE `vl_request_form` CHANGE `patient_name` `patient_first_name` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;

ALTER TABLE `vl_request_form` CHANGE `surname` `patient_last_name` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;

ALTER TABLE `vl_request_form` CHANGE `gender` `patient_gender` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;

ALTER TABLE `vl_request_form` CHANGE `age_in_yrs` `patient_age_in_years` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;

ALTER TABLE `vl_request_form` CHANGE `age_in_mnts` `patient_age_in_months` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;

ALTER TABLE `vl_request_form` ADD `physician_name` VARCHAR(255) NULL DEFAULT NULL AFTER `duration_of_conservation`;

ALTER TABLE `vl_request_form` CHANGE `patient_receive_sms` `consent_to_receive_sms` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;

ALTER TABLE `vl_request_form` CHANGE `consent_to_receive_sms` `consent_to_receive_sms` VARCHAR(45) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;

ALTER TABLE `vl_request_form` CHANGE `patient_phone_number` `patient_mobile_number` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;

ALTER TABLE `vl_request_form` CHANGE `location` `patient_location` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;

ALTER TABLE `vl_request_form` CHANGE `focal_person_phone_number` `vl_focal_person_phone_number` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;

ALTER TABLE `vl_request_form` DROP `email_for_HF`;

ALTER TABLE `vl_request_form` ADD `patient_address` TEXT NULL DEFAULT NULL AFTER `patient_location`;

ALTER TABLE `vl_request_form` CHANGE `trimestre` `pregnancy_trimester` INT(11) NULL DEFAULT NULL;

ALTER TABLE `vl_request_form` CHANGE `routine_monitoring_last_vl_date` `last_vl_date_routine` DATE NULL DEFAULT NULL;

ALTER TABLE `vl_request_form` CHANGE `routine_monitoring_value` `last_vl_result_routine` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;

ALTER TABLE `vl_request_form` CHANGE `routine_monitoring_sample_type` `last_vl_sample_type_routine` INT(11) NULL DEFAULT NULL;

ALTER TABLE `vl_request_form` CHANGE `vl_treatment_failure_adherence_counseling_last_vl_date` `last_vl_date_failure_ac` DATE NULL DEFAULT NULL;

ALTER TABLE `vl_request_form` CHANGE `vl_treatment_failure_adherence_counseling_value` `last_vl_result_failure_ac` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;

ALTER TABLE `vl_request_form` CHANGE `vl_treatment_failure_adherence_counseling_sample_type` `last_vl_sample_type_failure_ac` INT(11) NULL DEFAULT NULL;

ALTER TABLE `vl_request_form` CHANGE `suspected_treatment_failure_last_vl_date` `last_vl_date_failure` DATE NULL DEFAULT NULL, CHANGE `suspected_treatment_failure_value` `last_vl_result_failure` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL, CHANGE `suspected_treatment_failure_sample_type` `last_vl_sample_type_failure` INT(11) NULL DEFAULT NULL;

ALTER TABLE `vl_request_form` CHANGE `date_of_regimen_changed` `regimen_change_date` DATE NULL DEFAULT NULL;

ALTER TABLE `vl_request_form` CHANGE `arv_adherence` `arv_adherance_percentage` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;

ALTER TABLE `vl_request_form` CHANGE `poor_adherence` `is_adherance_poor` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;

ALTER TABLE `vl_request_form` CHANGE `viral_load_log` `last_vl_result_in_log` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;

ALTER TABLE `vl_request_form` CHANGE `viral_load_no` `vl_test_number` VARCHAR(45) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;

ALTER TABLE `vl_request_form` CHANGE `enhance_session` `number_of_enhanced_sessions` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;

ALTER TABLE `vl_request_form` CHANGE `rejection` `is_sample_rejected` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;

--Pal 30-Mar-2017
ALTER TABLE `vl_request_form` ADD `facility_sample_id` VARCHAR(255) NULL DEFAULT NULL AFTER `facility_id`;

ALTER TABLE `vl_request_form` CHANGE `sample_rejection_reason` `reason_for_sample_rejection` INT(11) NULL DEFAULT NULL;

ALTER TABLE `vl_request_form` CHANGE `duration_of_conservation` `plasma_conservation_duration` VARCHAR(45) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;

ALTER TABLE `vl_request_form` CHANGE `status` `result_status` INT(11) NOT NULL;

ALTER TABLE `temp_sample_report` CHANGE `status` `result_status` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;
ALTER TABLE `vl_request_form` CHANGE `urgency` `test_urgency` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;
ALTER TABLE `vl_request_form` CHANGE `sample_id` `sample_type` INT(11) NULL DEFAULT NULL;
ALTER TABLE `vl_request_form` DROP `viral_load_indication`;
ALTER TABLE `vl_request_form` CHANGE `request_clinician` `request_clinician_name` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;
ALTER TABLE `vl_request_form` CHANGE `clinician_ph_no` `request_clinician_phone_number` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;
ALTER TABLE `vl_request_form` DROP `justification`;
ALTER TABLE `vl_request_form` CHANGE `vl_test_reason` `reason_for_vl_testing` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;
ALTER TABLE `vl_request_form` CHANGE `collected_by` `sample_collected_by` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;
ALTER TABLE `vl_request_form` CHANGE `support_partner` `facility_support_partner` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;
ALTER TABLE `vl_request_form` CHANGE `date_of_demand` `date_test_ordered_by_physician` DATE NULL DEFAULT NULL;

ALTER TABLE `vl_request_form`
  DROP `switch_to_tdf_last_vl_date`,
  DROP `switch_to_tdf_value`,
  DROP `switch_to_tdf_sample_type`,
  DROP `missing_last_vl_date`,
  DROP `missing_value`,
  DROP `missing_sample_type`;

--saravanan 29-mar-2017
INSERT INTO `global_config` (`display_name`, `name`, `value`) VALUES ('Instance Type', 'instance_type', 'Both');

ALTER TABLE `vl_request_form` CHANGE `file_name` `import_machine_file_name` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;
ALTER TABLE `vl_request_form` CHANGE `modified_on` `last_modified_datetime` DATETIME NULL DEFAULT NULL;
ALTER TABLE `vl_request_form` CHANGE `modified_by` `last_modified_by` INT(11) NULL DEFAULT NULL;
ALTER TABLE `vl_request_form` CHANGE `created_on` `request_created_datetime` DATETIME NULL DEFAULT NULL;
ALTER TABLE `batch_details` CHANGE `created_on` `request_created_datetime` DATETIME NOT NULL;
ALTER TABLE `vl_request_form` CHANGE `created_by` `request_created_by` INT(11) NOT NULL;
ALTER TABLE `vl_request_form` CHANGE `result_coming_from` ` manual_result_entry` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;
ALTER TABLE `vl_request_form` CHANGE ` manual_result_entry` `manual_result_entry` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;
ALTER TABLE `temp_sample_report` CHANGE `file_name` `import_machine_file_name` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;
ALTER TABLE `import_config` CHANGE `file_name` `import_machine_file_name` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;
ALTER TABLE `hold_sample_report` CHANGE `file_name` `import_machine_file_name` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;
ALTER TABLE `vl_request_form` CHANGE `result_mail_sent` `is_result_mail_sent` VARCHAR(45) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'no';
ALTER TABLE `vl_request_form` CHANGE `request_mail_sent` `is_request_mail_sent` VARCHAR(45) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'no';
ALTER TABLE `vl_request_form` CHANGE `batch_id` `sample_batch_id` VARCHAR(11) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;
ALTER TABLE `vl_request_form` CHANGE `form_id` `vlsm_country_id` INT(11) NOT NULL DEFAULT '1';
ALTER TABLE `form_details` CHANGE `form_id` `vlsm_country_id` INT(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `vl_instance` CHANGE `vl_instance_id` `vlsm_instance_id` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;
ALTER TABLE `vl_request_form` CHANGE `vl_instance_id` `vlsm_instance_id` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;
ALTER TABLE `hold_sample_report` ADD `manual_result_entry` VARCHAR(255) NULL DEFAULT NULL AFTER `import_machine_file_name`;
ALTER TABLE `vl_request_form` CHANGE `date_result_printed` `result_printed_datetime` DATETIME NULL DEFAULT NULL;
ALTER TABLE `vl_request_form` CHANGE `result_approved_on` `result_approved_datetime` DATETIME NULL DEFAULT NULL;
ALTER TABLE `vl_request_form` CHANGE `result_reviewed_date` `result_reviewed_datetime` DATETIME NULL DEFAULT NULL;
ALTER TABLE `temp_sample_report` CHANGE `result_reviewed_date` `result_reviewed_datetime` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;
ALTER TABLE `hold_sample_report` CHANGE `result_reviewed_date` `result_reviewed_datetime` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;
ALTER TABLE `vl_request_form` CHANGE `text_value` `result_value_text` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;
ALTER TABLE `temp_sample_report` CHANGE `text_value` `result_value_text` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;
ALTER TABLE `hold_sample_report` CHANGE `text_value` `result_value_text` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;
ALTER TABLE `vl_request_form` CHANGE `absolute_value` `result_value_absolute` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;
ALTER TABLE `temp_sample_report` CHANGE `absolute_value` `result_value_absolute` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;
ALTER TABLE `hold_sample_report` CHANGE `absolute_value` `result_value_absolute` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;
ALTER TABLE `vl_request_form` CHANGE `log_value` `result_value_log` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;
ALTER TABLE `temp_sample_report` CHANGE `log_value` `result_value_log` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;
ALTER TABLE `hold_sample_report` CHANGE `log_value` `result_value_log` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;

--saravanan 30-mar-2017
ALTER TABLE `vl_request_form` CHANGE `comments` `approver_comments` TEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;
ALTER TABLE `temp_sample_report` CHANGE `comments` `approver_comments` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;
ALTER TABLE `hold_sample_report` CHANGE `comments` `approver_comments` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;
ALTER TABLE `vl_request_form` CHANGE `date_results_dispatched` `result_dispatched_datetime` DATETIME NULL DEFAULT NULL;
ALTER TABLE `temp_sample_report` CHANGE `date_results_dispatched` `result_dispatched_datetime` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;
ALTER TABLE `hold_sample_report` CHANGE `date_results_dispatched` `result_dispatched_datetime` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;
ALTER TABLE `vl_request_form` DROP `date_of_completion_of_viral_load`;
ALTER TABLE `vl_request_form` CHANGE `absolute_decimal_value` `result_value_absolute_decimal` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;
ALTER TABLE `temp_sample_report` CHANGE `absolute_decimal_value` `result_value_absolute_decimal` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;
ALTER TABLE `hold_sample_report` CHANGE `absolute_decimal_value` `result_value_absolute_decimal` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;
ALTER TABLE `vl_request_form` ADD `is_result_sms_sent` VARCHAR(45) NOT NULL DEFAULT 'no' AFTER `is_result_mail_sent`;
ALTER TABLE `vl_request_form` ADD `result_sms_sent_datetime` DATETIME NULL DEFAULT NULL AFTER `result_printed_datetime`;
ALTER TABLE `vl_request_form` CHANGE `lab_tested_date` `sample_tested_datetime` DATETIME NULL DEFAULT NULL;
ALTER TABLE `temp_sample_report` CHANGE `lab_tested_date` `sample_tested_datetime` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;
ALTER TABLE `hold_sample_report` CHANGE `lab_tested_date` `sample_tested_datetime` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;
ALTER TABLE `vl_request_form` CHANGE `date_sample_received_at_testing_lab` `sample_received_at_vl_lab_datetime` DATETIME NULL DEFAULT NULL;
ALTER TABLE `temp_sample_report` CHANGE `date_sample_received_at_testing_lab` `sample_received_at_vl_lab_datetime` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;
ALTER TABLE `hold_sample_report` CHANGE `date_sample_received_at_testing_lab` `sample_received_at_vl_lab_datetime` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;
ALTER TABLE `vl_request_form` CHANGE `lab_phone_no` `lab_phone_number` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;
ALTER TABLE `hold_sample_report` CHANGE `lab_phone_no` `lab_phone_number` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;
ALTER TABLE `temp_sample_report` CHANGE `lab_phone_no` `lab_phone_number` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;
ALTER TABLE `vl_request_form` CHANGE `lab_no` `lab_code` INT(11) NULL DEFAULT NULL;
ALTER TABLE `facility_details` CHANGE `vl_instance_id` `vlsm_instance_id` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;
ALTER TABLE `facility_details` CHANGE `state` `facility_state` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL, CHANGE `district` `facility_district` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL, CHANGE `hub_name` `facility_hub_name` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;

RENAME `testing_status` TO `r_sample_status`;

--palz 01-apr-2017
ALTER TABLE `vl_request_form` ADD `source` VARCHAR(500) NULL DEFAULT 'manual' AFTER `manual_result_entry`;

--palz 03-apr-2017
INSERT INTO `r_sample_status` (`status_id`, `status_name`) VALUES (NULL, 'Sent to Lab');

--saravanan 11-apr-2017
INSERT INTO `global_config` (`display_name`, `name`, `value`) VALUES ('Barcode Printing', 'bar_code_printing', 'off');
--saravanan 12-apr-2017
INSERT INTO `form_details` (`vlsm_country_id`, `form_name`) VALUES (6, 'WHO FORM');
ALTER TABLE `vl_request_form` ADD `patient_receiving_therapy` VARCHAR(255) NULL DEFAULT NULL AFTER `patient_art_date`, ADD `patient_drugs_transmission` VARCHAR(255) NULL DEFAULT NULL AFTER `patient_receiving_therapy`, ADD `patient_tb` VARCHAR(255) NULL DEFAULT NULL AFTER `patient_drugs_transmission`, ADD `patient_tb_yes` VARCHAR(255) NULL DEFAULT NULL AFTER `patient_tb`;
ALTER TABLE `vl_request_form` ADD `test_requested_on` DATE NULL DEFAULT NULL AFTER `request_clinician_name`;

--Pal 24-apr-2017
ALTER TABLE `vl_request_form` ADD `patient_middle_name` VARCHAR(255) NULL DEFAULT NULL AFTER `patient_first_name`;

--Pal 25-apr-2017
ALTER TABLE `vl_request_form` ADD `line_of_treatment` INT(11) NULL DEFAULT NULL AFTER `treatment_initiation`;

--Pal 26-apr-2017
ALTER TABLE `temp_sample_report` ADD `lot_number` VARCHAR(255) NULL DEFAULT NULL AFTER `approver_comments`, ADD `lot_expiration_date` DATE NULL DEFAULT NULL AFTER `lot_number`;

ALTER TABLE `vl_request_form` ADD `lot_number` VARCHAR(255) NULL DEFAULT NULL AFTER `approver_comments`, ADD `lot_expiration_date` DATE NULL DEFAULT NULL AFTER `lot_number`;

ALTER TABLE `hold_sample_report` ADD `lot_number` VARCHAR(255) NULL DEFAULT NULL AFTER `approver_comments`, ADD `lot_expiration_date` DATE NULL DEFAULT NULL AFTER `lot_number`;

INSERT INTO `global_config` (`display_name`, `name`, `value`) VALUES ('Result PDF High Viral Load Message', 'h_vl_msg', NULL), ('Result PDF Low Viral Load Message', 'l_vl_msg', NULL);

UPDATE `global_config` SET `value` = 'High Viral Load - need assessment for enhanced adherence or clinical assessment for possible switch to second line.' WHERE `global_config`.`name` = 'h_vl_msg';

UPDATE `global_config` SET `value` = 'Viral load adequately controlled : continue current regimen' WHERE `global_config`.`name` = 'l_vl_msg';

--Pal 27-apr-2017
ALTER TABLE `vl_request_form` ADD `reason_for_vl_result_changes` TEXT NULL DEFAULT NULL AFTER `approver_comments`;

--Pal 04-may-2017
INSERT INTO `form_details` (`vlsm_country_id`, `form_name`) VALUES (NULL, 'Rwanda FORM');

--Pal 06-may-2017
INSERT INTO `r_art_code_details` (`art_id`, `art_code`, `parent_art`, `nation_identifier`) VALUES (NULL, '1a = AZT+3TC+EFV', '7', 'rwd'), (NULL, '1b = AZT+3TC+NVP', '7', 'rwd');

INSERT INTO `r_art_code_details` (`art_id`, `art_code`, `parent_art`, `nation_identifier`) VALUES (NULL, '1c = d4T+3TC+EFV', '7', 'rwd'), (NULL, '1d = d4T+3TC+NVP', '7', 'rwd');

INSERT INTO `r_art_code_details` (`art_id`, `art_code`, `parent_art`, `nation_identifier`) VALUES (NULL, '1f = TDF+3TC+EFV', '7', 'rwd'), (NULL, '1g = TDF+3TC+NVP', '7', 'rwd');

INSERT INTO `r_art_code_details` (`art_id`, `art_code`, `parent_art`, `nation_identifier`) VALUES (NULL, '1h = TDF +FTC+ EFV', '7', 'rwd'), (NULL, '1j = TDF+FTC+NVP', '7', 'rwd');

INSERT INTO `r_art_code_details` (`art_id`, `art_code`, `parent_art`, `nation_identifier`) VALUES (NULL, '1k=ABC+3TC+EFV', '7', 'rwd'), (NULL, '1m=ABC+3TC+NVP', '7', 'rwd');

INSERT INTO `r_art_code_details` (`art_id`, `art_code`, `parent_art`, `nation_identifier`) VALUES (NULL, '2a = ABC+ddI+LPV/r', '7', 'rwd'), (NULL, '2b = ABC+ddI+NFV', '7', 'rwd');

INSERT INTO `r_art_code_details` (`art_id`, `art_code`, `parent_art`, `nation_identifier`) VALUES (NULL, '2c = TDF+ddI+LPV/r', '7', 'rwd'), (NULL, '2d = TDF+ddI+NFV', '7', 'rwd');

INSERT INTO `r_art_code_details` (`art_id`, `art_code`, `parent_art`, `nation_identifier`) VALUES (NULL, '2e = TDF+3TC+LPV/r', '7', 'rwd'), (NULL, '2f = TDF+3TC+ATZ/r', '7', 'rwd');

INSERT INTO `r_art_code_details` (`art_id`, `art_code`, `parent_art`, `nation_identifier`) VALUES (NULL, '2g = AZT+3TC+LPV/r', '7', 'rwd'), (NULL, '2h = AZT+3TC+ATZ', '7', 'rwd');

INSERT INTO `r_art_code_details` (`art_id`, `art_code`, `parent_art`, `nation_identifier`) VALUES (NULL, '4a = AZT+3TC+NVP', '7', 'rwd'), (NULL, '4b = AZT+3TC+NFV', '7', 'rwd');

INSERT INTO `r_art_code_details` (`art_id`, `art_code`, `parent_art`, `nation_identifier`) VALUES (NULL, '4c = d4T+3TC+NVP', '7', 'rwd'), (NULL, '4d = d4T+3TC+EFV', '7', 'rwd');

INSERT INTO `r_art_code_details` (`art_id`, `art_code`, `parent_art`, `nation_identifier`) VALUES (NULL, '4f = ABC+3TC+NVP', '7', 'rwd'), (NULL, '4g = ABC+3TC+EFV', '7', 'rwd');

INSERT INTO `r_art_code_details` (`art_id`, `art_code`, `parent_art`, `nation_identifier`) VALUES (NULL, '4h = TDF+3TC+EFV', '7', 'rwd');

INSERT INTO `r_art_code_details` (`art_id`, `art_code`, `parent_art`, `nation_identifier`) VALUES (NULL, '5a = ABC+ddI+LPV/r', '7', 'rwd'), (NULL, '5b = AZT+3TC+LPV/r', '7', 'rwd');

INSERT INTO `r_art_code_details` (`art_id`, `art_code`, `parent_art`, `nation_identifier`) VALUES (NULL, '5c = ABC+3TC+LPV/r', '7', 'rwd'), (NULL, '5d = TDF+3TC+LPV/r', '7', 'rwd');

--Pal 07-may-2017
ALTER TABLE `vl_request_form` ADD `sample_reordered` VARCHAR(45) NOT NULL DEFAULT 'no' AFTER `sample_batch_id`;

INSERT INTO `global_config` (`display_name`, `name`, `value`) VALUES ('Result PDF Mandatory Fields', 'r_mandatory_fields', NULL);

ALTER TABLE `r_sample_rejection_reasons` ADD `rejection_type` VARCHAR(255) NOT NULL DEFAULT 'general' AFTER `rejection_reason_name`;

INSERT INTO `r_sample_rejection_reasons` (`rejection_reason_id`, `rejection_reason_name`, `rejection_type`, `rejection_reason_status`) VALUES (NULL, 'Poorly labelled specimen', 'general', 'active'), (NULL, 'Mismatched sample and form labeling', 'general', 'active');

INSERT INTO `r_sample_rejection_reasons` (`rejection_reason_id`, `rejection_reason_name`, `rejection_type`, `rejection_reason_status`) VALUES (NULL, 'Missing labels on container or tracking form', 'general', 'active'), (NULL, 'Sample without request forms/Tracking forms', 'general', 'active');

INSERT INTO `r_sample_rejection_reasons` (`rejection_reason_id`, `rejection_reason_name`, `rejection_type`, `rejection_reason_status`) VALUES (NULL, 'Name/Information of requester is missing', 'general', 'active'), (NULL, 'Missing information on request form - Age', 'general', 'active');

INSERT INTO `r_sample_rejection_reasons` (`rejection_reason_id`, `rejection_reason_name`, `rejection_type`, `rejection_reason_status`) VALUES (NULL, 'Missing information on request form - Sex', 'general', 'active'), (NULL, 'Missing information on request form - Sample Collection Date', 'general', 'active');

INSERT INTO `r_sample_rejection_reasons` (`rejection_reason_id`, `rejection_reason_name`, `rejection_type`, `rejection_reason_status`) VALUES (NULL, 'Missing information on request form - ART No', 'general', 'active'), (NULL, 'Inappropriate specimen packing', 'general', 'active');

INSERT INTO `r_sample_rejection_reasons` (`rejection_reason_id`, `rejection_reason_name`, `rejection_type`, `rejection_reason_status`) VALUES (NULL, 'Inappropriate specimen for test request', 'general', 'active'), (NULL, 'Wrong container/anticoagulant used', 'whole blood', 'active');

INSERT INTO `r_sample_rejection_reasons` (`rejection_reason_id`, `rejection_reason_name`, `rejection_type`, `rejection_reason_status`) VALUES (NULL, 'EDTA tube specimens that arrived hemolyzed', 'whole blood', 'active'), (NULL, 'ETDA tube that arrives more than 24 hours after specimen collection', 'whole blood', 'active');

INSERT INTO `r_sample_rejection_reasons` (`rejection_reason_id`, `rejection_reason_name`, `rejection_type`, `rejection_reason_status`) VALUES (NULL, 'Plasma that arrives at a temperature above 8 C', 'plasma', 'active'), (NULL, 'Plasma tube contain less than 1.5 mL', 'plasma', 'active');

INSERT INTO `r_sample_rejection_reasons` (`rejection_reason_id`, `rejection_reason_name`, `rejection_type`, `rejection_reason_status`) VALUES (NULL, 'DBS cards with insufficient blood spots', 'dbs', 'active'), (NULL, 'DBS card with clotting present in spots', 'dbs', 'active');

INSERT INTO `r_sample_rejection_reasons` (`rejection_reason_id`, `rejection_reason_name`, `rejection_type`, `rejection_reason_status`) VALUES (NULL, 'DBS cards that have serum rings indicating contamination around spots', 'dbs', 'active'), (NULL, 'VL Mechine Flag', 'testing', 'active');

--saravanan 08-may-2017
ALTER TABLE  `temp_sample_report` ADD  `temp_sample_status` INT NOT NULL DEFAULT  '0';
ALTER TABLE  `temp_sample_report` ADD  `sample_review_by` VARCHAR( 10 ) NULL DEFAULT NULL ;

--Pal 08-may-2017
DELETE FROM `vl_lab_request`.`r_sample_rejection_reasons` WHERE `r_sample_rejection_reasons`.`rejection_reason_id` = 16
ALTER TABLE `r_sample_status` ADD `status` VARCHAR(45) NOT NULL DEFAULT 'active' AFTER `status_name`;

ALTER TABLE `r_art_code_details` ADD `art_status` VARCHAR(45) NOT NULL DEFAULT 'active' AFTER `nation_identifier`;

--saravanan 09-may-2017
INSERT INTO `vl_lab_request`.`global_config` (`display_name`, `name`, `value`) VALUES ('Sample Code Prefix', 'sample_code_prefix', NULL);

--saravanna 10-may-2017
INSERT INTO `vl_lab_request`.`resources` (`resource_id`, `resource_name`, `display_name`) VALUES (NULL, 'sample_rejection_report', 'Sample Rejection Report');
INSERT INTO `vl_lab_request`.`privileges` (`privilege_id`, `resource_id`, `privilege_name`, `display_name`) VALUES (NULL, '19', 'sampleRejectionReport.php', 'Access');
ALTER TABLE  `r_sample_rejection_reasons` ADD  `rejection_reason_code` VARCHAR( 255 ) NULL DEFAULT NULL ;

--saravanan 12-may-2017
CREATE TABLE IF NOT EXISTS `r_sample_controls` (
  `r_sample_control_id` int(11) NOT NULL AUTO_INCREMENT,
  `r_sample_control_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`r_sample_control_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
--saravanan 23-may-2017
CREATE TABLE IF NOT EXISTS `import_config_machines` (
  `config_machine_id` int(11) NOT NULL AUTO_INCREMENT,
  `config_id` int(11) NOT NULL,
  `config_machine_name` varchar(255) NOT NULL,
  PRIMARY KEY (`config_machine_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

ALTER TABLE  `temp_sample_report` ADD  `import_machine_name` INT NULL DEFAULT NULL AFTER  `vl_test_platform` ;
ALTER TABLE  `vl_request_form` ADD  `import_machine_name` INT NULL DEFAULT NULL AFTER  `vl_test_platform` ;
ALTER TABLE  `hold_sample_report` ADD  `import_machine_name` INT NULL DEFAULT NULL AFTER  `vl_test_platform` ;

--saravanan 25-may-2017
INSERT INTO `vl_lab_request`.`resources` (`resource_id`, `resource_name`, `display_name`) VALUES (NULL, 'vl_monitoring_report', 'Sample Monitoring Report');
INSERT INTO `vl_lab_request`.`privileges` (`privilege_id`, `resource_id`, `privilege_name`, `display_name`) VALUES (NULL, '20', 'vlMonitoringReport.php', 'Sample Monitoring Report');

--Pal 26-may-2017
INSERT INTO `privileges` (`privilege_id`, `resource_id`, `privilege_name`, `display_name`) VALUES (NULL, '6', 'import.php', 'Import Vl Request');

--Pal 02-Jun-2017
DELETE FROM `privileges` WHERE `privileges`.`privilege_id` = 58

INSERT INTO `resources` (`resource_id`, `resource_name`, `display_name`) VALUES (NULL, 'qr-code', 'QR Code');

INSERT INTO `privileges` (`privilege_id`, `resource_id`, `privilege_name`, `display_name`) VALUES (NULL, '20', 'generate.php', 'Generate QR Code');

INSERT INTO `privileges` (`privilege_id`, `resource_id`, `privilege_name`, `display_name`) VALUES (NULL, '20', 'readQRCode.php', 'Read QR Code');

INSERT INTO `global_config` (`display_name`, `name`, `value`) VALUES ('Enable QR Code Mechanism', 'enable_qr_mechanism', 'yes');

-- Pal 14-Jun-2017 

INSERT INTO `privileges` (`privilege_id`, `resource_id`, `privilege_name`, `display_name`) VALUES (NULL, '20', 'vlRequestRwdForm.php', 'Manage QR Code Rwd Form');

ALTER TABLE  `r_art_code_details` ADD  `headings` VARCHAR( 255 ) NULL DEFAULT NULL AFTER  `parent_art` ;

--Pal 23-Jun-2017
ALTER TABLE `vl_request_form` ADD `sample_visit_type` VARCHAR(45) NULL DEFAULT NULL AFTER `treatment_details`;

ALTER TABLE `vl_request_form` ADD `vl_sample_suspected_treatment_failure_at` VARCHAR(255) NULL DEFAULT NULL AFTER `sample_visit_type`;

ALTER TABLE `vl_request_form` ADD `facility_comments` TEXT NULL DEFAULT NULL AFTER `sample_collected_by`;

ALTER TABLE `vl_request_form` ADD `repeat_sample_collection` VARCHAR(45) NULL DEFAULT NULL AFTER `sample_test_quality`;

ALTER TABLE  `vl_request_form` ADD  `sample_code_title` VARCHAR( 45 ) NOT NULL DEFAULT  'auto' AFTER  `sample_code_format`;



--saravanan 11-July-2017
ALTER TABLE `vl_request_form` ADD `request_mail_datetime` DATETIME NULL DEFAULT NULL AFTER `is_request_mail_sent`;
ALTER TABLE `vl_request_form` ADD `result_mail_datetime` DATETIME NULL DEFAULT NULL AFTER `is_result_mail_sent`;

--Pal 07-Aug-2017
ALTER TABLE `vl_request_form` ADD `request_exported_datetime` DATETIME NULL DEFAULT NULL AFTER `test_result_import`, ADD `request_imported_datetime` DATETIME NULL DEFAULT NULL AFTER `request_exported_datetime`, ADD `result_exported_datetime` DATETIME NULL DEFAULT NULL AFTER `request_imported_datetime`, ADD `result_imported_datetime` DATETIME NULL DEFAULT NULL AFTER `result_exported_datetime`;

ALTER TABLE `temp_sample_report` ADD `request_exported_datetime` DATETIME NULL DEFAULT NULL AFTER `import_machine_name`, ADD `request_imported_datetime` DATETIME NULL DEFAULT NULL AFTER `request_exported_datetime`, ADD `result_exported_datetime` DATETIME NULL DEFAULT NULL AFTER `request_imported_datetime`, ADD `result_imported_datetime` DATETIME NULL DEFAULT NULL AFTER `result_exported_datetime`;


--saravanna 30-aug-2017
INSERT INTO `global_config` (`display_name`, `name`, `value`) VALUES ('Patient Name in Result PDF', 'patient_name_pdf', 'flname');

--Pal 05-Sep-2017
INSERT INTO `global_config` (`display_name`, `name`, `value`) VALUES ('Import Non matching Sample Results from Machine generated file', 'import_non_matching_sample', 'no');

--saravanan 06-Sep-2017
ALTER TABLE `vl_instance` ADD `instance_facility_name` VARCHAR(255) NULL DEFAULT NULL AFTER `vlsm_instance_id`, ADD `instance_facility_code` VARCHAR(255) NULL DEFAULT NULL AFTER `instance_facility_name`, ADD `instance_facility_type` INT NULL DEFAULT NULL AFTER `instance_facility_code`, ADD `instance_facility_logo` VARCHAR(255) NULL DEFAULT NULL AFTER `instance_facility_type`;
ALTER TABLE `vl_instance` ADD `instance_added_on` DATETIME NULL DEFAULT NULL AFTER `instance_facility_logo`, ADD `instance_update_on` DATETIME NULL DEFAULT NULL AFTER `instance_added_on`;

ALTER TABLE `vl_instance` CHANGE `instance_facility_type` `instance_facility_type` VARCHAR(255) NULL DEFAULT NULL;

--saravanna 07-sep-2017
ALTER TABLE `vl_instance` ADD `instance_mac_address` VARCHAR(255) NULL DEFAULT NULL AFTER `instance_update_on`;


-- Amit 08-Sep-2017
ALTER TABLE vl_instance RENAME s_vlsm_instance;
ALTER TABLE temp_sample_report RENAME temp_sample_import;
ALTER TABLE hold_sample_report RENAME hold_sample_import;


--saravanan 04-Oct-2017
INSERT INTO `resources` (`resource_id`, `resource_name`, `display_name`) VALUES (NULL, 'control_report', 'Manage Control Reports');
INSERT INTO `privileges` (`privilege_id`, `resource_id`, `privilege_name`, `display_name`) VALUES (NULL, '22', 'controlReport.php', 'Control Report');
UPDATE `privileges` SET `privilege_name` = 'vlControlReport.php' WHERE `privileges`.`privilege_id` = 63;

CREATE TABLE `vl_imported_controls` (
  `control_id` int(11) NOT NULL,
  `control_code` varchar(255) NOT NULL,
  `lab_id` int(11) DEFAULT NULL,
  `batch_id` int(11) DEFAULT NULL,
  `control_type` varchar(255) DEFAULT NULL,
  `lot_number` varchar(255) DEFAULT NULL,
  `lot_expiration_date` date DEFAULT NULL,
  `sample_tested_datetime` datetime DEFAULT NULL,
  `is_sample_rejected` varchar(255) DEFAULT NULL,
  `reason_for_sample_rejection` varchar(255) DEFAULT NULL,
  `result_value_absolute` varchar(255) DEFAULT NULL,
  `result_value_log` varchar(255) DEFAULT NULL,
  `result_value_text` varchar(255) DEFAULT NULL,
  `result_value_absolute_decimal` varchar(255) DEFAULT NULL,
  `result` varchar(255) DEFAULT NULL,
  `approver_comments` varchar(255) DEFAULT NULL,
  `result_approved_by` varchar(255) DEFAULT NULL,
  `result_approved_datetime` datetime DEFAULT NULL,
  `result_reviewed_by` varchar(15) DEFAULT NULL,
  `result_reviewed_datetime` datetime DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `vlsm_country_id` varchar(10) DEFAULT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `imported_date_time` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `vl_imported_controls`
  ADD PRIMARY KEY (`control_id`);
  ALTER TABLE `vl_imported_controls`
  MODIFY `control_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
  
  --saravanan 20-oct-2017
  INSERT INTO `form_details` (`vlsm_country_id`, `form_name`) VALUES (NULL, 'Angola Form');
  
  --saravanan 23-oct-2017
  ALTER TABLE `vl_request_form` ADD `professional_number` VARCHAR(255) NULL DEFAULT NULL AFTER `sample_to_transport`, ADD `category` VARCHAR(255) NULL DEFAULT NULL AFTER `professional_number`;
  ALTER TABLE `vl_request_form` ADD `patient_province` VARCHAR(255) NULL DEFAULT NULL AFTER `patient_nationality`, ADD `patient_district` VARCHAR(255) NULL DEFAULT NULL AFTER `patient_province`;
  ALTER TABLE `vl_request_form` ADD `patient_responsible_person` VARCHAR(255) NULL DEFAULT NULL AFTER `patient_last_name`;
  ALTER TABLE `vl_request_form` ADD `line_of_treatment_ref_type` VARCHAR(255) NULL DEFAULT NULL AFTER `line_of_treatment`;
  ALTER TABLE `vl_request_form` ADD `patient_group` VARCHAR(255) NULL DEFAULT NULL AFTER `patient_district`;
  
  ALTER TABLE `vl_request_form` ADD `last_vl_date_ecd` DATE NULL DEFAULT NULL AFTER `last_vl_sample_type_failure`, ADD `last_vl_result_ecd` VARCHAR(255) NULL DEFAULT NULL AFTER `last_vl_date_ecd`, ADD `last_vl_date_cf` DATE NULL DEFAULT NULL AFTER `last_vl_result_ecd`, ADD `last_vl_result_cf` VARCHAR(255) NULL DEFAULT NULL AFTER `last_vl_date_cf`, ADD `last_vl_date_if` DATE NULL DEFAULT NULL AFTER `last_vl_result_cf`, ADD `last_vl_result_if` VARCHAR(255) NULL DEFAULT NULL AFTER `last_vl_date_if`;
  ALTER TABLE `vl_request_form` ADD `vl_service_sector` VARCHAR(255) NULL DEFAULT NULL AFTER `category`;
  
  --saravanan 26-oct-2017
  INSERT INTO `privileges` (`privilege_id`, `resource_id`, `privilege_name`, `display_name`) VALUES (NULL, '2', 'addVlFacilityMap.php', 'Add Facility Map');
  INSERT INTO `privileges` (`privilege_id`, `resource_id`, `privilege_name`, `display_name`) VALUES (NULL, '2', 'facilityMap.php', 'Access Facility Map');
  INSERT INTO `privileges` (`privilege_id`, `resource_id`, `privilege_name`, `display_name`) VALUES (NULL, '2', 'editVlFacilityMap.php', 'Edit Facility Map');
  
  CREATE TABLE `vl_facility_map` (
  `facility_map_id` int(11) NOT NULL,
  `vl_lab_id` int(11) NOT NULL,
  `facility_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
ALTER TABLE `vl_facility_map`
  ADD PRIMARY KEY (`facility_map_id`);

ALTER TABLE vl_facility_map
ADD FOREIGN KEY (vl_lab_id) REFERENCES facility_details(facility_id);
ALTER TABLE vl_facility_map
ADD FOREIGN KEY (facility_id) REFERENCES facility_details(facility_id);
--saravanan 30-oct-2017
INSERT INTO `r_sample_status` (`status_id`, `status_name`, `status`) VALUES (NULL, 'Sample Registered at Health Center', 'active');

CREATE TABLE `vl_user_facility_map` (
  `user_facility_map_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `facility_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
ALTER TABLE `vl_user_facility_map`
  ADD PRIMARY KEY (`user_facility_map_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `facility_id` (`facility_id`);
  
  ALTER TABLE `vl_user_facility_map`
  MODIFY `user_facility_map_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
  
ALTER TABLE `vl_user_facility_map`
  ADD CONSTRAINT `vl_user_facility_map_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user_details` (`user_id`),
  ADD CONSTRAINT `vl_user_facility_map_ibfk_2` FOREIGN KEY (`facility_id`) REFERENCES `facility_details` (`facility_id`);
  
-- saravanan 06-nov-2017
ALTER TABLE `vl_request_form` ADD `remote_sample_code` VARCHAR(255) NULL DEFAULT NULL AFTER `vlsm_country_id`;
ALTER TABLE `vl_request_form` ADD `remote_sample_code_key` VARCHAR(255) NULL DEFAULT NULL AFTER `sample_reordered`;
-- saravanan 09-nov-2017
INSERT INTO `global_config` (`display_name`, `name`, `value`) VALUES ('Data Sync Interval', 'data_sync_interval', '3');

-- saravanna 13-nov-2017
ALTER TABLE `vl_request_form` ADD `data_sync` VARCHAR(10) NOT NULL DEFAULT '0' AFTER `vl_service_sector`;

-- saravanna 15-nov-2017
ALTER TABLE `user_details` ADD `user_alpnum_id` VARCHAR(255) NULL AFTER `user_id`;
UPDATE vl_request_form
SET last_vl_date_routine = NULL
WHERE last_vl_date_routine = 0000-00-00;
UPDATE vl_request_form SET last_vl_date_failure_ac = NULL WHERE last_vl_date_failure_ac = 0000-00-00;
UPDATE vl_request_form SET last_vl_date_failure = NULL WHERE last_vl_date_failure = 0000-00-00;
UPDATE vl_request_form SET sample_testing_date = NULL WHERE sample_testing_date = 0000-00-00;
UPDATE vl_request_form SET treatment_initiated_date = NULL WHERE treatment_initiated_date = 0000-00-00;
UPDATE vl_request_form SET last_viral_load_date = NULL WHERE last_viral_load_date = 0000-00-00;
UPDATE vl_request_form SET regimen_change_date = NULL WHERE regimen_change_date = 0000-00-00;
UPDATE vl_request_form SET date_test_ordered_by_physician = NULL WHERE date_test_ordered_by_physician = 0000-00-00;
UPDATE vl_request_form SET result_sms_sent_datetime = NULL WHERE result_sms_sent_datetime=0000-00-00;
UPDATE vl_request_form SET art_cd_date = NULL WHERE art_cd_date=0000-00-00;
UPDATE vl_request_form SET failed_test_date = NULL WHERE failed_test_date=0000-00-00;
UPDATE vl_request_form SET clinic_date = NULL WHERE clinic_date=0000-00-00;
UPDATE vl_request_form SET report_date = NULL WHERE report_date=0000-00-00;
UPDATE vl_request_form SET request_created_datetime = NULL WHERE request_created_datetime=0000-00-00;
UPDATE vl_request_form SET last_modified_datetime = NULL WHERE last_modified_datetime=0000-00-00;
UPDATE vl_request_form SET lot_expiration_date = NULL WHERE lot_expiration_date=0000-00-00;
UPDATE vl_request_form SET result_approved_datetime = NULL WHERE result_approved_datetime=0000-00-00;
UPDATE vl_request_form SET result_reviewed_datetime = NULL WHERE result_reviewed_datetime=0000-00-00;
UPDATE vl_request_form SET test_requested_on = NULL WHERE test_requested_on=0000-00-00;
UPDATE vl_request_form SET sample_received_at_vl_lab_datetime = NULL WHERE sample_received_at_vl_lab_datetime=0000-00-00;
UPDATE vl_request_form SET result_dispatched_datetime = NULL WHERE result_dispatched_datetime=0000-00-00;
UPDATE vl_request_form SET sample_tested_datetime = NULL WHERE sample_tested_datetime=0000-00-00;
UPDATE vl_request_form SET result_printed_datetime = NULL WHERE result_printed_datetime=0000-00-00;
UPDATE vl_request_form SET patient_dob = NULL WHERE patient_dob=0000-00-00;
UPDATE vl_request_form SET date_dispatched_from_clinic_to_lab = NULL WHERE date_dispatched_from_clinic_to_lab=0000-00-00;
ALTER TABLE `vl_request_form` CHANGE `last_modified_by` `last_modified_by` VARCHAR(255) NULL DEFAULT NULL;
alter table vl_user_facility_map drop foreign key vl_user_facility_map_ibfk_1;
ALTER TABLE `vl_request_form` CHANGE `request_created_by` `request_created_by` VARCHAR(255) NOT NULL;
ALTER TABLE `vl_user_facility_map` CHANGE `user_id` `user_id` VARCHAR(255) NOT NULL;
ALTER TABLE `user_details` CHANGE `user_id` `user_id` VARCHAR(255) NOT NULL;

-- saravanna 17-nov-2017
ALTER TABLE `vl_request_form` CHANGE `professional_number` `requesting_professional_number` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL, CHANGE `category` `requesting_category` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL, CHANGE `vl_service_sector` `requesting_vl_service_sector` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;

ALTER TABLE `vl_request_form` ADD `requesting_facility_id` INT NULL DEFAULT NULL AFTER `requesting_vl_service_sector`, ADD `requesting_person` VARCHAR(255) NULL DEFAULT NULL AFTER `requesting_facility_id`, ADD `requesting_phone` VARCHAR(255) NULL DEFAULT NULL AFTER `requesting_person`, ADD `requesting_date` DATE NULL DEFAULT NULL AFTER `requesting_phone`;
-- saravanan 20-nov-2017
ALTER TABLE `vl_request_form` ADD `collection_site` VARCHAR(255) NULL DEFAULT NULL AFTER `requesting_date`;
-- saravanan 21-nov-2017
ALTER TABLE `vl_request_form` ADD `remote_sample` VARCHAR(255) NOT NULL DEFAULT 'no' AFTER `data_sync`;

-- saravanan 23-nov-2017
ALTER TABLE `r_sample_type` ADD `data_sync` INT NOT NULL DEFAULT '0' AFTER `status`;
ALTER TABLE `r_art_code_details` ADD `data_sync` INT NOT NULL DEFAULT '0' AFTER `art_status`;
ALTER TABLE `r_sample_rejection_reasons` ADD `data_sync` INT NOT NULL DEFAULT '0' AFTER `rejection_reason_code`;
ALTER TABLE `province_details` ADD `data_sync` INT NOT NULL DEFAULT '0' AFTER `province_code`;
ALTER TABLE `facility_details` ADD `data_sync` INT NOT NULL DEFAULT '0' AFTER `status`;

-- saravanan 25-nov-2017
ALTER TABLE `r_art_code_details` ADD `updated_datetime` DATETIME NULL DEFAULT NULL AFTER `art_status`;
ALTER TABLE `r_sample_rejection_reasons` ADD `updated_datetime` DATETIME NULL DEFAULT NULL AFTER `rejection_reason_code`;
ALTER TABLE `facility_details` ADD `updated_datetime` DATETIME NULL DEFAULT NULL AFTER `status`;
ALTER TABLE `province_details` ADD `updated_datetime` DATETIME NULL DEFAULT NULL AFTER `province_code`;

-- saravanana 28-nov-2017
CREATE TABLE `system_config` (
  `display_name` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `value` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `system_config`
--

INSERT INTO `system_config` (`display_name`, `name`, `value`) VALUES
('Lab Name', 'lab_name', ''),
('User Type', 'user_type', 'standalone');

ALTER TABLE `system_config`
  ADD PRIMARY KEY (`name`);
  CREATE TABLE `user_admin_details` (
  `user_admin_id` int(11) NOT NULL,
  `user_admin_name` varchar(255) DEFAULT NULL,
  `user_admin_login` varchar(255) DEFAULT NULL,
  `user_admin_password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `user_admin_details`
--

INSERT INTO `user_admin_details` (`user_admin_id`, `user_admin_name`, `user_admin_login`, `user_admin_password`) VALUES
(1, 'S Admin', 'sadmin', 'sadmin@123');

ALTER TABLE `user_admin_details`
  ADD UNIQUE KEY `user_admin_id` (`user_admin_id`);

--
ALTER TABLE `user_admin_details`
  MODIFY `user_admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
  -- saravanan 30-nov-2017
  ALTER TABLE vl_user_facility_map
DROP FOREIGN KEY vl_user_facility_map_ibfk_2;

-- saravanan 01-dec-2017
INSERT INTO `resources` (`resource_id`, `resource_name`, `display_name`) VALUES (NULL, 'package-details', 'Manage Package Details');
INSERT INTO `privileges` (`privilege_id`, `resource_id`, `privilege_name`, `display_name`) VALUES (NULL, '23', 'addPackage.php', 'Add'), (NULL, '23', 'editPackage.php', 'Edit');
INSERT INTO `privileges` (`privilege_id`, `resource_id`, `privilege_name`, `display_name`) VALUES (NULL, '23', 'packageList.php', 'Access');

-- saravanan 01-dec-2017
CREATE TABLE `package_details` (
  `package_id` int(11) NOT NULL,
  `package_code` varchar(255) NOT NULL,
  `added_by` varchar(255) NOT NULL,
  `request_created_datetime` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
ALTER TABLE `package_details`
  ADD PRIMARY KEY (`package_id`);
ALTER TABLE `package_details`
  MODIFY `package_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

  CREATE TABLE `r_package_details_map` (
  `package_map_id` int(11) NOT NULL,
  `package_id` int(11) NOT NULL,
  `sample_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `r_package_details_map`
  ADD PRIMARY KEY (`package_map_id`);

ALTER TABLE `r_package_details_map`
  MODIFY `package_map_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

-- saravanan 11-dec-2017
ALTER TABLE `package_details` ADD `package_status` VARCHAR(255) NULL DEFAULT NULL AFTER `added_by`;

ALTER TABLE `vl_request_form` ADD `sample_package_id` VARCHAR(11) NULL DEFAULT NULL AFTER `sample_batch_id`;

DROP TABLE r_package_details_map;

ALTER TABLE `vl_request_form` DROP `professional_number`, DROP `category`;

-- saravanan 22-jan-2017
INSERT INTO `privileges` (`privilege_id`, `resource_id`, `privilege_name`, `display_name`) VALUES (NULL, '12', 'vlResultAllFieldExportInExcel.php', 'Export Data');

-- ilahir 19-Feb-2018
INSERT INTO `global_config` (`display_name`, `name`, `value`) VALUES ('Vldashboard Url', 'vldashboard_url', NULL);

-- Pal 09-Mar-2017
ALTER TABLE `vl_request_form` ADD `funding_source` VARCHAR(500) NULL DEFAULT NULL AFTER `test_urgency`, ADD `implementing_partner` VARCHAR(500) NULL DEFAULT NULL AFTER `funding_source`;

ALTER TABLE `vl_request_form` CHANGE `funding_source` `funding_source` INT(11) NULL DEFAULT NULL, CHANGE `implementing_partner` `implementing_partner` INT(11) NULL DEFAULT NULL;

CREATE TABLE `r_funding_sources` (
  `funding_source_id` int(11) NOT NULL,
  `funding_source_name` varchar(500) NOT NULL,
  `funding_source_status` varchar(45) NOT NULL DEFAULT 'active'
);

INSERT INTO `r_funding_sources` (`funding_source_id`, `funding_source_name`, `funding_source_status`) VALUES
(1, 'MOH', 'active');

ALTER TABLE `r_funding_sources`
  ADD PRIMARY KEY (`funding_source_id`);
  
  ALTER TABLE `r_funding_sources`
  MODIFY `funding_source_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
  
CREATE TABLE `r_implementation_partners` (
  `i_partner_id` int(11) NOT NULL,
  `i_partner_name` varchar(500) NOT NULL,
  `i_partner_status` varchar(45) NOT NULL DEFAULT 'active'
);

INSERT INTO `r_implementation_partners` (`i_partner_id`, `i_partner_name`, `i_partner_status`) VALUES
(1, 'MOH', 'active');

ALTER TABLE `r_implementation_partners`
  ADD PRIMARY KEY (`i_partner_id`);
  
  ALTER TABLE `r_implementation_partners`
  MODIFY `i_partner_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

alter table vl_request_form add FOREIGN key(implementing_partner) REFERENCES r_implementing_partners(i_partner_id);

alter table vl_request_form add FOREIGN key(funding_source) REFERENCES r_funding_sources(funding_source_id);

-- Pal 12-Mar-2017
ALTER TABLE `vl_request_form` ADD `lab_technician` VARCHAR(500) NULL DEFAULT NULL AFTER `lab_code`;

-- Amit 03 Mar 2018

ALTER TABLE `facility_details` CHANGE `facility_name` `facility_name` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;

-- Amit 09 March 2018
ALTER TABLE `vl_facility_map` CHANGE `facility_map_id` `facility_map_id` INT(11) NOT NULL AUTO_INCREMENT;


-- Amit 17 March 2018

ALTER TABLE `s_vlsm_instance` ADD `last_vldash_sync` DATETIME NULL AFTER `instance_mac_address`;



-- Pal 20-Mar-2017
UPDATE `resources` SET `display_name` = 'Manage Specimen Referral Manifests' WHERE `resources`.`resource_id` = 23;
UPDATE `resources` SET `resource_name` = 'specimen-referral-manifest' WHERE `resources`.`resource_id` = 23;

UPDATE `privileges` SET `privilege_name` = 'specimenReferralManifestList.php' WHERE `privileges`.`privilege_id` = 69;

UPDATE `privileges` SET `privilege_name` = 'addSpecimenReferralManifest.php' WHERE `privileges`.`privilege_id` = 67;

UPDATE `privileges` SET `privilege_name` = 'editSpecimenReferralManifest.php' WHERE `privileges`.`privilege_id` = 68;


-- Version 3.0 ---------- Pal 21-Mar-2017
-- Version 3.2 ---------- Amit 29-Mar-2017

ALTER TABLE `vl_request_form` ADD `consultation` VARCHAR(255) NULL DEFAULT NULL AFTER `manual_result_entry`, ADD `first_line` VARCHAR(255) NULL DEFAULT NULL AFTER `consultation`, ADD `second_line` VARCHAR(255) NULL DEFAULT NULL AFTER `first_line`, ADD `first_viral_load` VARCHAR(255) NULL DEFAULT NULL AFTER `second_line`, ADD `collection_type` VARCHAR(255) NULL DEFAULT NULL AFTER `first_viral_load`, ADD `sample_processed` VARCHAR(255) NULL DEFAULT NULL AFTER `collection_type`;
ALTER TABLE `vl_request_form` ADD `vl_result_category` VARCHAR(255) NULL DEFAULT NULL AFTER `sample_processed`;
ALTER TABLE `vl_request_form` ADD `sample_received_at_hub_datetime` DATETIME NULL DEFAULT NULL AFTER `vl_focal_person_phone_number`;

UPDATE `privileges` SET `privilege_name` = 'vlWeeklyReport.php' WHERE `privilege_name` = 'monthlyReport.php'; 

-- Version 3.3 ---------- Amit 06-May-2018

-- Version 3.4 ---------- Amit 23-May-2018

-- Version 3.5 ---------- Amit 08-Jun-2018



UPDATE `global_config` SET `value` = '5' WHERE `global_config`.`name` = 'data_sync_interval'; 
-- saravanan 12-jun-2018
INSERT INTO `global_config` (`display_name`, `name`, `value`) VALUES ('Edit Profile', 'edit_profile', 'no');

-- Version 3.7 ---------- Amit 24-Jul-2018

-- saravanan 26-july-2018

ALTER TABLE `vl_request_form` ADD `cphl_vl_result` VARCHAR(255) NULL DEFAULT NULL AFTER `vl_test_platform`; -- for png form

ALTER TABLE `temp_sample_import` CHANGE `sample_review_by` `sample_review_by` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;


-- saravanana 02-Aug-2018

  ALTER TABLE `vl_request_form` CHANGE `remote_sample_code_key` `remote_sample_code_key` INT NULL DEFAULT NULL, CHANGE `sample_code_key` `sample_code_key` INT NULL DEFAULT NULL;

-- saravanan 16-aug-2018
ALTER TABLE `vl_request_form` ADD `province_id` VARCHAR(255) NULL DEFAULT NULL AFTER `facility_id`;
-- Version 3.8 ---------- Amit 28-Aug-2018

-- saravanan 03-sep-2018
ALTER TABLE `vl_request_form` ADD `reason_for_vl_testing_other` VARCHAR(255) NULL DEFAULT NULL AFTER `reason_for_vl_testing`;


-- Amit 05 Sep 2018

UPDATE vl_request_form INNER JOIN r_vl_test_reasons
    ON vl_request_form.reason_for_vl_testing = r_vl_test_reasons.test_reason_name
SET vl_request_form.reason_for_vl_testing = r_vl_test_reasons.test_reason_id;


-- Version 3.9 ---------- Amit 14-Sep-2018

-- Amit 25 Sep 2018
ALTER TABLE `activity_log` CHANGE `action` `action` TEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;

-- Version 3.9.5 ------- saravanan 09-oct-2018

-- saravanan 25-oct-2018
ALTER TABLE `facility_details` ADD `facility_logo` VARCHAR(255) NULL DEFAULT NULL AFTER `facility_type`;

-- saravanan 30-oct-2018
INSERT INTO `resources` (`resource_id`, `resource_name`, `display_name`) VALUES (NULL, 'move-samples', 'Move Samples');
INSERT INTO `privileges` (`privilege_id`, `resource_id`, `privilege_name`, `display_name`) VALUES (NULL, '24', 'sampleList.php', 'Access'), (NULL, '24', 'addSampleList.php', 'Add Samples List');
INSERT INTO `privileges` (`privilege_id`, `resource_id`, `privilege_name`, `display_name`) VALUES (NULL, '24', 'editSampleList.php', 'Edit Sample List');

-- Version 3.9.6 ------- saravanan 01-nov-2018
ALTER TABLE `facility_details` ADD `header_text` VARCHAR(255) NULL DEFAULT NULL AFTER `facility_logo`;


-- Version 3.9.7 ---------- Amit 16-Nov-2018

-- Version 3.9.8 ---------- Amit 11-Dec-2018

-- Version 3.9.9 ---------- Amit 11-Jan-2018

-- saravanan 21-jan-2019
ALTER TABLE `activity_log` ADD `ip_address` VARCHAR(255) NULL DEFAULT NULL AFTER `date_time`;
UPDATE vl_request_form SET result_status = 7 WHERE result_status=6 AND (result is NOT null AND result != '');


-- Version 3.10 ---------- Amit 11-Feb-2018

-- Version 3.10.1 ---------- Saravanan 14-Feb-2018

-- Version 3.10.2 ---------- Saravanan 16-Feb-2018

-- Version 3.10.3 ---------- Amit 18-Feb-2018

UPDATE `r_sample_status` SET `status_name` = 'Sample Registered at VL Lab' WHERE `status_id` = 6;
UPDATE `r_sample_status` SET `status_name` = 'Awaiting Approval' WHERE `r_sample_status`.`status_id` = 8;

-- Version 3.10.4 ---------- Amit 24-Feb-2018


ALTER TABLE `vl_request_form` ADD INDEX(`sample_collection_date`);
ALTER TABLE `vl_request_form` ADD INDEX(`sample_tested_datetime`);
ALTER TABLE `vl_request_form` ADD INDEX(`lab_id`);
ALTER TABLE `vl_request_form` ADD INDEX(`result_status`);


-- Version 3.10.5 ---------- Amit 28-Feb-2018
-- Version 3.10.6 ---------- Amit 04-Mar-2019
ALTER TABLE `vl_request_form` ADD `sample_registered_at_lab` DATETIME NULL AFTER `lab_phone_number`;
UPDATE `vl_request_form` set sample_registered_at_lab = request_created_datetime where sample_registered_at_lab is NULL;
UPDATE `vl_request_form` set sample_received_at_vl_lab_datetime = request_created_datetime where sample_received_at_vl_lab_datetime is NULL;


CREATE TABLE `move_samples` (
  `move_sample_id` int(11) NOT NULL,
  `moved_from_lab_id` int(11) NOT NULL,
  `moved_to_lab_id` int(11) NOT NULL,
  `moved_on` date DEFAULT NULL,
  `moved_by` varchar(255) DEFAULT NULL,
  `reason_for_moving` text,
  `move_approved_by` varchar(255) DEFAULT NULL,
  `list_request_created_datetime` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `move_samples_map` (
  `sample_map_id` int(11) NOT NULL,
  `move_sample_id` int(11) NOT NULL,
  `vl_sample_id` int(11) NOT NULL,
  `move_sync_status` varchar(255) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


ALTER TABLE `move_samples`
  ADD PRIMARY KEY (`move_sample_id`);

ALTER TABLE `move_samples_map`
  ADD PRIMARY KEY (`sample_map_id`);

ALTER TABLE `move_samples`
  MODIFY `move_sample_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

ALTER TABLE `move_samples_map`
  MODIFY `sample_map_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;



-- Version 3.10.7 ---------- Amit 23-Mar-2019



INSERT INTO `global_config` (`display_name`, `name`, `value`) VALUES ('Low Viral Load (text results)', 'low_vl_text_results', 'Target Not Detected, TND, < 20, < 40');
ALTER TABLE `import_config` ADD `low_vl_result_text` TEXT NULL DEFAULT NULL AFTER `number_of_calibrators`;



ALTER TABLE `resources` ADD `module` VARCHAR(255) NOT NULL AFTER `resource_id`;
UPDATE `resources` set `module` = 'vl';


-- Version 3.10.8 ---------- Amit 17-Apr-2019


CREATE TABLE `result_import_stats` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `imported_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `no_of_results_imported` int(11) DEFAULT NULL,
 `imported_by` varchar(1000) DEFAULT NULL,
 `import_mode` varchar(500) DEFAULT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `global_config` (`display_name`, `name`, `value`) VALUES ('Barcode Format', 'barcode_format', 'C39');
		
CREATE TABLE `eid_form` (
  `eid_id` INTEGER NOT NULL AUTO_INCREMENT,
  `vlsm_instance_id` VARCHAR(255) NULL DEFAULT NULL,
  `vlsm_country_id` INTEGER NOT NULL,
  `sample_code_key` INTEGER NOT NULL,
  `sample_code_format` VARCHAR(255) NULL DEFAULT NULL,
  `sample_code` VARCHAR(255) NULL DEFAULT NULL,
  `remote_sample` VARCHAR(255) NOT NULL DEFAULT 'no',
  `remote_sample_code_key` INTEGER NULL DEFAULT NULL,
  `remote_sample_code_format` VARCHAR(255) NULL DEFAULT NULL,
  `remote_sample_code` VARCHAR(255) NULL DEFAULT NULL,
  `sample_collection_date` DATETIME NOT NULL,
  `sample_received_at_hub_datetime` DATETIME NOT NULL,
  `sample_received_at_vl_lab_datetime` DATETIME NOT NULL,
  `sample_tested_datetime` DATETIME NULL DEFAULT NULL,
  `funding_source` INTEGER NULL DEFAULT NULL,
  `implementation_partner` INTEGER NULL DEFAULT NULL,
  `is_sample_rejected` VARCHAR(255) NOT NULL DEFAULT 'no',
  `sample_rejection_reason` INTEGER NOT NULL,
  `facility_id` INTEGER NOT NULL,
  `mother_id` VARCHAR(255) NULL DEFAULT NULL,
  `mother_name` VARCHAR(500) NULL DEFAULT NULL,
  `caretaker_phone_number` VARCHAR(255) NULL DEFAULT NULL,
  `caretaker_address` VARCHAR(1000) NULL DEFAULT NULL,
  `mother_dob` DATE NULL DEFAULT NULL,
  `mother_age_in_years` VARCHAR(255) NULL DEFAULT NULL,
  `mother_marital_status` VARCHAR(255) NULL DEFAULT NULL,
  `child_id` VARCHAR(255) NULL DEFAULT NULL,
  `child_name` VARCHAR(255) NULL DEFAULT NULL,
  `child_dob` DATE NULL DEFAULT NULL,
  `child_age` VARCHAR(255) NULL DEFAULT NULL,
  `child_gender` VARCHAR(255) NULL DEFAULT NULL,
  `mother_hiv_status` VARCHAR(255) NULL DEFAULT NULL,
  `mother_treatment` VARCHAR(255) NULL DEFAULT NULL,
  `mother_cd4` VARCHAR(255) NULL DEFAULT NULL,
  `mother_cd4_test_date` DATE NULL DEFAULT NULL,
  `mother_vl_result` VARCHAR(255) NULL DEFAULT NULL,
  `mother_vl_test_date` VARCHAR(255) NULL DEFAULT NULL,
  `child_treatment` VARCHAR(255) NULL DEFAULT NULL,
  `is_infant_receiving_treatment` VARCHAR(255) NULL DEFAULT NULL,
  `has_infant_stopped_breastfeeding` VARCHAR(255) NULL DEFAULT NULL,
  `age_breastfeeding_stopped_in_months` VARCHAR(255) NULL DEFAULT NULL,
  `choice_of_feeding` VARCHAR(255) NULL DEFAULT NULL,
  `is_cotrimoxazole_being_administered_to_the_infant` VARCHAR(255) NULL DEFAULT NULL,
  `sample_requestor_name` VARCHAR(255) NULL DEFAULT NULL,
  `sample_requestor_phone` VARCHAR(255) NULL DEFAULT NULL,
  `specimen_quality` VARCHAR(255) NULL DEFAULT NULL,
  `specimen_type` VARCHAR(255) NULL DEFAULT NULL,
  `last_pcr_id` VARCHAR(255) NULL DEFAULT NULL,
  `last_pcr_date` DATE NULL DEFAULT NULL,
  `reason_for_pcr` INTEGER NULL DEFAULT NULL,
  `rapid_test_performed` VARCHAR(255) NULL DEFAULT NULL,
  `rapid_test_date` DATE NULL DEFAULT NULL,
  `rapid_test_result` VARCHAR(255) NULL DEFAULT NULL,
  `lab_id` INTEGER NULL DEFAULT NULL,
  `lab_technician` VARCHAR(255) NULL DEFAULT NULL,
  `result_status` INTEGER NULL DEFAULT NULL,
  `result` VARCHAR(255) NOT NULL,
  `sample_printed_datetime` DATETIME NULL DEFAULT NULL,
  `created_on` DATETIME NULL DEFAULT NULL,
  `created_by` VARCHAR(255) NOT NULL,
  `last_modified_datetime` DATETIME NOT NULL,
  `last_modified_by` VARCHAR(255) NOT NULL,
  `data_sync` INTEGER NOT NULL DEFAULT 0,
  PRIMARY KEY (`eid_id`)
);

-- ---
-- Foreign Keys 
-- ---


-- ---
-- Table Properties
-- ---

-- ALTER TABLE `eid_form` ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


ALTER TABLE `vl_imported_controls` CHANGE `result_reviewed_by` `result_reviewed_by` VARCHAR(1000) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;

-- Version 3.10.9 ---------- Amit 6-May-2019

ALTER TABLE `user_details` ADD `user_signature` TEXT NULL DEFAULT NULL AFTER `role_id`;

INSERT INTO `resources` (`resource_id`, `module`, `resource_name`, `display_name`) VALUES (25, 'eid', 'eid-requests', 'EID Request Management');
INSERT INTO `privileges` (`privilege_id`, `resource_id`, `privilege_name`, `display_name`) VALUES (NULL, '25', 'eid-add-request.php', 'Add Request');

ALTER TABLE `eid_form` CHANGE `created_on` `request_created_on` DATETIME NULL DEFAULT NULL;
ALTER TABLE `eid_form` CHANGE `created_by` `request_created_by` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;



ALTER TABLE `eid_form` CHANGE `sample_received_at_hub_datetime` `sample_received_at_hub_datetime` DATETIME NULL DEFAULT NULL;
ALTER TABLE `eid_form` CHANGE `sample_received_at_vl_lab_datetime` `sample_received_at_vl_lab_datetime` DATETIME NULL DEFAULT NULL;
ALTER TABLE `eid_form` CHANGE `sample_rejection_reason` `sample_rejection_reason` INT(11) NULL DEFAULT NULL;
ALTER TABLE `eid_form` CHANGE `facility_id` `facility_id` INT(11) NULL DEFAULT NULL;
ALTER TABLE `eid_form` CHANGE `result` `result` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `request_created_by` `request_created_by` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `last_modified_datetime` `last_modified_datetime` DATETIME NULL DEFAULT NULL, CHANGE `last_modified_by` `last_modified_by` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;

ALTER TABLE `eid_form` ADD `mother_treatment_other` VARCHAR(1000) NULL DEFAULT NULL AFTER `mother_treatment`;
ALTER TABLE `eid_form` CHANGE `reason_for_pcr` `reason_for_pcr` VARCHAR(500) NULL DEFAULT NULL;
ALTER TABLE `eid_form` CHANGE `sample_rejection_reason` `sample_rejection_reason` VARCHAR(500) NULL DEFAULT NULL;


INSERT INTO `privileges` (`privilege_id`, `resource_id`, `privilege_name`, `display_name`) VALUES (NULL, '25', 'eid-edit-request.php', 'Edit Request');
INSERT INTO `privileges` (`privilege_id`, `resource_id`, `privilege_name`, `display_name`) VALUES (NULL, '25', 'eid-requests.php', 'View Requests');

INSERT INTO `resources` (`resource_id`, `module`, `resource_name`, `display_name`) VALUES (26, 'eid', 'eid-batches', 'EID Batch Management');
INSERT INTO `privileges` (`privilege_id`, `resource_id`, `privilege_name`, `display_name`) VALUES (NULL, '26', 'eid-batches.php', 'View Batches');
INSERT INTO `privileges` (`privilege_id`, `resource_id`, `privilege_name`, `display_name`) VALUES (NULL, '26', 'eid-add-batch.php', 'Add Batch');
INSERT INTO `privileges` (`privilege_id`, `resource_id`, `privilege_name`, `display_name`) VALUES (NULL, '26', 'eid-edit-batch.php', 'Edit Batch');


ALTER TABLE `batch_details` ADD `test_type` VARCHAR(255) NULL DEFAULT NULL AFTER `batch_code_key`;
ALTER TABLE `eid_form` ADD `sample_batch_id` INT NULL DEFAULT NULL AFTER `last_modified_by`;
ALTER TABLE `eid_form` ADD `lot_number` VARCHAR(255) NULL DEFAULT NULL AFTER `sample_batch_id`;
ALTER TABLE `eid_form` ADD `lot_expiration_date` DATE NULL DEFAULT NULL AFTER `lot_number`;
ALTER TABLE `eid_form` ADD `result_reviewed_datetime` DATETIME NULL DEFAULT NULL AFTER `result`;
ALTER TABLE `eid_form` ADD `result_reviewed_by` VARCHAR(255) NULL DEFAULT NULL AFTER `result_reviewed_datetime`;
ALTER TABLE `eid_form` ADD `result_approved_datetime` DATETIME NULL DEFAULT NULL AFTER `result_reviewed_by`;
ALTER TABLE `eid_form` ADD `result_approved_by` VARCHAR(255) NULL DEFAULT NULL AFTER `result_approved_datetime`;


-- Version 3.11 ---------- Amit 28-May-2019


-- Thanaseelan 30-May-2019 For Betwwn Recency and VLSM API integration while Assay outcome is Assay Recent(VL lab test request)
ALTER TABLE `vl_request_form` ADD `recency_vl` VARCHAR(255) NOT NULL DEFAULT 'no' AFTER `remote_sample`;

-- Thanaseelan 03-May-2019 Create vl test resilt data send to recnecy or not status
ALTER TABLE `vl_request_form` ADD `recency_sync` INT(11) NULL DEFAULT NULL AFTER `recency_vl`;
ALTER TABLE `vl_request_form` CHANGE `recency_sync` `recency_sync` INT(11) NULL DEFAULT '0';


INSERT INTO `resources` (`resource_id`, `module`, `resource_name`, `display_name`) VALUES (27, 'eid', 'eid-results', 'EID Result Management');
INSERT INTO `privileges` (`privilege_id`, `resource_id`, `privilege_name`, `display_name`) VALUES (NULL, '27', 'eid-manual-results.php', 'Enter Result Manually');
INSERT INTO `privileges` (`privilege_id`, `resource_id`, `privilege_name`, `display_name`) VALUES (NULL, '27', 'eid-import-result.php', 'Import Result File');


-- Amit 17 June 2019

UPDATE `privileges` SET `privilege_name` = 'eid-manual-results.php' WHERE `privilege_name` = 'eid-results.php';
UPDATE `resources` SET `display_name` = 'Import Result' WHERE `resource_id` = 8;
UPDATE `privileges` SET `display_name` = 'Import Result from File' WHERE `privilege_id` = 19;
delete from roles_privileges_map where privilege_id = 53; 
delete from privileges where privilege_id = 53;

delete from roles_privileges_map where privilege_id = 54; 
delete from privileges where privilege_id = 54;


ALTER TABLE `eid_form` ADD `import_machine_file_name` VARCHAR(255) NULL AFTER `result_approved_by`;
ALTER TABLE `eid_form` ADD `sample_registered_at_lab` DATETIME NULL DEFAULT NULL AFTER `request_created_by`;
ALTER TABLE `temp_sample_import` ADD `module` VARCHAR(255) NULL DEFAULT NULL AFTER `temp_sample_id`;
ALTER TABLE `temp_sample_import` ADD `imported_by` VARCHAR(255) NOT NULL AFTER `sample_review_by`;


-- Amit 21 Jun 2019



CREATE TABLE `r_eid_sample_rejection_reasons` (
  `rejection_reason_id` int(11) NOT NULL,
  `rejection_reason_name` varchar(255) DEFAULT NULL,
  `rejection_type` varchar(255) NOT NULL DEFAULT 'general',
  `rejection_reason_status` varchar(255) DEFAULT NULL,
  `rejection_reason_code` varchar(255) DEFAULT NULL,
  `updated_datetime` datetime DEFAULT NULL,
  `data_sync` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `r_eid_sample_rejection_reasons`
--
ALTER TABLE `r_eid_sample_rejection_reasons`
  ADD PRIMARY KEY (`rejection_reason_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `r_eid_sample_rejection_reasons`
--
ALTER TABLE `r_eid_sample_rejection_reasons`
  MODIFY `rejection_reason_id` int(11) NOT NULL AUTO_INCREMENT;



CREATE TABLE `eid_imported_controls` (
 `control_id` int(11) NOT NULL AUTO_INCREMENT,
 `control_code` varchar(255) NOT NULL,
 `lab_id` int(11) DEFAULT NULL,
 `batch_id` int(11) DEFAULT NULL,
 `control_type` varchar(255) DEFAULT NULL,
 `lot_number` varchar(255) DEFAULT NULL,
 `lot_expiration_date` date DEFAULT NULL,
 `sample_tested_datetime` datetime DEFAULT NULL,
 `is_sample_rejected` varchar(255) DEFAULT NULL,
 `reason_for_sample_rejection` varchar(255) DEFAULT NULL,
 `result` varchar(255) DEFAULT NULL,
 `approver_comments` varchar(255) DEFAULT NULL,
 `result_approved_by` varchar(255) DEFAULT NULL,
 `result_approved_datetime` datetime DEFAULT NULL,
 `result_reviewed_by` varchar(1000) DEFAULT NULL,
 `result_reviewed_datetime` datetime DEFAULT NULL,
 `status` varchar(255) DEFAULT NULL,
 `vlsm_country_id` varchar(10) DEFAULT NULL,
 `file_name` varchar(255) DEFAULT NULL,
 `imported_date_time` datetime DEFAULT NULL,
 PRIMARY KEY (`control_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;  



ALTER TABLE `eid_form` ADD `eid_test_platform` VARCHAR(255) NULL DEFAULT NULL AFTER `lab_technician`;
ALTER TABLE `eid_form` ADD `result_dispatched_datetime` DATETIME NULL DEFAULT NULL AFTER `result_approved_by`;
ALTER TABLE `eid_form` ADD `approver_comments` VARCHAR(1000) NULL DEFAULT NULL AFTER `result_approved_by`;
ALTER TABLE `eid_form` ADD `import_machine_name` VARCHAR(255) NULL DEFAULT NULL AFTER `result_dispatched_datetime`;
ALTER TABLE `eid_form` ADD `manual_result_entry` VARCHAR(255) NULL DEFAULT 'no' AFTER `result_dispatched_datetime`;
ALTER TABLE `eid_form` CHANGE `request_created_on` `request_created_datetime` DATETIME NULL DEFAULT NULL;
ALTER TABLE `eid_form` CHANGE `sample_rejection_reason` `reason_for_sample_rejection` VARCHAR(500) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;


INSERT INTO `global_config` (`display_name`,`name`, `value`) VALUES ('EID Positive','eid_positive', 'Positive');
INSERT INTO `global_config` (`display_name`,`name`, `value`) VALUES ('EID Negative','eid_negative', 'Negative');
INSERT INTO `global_config` (`display_name`,`name`, `value`) VALUES ('EID Indeterminate','eid_indeterminate', 'Indeterminate');

ALTER TABLE `global_config` ADD `status` VARCHAR(255) NOT NULL DEFAULT 'active' AFTER `value`;
UPDATE `global_config` SET `status` = 'inactive' WHERE `global_config`.`name` = 'auto_approval';
UPDATE `global_config` SET `status` = 'inactive' WHERE `global_config`.`name` = 'enable_qr_mechanism';
UPDATE `global_config` SET `status` = 'inactive' WHERE `global_config`.`name` = 'sync_path';


CREATE TABLE `r_eid_results` (
  `result_id` varchar(255) NOT NULL,
  `result` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `data_sync` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `r_eid_results`
--

INSERT INTO `r_eid_results` (`result_id`, `result`, `status`, `data_sync`) VALUES
('indeterminate', 'Indeterminate', 'active', 0),
('negative', 'Negative', 'active', 0),
('positive', 'Positive', 'active', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `r_eid_results`
--
ALTER TABLE `r_eid_results`
  ADD PRIMARY KEY (`result_id`);

--

INSERT INTO `privileges` (`privilege_id`, `resource_id`, `privilege_name`, `display_name`) VALUES (NULL, '27', 'eid-result-status.php', 'Manage Result Status');
INSERT INTO `privileges` (`privilege_id`, `resource_id`, `privilege_name`, `display_name`) VALUES (NULL, '27', 'eid-print-results.php', 'Print Results');

ALTER TABLE `eid_form` CHANGE `sample_printed_datetime` `result_printed_datetime` DATETIME NULL DEFAULT NULL;
ALTER TABLE `eid_form` CHANGE `implementation_partner` `implementing_partner` INT(11) NULL DEFAULT NULL;


INSERT INTO `resources` (`resource_id`, `module`, `resource_name`, `display_name`) VALUES (28, 'eid', 'eid-management', 'EID Reports');
INSERT INTO `privileges` (`privilege_id`, `resource_id`, `privilege_name`, `display_name`) VALUES (NULL, 28, 'eid-export-data.php', 'Export Data');
INSERT INTO `privileges` (`privilege_id`, `resource_id`, `privilege_name`, `display_name`) VALUES (NULL, 28, 'eid-sample-rejection-report.php', 'Sample Rejection Report');
INSERT INTO `privileges` (`privilege_id`, `resource_id`, `privilege_name`, `display_name`) VALUES (NULL, 28, 'eid-sample-status.php', 'Sample Status Report');

ALTER TABLE `eid_form` ADD `result_mail_datetime` DATETIME NULL DEFAULT NULL AFTER `result_dispatched_datetime`;

UPDATE `resources` SET `module` = 'eid' WHERE `resource_name` = 'eid-requests';
UPDATE `privileges` SET `privilege_name` = 'vl-sample-status.php' WHERE `privilege_name` = 'missingResult.php';


-- Version 3.12 ---------- Amit 25-June-2019 

-- Amit 28 June 2019

INSERT INTO `global_config` (`display_name`, `name`, `value`, `status`) VALUES ('EID Maximum Length', 'eid_max_length', '', 'active'), ('EID Minimum Length', 'eid_min_length', '', 'active'), ('EID Sample Code', 'eid_sample_code', 'MMYY', 'active'), ('EID Sample Code Prefix', 'eid_sample_code_prefix', 'EID', 'active');

-- Amit 04 July 2019

ALTER TABLE `eid_form` ADD `sample_package_id` VARCHAR(255) NULL DEFAULT NULL AFTER `sample_batch_id`;


-- Amit 09 July 2019

ALTER TABLE `package_details` ADD `module` VARCHAR(255) NULL DEFAULT NULL AFTER `package_status`;
UPDATE `package_details` SET module = 'vl' where module is NULL;

-- Version 3.13 ---------- Amit 09 July 2019


-- Amit 19 August 2019
ALTER TABLE `vl_request_form` ADD `vldash_sync` INT NOT NULL DEFAULT '0' AFTER `vl_result_category`;

-- Version 3.14


-- Amit 15 October 2019

ALTER TABLE `eid_form` ADD `mother_surname` VARCHAR(255) NULL DEFAULT NULL AFTER `mother_name`;
ALTER TABLE `eid_form` ADD `child_surname` VARCHAR(255) NULL DEFAULT NULL AFTER `child_name`;
ALTER TABLE `eid_form` ADD `mode_of_delivery` VARCHAR(255) NULL DEFAULT NULL AFTER `mother_hiv_status`;
ALTER TABLE `eid_form` ADD `reason_for_eid_test` INT NULL DEFAULT NULL AFTER `specimen_type`;


CREATE TABLE `r_eid_test_reasons` (
 `test_reason_id` int(11) NOT NULL AUTO_INCREMENT,
 `test_reason_name` varchar(255) DEFAULT NULL,
 `test_reason_status` varchar(45) DEFAULT NULL,
 PRIMARY KEY (`test_reason_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Version 3.15

RENAME TABLE `r_sample_type` TO `r_vl_sample_type`;

CREATE TABLE `r_eid_sample_type` (
 `sample_id` int(11) NOT NULL AUTO_INCREMENT,
 `sample_name` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
 `status` varchar(45) CHARACTER SET latin1 DEFAULT NULL,
 `data_sync` int(11) NOT NULL DEFAULT '0',
 PRIMARY KEY (`sample_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `r_eid_sample_type` (`sample_id`, `sample_name`, `status`, `data_sync`) VALUES (NULL, 'DBS', 'active', '0'), (NULL, 'Whole Blood', 'active', '0');
ALTER TABLE `eid_form` ADD `lab_reception_person` VARCHAR(255) NULL DEFAULT NULL AFTER `lab_technician`;
ALTER TABLE `eid_form` ADD `child_treatment_other` VARCHAR(1000) NULL DEFAULT NULL AFTER `child_treatment`;
ALTER TABLE `eid_form` ADD `mother_treatment_initiation_date` DATE NULL DEFAULT NULL AFTER `mother_treatment_other`;
ALTER TABLE `eid_form` ADD `caretaker_contact_consent` VARCHAR(255) NULL DEFAULT NULL AFTER `mother_surname`;



-- Version 3.16 ---- Amit Nov 12 2019


-- Amit 14 Nov 2019

ALTER TABLE `eid_form` ADD `province_id` INT NULL DEFAULT NULL AFTER `facility_id`;


-- Version 3.17 ---- Amit Nov 14 2019

-- Amit 15 Nov 2019

ALTER TABLE `vl_request_form` ADD `sample_package_code` VARCHAR(255) NULL DEFAULT NULL AFTER `sample_package_id`;
ALTER TABLE `eid_form` ADD `sample_package_code` VARCHAR(255) NULL DEFAULT NULL AFTER `sample_package_id`;

UPDATE vl_request_form INNER JOIN package_details
    ON vl_request_form.sample_package_id = package_details.package_id
SET vl_request_form.sample_package_code = package_details.package_code 
WHERE vl_request_form.sample_package_code is NULL;

UPDATE eid_form INNER JOIN package_details
    ON eid_form.sample_package_id = package_details.package_id
SET eid_form.sample_package_code = package_details.package_code
WHERE eid_form.sample_package_code is NULL;
