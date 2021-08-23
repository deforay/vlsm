<?php

require_once(dirname(__FILE__) . "/../../startup.php");
ini_set('memory_limit', -1);
ini_set('max_execution_time', -1);
$general = new \Vlsm\Models\General($db);
$app = new \Vlsm\Models\App($db);
$syncTimeOE = $general->getLatestSynDateTime();
$synctime = date('YmdHis', strtotime($syncTimeOE));
if ($synctime >= $_POST['time']) {
    if (isset($syncTimeOE) && $syncTimeOE != '') { ?>
        <small><a href="javascript:forceRemoteSync();" class="text-muted" title="Last synced at : <?php echo $syncTimeOE; ?>">Force Remote sync</a>&nbsp;&nbsp;</small>
        <?php if (isset($_SESSION['privileges']) && in_array("sync-details.php", $_SESSION['privileges'])) { ?>
            <a href="/common/reference/sync-details.php"><small><span style="color:gray;font-size:xx-small;">Last Synced :<?php echo $syncTimeOE; ?></span></small></a>
        <?php } else { ?>
            <small><span style="color:gray;font-size:xx-small;">Last Synced :<?php echo $syncTimeOE; ?></span></small>
<?php }
    }
    http_response_code(200);
} else {
    echo false;
    http_response_code(301);
}
