<?php
ob_start();
session_start();
include('./includes/MysqliDb.php');

$tableName1="roles";
$tableName2="roles_privileges_map";
try {
        if(isset($_POST['roleName']) && trim($_POST['roleName'])!=""){
                $data=array(
                            'role_name'=>$_POST['roleName'],
                            'role_code'=>$_POST['roleCode'],
                            'status'=>$_POST['status']
                        );
                $db->insert($tableName1,$data);
                $lastId = $db->getInsertId();
                if($lastId!=0 && $lastId!=''){
                foreach($_POST['resource'] as $key=>$priviId){
                        if($priviId=='allow'){
                          $value = array('role_id'=>$lastId,'privilege_id'=>$key);
                          $db->insert($tableName2,$value);
                        }
                }
                $_SESSION['alertMsg']="Roles Added successfully";
                }
        }
        header("location:roles.php");
} catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}