<?php
include_once('../startup.php');
include_once(APPLICATION_PATH . '/header.php');
$tsQuery = "SELECT * FROM r_sample_status";
$tsResult = $db->rawQuery($tsQuery);
$userQuery = "SELECT * FROM user_details where status='active'";
$userResult = $db->rawQuery($userQuery);
$tQuery = "select module, sample_review_by from temp_sample_import where imported_by ='" . $_SESSION['userId'] . "' limit 0,1";

$tResult = $db->rawQueryOne($tQuery);
if (!empty($tResult['sample_review_by'])) {
  $reviewBy = $tResult['sample_review_by'];
} else {
  $reviewBy = $_SESSION['userId'];
}

$module = $tResult['module'];


//global config
$cSampleQuery = "SELECT * FROM global_config";
$cSampleResult = $db->query($cSampleQuery);
$arr = array();
// now we create an associative array so that we can easily create view variables
for ($i = 0; $i < sizeof($cSampleResult); $i++) {
  $arr[$cSampleResult[$i]['name']] = $cSampleResult[$i]['value'];
}


if ($module == 'vl') {

  $rejectionTypeQuery = "SELECT DISTINCT rejection_type FROM r_sample_rejection_reasons WHERE rejection_reason_status ='active'";
  $rejectionTypeResult = $db->rawQuery($rejectionTypeQuery);

  //sample rejection reason
  $rejectionQuery = "SELECT * FROM r_sample_rejection_reasons where rejection_reason_status = 'active'";
  $rejectionResult = $db->rawQuery($rejectionQuery);
  
} else if ($module == 'eid') {

  $rejectionTypeQuery = "SELECT DISTINCT rejection_type FROM r_eid_sample_rejection_reasons WHERE rejection_reason_status ='active'";
  $rejectionTypeResult = $db->rawQuery($rejectionTypeQuery);

  //sample rejection reason
  $rejectionQuery = "SELECT * FROM r_eid_sample_rejection_reasons where rejection_reason_status = 'active'";
  $rejectionResult = $db->rawQuery($rejectionQuery);
}


$rejectionReason = '<option value="">-- Select sample rejection reason --</option>';
foreach ($rejectionTypeResult as $type) {
  $rejectionReason .= '<optgroup label="' . ($type['rejection_type']) . '">';
  foreach ($rejectionResult as $reject) {
    if ($type['rejection_type'] == $reject['rejection_type']) {
      $rejectionReason .= '<option value="' . $reject['rejection_reason_id'] . '">' . ucwords($reject['rejection_reason_name']) . '</option>';
    }
  }
  $rejectionReason .= '</optgroup>';
}

?>
<style>
  .dataTables_wrapper {
    position: relative;
    clear: both;
    overflow-x: visible !important;
    overflow-y: visible !important;
    padding: 15px 0 !important;
  }

  .sampleType select {
    max-width: 100px;
    width: 100px !important
  }

  #rejectReasonDiv {
    border: 1px solid #ecf0f5;
    box-shadow: 3px 3px 15px #000;
    background-color: #ecf0f5;
    width: 50%;
    display: none;
    padding: 10px;
    border-radius: 10px;

  }

  .arrow-right {
    width: 0;
    height: 0;
    border-top: 15px solid transparent;
    border-bottom: 15px solid transparent;
    border-left: 15px solid #ecf0f5;
    position: absolute;
    left: 100%;
    top: 24px;
  }
</style>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>Imported Results</h1>
    <ol class="breadcrumb">
      <li><a href="/"><i class="fa fa-dashboard"></i> Home</a></li>
      <li class="active">Test Request</li>
    </ol>
  </section>
  <!-- for sample rejection -->
  <div id="rejectReasonDiv">
    <a href="javascript:void(0)" style="float:right;color:red;" title="close" onclick="hideReasonDiv('rejectReasonDiv')"><i class="fa fa-close"></i></a>
    <div class="arrow-right"></div>
    <input type="hidden" name="statusDropDownId" id="statusDropDownId" />
    <h3 style="color:red;">Choose Rejection Reason</h3>
    <select name="rejectionReason" id="rejectionReason" class="form-control" title="Please choose reason" onchange="updateRejectionReasonStatus(this);">
      <?php echo $rejectionReason; ?>
    </select>

  </div>
  <!-- Main content -->
  <section class="content">
    <div class="row">
      <div class="col-xs-12">
        <div class="box">
          <div class="box-header with-border">
            <div class="box-header with-border">
              <!--<div class="col-md-4 col-sm-4">
				<select style="" class="form-control" id="status" name="status" title="Please select test status" >
				  <option value="">-- Select --</option>
				  <option value="7">Accepted</option>
				  <option value="1">Hold</option>
				  <option value="4">Rejected</option>
				</select>
				</div>
			  <div class="col-md-2 col-sm-2"><input type="button" onclick="submitTestStatus();" value="Update" class="btn btn-success btn-sm"></div>-->
              <ul style="list-style: none;float: right;">
                <li><i class="fa fa-square" aria-hidden="true" style="color:#e8000b;"></i> - Unknown Sample</li>
                <li><i class="fa fa-square" aria-hidden="true" style="color:#86c0c8;"></i> - Existing Result</li>
                <li><i class="fa fa-square" aria-hidden="true" style="color:#337ab7;"></i> - Result for Sample</li>
                <li><i class="fa fa-square" aria-hidden="true" style="color:#7d8388;"></i> - Control</li>
              </ul>
            </div>
            <span><b style="color: #f03033;">Note:-</b>When you leave from this page, these temporary records will be deleted from the system.</span>
          </div>
          <!-- /.box-header -->
          <div class="box-body">
            <div class="col-md-2 col-sm-2"><input type="button" onclick="acceptAllSamples();" value="Accept All Samples" class="btn btn-success btn-sm"></div>
            <table id="vlRequestDataTable" class="table table-bordered table-striped">
              <thead>
                <tr>
                  <!--<th style="width: 1%;"><input type="checkbox" id="checkTestsData" onclick="toggleAllVisible()"/></th>-->
                  <th style="width: 23%;">Form Serial No.</th>
                  <th style="width: 11%;">Sample Collection Date</th>
                  <th style="width: 10%;">Sample Test Date</th>
                  <th style="width: 10%;">Clinic Name</th>
                  <th style="width: 10%;">Batch Code</th>
                  <th style="width: 10%;">Lot No.</th>
                  <th style="width: 10%;">Lot Expiry Date</th>
                  <th style="width: 10%;">Reason</th>
                  <th style="max-width: 9%;">Sample Type</th>
                  <th style="width: 9%;">Result</th>
                  <th style="width: 9%;">Status</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td colspan="11" class="dataTables_empty">Loading data from server</td>
                </tr>
              </tbody>
            </table>
          </div>
          <table class="table" cellpadding="1" cellspacing="3" style="margin-left:1%;margin-top:30px;width: 75%;">
            <tr>
              <input type="hidden" name="checkedTests" id="checkedTests" />
              <input type="hidden" name="checkedTestsIdValue" id="checkedTestsIdValue" />
              <td>
                <b>Comments&nbsp;</b>
                <textarea style="height: 34px;" class="form-control" id="comments" name="comments" placeholder="Comments"></textarea>
              </td>
              <td>
                <b>Reviewed By&nbsp;</b>
                <!--<input type="text" name="reviewedBy" id="reviewedBy" class="form-control" title="Please enter Reviewed By" placeholder ="Reviewed By"/>-->
                <select name="reviewedBy" id="reviewedBy" class="form-control" title="Please choose reviewed by">
                  <option value="">-- Select --</option>
                  <?php
                  foreach ($userResult as $uName) {
                    ?>
                    <option value="<?php echo $uName['user_id']; ?>" <?php echo ($uName['user_id'] == $reviewBy) ? "selected=selected" : ""; ?>><?php echo ucwords($uName['user_name']); ?></option>
                  <?php
                }
                ?>
                </select>
              </td>
              <td>
                <b>Approved By&nbsp;</b>
                <!--<input type="text" name="approvedBy" id="approvedBy" class="form-control" title="Please enter Approved By" placeholder ="Approved By"/>-->
                <select name="approvedBy" id="approvedBy" class="form-control" title="Please choose approved by">
                  <option value="">-- Select --</option>
                  <?php
                  foreach ($userResult as $uName) {
                    ?>
                    <option value="<?php echo $uName['user_id']; ?>" <?php echo ($uName['user_id'] == $_SESSION['userId']) ? "selected=selected" : ""; ?>><?php echo ucwords($uName['user_name']); ?></option>
                  <?php
                }
                ?>
                </select>
              </td>
              <td>
                <br>
                <input type="hidden" name="print" id="print" />
                <input type="hidden" name="module" id="module" value="<?php echo $module; ?>" />
                <input type="button" onclick="submitTestStatus();" value="Save" class="btn btn-success btn-sm"></td>
            </tr>

          </table>
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

<script type="text/javascript">
  var startDate = "";
  var endDate = "";
  var selectedTests = [];
  var selectedTestsIdValue = [];
  $(document).ready(function() {
    loadVlRequestData();
  });

  var oTable = null;

  function loadVlRequestData() {
    $.blockUI();
    oTable = $('#vlRequestDataTable').dataTable({
      "oLanguage": {
        "sLengthMenu": "_MENU_ records per page"
      },
      "bJQueryUI": false,
      "bAutoWidth": false,
      "bInfo": true,
      "bScrollCollapse": true,
      "bRetrieve": true,
      "aoColumns": [{
          "sClass": "center",
          "bSortable": false
        },
        {
          "sClass": "center",
          "bSortable": false
        },
        {
          "sClass": "center",
          "bSortable": false
        },
        {
          "sClass": "center",
          "bSortable": false
        },
        {
          "sClass": "center",
          "bSortable": false
        },
        {
          "sClass": "center",
          "bSortable": false
        },
        {
          "sClass": "center",
          "bSortable": false
        },
        {
          "sClass": "center",
          "bSortable": false
        },
        {
          "sClass": "center sampleType",
          "bSortable": false
        },
        {
          "sClass": "center",
          "bSortable": false
        },
        {
          "sClass": "center sampleType",
          "bSortable": false
        },
      ],
      "iDisplayLength": 100,
      //"aaSorting": [[ 1, "desc" ]],
      "fnDrawCallback": function() {
        //		var checkBoxes=document.getElementsByName("chk[]");
        //                len = checkBoxes.length;
        //                for(c=0;c<len;c++){
        //                    if (jQuery.inArray(checkBoxes[c].id, selectedTestsId) != -1 ){
        //			checkBoxes[c].setAttribute("checked",true);
        //                    }
        //                }
        var oSettings = this.fnSettings();
        var iTotalRecords = oSettings.fnRecordsTotal();
        if (iTotalRecords == 0) {
          window.location.href = "importedStatistics.php";
        }
      },
      "bProcessing": true,
      "bServerSide": true,
      "sAjaxSource": "getImportedResults.php",
      "fnServerData": function(sSource, aoData, fnCallback) {
        $.ajax({
          "dataType": 'json',
          "type": "POST",
          "url": sSource,
          "data": aoData,
          "success": fnCallback,
        });
      },
    });
    $.unblockUI();
  }

  function toggleTest(obj, sampleCode) {
    if (sampleCode == '') {
      alert("Please enter sample code");
      $("#" + obj.id).val('');
      return false;
    }
    if (obj.value == '4') {
      var confrm = confirm("Do you wish to overwrite this result?");
      if (confrm) {
        var pos = $("#" + obj.id).offset();
        $("#rejectReasonDiv").show();
        $("#rejectReasonDiv").css({
          top: Math.round(pos.top) - 30,
          position: 'absolute',
          'z-index': 1,
          right: '15%'
        });
        $("#statusDropDownId").val(obj.id);
        $(".content").css('pointer-events', 'none');
        //return false;
      } else {
        $("#" + obj.id).val('');
        return false;
      }
    } else {
      $("#rejectReasonName" + obj.id).html('');
      $("#rejectReasonDiv").hide();
    }

    // var dValue = obj.value;
    // var dId = obj.id;
    // if($.inArray(obj.id, selectedTests) == -1){
    //   selectedTests.push(obj.id);
    //   selectedTestsIdValue.push(obj.value);
    // }else{
    //   var indexValue = selectedTests.indexOf(obj.id);
    //   selectedTestsIdValue[indexValue]=obj.value;  
    // }
    // $("#checkedTests").val(selectedTests.join());
    // $("#checkedTestsIdValue").val(selectedTestsIdValue.join());
  }

  function updateRejectionReasonStatus(obj) {
    var rejectDropDown = $("#statusDropDownId").val();
    //var indexValue = selectedTests.indexOf(rejectDropDown);
    if (obj.value != '') {
      //var result = {statusId:selectedTestsIdValue[indexValue],reasonId:obj.value};
      //selectedTestsIdValue[indexValue] = result;
      $("#rejectReasonName" + rejectDropDown).html(
        $("#" + obj.id + " option:selected").text() +
        '<input type="hidden" id="rejectedReasonId' + rejectDropDown + '" name="rejectedReasonId[]" value="' + obj.value + '"/><a href="javascript:void(0)" style="float:right;color:red;" title="cancel" onclick="showRejectedReasonList(' + rejectDropDown + ');"><i class="fa fa-close"></i></a>'
      );
    } else {
      $("#rejectedReasonId" + rejectDropDown).val('');
      //selectedTestsIdValue[indexValue] = $("#"+$("#statusDropDownId").val()).val();
    }
    //$("#checkedTests").val(selectedTests.join());
    //$("#checkedTestsIdValue").val(selectedTestsIdValue.join());
  }

  function showRejectedReasonList(postionId) {
    var pos = $("#" + postionId).offset();
    $("#rejectReasonDiv").show();
    $("#rejectReasonDiv").css({
      top: Math.round(pos.top) - 30,
      position: 'absolute',
      'z-index': 1,
      right: '15%'
    });
    $("#statusDropDownId").val(postionId);
    $(".content").css('pointer-events', 'none');
  }


  function submitTestStatus() {

    var idArray = [];
    var statusArray = [];
    var rejectReasonArray = [];
    var somethingmissing = false;

    $('[name="status[]"]').each(function() {

      if ($(this).val() == null || $(this).val() == '') {
        somethingmissing = true;
      }

      idArray.push($(this).attr('id'));
      statusArray.push($(this).val());
      rejectReasonArray.push($("#rejectedReasonId" + $(this).attr('id')).val());




    });

    id = idArray.join();
    status = statusArray.join();
    rejectReasonId = rejectReasonArray.join();
    comments = $("#comments").val();
    appBy = $("#approvedBy").val();
    reviewedBy = $("#reviewedBy").val();
    moduleName = $("#module").val();
    globalValue = '<?php echo $arr["user_review_approve"]; ?>';
    if (appBy == reviewedBy && (reviewedBy != '' && appBy != '') && globalValue == 'yes') {
      conf = confirm("Same person is reviewing and approving result!");
      if (conf) {} else {
        return false;
      }
    } else if (appBy == reviewedBy && (reviewedBy != '' && appBy != '') && globalValue == 'no') {
      alert("Same person is reviewing and approving result!");
      return false;
    }

    //alert(somethingmissing);return false;

    if (somethingmissing == true) {
      alert("Please ensure that you have updated the status of all the Controls and Samples");
      $.unblockUI();
      return false;
    }

    if (appBy != '' && somethingmissing == false) {
      conf = confirm("Are you sure you want to continue ?");
      if (conf) {
        $.blockUI();
        $.post("processImportedResults.php", {
            rejectReasonId: rejectReasonId,
            value: id,
            status: status,
            comments: comments,
            appBy: appBy,
            module: moduleName,
            reviewedBy: reviewedBy,
            format: "html"
          },
          function(data) {
            if ($("#print").val() == 'print') {
              convertSearchResultToPdf('');
            }
            if (data == 'importedStatistics.php') {
              window.location.href = "importedStatistics.php";
            }
            oTable.fnDraw();
            selectedTests = [];
            selectedTestsIdValue = [];
            $("#checkedTests").val('');
            $("#checkedTestsIdValue").val('');
            $("#comments").val('');
          });
        //$.unblockUI();
      } else {
        oTable.fnDraw();
      }
    } else {
      alert("Please ensure you have updated the status and the approved by field");
      return false;
    }
  }

  function submitTestStatusAndPrint() {
    $("#print").val('print');
    submitTestStatus();
  }

  function updateStatus(value, status) {
    if (status != '') {
      conf = confirm("Do you wish to change the status ?");
      if (conf) {
        $.blockUI();
        $.post("processImportedResults.php", {
            value: value,
            status: status,
            format: "html"
          },
          function(data) {
            convertSearchResultToPdf('');
            oTable.fnDraw();
            selectedTests = [];
            selectedTestsId = [];
            $("#checkedTests").val('');
            $(".countChecksPending").html(0);
          });
        $.unblockUI();
      } else {
        oTable.fnDraw();
      }
    } else {
      alert("Please select the status.");
    }
  }

  function updateSampleCode(obj, oldSampleCode, tempsampleId) {
    $(obj).fastConfirm({
      position: "right",
      questionText: "Are you sure you want to rename this Sample?",
      onProceed: function(trigger) {
        var pos = oTable.fnGetPosition(obj);
        $.blockUI();
        $.post("updateImportedSample.php", {
            sampleCode: obj.value,
            tempsampleId: tempsampleId
          },
          function(data) {
            if (data == 0) {
              alert("Something went wrong!Please try again");
              oTable.fnDraw();
            }
          });
        $.unblockUI();
      },
      onCancel: function(trigger) {
        $("#" + obj.id).val(oldSampleCode);
      }
    });
  }

  function updateBatchCode(obj, oldBatchCode, tempsampleId) {
    $(obj).fastConfirm({
      position: "right",
      questionText: "Are you sure you want to rename this Batch?",
      onProceed: function(trigger) {
        var pos = oTable.fnGetPosition(obj);
        $.blockUI();
        $.post("updateImportedSample.php", {
            batchCode: obj.value,
            tempsampleId: tempsampleId
          },
          function(data) {
            if (data == 0) {
              alert("Something went wrong! Please try again");
              oTable.fnDraw();
            }
          });
        $.unblockUI();
      },
      onCancel: function(trigger) {
        $("#" + obj.id).val(oldBatchCode);
      }
    });
  }

  function sampleToControl(obj, oldValue, tempsampleId) {
    $(obj).fastConfirm({
      position: "left",
      questionText: "Are you sure you want to change this Sample?",
      onProceed: function(trigger) {
        var pos = oTable.fnGetPosition(obj);
        $.blockUI();
        $.post("updateImportedSample.php", {
            sampleType: obj.value,
            tempsampleId: tempsampleId
          },
          function(data) {
            if (data == 0) {
              alert("Something went wrong! Please try again");
              oTable.fnDraw();
            }
          });
        $.unblockUI();
      },
      onCancel: function(trigger) {
        $("#" + obj.id).val(oldValue);
      }
    });
  }

  function sampleToControlAlert(number) {
    alert("Max number of controls as per the config is " + number);
    oTable.fnDraw();
  }

  function hideReasonDiv(id) {
    $("#" + id).hide();
    $(".content").css('pointer-events', 'auto');
    if ($("#rejectionReason").val() == '') {
      $("#" + $("#statusDropDownId").val()).val('');
    }
  }

  function acceptAllSamples() {
    conf = confirm("Are you sure you want to mark all samples as 'Accepted' ?");
    if (conf) {
      $.blockUI();
      $.post("updateAllSampleStatus.php", {},
        function(data) {
          oTable.fnDraw();
        });
      $.unblockUI();
    } else {
      oTable.fnDraw();
    }
  }
</script>
<?php
include(APPLICATION_PATH . '/footer.php');
?>