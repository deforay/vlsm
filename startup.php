<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
    // if (empty($_SESSION['csrf'])) {
    //     $_SESSION['csrf'] = bin2hex(random_bytes(32));
    // }    
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