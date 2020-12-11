
CREATE DATABASE IF NOT EXISTS vlsm_audit;

CREATE TABLE `vlsm_audit`.`audit_vl_request_form` SELECT * from `vlsm`.`vl_request_form` WHERE 1=0;


-- DROP TRIGGER audit_vl_form;
DELIMITER //
CREATE TRIGGER audit_vl_form AFTER UPDATE ON vl_request_form
FOR EACH ROW BEGIN  
    INSERT INTO `vlsm_audit`.`audit_vl_request_form` SELECT * FROM `vlsm`.`vl_request_form` where `vlsm`.`vl_sample_id` = NEW.vl_sample_id;
END;//
DELIMITER ;