<?php

require_once(__DIR__.'/config.production.php');

$HOST = 'localhost';
$USER = 'root';
$PASSWORD = 'zaq12345';
$DBNAME = 'vlsm';
$PORT = 3306;

// Portable Uniform Server : following is the path in the portable Uniform Server
//$MYSQLDUMP = __DIR__.'\..\..\core\mysql\bin\mysqldump.exe';

// Windows : Change it to the mysqldump.exe path for your computer for eg.
// $MYSQLDUMP = 'C:\wamp\mysql\bin\mysqldump.exe';

// Linux : default for Ubuntu 16.04, may be different for your distribution
$MYSQLDUMP = '/usr/bin/mysqldump';

// VLSTS URL
$REMOTEURL = '';


// Enable/Disable Interfacing
// true => Enabled
// false => Disabled
$interfaceConfig['enabled'] = false;


// Interfacing Database Details (not needed if above feature set to false)
$interfaceConfig['dbHost'] = '127.0.0.1';
$interfaceConfig['dbUser'] = 'root';
$interfaceConfig['dbPassword'] = 'zaq12345';
$interfaceConfig['dbName'] = 'interfacing';
$interfaceConfig['dbPort'] = 3306;

// Enable/Disable EID 
// true => Enabled
// false => Disabled
$eidConfig['enabled'] = true;

// Domain URL of the Recency Web Application
$recencyConfig['url'] = "";

// Enable/Disable Recency Web Application Sync 
// true => Enabled
// false => Disabled
$recencyConfig['vlsync'] = false;

// Enable/Disable Cross Login with Recency Web Application
// true => Enabled
// false => Disabled
$recencyConfig['crosslogin'] = false;