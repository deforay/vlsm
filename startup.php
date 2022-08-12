<?php

if (session_status() == PHP_SESSION_NONE) {
    session_set_cookie_params([
        'domain' => $_SERVER['HTTP_HOST'],
        'path' => '/',
        'samesite' => 'lax',
        'secure' => true,
        'httponly' => true
    ]);
    session_start();
}
$domain = '';
if (php_sapi_name() !== 'cli') {
    // base directory
    $baseDir = __DIR__ . "/../";

    $docRoot = preg_replace("!${_SERVER['SCRIPT_NAME']}$!", '', $_SERVER['SCRIPT_FILENAME']);

    // server protocol
    $protocol = (!empty($_SERVER['HTTPS'])  && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';

    // domain name
    $domain = $_SERVER['SERVER_NAME'];

    // base url
    $baseUrl = preg_replace("!^${docRoot}!", '', $baseDir);

    // server port
    $port = $_SERVER['SERVER_PORT'];
    $dispPort = ($protocol == 'http' && $port == 80 || $protocol == 'https' && $port == 443) ? '' : ":$port";

    // put em all together to get the complete base URL
    $domain = "${protocol}://${domain}${dispPort}${baseUrl}";
}

defined('DOMAIN')
    || define('DOMAIN', $domain);

defined('WEB_ROOT')
    || define('WEB_ROOT', (getenv('WEB_ROOT') ?
        getenv('WEB_ROOT') :
        realpath(__DIR__ . DIRECTORY_SEPARATOR . 'public')));

defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(WEB_ROOT . '/../app'));

defined('UPLOAD_PATH')
    || define('UPLOAD_PATH', WEB_ROOT . DIRECTORY_SEPARATOR . 'uploads');

defined('TEMP_PATH')
    || define('TEMP_PATH', WEB_ROOT . DIRECTORY_SEPARATOR . 'temporary');

defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ?
        getenv('APPLICATION_ENV') :
        'production'));

set_include_path(implode(PATH_SEPARATOR, array(
    realpath(__DIR__ . '/vendor'),
    get_include_path()
)));

require_once(APPLICATION_PATH . '/system/system.php');
require_once(APPLICATION_PATH . '/../vendor/autoload.php');

define('SYSTEM_CONFIG', require_once(APPLICATION_PATH . "/../configs/config." . APPLICATION_ENV . ".php"));

// Database Connection
$db = new MysqliDb(array(
    'host' => SYSTEM_CONFIG['dbHost'],
    'username' => SYSTEM_CONFIG['dbUser'],
    'password' => SYSTEM_CONFIG['dbPassword'],
    'db' =>  SYSTEM_CONFIG['dbName'],
    'port' => (!empty(SYSTEM_CONFIG['dbPort']) ? SYSTEM_CONFIG['dbPort'] : 3306),
    'charset' => (!empty(SYSTEM_CONFIG['dbCharset']) ? SYSTEM_CONFIG['dbCharset'] : 'utf8mb4')
));

// Locale
if (empty($_SESSION['APP_LOCALE'])) {
    $general = new \Vlsm\Models\General();
    $_SESSION['APP_LOCALE'] = $general->getGlobalConfig('app_locale');
    $_SESSION['APP_LOCALE'] = !empty($_SESSION['APP_LOCALE']) ? $_SESSION['APP_LOCALE'] : 'en_US';
}

putenv('LC_ALL=' . $_SESSION['APP_LOCALE']);
putenv('LANGUAGE=' . $_SESSION['APP_LOCALE']);
setlocale(LC_ALL,  $_SESSION['APP_LOCALE']);
$domain = "messages";
bindtextdomain($domain, APPLICATION_PATH . DIRECTORY_SEPARATOR . 'locale');
bind_textdomain_codeset($domain, 'UTF-8');
textdomain($domain);

// Timezone
if (empty($_SESSION['APP_TIMEZONE'])) {
    $_SESSION['APP_TIMEZONE'] = $general->getGlobalConfig('default_time_zone');
    $_SESSION['APP_TIMEZONE'] = !empty($_SESSION['APP_TIMEZONE']) ? $_SESSION['APP_TIMEZONE'] : 'UTC';
}

date_default_timezone_set($_SESSION['APP_TIMEZONE']);


// if (
//     isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
//     strcasecmp($_SERVER['HTTP_X_REQUESTED_WITH'], 'xmlhttprequest') == 0
// ) {
//     //Set our $isAjaxRequest to true.
//     error_log("Ajax Request from " . $_SERVER['REQUEST_URI']);
// }
    
// $request = Laminas\Diactoros\ServerRequestFactory::fromGlobals(
//     $_SERVER,
//     $_GET,
//     $_POST,
//     $_COOKIE,
//     $_FILES
// );

// Registry::set('request', $request);
