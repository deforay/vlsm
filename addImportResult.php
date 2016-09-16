<?php
ob_start();
include('header.php');
//include('./includes/MysqliDb.php');
include('General.php');
$general=new Deforay_Commons_General();
$query="SELECT config_id,machine_name,file_name FROM import_config where status='active'";
$iResult = $db->rawQuery($query);

$fQuery="SELECT * FROM facility_details where facility_type=2";
$fResult = $db->rawQuery($fQuery);
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
                                  <option value=""> -- Select -- </option>
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
                        
                    </div>
                  </div>
                </div>
                
                <div class="row setup-content step" id="step-2">
                  <div class="col-xs-12">
                    <div class="col-md-12" id="stepTwoForm">
                      <div class="row">
                         <div class="col-md-6">
                          <div class="form-group">
                              <label for="labId" class="col-lg-4 control-label">Lab Name <span class="mandatory">*</span></label>
                              <div class="col-lg-7">
                              <select name="labId" id="labId" class="form-control isRequired" title="Please select the lab name">
								<option value=""> -- Select -- </option>
                                  <?php
                                  foreach($fResult as $val){
                                  ?>
                                  <option value="<?php echo base64_encode($val['facility_id']); ?>" selected="selected"><?php echo ucwords($val['facility_name']); ?></option>
                                  <?php } ?>
							  </select>
                              </div>
                          </div>
                        </div>
                        
                      </div>
                   
                      
                      
                     
                      <div class="row form-group">        
                      <div class="box-footer">
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
</script>
 <?php
 include('footer.php');
 ?>
