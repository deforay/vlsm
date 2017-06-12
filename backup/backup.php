<?php
$dbhost = 'localhost';
$dbuser = 'root';
$dbpass = 'zaq12345';
$dbName="vl_lab_request";
   
$folderPath = "backup". DIRECTORY_SEPARATOR;
$currentDate = date("d-m-Y-H-i-s");
$file = $folderPath . 'vl-sample-' . $currentDate . '.sql';
if(PHP_OS=='Linux'){
  $command = sprintf("mysqldump -h %s -u %s --password='%s' -d %s --skip-no-data > %s", $dbhost, $dbuser, $dbpass, $dbName, $file);
}else if(PHP_OS=='WINNT'){
 $command = sprintf("mysqldump -h %s -u %s --password='%s' -d %s --skip-no-data > %s", $dbhost, $dbuser, $dbpass, $dbName, $file);
}
exec($command);