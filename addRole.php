<?php
ob_start();
include('header.php');
$resourcesQuery="SELECT * from resources";
$rInfo=$db->query($resourcesQuery);
?>
<style>
    .labelName{font-size: 13px;}
</style>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>Add Role</h1>
      <ol class="breadcrumb">
        <li><a href="index.php"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Add Role</li>
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
            <form class="form-horizontal" method='post'  name='roleAddForm' id='roleAddForm' autocomplete="off" action="addRolesHelper.php">
              <div class="box-body">
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="userName" class="col-lg-4 control-label">Role Name <span class="mandatory">*</span></label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control isRequired" id="roleName" name="roleName" placeholder="Role Name" title="Please enter user name"  onblur="checkNameValidation('roles','role_name',this,null,'This role name already exists.Try another role name',null)"/>
                        </div>
                    </div>
                  </div>
                   <div class="col-md-6">
                    <div class="form-group">
                        <label for="email" class="col-lg-4 control-label">Role Code <span class="mandatory">*</span></label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control isRequired" id="roleCode" name="roleCode" placeholder="Role Code" title="Please enter role code" onblur="checkNameValidation('roles','role_code',this,null,'This role code already exists.Try another role code',null)"/>
                        </div>
                    </div>
                  </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                    <div class="form-group">
                        <label for="landingPage" class="col-lg-4 control-label">Landing Page</label>
                        <div class="col-lg-7">
                          <select class="form-control isRequired" name='landingPage' id='landingPage' title="Please select landing page">
                            <option value=""> -- Select -- </option>
                            <option value="index.php">Dashboard</option>
                            <option value="addVlRequest.php">Add New Request</option>
                            <option value="addImportResult.php">Add Import Result</option>
                          </select>
                        </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                        <label for="status" class="col-lg-4 control-label">Status <span class="mandatory">*</span></label>
                        <div class="col-lg-7">
                          <select class="form-control isRequired" name='status' id='status' title="Please select the status">
                            <option value=""> -- Select -- </option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                          </select>
                        </div>
                    </div>
                  </div>
                </div>
                
                <fieldset>
                        <div class="form-group">
                                <label class="col-sm-2 control-label">Note:</label>
                                <div class="col-sm-10">
                                        <p class="form-control-static">Unless you choose "access" the people belonging to this role will not be able to access other rights like "add", "edit" etc.</p>
                                </div>
                        </div>
                        <div class="form-group" style="padding-left:138px;">
                        <strong>Select All</strong> <a style="color: #333;" href="javascript:void(0);" id="cekAllPrivileges"><input type='radio' class='layCek' name='cekUnCekAll'/> <i class='fa fa-check'></i></a>
                        &nbsp&nbsp&nbsp&nbsp<strong>Unselect All</strong> <a style="color: #333;" href="javascript:void(0);" id="unCekAllPrivileges"><input type='radio' class='layCek' name='cekUnCekAll'/> <i class='fa fa-times'></i></a>
                        </div>
                        <table class="table table-striped table-hover responsive-utilities jambo_table">
                        <?php
                        foreach ($rInfo as $value) {
                          
                          echo "<tr class=''>";
                          echo "<td><strong>" . ucwords($value['display_name']) . "</strong></td>";
                          $pQuery="SELECT * from privileges where resource_id='".$value['resource_id']."'";
                          $pInfo=$db->query($pQuery);
                          foreach ($pInfo as $privilege) {
                            //if (in_array($privilege['privilege_id'], $priId)){
                            //  $allowChecked = " checked='' ";
                            //  $denyChecked = "";
                            //  } else {
                            //  $denyChecked = " checked='' ";
                            //  $allowChecked = "";
                            //  }
                          echo "<td>"
                          . "<label class='labelName'>" . ucwords($privilege['display_name']) . "</label>
                            <label>
                                    <input type='radio' class='cekAll layCek'  name='resource[" . $privilege['privilege_id'] . "]" . "' value='allow' > <i class='fa fa-check'></i>
                            </label>
                            <label>
                                    <input type='radio' class='unCekAll layCek'  name='resource[" . $privilege['privilege_id'] . "]" . "' value='deny' >  <i class='fa fa-times'></i>
                            </label>
                            </td>";
                            }
                            echo "</tr>";
                            }
                            ?>	
                          </table>
                          </fieldset>
                
                
              </div>
              <!-- /.box-body -->
              <div class="box-footer">
                <a class="btn btn-primary" href="javascript:void(0);" onclick="validateNow();return false;">Submit</a>
                <a href="roles.php" class="btn btn-default"> Cancel</a>
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
        formId: 'roleAddForm'
    });
    
    if(flag){
      $.blockUI();
      document.getElementById('roleAddForm').submit();
    }
  }
  
  $("#cekAllPrivileges").click(function() {
       $('.unCekAll').prop('checked', false);
       $('.cekAll').prop('checked', true);
   });

   $("#unCekAllPrivileges").click(function() {
       $('.cekAll').prop('checked', false);
       $('.unCekAll').prop('checked', true);
       
   });
    
  function checkNameValidation(tableName,fieldName,obj,fnct,alrt,callback){
        var removeDots=obj.value.replace(/\./g,"");
        var removeDots=removeDots.replace(/\,/g,"");
        //str=obj.value;
        removeDots = removeDots.replace(/\s{2,}/g,' ');

        $.post("checkDuplicate.php", { tableName: tableName,fieldName : fieldName ,value : removeDots.trim(),fnct : fnct, format: "html"},
        function(data){
            if(data==='1'){
                alert(alrt);
                document.getElementById(obj.id).value="";
            }
        });
  }
</script>
  
 <?php
 include('footer.php');
 ?>
