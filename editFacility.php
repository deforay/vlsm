<?php
ob_start();
include('header.php');
include('./includes/MysqliDb.php');
$id=base64_decode($_GET['id']);
$facilityQuery="SELECT * from facility_details where facility_id=$id";
$facilityInfo=$db->query($facilityQuery);
$fQuery="SELECT * FROM facility_type";
$fResult = $db->rawQuery($fQuery);
?>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>Edit Facility</h1>
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
            <form class="form-horizontal" method='post'  name='editFacilityForm' id='editFacilityForm' autocomplete="off" action="editFacilityHelper.php">
              <div class="box-body">
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="facilityName" class="col-lg-4 control-label">Facility Name <span class="mandatory">*</span></label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control isRequired" id="facilityName" name="facilityName" placeholder="Facility Name" title="Please enter facility name" value="<?php echo $facilityInfo[0]['facility_name']; ?>" />
                        <input type="hidden" class="form-control isRequired" id="facilityId" name="facilityId" value="<?php echo base64_encode($facilityInfo[0]['facility_id']); ?>" />
                        </div>
                    </div>
                  </div>
                   <div class="col-md-6">
                    <div class="form-group">
                        <label for="facilityCode" class="col-lg-4 control-label">Facility Code <span class="mandatory">*</span> </label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control isRequired" id="facilityCode" name="facilityCode" placeholder="Facility Code" title="Please enter facility code" value="<?php echo $facilityInfo[0]['facility_code']; ?>" />
                        </div>
                    </div>
                  </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                    <div class="form-group">
                        <label for="email" class="col-lg-4 control-label">Email </label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control isEmail" id="email" name="email" placeholder="Email" value="<?php echo $facilityInfo[0]['email']; ?>"/>
                        </div>
                    </div>
                  </div>
                   <div class="col-md-6">
                    <div class="form-group">
                        <label for="contactPerson" class="col-lg-4 control-label">Contact Person</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="contactPerson" name="contactPerson" placeholder="Contact Person" value="<?php echo $facilityInfo[0]['contact_person']; ?>" />
                        </div>
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="phoneNo" class="col-lg-4 control-label">Phone Number</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="phoneNo" name="phoneNo" placeholder="Phone Number" value="<?php echo $facilityInfo[0]['phone_number']; ?>" />
                        </div>
                    </div>
                  </div>
                   <div class="col-md-6">
                    <div class="form-group">
                        <label for="state" class="col-lg-4 control-label">State</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="state" name="state" placeholder="State" value="<?php echo $facilityInfo[0]['state']; ?>" />
                        </div>
                    </div>
                  </div>
                </div>
                
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="hubName" class="col-lg-4 control-label">Linked Hub Name (If Applicable)</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="hubName" name="hubName" placeholder="Hub Name" title="Please enter hub name" value="<?php echo $facilityInfo[0]['hub_name']; ?>"/>
                        </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="address" class="col-lg-4 control-label">Address</label>
                        <div class="col-lg-7">
                        <textarea class="form-control" name="address" id="address" placeholder="Address"><?php echo $facilityInfo[0]['address']; ?></textarea>
                        </div>
                    </div>
                  </div>
                </div>
               
               <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="country" class="col-lg-4 control-label">Country</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="country" name="country" placeholder="Country" value="<?php echo $facilityInfo[0]['country']; ?>"/>
                        </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="address" class="col-lg-4 control-label">Facility Type</label>
                        <div class="col-lg-7">
                        <select class="form-control" id="facilityType" name="facilityType" title="Please select facility type">
			  <option value="">--select--</option>
			    <?php
			    foreach($fResult as $type){
			     ?>
			     <option value="<?php echo $type['facility_type_id'];?>"<?php echo ($facilityInfo[0]['facility_type']==$type['facility_type_id'])?"selected='selected'":""?>><?php echo ucwords($type['facility_type_name']);?></option>
			     <?php
			    }
			    ?>
			  </select>
                        </div>
                    </div>
                  </div>
               
               
              </div>
               <div class="row">
                 <div class="col-md-6">
                    <div class="form-group">
                        <label for="status" class="col-lg-4 control-label">Status <span class="mandatory">*</span></label>
                        <div class="col-lg-7">
                          <select class="form-control isRequired" name='status' id='status' title="Please select the status">
                            <option value="">--Select--</option>
                            <option value="active" <?php echo ($facilityInfo[0]['status']=='active')?"selected='selected'":""?>>Active</option>
                            <option value="inactive" <?php echo ($facilityInfo[0]['status']=='inactive')?"selected='selected'":""?>>Inactive</option>
                          </select>
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
        formId: 'editFacilityForm'
    });
    
    if(flag){
      document.getElementById('editFacilityForm').submit();
    }
  }
</script>
  
 <?php
 include('footer.php');
 ?>
