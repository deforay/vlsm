<?php
ob_start();
session_start();
include('header.php');
include('./includes/MysqliDb.php');
?>
<link href="assets/css/jquery.multiselect.css" rel="stylesheet" />
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>Create Batch</h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Facility</li>
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
            <form class="form-horizontal" method='post'  name='addBatchForm' id='addBatchForm' autocomplete="off" action="addFacilityHelper.php">
              <div class="box-body">
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="batchCode" class="col-lg-4 control-label">Batch Code <span class="mandatory">*</span></label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control isRequired" id="batchCode" name="batchCode" placeholder="Batch Code" title="Please enter batch code" />
                        </div>
                    </div>
                  </div>
                </div>
                
              <div class="row">
                <div class="col-md-6" id="lefter">
                  <div class="widget widget-radar widget-pie">
                    <div class="widget-head">
		      
                      <span class="title"><b>Audit Performance</b></span>
                      <label style="margin-left:5%;">Select Audit Round</label>
                      <select name="auditRndNo[]" id="auditRndNo" multiple="multiple">
                          <option value='elem_1'>elem 1</option>
                          <option value='elem_2'>elem 2</option>
                          <option value='elem_3'>elem 3</option>
                          <option value='elem_4'>elem 4</option>
                        </select>
                    </div>
                        <div id="radarChart">
		      	
                        </div>
                    </div>
                  
                  
                </div>
              </div>
               
              </div>
              <!-- /.box-body -->
              <div class="box-footer">
                <a class="btn btn-primary" href="javascript:void(0);" onclick="validateNow();return false;">Submit</a>
                <a href="facilities.php" class="btn btn-default"> Cancel</a>
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
  
  <script src="./assets/js/jquery.multiselect.js"></script>
  <script type="text/javascript">

  function validateNow(){
    flag = deforayValidator.init({
        formId: 'addBatchForm'
    });
    
    if(flag){
      document.getElementById('addBatchForm').submit();
    }
  }
   //$("#auditRndNo").multiselect({height: 100,minWidth: 150});
   
  </script>
  
 <?php
 include('footer.php');
 ?>
