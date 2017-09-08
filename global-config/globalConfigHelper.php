<?php
session_start();
ob_start();
include('../includes/MysqliDb.php');
include('../General.php');
include('../includes/ImageResize.php');
define('UPLOAD_PATH','../uploads');
$general = new Deforay_Commons_General();
$tableName="global_config";
$instanceTableName="s_vlsm_instance";
try {
	$configQuery ="SELECT value FROM global_config where name='sample_code'";
	$configResult = $db->rawQuery($configQuery);
	$configFormQuery ="SELECT value FROM global_config where name='vl_form'";
	$configFormResult = $db->rawQuery($configFormQuery);
	
	//remove instance table data
	if(isset($_POST['removedInstanceLogoImage']) && trim($_POST['removedInstanceLogoImage']) != "" && file_exists(UPLOAD_PATH . DIRECTORY_SEPARATOR . "instance-logo" . DIRECTORY_SEPARATOR . $_POST['removedInstanceLogoImage'])){
        unlink(UPLOAD_PATH . DIRECTORY_SEPARATOR . "instance-logo" . DIRECTORY_SEPARATOR . $_POST['removedInstanceLogoImage']);
        $data=array('instance_facility_logo'=>'');
        $db=$db->where('vlsm_instance_id',$_SESSION['instanceId']);
        $db->update($instanceTableName,$data);
    }
    if(isset($_POST['removedLogoImage']) && trim($_POST['removedLogoImage']) != "" && file_exists(UPLOAD_PATH . DIRECTORY_SEPARATOR . "logo" . DIRECTORY_SEPARATOR . $_POST['removedLogoImage'])){
        unlink(UPLOAD_PATH . DIRECTORY_SEPARATOR . "logo" . DIRECTORY_SEPARATOR . $_POST['removedLogoImage']);
        $data=array('value'=>'');
        $db=$db->where('name','logo');
        $db->update($tableName,$data);
        $_SESSION['alertMsg']="Logo deleted successfully";
    }
    
	if(isset($_FILES['instanceLogo']['name']) && $_FILES['instanceLogo']['name'] != ""){
		if(!file_exists(UPLOAD_PATH . DIRECTORY_SEPARATOR . "instance-logo") && !is_dir(UPLOAD_PATH . DIRECTORY_SEPARATOR . "instance-logo")) {
			mkdir(UPLOAD_PATH . DIRECTORY_SEPARATOR . "instance-logo");
		}
		$extension = strtolower(pathinfo(UPLOAD_PATH . DIRECTORY_SEPARATOR . $_FILES['instanceLogo']['name'], PATHINFO_EXTENSION));
		$string = $general->generateRandomString(6).".";
		$imageName = "logo".$string.$extension;
		if (move_uploaded_file($_FILES["instanceLogo"]["tmp_name"], UPLOAD_PATH . DIRECTORY_SEPARATOR . "instance-logo" . DIRECTORY_SEPARATOR . $imageName)) {
			$resizeObj = new Deforay_Image_Resize(UPLOAD_PATH . DIRECTORY_SEPARATOR ."instance-logo". DIRECTORY_SEPARATOR .$imageName);
			  $resizeObj->resizeImage(80, 80, 'auto');
			$resizeObj->saveImage(UPLOAD_PATH . DIRECTORY_SEPARATOR ."instance-logo". DIRECTORY_SEPARATOR. $imageName, 100);
			$image=array('instance_facility_logo'=>$imageName);
			$db=$db->where('vlsm_instance_id',$_SESSION['instanceId']);
			$db->update($instanceTableName,$image);
		}
    }
		$instanceData=array(
        'instance_facility_name'=>$_POST['fName'],
        'instance_facility_code'=>$_POST['fCode'],
        'instance_facility_type'=>$_POST['instance_type'],
        'instance_update_on'=>$general->getDateTime(),
        );
        $db=$db->where('vlsm_instance_id',$_SESSION['instanceId']);
        $updateInstance = $db->update($instanceTableName,$instanceData);
		if($updateInstance>0){
			//Add event log
            $eventType = 'update-instance';
            $action = ucwords($_SESSION['userName']).' update instance id';
            $resource = 'instance-details';
            $data=array(
                'event_type'=>$eventType,
                'action'=>$action,
                'resource'=>$resource,
                'date_time'=>$general->getDateTime()
            );
            $db->insert('activity_log',$data);
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
		if(($configResult[0]['value']!=$_POST['sample_code']) || ($_POST['vl_form']!=$configFormResult[0]['value']))
		{
			$prefix = trim($_POST['sample_code_prefix']);
			$vlDistinctQuery ="SELECT DISTINCT DATE_FORMAT( request_created_datetime,'%Y-%m' ) as month FROM vl_request_form where vlsm_country_id=".$_POST['vl_form'];
			$distnictResult = $db->rawQuery($vlDistinctQuery);
			if($distnictResult){
				$increment = 1;				
				foreach($distnictResult as $month){
					$y = explode("-",$month['month']);
					if($_POST['sample_code']=='YY'){
						$dtYr = substr($y[0],2);
						$start_date = date('Y-01-01');$end_date = date('Y-12-31');
					}
					else if($_POST['sample_code']=='MMYY'){
						$increment = 1;
						$dtYr = $y[1].substr($y[0],2);
						$start_date = date($month['month'].'-01');$end_date = date($month['month'].'-31');
					}
					if($_POST['vl_form']==7){
						$start_date = date('Y-01-01');$end_date = date('Y-12-31');
					}
					$vlQuery = 'select sample_code,vl_sample_id from vl_request_form as vl where vl.vlsm_country_id='.$_POST['vl_form'].' AND DATE(vl.request_created_datetime) >= "'.$start_date.'" AND DATE(vl.request_created_datetime) <= "'.$end_date.'" order by vl_sample_id';
					$svlResult=$db->query($vlQuery);
					
					foreach($svlResult as $sample){
						$maxId = $increment;
						$strparam = strlen($maxId);
						$zeros = substr("000", $strparam);
						$maxId = $zeros.$maxId;
						$sampleCode = $prefix.$dtYr.$maxId;
						$vlData = array('serial_no'=>$sampleCode,'sample_code_title'=>$_POST['sample_code'],'sample_code'=>$sampleCode,'sample_code_format'=>$prefix.$dtYr,'sample_code_key'=>$maxId);
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