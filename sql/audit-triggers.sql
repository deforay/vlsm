
CREATE DATABASE IF NOT EXISTS audit_vlsm;

CREATE TABLE `audit_vlsm`.`vl_request_form` SELECT * from `vlsm`.`vl_request_form` WHERE 1=0;


-- DROP TRIGGER audit_vl_request_form;
DELIMITER //
CREATE TRIGGER `vlsm`.`audit_vl_request_form` AFTER UPDATE ON `vlsm`.`vl_request_form`
FOR EACH ROW BEGIN  
    INSERT INTO `audit_vlsm`.`vl_request_form` SELECT * FROM `vlsm`.`vl_request_form` where `vlsm`.vl_request_form.`vl_sample_id` = `audit_vlsm`.NEW.vl_sample_id;
END;//
DELIMITER ;