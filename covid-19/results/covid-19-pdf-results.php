<?php
$ciphering = "AES-128-CTR";
$iv_length = openssl_cipher_iv_length($ciphering);
$encryption = $_GET['id'];
$options = 0;
$decryption_iv = $systemConfig['tryCrypt'];
$decryption_key = $systemConfig['tryCrypt'];
$decryption=openssl_decrypt ($encryption, $ciphering, 
$decryption_key, $options, $decryption_iv);
$data = explode('&&&', $decryption);
if($data[1]!="qr")
    include_once(APPLICATION_PATH . '/header.php');
$id = $data[0]; 
?>
<script type="text/javascript" src="/assets/js/jquery.min.js"></script>


<script type="text/javascript">
   
    $(document).ready(function() {
        
        convertSearchResultToPdf(<?php  echo ($id);?>);
    });

    function convertSearchResultToPdf(id) {

        $.blockUI();
        <?php
        $path = '';
        $path = '/covid-19/results/generate-result-pdf.php';
        ?>
        
        $.post("<?php echo $path; ?>", {
                source: 'print',
                id: id
            },
            function(data) {
                if (data == "" || data == null || data == undefined) {
                    $.unblockUI();
                    alert('Unable to generate download');
                } else {
                    $.unblockUI();
                   
                    window.open('/uploads/' + data,"_self");
                }
            });
    }

   
</script>
<?php
include(APPLICATION_PATH . '/footer.php');
