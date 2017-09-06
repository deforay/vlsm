<?php
ob_start();
session_start();
include('includes/MysqliDb.php');
include('General.php');
include('includes/ImageResize.php');
define('UPLOAD_PATH','uploads');
$general=new Deforay_Commons_General();
$tableName="vl_instance";
$globalTable="global_config";
try {
    if(isset($_POST['fName']) && trim($_POST['fName'])!=""){
	$instanceId = '';
	if(isset($_SESSION['instanceId'])){
	    $instanceId = $_SESSION['instanceId'];
	}
	$db=$db->where('name','instance_type');
	$db->update($globalTable,array('value'=>$_POST['fType']));
        $data=array(
        'instance_facility_name'=>$_POST['fName'],
        'instance_facility_code'=>$_POST['fCode'],
        'instance_facility_type'=>$_POST['fType'],
        'instance_added_on'=>$general->getDateTime(),
        'instance_update_on'=>$general->getDateTime(),
        );
        $db=$db->where('vlsm_instance_id',$instanceId);
        $id = $db->update($tableName,$data);
        if($id>0){
            $_SESSION['instanceFname'] = $_POST['fName'];
            if(isset($_FILES['logo']['name']) && $_FILES['logo']['name'] != ""){
                if(!file_exists(UPLOAD_PATH . DIRECTORY_SEPARATOR . "instance-logo") && !is_dir(UPLOAD_PATH . DIRECTORY_SEPARATOR . "instance-logo")) {
                    mkdir(UPLOAD_PATH . DIRECTORY_SEPARATOR . "instance-logo");
                }
                $extension = strtolower(pathinfo(UPLOAD_PATH . DIRECTORY_SEPARATOR . $_FILES['logo']['name'], PATHINFO_EXTENSION));
                $string = $general->generateRandomString(6).".";
                $imageName = "logo".$string.$extension;
                if (move_uploaded_file($_FILES["logo"]["tmp_name"], UPLOAD_PATH . DIRECTORY_SEPARATOR . "instance-logo" . DIRECTORY_SEPARATOR . $imageName)) {
                    $resizeObj = new Deforay_Image_Resize(UPLOAD_PATH . DIRECTORY_SEPARATOR ."instance-logo". DIRECTORY_SEPARATOR .$imageName);
                      $resizeObj->resizeImage(80, 80, 'auto');
                $resizeObj->saveImage(UPLOAD_PATH . DIRECTORY_SEPARATOR ."instance-logo". DIRECTORY_SEPARATOR. $imageName, 100);
                    $image=array('instance_facility_logo'=>$imageName);
                    $db=$db->where('vlsm_instance_id',$instanceId);
                    $db->update($tableName,$image);
                }
            }
			//Add event log
            $eventType = 'add-instance';
            $action = ucwords($_SESSION['userName']).' added instance id';
            $resource = 'instance-details';
            $data=array(
                'event_type'=>$eventType,
                'action'=>$action,
                'resource'=>$resource,
                'date_time'=>$general->getDateTime()
            );
            $db->insert('activity_log',$data);
            $_SESSION['alertMsg']="Instance details added successfully";
            $_SESSION['success']="success";
        }else{
            $_SESSION['alertMsg']="Something went wrong!Please try again.";
        }
    }
    header("location:addInstanceDetails.php");
  
} catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}