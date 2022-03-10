<?php
ob_start();
#require_once('../startup.php'); 
require_once(APPLICATION_PATH . '/header.php');
$query = "SELECT * FROM roles where status='active' GROUP BY role_code";
$result = $db->rawQuery($query);

$fResult = array();
$display = 'display:none';
if ($_SESSION['instanceType'] == 'remoteuser') {
     //get all facility list with lab,clinic
     $fQuery = "SELECT facility_name,facility_id FROM facility_details";
     $fResult = $db->rawQuery($fQuery);
     $display = 'display:block';
}
//province Stratt
$rKey = '';
$pdQuery = "SELECT * from geographical_divisions WHERE geo_parent = 0";
$pdResult = $db->query($pdQuery);
$province = '';
$province .= "<option value=''> -- Select -- </option>";
foreach ($pdResult as $provinceName) {
     $province .= "<option value='" . $provinceName['geo_name'] . "##" . $provinceName['geo_code'] . "'>" . ucwords($provinceName['geo_name']) . "</option>";
}

// $facility = '';
// $facility.="<option data-code='' data-emails='' data-mobile-nos='' data-contact-person='' value=''> -- Select -- </option>";
//province end
$fQuery = "SELECT * FROM facility_type where facility_type_id IN(1,2)";
$ftResult = $db->rawQuery($fQuery);

?>
<link href="/assets/css/jasny-bootstrap.min.css" rel="stylesheet" />
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
     <!-- Content Header (Page header) -->
     <section class="content-header">
          <h1><i class="fa fa-user"></i> <?php echo _("Add User"); ?></h1>
          <ol class="breadcrumb">
               <li><a href="/"><i class="fa fa-dashboard"></i> <?php echo _("Home"); ?></a></li>
               <li class="active"><?php echo _("Users"); ?></li>
          </ol>
     </section>

     <!-- Main content -->
     <section class="content">

          <div class="box box-default">
               <div class="box-header with-border">
                    <div class="pull-right" style="font-size:15px;"><span class="mandatory">*</span> <?php echo _("indicates required field"); ?> &nbsp;</div>
               </div>
               <!-- /.box-header -->
               <div class="box-body">
                    <!-- form start -->
                    <form class="form-horizontal" method='post' name='userForm' id='userForm' autocomplete="off" action="addUserHelper.php" enctype="multipart/form-data">
                         <div class="box-body">
                              <div class="row">
                                   <div class="col-md-6">
                                        <div class="form-group">
                                             <label for="userName" class="col-lg-4 control-label"><?php echo _("Full Name"); ?> <span class="mandatory">*</span></label>
                                             <div class="col-lg-7">
                                                  <input type="text" class="form-control isRequired" id="userName" name="userName" placeholder="<?php echo _('Full Name'); ?>" title="<?php echo _('Please enter user name'); ?>" />
                                             </div>
                                        </div>
                                   </div>
                                   <div class="col-md-6">
                                        <div class="form-group">
                                             <label for="email" class="col-lg-4 control-label"><?php echo _("Email"); ?> </label>
                                             <div class="col-lg-7">
                                                  <input type="text" class="form-control" id="email" name="email" placeholder="<?php echo _('Email'); ?>" title="<?php echo _('Please enter email'); ?>" onblur='checkNameValidation("user_details","email",this,null,"<?php echo _("This email id that you entered already exists.Try another email id"); ?>",null)' />
                                             </div>
                                        </div>
                                   </div>
                              </div>
                              <div class="row">
                                   <div class="col-md-6">
                                        <div class="form-group">
                                             <label for="phoneNo" class="col-lg-4 control-label"><?php echo _("Phone Number"); ?> </label>
                                             <div class="col-lg-7">
                                                  <input type="text" class="form-control" id="phoneNo" name="phoneNo" placeholder="<?php echo _('Phone Number'); ?>" title="<?php echo _('Please enter phone number'); ?>" />
                                             </div>
                                        </div>
                                   </div>
                                   <div class="col-md-6">
                                        <div class="form-group">
                                             <label for="role" class="col-lg-4 control-label"><?php echo _("Role"); ?> <span class="mandatory">*</span></label>
                                             <div class="col-lg-7">
                                                  <select class="form-control isRequired" name='role' id='role' title="<?php echo _('Please select the role'); ?>">
                                                       <option value=""><?php echo _("--Select--"); ?></option>
                                                       <?php foreach ($result as $row) { ?>
                                                            <option value="<?php echo $row['role_id']; ?>" data-code="<?php echo $row['role_code']; ?>"><?php echo ucwords(($row['role_name'])); ?></option>
                                                       <?php } ?>
                                                  </select>
                                             </div>
                                        </div>
                                   </div>
                              </div>
                              <div class="row show-token" style="display: none;">
                                   <div class="col-md-6">
                                        <div class="form-group">
                                             <label for="authToken" class="col-lg-4 control-label"><?php echo _("AuthToken"); ?> <span class="mandatory">*</span></label>
                                             <div class="col-lg-7">
                                                  <input type="text" class="form-control" id="authToken" name="authToken" placeholder="<?php echo _('Auth Token'); ?>" title="<?php echo _('Please Generate the auth token'); ?>" readonly>
                                             </div>
                                        </div>
                                   </div>
                                   <div class="col-md-6">
                                        <div class="form-group">
                                             <a href="javascript:void(0);" class="btn btn-sm btn-primary" onclick="generateToken('authToken');"><?php echo _("Generate Token"); ?></a>
                                        </div>
                                   </div>
                              </div>
                              <div class="row">
                                   <div class="col-md-6">
                                        <div class="form-group">
                                             <label for="interfaceUserName" class="col-lg-4 control-label"><?php echo _("Interface User Name (from your Molecular testing machine)"); ?></label>
                                             <div class="col-lg-7">
                                                  <input type="text" class="form-control" id="interfaceUserName" name="interfaceUserName" placeholder="<?php echo _('Interface User Name'); ?>" title="<?php echo _('Please enter interface user name'); ?>" />
                                             </div>
                                        </div>
                                   </div>
                                   <div class="col-md-6">
                                        <div class="form-group">
                                             <label for="appAccessable" class="col-lg-4 control-label"><?php echo _("Mobile App Access"); ?></label>
                                             <div class="col-lg-7">
                                                  <select class="form-control" name='appAccessable' id='appAccessable' title="<?php echo _('Please select the mobile App access or not'); ?>?">
                                                       <option value=""><?php echo _("--Select--"); ?></option>
                                                       <option value="yes"><?php echo _("Yes"); ?></option>
                                                       <option value="no"><?php echo _("No"); ?></option>
                                                  </select>
                                             </div>
                                        </div>
                                   </div>
                              </div>

                              <div class="row">
                                   <div class="col-md-6">
                                        <div class="form-group">
                                             <label for="" class="col-lg-4 control-label"><?php echo _("Signature"); ?> <br><?php echo _("(Used to embed in Result PDF)"); ?></label>
                                             <div class="col-lg-8">
                                                  <div class="fileinput fileinput-new userSignature" data-provides="fileinput">
                                                       <div class="fileinput-preview thumbnail" data-trigger="fileinput" style="width:200px; height:150px;">

                                                            <img src="http://www.placehold.it/200x150/EFEFEF/AAAAAA&text=No image">

                                                       </div>
                                                       <div>
                                                            <span class="btn btn-default btn-file"><span class="fileinput-new"><?php echo _("Select Signature Image"); ?></span><span class="fileinput-exists"><?php echo _("Change"); ?></span>
                                                                 <input type="file" id="userSignature" name="userSignature" title="<?php echo _('Please select user signature'); ?>" onchange="">
                                                            </span>

                                                            <a href="#" class="btn btn-default fileinput-exists" data-dismiss="fileinput"><?php echo _("Remove"); ?></a>
                                                       </div>
                                                  </div>
                                                  <div class="box-body">
                                                       <?php echo _("Image Size"); ?> : <code>100px x 100px</code>
                                                  </div>
                                             </div>
                                        </div>
                                   </div>
                                   <div class="col-md-6">
                                        <div class="form-group">
                                             <label for="loginId" class="col-lg-4 control-label"><?php echo _("Login ID"); ?> <span class="mandatory">*</span></label>
                                             <div class="col-lg-7">
                                                  <input type="text" class="form-control isRequired" id="loginId" name="loginId" placeholder="<?php echo _('Login Id'); ?>" title="<?php echo _('Please enter login id'); ?>" onblur='checkNameValidation("user_details","login_id",this,null,"<?php echo _("This login id that you entered already exists.Try another login id"); ?>",null)' />
                                             </div>
                                        </div>
                                   </div>
                              </div>
                              <div class="row">
                                   <div class="col-md-6">
                                        <div class="form-group">
                                             <label for="password" class="col-lg-4 control-label"><?php echo _("Password"); ?> <span class="mandatory">*</span></label>
                                             <div class="col-lg-7">
                                                  <input type="password" class="form-control ppwd isRequired" id="confirmPassword" name="password" placeholder="<?php echo _('Password'); ?>" title="<?php echo _('Please enter the password'); ?>" />
                                                  <code><?= _("Password must be at least 8 characters long and must include AT LEAST one number, one alphabet and may have special characters.") ?></code>
                                             </div>
                                        </div>
                                   </div>
                                   <div class="col-md-6">
                                        <div class="form-group">
                                             <label for="confirmPassword" class="col-lg-4 control-label"><?php echo _("Confirm Password"); ?> <span class="mandatory">*</span></label>
                                             <div class="col-lg-7">
                                                  <input type="password" class="form-control cpwd isRequired confirmPassword" id="confirmPassword" name="password" placeholder="<?php echo _('Confirm Password'); ?>" title="" />
                                             </div>
                                        </div>
                                   </div>
                              </div>
                              <div class="row" style=<?php echo $display; ?>>
                                   <div class="col-md-12">
                                        <a href="javascript:void(0);" id="showFilter" class="btn btn-primary"><?php echo _("Show Advanced Filter"); ?></a>
                                        <a href="javascript:void(0);" style="display:none;" id="hideFilter" class="btn btn-danger"><?php echo _("Hide Advanced Filter"); ?></a>
                                   </div>
                                   <div id="facilityFilter" style="display:none;">
                                        <h4 style="padding:36px 0px 0px 14px;"> <?php echo _("Filter Facilities by Province & Districts"); ?></h4>
                                        <div class="col-md-4">
                                             <div class="form-group">
                                                  <label for="province" class="col-lg-4 control-label"><?php echo _("Province"); ?> </label>
                                                  <div class="col-lg-7">
                                                       <select class="form-control " name="province" id="province" title="<?php echo _('Please choose province'); ?>" style="width:100%;" onchange="getProvinceDistricts();">
                                                            <?php echo $province; ?>
                                                       </select>
                                                  </div>
                                             </div>
                                        </div>
                                        <div class="col-md-4">
                                             <div class="form-group">
                                                  <label for="province" class="col-lg-4 control-label"><?php echo _("District"); ?> </label>
                                                  <div class="col-lg-7">
                                                       <select class="form-control " name="district" id="district" title="<?php echo _('Please choose district'); ?>" style="width:100%;" onchange="getFacilities();">
                                                            <option value=""> <?php echo _("-- Select --"); ?> </option>
                                                       </select>
                                                  </div>
                                             </div>
                                        </div>
                                        <div class="col-md-4">
                                             <div class="form-group">
                                                  <label for="province" class="col-lg-4 control-label"><?php echo _("Facility Type"); ?> </label>
                                                  <div class="col-lg-7">
                                                       <select class="form-control" id="facilityType" name="facilityType" title="<?php echo _('Please select facility type'); ?>" onchange="getFacility()">
                                                            <option value=""> <?php echo _("-- Select --"); ?> </option>
                                                            <?php
                                                            foreach ($ftResult as $type) {
                                                            ?>
                                                                 <option value="<?php echo $type['facility_type_id']; ?>"><?php echo ucwords($type['facility_type_name']); ?></option>
                                                            <?php
                                                            }
                                                            ?>
                                                       </select>
                                                  </div>
                                             </div>
                                        </div>
                                   </div>
                              </div>

                              <div class="row" style="margin: 15px;<?php echo $display; ?>">
                                   <h4> <?php echo _("Facility User Map Details"); ?></h4>
                                   <div class="col-md-5">
                                        <!-- <div class="col-lg-5"> -->

                                        <select name="facilityMap[]" id="search" class="form-control" size="8" multiple="multiple">
                                             <?php
                                             if ($fResult > 0) {
                                                  foreach ($fResult as $fName) {
                                             ?>
                                                       <option value="<?php echo $fName['facility_id']; ?>"><?php echo ucwords($fName['facility_name']); ?></option>
                                             <?php
                                                  }
                                             }
                                             ?>
                                        </select>
                                   </div>

                                   <div class="col-md-2">
                                        <button type="button" id="search_rightAll" class="btn btn-block"><i class="glyphicon glyphicon-forward"></i></button>
                                        <button type="button" id="search_rightSelected" class="btn btn-block"><i class="glyphicon glyphicon-chevron-right"></i></button>
                                        <button type="button" id="search_leftSelected" class="btn btn-block"><i class="glyphicon glyphicon-chevron-left"></i></button>
                                        <button type="button" id="search_leftAll" class="btn btn-block"><i class="glyphicon glyphicon-backward"></i></button>
                                   </div>

                                   <div class="col-md-5">
                                        <select name="to[]" id="search_to" class="form-control" size="8" multiple="multiple"></select>
                                   </div>
                              </div>

                         </div>
                         <!-- /.box-body -->
                         <div class="box-footer">
                              <input type="hidden" name="selectedFacility" id="selectedFacility" />
                              <a class="btn btn-primary" href="javascript:void(0);" onclick="validateNow();return false;"><?php echo _("Submit"); ?></a>
                              <a href="users.php" class="btn btn-default"> <?php echo _("Cancel"); ?></a>
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
<script type="text/javascript" src="/assets/js/multiselect.min.js"></script>
<script type="text/javascript" src="/assets/js/jasny-bootstrap.js"></script>
<script type="text/javascript">
     provinceName = true;
     facilityName = true;

     jQuery(document).ready(function($) {
          $('#search').multiselect({
               search: {
                    left: '<input type="text" name="q" class="form-control" placeholder="<?php echo _("Search"); ?>..." />',
                    right: '<input type="text" name="q" class="form-control" placeholder="<?php echo _("Search"); ?>..." />',
               },
               fireSearch: function(value) {
                    return value.length > 3;
               }
          });
          $("#showFilter").click(function() {
               $("#showFilter").hide();
               $("#facilityFilter,#hideFilter").fadeIn();
          });

          $("#hideFilter").click(function() {
               $("#facilityFilter,#hideFilter").hide();
               $("#showFilter").fadeIn();
          });

          $('#role').change(function(e) {
               $('#authToken').val('');
               var selectedText = $(this).find("option:selected").attr('data-code');
               if (selectedText == "API") {
                    $('.show-token').show();
                    $('#authToken').addClass('isRequired');
               } else {
                    $('.show-token').hide();
                    $('#authToken').removeClass('isRequired');
               }
          });

          $('#userSignature').change(function(e) {
               const file = this.files[0];
               const fileType = file['type'];
               const validImageTypes = ['image/jpg', 'image/jpeg', 'image/png'];
               if (!validImageTypes.includes(fileType)) {
                    $('#userSignature').val('');
                    alert('<?php echo _("Invalid file type. Please upload correct image format like JPG or JPEG or PNG"); ?>');
                    return false;
               }
          });
     });

     function validateNow() {
          var selVal = [];
          $('#search_to option').each(function(i, selected) {
               selVal[i] = $(selected).val();
          });
          $("#selectedFacility").val(selVal);

          flag = deforayValidator.init({
               formId: 'userForm'
          });
          if (flag) {
               pwdflag = checkPasswordLength();
               if (pwdflag) {
                    $.blockUI();
                    document.getElementById('userForm').submit();
               }
          }
     }

     function checkNameValidation(tableName, fieldName, obj, fnct, alrt, callback) {
          var removeDots = obj.value.replace(/\,/g, "");
          //str=obj.value;
          removeDots = removeDots.replace(/\s{2,}/g, ' ');
          $.post("/includes/checkDuplicate.php", {
                    tableName: tableName,
                    fieldName: fieldName,
                    value: removeDots.trim(),
                    fnct: fnct,
                    format: "html"
               },
               function(data) {
                    if (data === '1') {
                         alert(alrt);
                         document.getElementById(obj.id).value = "";
                    }
               });
     }

     function checkPasswordLength() {
          var pwd = $('#confirmPassword').val();
          var regex = /^(?=.*[0-9])(?=.*[a-zA-Z])([a-zA-Z0-9!@#\$%\^\&*\)\(+=. _-]+){8,}$/;
          if (regex.test(pwd) == false) {
               alert("<?= _("Password must be at least 8 characters long and must include AT LEAST one number, one alphabet and may have special characters.") ?>");
               $('.ppwd').focus();
          }
          return regex.test(pwd);
     }

     function getProvinceDistricts() {
          $.blockUI();
          var pName = $("#province").val();
          if (pName != '') {
               $.post("/includes/siteInformationDropdownOptions.php", {
                         pName: pName,
                         fType: $("#facilityType").val(),
                         comingFromUser: 'yes'
                    },
                    function(data) {
                         if (data != "") {
                              details = data.split("###");
                              $("#district").html(details[1]);
                              $("#search").html(details[0]);
                         }
                    });
          }
          $.unblockUI();
     }

     function getFacilities() {
          $.blockUI();
          var dName = $("#district").val();
          if (dName != '') {
               $.post("/includes/siteInformationDropdownOptions.php", {
                         dName: dName,
                         fType: $("#facilityType").val(),
                         comingFromUser: 'yes'
                    },
                    function(data) {
                         if (data != "") {
                              details = data.split("###");
                              $("#search").html(details[0]);
                         }
                    });
          }
          $.unblockUI();
     }

     function getFacility() {
          $.blockUI();
          var pName = $("#province").val();
          var dName = $("#district").val();
          var fType = $("#facilityType").val();
          if (dName != '') {
               getFacilities();
          } else if (pName != '') {
               getProvinceDistricts();
          } else if (fType != '') {
               $.post("/includes/siteInformationDropdownOptions.php", {
                         fType: fType,
                         comingFromUser: 'yes'
                    },
                    function(data) {
                         $("#search").html(data);
                    });
          }
          $.unblockUI();
     }

     function generateToken(id) {
          $.post("/includes/generate-auth-token.php", {
                    size: 32
               },
               function(data) {
                    if (data != "") {
                         $("#" + id).val(data);
                    }
               });
     }
</script>
<?php
require_once(APPLICATION_PATH . '/footer.php');
?>