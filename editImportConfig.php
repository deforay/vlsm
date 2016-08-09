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
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
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
                        <input type="text" class="form-control isRequired" id="configurationName" name="configurationName" placeholder="Machine Name" title="Please enter machine name" value="<?php echo $sInfo[0]['machine_name']; ?>" onblur="checkNameValidation('import_config','machine_name',this,'<?php echo "config_id##".$sInfo[0]['config_id'];?>','This configuration name already Exist.Try with another name',null)"/>
                        </div>
                    </div>
                  </div>
                </div>
                
                <div class="row">
                   <div class="col-md-12">
                    <div class="form-group">
                        <label for="logAndAbsoluteInSameColumnYes" class="col-lg-4 control-label">Is Log and Absolute Values are same Column <span class="mandatory">*</span></label>
                        <div class="col-lg-7">
                        <label class="radio-inline">
			  <input type="radio" class="isRequired" id="logAndAbsoluteInSameColumnYes" name="logAndAbsoluteInSameColumn" value="yes" title="Please check log and absolute value are same column" <?php echo ($sInfo[0]['log_absolute_val_same_col'] == 'yes')?'checked="checked"':''; ?>> Yes
			  </label>
			  <label class="radio-inline">
			    <input type="radio" class="" id="logAndAbsoluteInSameColumnNo" name="logAndAbsoluteInSameColumn" value="no" title="Please check log and absolute value are same column" <?php echo ($sInfo[0]['log_absolute_val_same_col'] == 'no')?'checked="checked"':''; ?>> No
			  </label>
                        </div>
                    </div>
                  </div>
                </div>
                
                <div class="row" style="padding-bottom:10px;">
                  <div class="col-md-6" style="padding-left:10%;">
                    <label for="column" class="col-lg-4 control-label">Column </label>
                  </div>
                  <div class="col-md-6" style="padding-left:10%;">
                    <label for="row" class="control-label">Row</label>
                  </div>
                </div>
                
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="sampleIdCol" class="col-lg-4 control-label">Sample Id <span class="mandatory">*</span></label>
                        <div class="col-lg-7">
                          <input type="text" class="form-control isRequired" id="sampleIdCol" name="sampleIdCol" placeholder="Sample Id Column" title="Please enter sample id column" value="<?php echo $sInfo[0]['sample_id_col']; ?>"/>
                        </div>
                    </div>
                  </div>
                  
                  <div class="col-md-6">
                    <div class="form-group">
                        <div class="col-lg-7">
                        <input type="text" class="form-control isRequired" id="sampleIdRow" name="sampleIdRow" placeholder="Sample Id Row" title="Please enter sample id row" value="<?php echo $sInfo[0]['sample_id_row']; ?>"/>
                        </div>
                    </div>
                  </div>
                </div>
                
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="logValCol" class="col-lg-4 control-label">Log Value </label>
                        <div class="col-lg-7">
                          <input type="text" class="form-control" id="logValCol" name="logValCol" placeholder="Log Value Column" title="Please enter log value column" value="<?php echo $sInfo[0]['log_val_col']; ?>"/>
                        </div>
                    </div>
                  </div>
                  
                  <div class="col-md-6">
                    <div class="form-group">
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="logValRow" name="logValRow" placeholder="Log Value Row" title="Please enter log value row" value="<?php echo $sInfo[0]['log_val_row']; ?>"/>
                        </div>
                    </div>
                  </div>
                </div>
                
                <div id="absRow" class="row">
                   <div class="col-md-6">
                    <div class="form-group">
                        <label for="absoluteValCol" class="col-lg-4 control-label">Absolute Value</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="absoluteValCol" name="absoluteValCol" placeholder="Absolute Value Column" title="Please enter absolute value column" value="<?php echo $sInfo[0]['absolute_val_col']; ?>"/>
                        </div>
                    </div>
                  </div>
                   <div class="col-md-6">
                    <div class="form-group">
                        <div class="col-lg-7">
                          <input type="text" class="form-control" id="absoluteValRow" name="absoluteValRow" placeholder="Absolute Value Row" title="Please enter absolute value row" value="<?php echo $sInfo[0]['absolute_val_row']; ?>"/>
                        </div>
                    </div>
                  </div>
                </div>
                
                <div class="row">
                   <div class="col-md-6">
                    <div class="form-group">
                        <label for="textValCol" class="col-lg-4 control-label">Text Value</label>
                        <div class="col-lg-7">
                          <input type="text" class="form-control" id="textValCol" name="textValCol" placeholder="Text Value Column" title="Please enter text value column" value="<?php echo $sInfo[0]['text_val_col']; ?>"/>
                        </div>
                    </div>
                  </div>
                   <div class="col-md-6">
                    <div class="form-group">
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="textValRow" name="textValRow" placeholder="Text Value Row" title="Please enter text val row" value="<?php echo $sInfo[0]['text_val_row']; ?>"/>
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
  $(document).ready(function(){
    <?php
    if($sInfo[0]['log_absolute_val_same_col'] == 'yes'){ ?>
      $("#absRow").hide();
      $("#absoluteValCol,#absoluteValRow").val("");
      $("label[for*='logValCol']").html("Log/Absolute Value");
      $("#logValCol").attr("placeholder","Log/Absolute Val Column");
      $("#logValCol").attr("title","Please enter log/absolute val column");
      $("#logValRow").attr("placeholder","Log/Absolute Val Row");
      $("#logValRow").attr("title","Please enter log/absolute val row");
    <?php }
    ?>
  });
  
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
  
  $("input[type='radio']").click(function(){
    var id = $(this).attr('id');
    if(id == 'logAndAbsoluteInSameColumnYes'){
      $("#absRow").hide();
      $("#absoluteValCol,#absoluteValRow").val("");
      $("label[for*='logValCol']").html("Log/Absolute Value");
      $("#logValCol").attr("placeholder","Log/Absolute Val Column");
      $("#logValCol").attr("title","Please enter log/absolute val column");
      $("#logValRow").attr("placeholder","Log/Absolute Val Row");
      $("#logValRow").attr("title","Please enter log/absolute val row");
    }else{
      $("#absRow").show();
      $("label[for*='logValCol']").html("Log Value");
      $("#logValCol").attr("placeholder","Log Val Column");
      $("#logValCol").attr("title","Please enter log val column");
      $("#logValRow").attr("placeholder","Log Val Row");
      $("#logValRow").attr("title","Please enter log val row");
    }
  });
</script>
  
 <?php
 include('footer.php');
 ?>
