<?php
ob_start();
include('header.php');
//include('./includes/MysqliDb.php');
include('General.php');
$general=new Deforay_Commons_General();
$query="SELECT config_id,machine_name,file_name FROM import_config where status='active'";
$iResult = $db->rawQuery($query);
$reviewedOn = $general->humanDateFormat(date('Y-m-d'));
?>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>Add Import Result</h1>
      <ol class="breadcrumb">
        <li><a href="index.php"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Users</li>
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
          <div class="row form-group">
          <div class="col-xs-12 wizard_steps">
               <ul class="clearfix nav nav-pills nav-justified thumbnail setup-panel"> 
                  <li class="active" id="list-step-1"> 
                      <a href="javascript:void(0);" id="#step-1" class="clearfix" onclick="changeStep(this, null)"> 
                          <h4 class="list-group-item-heading">STEP ONE</h4>
                      </a> 
                  </li> 
                  <li id="list-step-2"> 
                      <a href="javascript:void(0);" id="#step-2" class="clearfix" onclick="changeStep(this, 'stepOneForm')"> 
                          <h4 class="list-group-item-heading">STEP TWO</h4>
                      </a> 
                  </li>
              </ul>	
          </div>
	</div>
            <form class="form-horizontal" method='post'  name='addImportResultForm' id='addImportResultForm' enctype="multipart/form-data" autocomplete="off" action="addImportResultHelper.php">
              <div class="box-body">
                <div class="wizard_content">
                  <div class="row setup-content step" id="step-1" style="display:block;">
                    <div class="col-xs-12">
                      <div class="col-md-12" id="stepOneForm">
                        <div class="row">
                          <div class="col-md-6">
                            <div class="form-group">
                                <label for="machineName" class="col-lg-4 control-label">Configuration Name <span class="mandatory">*</span></label>
                                <div class="col-lg-7">
                                <select name="machineName" id="machineName" class="form-control isRequired" title="Please select the import machine type">
                                  <option value="">-- Select --</option>
                                  <?php
                                  foreach($iResult as $val){
                                  ?>
                                  <option value="<?php echo base64_encode($val['file_name']); ?>"><?php echo ucwords($val['machine_name']); ?></option>
                                  <?php } ?>
                                </select>
                                </div>
                            </div>
                          </div>
                        </div>
                        <div class="row">
                          <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-lg-4 control-label">Upload File <span class="mandatory">*</span></label>
                                <div class="col-lg-7">
                                <input type="file" class="isRequired" name="resultFile" id="resultFile" title="Please choose result file">
                                (Upload xls, xlsx, csv format)
                                </div>
                            </div>
                          </div>
                        </div>
                        <a href="javascript:void(0)" class="btn btn-primary" id="step-2" onclick="validateStep(this.id, 'stepOneForm', false);return false;">Next</a>
                    </div>
                  </div>
                </div>
                
                <div class="row setup-content step" id="step-2" style="display:none;">
                  <div class="col-xs-12">
                    <div class="col-md-12" id="stepTwoForm">
                      <div class="row">
                         <div class="col-md-6">
                          <div class="form-group">
                              <label for="labName" class="col-lg-4 control-label">Lab Name <span class="mandatory">*</span></label>
                              <div class="col-lg-7">
                              <input type="text" class="form-control isRequired" id="labName" name="labName" placeholder="Lab Name" title="Please enter lab name" />
                              <input type="hidden" class="form-control" id="labId" name="labId" placeholder="Lab ID" title="Please enter lab name" />
                              </div>
                          </div>
                        </div>
                         <div class="col-md-6">
                          <div class="form-group">
                              <label for="labContactPerson" class="col-lg-4 control-label">Lab Contact Person </label>
                              <div class="col-lg-7">
                              <input type="text" class="form-control" id="labContactPerson" name="labContactPerson" placeholder="Lab Contact Person" title="Please enter the lab contact person"/>
                              </div>
                          </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-md-6">
                           <div class="pull-right" style="margin-right:8%;margin-bottom:10px;"><a id="clearFInfo" href="javascript:void(0);" onclick="clearFacilitiesInfo();" class="btn btn-danger btn-sm" style="display:none;">Clear</a>&nbsp;&nbsp;<a href="javascript:void(0);" onclick="showModal('facilitiesModal.php',900,520);" class="btn btn-default btn-sm" style="margin-right: 2px;" title="Search"><i class="fa fa-search"></i> Search</a></div>
                        </div>
                      </div>
                      <div class="row">
                         <div class="col-md-6">
                          <div class="form-group">
                              <label for="labPhoneNo" class="col-lg-4 control-label">Lab Phone</label>
                              <div class="col-lg-7">
                              <input type="text" class="form-control" id="labPhoneNo" name="labPhoneNo" placeholder="Lab Phone Number" title="Please enter lab phone number" />
                              </div>
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="form-group">
                              <label class="col-lg-4 control-label">Samples Received On <span class="mandatory">*</span></label>
                              <div class="col-lg-7">
                              <input type="text" class="form-control isRequired datePicker readonly" id="sampleReceivedDate" name="sampleReceivedDate" placeholder="Samples/Batch Received Date" title="Please enter samples/batch received date" readonly="readonly"/>
                              </div>
                          </div>
                        </div>
                      </div>
                      <div class="row">
                         <div class="col-md-6">
                          <div class="form-group">
                              <label class="col-lg-4 control-label">Samples Tested On <span class="mandatory">*</span></label>
                              <div class="col-lg-7">
                              <input type="text" class="form-control isRequired datePicker readonly" id="testingDate" name="testingDate" placeholder="Samples/Batch Testing Date" title="Please enter samples/batch testing date" readonly="readonly" />
                              </div>
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="form-group">
                              <label class="col-lg-4 control-label">Dispatch On <span class="mandatory">*</span></label>
                              <div class="col-lg-7">
                              <input type="text" class="form-control isRequired datePicker readonly" id="dispatchedDate" name="dispatchedDate" placeholder="Dispatched Date" title="Please enter dispatched date" readonly="readonly"/>
                              </div>
                          </div>
                        </div>
                      </div>
                      <div class="row">
                        <!--<div class="col-md-6">
                          <div class="form-group">
                              <label for="reviewedBy" class="col-lg-4 control-label">Reviewed By</label>
                              <div class="col-lg-7">
                              <input type="text" class="form-control" id="reviewedBy" name="reviewedBy" placeholder="Reviewed By" title="Please enter reviewed by" value="< ?php if(isset($_SESSION['userName'])){ echo $_SESSION['userName']; } ?>"/>
                              </div>
                          </div>
                        </div>-->
                        <div class="col-md-6">
                          <div class="form-group">
                              <label class="col-lg-4 control-label">Reviewed On</label>
                              <div class="col-lg-7">
                              <input type="text" class="form-control datePicker readonly" id="reviewedDate" name="reviewedDate" placeholder="Reviewed Date" title="Please enter reviewed date" value="<?php echo $reviewedOn; ?>" readonly="readonly"/>
                              </div>
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="form-group">
                              <label for="comments" class="col-lg-4 control-label">Comments </label>
                              <div class="col-lg-7">
                              <textarea class="form-control" id="comments" name="comments" placeholder="Comments"></textarea>
                              </div>
                          </div>
                        </div>
                      </div>
                      <div class="row form-group">        
                      <div class="box-footer">
                        <a href="javascript:void(0)" class="btn btn-primary" onclick="prevStep('step-1')">Prev</a>
                        <a class="btn btn-primary" href="javascript:void(0);" onclick="validateNow();return false;">Submit</a>
                        <a href="index.php" class="btn btn-default"> Cancel</a>
                      </div>
                      </div>
                    </div>
                  </div>
                </div>
                </div>
              </div>
              <!-- /.box-body -->
              
              <!-- /.box-footer -->
            </form>
          <!-- /.row -->
        </div>
       
      </div>
      <!-- /.box -->

    </section>
    <!-- /.content -->
  </div>
  
  
  <script type="text/javascript">

  function validateNow(){
    flag = deforayValidator.init({
        formId: 'addImportResultForm'
    });
    
    if(flag){
      document.getElementById('addImportResultForm').submit();
    }
  }
  $(document).ready(function() {
    $('.datePicker').datepicker({
        changeMonth: true,
        changeYear: true,
        dateFormat: 'dd-M-yy',
        timeFormat: "hh:mm TT",
        yearRange: <?php echo (date('Y') - 100); ?> + ":" + "<?php echo (date('Y')) ?>"
    });
  
  });
  function changeStep(obj, formId){
	var step = $(obj).attr('id');
	var step_num = $(obj).attr('id').replace('#step-','');
	for(m=1;m<step_num;m++){
	  if (m==1) {
	    formId='stepOneForm';
	  }
	  if (m==2) {
	    formId='stepTwoForm';
	  }
	  
	  if(formId != null){
	      flag = deforayValidator.init({
		  formId: formId
	      });
	      
	      if(flag == false || flag == 'false'){
		if(!$("#list-step-"+m).hasClass('active')){
		  $('.wizard_content').children().hide();
		}
		$('.wizard_steps ul li').removeClass('active');
		$("#list-step-"+m).addClass('active');
		
		$('.wizard_content').children("#step-"+m).fadeIn();
		return false;
	      }
	  }
	}
       
        if(flag == true || flag == 'true' || formId == null){
            $('.wizard_steps ul li').removeClass('active');
            $(obj).parent('li').addClass('active');
            var step = $(obj).attr('id');
            $('.wizard_content').children().hide();
            $('.wizard_content').children(step).fadeIn();
            return false;
        }
    }
  duplicateName=true;
    function validateStep(buttonID,formId,submitForm){
	if (submitForm) {
	  formId='addEmployeeInformation';
	}
        flag = deforayValidator.init({
            formId: formId
        });         

      if(flag == true || flag == 'true'){
        if(duplicateName){
            var step = buttonID;
            var hash_step = ('#'+step);	    
            // if submitForm is true, then no need to change the step.   
            if(submitForm){
                document.getElementById('addEmployeeInformation').submit();
            }
            else{
                $('.wizard_steps ul li').removeClass('active');
                $('a[id='+ hash_step +']').parent().addClass('active');
                $('.wizard_content').children().hide();
                $('.wizard_content').children(hash_step).fadeIn();
                return false;
            }
        }
      }
    }
    function prevStep(objId) {
	$('.wizard_steps ul li').removeClass('active');
        $("#list-"+objId).addClass('active');
	$('.wizard_content').children().hide();
        $('.wizard_content').children("#"+objId).fadeIn();
	
    }
	
	
	
function setFacilityDetails(fDetails){
      $("#labId").val("");
      $("#labName").val("");
      facilityArray = fDetails.split("##");
      $("#labId").val(facilityArray[0]);
      $("#labName").val(facilityArray[1]);
	  $("#labContactPerson").val(facilityArray[4]);
	  $("#labPhoneNo").val(facilityArray[5]);
      $("#labName").prop('readonly',true);
      $("#clearFInfo").show();
    }
    
    function clearFacilitiesInfo(){
      $("#labId").val("");
      $("#labName").val("");
	  $("#labContactPerson").val("");
	  $("#labPhoneNo").val("");
      $("#labName").prop('readonly',false);
      $("#clearFInfo").hide();
    }	
	
	
</script>
 <?php
 include('footer.php');
 ?>
