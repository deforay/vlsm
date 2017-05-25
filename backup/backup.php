<?php

//$folderPath = "backup". DIRECTORY_SEPARATOR;
//$currentDate = date("d-m-Y-H-i-s");
//$file = $folderPath . 'vl-sample-' . $currentDate . '.sql';
//$command = sprintf("mysqldump -h %s -u %s --password='%s' -d %s --skip-no-data > %s", $dbHost, $dbUsername, $dbPassword, $dbName, $file);
//exec($command);
    
    $dbhost = 'localhost';
    $dbuser = 'root';
    $dbpass = 'zaq12345';
    $dbName="vl_lab_request";
    
    $conn = mysqli_connect($dbhost,$dbuser,$dbpass,$dbName);
    if (mysqli_connect_errno())
    {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
    
    $sql = "SHOW TABLES";
    $return = "";
    $result = mysqli_query($conn,$sql);
    $finalForeignKey = '';
    
    while ($row = mysqli_fetch_row($result)) {
        foreach ($row as $col => $val) {
            $tables[] = $val;
        }
    }
    
    foreach ($tables as $table) {
        
        $sql="SELECT * FROM ".$table;
        $result = mysqli_query($conn,$sql);
        $num_fields=mysqli_num_fields($result)-1;
        
        $return.= 'DROP TABLE IF EXISTS ' . $table . ';';
        
        $row2 = mysqli_fetch_row(mysqli_query($conn,'show create table ' . $table));
        $create_table = "\n\n" . $row2[1] . ";\n\n";
        //print_r($row2);
        
        $return.= 'DROP TABLE IF EXISTS ' . $table . ';';
        
        $createTable = explode("CONSTRAINT", $create_table);
        $n = count($createTable);
        
        $foreignQuery = '';
        $sqlEngine = '';
        $foreignLastKey = '';
        for ($i = 0; $i < $n; $i++) {
            if ($i == 0) {
                $query = trim($createTable[$i]);
                $last = $query[strlen($query) - 1];
                if ($last == ',') {
                    $query = substr($query, 0, -1);
                }
            } else if ($i == ($n - 1)) {
                $ex = explode("ENGINE", $createTable[$i]);
                $foreignLastKey = 'ADD CONSTRAINT ' . substr($ex[0], 0, -2);
                $sqlEngine = ')ENGINE' . $ex[1];
            } else {
                $foreignQuery.=trim($createTable[$i]);
            }
        }
        if ($foreignQuery) {
            $addConstraint = explode(",", $foreignQuery);
            $k = count($addConstraint);

            $explodeForeign = "";
            for ($j = 0; $j < $k; $j++) {
                if ($addConstraint[$j] != "") {
                    $explodeForeign.='ADD CONSTRAINT ' . $addConstraint[$j] . ',';
                }
            }
            $finalForeignKey.='ALTER TABLE ' . $table . ' ' . $explodeForeign . $foreignLastKey . ";";
        } else if ($foreignLastKey != "") {
            $finalForeignKey.='ALTER TABLE ' . $table . ' ' . $foreignLastKey . ";";
        }

        $return.=$query . $sqlEngine;

        $sql="SELECT * FROM ".$table;
        
        $result = mysqli_query($conn,$sql);
       
        //$result = $db->fetchAll($db->select()->from(array($table)));
        //if (count($result) > 0) {
            $return.= 'INSERT INTO ' . $table . ' VALUES';
            while ($row = mysqli_fetch_array($result)) {
                $colValue = "";
                $i = 0;
                foreach ($row as $col => $value) {
                    if ($num_fields == $i) {

                        if (isset($value)) {
                            $colValue.= '"' . addslashes($value) . '"),';
                        } else {
                            $colValue.= ' NULL ),';
                        }
                    } else {
                        if ($colValue == "") {
                            if (isset($value)) {
                                $colValue.= '("' . addslashes($value) . '",';
                            } else {
                                $colValue.= '( NULL,';
                            }
                        } else {
                            if (isset($value)) {
                                $colValue.= '"' . addslashes($value) . '",';
                            } else {
                                $colValue.= 'NULL,';
                            }
                        }
                    }
                    $i++;
                }
                $return.=$colValue;
            }
            $return = substr($return, 0, -1) . ";";
        //}
    }
    
    $sqlQuery = $return . $finalForeignKey;
    $folderPath = "backup". DIRECTORY_SEPARATOR;
     if (!file_exists($folderPath) && !is_dir($folderPath)) {
        mkdir($folderPath);
    }
    $date = 'db-backup-' . date("d-m-Y-H-i-s");
    $current_file_name=sprintf($date.'.sql');
    $fileName = $folderPath.$current_file_name;
    
    $handle = fopen($fileName, 'w+');
    fwrite($handle, $sqlQuery);
    fclose($handle);
    $days=30;
   
    if (is_dir($folderPath)) {
        $dh = opendir($folderPath);
        while (($oldFileName = readdir($dh)) !== false) {
            if($oldFileName == 'index.php'){
                continue;
            }               
            $file=$folderPath.$oldFileName;       
            if(time()-filemtime($file)> (86400) * $days){
               unlink($file);
            }
        }
        closedir($dh);
    }
    $destination = "backup" . DIRECTORY_SEPARATOR . $current_file_name;
    if (file_exists($destination)) {
      //$files_to_zip = $destination;
      //create the archive
      //$zip = new ZipArchive();
      //if($zip->open('backup/db-backup-' . date("d-m-Y-H-i-s").'.zip', ZIPARCHIVE::CREATE) !== true) {
      //    echo 'error';
      //}
      ////add the files
      //$zip->addFile($files_to_zip,$files_to_zip);
      ////debug
      ////echo 'The zip archive contains ',$zip->numFiles,' files with a status of ',$zip->status;
      //
      ////close the zip -- done!
      //$zip->close();
      
      system('zip --password saro backup/' . $date.'.zip '.$destination);
      
      //return file_exists('my-archive.zip');
      
        header('Content-Description: File Transfer');
        header("Content-type: application/zip");
        header('Content-Disposition: attachment; filename=' . basename('backup/' . $date.'.zip'));
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Content-Length: ' . filesize('backup/' . $date.'.zip'));
        ob_clean();
        flush();
        readfile('backup/' . $date.'.zip');
        unlink($destination);
        exit;
    }
    mysqli_close($conn);
