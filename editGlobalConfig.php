<?php
ob_start();
include('header.php');
//include('./includes/MysqliDb.php');
define('UPLOAD_PATH','uploads');
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
<link href="assets/css/jasny-bootstrap.min.css" rel="stylesheet" />
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>Edit General Configuration</h1>
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
                      <label for="max_no_of_samples_in_a_batch" class="col-lg-4 control-label">Maximum No. of Samples In a Batch </label>
                      <div class="col-lg-8">
                        <input type="text" class="form-control isNumeric" id="max_no_of_samples_in_a_batch" name="max_no_of_samples_in_a_batch" placeholder="Max. no of samples" title="Please enter max no of samples in a row" value="<?php echo $arr['max_no_of_samples_in_a_batch']; ?>"/>
                      </div>
                    </div>
                   </div>
                </div>
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
                           <img src="./uploads/logo/<?php echo $arr['logo']; ?>" alt="Logo image">
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
                      <label for="vl_form" class="col-lg-4 control-label">Viral Load Form <span class="mandatory">*</span> </label>
                      <div class="col-lg-8">
                        <select class="form-control isRequired" name="vl_form" id="vl_form" title="Please select the viral load form">
                            <option value=""> -- Select -- </option>
                            <?php
                            foreach($formResult as $val){
                            ?>
                            <option value="<?php echo $val['form_id']; ?>" <?php echo ($val['form_id']==$arr['vl_form'])?"selected='selected'":""?>><?php echo $val['form_name']; ?></option>
                            <?php
                            }
                            ?>
                        </select>
                      </div>
                    </div>
                   </div>
                </div>
                <div class="row">
                  <div class="col-md-7">
                    <div class="form-group">
                      <label for="auto_approval" class="col-lg-4 control-label">Sample Code </label>
                      <div class="col-lg-8">
                        <input type="radio" class="" id="auto_generate" name="sample_code" value="auto" <?php echo($arr['sample_code'] == 'auto')?'checked':''; ?>>&nbsp;&nbsp;Auto&nbsp;&nbsp;
                        <input type="radio" class="" id="numeric" name="sample_code" value="numeric" <?php echo($arr['sample_code'] == 'numeric')?'checked':''; ?>>&nbsp;&nbsp;Numeric
                        <input type="radio" class="" id="alpha_numeric" name="sample_code" value="alphanumeric" <?php echo($arr['sample_code']=='alphanumeric')?'checked':''; ?>>&nbsp;&nbsp;Alpha Numeric
                      </div>
                    </div>
                   </div>
                </div>
                <div class="row">
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
  <script type="text/javascript" src="assets/js/jasny-bootstrap.js"></script>
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
</script>
  
 <?php
 include('footer.php');
 ?>
