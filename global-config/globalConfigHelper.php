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
	$configQuery ="SELECT value FROM global_config where name='sample_code'";
	$configResult = $db->rawQuery($configQuery);
	
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
	    list($width, $height) = getimagesize(UPLOAD_PATH . DIRECTORY_SEPARATOR ."logo". DIRECTORY_SEPARATOR .$imageName);
	    if($width>240){
	     $resizeObj->resizeImage(240, 80, 'auto');
	    }
	   }else{
             $resizeObj->resizeImage(80, 80, 'auto');
	   }
	   $resizeObj->saveImage(UPLOAD_PATH . DIRECTORY_SEPARATOR ."logo". DIRECTORY_SEPARATOR. $imageName, 100);
           $data=array('value'=>$imageName);
           $db=$db->where('name','logo');
           $db->update($tableName,$data);
       }
    }
    if(!isset($_POST['r_mandatory_fields'])){
	$data=array('value'=>null);
        $db=$db->where('name','r_mandatory_fields');
        $db->update($tableName,$data);
    }
    
    foreach ($_POST as $fieldName => $fieldValue) {
        if($fieldName!= 'removedLogoImage'){
	   if($fieldName == 'r_mandatory_fields'){
	     $fieldValue = implode(',',$fieldValue);
	   }
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
	//update all sample code in database
	if(isset($_POST['sample_code_prefix']) && trim($_POST['sample_code_prefix'])!='' && ($_POST['vl_form']==7 || $_POST['vl_form']==3 || $_POST['vl_form']==4)){
		if($configResult[0]['value']!=$_POST['sample_code'])
		{
			$prefix = trim($_POST['sample_code_prefix']);
			$vlDistinctQuery ="SELECT DISTINCT DATE_FORMAT( request_created_datetime,'%Y-%m' ) as month FROM vl_request_form";
			$distnictResult = $db->rawQuery($vlDistinctQuery);
			if($distnictResult){
				$increment = 1;
				foreach($distnictResult as $month){
					$start_date = date($month['month'].'-01');
					$end_date = date($month['month'].'-31');
					$vlQuery = 'select sample_code,vl_sample_id from vl_request_form as vl where vl.vlsm_country_id='.$_POST['vl_form'].' AND DATE(vl.request_created_datetime) >= "'.$start_date.'" AND DATE(vl.request_created_datetime) <= "'.$end_date.'" order by vl_sample_id';
					$svlResult=$db->query($vlQuery);
					$y = explode("-",$month['month']);
					if($_POST['sample_code']=='YY'){
						$dtYr = substr($y[0],2);
					}
					else if($_POST['sample_code']=='MMYY'){
						$increment = 1;
						$dtYr = $y[1].substr($y[0],2);
					}
					foreach($svlResult as $sample){
						$maxId = $increment;
						$strparam = strlen($maxId);
						$zeros = substr("000", $strparam);
						$maxId = $zeros.$maxId;
						$sampleCode = $prefix.$dtYr.$maxId;
						$vlData = array('serial_no'=>$sampleCode,'sample_code'=>$sampleCode,'sample_code_format'=>$prefix.$dtYr,'sample_code_key'=>$maxId);
						$db=$db->where('vl_sample_id',$sample['vl_sample_id']);
						$id=$db->update('vl_request_form',$vlData);
						$increment++;
					}
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