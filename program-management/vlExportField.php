<?php
include('../header.php');
?>
<script type="text/javascript">
    $(document).ready(function() {
        exportInexcel();
    });
function exportInexcel() {
    $.post("vlResultAllFieldExportInExcel.php",
    function(data){
	  if(data == "" || data == null || data == undefined){
	  
	      alert('Unable to generate excel..');
	  }else{
		
	     location.href = '../temporary/'+data;
	  }
    });
  }
</script>