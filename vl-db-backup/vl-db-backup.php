<?php
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? 
                                  getenv('APPLICATION_ENV') : 
                                  'production'));

if(APPLICATION_ENV == 'production'){
	require_once('../includes/config.production.php');
}else{
	require_once('../includes/config.development.php');
}
define("BACKUP_PATH", "../db-backup");

$dbUsername = $USER;
$dbPassword = $PASSWORD;
$dbName = $DBNAME;
$dbHost = $HOST;

$folderPath = BACKUP_PATH . DIRECTORY_SEPARATOR;

if (!file_exists($folderPath) && !is_dir($folderPath)) {
    mkdir($folderPath);
}
$currentDate = date("d-m-Y-H-i-s");
$file = $folderPath . 'vl-dbdump-' . $currentDate . '.sql';
$command = sprintf("mysqldump -h %s -u %s --password='%s' -d %s --skip-no-data > %s", $dbHost, $dbUsername, $dbPassword, $dbName, $file);
exec($command);

$days = 30;
if (is_dir($folderPath)) {
    $dh = opendir($folderPath);
    while (($oldFileName = readdir($dh)) !== false) {
        if ($oldFileName == 'index.php' || $oldFileName == "." || $oldFileName == ".." || $oldFileName == "") {
            continue;
        }
        
        if (time() - filemtime($file) > (86400) * $days) {
            unlink($file);
        }
    }
    closedir($dh);
}