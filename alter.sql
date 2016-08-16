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
--pal 16-aug-2016
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


ALTER TABLE  `contact_notes_details` ADD  `added_on` DATETIME NULL DEFAULT NULL ;