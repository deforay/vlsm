<?php

session_start();

// chdir(dirname(__DIR__));


if(php_sapi_name() !== 'cli'){
    // base directory
    $base_dir = __DIR__;

    $doc_root = preg_replace("!${_SERVER['SCRIPT_NAME']}$!", '', $_SERVER['SCRIPT_FILENAME']);
    
    // server protocol
    $protocol = empty($_SERVER['HTTPS']) ? 'http' : 'https';
    
    // domain name
    $domain = $_SERVER['SERVER_NAME'];
    
    // base url
    $base_url = preg_replace("!^${doc_root}!", '', $base_dir);
    
    // server port
    $port = $_SERVER['SERVER_PORT'];
    $disp_port = ($protocol == 'http' && $port == 80 || $protocol == 'https' && $port == 443) ? '' : ":$port";
    
    // put em all together to get the complete base URL
    $domain = "${protocol}://${domain}${disp_port}${base_url}";
    
    defined('DOMAIN')
        || define('DOMAIN', $domain); 
}


defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__)));

defined('UPLOAD_PATH')
    || define('UPLOAD_PATH', realpath(dirname(__FILE__) . '/uploads'));
    
defined('TEMP_PATH')
    || define('TEMP_PATH', realpath(dirname(__FILE__) . '/temporary'));     

defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? 
                                  getenv('APPLICATION_ENV') : 
                                  'production'));    