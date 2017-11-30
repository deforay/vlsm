<?php
require_once(__DIR__.'/../system/system.php'); 

// These settings are for the portable Uniform Server distribution

$HOST = 'localhost';
$USER = 'root';
$PASSWORD = 'zaq12345';
$DBNAME = 'vl_lab_request_ss';
//$DBNAME = 'vl_lab_request_rwanda';
$PORT = 3306;

$sHOST = 'localhost';
$sUSER = 'root';
$sPASSWORD = 'zaq12345';
$sDBNAME = 'vl_lab_request_rwanda';
//$sDBNAME = 'vl_lab_request';
$sPORT = 3306;

// If using WAMP default settings, then uncomment the following 2 lines
//$PASSWORD = '';
//$PORT = 3306;

// Portable Uniform Server : following is the path in the portable Uniform Server
$MYSQLDUMP = __DIR__.'\..\..\core\mysql\bin\mysqldump.exe';

// Windows : Change it to the mysqldump.exe path for your computer for eg.
// $MYSQLDUMP = 'C:\wamp\mysql\bin\mysqldump.exe';

// Linux : default for Ubuntu 16.04, may be different for your distribution
//$MYSQLDUMP = '/usr/bin/mysqldump'; 