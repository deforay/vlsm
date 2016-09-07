<?php
ob_start();
include('header.php');
//include('./includes/MysqliDb.php');
$query="SELECT * FROM roles where status='active'";
$result = $db->rawQuery($query);
$fQuery="SELECT * FROM facility_details where status='active'";
$fResult = $db->rawQuery($fQuery);
$aQuery="SELECT * from r_art_code_details where nation_identifier='zmb'";
$aResult=$db->query($aQuery);
?>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>VIRAL LOAD LABORATORY REQUEST FORM</h1>
      <ol class="breadcrumb">
        <li><a href="index.php"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Add Vl Request</li>
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
            <form class="form-inline" method='post'  name='vlRequestForm' id='vlRequestForm' autocomplete="off" action="addVlRequestHelperZm.php">
              <div class="box-body">
                <div class="box box-default">
                  <div class="box-body">
                    <div class="row">
                      <div class="col-xs-3 col-md-3">
                        <div class="form-group">
                        <label for="province">Province</label>
                        <input type="text" class="form-control" name="province" id="province" placeholder="Province" title="Provice" style="width:100%;">
                        </div>
                      </div>
                      <div class="col-xs-3 col-md-3">
                        <div class="form-group">
                        <label for="District">District  </label>
                        <input type="text" class="form-control" name="district" id="district" placeholder="District" title="District" style="width:100%;">
                        </div>
                      </div>
                      <div class="col-xs-3 col-md-3">
                        <div class="form-group">
                        <label for="urgency">Urgency  </label>
                        <label class="radio-inline">
                             <input type="radio" class="" id="urgencyNormal" name="urgency" value="normal" title="Please check urgency" checked="checked"> Normal
                        </label>
                        <label class="radio-inline">
                             <input type="radio" class=" " id="urgencyUrgent" name="urgency" value="normal" title="Please check urgency"> Urgent
                        </label>
                        </div>
                      </div>
                    </div>
                
                <div class="row">
                  <div class="col-xs-3 col-md-3">
                    <div class="form-group">
                    <label for="clinicName">Clinic Name </label>
                      <select class="form-control" id="clinicName" name="clinicName" title="Please select clinic name" style="width:100%;" onchange="getfacilityDetails()">
		      <option value="">-- Select --</option>
			<?php
			foreach($fResult as $name){
			 ?>
			 <option value="<?php echo $name['facility_id'];?>"><?php echo ucwords($name['facility_name']);?></option>
			 <?php
			}
			?>
		      </select>
                    </div>
                  </div>
                  <div class="col-xs-3 col-md-3">
                    <div class="form-group">
                    <label for="clinicianName">Clinician Name </label>
                    <input type="text" class="form-control  " name="clinicianName" id="clinicianName" placeholder="Enter Clinician Name" style="width:100%;">
                    </div>
                  </div>
                  <div class="col-xs-3 col-md-3">
                    <div class="form-group">
                    <label for="sampleCollectionDate">Sample Collection Date</label>
                    <input type="text" class="form-control date" style="width:100%;" name="sampleCollectionDate" id="sampleCollectionDate" placeholder="Sample Collection Date">
                    </div>
                  </div>
                  <div class="col-xs-3 col-md-3 col-lg-3">
                    <div class="form-group">
                    <label for="collectedBy">Collected by (Initials)</label>
                    <input type="text" class="form-control" name="collectedBy" id="collectedBy" style="width:100%;" title="Enter Collected by (Initials)" placeholder="Enter Collected by (Initials)">
                    </div>
                  </div>
                </div>
                <br/>
                    <table class="table" style="width:100%">
                      <tr>
                        <td style="width:18%">
                        <label for="sampleCode">Sample Code  </label>
                        </td>
                        <td style="width:20%">
                          <input type="text" class="form-control  " name="sampleCode" id="sampleCode" placeholder="Sample Code" title="Enter Sample Code"  style="width:100%;" >
                        </td>
                      </tr>
                      <tr>
                        <td style="width:18%">
                        <label for="patientFname">Patient First Name  </label>
                        </td>
                        <td style="width:20%">
                          <input type="text" class="form-control  " name="patientFname" id="patientFname" placeholder="First Name" title="Enter First Name"  style="width:100%;" >
                        </td>
                        <td style="width:16%">
                        <label for="surName">Surname </label>
                        </td>
                        <td style="width:20%">
                          <input type="text" class="form-control" name="surName" id="surName" placeholder="Surname" title="Enter Surname"  style="width:100%;" >
                        </td>
                        <td style="width:10%">
                          <label for="gender">Gender </label>
                        </td>
                        <td style="width:20%">
                           <label class="radio-inline">
                            <input type="radio" class="" id="genderMale" name="gender" value="male" title="Please check gender"> Male
                            </label>
                          <label class="radio-inline">
                            <input type="radio" class=" " id="genderFemale" name="gender" value="female" title="Please check gender"> Female
                          </label>
                        </td>
                      </tr>
                      <tr>
                        <td><label>Date Of Birth</label></td>
                        <td>
                          <input type="text" class="form-control date" placeholder="DOB"  style="width:100%;" >
                        </td>
                        <td><label for="ageInYears">Age in years</label></td>
                        <td>
                          <input type="text" class="form-control" name="ageInYears" id="ageInYears" placeholder="If DOB Unkown" title="Enter DOB" style="width:100%;" >
                          
                        </td>
                        <td><label for="ageInMonths">Age in months</label></td>
                        <td>
                          <input type="text" class="form-control" name="ageInMonths" id="ageInMonths" placeholder="If age < 1 year" title="Enter age in months" style="width:100%;" >
                        </td>
                      </tr>
                      <tr class="femaleElements">
                        <td><label for="patientPregnant">Is Patient Pregnant ?</label></td>
                        <td>
                          <label class="radio-inline">
                           <input type="radio" class="" id="pregYes" name="patientPregnant" value="yes" title="Please check Is Patient Pregnant" > Yes
                          </label>
                          <label class="radio-inline">
                           <input type="radio" class="" id="pregNo" name="patientPregnant" value="no" title="Please check Is Patient Pregnant" > No
                          </label>
                        </td>
                        <td colspan="4"><label for="breastfeeding">Is Patient Breastfeeding?</label>
                        
                          <label class="radio-inline">
                             <input type="radio" class="" id="breastfeedingYes" name="breastfeeding" value="yes" title="Is Patient Breastfeeding" onclick="checkPatientIsBreastfeeding(this.value);"> Yes
                       </label>
                       <label class="radio-inline">
                               <input type="radio" class="" id="breastfeedingNo" name="breastfeeding" value="no" title="Is Patient Breastfeeding" onclick="checkPatientIsBreastfeeding(this.value);"> No
                       </label>
                        </td>
                      </tr>
                      <tr>
                        <td><label for="patientArtNo">Patient OI/ART Number</label></td>
                        <td>
                          <input type="text" class="form-control" name="patientArtNo" id="patientArtNo" placeholder="Patient OI/ART Number" title="Enter Patient OI/ART Number" style="width:100%;" >
                        </td>
                        <td><label for="dateOfArt">Date Of ART Initiation</label></td>
                        <td>
                          <input type="text" class="form-control" name="dateOfArt" id="dateOfArt" placeholder="Date Of ART Initiation" title="Date Of ART Initiation" style="width:100%;" >
                        </td>
                      </tr>
                      <tr>
                        <td><label for="artRegimen">ART Regimen</label></td>
                        <td>
                            <select class="form-control" id="artRegimen" name="artRegimen" placeholder="Enter ART Regimen" title="Please choose ART Regimen">
                         <option value="">-- Select --</option>
                         <?php
                         foreach($aResult as $parentRow){
                         ?>
                          <option value="<?php echo $parentRow['art_code']; ?>"><?php echo $parentRow['art_code']; ?></option>
                         <?php
                         }
                         ?>
                        </select>
                        </td>
                        <td><label>Patient consent to SMS Notification</label></td>
                        <td>
                          <label class="radio-inline">
                             <input type="radio" class="" id="receivesmsYes" name="receiveSms" value="yes" title="Patient consent to receive SMS"> Yes
                          </label>
                          <label class="radio-inline">
                                  <input type="radio" class="" id="receivesmsNo" name="receiveSms" value="no" title="Patient consent to receive SMS"> No
                          </label>
                        </td>
                        <td><label for="patientPhoneNumber">Mobile Number</label></td>
                        <td><input type="text" class="form-control" id="patientPhoneNumber" name="patientPhoneNumber" placeholder="Enter Mobile Number." title="Please enter patient Phone No" style="width:100%;" /></td>
                      </tr>
                      <tr>
                        <td><label for="lastViralLoadTestDate">Date Of Last Viral Load Test</label></td>
                        <td><input type="text" class="form-control" id="lastViralLoadTest" name="lastViralLoadTestDate" placeholder="Enter Date Of Last Viral Load Test" title="Enter Date Of Last Viral Load Test" style="width:100%;" /></td>
                        <td><label for="lastViralLoadResult">Result Of Last Viral Load</label></td>
                        <td><input type="text" class="form-control" id="lastViralLoadResult" name="lastViralLoadResult" placeholder="Enter Result Of Last Viral Load" title="Enter Result Of Last Viral Load" style="width:100%;" /></td>
                        <td><label for="viralLoadLog">Viral Load Log</label></td>
                        <td><input type="text" class="form-control" id="viralLoadLog" name="viralLoadLog" placeholder="Enter Viral Load Log" title="Enter Viral Load Log" style="width:100%;" /></td>
                      </tr>
                      <tr>
                        <td><label for="vlTestReason">Reason For VL test</label></td>
                        <td>
                          <select name="vlTestReason" id="vlTestReason" class="form-control" title="Please choose Reason For VL test" style="width:200px;">
                            <option value="">--select--</option>
                            <option value="routive_VL">Routive VL</option>
                            <option value="confirmation_of_treatment_failure">Confirmation Of Treatment Failure(repeat VL at 3M)</option>
                            <option value="clinical_failure">Clinical Failure</option>
                            <option value="immunological_failure">Immunological Failure</option>
                           </select>
                        </td>
                        <td><label for="arvAdherence">Single Drug Substitution</label></td>
                        <td>
                          <select name="arvAdherence" id="arvAdherence" class="form-control" title="Please choose Adherence">
                            <option value="">--select--</option>
                            <option value="pregnant_other">Pregnant Mother</option>
                            <option value="lactating mother">Lactating Mother</option>
                            <option value="baseline_VL">Baseline VL</option>
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
                        <td><label for="labNo">LAB No</label></td>
                        <td><input type="text" class="form-control" id="labNo" name="labNo" placeholder="Enter LAB No." title="Please enter patient Phone No" style="width:100%;" /></td>
                        <td><label for="testingPlatform">VL Testing Platform</label></td>
                        <td>
                          <select name="testingPlatform" id="testingPlatform" class="form-control" title="Please choose VL Testing Platform">
                              <option value="">--select--</option>
                              <option value="roche">ROCHE</option>
                              <option value="abbott">ABBOTT</option>
                              <option value="poor">BIOMEREUX</option>
                              <option value="poc">POC</option>
                              <option value="other">OTHER</option>
                          </select>
                        </td>
                        <td><label for="specimenType">Specimen type</label></td>
                        <td>
                          <select name="specimenType" id="specimenType" class="form-control" title="Please choose Specimen type">
                              <option value="">--select--</option>
                              <option value="venous_blood">Venous blood(EDTA)</option>
                              <option value="frozen_plasma">Frozen Plasma</option>
                              <option value="venous_DBS">Venous DBS(EDTA)</option>
                              <option value="capillary_DBS">CAPILLARY DBS</option>
                          </select>
                        </td>
                      </tr>
                      <tr>
                        <td><label for="dateOfResult">Date Of Result</label></td>
                        <td><input type="text" class="form-control date" id="dateOfResult" name="dateOfResult" placeholder="Enter Date Of Result." title="Please enter date of result" style="width:100%;" /></td>
                        <td><label for="vlResult">Viral Load Result<br/> (copiesl/ml)</label></td>
                        <td><input type="text" class="form-control" id="vlResult" name="vlResult" placeholder="Enter Viral Load Result" title="Please enter viral load result" style="width:100%;" /></td>
                        <td><label for="vlLog">Viral Load Log</label></td>
                        <td><input type="text" class="form-control" id="vlLog" name="vlLog" placeholder="Enter Viral Load Log" title="Please enter viral load log" style="width:100%;" /></td>
                      </tr>
                      <tr>
                        <td><label>If no result</label></td>
                        <td colspan="3">
                          <label class="radio-inline">
                             <input type="radio" class="" id="noResultRejected" name="noResult" value="sample_rejected" title="Choose result"> Sample Rejected
                          </label>
                          <label class="radio-inline">
                                  <input type="radio" class="" id="noResultError" name="noResult" value="technical_error" title="Choose result"> Lab testing Technical Error
                          </label>
                        </td>
                        <td><label>Approved By</label></td>
                        <td><input type="text" class="form-control" id="patientPhoneNumber" name="patientPhoneNumber" placeholder="Enter Approved By" title="Please enter approved by" style="width:100%;" /></td>
                      </tr>
                      <tr>
                        <td><label for="labCommnets">Laboratory <br/>Scientist Comments</label></td>
                        <td colspan="3"><textarea class="form-control" name="labCommnets" id="labComments" title="Enter lab comments" style="width:100%"></textarea></td>
                        <td><label for="dateOfReceivedStamp">Date Received Stamp</label></td>
                        <td><input type="text" class="form-control" id="dateOfReceivedStamp" name="dateOfReceivedStamp" placeholder="Enter Date Received Stamp." title="Please enter date received stamp" style="width:100%;" /></td>
                      </tr>
                      <tr>
                        <td><label for="serialNo">Serial No.</label></td>
                        <td><input type="text" class="form-control" id="serialNo" name="serialNo" placeholder="Enter Serial No." title="Please enter serial No" style="width:100%;" /></td>
                      </tr>
                    </table>
                  </div>
                </div>
              </div>
              <!-- /.box-body -->
              <div class="box-footer">
                <a class="btn btn-primary" href="javascript:void(0);" onclick="validateNow();return false;">Save</a>
                <a class="btn btn-primary" href="javascript:void(0);" onclick="validateNow();return false;">Save and Next</a>
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
        formId: 'vlRequestForm'
    });
    
    if(flag){
      document.getElementById('vlRequestForm').submit();
    }
  }
  function getfacilityDetails()
  {
    var cName = $("#clinicName").val();
    if(cName!=''){
      $.post("getFacilityForClinic.php", { cName : cName},
      function(data){
	  if(data != ""){
            details = data.split("##");
            $("#province").val(details[0]);
            $("#district").val(details[1]);
            $("#clinicianName").val(details[2]);
            if(details[0]!=''){
              $("#province").prop('readonly',true);
            }else{
              $("#province").prop('readonly',false);
            }
            if(details[1]!=''){
              $("#district").prop('readonly',true);
            }else{
              $("#district").prop('readonly',false);
            }
            if(details[2]!=''){
              $("#clinicianName").prop('readonly',true);
            }else{
              $("#clinicianName").prop('readonly',false);
            }
	  }
      });
    }else{
      
    }
  }
  $(document).ready(function() {
  $('.date').datepicker({
     changeMonth: true,
     changeYear: true,
     dateFormat: 'dd-M-yy',
     timeFormat: "hh:mm TT",
     yearRange: <?php echo (date('Y') - 100); ?> + ":" + "<?php echo (date('Y')) ?>"
    }).click(function(){
   	$('.ui-datepicker-calendar').show();
   });
   
   $('.date').mask('99-aaa-9999');
  });
  $("input:radio[name=gender]").click(function() {
      if($(this).val() == 'male'){
         $(".femaleElements").hide();
      }else if($(this).val() == 'female'){
        $(".femaleElements").show();
      }
    });
</script>
  
 <?php
 include('footer.php');
 ?>
