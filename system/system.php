<?php

define('VERSION', '3.10.7');


/**********/

defined('UPLOAD_PATH')
    || define('UPLOAD_PATH',realpath(__DIR__.DIRECTORY_SEPARATOR.'/../uploads'));
defined('TEMP_PATH')
    || define('TEMP_PATH',realpath(__DIR__.DIRECTORY_SEPARATOR.'/../temporary'));