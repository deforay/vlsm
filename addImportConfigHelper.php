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
        'max_no_of_samples_in_a_batch'=>$_POST['maxNOfSamplesInBatch'],
        'number_of_in_house_controls'=>$_POST['noOfInHouseControls'],
        'number_of_manufacturer_controls'=>$_POST['noOfManufacturerControls'],
        'number_of_calibrators'=>$_POST['numberOfCalibrators'],
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