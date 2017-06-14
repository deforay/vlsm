<?php
ob_start();
include('../header.php');
?>
<div class="content-wrapper" style="min-height: 347px;">
    <section class="content-header">
      <blockquote>
        <h3><i class="fa fa-hand-o-right" aria-hidden="true"></i> Please connect your QR code scanner with the computer and then scan the QR code image.</h3>
      </blockquote>
      <textarea class="form-control" id="qrText" name="qrText" placeholder="Please place the cursor here before start reading QR code" style="width:100%;min-height:100px;max-height:200px;box-shadow: 0 0 10px rgba(0,0,0,0.6);"></textarea>
    </section>
</div>
<script>
    $(document).ready(function() {
       $('#qrText').focus();
    });
    
    var timer = null;
    $('#qrText').keydown(function(){
        if($('#qrText').val() !=''){
            clearTimeout(timer); 
            timer = setTimeout(setRedirect, 1000);
        }
    });
    
    function setRedirect() {
        window.open(
          '/qr-code/vlRequestRwdForm.php?q='+$('#qrText').val(),
          '_self'
        );
    }
</script>
<?php
 include('../footer.php');
?>