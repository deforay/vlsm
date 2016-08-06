<?php
ob_start();
include('header.php');
include('./includes/MysqliDb.php');
define('UPLOAD_PATH','uploads');
$name='logo';
$globalConfigQuery ="SELECT value from global_config where name ='$name'";
$logoInfo=$db->query($globalConfigQuery);
?>
<link href="assets/css/jasny-bootstrap.min.css" rel="stylesheet" />
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>Manage Global Config</h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Global Config</li>
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
            <form class="form-horizontal" method='post' name='globalConfigForm' id='globalConfigForm' enctype="multipart/form-data" autocomplete="off" action="globalConfigHelper.php">
              <div class="box-body">
                <div class="row">
                  <div class="col-md-6">
                    <div class="fileinput fileinput-new" data-provides="fileinput">
                      <div class="fileinput-preview thumbnail" data-trigger="fileinput" style="width: 200px; height: 150px;">
                        <?php
                        if(isset($logoInfo[0]['value']) && trim($logoInfo[0]['value'])!= '' && file_exists(UPLOAD_PATH . DIRECTORY_SEPARATOR . "logo" . DIRECTORY_SEPARATOR . $logoInfo[0]['value'])){
                        ?>
                         <img src="uploads/logo/<?php echo $logoInfo[0]['value']; ?>" alt="Logo image">
                        <?php } else { ?>
                         <img src="http://www.placehold.it/200x150/EFEFEF/AAAAAA&text=No image">
                        <?php } ?>
                      </div>
                      <!--<div class="fileinput-preview fileinput-exists thumbnail" style="max-width: 200px; max-height: 150px;"></div>-->
                      <div>
                        <span class="btn btn-default btn-file"><span class="fileinput-new">Select image</span><span class="fileinput-exists">Change</span>
                        <input type="file" id="logoImage" name="logoImage">
                        </span>
                        <?php
                        if(isset($logoInfo[0]['value']) && trim($logoInfo[0]['value'])!= '' && file_exists(UPLOAD_PATH . DIRECTORY_SEPARATOR . "logo" . DIRECTORY_SEPARATOR . $logoInfo[0]['value'])){
                        ?>
                          <a id="clearImage" href="javascript:void(0);" class="btn btn-default" data-dismiss="fileupload" onclick="clearImage('<?php echo $logoInfo[0]['value']; ?>')">Clear</a>
                        <?php } ?>
                        <a href="#" class="btn btn-default fileinput-exists" data-dismiss="fileinput">Remove</a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <!-- /.box-body -->
              <div class="box-footer">
                <input type="hidden" name="removedLogoImage" id="removedLogoImage"/> 
                <a class="btn btn-primary" href="javascript:void(0);" onclick="validateNow();return false;">Submit</a>
                <a href="index.php" class="btn btn-default"> Cancel</a>
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
        formId: 'globalConfigForm'
    });
    
    if(flag){
      document.getElementById('globalConfigForm').submit();
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
