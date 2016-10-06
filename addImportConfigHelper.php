<?php
session_start();
ob_start();
include('./includes/MysqliDb.php');

$tableName="import_config";

try {
    if(trim($_POST['configurationName'])!=""){
        $data=array(
        'machine_name'=>$_POST['configurationName'],
        'file_name'=>$_POST['configurationFile'],
        'lower_limit'=>$_POST['lowerLimit'],
        'higher_limit'=>$_POST['higherLimit'],
        'status' => 'active'
        );
        //print_r($data);die;
        $db->insert($tableName,$data);    
        
        $_SESSION['alertMsg']="Result Import configuration initited for ".$_POST['configurationName'].". Please proceed to write the import logic in the file ".$_POST['configurationFile']." present in import-configs folder" ;
        
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