<?php
ob_start();
include('./includes/MysqliDb.php');
include('General.php');
define('UPLOAD_PATH','uploads');
$general=new Deforay_Commons_General();
$tableName="global_config";

try {
    if(isset($_POST['removedLogoImage']) && trim($_POST['removedLogoImage']) != "" && file_exists(UPLOAD_PATH . DIRECTORY_SEPARATOR . "logo" . DIRECTORY_SEPARATOR . $_POST['removedLogoImage'])){
        unlink(UPLOAD_PATH . DIRECTORY_SEPARATOR . "logo" . DIRECTORY_SEPARATOR . $_POST['removedLogoImage']);
        $data=array('value'=>'');
        $db=$db->where('name','logo');
        $db->update($tableName,$data);
        $_SESSION['alertMsg']="Logo deleted successfully";
    }
    if(isset($_FILES['logoImage']['name']) && $_FILES['logoImage']['name'] != ""){
       if(!file_exists(UPLOAD_PATH . DIRECTORY_SEPARATOR . "logo") && !is_dir(UPLOAD_PATH . DIRECTORY_SEPARATOR . "logo")) {
           mkdir(UPLOAD_PATH . DIRECTORY_SEPARATOR . "logo");
       }
       $extension = strtolower(pathinfo(UPLOAD_PATH . DIRECTORY_SEPARATOR . $_FILES['logoImage']['name'], PATHINFO_EXTENSION));
       $string = $general->generateRandomString(6).".";
       $imageName = "logo".$string.$extension;
       if (move_uploaded_file($_FILES["logoImage"]["tmp_name"], UPLOAD_PATH . DIRECTORY_SEPARATOR . "logo" . DIRECTORY_SEPARATOR . $imageName)) {
           $data=array('value'=>$imageName);
           $db=$db->where('name','logo');
           $db->update($tableName,$data);
           $_SESSION['alertMsg']="Logo uploaded successfully";
       }
    }
    header("location:globalConfig.php");
}catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}