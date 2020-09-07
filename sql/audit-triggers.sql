CREATE TABLE `audit_vl_request_form` SELECT * from vl_request_form WHERE 1=0;


-- DROP TRIGGER audit_vl_form;
DELIMITER //
CREATE TRIGGER audit_vl_form AFTER UPDATE ON vl_request_form
FOR EACH ROW BEGIN  
    INSERT INTO audit_vl_request_form select * from vl_request_form where vl_sample_id = NEW.vl_sample_id;
END;//
DELIMITER ;