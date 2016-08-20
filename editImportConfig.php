<?php
ob_start();
include('header.php');
include('./includes/MysqliDb.php');
$id=base64_decode($_GET['id']);
$sQuery="SELECT * from import_config where config_id=$id";
$sInfo=$db->query($sQuery);
?>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>Edit Import Configuration</h1>
      <ol class="breadcrumb">
        <li><a href="index.php"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Edit Import Config</li>
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
            <form class="form-horizontal" method='post' name='editImportConfigForm' id='editImportConfigForm' autocomplete="off" action="editImportConfigHelper.php">
              <div class="box-body">
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="configurationName" class="col-lg-4 control-label">Configuration Name<span class="mandatory">*</span></label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control isRequired" id="configurationName" name="configurationName" placeholder="eg. Roche or Abbott" title="Please enter configuration name" value="<?php echo $sInfo[0]['machine_name']; ?>" onblur="checkNameValidation('import_config','machine_name',this,'<?php echo "config_id##".$sInfo[0]['config_id'];?>','This configuration name already exists.Try another name',null)"/>
                        </div>
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="configurationName" class="col-lg-4 control-label">Configuration File Name<span class="mandatory">*</span></label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control isRequired" id="configurationFile" name="configurationFile" placeholder="eg. roche.php or abbott.php" title="Please enter file name" value="<?php echo $sInfo[0]['file_name']; ?>" onblur="checkNameValidation('import_config','file_name',this,'<?php echo "config_id##".$sInfo[0]['config_id'];?>','This file name already exists.Try another name',null)"/>
                        </div>
                    </div>
                  </div>
                </div>
                
		<div class="row">
                   <div class="col-md-6" style="padding-top:20px;">
                    <div class="form-group">
                        <label for="status" class="col-lg-4 control-label">Status</label>
                        <div class="col-lg-7">
                          <select class="form-control" id="status" name="status" title="Please select import config status">
			    <option value="active" <?php echo ($sInfo[0]['status'] == 'active')?'selected="selected"':''; ?>>Active</option>
			    <option value="inactive" <?php echo ($sInfo[0]['status'] == 'inactive')?'selected="selected"':''; ?>>Inactive</option>
			  </select>
                        </div>
                    </div>
                  </div>
                </div>
              </div>
              <!-- /.box-body -->
              <div class="box-footer">
		<input type="hidden" id="configId" name="configId" value="<?php echo base64_encode($sInfo[0]['config_id']); ?>"/>
                <a class="btn btn-primary" href="javascript:void(0);" onclick="validateNow();return false;">Submit</a>
                <a href="importConfig.php" class="btn btn-default"> Cancel</a>
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
  
  <script type="text/javascript">
  function validateNow(){
    flag = deforayValidator.init({
        formId: 'editImportConfigForm'
    });
    
    if(flag){
      document.getElementById('editImportConfigForm').submit();
    }
  }
  
  function checkNameValidation(tableName,fieldName,obj,fnct,alrt,callback){
        var removeDots=obj.value.replace(/\./g,"");
        var removeDots=removeDots.replace(/\,/g,"");
        //str=obj.value;
        removeDots = removeDots.replace(/\s{2,}/g,' ');

        $.post("checkDuplicate.php", { tableName: tableName,fieldName : fieldName ,value : removeDots.trim(),fnct : fnct, format: "html"},
        function(data){
            if(data==='1'){
                alert(alrt);
                document.getElementById(obj.id).value="";
            }
        });
  }
</script>
  
 <?php
 include('footer.php');
 ?>
