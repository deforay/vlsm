<?php

define('VERSION', '3.20');


/**********/

defined('UPLOAD_PATH')
    || define('UPLOAD_PATH',realpath(__DIR__.DIRECTORY_SEPARATOR.'/../uploads'));
defined('TEMP_PATH')
    || define('TEMP_PATH',realpath(__DIR__.DIRECTORY_SEPARATOR.'/../temporary'));
