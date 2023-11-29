<?php

use App\Utilities\DateUtility;
use App\Services\CommonService;
use App\Registries\ContainerRegistry;

// Sanitized values from $request object
/** @var Laminas\Diactoros\ServerRequest $request */
$request = $GLOBALS['request'];
$_GET = $request->getQueryParams();

/** @var CommonService $general */
$general = ContainerRegistry::get(CommonService::class);
$date = DateUtility::isoDateFormat($_GET['date']);
$file = ROOT_PATH . '/logs/' . $date . '-logfile.log';

if (file_exists($file)) {
    echo file_get_contents($file);
} else {
    echo 'No files found';
}
