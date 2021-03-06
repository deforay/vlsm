<?php
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}
$title = "Hepatitis | Sample Status Report";
#require_once('../../startup.php');
include_once(APPLICATION_PATH . '/header.php');

$general = new \Vlsm\Models\General($db); // passing $db which is coming from startup.php

$facilitiesDb = new \Vlsm\Models\Facilities($db);
$healthFacilites = $facilitiesDb->getHealthFacilities('hepatitis');
$facilitiesDropdown = $general->generateSelectOptions($healthFacilites, null, "-- Select --");

// $tsQuery = "SELECT * FROM r_sample_status";
// $tsResult = $db->rawQuery($tsQuery);
// $configFormQuery = "SELECT * FROM global_config WHERE name ='vl_form'";
// $configFormResult = $db->rawQuery($configFormQuery);


$batQuery = "SELECT batch_code FROM batch_details WHERE test_type='hepatitis' AND batch_status='completed'";
$batResult = $db->rawQuery($batQuery);
?>
<style>
  .select2-selection__choice {
    color: black !important;
  }
</style>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1><i class="fa fa-book"></i> Hepatitis Sample Status Report</h1>
    <ol class="breadcrumb">
      <!-- <li><a href="/"><i class="fa fa-dashboard"></i> Home</a></li> -->
      <li><i class="fa fa-book"></i> Hepatitis</li>
      <li>Management</li>
      <li class="active">Hepatitis Sample Status</li>
    </ol>
  </section>

  <!-- Main content -->
  <section class="content">
    <div class="row">
      <div class="col-xs-12">
        <div class="box">
          <table class="table" cellpadding="1" cellspacing="3" style="margin-left:1%;margin-top:20px;width:98%;">
            <tr>
              <td><b>Sample Collection Date&nbsp;:</b></td>
              <td>
                <input type="text" id="sampleCollectionDate" name="sampleCollectionDate" class="form-control" placeholder="Select Collection Date" readonly style="width:220px;background:#fff;" />
              </td>
              <td>&nbsp;<b>Batch Code&nbsp;:</b></td>
              <td>
                <select class="form-control" id="batchCode" name="batchCode" title="Please select batch code" style="width:220px;">
                  <option value=""> -- Select -- </option>
                  <?php
                  foreach ($batResult as $code) {
                  ?>
                    <option value="<?php echo $code['batch_code']; ?>"><?php echo $code['batch_code']; ?></option>
                  <?php
                  }
                  ?>
                </select>
              </td>
            </tr>
            <tr>


              <td>&nbsp;<b>Facility Name &nbsp;:</b></td>
              <td>
                <select class="form-control" id="facilityName" name="facilityName" title="Please select facility name" multiple="multiple" style="width:220px;">
                  <?= $facilitiesDropdown; ?>
                </select>
              </td>

              <td></td>
              <td></td>


            </tr>
            <tr>
              <td colspan="4">&nbsp;<input type="button" onclick="searchResultData(),searchVlTATData();" value="Search" class="btn btn-success btn-sm">
                &nbsp;<button class="btn btn-danger btn-sm" onclick="document.location.href = document.location"><span>Reset</span></button>
              </td>
            </tr>

          </table>
        </div>
      </div>

      <!-- /.box-header -->
      <div id="pieChartDiv">

      </div>
      <div class="col-xs-12">
        <div class="box">
          <div class="box-body">
            <button class="btn btn-success pull-right" type="button" onclick="hepatitisExportTAT()"><i class="fa fa-cloud-download" aria-hidden="true"></i> Export to excel</button>
            <table id="hepatitisRequestDataTable" class="table table-bordered table-striped">
              <thead>
                <tr>
                  <th>Hepatitis Sample ID</th>
                  <th>Sample Collection Date</th>
                  <th>Sample Received Date in Lab</th>
                  <th>Sample Test Date</th>
                  <th>Sample Print Date</th>
                  <th>Sample Email Date</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td colspan="6" class="dataTables_empty">Loading data from server</td>
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
<script type="text/javascript" src="/assets/plugins/daterangepicker/moment.min.js"></script>
<script type="text/javascript" src="/assets/plugins/daterangepicker/daterangepicker.js"></script>
<script src="/assets/js/highcharts.js"></script>
<script src="/assets/js/highchart-exporting.js"></script>
<script>
  $(function() {
    $("#facilityName").select2({
      placeholder: "Select Facilities"
    });
    $('#sampleCollectionDate').daterangepicker({
        locale: {
          cancelLabel: 'Clear'
        },
        format: 'DD-MMM-YYYY',
        separator: ' to ',
        startDate: moment().subtract(29, 'days'),
        endDate: moment(),
        maxDate: moment(),
        ranges: {
          'Today': [moment(), moment()],
          'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
          'Last 7 Days': [moment().subtract(6, 'days'), moment()],
          'Last 30 Days': [moment().subtract(29, 'days'), moment()],
          'This Month': [moment().startOf('month'), moment().endOf('month')],
          'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        }
      },
      function(start, end) {
        startDate = start.format('YYYY-MM-DD');
        endDate = end.format('YYYY-MM-DD');
      });
    $('#sampleCollectionDate').val("");
    searchResultData();
    loadVlTATData();

  });

  function searchResultData() {
    $.blockUI();
    $.post("/hepatitis/management/get-sample-status.php", {
        sampleCollectionDate: $("#sampleCollectionDate").val(),
        batchCode: $("#batchCode").val(),
        facilityName: $("#facilityName").val(),
        sampleType: $("#sampleType").val()
      },
      function(data) {
        if (data != '') {
          $("#pieChartDiv").html(data);
        }
      });
    $.unblockUI();
  }

  function searchVlTATData() {
    $.blockUI();
    oTable.fnDraw();
    $.unblockUI();
  }

  function loadVlTATData() {
    $.blockUI();
    oTable = $('#hepatitisRequestDataTable').dataTable({
      "oLanguage": {
        "sLengthMenu": "_MENU_ records per page"
      },
      "bJQueryUI": false,
      "bAutoWidth": false,
      "bInfo": true,
      "bScrollCollapse": true,
      //"bStateSave" : true,
      "iDisplayLength": 10,
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
      "sAjaxSource": "/hepatitis/management/get-hepatitis-sample-tat-details.php",
      "fnServerData": function(sSource, aoData, fnCallback) {
        aoData.push({
          "name": "batchCode",
          "value": $("#batchCode").val()
        });
        aoData.push({
          "name": "sampleCollectionDate",
          "value": $("#sampleCollectionDate").val()
        });
        aoData.push({
          "name": "facilityName",
          "value": $("#facilityName").val()
        });
        aoData.push({
          "name": "sampleType",
          "value": $("#sampleType").val()
        });
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
  }

  function hepatitisExportTAT() {
    $.blockUI();
    oTable.fnDraw();
    $.post("/hepatitis/management/hepatitis-export-tat.php", {
        Sample_Collection_Date: $("#sampleCollectionDate").val(),
        Batch_Code: $("#batchCode  option:selected").text(),
        Sample_Type: $("#sampleType  option:selected").text(),
        Facility_Name: $("#facilityName  option:selected").text()
      },
      function(data) {
        if (data == "" || data == null || data == undefined) {
          $.unblockUI();
          alert('Unable to generate excel..');
        } else {
          $.unblockUI();
          location.href = '/temporary/' + data;
        }
      });

  }
</script>
<?php
include(APPLICATION_PATH . '/footer.php');
?>