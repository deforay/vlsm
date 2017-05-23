<?php
session_start();
ob_start();
include('../includes/MysqliDb.php');

$tableName="import_config";
$importMachineTable="import_config_machines";

$configId=(int) base64_decode($_POST['configId']);
try {
    if(trim($_POST['configurationName'])!=""){
    $importConfigData=array(
    'machine_name'=>$_POST['configurationName'],
    'import_machine_file_name'=>$_POST['configurationFile'],
    'lower_limit'=>$_POST['lowerLimit'],
    'higher_limit'=>$_POST['higherLimit'],
    'max_no_of_samples_in_a_batch'=>$_POST['maxNOfSamplesInBatch'],
    'number_of_in_house_controls'=>$_POST['noOfInHouseControls'],
    'number_of_manufacturer_controls'=>$_POST['noOfManufacturerControls'],
    'number_of_calibrators'=>$_POST['numberOfCalibrators'],
    'status'=>$_POST['status']
    );
    //print_r($importConfigData);die;
    $db=$db->where('config_id',$configId);
    //print_r($vldata);die;
    $db->update($tableName,$importConfigData);
    if(count($_POST['configMachineName'])>0){
        for($c = 0;$c<count($_POST['configMachineName']);$c++){
            if(trim($_POST['configMachineName'][$c])!=''){
                if(isset($_POST['configMachineId'][$c]) && $_POST['configMachineId'][$c]!=''){
                    $configMachineData = array('config_machine_name'=>$_POST['configMachineName'][$c]);
                    $db=$db->where('config_machine_id',$_POST['configMachineId'][$c]);
                    $db->update($importMachineTable,$configMachineData);
                }else{
                    $configMachineData = array('config_id'=>$configId,'config_machine_name'=>$_POST['configMachineName'][$c]);
                    $db->insert($importMachineTable,$configMachineData);
                }
            }
        }
    }
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
