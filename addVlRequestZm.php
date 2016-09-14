<?php
ob_start();
include('header.php');
//include('./includes/MysqliDb.php');
include('General.php');
$general=new Deforay_Commons_General();
$query="SELECT * FROM roles where status='active'";
$result = $db->rawQuery($query);
$fQuery="SELECT * FROM facility_details where status='active'";
$fResult = $db->rawQuery($fQuery);
//get lab facility details
$lQuery="SELECT * FROM facility_details where facility_type='2'";
$lResult = $db->rawQuery($lQuery);

$aQuery="SELECT * from r_art_code_details where nation_identifier='zmb'";
$aResult=$db->query($aQuery);
$sQuery="SELECT * from r_sample_type where form_identification='2'";
$sResult=$db->query($sQuery);
$pdQuery="SELECT * from province_details";
$pdResult=$db->query($pdQuery);
$province = '';
$province.="<option value=''>-- Select --</option>";
            foreach($pdResult as $provinceName){
              $province .= "<option value='".$provinceName['province_name']."##".$provinceName['province_code']."'>".ucwords($provinceName['province_name'])."</option>";
            }
            $facility = '';
            $facility.="<option value=''>-- Select --</option>";
            foreach($fResult as $fDetails){
              $facility .= "<option value='".$fDetails['facility_id']."'>".ucwords($fDetails['facility_name'])."</option>";
            }
//sample code
$start_date = date('Y-m-01');
$end_date = date('Y-m-31');
$svlQuery='select MAX(sample_code_key) FROM vl_request_form as vl where vl.form_id="2" AND DATE(vl.created_on) >= "'.$start_date.'" AND DATE(vl.created_on) <= "'.$end_date.'"';
$svlResult=$db->query($svlQuery);
  if($svlResult[0]['MAX(sample_code_key)']!='' && $svlResult[0]['MAX(sample_code_key)']!=NULL){
 $maxId = $svlResult[0]['MAX(sample_code_key)']+1;
 $maxId = "00".$maxId;
}else{
 $maxId = '001';
}
$facilityResult='';
$stateResult='';
$districtResult = '';
$sDate ='';
$cBy ='';
$urgency ='';
$clinicianName = '';
$sKey = '';
$sFormat = '';
$sCodeValue = '';
if(isset($_SESSION['treamentId']) && $_SESSION['treamentId']!=''){
  //facility details
 $facilityQuery="SELECT * from facility_details where facility_id='".$_SESSION['facilityId']."'";
 $facilityResult=$db->query($facilityQuery);
 
 $stateName = $facilityResult[0]['state'];
 $stateQuery="SELECT * from province_details where province_name='".$stateName."'";
 $stateResult=$db->query($stateQuery);
 
 //district details
 $districtQuery="SELECT * from facility_details where state='".$stateName."'";
 $districtResult=$db->query($districtQuery);
 
 $vlQuery = 'select vl.urgency,vl.collected_by,vl.sample_collection_date,vl.lab_contact_person,vl.sample_code_key,vl.sample_code_format from vl_request_form as vl where vl.treament_id="'.$_SESSION['treamentId'].'"';
 $vlResult=$db->query($vlQuery);
 $urgency = $vlResult[0]['urgency'];
 $cBy = $vlResult[0]['collected_by'];
 $clinicianName = $vlResult[0]['lab_contact_person'];
 
 $sKey = $vlResult[0]['sample_code_key']+1;
 $sKey = "00".$sKey;
 $sFormat = $vlResult[0]['sample_code_format'];
 $sCodeValue = $vlResult[0]['sample_code_format'].$sKey; 
 
 if(isset($vlResult[0]['sample_collection_date']) && trim($vlResult[0]['sample_collection_date'])!='' && $vlResult[0]['sample_collection_date']!='0000-00-00 00:00:00'){
  $expStr=explode(" ",$vlResult[0]['sample_collection_date']);
  $vlResult[0]['sample_collection_date']=$general->humanDateFormat($expStr[0])." ".$expStr[1];
 }else{
  $vlResult[0]['sample_collection_date']='';
 }
 $sDate = $vlResult[0]['sample_collection_date'];
}

?>
<style>
  .ui_tpicker_second_label {
       display: none !important;
      }
      .ui_tpicker_second_slider {
       display: none !important;
      }.ui_tpicker_millisec_label {
       display: none !important;
      }.ui_tpicker_millisec_slider {
       display: none !important;
      }.ui_tpicker_microsec_label {
       display: none !important;
      }.ui_tpicker_microsec_slider {
       display: none !important;
      }.ui_tpicker_timezone_label {
       display: none !important;
      }.ui_tpicker_timezone {
       display: none !important;
      }.ui_tpicker_time_input{
       width:100%;
      }
</style>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>VIRAL LOAD LABORATORY REQUEST FORM (ZIMBABWE)</h1>
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
                          <label for="serialNo">Form Serial No</label>
                          <input type="text" class="form-control serialNo checkNum" id="" name="serialNo" placeholder="Enter Form Serial No." title="Please enter serial No" style="width:100%;" />
                        </div>
                      </div>
                      <div class="col-xs-3 col-md-3 col-sm-offset-2 col-md-offset-2" style="padding:10px;">
                        <div class="form-group">
                        <label for="urgency">Urgency&nbsp;&nbsp;&nbsp;&nbsp;</label>
                        <label class="radio-inline">
                             <input type="radio" class="" id="urgencyNormal" name="urgency" value="normal" title="Please check urgency" <?php echo ($urgency=='normal')?"checked='checked'":""?>> Normal
                        </label>
                        <label class="radio-inline">
                             <input type="radio" class=" " id="urgencyUrgent" name="urgency" value="urgent" title="Please check urgency" <?php echo ($urgency=='urgent')?"checked='checked'":""?>  > Urgent
                        </label>
                        </div>
                      </div>
                      
                    </div>
                    <div class="row">
                      <div class="col-xs-3 col-md-3">
                        <div class="form-group">
                        <label for="province">Province</label>
                          <select class="form-control" name="province" id="province" title="Please choose province" style="width:100%;" onchange="getfacilityDetails(this);">
                          <?php if($facilityResult!='') { ?>
                            <option value="">-- Select --</option>
                            <?php foreach($pdResult as $provinceName){ ?>
                            <option value="<?php echo $provinceName['province_name']."##".$provinceName['province_code'];?>" <?php echo ($facilityResult[0]['state']."##".$stateResult[0]['province_code']==$provinceName['province_name']."##".$provinceName['province_code'])?"selected='selected'":""?>><?php echo ucwords($provinceName['province_name']);?></option>;
                            <?php } } else { echo $province;  } ?>
                          </select>
                        </div>
                      </div>
                      <div class="col-xs-3 col-md-3">
                        <div class="form-group">
                        <label for="District">District  </label>
                          <select class="form-control" name="district" id="district" title="Please choose district" style="width:100%;">
                            <option value="">-- Select --</option>
                            <?php
                            if($districtResult!=''){
                            foreach($districtResult as $districtName){
                              ?>
                              <option value="<?php echo $districtName['district'];?>" <?php echo ($facilityResult[0]['district']==$districtName['district'])?"selected='selected'":""?>><?php echo ucwords($districtName['district']);?></option>
                              <?php
                            }
                            }
                            ?>
                          </select>
                        </div>
                      </div>
                    </div>
                
                <div class="row">
                  <div class="col-xs-3 col-md-3">
                    <div class="form-group">
                    <label for="clinicName">Clinic Name </label>
                      <select class="form-control" id="clinicName" name="clinicName" title="Please select clinic name" style="width:100%;" onchange="getfacilityProvinceDetails(this)">
                      <?php if($facilityResult!=''){ ?>
		      <option value="">-- Select --</option>
			<?php foreach($fResult as $fDetails){ ?>
                        <option value="<?php echo $fDetails['facility_id'];?>" <?php echo ($_SESSION['facilityId']==$fDetails['facility_id'])?"selected='selected'":""?>><?php echo ucwords($fDetails['facility_name']);?></option>
                        <?php } } else { echo $facility; } ?>
		      </select>
                    </div>
                  </div>
                  <div class="col-xs-3 col-md-3">
                    <div class="form-group">
                    <label for="clinicianName">Clinician Name </label>
                    <input type="text" class="form-control  " name="clinicianName" id="clinicianName" placeholder="Enter Clinician Name" style="width:100%;" value="<?php echo $clinicianName;?>" >
                    </div>
                  </div>
                  <div class="col-xs-3 col-md-3">
                    <div class="form-group">
                    <label for="sampleCollectionDate">Sample Collection Date</label>
                    <input type="text" class="form-control" style="width:100%;" name="sampleCollectionDate" id="sampleCollectionDate" placeholder="Sample Collection Date" value="<?php echo $sDate;?>">
                    </div>
                  </div>
                  <div class="col-xs-3 col-md-3 col-lg-3">
                    <div class="form-group">
                    <label for="collectedBy">Collected by (Initials)</label>
                    <input type="text" class="form-control" name="collectedBy" id="collectedBy" style="width:100%;" title="Enter Collected by (Initials)" placeholder="Enter Collected by (Initials)" value="<?php echo $cBy;?>">
                    </div>
                  </div>
                </div>
                <br/>
                    <table class="table" style="width:100%">
                      <tr>
                        <!--<td style="width:18%">
                        <label for="sampleCode">Sample Code  </label>
                        </td>
                        <td style="width:20%">
                          <input type="text" class="form-control  " name="sampleCode" id="sampleCode" placeholder="Sample Code" title="Enter Sample Code"  style="width:100%;" value="<?php echo $sCodeValue;?>">
                        </td>-->
                        <td style="width:16%">
                        <label for="patientFname">Patient First Name  </label>
                        </td>
                        <td style="width:20%">
                          <input type="text" class="form-control  " name="patientFname" id="patientFname" placeholder="First Name" title="Enter First Name"  style="width:100%;" >
                        </td>
                        <td style="width:10%">
                        <label for="surName">Surname </label>
                        </td>
                        <td style="width:20%">
                          <input type="text" class="form-control" name="surName" id="surName" placeholder="Surname" title="Enter Surname"  style="width:100%;" >
                        </td>
                      </tr>
                      <tr>
                        <td colspan="2">
                          <label for="gender">Gender &nbsp;&nbsp;</label>
                           <label class="radio-inline">
                            <input type="radio" class="" id="genderMale" name="gender" value="male" title="Please check gender"> Male
                            </label>
                          <label class="radio-inline">
                            <input type="radio" class=" " id="genderFemale" name="gender" value="female" title="Please check gender"> Female
                          </label>
                          <label class="radio-inline">
                            <input type="radio" class=" " id="genderNotRecorded" name="gender" value="not_recorded" title="Please check gender"> Not Recorded
                          </label>
                        </td>
                        <td><label>Date Of Birth</label></td>
                        <td>
                          <input type="text" class="form-control date" placeholder="DOB" name="dob" id="dob" title="Please choose DOB" style="width:100%;" onchange="getDateOfBirth();return false;">
                        </td>
                        <td><label for="ageInYears">Age in years</label></td>
                        <td>
                          <input type="text" class="form-control" name="ageInYears" id="ageInYears" placeholder="If DOB Unkown" title="Enter DOB" style="width:100%;" >
                        </td>
                      </tr>
                      <tr>
                        
                        
                        <td><label for="ageInMonths">Age in months</label></td>
                        <td>
                          <input type="text" class="form-control" name="ageInMonths" id="ageInMonths" placeholder="If age < 1 year" title="Enter age in months" style="width:100%;" >
                        </td>
                        <td class="femaleElements"><label for="patientPregnant">Is Patient Pregnant ?</label></td>
                        <td class="femaleElements">
                          <label class="radio-inline">
                           <input type="radio" class="" id="pregYes" name="patientPregnant" value="yes" title="Please check Is Patient Pregnant" > Yes
                          </label>
                          <label class="radio-inline">
                           <input type="radio" class="" id="pregNo" name="patientPregnant" value="no" title="Please check Is Patient Pregnant" > No
                          </label>
                        </td>
                        
                         <td colspan="2" class="femaleElements"><label for="breastfeeding">Is Patient Breastfeeding?</label>
                        
                          <label class="radio-inline">
                             <input type="radio" id="breastfeedingYes" name="breastfeeding" value="yes" title="Is Patient Breastfeeding" onclick="checkPatientIsBreastfeeding(this.value);">Yes
                          </label>
                          <label class="radio-inline">
                            <input type="radio" id="breastfeedingNo" name="breastfeeding" value="no" title="Is Patient Breastfeeding" onclick="checkPatientIsBreastfeeding(this.value);">No
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
                          <input type="text" class="form-control date" name="dateOfArtInitiation" id="dateOfArtInitiation" placeholder="Date Of ART Initiation" title="Date Of ART Initiation" style="width:100%;" >
                        </td>
                        <td><label for="artRegimen">ART Regimen</label></td>
                        <td>
                            <select class="form-control" id="artRegimen" name="artRegimen" placeholder="Enter ART Regimen" title="Please choose ART Regimen" onchange="checkValue();">
                         <option value="">-- Select --</option>
                         <?php
                         foreach($aResult as $parentRow){
                         ?>
                          <option value="<?php echo $parentRow['art_code']; ?>"><?php echo $parentRow['art_code']; ?></option>
                         <?php
                         }
                         ?>
                         <option value="other">Other</option>
                        </select>
                        </td>
                      </tr>
                      <tr>
                        <td class="newArtRegimen" style="display: none;"><label for="newArtRegimen">New ART Regimen</label><span class="mandatory">*</span></td>
                        <td class="newArtRegimen" style="display: none;">
                          <input type="text" class="form-control newArtRegimen" name="newArtRegimen" id="newArtRegimen" placeholder="New Art Regimen" title="New Art Regimen" style="width:100%;" >
                        </td>
                        <td><label>Patient consent to SMS Notification</label></td>
                        <td>
                          <label class="radio-inline">
                             <input type="radio" class="" id="receivesmsYes" name="receiveSms" value="yes" title="Patient consent to receive SMS" onclick="checkPatientReceivesms(this.value);"> Yes
                          </label>
                          <label class="radio-inline">
                                  <input type="radio" class="" id="receivesmsNo" name="receiveSms" value="no" title="Patient consent to receive SMS" onclick="checkPatientReceivesms(this.value);"> No
                          </label>
                        </td>
                        <td><label for="patientPhoneNumber" class="">Mobile Number</label></td>
                        <td><input type="text" class="form-control" id="patientPhoneNumber" name="patientPhoneNumber" placeholder="Enter Mobile Number." title="Please enter patient Phone No" style="width:100%;" /></td>
                      </tr>
                      
                      <tr>
                        <td><label for="lastViralLoadTestDate">Date Of Last Viral Load Test</label></td>
                        <td><input type="text" class="form-control date" id="lastViralLoadTestDate" name="lastViralLoadTestDate" placeholder="Enter Date Of Last Viral Load Test" title="Enter Date Of Last Viral Load Test" style="width:100%;" /></td>
                        <td><label for="lastViralLoadResult">Result Of Last Viral Load</label></td>
                        <td><input type="text" class="form-control" id="lastViralLoadResult" name="lastViralLoadResult" placeholder="Enter Result Of Last Viral Load" title="Enter Result Of Last Viral Load" style="width:100%;" /></td>
                        <td><label for="viralLoadLog">Viral Load Log</label></td>
                        <td><input type="text" class="form-control" id="viralLoadLog" name="viralLoadLog" placeholder="Enter Viral Load Log" title="Enter Viral Load Log" style="width:100%;" /></td>
                      </tr>
                      <tr>
                        <td><label for="vlTestReason">Reason For VL test</label></td>
                        <td>
                          <select name="vlTestReason" id="vlTestReason" class="form-control" title="Please choose Reason For VL test" style="width:200px;">
                            <option value="">-- Select --</option>
                            <option value="routive_VL">Routive VL</option>
                            <option value="confirmation_of_treatment_failure">Confirmation Of Treatment Failure(repeat VL at 3M)</option>
                            <option value="clinical_failure">Clinical Failure</option>
                            <option value="immunological_failure">Immunological Failure</option>
                            <option value="single_drug_substitution">Single Drug Substitution</option>
                            <option value="pregnant_other">Pregnant Mother</option>
                            <option value="lactating_mother">Lactating Mother</option>
                            <option value="baseline_VL">Baseline VL</option>
                           </select>
                        </td>
                        <td></td>
                        <td>
                          
                        </td>
                      </tr>
                    </table>
                  </div>
                </div>
                <div class="box box-primary">
                  <div class="box-body">
                    <div class="box-header with-border">
                    <h3 class="box-title">FOR LABORATORY USE ONLY</h3>
                    <div class="pull-right"><a href="javascript:void(0);" onclick="showModal('facilitiesModal.php?type=lab',900,520);" class="btn btn-default btn-sm" style="margin-right: 2px;" title="Search"><i class="fa fa-search"></i> Search</a></div>
                    </div>
                    <table class="table">
                      <tr>
                        <td><label for="serialNo">Form Serial No.</label></td>
                        <td><input type="text" class="form-control serialNo1 checkNum" id="" name="serialNo" placeholder="Enter Form Serial No." title="Please enter serial No" style="width:100%;" /></td>
                        <td><label for="sampleCode">Request Barcode</label></td>
                        <td>
                          <input type="text" class="form-control  reqBarcode checkNum" name="reqBarcode" id="reqBarcode" placeholder="Request Barcode" title="Enter Request Barcode"  style="width:100%;">
                          <input type="hidden" class="form-control  sampleCode" name="sampleCode" id="sampleCode" placeholder="Request Barcode" title="Enter Request Barcode"  style="width:100%;" value="<?php echo $sCodeValue;?>">
                        </td>
                        <td><label for="labId">Lab Name</label></td>
                        <td>
                          <select name="labId" id="labId" class="form-control" title="Please choose lab name">
                            <option value="">--Select--</option>
                            <?php
                            foreach($lResult as $labName){
                              ?>
                              <option value="<?php echo $labName['facility_id'];?>"><?php echo $labName['facility_name'];?></option>
                              <?php
                            }
                            ?>
                          </select>
                        </td>
                      </tr>
                      <tr>
                        <td><label for="labNo">LAB No</label></td>
                        <td><input type="text" class="form-control checkNum" id="labNo" name="labNo" placeholder="Enter LAB No." title="Please enter patient Phone No" style="width:100%;" /></td>
                        <td><label for="testingPlatform">VL Testing Platform</label></td>
                        <td>
                          <select name="testingPlatform" id="testingPlatform" class="form-control" title="Please choose VL Testing Platform">
                              <option value="">-- Select --</option>
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
                              <option value="">-- Select --</option>
                              <?php
                              foreach($sResult as $name){
                               ?>
                               <option value="<?php echo $name['sample_id'];?>"><?php echo ucwords($name['sample_name']);?></option>
                               <?php
                              }
                              ?>
                          </select>
                        </td>
                      </tr>
                      <tr>
                        <td><label for="sampleTestingDateAtLab">Sample Testing Date</label></td>
                        <td><input type="text" class="form-control date" id="sampleTestingDateAtLab" name="sampleTestingDateAtLab" placeholder="Enter Sample Testing Date." title="Please enter Sample Testing Date" style="width:100%;" /></td>
                        <td><label for="vlResult">Viral Load Result<br/> (copiesl/ml)</label></td>
                        <td><input type="text" class="form-control" id="vlResult" name="vlResult" placeholder="Enter Viral Load Result" title="Please enter viral load result" style="width:100%;" /></td>
                        <td><label for="vlLog">Viral Load Log</label></td>
                        <td><input type="text" class="form-control" id="vlLog" name="vlLog" placeholder="Enter Viral Load Log" title="Please enter viral load log" style="width:100%;" /></td>
                      </tr>
                      <tr>
                        <td><label class="noResult">If no result</label></td>
                        <td colspan="3">
                          <label class="radio-inline noResult">
                             <input type="radio" class="" id="noResultRejected" name="noResult" value="sample_rejected" title="Choose result"> Sample Rejected
                          </label>
                          <label class="radio-inline noResult">
                                  <input type="radio" class="" id="noResultError" name="noResult" value="technical_error" title="Choose result"> Lab testing Technical Error
                          </label>
                        </td>
                        <td><label>Approved By</label></td>
                        <td><input type="text" class="form-control" id="approvedBy" name="approvedBy" placeholder="Enter Approved By" title="Please enter approved by" style="width:100%;" /></td>
                      </tr>
                      <tr>
                        <td><label for="labCommnets">Laboratory <br/>Scientist Comments</label></td>
                        <td colspan="3"><textarea class="form-control" name="labCommnets" id="labComments" title="Enter lab comments" style="width:100%"></textarea></td>
                        <td><label for="dateOfReceivedStamp">Date Of Result</label></td>
                        <td><input type="text" class="form-control date" id="dateOfReceivedStamp" name="dateOfReceivedStamp" placeholder="Enter Date Received Stamp." title="Please enter date received stamp" style="width:100%;" /></td>
                      </tr>
                      
                    </table>
                  </div>
                </div>
              </div>
              <!-- /.box-body -->
              <div class="box-footer">
                <a class="btn btn-primary" href="javascript:void(0);" onclick="validateNow();return false;">Save</a>
                <input type="hidden" name="saveNext" id="saveNext"/>
                <input type="hidden" name="formId" id="formId" value="2"/>
                <input type="hidden" name="sampleCodeFormat" id="sampleCodeFormat" value="<?php echo $sFormat;?>"/>
                <input type="hidden" name="sampleCodeKey" id="sampleCodeKey" value="<?php echo $sKey;?>"/>
                
                <a class="btn btn-primary" href="javascript:void(0);" onclick="validateSaveNow();return false;">Save and Next</a>
                <a href="vlRequest.php" class="btn btn-default"> Cancel</a>
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
provinceName = true;
facilityName = true;
  function validateNow(){
    flag = deforayValidator.init({
        formId: 'vlRequestForm'
    });
    $("#saveNext").val('save');
    if(flag){
      $.blockUI();
      document.getElementById('vlRequestForm').submit();
    }
  }
  function validateSaveNow(){
    flag = deforayValidator.init({
        formId: 'vlRequestForm'
    });
    $("#saveNext").val('next');
    if(flag){
    $.blockUI();
      document.getElementById('vlRequestForm').submit();
    }
  }
  function getfacilityDetails(obj)
  {
      var cName = $("#clinicName").val();
      var pName = $("#province").val();
      if(pName!='' && provinceName && facilityName){
        facilityName = false;
      }
    if(pName!=''){
      if(provinceName){
      $.post("getFacilityForClinic.php", { pName : pName},
      function(data){
	  if(data != ""){
            details = data.split("###");
            $("#clinicName").html(details[0]);
            $("#district").html(details[1]);
            $("#clinicianName").val(details[2]);
	  }
      });
      }
      pNameVal = pName.split("##");
      sCode = '<?php echo date('Ymd');?>';
      sCodeKey = '<?php echo $maxId;?>';
      $("#sampleCode").val(pNameVal[1]+sCode+sCodeKey);
      $("#sampleCodeFormat").val(pNameVal[1]+sCode);
      $("#sampleCodeKey").val(sCodeKey);
    }else if(pName=='' && cName==''){
      provinceName = true;
      facilityName = true;
      $("#province").html("<?php echo $province;?>");
      $("#clinicName").html("<?php echo $facility;?>");
    }
  }
  function getfacilityProvinceDetails(obj)
  {
     //check facility name
      var cName = $("#clinicName").val();
      var pName = $("#province").val();
      if(cName!='' && provinceName && facilityName){
        provinceName = false;
      }
    if(cName!='' && facilityName){
      $.post("getFacilityForClinic.php", { cName : cName},
      function(data){
	  if(data != ""){
            details = data.split("###");
            $("#province").html(details[0]);
            $("#district").html(details[1]);
            $("#clinicianName").val(details[2]);
	  }
      });
    }else if(pName=='' && cName==''){
      provinceName = true;
      facilityName = true;
      $("#province").html("<?php echo $province;?>");
      $("#clinicName").html("<?php echo $facility;?>");
    }
  }
  $(document).ready(function() {
    
  
$("#vlResult").bind("keyup change", function(e) {
    if($("#vlResult").val() == "" && $("#vlLog").val() == "" ){
      $(".noResult").show();
    }else{
      $(".noResult").hide();
    }
});
$("#vlLog").bind("keyup change", function(e) {
    if($("#vlResult").val() == "" && $("#vlLog").val() == "" ){
      $(".noResult").show();
    }else{
      $(".noResult").hide();
    }
});
    
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
   $('#sampleCollectionDate').mask('99-aaa-9999 99:99');
   
   $('#sampleCollectionDate').datetimepicker({
     changeMonth: true,
     changeYear: true,
     dateFormat: 'dd-M-yy',
     timeFormat: "HH:mm",
     onChangeMonthYear: function(year, month, widget) {
           setTimeout(function() {
              $('.ui-datepicker-calendar').show();
           });
     },
     yearRange: <?php echo (date('Y') - 100); ?> + ":" + "<?php echo (date('Y')) ?>"
     }).click(function(){
   	$('.ui-datepicker-calendar').show();
   });
  
  });
  $("input:radio[name=gender]").click(function() {
      if($(this).val() == 'male'){
         $(".femaleElements").hide();
      }else if($(this).val() == 'female'){
        $(".femaleElements").show();
      }else if($(this).val() == 'not_recorded'){
        $(".femaleElements").show();
      }
  });
  $("#patientPhoneNumber").attr("disabled","disabled");
  $("input:radio[name=receiveSms]").click(function() {
      if($(this).val() == 'no'){
         $("#patientPhoneNumber").attr("disabled","disabled");
      }else if($(this).val() == 'yes'){
        $("#patientPhoneNumber").removeAttr("disabled");
      }
  });
  function checkValue()
  {
    var artRegimen = $("#artRegimen").val();
    if(artRegimen=='other'){
      $(".newArtRegimen").show();
      $("#newArtRegimen").addClass("isRequired");
    }else{
      $(".newArtRegimen").hide();
      $("#newArtRegimen").removeClass("isRequired");
    }
  }
  function checkPatientReceivesms(val){
   if(val=='yes'){
    $('#patientPhoneNumber').addClass('isRequired');
   }else{
     $('#patientPhoneNumber').removeClass('isRequired');
   }
  }
   $(".serialNo").keyup(function(){
    $(".serialNo1").val($(".serialNo").val());
    $(".reqBarcode").val($(".serialNo").val());
  });
  $(".serialNo1").keyup(function(){
    $(".serialNo").val($(".serialNo1").val());
    $(".reqBarcode").val($(".serialNo1").val());
  });
  $(".reqBarcode").keyup(function(){
    $(".serialNo").val($(".reqBarcode").val());
    $(".serialNo1").val($(".reqBarcode").val());
  });
  
  function getDateOfBirth(){
      var today = new Date();
      var dob = $("#dob").val();
      if($.trim(dob) == ""){
        $("#ageInMonths").val("");
        $("#ageInYears").val("");
        return false;
      }
      var dd = today.getDate();
      var mm = today.getMonth();
      var yyyy = today.getFullYear();
      if(dd<10) {
        dd='0'+dd
      } 
      
      if(mm<10) {
       mm='0'+mm
      }
      
      splitDob = dob.split("-");
      var dobDate = new Date(splitDob[1] + splitDob[2]+", "+splitDob[0]);
      var monthDigit = dobDate.getMonth();
      var dobYear = splitDob[2];
      var dobMonth = isNaN(monthDigit) ? 0 : (monthDigit);
      var dobMonth = (dobMonth<10) ? '0'+dobMonth: dobMonth;
      var dobDate = (splitDob[0]<10) ? '0'+splitDob[0]: splitDob[0];
      
      var date1 = new Date(yyyy,mm,dd);
      var date2 = new Date(dobYear,dobMonth,dobDate);
      var diff = new Date(date1.getTime() - date2.getTime());
      $("#ageInMonths").val(diff.getUTCMonth()); // Gives difference as year
      $("#ageInYears").val((diff.getUTCFullYear() - 1970)); // Gives month count of difference
      //console.log(diff.getUTCDate() - 1); // Gives day count of difference
  }
  function setFacilityLabDetails(fDetails){
      $("#labId").val("");
      facilityArray = fDetails.split("##");
      $("#labId").val(facilityArray[0]);
    }
  
</script>
  
 <?php
 include('footer.php');
 ?>
