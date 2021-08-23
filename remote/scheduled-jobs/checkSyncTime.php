<?php

require_once(dirname(__FILE__) . "/../../startup.php");
ini_set('memory_limit', -1);
ini_set('max_execution_time', -1);
$general = new \Vlsm\Models\General($db);
$app = new \Vlsm\Models\App($db);
$synctime = date('YmdHis', strtotime($general->getLatestSynDateTime()));
if ($synctime >= $_POST['time']) {
    echo true;
    http_response_code(200);
} else {
    echo false;
    http_response_code(301);
}
