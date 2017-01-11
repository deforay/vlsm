

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
CREATE TABLE IF NOT EXISTS `testing_status` (
  `status_id` int(11) NOT NULL AUTO_INCREMENT,
  `status_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`status_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

--
-- Dumping data for table `testing_status`
--

INSERT INTO `testing_status` (`status_id`, `status_name`) VALUES
(1, 'waiting'),
(2, 'lost'),
(3, 'sample reordered'),
(4, 'cancel'),
(5, 'invalid');

ALTER TABLE vl_request_form
ADD FOREIGN KEY (status)
REFERENCES testing_status(status_id)


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


ALTER TABLE  `vl_request_form` ADD  `location` VARCHAR( 255 ) NULL DEFAULT NULL AFTER  `patient_phone_number` ;
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
INSERT INTO `testing_status` (`status_id`, `status_name`) VALUES (NULL, 'Awaiting Clinic Approval'), (NULL, 'Received and Approved');
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
ALTER TABLE  `vl_request_form` ADD  `patient_receive_sms` VARCHAR( 255 ) NULL DEFAULT NULL AFTER  `arv_adherence` ;
ALTER TABLE  `vl_request_form` ADD  `switch_to_tdf_last_vl_date` DATE NULL DEFAULT NULL AFTER  `suspected_treatment_failure_sample_type` ,
ADD  `switch_to_tdf_value` VARCHAR( 255 ) NULL DEFAULT NULL AFTER  `switch_to_tdf_last_vl_date` ,
ADD  `switch_to_tdf_sample_type` INT NULL DEFAULT NULL AFTER  `switch_to_tdf_value` ,
ADD  `missing_last_vl_date` DATE NULL DEFAULT NULL AFTER  `switch_to_tdf_sample_type` ,
ADD  `missing_value` VARCHAR( 255 ) NULL DEFAULT NULL AFTER  `missing_last_vl_date` ,
ADD  `missing_sample_type` INT NULL DEFAULT NULL AFTER  `missing_value` ;

--saravanan 31-aug-2016
ALTER TABLE  `vl_request_form` ADD  `viral_load_indication` VARCHAR( 255 ) NULL DEFAULT NULL AFTER  `patient_receive_sms` ;
ALTER TABLE  `vl_request_form` ADD  `enhance_session` VARCHAR( 255 ) NULL DEFAULT NULL AFTER  `viral_load_indication` ;
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

ALTER TABLE `vl_request_form` ADD `reason_for_regimen_change` VARCHAR(255) NULL DEFAULT NULL AFTER `has_patient_changed_regimen`, ADD `date_of_regimen_changed` DATE NULL DEFAULT NULL AFTER `reason_for_regimen_change`;

ALTER TABLE `vl_request_form` ADD `plasma_storage_temperature` FLOAT NULL DEFAULT NULL AFTER `date_of_regimen_changed`;


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
ALTER TABLE `vl_request_form` ADD `poor_adherence` VARCHAR(255) NULL DEFAULT NULL AFTER `arv_adherence`;

--Pal 11-Jan-2017
INSERT INTO `privileges` (`privilege_id`, `resource_id`, `privilege_name`, `display_name`) VALUES (NULL, '6', 'vlRequestMailConfirm.php', 'Email Test Request Confirm');

INSERT INTO `privileges` (`privilege_id`, `resource_id`, `privilege_name`, `display_name`) VALUES (NULL, '6', 'vlResultMailConfirm.php', 'Email Test Result Confirm');