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
	   if($_POST['vl_form']==4){
	   $resizeObj->resizeImage(240, 80, 'auto'); 
	   }else{
           $resizeObj->resizeImage(80, 80, 'auto');
	   }
	   $resizeObj->saveImage(UPLOAD_PATH . DIRECTORY_SEPARATOR ."logo". DIRECTORY_SEPARATOR. $imageName, 100);
           $data=array('value'=>$imageName);
           $db=$db->where('name','logo');
           $db->update($tableName,$data);
       }
    }
    
    foreach ($_POST as $fieldName => $fieldValue) {
        if($fieldName!= 'removedLogoImage'){
           $data=array('value'=>$fieldValue);
           $db=$db->where('name',$fieldName);
           $db->update($tableName,$data);
	   //Generate syn sub folder
	   if($fieldName == 'sync_path' && trim($fieldValue)!= ''){
	    //root folder creation
	      if(!file_exists($fieldValue)){
		  mkdir($fieldValue);
	      }
	      //request folder creation
	      if(!file_exists($fieldValue  . DIRECTORY_SEPARATOR . "request")){
		mkdir($fieldValue . DIRECTORY_SEPARATOR . "request");
	      }if(!file_exists($fieldValue  . DIRECTORY_SEPARATOR . "request". DIRECTORY_SEPARATOR . "new")){
		mkdir($fieldValue  . DIRECTORY_SEPARATOR . "request". DIRECTORY_SEPARATOR . "new");
	      }if(!file_exists($fieldValue  . DIRECTORY_SEPARATOR . "request". DIRECTORY_SEPARATOR . "synced")){
		mkdir($fieldValue  . DIRECTORY_SEPARATOR . "request". DIRECTORY_SEPARATOR . "synced");
	      }if(!file_exists($fieldValue  . DIRECTORY_SEPARATOR . "request". DIRECTORY_SEPARATOR . "error")){
		mkdir($fieldValue  . DIRECTORY_SEPARATOR . "request". DIRECTORY_SEPARATOR . "error");
	      }
	       //result folder creation
	      if(!file_exists($fieldValue  . DIRECTORY_SEPARATOR . "result")){
		mkdir($fieldValue . DIRECTORY_SEPARATOR . "result");
	      }if(!file_exists($fieldValue  . DIRECTORY_SEPARATOR . "result". DIRECTORY_SEPARATOR . "new")){
		mkdir($fieldValue  . DIRECTORY_SEPARATOR . "result". DIRECTORY_SEPARATOR . "new");
	      }if(!file_exists($fieldValue  . DIRECTORY_SEPARATOR . "result". DIRECTORY_SEPARATOR . "synced")){
		mkdir($fieldValue  . DIRECTORY_SEPARATOR . "result". DIRECTORY_SEPARATOR . "synced");
	      }if(!file_exists($fieldValue  . DIRECTORY_SEPARATOR . "result". DIRECTORY_SEPARATOR . "error")){
		mkdir($fieldValue  . DIRECTORY_SEPARATOR . "result". DIRECTORY_SEPARATOR . "error");
	      }
	   }
        }
    }
    $_SESSION['alertMsg']="Global Config values updated successfully";
    header("location:globalConfig.php");
}catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}