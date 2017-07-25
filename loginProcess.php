<?php
session_start();
ob_start();
include('./includes/MysqliDb.php');
include('General.php');
$general=new Deforay_Commons_General();

try {
    if(isset($_POST['username']) && trim($_POST['username'])!="" && isset($_POST['password']) && trim($_POST['password'])!=""){
        $passwordSalt = '0This1Is2A3Real4Complex5And6Safe7Salt8With9Some10Dynamic11Stuff12Attched13later';
        $password = sha1($_POST['password'].$passwordSalt);
        $adminUsername=$db->escape($_POST['username']);
        $adminPassword=$db->escape($password);
        $params = array($adminUsername,$adminPassword,'active');
        $admin = $db->rawQuery("SELECT ud.user_id,ud.user_name,ud.email,r.role_name,r.role_code,r.role_id,r.landing_page FROM user_details as ud INNER JOIN roles as r ON ud.role_id=r.role_id WHERE ud.login_id = ? AND ud.password = ? AND ud.status = ?", $params);
        
        if(count($admin)>0){
            //add random key
            $instanceQuery="SELECT * FROM vl_instance";
            $instanceResult=$db->query($instanceQuery);
            if($instanceResult){
                $_SESSION['instanceId']=$instanceResult[0]['vlsm_instance_id'];
            }else{
                $id = $general->generateRandomString(32);
                $db->insert('vl_instance',array('vlsm_instance_id'=>$id));
                $_SESSION['instanceId']=$id;
                
                //Update instance ID in facility and vl_request_form tbl
                $data=array('vlsm_instance_id'=>$id);
                $db->update('facility_details',$data);
                
                //$data=array('vlsm_instance_id'=>$id);
                $db->update('vl_request_form',$data);                
                
            }
            //Add event log
            $eventType = 'login';
            $action = ucwords($admin[0]['user_name']).' logged in';
            $resource = 'user-login';
            $data=array(
                'event_type'=>$eventType,
                'action'=>$action,
                'resource'=>$resource,
                'date_time'=>$general->getDateTime()
            );
            $db->insert('activity_log',$data);
    
            $_SESSION['userId']=$admin[0]['user_id'];
            $_SESSION['userName']=ucwords($admin[0]['user_name']);
            $_SESSION['roleCode']=$admin[0]['role_code'];
            $_SESSION['email']=$admin[0]['email'];
            
            $redirect = 'error/error.php';
            //set role and privileges
            $priQuery="SELECT p.privilege_name,rp.privilege_id from roles_privileges_map as rp INNER JOIN privileges as p ON p.privilege_id=rp.privilege_id  where rp.role_id='".$admin[0]['role_id']."'";
            $priInfo=$db->query($priQuery);
            $priId = array();
            if($priInfo){
              foreach($priInfo as $id){
                $priId[] = $id['privilege_name'];
              }
              
              if($admin[0]['landing_page']!=''){
                 $redirect= $admin[0]['landing_page'];
              }else{
                $fileNameList = array('index.php','addVlRequest.php','vlRequest.php','batchcode.php','vlRequestMail.php','addImportResult.php','vlPrintResult.php','vlTestResult.php','missingResult.php','vlResult.php','highViralLoad.php','roles.php','users.php','facilities.php','globalConfig.php','importConfig.php');
                $fileName = array('dashboard/index.php','vl-request/addVlRequest.php','vl-request/vlRequest.php','batch/batchcode.php','mail/vlRequestMail.php','import-result/addImportResult.php','vl-print/vlPrintResult.php','vl-print/vlTestResult.php','program-management/missingResult.php','program-management/vlResult.php','program-management/highViralLoad.php','roles/roles.php','users/users.php','facilities/facilities.php','global-config/globalConfig.php','import-configs/importConfig.php');
                foreach($fileNameList as $redirectFile){
                  if(in_array($redirectFile,$priId)){
                    $arrIndex = array_search($redirectFile,$fileNameList);
                    $redirect = $fileName[$arrIndex];
                    break;
                  }
                }
              }
            }
            $_SESSION['privileges'] = $priId;
            header("location:".$redirect);
        }else{
            header("location:login.php");
            $_SESSION['alertMsg']="Please check login credential";
        }
    }else{
        header("location:login.php");
    }
} catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}
