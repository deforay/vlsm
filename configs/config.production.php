<?php
require_once(__DIR__ . '/../system/system.php');


$systemConfig = array();
$systemConfig['dbHost']     = '127.0.0.1';
$systemConfig['dbUser']     = 'root';
$systemConfig['dbPassword'] = 'zaq12345';
$systemConfig['dbName']     = 'vlsm';
$systemConfig['dbPort']     = 3306;
$systemConfig['passwordSalt']= '0This1Is2A3Real4Complex5And6Safe7Salt8With9Some10Dynamic11Stuff12Attched13later';

//Please use only GMAIL ID AND PASSWORD
$systemConfig['adminEmailUserName'] = '';
$systemConfig['adminEmailPassword'] = '';

// If using WAMP default settings, then uncomment the following 2 lines
//$systemConfig['dbPassword'] = '';
//$systemConfig['dbPort'] = 3306;

// Portable Uniform Server : following is the path in the portable Uniform Server
//$systemConfig['mysqlDump'] = __DIR__.'\..\..\core\mysql\bin\mysqldump.exe';

// Windows : Change it to the mysqldump.exe path for your computer for eg.
// $systemConfig['mysqlDump'] = 'C:\wamp\mysql\bin\mysqldump.exe';

// Linux : default for Ubuntu 16.04, may be different for your distribution
$systemConfig['mysqlDump'] = '/usr/bin/mysqldump';


// VLSTS URL
$systemConfig['remoteURL'] = 'http://vlsm-remote';


$interfaceConfig = array();
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


$eidConfig = array();
// Enable/Disable EID 
// true => Enabled
// false => Disabled
$eidConfig['enabled'] = true;


$recencyConfig = array();

// Domain URL of the Recency Web Application
$recencyConfig['url'] = "";

// This Salt should match the Salt on Recency Web app 
$recencyConfig['crossloginSalt'] = "0This1Is2A3Real4Complex5And6Safe7Salt8With9Some10Dynamic11Stuff12Attched13later";

// Enable/Disable Recency Sync 
// true => Enabled
// false => Disabled
$recencyConfig['vlsync'] = false;

// Enable/Disable Cross Login with Recency
// true => Enabled
// false => Disabled
$recencyConfig['crosslogin'] = true;
