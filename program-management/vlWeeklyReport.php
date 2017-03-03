  <?php
   include('../header.php');
   $facilityQuery="SELECT * FROM facility_details where facility_type = 2 AND status='active'";
   $facilityResult = $db->rawQuery($facilityQuery);
   ?>
   <link href="../assets/css/multi-select.css" rel="stylesheet" />
    <style>
        .ms-container{
          width:100%;
        }
        .select2-selection__choice{
          color:#000000 !important;
        }
    </style>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1><i class="fa fa-signal"></i> VL Weekly Report
      <!--<ol class="breadcrumb">-->
      <!--  <li><a href="index.php"><i class="fa fa-dashboard"></i> Home</a></li>-->
      <!--  <li class="active">Export Result</li>-->
      <!--</ol>-->
      </h1>
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
		      <input type="text" id="sampleCollectionDate" name="sampleCollectionDate" class="form-control" placeholder="Select Collection Date" readonly style="width:220px;background:#eee;"/>
		    </td>
                    <td><b>Lab&nbsp;:</b></td>
		    <td>
		      <select id="lab" name="lab" class="form-control" title="Please select lab" multiple>
                         <option value=""> -- Select -- </option>
                            <?php
                            foreach($facilityResult as $lab){
                             ?>
                               <option value="<?php echo $lab['facility_id'];?>"><?php echo ucwords($lab['facility_name']."-".$lab['facility_code']);?></option>
                             <?php
                            }
                            ?>
                      </select>
		    </td>
                    <td style="width:30%;">&nbsp;<button class="btn btn-danger btn-sm" onclick="document.location.href = document.location"><span>Reset</span></button>
		       &nbsp;<input type="button" onclick="searchData();" value="Search" class="btn btn-success btn-sm">
		       &nbsp;<button class="btn btn-info" type="button" onclick="exportVLWeeklyReport()">Export to excel</button>
		    </td>
                </tr>
            </table>
            <!-- /.box-header -->
            <div class="box-body">
              <table id="vlWeeklyReportDataTable" class="table table-bordered table-striped">
                <thead>
                <tr>
		  <th rowspan="2">Province</th>
		  <th rowspan="2">District</th>
		  <th rowspan="2">Site Name</th>
                  <th rowspan="2">IPSL#</th>
                  <th rowspan="2">No. of Rejections</th>
                  <th colspan="2" style="text-align:center;">Viral Load Results -Peds</th>
                  <th colspan="4" style="text-align:center;">Viral Load Results -Adults</th>
                  <th colspan="2" style="text-align:center;">Viral Load Results- Pregnant/Breastfeeding Women</th>
                  <th colspan="2" style="text-align:center;">Age/Sex Unknown</th>
                  <th colspan="2" style="text-align:center;">Totals</th>
                  <th rowspan="2">Total Test per Facility</th>
                  <th rowspan="2">Comments</th>
                </tr>
		<tr>
		 <th><= 14 yrs <=1000 copies/ml</th>
		 <th><= 14 yrs >1000 copies/ml</th>
		 <th>> 14yrs Male <= 1000 copies/ml</th>
		 <th>> 14yrs Male > 1000 copies/ml</th>
		 <th>> 14yrs Female <= 1000 copies/ml</th>
		 <th>> 14yrs  Female > 1000 copies/ml</th>
		 <th><= 1000 copies/ml</th>
		 <th>> 1000 copies/ml</th>
		 <th>Unknown Age/Sex <= 1000ml</th>
		 <th>Unknown Age/Sex > 1000ml</th>
		 <th><= 1000 copies/ml</th>
		 <th>> 1000 copies/ml</th>
		</tr>
                </thead>
                <tbody>
                  <tr>
                    <td colspan="19" class="dataTables_empty">Loading data from server</td>
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
  <script type="text/javascript" src="../assets/plugins/daterangepicker/moment.min.js"></script>
  <script type="text/javascript" src="../assets/plugins/daterangepicker/daterangepicker.js"></script>
  <script type="text/javascript">
    var startDate = "";
    var endDate = "";
    var oTable = null;
    $(document).ready(function() {
        $('#lab').select2({placeholder:"All Labs"});
        $('#sampleCollectionDate').daterangepicker({
            format: 'DD-MMM-YYYY',
	    separator: ' to ',
            startDate: moment().subtract('days', 6),
            endDate: moment(),
            maxDate: moment(),
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract('days', 1), moment().subtract('days', 1)],
                'Last 7 Days': [moment().subtract('days', 6), moment()],
                'Last 30 Days': [moment().subtract('days', 29), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract('month', 1).startOf('month'), moment().subtract('month', 1).endOf('month')]
            }
        },
        function(start, end) {
            startDate = start.format('YYYY-MM-DD');
            endDate = end.format('YYYY-MM-DD');
       });
       loadDataTable();
    } );
  
   function loadDataTable(){
       oTable = $('#vlWeeklyReportDataTable').dataTable({
        "oLanguage": {
                "sLengthMenu": "_MENU_ records per page"
            },
            "bJQueryUI": false,
            "bAutoWidth": false,
            "bInfo": true,
            "bScrollCollapse": true,
            "iDisplayLength": 10,
            "bRetrieve": true,                        
            "aoColumns": [
                {"sClass":"center"},
                {"sClass":"center"},
                {"sClass":"center"},
                {"sClass":"center","bSortable":false},
                {"sClass":"center","bSortable":false},
                {"sClass":"center","bSortable":false},
                {"sClass":"center","bSortable":false},
                {"sClass":"center","bSortable":false},
                {"sClass":"center","bSortable":false},
                {"sClass":"center","bSortable":false},
                {"sClass":"center","bSortable":false},
                {"sClass":"center","bSortable":false},
                {"sClass":"center","bSortable":false},
                {"sClass":"center","bSortable":false},
                {"sClass":"center","bSortable":false},
                {"sClass":"center","bSortable":false},
                {"sClass":"center","bSortable":false},
                {"sClass":"center","bSortable":false},
                {"sClass":"center"}
            ],
            "aaSorting": [[ 2, "asc" ]],
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "getVlWeeklyReport.php",
            "fnServerData": function ( sSource, aoData, fnCallback ) {
                aoData.push({"name": "sampleCollectionDate", "value": $("#sampleCollectionDate").val()});
                aoData.push({"name": "lab", "value": $("#lab").val()});
              $.ajax({
                  "dataType": 'json',
                  "type": "POST",
                  "url": sSource,
                  "data": aoData,
                  "success": fnCallback
              });
            }
        });
    }
    
    function searchData(){
       $.blockUI();
       oTable.fnDraw();
       $.unblockUI(); 
    }
    
    function exportVLWeeklyReport(){
       $.blockUI();
       $.post("generateVlWeeklyReportExcel.php",{reportedDate:$("#sampleCollectionDate").val(),lab:$("#lab").val(),searchData:$('.dataTables_filter input').val()},
       function(data){
	     $.unblockUI();
	     if(data == "" || data == null || data == undefined){
		 alert('Unable to generate excel..');
	     }else{
	        $.unblockUI();
		location.href = '../temporary/'+data;
	     }
       });
    }
</script>
 <?php
 include('../footer.php');
 ?>