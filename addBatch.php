<?php
ob_start();
include('header.php');
include('./includes/MysqliDb.php');
$query="SELECT * FROM vl_request_form where batch_id is NULL OR batch_id=''";
$result = $db->rawQuery($query);
$fQuery="SELECT * FROM facility_details where status='active'";
$fResult = $db->rawQuery($fQuery);
$sQuery="SELECT * FROM r_sample_type";
$sResult = $db->rawQuery($sQuery);
?>
<link href="assets/css/multi-select.css" rel="stylesheet" />
<style>
  #ms-sampleCode{width: 110%;}
</style>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>Create Batch</h1>
      <ol class="breadcrumb">
        <li><a href="index.php"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Batch</li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <!-- SELECT2 EXAMPLE -->
      <div class="box box-default">
        <div class="box-header with-border">
          <div class="pull-right" style="font-size:15px;"><span class="mandatory">*</span> indicates required field &nbsp;</div>
        </div>
	<table class="table" cellpadding="1" cellspacing="3" style="margin-left:1%;margin-top:20px;width: 80%;">
		<tr>
		  <td>&nbsp;<b>Sample Collection Date&nbsp;:</b></td>
		  <td>
		      <input type="text" id="sampleCollectionDate" name="sampleCollectionDate" class="form-control" placeholder="Select Collection Date" readonly style="width:275px;background:#fff;"/>
		    </td>
		    <td>&nbsp;<b>Sample Type&nbsp;:</b></td>
		    <td>
		      <select class="form-control" id="sampleType" name="sampleType" title="Please select sample type">
			<option value="">-- Select --</option>
			  <?php
			  foreach($sResult as $type){
			   ?>
			   <option value="<?php echo $type['sample_id'];?>"><?php echo ucwords($type['sample_name']);?></option>
			   <?php
			  }
			  ?>
			</select>
		    </td>
		</tr>
		<tr>
		   <td>&nbsp;<b>Facility Name & Code&nbsp;:</b></td>
		    <td>
		      <select style="width: 275px;" class="form-control" id="facilityName" name="facilityName" title="Please select facility name">
			  <option value="">-- Select --</option>
			    <?php
			    foreach($fResult as $name){
			     ?>
			     <option value="<?php echo $name['facility_id'];?>"><?php echo ucwords($name['facility_name']."-".$name['facility_code']);?></option>
			     <?php
			    }
			    ?>
			  </select>
		    </td>
		  <td>&nbsp;<input type="button" onclick="getSampleCodeDetails();" value="Search" class="btn btn-success btn-sm">
		    &nbsp;<button class="btn btn-danger btn-sm" onclick="document.location.href = document.location"><span>Reset</span></button>
		    
		    </td>
		</tr>
	    </table>
        <!-- /.box-header -->
        <div class="box-body">
	  
          <!-- form start -->
            <form class="form-horizontal" method='post'  name='addBatchForm' id='addBatchForm' autocomplete="off" action="addBatchCodeHelper.php">
              <div class="box-body">
		<div class="row" id="sampleDetails">
		  <div class="col-md-8">
                    <div class="form-group">
                        <div class="col-md-12">
			  <div class="col-md-12">
			     <div style="width:60%;margin:0 auto;clear:both;">
			      <a href='#' id='select-all-samplecode' style="float:left" class="btn btn-info btn-xs">Select All&nbsp;&nbsp;<i class="icon-chevron-right"></i></a>  <a href='#' id='deselect-all-samplecode' style="float:right" class="btn btn-danger btn-xs"><i class="icon-chevron-left"></i>&nbsp;Deselect All</a>
			      </div><br/><br/>
			    <select id='sampleCode' name="sampleCode[]" multiple='multiple' class="search">
			    <?php
			    foreach($result as $sample){
			      ?>
			      <option value="<?php echo $sample['treament_id'];?>"><?php  echo ucwords($sample['sample_code']);?></option>
			      <?php
			    }
			    ?>
			  </select>
			  </div>
                        </div>
                    </div>
                  </div>
		</div>
		<div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="batchCode" class="col-lg-4 control-label">Enter Batch Code <span class="mandatory">*</span></label>
                        <div class="col-lg-7" style="margin-left:3%;">
                        <input type="text" class="form-control isRequired" id="batchCode" name="batchCode" placeholder="Batch Code" title="Please enter batch code" onblur="checkNameValidation('batch_details','batch_code',this,null,'This batch code already exists.Try another batch code',null)" />
                        </div>
                    </div>
                  </div>
      </div>

               
              </div>
              <!-- /.box-body -->
              <div class="box-footer">
                <a class="btn btn-primary" href="javascript:void(0);" onclick="validateNow();return false;">Submit</a>
                <a href="batchcode.php" class="btn btn-default"> Cancel</a>
              </div>
              <!-- /.box-footer -->
            </form>
          <!-- /.row -->
        </div>
       
      </div>
      <!-- /.box -->

    </section>
    <!-- /.content -->
  </div>
  
  <script src="assets/js/jquery.multi-select.js"></script>
  <script src="assets/js/jquery.quicksearch.js"></script>
  <script type="text/javascript" src="assets/plugins/daterangepicker/moment.min.js"></script>
  <script type="text/javascript" src="assets/plugins/daterangepicker/daterangepicker.js"></script>
  <script type="text/javascript">

  var startDate = "";
  var endDate = "";
  $(document).ready(function() {
     $('#sampleCollectionDate').daterangepicker({
            format: 'DD-MMM-YYYY',
            startDate: moment().subtract('days', 29),
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
     $('#sampleCollectionDate').val("");
  } );
  function validateNow(){
    flag = deforayValidator.init({
        formId: 'addBatchForm'
    });
    
    if(flag){
      document.getElementById('addBatchForm').submit();
    }
  }
   //$("#auditRndNo").multiselect({height: 100,minWidth: 150});
   $(document).ready(function() {
   $('.search').multiSelect({
  selectableHeader: "<input type='text' class='search-input form-control' autocomplete='off' placeholder='Enter Sample Code'>",
  selectionHeader: "<input type='text' class='search-input form-control' autocomplete='off' placeholder='Enter Sample Code'>",
  afterInit: function(ms){
    var that = this,
        $selectableSearch = that.$selectableUl.prev(),
        $selectionSearch = that.$selectionUl.prev(),
        selectableSearchString = '#'+that.$container.attr('id')+' .ms-elem-selectable:not(.ms-selected)',
        selectionSearchString = '#'+that.$container.attr('id')+' .ms-elem-selection.ms-selected';

    that.qs1 = $selectableSearch.quicksearch(selectableSearchString)
    .on('keydown', function(e){
      if (e.which === 40){
        that.$selectableUl.focus();
        return false;
      }
    });

    that.qs2 = $selectionSearch.quicksearch(selectionSearchString)
    .on('keydown', function(e){
      if (e.which == 40){
        that.$selectionUl.focus();
        return false;
      }
    });
  },
  afterSelect: function(){
    this.qs1.cache();
    this.qs2.cache();
  },
  afterDeselect: function(){
    this.qs1.cache();
    this.qs2.cache();
  }
});
   $('#select-all-samplecode').click(function(){
  $('#sampleCode').multiSelect('select_all');
  return false;
});
$('#deselect-all-samplecode').click(function(){
  $('#sampleCode').multiSelect('deselect_all');
  return false;
});
   });
   function checkNameValidation(tableName,fieldName,obj,fnct,alrt,callback)
    {
        var removeDots=obj.value.replace(/\./g,"");
        var removeDots=removeDots.replace(/\,/g,"");
        //str=obj.value;
        removeDots = removeDots.replace(/\s{2,}/g,' ');

        $.post("checkDuplicate.php", { tableName: tableName,fieldName : fieldName ,value : removeDots.trim(),fnct : fnct, format: "html"},
        function(data){
            if(data==='1'){
                alert(alrt);
                duplicateName=false;
                document.getElementById(obj.id).value="";
            }
        });
    }
    function getSampleCodeDetails()
    {
      var fName = $("#facilityName").val();
      var sName = $("#sampleType").val();
      var sCode= $("#sampleCode").val();
      $.post("getSampleCodeDetails.php", { fName : fName,sCode : sCode,sName:sName,sampleCollectionDate:$("#sampleCollectionDate").val()},
      function(data){
	  if(data != ""){
	    $("#sampleDetails").html(data);
	  }
      });
    }
  </script>
  
 <?php
 include('footer.php');
 ?>
