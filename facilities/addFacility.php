<?php
ob_start();
include('../header.php');
//include('../includes/MysqliDb.php');
$fQuery="SELECT * FROM facility_type";
$fResult = $db->rawQuery($fQuery);
$pQuery="SELECT * FROM province_details";
$pResult = $db->rawQuery($pQuery);
?>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1><i class="fa fa-gears"></i> Add Facility</h1>
      <ol class="breadcrumb">
        <li><a href="/"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Facilities</li>
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
            <form class="form-horizontal" method='post' name='addFacilityForm' id='addFacilityForm' autocomplete="off" action="addFacilityHelper.php">
              <div class="box-body">
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="facilityName" class="col-lg-4 control-label">Facility Name <span class="mandatory">*</span></label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control isRequired" id="facilityName" name="facilityName" placeholder="Facility Name" title="Please enter facility name" onblur="checkNameValidation('facility_details','facility_name',this,null,'The facility name that you entered already exists.Enter another name',null)"/>
                        </div>
                    </div>
                  </div>
                   <div class="col-md-6">
                    <div class="form-group">
                        <label for="facilityCode" class="col-lg-4 control-label">Facility Code</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="facilityCode" name="facilityCode" placeholder="Facility Code" title="Please enter facility code" onblur="checkNameValidation('facility_details','facility_code',this,null,'The code that you entered already exists.Try another code',null)"/>
                        </div>
                    </div>
                  </div>
                </div>
                
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="otherId" class="col-lg-4 control-label">Other Id </label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="otherId" name="otherId" placeholder="Other Id" />
                        </div>
                    </div>
                  </div>
                    <div class="col-md-6">
                    <div class="form-group">
                        <label for="facilityType" class="col-lg-4 control-label">Facility Type <span class="mandatory">*</span> </label>
                        <div class="col-lg-7">
                        <select class="form-control isRequired" id="facilityType" name="facilityType" title="Please select facility type">
                          <option value=""> -- Select -- </option>
                            <?php
                            foreach($fResult as $type){
                             ?>
                             <option value="<?php echo $type['facility_type_id'];?>"><?php echo ucwords($type['facility_type_name']);?></option>
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
                        <label for="email" class="col-lg-4 control-label">Email(s) </label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="email" name="email" placeholder="eg-f1@gmail.com,f2@gmail.com" />
                        </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="reportEmail" class="col-lg-4 control-label">Report Email(s) </label>
                        <div class="col-lg-7">
                        <textarea class="form-control" id="reportEmail" name="reportEmail" placeholder="eg-user1@gmail.com,user2@gmail.com" rows="3"></textarea>
                        </div>
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="contactPerson" class="col-lg-4 control-label">Contact Person</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="contactPerson" name="contactPerson" placeholder="Contact Person" />
                        </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="phoneNo" class="col-lg-4 control-label">Phone Number</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control checkNum" id="phoneNo" name="phoneNo" placeholder="Phone Number" onblur="checkNameValidation('facility_details','facility_mobile_numbers',this,null,'The mobile no that you entered already exists.Enter another mobile no.',null)"/>
                        </div>
                    </div>
                  </div>
                </div>
                <div class="row">
                   <div class="col-md-6">
                    <div class="form-group">
                        <label for="state" class="col-lg-4 control-label">Province/State <span class="mandatory">*</span></label>
                        <div class="col-lg-7">
                        <select name="state" id="state" class="form-control isRequired" title="Please choose province/state">
                          <option value=""> -- Select -- </option>
                          <?php
                          foreach($pResult as $province){
                            ?>
                            <option value="<?php echo $province['province_name'];?>"><?php echo $province['province_name'];?></option>
                            <?php
                          }
                          ?>
                          <option value="other">Other</option>
                        </select>
                        <input type="text" class="form-control" name="provinceNew" id="provinceNew" placeholder="Enter Province/State" title="Please enter province/state" style="margin-top:4px;display:none;"/>
                        </div>
                    </div>
                  </div>
                   <div class="col-md-6">
                    <div class="form-group">
                        <label for="district" class="col-lg-4 control-label">District/County <span class="mandatory">*</span></label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control isRequired" id="district" name="district" placeholder="District/County" title="Please enter district/county"/>
                        </div>
                    </div>
                  </div>
                </div>
               
               <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="hubName" class="col-lg-4 control-label">Linked Hub Name (If Applicable)</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="hubName" name="hubName" placeholder="Hub Name" title="Please enter hub name" />
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="address" class="col-lg-4 control-label">Address</label>
                        <div class="col-lg-7">
                        <textarea class="form-control" name="address" id="address" placeholder="Address"></textarea>
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
                        <label for="latitude" class="col-lg-4 control-label">Latitude</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control checkNum" id="latitude" name="latitude" placeholder="Latitude" title="Please enter latitude"/>
                        </div>
                    </div>
                  </div>
               </div>
               <div class="row">
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="longitude" class="col-lg-4 control-label">Longitude</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control checkNum" id="longitude" name="longitude" placeholder="Longitude" title="Please enter longitude" />
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
      $.blockUI();
      document.getElementById('addFacilityForm').submit();
    }
  }
  
  function checkNameValidation(tableName,fieldName,obj,fnct,alrt,callback){
        var removeDots=obj.value.replace(/\./g,"");
        var removeDots=removeDots.replace(/\,/g,"");
        //str=obj.value;
        removeDots = removeDots.replace(/\s{2,}/g,' ');

        $.post("../includes/checkDuplicate.php", { tableName: tableName,fieldName : fieldName ,value : removeDots.trim(),fnct : fnct, format: "html"},
        function(data){
            if(data==='1'){
                alert(alrt);
                document.getElementById(obj.id).value="";
            }
        });
  }
  
  $('#state').on('change',function(){
    if(this.value == 'other'){
      $('#provinceNew').show();
      $('#provinceNew').addClass('isRequired');
      $('#provinceNew').focus();
    }else{
      $('#provinceNew').hide();
      $('#provinceNew').removeClass('isRequired');
      $('#provinceNew').val('');
    }
  });
  </script>
  
 <?php
 include('../footer.php');
 ?>