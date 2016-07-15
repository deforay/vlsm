<?php
ob_start();
session_start();
include('header.php');
include('./includes/MysqliDb.php');
?>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>Add Facility</h1>
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
            <form class="form-horizontal" method='post'  name='addFacilityForm' id='addFacilityForm' autocomplete="off" action="addFacilityHelper.php">
              <div class="box-body">
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="facilityName" class="col-lg-4 control-label">Facility Name <span class="mandatory">*</span></label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control isRequired" id="facilityName" name="facilityName" placeholder="Facility Name" title="Please enter facility name" />
                        </div>
                    </div>
                  </div>
                   <div class="col-md-6">
                    <div class="form-group">
                        <label for="facilityCode" class="col-lg-4 control-label">Facility Code <span class="mandatory">*</span> </label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control isRequired" id="facilityCode" name="facilityCode" placeholder="Facility Code" title="Please enter facility code"/>
                        </div>
                    </div>
                  </div>
                </div>
                <div class="row">
                   <div class="col-md-6">
                    <div class="form-group">
                        <label for="country" class="col-lg-4 control-label">Country</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="country" name="country" placeholder="Country"/>
                        </div>
                    </div>
                  </div>
                   <div class="col-md-6">
                    <div class="form-group">
                        <label for="state" class="col-lg-4 control-label">State</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="state" name="state" placeholder="State" />
                        </div>
                    </div>
                  </div>
                </div>
                
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="hubName" class="col-lg-4 control-label">Hub Name</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="hubName" name="hubName" placeholder="Hub Name" title="Please enter hub name" />
                        </div>
                    </div>
                  </div>
                   <div class="col-md-6">
                    <div class="form-group">
                        <label for="phoneNo" class="col-lg-4 control-label">Phone Number</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="phoneNo" name="phoneNo" placeholder="Phone Number" />
                        </div>
                    </div>
                  </div>
                </div>
               
               <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="address" class="col-lg-4 control-label">Address</label>
                        <div class="col-lg-7">
                        <textarea class="form-control" name="address" id="address" placeholder="Address"></textarea>
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
  
  
  <script type="text/javascript">

  function validateNow(){
    flag = deforayValidator.init({
        formId: 'addFacilityForm'
    });
    
    if(flag){
      document.getElementById('addFacilityForm').submit();
    }
  }
</script>
  
 <?php
 include('footer.php');
 ?>
