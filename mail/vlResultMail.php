<?php
ob_start();
include('../header.php');
//include('../includes/MysqliDb.php');
$configQuery="SELECT * FROM global_config WHERE name ='vl_form'";
$configResult = $db->rawQuery($configQuery);
$formId = 0;
if(isset($configResult[0]['value']) && trim($configResult[0]['value'])!= ''){
  $formId = intval($configResult[0]['value']);
}
$query="SELECT vl.sample_code,vl.vl_sample_id,vl.facility_id,f.facility_name,f.facility_code FROM vl_request_form as vl LEFT JOIN facility_details as f ON vl.facility_id=f.facility_id where form_id = $formId AND result_mail_sent ='no' ORDER BY f.facility_name ASC";
$result = $db->rawQuery($query);
$sTypeQuery="SELECT * FROM r_sample_type where status='active'";
$sTypeResult = $db->rawQuery($sTypeQuery);
$facilityQuery="SELECT * FROM facility_details where status='active'";
$facilityResult = $db->rawQuery($facilityQuery);
//Get batches
$batchQuery="SELECT * FROM batch_details";
$batchResult = $db->rawQuery($batchQuery);
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
      <h1 class="fa fa-envelope"> E-mail Test Result</h1>
      <ol class="breadcrumb">
        <li><a href="index.php"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">E-mail Test Result</li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <!-- SELECT2 EXAMPLE -->
      <div class="box box-default">
        <div class="box-header with-border">
          <div class="pull-right" style="font-size:15px;"><span class="mandatory">*</span> indicates required field &nbsp;</div>
        </div>
        <!-- /.box-header -->
        <div class="box-body">
          <!-- form start -->
            <form class="form-horizontal" method="post" name="mailForm" id="mailForm" autocomplete="off" action="vlResultMailHelper.php">
              <div class="box-body">
                <div class="row">
                    <div class="col-md-9">
                    <div class="form-group">
                        <label for="subject" class="col-lg-3 control-label">Subject <span class="mandatory">*</span></label>
                        <div class="col-lg-9">
                           <input type="text" id="subject" name="subject" class="form-control isRequired" placeholder="Subject" title="Please enter subject"/>
                        </div>
                    </div>
                  </div>
                </div>
		<div class="row">
                    <div class="col-md-9">
                    <div class="form-group">
                        <label for="facility" class="col-lg-3 control-label">Facility <span class="mandatory">*</span></label>
                        <div class="col-lg-9">
                          <select class="form-control isRequired" id="facility" name="facility" title="Please select facility">
			    <option value=""> -- Select -- </option>
			    <?php
			    foreach($facilityResult as $facility){ ?>
			    ?>
			      <option data-email="<?php echo $facility['email']; ?>" data-report-email="<?php echo $facility['report_email']; ?>" value="<?php echo base64_encode($facility['facility_id']); ?>"><?php echo ucwords($facility['facility_name']); ?></option>
			    <?php } ?>
			  </select>
                        </div>
                    </div>
                  </div>
                </div>
		<div class="row">
                    <div class="col-md-12 emailSection" style="text-align:center;margin-bottom:10px;"></div>
		</div>
                <div class="row">
                    <div class="col-md-9">
                    <div class="form-group">
                        <label for="message" class="col-lg-3 control-label">Message <span class="mandatory">*</span></label>
                        <div class="col-lg-9">
                           <textarea id="message" name="message" class="form-control isRequired" placeholder="Message" title="Please enter message"></textarea>
                        </div>
                    </div>
                  </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <table class="table" cellpadding="1" cellspacing="3" style="margin-left:1%;margin-top:20px;width:90%;">
                            <tr>
                                <td>&nbsp;<b>Sample Collection Date&nbsp;:</b></td>
                                <td>
                                        <input type="text" id="sampleCollectionDate" name="sampleCollectionDate" class="form-control" placeholder="Select Collection Date" readonly style="width:275px;background:#fff;"/>
                                    </td>
                                    <td>&nbsp;<b>Sample Type&nbsp;:</b></td>
                                    <td>
                                        <select class="form-control" id="sampleType" name="sampleType" title="Please select sample type">
                                           <option value=""> -- Select -- </option>
                                            <?php
                                            foreach($sTypeResult as $type){
                                             ?>
                                             <option value="<?php echo $type['sample_id'];?>"><?php echo ucwords($type['sample_name']);?></option>
                                             <?php
                                            }
                                            ?>
                                        </select>
                                    </td>
                            </tr>
                            <tr>
                                 <td>&nbsp;<b>Facility&nbsp;:</b></td>
                                    <td>
                                        <select style="width: 275px;" class="form-control" id="facilityName" name="facilityName" title="Please select facility name"  multiple="multiple">
                                        <?php
                                        foreach($facilityResult as $name){
                                         ?>
                                           <option value="<?php echo $name['facility_id'];?>"><?php echo ucwords($name['facility_name']."-".$name['facility_code']);?></option>
                                         <?php
                                        }
                                        ?>
                                        </select>
                                    </td>
                                    <td><b>Gender&nbsp;:</b></td>
                                    <td>
                                        <select name="gender" id="gender" class="form-control" title="Please choose gender" onchange="enablePregnant(this);">
                                            <option value=""> -- Select -- </option>
                                            <option value="male">Male</option>
                                            <option value="female">Female</option>
                                            <option value="not_recorded">Not Recorded</option>
                                        </select>
                                    </td>
                            </tr>
                            <tr>
                                <td><b class="showPregnant">Pregnant&nbsp;:</b></td>
                                <td>
                                    <input type="radio" name="pregnant" title="Please choose type" class="pregnant showPregnant" id="prgYes" value="yes" disabled="disabled"/>&nbsp;&nbsp;Yes
                                    <input type="radio" name="pregnant" title="Please choose type" class="pregnant showPregnant" id="prgNo" value="no" disabled="disabled"/>&nbsp;&nbsp;No
                                </td>
                                <td class=""><b>Urgency&nbsp;:</b></td>
                                <td class="">
                                    <input type="radio" name="urgency" title="Please choose urgency type" class="urgent" id="urgentYes" value="normal"/>&nbsp;&nbsp;Normal
                                    <input type="radio" name="urgency" title="Please choose urgency type" class="urgent" id="urgentYes" value="urgent"/>&nbsp;&nbsp;Urgent
                                </td>
                            </tr>
														<tr>
                                <td>&nbsp;<b>State/Province &nbsp;:</b></td>
                                <td>
                                    <input type="text" id="state" name="state" class="form-control" placeholder="State/Province" style="width:275px;"/>
                                </td>
                                <td>&nbsp;<b>District&nbsp;:</b></td>
                                <td>
                                    <input type="text" id="district" name="district" class="form-control" placeholder="District" style="width:275px;"/>
                                </td>
                            </tr>
														<tr>
                                <td class=""><b>Batch&nbsp;:</b></td>
                                <td>
                                    <select name="batch" id="batch" class="form-control" title="Please choose batch" style="width:275px;" multiple="multiple">
                                          <option value=""> -- Select -- </option>
                                          <?php
                                          foreach($batchResult as $batch){
                                          ?>
                                           <option value="<?php echo $batch['batch_id']; ?>"><?php echo $batch['batch_code']; ?></option>
                                          <?php } ?>
                                    </select>
                                </td>
																<td class=""><b>Sample Status&nbsp;:</b></td>
                                <td>
                                    <select name="sampleStatus" id="sampleStatus" class="form-control" title="Please choose sample status" style="width:275px;">
                                            <option value=""> -- Select -- </option>
                                            <option value="7">Accepted</option>
                                            <option value="4">Rejected</option>
                                    </select>
                                </td>
														</tr>
                            <tr>
                                <td class=""><b>Mail Sent Status&nbsp;:</b></td>
                                <td>
                                    <select name="sampleMailSentStatus" id="sampleMailSentStatus" class="form-control" title="Please choose sample mail sent status" style="width:275px;">
                                            <option value="no">Samples Not yet Mailed</option>
                                            <option value="">All Samples</option>
                                            <option value="yes">Already Mailed Samples</option>
                                    </select>
                                </td>
																<td></td>
																<td></td>
                            </tr>
                            <tr>
                                <td colspan="4" style="text-align:center;">&nbsp;<input type="button" class="btn btn-success btn-sm" onclick="getSampleDetails();" value="Search"/>
                                    &nbsp;<input type="button" class="btn btn-danger btn-sm" value="Reset" onclick="document.location.href = document.location;"/>
                                    </td>
                            </tr>
                        </table>
                    </div>
                </div>
                <div class="row" id="sampleDetails">
                    <div class="col-md-9">
                    <div class="form-group">
                        <label for="sample" class="col-lg-3 control-label">Choose Sample(s) <span class="mandatory">*</span></label>
                        <div class="col-lg-9">
                           <div style="width:100%;margin:0 auto;clear:both;">
                            <a href="#" id="select-all-sample" style="float:left" class="btn btn-info btn-xs">Select All&nbsp;&nbsp;<i class="icon-chevron-right"></i></a>  <a href="#" id="deselect-all-sample" style="float:right" class="btn btn-danger btn-xs"><i class="icon-chevron-left"></i>&nbsp;Deselect All</a>
                            </div><br/><br/>
                            <select id="sample" name="sample[]" multiple="multiple" class="search isRequired" title="Please select sample(s)">
                                <?php
                                foreach($result as $sample){
                                   if(trim($sample['sample_code'])!= ''){
                                  ?>
                                   <option value="<?php echo $sample['vl_sample_id'];?>"><?php  echo ucwords($sample['sample_code']);?></option>
                                  <?php
                                   }
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                  </div>
                </div>
              </div>
              <!-- /.box-body -->
              <div class="box-footer">
		<input type="hidden" id="type" name="type" value="result"/>
		<input type="hidden" id="toEmail" name="toEmail"/>
		<input type="hidden" id="reportEmail" name="reportEmail"/>
                <input type="hidden" name="pdfFile" id="pdfFile"/>
                <a class="btn btn-primary" href="javascript:void(0);" onclick="validateNow();return false;"><i class="fa fa-paper-plane" aria-hidden="true"></i> Send</a>
                <a href="../result-mail/testResultEmailConfig.php" class="btn btn-default"> Cancel</a>
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
  <script src="../assets/js/jquery.multi-select.js"></script>
  <script src="../assets/js/jquery.quicksearch.js"></script>
  <script type="text/javascript" src="../assets/plugins/daterangepicker/moment.min.js"></script>
  <script type="text/javascript" src="../assets/plugins/daterangepicker/daterangepicker.js"></script>
  <script type="text/javascript">
  var startDate = "";
  var endDate = ""; 
  $(document).ready(function() {
      $('#facilityName').select2({placeholder:"Select Facilities"});
      $('#batch').select2({placeholder:"Select Batches"});
      $('#sampleCollectionDate').daterangepicker({
        format: 'DD-MMM-YYYY',
	    separator: ' to ',
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
      
      $('#select-all-sample').click(function(){
       $('#sample').multiSelect('select_all');
       return false;
     });
     $('#deselect-all-sample').click(function(){
       $('#sample').multiSelect('deselect_all');
       return false;
     });
   });
  
  function enablePregnant(obj){
    if(obj.value=="female"){
	   $(".pregnant").prop("disabled",false);
      }else{
        $(".pregnant").prop("checked",false);
	    $(".pregnant").attr("disabled",true);
      }
  }
  
  function getSampleDetails(){
      $.blockUI();
      var facilityName = $("#facilityName").val();
      var sTypeName = $("#sampleType").val();
      var gender= $("#gender").val();
      var prg =   $("input:radio[name=pregnant]");
      if(prg[0].checked==false && prg[1].checked==false){
	pregnant = "";
      }else{
	pregnant = $('input[name=pregnant]:checked').val();
      }
      var urgent = $('input[name=urgency]:checked').val();
      var state = $('#state').val();
      var district = $('#district').val();
      var batch = $('#batch').val();
      var status = $('#sampleStatus').val();
      var sampleMailSentStatus = $('#sampleMailSentStatus').val();
      var type = $('#type').val();
      $.post("getRequestSampleCodeDetails.php", { facility : facilityName,sType:sTypeName,sampleCollectionDate:$("#sampleCollectionDate").val(),gender:gender,pregnant:pregnant,urgent:urgent,state:state,district:district,batch:batch,status:status,mailSentStatus:sampleMailSentStatus,type:type},
      function(data){
        if($.trim(data) !== ""){
          $("#sampleDetails").html(data);
        }
      });
      $.unblockUI();
  }
  
  function convertSearchResultToPdf(){
    $.blockUI();
    var sample = $("#sample").val();
    var id = sample.toString();
    $.post("<?php echo($formId == 3)?'../includes/vlRequestDrcSearchResultPdf.php':'../includes/vlRequestSearchResultPdf.php'; ?>", { source:'print',id : id,resultMail:'resultMail'},
      function(data){
	  if(data === "" || data === null || data === undefined){
	      $.unblockUI();
	      alert('Please select the result sample!');
	  }else{
	    $.blockUI();
	    $("#pdfFile").val(data);
	    document.getElementById('mailForm').submit();
	  }
      });
  }
  
  $('#facility').change(function(e){
    var toEmailId = $(this).find(':selected').data('email');
    var reportEmailId = $(this).find(':selected').data('report-email');
    if($.trim(toEmailId) == '' || $.trim(reportEmailId) == ''){
      $('.emailSection').html('No valid Email id available. Please add valid email for this facility..');
    }else{
      $('.emailSection').html('This email will be sent to the facility with an email id : '+toEmailId);
    }
    $('#toEmail').val(toEmailId);
    $('#reportEmail').val(reportEmailId);
  });
  
  function validateNow(){
    flag = deforayValidator.init({
        formId: 'mailForm'
    });
    
    if(flag){
        $.blockUI();
        convertSearchResultToPdf();
    }
  }
</script>
  
 <?php
 include('../footer.php');
 ?>
