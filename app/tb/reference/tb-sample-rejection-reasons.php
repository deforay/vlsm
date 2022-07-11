<?php
$title = _("TB Sample Rejection Reasons");
 
require_once(APPLICATION_PATH . '/header.php');

// if($sarr['sc_user_type']=='vluser'){
//   include('../remote/pullDataFromRemote.php');
// }
?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1><i class="fa-solid fa-heart-pulse"></i> <?php echo _("TB Sample Rejection Reasons");?></h1>
    <ol class="breadcrumb">
      <li><a href="/"><i class="fa-solid fa-chart-pie"></i> <?php echo _("Home");?></a></li>
      <li class="active"><?php echo _("TB Sample Rejection Reasons");?></li>
    </ol>
  </section>

  <!-- Main content -->
  <section class="content">
    <div class="row">
      <div class="col-xs-12">
        <div class="box">
          <div class="box-header with-border">
            <?php if (isset($_SESSION['privileges']) && in_array("tb-sample-type.php", $_SESSION['privileges']) && $sarr['sc_user_type'] != 'vluser') { ?>
              <a href="add-tb-sample-rejection-reason.php" class="btn btn-primary pull-right"> <i class="fa-solid fa-plus"></i> <?php echo _("Add TB Sample Rejection Reasons");?></a>
            <?php } ?>
            <!--<button class="btn btn-primary pull-right" style="margin-right: 1%;" onclick="$('#showhide').fadeToggle();return false;"><span>Manage Columns</span></button>-->
          </div>
          <!-- /.box-header -->
          <div class="box-body">
            <table id="samRejReasonDataTable" class="table table-bordered table-striped">
              <thead>
                <tr>
                  <th><?php echo _("Rejection Reason");?></th>
                  <th><?php echo _("Rejection Reason Type");?></th>
                  <th><?php echo _("Rejection Reason Code");?></th>
                  <th><?php echo _("Rejection Reason Status");?></th>
                  <?php if (isset($_SESSION['privileges']) && in_array("tb-sample-type.php", $_SESSION['privileges']) && $sarr['sc_user_type'] != 'vluser') { ?>
                    <!-- <th>Action</th> -->
                  <?php } ?>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td colspan="6" class="dataTables_empty"><?php echo _("Loading data from server");?></td>
                </tr>
              </tbody>

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
  $(function() {
    //$("#example1").DataTable();
  });
  $(document).ready(function() {
    $.blockUI();
    oTable = $('#samRejReasonDataTable').dataTable({
      "oLanguage": {
        "sLengthMenu": "_MENU_ records per page"
      },
      "bJQueryUI": false,
      "bAutoWidth": false,
      "bInfo": true,
      "bScrollCollapse": true,
      "bStateSave": true,
      "bRetrieve": true,
      "aoColumns": [{
          "sClass": "center"
        },
        {
          "sClass": "center"
        },
        {
          "sClass": "center"
        },
        {
          "sClass": "center"
        },
      ],
      "aaSorting": [
        [0, "asc"]
      ],
      "bProcessing": true,
      "bServerSide": true,
      "sAjaxSource": "getTbSampleRejectionDetails.php",
      "fnServerData": function(sSource, aoData, fnCallback) {
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
  });
  function updateStatus(obj, optVal) {
    if (obj.value != '') {
      conf = confirm("<?php echo _("Are you sure you want to change the status?");?>");
      if (conf) {
        $.post("update-tb-rejection-status.php", {
            status: obj.value,
            id: obj.id
          },
          function(data) {
            if (data != "") {
              oTable.fnDraw();
              alert("<?php echo _("Updated successfully");?>.");
            }
          });
      }
	  else {
		window.top.location = window.top.location;
	  }
    }
  }
</script>
<?php
require_once(APPLICATION_PATH . '/footer.php');
?>