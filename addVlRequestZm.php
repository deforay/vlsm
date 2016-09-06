<?php
ob_start();
include('header.php');
include('./includes/MysqliDb.php');
$query="SELECT * FROM roles where status='active'";
$result = $db->rawQuery($query);
?>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>VIRAL LOAD LABORATORY REQUEST FORM</h1>
      <ol class="breadcrumb">
        <li><a href="index.php"><i class="fa fa-dashboard"></i> Home</a></li>
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
            <form class="form-inline" method='post'  name='userForm' id='userForm' autocomplete="off" action="addUserHelper.php">
              <div class="box-body">
                <div class="box box-default">
                  <div class="box-body">
                    <div class="row">
                      <div class="col-xs-3 col-md-3">
                        <div class="form-group">
                        <label for="exampleInputEmail1">Province <span class="mandatory">*</span></label>
                        <input type="email" class="form-control" placeholder="Province" style="width:100%;">
                        </div>
                      </div>
                      <div class="col-xs-3 col-md-3">
                        <div class="form-group">
                        <label for="exampleInputEmail1">District <span class="mandatory">*</span></label>
                        <input type="email" class="form-control" placeholder="District" style="width:100%;">
                        </div>
                      </div>
                      <div class="col-xs-3 col-md-3">
                        <div class="form-group">
                        <label for="exampleInputEmail1">Urgency <span class="mandatory">*</span></label>
                        <label class="radio-inline">
                             <input type="radio" class="" id="genderMale" name="gender" value="male" title="Please check gender"> Normal
                        </label>
                        <label class="radio-inline">
                             <input type="radio" class="isRequired" id="genderFemale" name="gender" value="female" title="Please check gender"> Urgent
                        </label>
                        </div>
                      </div>
                    </div>
                
                <div class="row">
                  <div class="col-xs-3 col-md-3">
                    <div class="form-group">
                    <label for="exampleInputEmail1">Clinic Name<span class="mandatory">*</span></label>
                    <input type="email" class="form-control" placeholder="Enter Clinic Name" style="width:100%;">
                    </div>
                  </div>
                  <div class="col-xs-3 col-md-3">
                    <div class="form-group">
                    <label for="exampleInputEmail1">Clinician Name<span class="mandatory">*</span></label>
                    <input type="email" class="form-control" placeholder="Enter Clinician Name" style="width:100%;">
                    </div>
                  </div>
                  <div class="col-xs-3 col-md-3">
                    <div class="form-group">
                    <label for="exampleInputEmail1">Sample Collection Date</label>
                    <input type="email" class="form-control" style="width:100%;" placeholder="Sample Collection Date">
                    </div>
                  </div>
                  <div class="col-xs-3 col-md-3 col-lg-3">
                    <div class="form-group">
                    <label for="exampleInputEmail1">Collected by (Initials)</label>
                    <input type="email" class="form-control" style="width:100%;" placeholder="Enter Collected by (Initials)">
                    </div>
                  </div>
                </div>
                <br/>
                    <table class="table" style="width:100%">
                      <tr>
                        <td style="width:18%">
                        <label for="exampleInputEmail1">Patient First Name <span class="mandatory">*</span></label>
                        </td>
                        <td style="width:20%">
                          <input type="text" class="form-control" placeholder="First Name"  style="width:100%;" >
                        </td>
                        <td style="width:16%">
                        <label>Surname </label>
                        </td>
                        <td style="width:20%">
                          <input type="text" class="form-control" placeholder="Surname"  style="width:100%;" >
                        </td>
                        <td style="width:10%">
                          <label for="exampleInputEmail1">Gender<span class="mandatory">*</span></label>
                        </td>
                        <td style="width:20%">
                           <label class="radio-inline">
                            <input type="radio" class="" id="genderMale" name="gender" value="male" title="Please check gender"> Male
                            </label>
                          <label class="radio-inline">
                            <input type="radio" class="isRequired" id="genderFemale" name="gender" value="female" title="Please check gender"> Female
                          </label>
                        </td>
                      </tr>
                      <tr>
                        <td><label>Date Of Birth</label></td>
                        <td>
                          <input type="text" class="form-control" placeholder="DOB"  style="width:100%;" >
                        </td>
                        <td><label>Age in years</label></td>
                        <td>
                          <input type="text" class="form-control" placeholder="If DOB Unkown"  style="width:100%;" >
                          
                        </td>
                        <td><label>Age in months</label></td>
                        <td>
                          <input type="text" class="form-control" placeholder="If age < 1 year"  style="width:100%;" >
                        </td>
                      </tr>
                      <tr>
                        <td><label>Is Patient Pregnant ?</label></td>
                        <td>
                          <label class="radio-inline">
                           <input type="radio" class="" id="pregYes" name="patientPregnant" value="yes" title="Please check Is Patient Pregnant" > Yes
                          </label>
                          <label class="radio-inline">
                           <input type="radio" class="" id="pregNo" name="patientPregnant" value="no" title="Please check Is Patient Pregnant" > No
                          </label>
                        </td>
                        <td colspan="4"><label>Is Patient Breastfeeding?</label>
                        
                          <label class="radio-inline">
                             <input type="radio" class="" id="breastfeedingYes" name="breastfeeding" value="yes" title="Is Patient Breastfeeding" onclick="checkPatientIsBreastfeeding(this.value);"> Yes
                       </label>
                       <label class="radio-inline">
                               <input type="radio" class="" id="breastfeedingNo" name="breastfeeding" value="no" title="Is Patient Breastfeeding" onclick="checkPatientIsBreastfeeding(this.value);"> No
                       </label>
                        </td>
                      </tr>
                      <tr>
                        <td><label>Patient OI/ART Number</label></td>
                        <td>
                          <input type="text" class="form-control" placeholder="Email"  style="width:100%;" >
                        </td>
                        <td><label>Date Of ART Initiation</label></td>
                        <td>
                          <input type="text" class="form-control" placeholder="Date Of ART Initiation"  style="width:100%;" >
                        </td>
                      </tr>
                      <tr>
                        <td><label>ART Regimen</label></td>
                        <td><input type="text" class="form-control" placeholder="ART Regimen"  style="width:100%;" ></td>
                        <td><label>Patient consent to SMS Notification</label></td>
                        <td>
                          <label class="radio-inline">
                             <input type="radio" class="" id="receivesmsYes" name="receiveSms" value="yes" title="Patient consent to receive SMS"> Yes
                          </label>
                          <label class="radio-inline">
                                  <input type="radio" class="" id="receivesmsNo" name="receiveSms" value="no" title="Patient consent to receive SMS"> No
                          </label>
                        </td>
                        <td><label>Mobile Number</label></td>
                        <td><input type="text" class="form-control" id="patientPhoneNumber" name="patientPhoneNumber" placeholder="Enter Mobile Number." title="Please enter patient Phone No" style="width:100%;" /></td>
                      </tr>
                      <tr>
                        <td><label>Date Of Last Viral Load Test</label></td>
                        <td><input type="text" class="form-control" id="patientPhoneNumber" name="patientPhoneNumber" placeholder="Enter Date Of Last Viral Load Test" title="Please enter patient Phone No" style="width:100%;" /></td>
                        <td><label>Result Of Last Viral Load</label></td>
                        <td><input type="text" class="form-control" id="patientPhoneNumber" name="patientPhoneNumber" placeholder="Enter Result Of Last Viral Load" title="Please enter patient Phone No" style="width:100%;" /></td>
                        <td><label>Viral Load Log</label></td>
                        <td><input type="text" class="form-control" id="patientPhoneNumber" name="patientPhoneNumber" placeholder="Enter Viral Load Log" title="Please enter patient Phone No" style="width:100%;" /></td>
                      </tr>
                      <tr>
                        <td><label>Reason For VL test</label></td>
                        <td>
                          <select name="vlTestReason" id="vlTestReason" class="form-control" title="Please choose Reason For VL test" style="width:200px;">
                            <option value="">--select--</option>
                            <option value="good">Confirmation Of Treatment Failure(repeat VL at 3M)</option>
                            <option value="fair">Clinical Failure</option>
                            <option value="poor">Immunological Failure</option>
                           </select>
                        </td>
                        <td><label>Single Drug Substitution</label></td>
                        <td>
                          <select name="arvAdherence" id="arvAdherence" class="form-control" title="Please choose Adherence">
                            <option value="">--select--</option>
                            <option value="good">Pregnant Mother</option>
                            <option value="fair">Lactating Mother</option>
                            <option value="poor">Baseline VL</option>
                           </select>
                        </td>
                      </tr>
                    </table>
                   
                  </div>
                </div>
                <div class="box box-primary">
                  <div class="box-body">
                    <div class="box-header with-border">
                    <h3 class="box-title">FOR LABORATORY USE ONLY</h3>
                    </div>
                    <table class="table">
                      <tr>
                        <td><label>LAB No</label></td>
                        <td><input type="text" class="form-control" id="labNo" name="labNo" placeholder="Enter LAB No." title="Please enter patient Phone No" style="width:100%;" /></td>
                        <td><label>VL Testing Platform</label></td>
                        <td>
                          <select name="arvAdherence" id="arvAdherence" class="form-control" title="Please choose Adherence">
                              <option value="">--select--</option>
                              <option value="roche">ROCHE</option>
                              <option value="abbott">ABBOTT</option>
                              <option value="poor">BIOMEREUX</option>
                              <option value="poc">POC</option>
                          </select>
                        </td>
                        <td><label>Specimen type</label></td>
                        <td>
                          <select name="arvAdherence" id="arvAdherence" class="form-control" title="Please choose Adherence">
                              <option value="">--select--</option>
                              <option value="roche">Venous blood(EDTA)</option>
                              <option value="abbott">Frozen Plasma</option>
                              <option value="poor">Venous DBS(EDTA)</option>
                              <option value="poc">CAPILLARY DBS</option>
                          </select>
                        </td>
                      </tr>
                      <tr>                        
                        <td><label>Date Of Result</label></td>
                        <td><input type="text" class="form-control" id="patientPhoneNumber" name="patientPhoneNumber" placeholder="Enter Date Of Result." title="Please enter patient Phone No" style="width:100%;" /></td>
                        <td><label>Viral Load Result<br/> (copiesl/ml)</label></td>
                        <td><input type="text" class="form-control" id="patientPhoneNumber" name="patientPhoneNumber" placeholder="Enter Viral Load Result" title="Please enter patient Phone No" style="width:100%;" /></td>
                        <td><label>Viral Load Log</label></td>
                        <td><input type="text" class="form-control" id="patientPhoneNumber" name="patientPhoneNumber" placeholder="Enter Viral Load Log" title="Please enter patient Phone No" style="width:100%;" /></td>
                      </tr>
                      <tr>
                        <td><label>If no result</label></td>
                        <td colspan="3">
                          <label class="radio-inline">
                             <input type="radio" class="" id="noResultRejected" name="noResult" value="sample_rejected" title="Patient consent to receive SMS"> Sample Rejected
                          </label>
                          <label class="radio-inline">
                                  <input type="radio" class="" id="noResultError" name="noResult" value="technical_error" title="Patient consent to receive SMS"> Lab testing Technical Error
                          </label>
                        </td>
                        <td><label>Approved By</label></td>
                        <td><input type="text" class="form-control" id="patientPhoneNumber" name="patientPhoneNumber" placeholder="Enter Approved By" title="Please enter approved by" style="width:100%;" /></td>
                      </tr>
                      <tr>
                        <td><label>Laboratory <br/>Scientist Comments</label></td>
                        <td colspan="3"><textarea class="form-control" style="width:100%"></textarea></td>
                        <td><label>Date Received Stamp</label></td>
                        <td><input type="text" class="form-control" id="patientPhoneNumber" name="patientPhoneNumber" placeholder="Enter Date Received Stamp." title="Please enter date received stamp" style="width:100%;" /></td>
                      </tr>
                      <tr>
                        <td><label>Serial No.</label></td>
                        <td><input type="text" class="form-control" id="patientPhoneNumber" name="patientPhoneNumber" placeholder="Enter Serial No." title="Please enter patient Phone No" style="width:100%;" /></td>
                      </tr>
                    </table>
                  </div>
                </div>
              </div>
              <!-- /.box-body -->
              <div class="box-footer">
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
  
  
  <script type="text/javascript">

  function validateNow(){
    flag = deforayValidator.init({
        formId: 'userForm'
    });
    
    if(flag){
      document.getElementById('userForm').submit();
    }
  }
  
 
</script>
  
 <?php
 include('footer.php');
 ?>
