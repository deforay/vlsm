<?php
// include_once(APPLICATION_PATH . '/header.php');


?>
<script type="text/javascript" src="/assets/js/jquery.min.js"></script>


<script type="text/javascript">
   
    $(document).ready(function() {
        convertSearchResultToPdf(<?php  echo base64_decode($_GET['id']);?>);
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
