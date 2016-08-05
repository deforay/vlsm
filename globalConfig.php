<?php
ob_start();
include('header.php');
include('./includes/MysqliDb.php');
//$query="SELECT * FROM roles where status='active'";
//$result = $db->rawQuery($query);
?>

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
        <div class="box-header with-border">
          <div class="pull-right" style="font-size:15px;"></div>
        </div>
        <!-- /.box-header -->
        <div class="box-body">
          <!-- form start -->
            <form class="form-horizontal" method='post'  name='globalConfigForm' id='globalConfigForm' autocomplete="off" action="globalConfigHelper.php">
              <div class="box-body">
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="" class="col-lg-4 control-label">Choose logo</label>
                        <div class="col-lg-7">
                          <!--<input type="file" class="form-control" id="logoImage" name="logoImage" title="Please choose logo image" />-->
                          
                        </div>
                    </div>
                  </div>
                </div>
              </div>
              <!-- /.box-body -->
              <div class="box-footer">
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
  
  <script type="text/javascript">
  function validateNow(){
    flag = deforayValidator.init({
        formId: 'globalConfigForm'
    });
    
    if(flag){
      document.getElementById('globalConfigForm').submit();
    }
  }
</script>
  
 <?php
 include('footer.php');
 ?>
