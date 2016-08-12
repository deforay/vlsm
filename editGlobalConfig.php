<?php
ob_start();
include('header.php');
include('./includes/MysqliDb.php');
define('UPLOAD_PATH','uploads');
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
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
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
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="header" class="col-lg-3 control-label">Header </label>
                      <div class="col-lg-9">
                        <textarea id="header" name="header" style="width:100%;min-height:80px;max-height:100px;"><?php echo $arr['header']; ?></textarea>
                      </div>
                    </div>
                   </div>
                </div>
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="" class="col-lg-3 control-label">Logo Image </label>
                      <div class="col-lg-9">
                       <div class="fileinput fileinput-new" data-provides="fileinput">
                        <div class="fileinput-preview thumbnail" data-trigger="fileinput" style="width:200px; height:150px;">
                          <?php
                          if(isset($arr['logo']) && trim($arr['logo'])!= '' && file_exists(UPLOAD_PATH . DIRECTORY_SEPARATOR . "logo" . DIRECTORY_SEPARATOR . $arr['logo'])){
                          ?>
                           <img src="uploads/logo/<?php echo $arr['logo']; ?>" alt="Logo image">
                          <?php } else { ?>
                           <img src="http://www.placehold.it/200x150/EFEFEF/AAAAAA&text=No image">
                          <?php } ?>
                        </div>
                        <!--<div class="fileinput-preview fileinput-exists thumbnail" style="max-width: 200px; max-height: 150px;"></div>-->
                        <div>
                          <span class="btn btn-default btn-file"><span class="fileinput-new">Select image</span><span class="fileinput-exists">Change</span>
                          <input type="hidden" name="removedLogoImage" id="removedLogoImage"/>   
                          <input type="file" id="logo" name="logo">
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
              </div>
              <!-- /.box-body -->
              <div class="box-footer">
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
      document.getElementById('editGlobalConfigForm').submit();
    }
  }
  
  function clearImage(img){
    $(".fileinput").fileinput("clear");
    $("#clearImage").addClass("hide");
    $("#removedLogoImage").val(img);
  }
</script>
  
 <?php
 include('footer.php');
 ?>
