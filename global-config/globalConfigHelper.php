<?php
session_start();
ob_start();
include('../includes/MysqliDb.php');
include('../General.php');
include('../includes/ImageResize.php');
define('UPLOAD_PATH','../uploads');
$general = new Deforay_Commons_General();
$tableName="global_config";
try {
    if(isset($_POST['removedLogoImage']) && trim($_POST['removedLogoImage']) != "" && file_exists(UPLOAD_PATH . DIRECTORY_SEPARATOR . "logo" . DIRECTORY_SEPARATOR . $_POST['removedLogoImage'])){
        unlink(UPLOAD_PATH . DIRECTORY_SEPARATOR . "logo" . DIRECTORY_SEPARATOR . $_POST['removedLogoImage']);
        $data=array('value'=>'');
        $db=$db->where('name','logo');
        $db->update($tableName,$data);
        $_SESSION['alertMsg']="Logo deleted successfully";
    }
    
    if(isset($_FILES['logo']['name']) && $_FILES['logo']['name'] != ""){
       if(!file_exists(UPLOAD_PATH . DIRECTORY_SEPARATOR . "logo") && !is_dir(UPLOAD_PATH . DIRECTORY_SEPARATOR . "logo")) {
           mkdir(UPLOAD_PATH . DIRECTORY_SEPARATOR . "logo");
       }
       $extension = strtolower(pathinfo(UPLOAD_PATH . DIRECTORY_SEPARATOR . $_FILES['logo']['name'], PATHINFO_EXTENSION));
       $string = $general->generateRandomString(6).".";
       $imageName = "logo".$string.$extension;
       if (move_uploaded_file($_FILES["logo"]["tmp_name"], UPLOAD_PATH . DIRECTORY_SEPARATOR . "logo" . DIRECTORY_SEPARATOR . $imageName)) {
           $resizeObj = new Deforay_Image_Resize(UPLOAD_PATH . DIRECTORY_SEPARATOR ."logo". DIRECTORY_SEPARATOR .$imageName);
           $resizeObj->resizeImage(80, 80, 'auto');
	   $resizeObj->saveImage(UPLOAD_PATH . DIRECTORY_SEPARATOR ."logo". DIRECTORY_SEPARATOR. $imageName, 100);
           $data=array('value'=>$imageName);
           $db=$db->where('name','logo');
           $db->update($tableName,$data);
       }
    }
    //print_r($_POST);die;
    foreach ($_POST as $fieldName => $fieldValue) {
        if($fieldName!= 'removedLogoImage'){
           $data=array('value'=>$fieldValue);
           $db=$db->where('name',$fieldName);
           $db->update($tableName,$data);
        }
    }
    $_SESSION['alertMsg']="Global Config values updated successfully";
    header("location:globalConfig.php");
}catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}