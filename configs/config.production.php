<?php
require_once(__DIR__.'/../system/system.php'); 

// These settings are for the portable Uniform Server distribution

$HOST = '127.0.0.1';
$USER = 'root';
$PASSWORD = 'zaq12345';
$DBNAME = 'vl_lab_request';
$PORT = 3306;

$emailUserName='';
$emailPassword='';

// If using WAMP default settings, then uncomment the following 2 lines
//$PASSWORD = '';
//$PORT = 3306;

// Portable Uniform Server : following is the path in the portable Uniform Server
$MYSQLDUMP = __DIR__.'\..\..\core\mysql\bin\mysqldump.exe';

// Windows : Change it to the mysqldump.exe path for your computer for eg.
// $MYSQLDUMP = 'C:\wamp\mysql\bin\mysqldump.exe';

// Linux : default for Ubuntu 16.04, may be different for your distribution
//$MYSQLDUMP = '/usr/bin/mysqldump';
$REMOTEURL = 'http://vlsms.rbc.gov.rw';
