<?php
ob_start();
include('../header.php');
$id=base64_decode($_GET['id']);
$userQuery="SELECT * from user_details where user_id='".$id."'";
$userInfo=$db->query($userQuery);
$query="SELECT * FROM roles where status='active'";
$result = $db->rawQuery($query);
$fResult = array();
$display = 'display:none';
if($sarr['user_type']=='remoteuser'){
//get all facility list with lab,clinic
$fQuery="SELECT facility_name,facility_id FROM facility_details where facility_type IN('1,4')";
$fResult = $db->rawQuery($fQuery);
$display = 'display:block';
}
$selectedQuery="SELECT * FROM vl_user_facility_map as vlfm join user_details as ud ON ud.user_id=vlfm.user_id join facility_details as fd ON fd.facility_id=vlfm.facility_id where vlfm.user_id = '".$id."'";
$selectedResult = $db->rawQuery($selectedQuery);

?>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1> <i  class="fa fa-gears"></i> Edit User</h1>
      <ol class="breadcrumb">
        <li><a href="/"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Users</li>
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
            <form class="form-horizontal" method='post'  name='userEditForm' id='userEditForm' autocomplete="off" action="editUserHelper.php">
              <div class="box-body">
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="userName" class="col-lg-4 control-label">User Name <span class="mandatory">*</span></label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control isRequired" id="userName" name="userName" placeholder="User Name" title="Please enter user name" value="<?php echo $userInfo[0]['user_name']; ?>"/>
                        <input type="hidden" name="userId" id="userId" value="<?php echo base64_encode($userInfo[0]['user_id']);?>"/>
                        </div>
                    </div>
                  </div>
                   <div class="col-md-6">
                    <div class="form-group">
                        <label for="email" class="col-lg-4 control-label">Email </label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="email" name="email" placeholder="Email" title="Please enter email" value="<?php echo $userInfo[0]['email']; ?>" onblur="checkNameValidation('user_details','email',this,'<?php echo "user_id##".$userInfo[0]['user_id'];?>','This email id that you entered already exists.Try another email id',null)"/>
                        </div>
                    </div>
                  </div>
                </div>
                <div class="row">
                   <div class="col-md-6">
                    <div class="form-group">
                        <label for="phoneNo" class="col-lg-4 control-label">Phone Number</label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" id="phoneNo" name="phoneNo" placeholder="Phone Number" title="Please enter phone number" value="<?php echo $userInfo[0]['phone_number']; ?>"/>
                        </div>
                    </div>
                  </div>
                  
                   <div class="col-md-6">
                    <div class="form-group">
                        <label for="role" class="col-lg-4 control-label">Role <span class="mandatory">*</span></label>
                        <div class="col-lg-7">
                        <select class="form-control isRequired" name='role' id='role' title="Please select the role">
                        <option value=""> -- Select -- </option>
                        <?php
                        foreach ($result as $row) {
                        ?>
                        <option value="<?php echo $row['role_id']; ?>" <?php echo ($userInfo[0]['role_id']==$row['role_id'])?"selected='selected'":""?>><?php echo ucwords($row['role_name']); ?></option>
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
                        <label for="loginId" class="col-lg-4 control-label">Login Id <span class="mandatory">*</span></label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control isRequired" id="loginId" name="loginId" placeholder="Login Id" title="Please enter login id" value="<?php echo $userInfo[0]['login_id']; ?>" onblur="checkNameValidation('user_details','login_id',this,'<?php echo "user_id##".$userInfo[0]['user_id'];?>','This login id that you entered already exists.Try another login id',null)"/>
                        </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="password" class="col-lg-4 control-label">Password </label>
                        <div class="col-lg-7">
                           <input type="password" class="form-control ppwd" id="confirmPassword" name="password" placeholder="Password" title="Please enter the password"/>
                           <code>Password must be at least 8 characters long and must include AT LEAST one number, one alphabet and may have special characters.</code>
                        </div>
                    </div>
                  </div>
                </div>
                
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="confirmPassword" class="col-lg-4 control-label">Confirm Password</label>
                        <div class="col-lg-7">
                        <input type="password" class="form-control cpwd confirmPassword" id="confirmPassword" name="password" placeholder="Confirm Password" title="" />
                        </div>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="status" class="col-lg-4 control-label">Status <span class="mandatory">*</span></label>
                        <div class="col-lg-7">
                          <select class="form-control isRequired" name='status' id='status' title="Please select the status">
                            <option value=""> -- Select -- </option>
                            <option value="active" <?php echo ($userInfo[0]['status']=='active')?"selected='selected'":""?>>Active</option>
                            <option value="inactive" <?php echo ($userInfo[0]['status']=='inactive')?"selected='selected'":""?>>Inactive</option>
                          </select>
                        </div>
                    </div>
                  </div>

                </div>
               
                <div class="row" style="<?php echo $display;?>" >
                <h4>Facility User Map Details</h4>
                  <div class="col-xs-5">
                      <select name="from[]" id="search" class="form-control" size="8" multiple="multiple">
                          <?php
                          if($fResult>0){
                            foreach($fResult as $fName){
                                ?>
                                    <option value="<?php echo $fName['facility_id'];?>"><?php echo ucwords($fName['facility_name']);?></option>
                                <?php
                            }
                          }
                          ?>
                      </select>
                  </div>

                  <div class="col-xs-2">
                      <button type="button" id="search_rightAll" class="btn btn-block"><i class="glyphicon glyphicon-forward"></i></button>
                      <button type="button" id="search_rightSelected" class="btn btn-block"><i class="glyphicon glyphicon-chevron-right"></i></button>
                      <button type="button" id="search_leftSelected" class="btn btn-block"><i class="glyphicon glyphicon-chevron-left"></i></button>
                      <button type="button" id="search_leftAll" class="btn btn-block"><i class="glyphicon glyphicon-backward"></i></button>
                  </div>

                  <div class="col-xs-5">
                      <select name="to[]" id="search_to" class="form-control" size="8" multiple="multiple">
                      <?php
                          foreach($selectedResult as $uName){
                              ?>
                                  <option value="<?php echo $uName['facility_id'];?>" selected="selected"><?php echo ucwords($uName['facility_name']);?></option>
                              <?php
                          }
                          ?>
                      </select>
                  </div>
                </div>
              </div>
              
              <!-- /.box-body -->
              <div class="box-footer">
              <input type="hidden" name="selectedFacility" id="selectedFacility"/>
                <a class="btn btn-primary" href="javascript:void(0);" onclick="validateNow();return false;">Submit</a>
                <a href="users.php" class="btn btn-default"> Cancel</a>
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
  <script type="text/javascript" src="//crlcu.github.io/multiselect/dist/js/multiselect.min.js"></script>
  <script type="text/javascript">
  jQuery(document).ready(function($) {
    $('#search').multiselect({
        search: {
            left: '<input type="text" name="q" class="form-control" placeholder="Search..." />',
            right: '<input type="text" name="q" class="form-control" placeholder="Search..." />',
        },
        fireSearch: function(value) {
            return value.length > 3;
        }
    });
    });
    pwdflag = true;
    function validateNow(){
      var selVal = []; 
    $('#search_to option').each(function(i, selected){
      selVal[i] = $(selected).val(); 
    });
    $("#selectedFacility").val(selVal);
    
      flag = deforayValidator.init({
          formId: 'userEditForm'
      });
      
      if(flag){
        if($('.ppwd').val() != ''){
          pwdflag = checkPasswordLength();
        }
        if(pwdflag){
          $.blockUI();
          document.getElementById('userEditForm').submit();
        }
      }
    }
    
    function checkNameValidation(tableName,fieldName,obj,fnct,alrt,callback){
          var removeDots=obj.value.replace(/\,/g,"");
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
    
    function checkPasswordLength(){
      var pwd = $('#confirmPassword').val();
      var regex = /^(?=.*[0-9])(?=.*[a-zA-Z])([a-zA-Z0-9!@#\$%\^\&*\)\(+=. _-]+){8,}$/;
      if(regex.test(pwd) == false){
        alert('Password must be at least 8 characters long and must include AT LEAST one number, one alphabet and may have special characters.');
        $('.ppwd').focus();
      }
      return regex.test(pwd);
    }
 </script>
 <?php
 include('../footer.php');
 ?>