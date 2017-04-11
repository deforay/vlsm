<?php
ob_start();
include('../header.php');
//include('../includes/MysqliDb.php');
define('UPLOAD_PATH','../uploads');
$formQuery ="SELECT * from form_details";
$formResult=$db->query($formQuery);
$globalConfigQuery ="SELECT * from global_config";
$configResult=$db->query($globalConfigQuery);
$arr = array();
// now we create an associative array so that we can easily create view variables
for ($i = 0; $i < sizeof($configResult); $i++) {
    $arr[$configResult[$i]['name']] = $configResult[$i]['value'];
}
?>
<link href="../assets/css/jasny-bootstrap.min.css" rel="stylesheet" />
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1 class="fa fa-gears"> Edit General Configuration</h1>
      <ol class="breadcrumb">
        <li><a href="index.php"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Manage General Config</li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <!-- SELECT2 EXAMPLE -->
      <div class="box box-default">
        <!--<div class="box-header with-border">
          <div class="pull-right" style="font-size:15px;"> </div>
        </div>-->
        <!-- /.box-header -->
        <div class="box-body">
          <!-- form start -->
            <form class="form-horizontal" method='post' name='editGlobalConfigForm' id='editGlobalConfigForm' enctype="multipart/form-data" autocomplete="off" action="globalConfigHelper.php">
              <div class="box-body">
                <div class="row">
                  <div class="col-md-7">
                    <div class="form-group">
                      <label for="default_time_zone" class="col-lg-4 control-label">Default Time Zone </label>
                      <div class="col-lg-8">
                        <input type="text" class="form-control" id="default_time_zone" name="default_time_zone" placeholder="eg: Africa/Harare" title="Please enter default time zone" value="<?php echo $arr['default_time_zone']; ?>"/>
                      </div>
                    </div>
                   </div>
                </div>
                <div class="row">
                  <div class="col-md-7">
                    <div class="form-group">
                      <label for="header" class="col-lg-4 control-label">Header </label>
                      <div class="col-lg-8">
                        <textarea class="form-control" id="header" name="header" placeholder="Header" title="Please enter header" style="width:100%;min-height:80px;max-height:100px;"><?php echo $arr['header']; ?></textarea>
                      </div>
                    </div>
                   </div>
                </div>
                <div class="row">
                  <div class="col-md-7">
                    <div class="form-group">
                      <label for="show_smiley" class="col-lg-4 control-label">Do you want to show smiley in the result PDF? </label>
                      <div class="col-lg-8">
                        <input type="radio" class="" id="show_smiley_yes" name="show_smiley" value="yes" <?php echo($arr['show_smiley'] == 'yes')?'checked':''; ?>>&nbsp;&nbsp;Yes&nbsp;&nbsp;
                        <input type="radio" class="" id="show_smiley_no" name="show_smiley" value="no" <?php echo($arr['show_smiley'] == 'no' || $arr['show_smiley'] == '')?'checked':''; ?>>&nbsp;&nbsp;No
                      </div>
                    </div>
                   </div>
                </div>
                <div class="row">
                  <div class="col-md-7">
                    <div class="form-group">
                      <label for="" class="col-lg-4 control-label">Logo Image </label>
                      <div class="col-lg-8">
                       <div class="fileinput fileinput-new" data-provides="fileinput">
                        <div class="fileinput-preview thumbnail" data-trigger="fileinput" style="width:200px; height:150px;">
                          <?php
                          if(isset($arr['logo']) && trim($arr['logo'])!= '' && file_exists(UPLOAD_PATH . DIRECTORY_SEPARATOR . "logo" . DIRECTORY_SEPARATOR . $arr['logo'])){
                          ?>
                           <img src=".././uploads/logo/<?php echo $arr['logo']; ?>" alt="Logo image">
                          <?php } else { ?>
                           <img src="http://www.placehold.it/200x150/EFEFEF/AAAAAA&text=No image">
                          <?php } ?>
                        </div>
                        <div>
                          <span class="btn btn-default btn-file"><span class="fileinput-new">Select image</span><span class="fileinput-exists">Change</span>
                          <input type="file" id="logo" name="logo" title="Please select logo image" onchange="getNewImage('<?php echo $arr['logo']; ?>');">
                          </span>
                          <?php
                          if(isset($arr['logo']) && trim($arr['logo'])!= '' && file_exists(UPLOAD_PATH . DIRECTORY_SEPARATOR . "logo" . DIRECTORY_SEPARATOR . $arr['logo'])){
                          ?>
                            <a id="clearImage" href="javascript:void(0);" class="btn btn-default" data-dismiss="fileupload" onclick="clearImage('<?php echo $arr['logo']; ?>')">Clear</a>
                          <?php } ?>
                          <a href="#" class="btn btn-default fileinput-exists" data-dismiss="fileinput">Remove</a>
                        </div>
                        </div>
                        <div class="box-body">
                            Please make sure logo image size of: <code>80x80</code>
                        </div>
                      </div>
                    </div>
                   </div>
                </div>
                <div class="row">
                  <div class="col-md-7">
                    <div class="form-group">
                      <label for="show_date" class="col-lg-4 control-label">Date For Patient ART NO. </label>
                      <div class="col-lg-8">
                        <input type="radio" class="" id="show_full_date_yes" name="show_date" value="yes" <?php echo($arr['show_date'] == 'yes')?'checked':''; ?>>&nbsp;&nbsp;Full Date&nbsp;&nbsp;
                        <input type="radio" class="" id="show_full_date_no" name="show_date" value="no" <?php echo($arr['show_date'] == 'no' || $arr['show_date'] == '')?'checked':''; ?>>&nbsp;&nbsp;Month and Year
                      </div>
                    </div>
                   </div>
                </div>
                <div class="row">
                  <div class="col-md-7">
                    <div class="form-group">
                      <label for="auto_approval" class="col-lg-4 control-label">Auto Approval </label>
                      <div class="col-lg-8">
                        <input type="radio" class="" id="auto_approval_yes" name="auto_approval" value="yes" <?php echo($arr['auto_approval'] == 'yes')?'checked':''; ?>>&nbsp;&nbsp;Yes&nbsp;&nbsp;
                        <input type="radio" class="" id="auto_approval_no" name="auto_approval" value="no" <?php echo($arr['auto_approval'] == 'no' || $arr['auto_approval'] == '')?'checked':''; ?>>&nbsp;&nbsp;No
                      </div>
                    </div>
                   </div>
                </div>
                <div class="row">
                  <div class="col-md-7">
                    <div class="form-group">
                      <label for="viral_load_threshold_limit" class="col-lg-4 control-label">Viral Load Threshold Limit <span class="mandatory">*</span></label>
                      <div class="col-lg-8">
                        <input type="text" class="form-control checkNum isNumeric isRequired" id="viral_load_threshold_limit" name="viral_load_threshold_limit" placeholder="Viral Load Threshold Limit" title="Please enter VL threshold limit" value="<?php echo $arr['viral_load_threshold_limit']; ?>"/>
                      </div>
                    </div>
                   </div>
                </div>
                <div class="row">
                  <div class="col-md-7">
                    <div class="form-group">
                      <label for="vl_form" class="col-lg-4 control-label">Viral Load Form <span class="mandatory">*</span> </label>
                      <div class="col-lg-8">
                        <select class="form-control isRequired" name="vl_form" id="vl_form" title="Please select the viral load form">
                            <?php
                            foreach($formResult as $val){
                            ?>
                            <option value="<?php echo $val['vlsm_country_id']; ?>" <?php echo ($val['vlsm_country_id']==$arr['vl_form'])?"selected='selected'":""?>><?php echo $val['form_name']; ?></option>
                            <?php
                            }
                            ?>
                        </select>
                      </div>
                    </div>
                   </div>
                </div>
                <div class="row">
                  <div class="col-md-7" style="height:28px;">
                    <div class="form-group" style="height:28px;">
                      <label for="auto_approval" class="col-lg-4 control-label">Sample Code </label>
                      <div class="col-lg-8">
                        <input type="radio" class="" id="auto_generate" name="sample_code" value="auto" <?php echo($arr['sample_code'] == 'auto')?'checked':''; ?>>&nbsp;&nbsp;Auto&nbsp;&nbsp;
                        <input type="radio" class="" id="numeric" name="sample_code" value="numeric" <?php echo($arr['sample_code'] == 'numeric')?'checked':''; ?>>&nbsp;&nbsp;Numeric
                        <input type="radio" class="" id="alpha_numeric" name="sample_code" value="alphanumeric" <?php echo($arr['sample_code']=='alphanumeric')?'checked':''; ?>>&nbsp;&nbsp;Alpha Numeric
                      </div>
                    </div>
                   </div>
                </div>
                <div id="auto-sample-eg" class="row" style="display:<?php echo($arr['sample_code'] == 'auto')?'block':'none'; ?>;">
                  <div class="col-md-7" style="text-align:center;">
                      <code>eg. Province Code+Year+Month+Date+Increment Counter</code>
                  </div>
                </div>
                <div class="row" style="margin-top:10px;">
                  <div class="col-md-7">
                    <div class="form-group">
                      <label for="auto_approval" class="col-lg-4 control-label">Same user can Review and Approve </label>
                      <div class="col-lg-8">
                        <input type="radio" class="" id="user_review_yes" name="user_review_approve" value="yes" <?php echo($arr['user_review_approve'] == 'yes')?'checked':''; ?>>&nbsp;&nbsp;Yes&nbsp;&nbsp;
                        <input type="radio" class="" id="user_review_no" name="user_review_approve" value="no" <?php echo($arr['user_review_approve'] == 'no')?'checked':''; ?>>&nbsp;&nbsp;No
                      </div>
                    </div>
                   </div>
                </div>
                <div class="row">
                  <div class="col-md-7">
                    <div class="form-group">
                      <label for="min_length" class="col-lg-4 control-label">Minimum Length<span class="mandatory minlth" style="display:<?php echo($arr['sample_code'] == 'auto')?'none':'block'; ?>">*</span></label>
                      <div class="col-lg-8">
                        <input type="text" class="form-control checkNum isNumeric <?php echo($arr['sample_code'] == 'auto')?'':'isRequired'; ?>" id="min_length" name="min_length" <?php echo($arr['sample_code'] == 'auto')?'readonly':''; ?> placeholder="Sample Code Min. Length" title="Please enter sample code min length" value="<?php echo ($arr['sample_code'] == 'auto')?'':$arr['min_length']; ?>"/>
                      </div>
                    </div>
                   </div>
                </div>
                <div class="row">
                  <div class="col-md-7">
                    <div class="form-group">
                      <label for="min_length" class="col-lg-4 control-label">Maximum Length<span class="mandatory maxlth" style="display:<?php echo($arr['sample_code'] == 'auto')?'none':'block'; ?>">*</span></label>
                      <div class="col-lg-8">
                        <input type="text" class="form-control checkNum isNumeric <?php echo($arr['sample_code'] == 'auto')?'':'isRequired'; ?>" id="max_length" name="max_length" <?php echo($arr['sample_code'] == 'auto')?'readonly':''; ?> placeholder="Sample Code Max. Length" title="Please enter sample code max length" value="<?php echo ($arr['sample_code'] == 'auto')?'':$arr['max_length']; ?>"/>
                      </div>
                    </div>
                   </div>
                </div>
                <div class="row">
                  <div class="col-md-7">
                    <div class="form-group">
                      <label for="sync_path" class="col-lg-4 control-label">Sync Path</label>
                      <div class="col-lg-8">
                        <input type="text" class="form-control" id="sync_path" name="sync_path" placeholder="Sync Path" title="Please enter sync path" value="<?php echo $arr['sync_path']; ?>"/>
                      </div>
                    </div>
                   </div>
                </div>
                <div class="row">
                  <div class="col-md-7" style="height:38px;">
                    <div class="form-group" style="height:38px;">
                      <label for="manager_email" class="col-lg-4 control-label">Manager Email</label>
                      <div class="col-lg-8">
                        <input type="text" class="form-control" id="manager_email" name="manager_email" placeholder="Manager Email" title="Please enter manager email" value="<?php echo $arr['manager_email']; ?>"/>
                      </div>
                    </div>
                   </div>
                </div>
                <div class="row">
                  <div class="col-md-7" style="text-align:center;">
                      <code>You can enter multiple email by separating them with comma</code>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-7">
                    <div class="form-group">
                      <label for="instance_type" class="col-lg-4 control-label">Instance Type <span class="mandatory">*</span> </label>
                      <div class="col-lg-8">
                        <select class="form-control isRequired" name="instance_type" id="instance_type" title="Please select the instance type">
                            <option value="Clinic/Lab" <?php echo ('Clinic/Lab'==$arr['instance_type'])?"selected='selected'":""?>>Clinic/Lab</option>
                            <option value="Viral Load Lab" <?php echo ('Viral Load Lab'==$arr['instance_type'])?"selected='selected'":""?>>Viral Load Lab</option>
                            <option value="Both" <?php echo ('Both'==$arr['instance_type'])?"selected='selected'":""?>>Both</option>
                        </select>
                      </div>
                    </div>
                   </div>
                </div>
                <div class="row">
                  <div class="col-md-7">
                    <div class="form-group">
                      <label for="instance_type" class="col-lg-4 control-label">Barcode Printing  (Experimental) <span class="mandatory">*</span> </label>
                      <div class="col-lg-8">
                        <select class="form-control isRequired" name="bar_code_printing" id="bar_code_printing" title="Please select the barcode printing">
                            <option value="off" <?php echo ('off'==$arr['bar_code_printing'])?"selected='selected'":""?>>Off</option>
                            <option value="zebra-printer" <?php echo ('zebra-printer'==$arr['bar_code_printing'])?"selected='selected'":""?>>Zebra Printer</option>
                            <option value="dymo-labelwriter-450" <?php echo ('dymo-labelwriter-450'==$arr['bar_code_printing'])?"selected='selected'":""?>>Dymo LabelWriter 450</option>
                        </select>
                      </div>
                    </div>
                   </div>
                </div>
              </div>
              
              <!-- /.box-body -->
              <div class="box-footer">
                <input type="hidden" name="removedLogoImage" id="removedLogoImage"/>
                <a class="btn btn-primary" href="javascript:void(0);" onclick="validateNow();return false;">Submit</a>
                <a href="globalConfig.php" class="btn btn-default"> Cancel</a>
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
  <script type="text/javascript" src="../assets/js/jasny-bootstrap.js"></script>
  <script type="text/javascript">
  function validateNow(){
    flag = deforayValidator.init({
        formId: 'editGlobalConfigForm'
    });
    
    if(flag){
        $.blockUI();
      document.getElementById('editGlobalConfigForm').submit();
    }
  }
  
  function clearImage(img){
    $(".fileinput").fileinput("clear");
    $("#clearImage").addClass("hide");
    $("#removedLogoImage").val(img);
  }
  
  function getNewImage(img){
    $("#clearImage").addClass("hide");
    $("#removedLogoImage").val(img);
  }
  
  $("input:radio[name=sample_code]").click(function() {
        if(this.value == 'auto'){
           $('#auto-sample-eg').show(); 
           $('#min_length').val(''); 
           $('.minlth').hide();
           $('#min_length').removeClass('isRequired'); 
           $('#min_length').prop('readonly',true); 
           $('#max_length').val('');
           $('.maxlth').hide();
           $('#max_length').removeClass('isRequired'); 
           $('#max_length').prop('readonly',true);
        }else{
           $('#auto-sample-eg').hide();
           $('.minlth').show();
           $('#min_length').addClass('isRequired');
           $('#min_length').prop('readonly',false);
           $('.maxlth').show();
           $('#max_length').addClass('isRequired');
           $('#max_length').prop('readonly',false);
        }
  });
</script>
  
 <?php
 include('../footer.php');
 ?>
