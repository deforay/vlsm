<?php
$title = "VLSM | Specimen Referral Manifest";
include_once('../startup.php'); include_once(APPLICATION_PATH.'/header.php');
?>
<style>
  .center{
    text-align:center;
  }
</style>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1><i class="fa fa-gears"></i> Specimen Referral Manifest</h1>
      <ol class="breadcrumb">
        <li><a href="/"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Manage Specimen Referral Manifest</li>
      </ol>
    </section>
     <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-xs-12">
          <div class="box">
            <div class="box-header with-border">
              <?php if(isset($_SESSION['privileges']) && in_array("addSpecimenReferralManifest.php", $_SESSION['privileges'])){ ?>
              <a href="addSpecimenReferralManifest.php" class="btn btn-primary pull-right"> <i class="fa fa-plus"></i> Add Specimen Referral Manifest</a>
	            <?php } ?>
	      <!--<button class="btn btn-primary pull-right" style="margin-right: 1%;" onclick="$('#showhide').fadeToggle();return false;"><span>Manage Columns</span></button>-->
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              <table id="specimenReferralManifestDataTable" class="table table-bordered table-striped">
                <thead>
                <tr>
		  <th>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" id="checkPackageData" onclick="checkAllPackageRows(this);"/></th>
                  <th>Manifest Code</th>
                  <th>No. of Samples</th>
                  <th>Added On</th>
		  <?php if(isset($_SESSION['privileges']) && in_array("editSpecimenReferralManifest.php", $_SESSION['privileges'])){ ?>
                  <th>Action</th>
		  <?php } ?>
                </tr>
                </thead>
                <tbody>
                  <tr>
                    <td colspan="5" class="dataTables_empty">Loading data from server</td>
                </tr>
                </tbody>
		<input type="hidden" name="checkedPackages" id="checkedPackages"/>
              </table>
            </div>
            <!-- /.box-body -->
          </div>
          <!-- /.box -->
        </div>
        <!-- /.col -->
      </div>
      <!-- /.row -->
    </section>
    <!-- /.content -->
  </div>
  <script>
  var oTable = null;
  $(function () {
  });
  $(document).ready(function() {
	$.blockUI();
        oTable = $('#specimenReferralManifestDataTable').dataTable({	
            "oLanguage": {
              "sLengthMenu": "_MENU_ records per page"
            },
            "bJQueryUI": false,
            "bAutoWidth": false,
            "bInfo": true,
            "bScrollCollapse": true,
	    "iDisplayLength": 100,
            //"bStateSave" : true,
            "bRetrieve": true,                        
            "aoColumns": [
	        {"sClass":"center","bSortable":false},
                {"sClass":""},
                {"sClass":"center","bSortable":false},
                {"sClass":"center"},
		<?php if(isset($_SESSION['privileges']) && in_array("editSpecimenReferralManifest.php", $_SESSION['privileges'])){ ?>
                {"sClass":"center","bSortable":false},
		<?php } ?>
            ],
            "aaSorting": [[ 1, "asc" ]],
	    "fnDrawCallback": function() {
	      var checkBoxes = document.getElementsByName("chkPackage[]");
              len = checkBoxes.length;
              for(c=0;c<len;c++){
                if (jQuery.inArray(checkBoxes[c].id, selectedPackages) != -1 ){
		    checkBoxes[c].setAttribute("checked",true);
                  }
                }
	    },
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "getSpecimenReferralManifestCodeDetails.php",
            "fnServerData": function ( sSource, aoData, fnCallback ) {
              $.ajax({
                  "dataType": 'json',
                  "type": "POST",
                  "url": sSource,
                  "data": aoData,
                  "success": fnCallback
              });
            }
        });
       $.unblockUI();
	} );
  
  function generateBarcode(pId,frmSrc){
    var ids = $("#checkedPackages").val();
    $.post("generateBarcode.php",{id:pId,ids:ids,frmSrc:frmSrc},
      function(data){
	  if(data == "" || data == null || data == undefined){
	      alert('Unable to generate barcode');
	  }else{
	      window.open('.././uploads/package_barcode/'+data,'_blank');
	  }
	  
      });
  }
  
  selectedPackages = [];
  function checkPackage(obj){
    if ($(obj).is(':checked')) {
      selectedPackages.push(obj.value);
    }else{
      selectedPackages.splice($.inArray(obj.value, selectedPackages), 1 );
    }
    $("#checkedPackages").val(selectedPackages.join());
    if(selectedPackages.length == 0){
      $('#checkPackageData').prop('checked', false);
    }
    $('.selectedRows').html(selectedPackages.length+' Row(s) Selected');
    if(selectedPackages.length > 0){
      $('.printBarcode').show();
    }else{
      $('.printBarcode').hide();
    }
  }
  
  function checkAllPackageRows(obj){
    if ($(obj).is(':checked')) {
      $(".chkPackage").each(function(){
	if($.inArray(this.value, selectedPackages) == -1){
	  $(this).prop('checked', true);
	  selectedPackages.push(this.value);
	}
      });
    }else{
      $(".chkPackage").each(function(){
	$(this).prop('checked', false);
	selectedPackages.splice($.inArray(this.value, selectedPackages), 1 );
      });
    }
    $("#checkedPackages").val(selectedPackages.join());
    $('.selectedRows').html(selectedPackages.length+' Row(s) Selected');
    if(selectedPackages.length > 0){
      $('.printBarcode').show();
    }else{
      $('.printBarcode').hide();
    }
  }
   
  var count_elem = document.getElementById('specimenReferralManifestDataTable');
  var div = document.createElement('div');
  div.innerHTML = '<span class="selectedRows" style="font-weight:bold;">0 Row(s) Selected</span>&nbsp;&nbsp;&nbsp;&nbsp;<a class="btn btn-info btn-xs printBarcode" href="javascript:void(0);" onclick="generateBarcode(\' \',\'pk2\');" style="display:none;margin-bottom: 1vh;"><i class="fa fa-barcode"></i> Print Barcode</a>';
  count_elem.parentNode.insertBefore(div, count_elem);
</script>
 <?php
 include(APPLICATION_PATH.'/footer.php');
 ?>
