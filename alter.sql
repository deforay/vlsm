ALTER TABLE vl_request_form DROP FOREIGN KEY vl_request_form_ibfk_2

ALTER TABLE `vl_request_form` CHANGE `art_no` `art_no` VARCHAR( 255 ) NULL DEFAULT NULL ;
