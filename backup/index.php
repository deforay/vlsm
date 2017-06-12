<?php
//$dbhost = 'localhost';
//    $dbuser = 'root';
//    $dbpass = 'zaq12345';
//    $dbName="vl_lab_request";
//    
//$folderPath = "backup". DIRECTORY_SEPARATOR;
//$currentDate = date("d-m-Y-H-i-s");
//$file = $folderPath . 'vl-sample-' . $currentDate . '.sql';
$path =  (__DIR__);
exec('mysqldump --user=root  --password="zaq12345"  --host=127.0.0.1  --port=3436 --databases vl_lab_request > output_file.sql');
$file = (__DIR__).'/output_file.sql';
$newfilePath = '../../backup'. DIRECTORY_SEPARATOR.'output_file.sql';
if(!is_dir('../../backup'))
{
 mkdir("../../backup");
 if (!copy($file, $newfilePath)) {
    echo "failed to copy $file...\
    ";
    }else{
        unlink($file);
    }
}else{
 if (!copy($file, $newfilePath)) {
    echo "failed to copy $file...\
    ";
    }else{
        unlink($file);
    }
}