<?php
ob_start();
include('../header.php');
include('../includes/General.php');
$general = new Deforay_Commons_General();
$tableName = "vl_request_form";
$showMsg = '';
if(isset($_GET['q']) && $_GET['q']!= ''){
    $showMsg = 'display:none';
    //vl instance id
    $vlInstanceQuery ="SELECT vlsm_instance_id FROM vl_instance";
    $vlInstanceResult = $db->rawQuery($vlInstanceQuery);
    $vlInstanceId = $vlInstanceResult[0]['vlsm_instance_id'];
    //import request/result country
    $formQuery ="SELECT value FROM global_config where name='vl_form'";
    $formResult = $db->rawQuery($formQuery);
    $country = $formResult[0]['value'];
    //get qr content
    $qrVal = explode(',',$_GET['q']);
    if(isset($qrVal[56]) && $qrVal[56]!= '' && $qrVal[56]!= null){
      include('vlRequestRwdForm.php');
    }else{ ?>
        <div class="content-wrapper" style="min-height: 347px;">
            <section class="content-header">
              <blockquote>
                <h3><i class="fa fa-hand-o-right" aria-hidden="true"></i> Sample code needed..</h3>
              </blockquote>
            </section>
        </div>
    <?php }
}
?>
<div class="content-wrapper" style="min-height: 347px;<?php echo $showMsg; ?>">
    <section class="content-header">
      <blockquote>
        <h3><i class="fa fa-hand-o-right" aria-hidden="true"></i> Please connect your QR code scanner with the computer and then scan the QR code image.</h3>
      </blockquote>
    </section>
</div>
<?php
 include('../footer.php');
?>