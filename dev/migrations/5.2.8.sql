-- Amit 26-Dec-2023 version 5.2.8
UPDATE `system_config` SET `value` = '5.2.8' WHERE `system_config`.`name` = 'sc_version';

-- Jeyabanu 26-Dec-2023
CREATE TABLE `temp_mail` (
  `id` int NOT NULL AUTO_INCREMENT,
  `test_type` varchar(10) DEFAULT NULL,
  `samples` varchar(256) DEFAULT NULL,
  `to_mail` varchar(255) DEFAULT NULL,
  `report_email` varchar(256) DEFAULT NULL,
  `subject` varchar(200) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `text_message` varchar(255) DEFAULT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  `status` varchar(11) DEFAULT NULL,
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1
