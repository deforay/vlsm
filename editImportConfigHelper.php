<?php
session_start();
ob_start();
include('./includes/MysqliDb.php');

$tableName="import_config";
$configId=(int) base64_decode($_POST['configId']);
try {
    if(trim($_POST['configurationName'])!=""){
    $importConfigData=array(
    'machine_name'=>$_POST['configurationName'],
    'file_name'=>$_POST['configurationFile'],
    'status'=>$_POST['status']
    );
    //print_r($importConfigData);die;
    $db=$db->where('config_id',$configId);
    //print_r($vldata);die;
    $db->update($tableName,$importConfigData);        
    
        $_SESSION['alertMsg']="Import config details updated successfully";
    
        $configDir = __DIR__.DIRECTORY_SEPARATOR.'import-configs';
        $configFile = $configDir.DIRECTORY_SEPARATOR.$_POST['configurationFile'];
        
        
        if (!file_exists($configDir)) {
            mkdir($configDir, 0777, true);
        }
        
        if (!file_exists($configFile)) {
            $fp=fopen($configFile,'w');
            fwrite($fp, '');
            fclose($fp);
        }    
    
    
    }
    header("location:importConfig.php");
  
} catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}