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