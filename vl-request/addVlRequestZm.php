<?php
ob_start();
include('../General.php');
$general=new Deforay_Commons_General();
$autoApprovalFieldStatus = 'show';
if(isset($_SESSION['roleCode']) && $_SESSION['roleCode'] == "DE"){
  $configQuery="SELECT value FROM global_config WHERE name = 'auto_approval'";
  $configResult=$db->query($configQuery);
  if(isset($configResult) && count($configResult)> 0 && $configResult[0]['value'] == 'no'){
    $autoApprovalFieldStatus = 'hide';
  }
}
//get import config
$importQuery="SELECT * FROM import_config WHERE status = 'active'";
$importResult=$db->query($importQuery);
//get lab facility details
$lQuery="SELECT * FROM facility_details where facility_type='2'";
$lResult = $db->rawQuery($lQuery);

//global config
$cSampleQuery="SELECT * FROM global_config";
$cSampleResult=$db->query($cSampleQuery);
$arr = array();
// now we create an associative array so that we can easily create view variables
for ($i = 0; $i < sizeof($cSampleResult); $i++) {
  $arr[$cSampleResult[$i]['name']] = $cSampleResult[$i]['value'];
}

if($arr['sample_code']=='auto' || $arr['sample_code']=='alphanumeric'){
  $numeric = '';
}else{
  $numeric = 'checkNum';
}
//sample rejection reason
$rejectionQuery="SELECT * FROM r_sample_rejection_reasons";
$rejectionResult = $db->rawQuery($rejectionQuery);

$userQuery="SELECT * FROM user_details where status='active'";
$userResult = $db->rawQuery($userQuery);
$query="SELECT * FROM roles where status='active'";
$result = $db->rawQuery($query);
$fQuery="SELECT * FROM facility_details where status='active'";
$fResult = $db->rawQuery($fQuery);
//get vltest reason details
$testRQuery="SELECT * FROM r_vl_test_reasons";
$testReason = $db->rawQuery($testRQuery);
//get lab facility details
$lQuery="SELECT * FROM facility_details where facility_type='2'";
$lResult = $db->rawQuery($lQuery);

$aQuery="SELECT * from r_art_code_details where nation_identifier='zmb'";
$aResult=$db->query($aQuery);
$sQuery="SELECT * from r_sample_type where status='active'";
$sResult=$db->query($sQuery);
$pdQuery="SELECT * from province_details";
$pdResult=$db->query($pdQuery);
$province = '';
$province.="<option value=''> -- Select -- </option>";
            foreach($pdResult as $provinceName){
              $province .= "<option value='".$provinceName['province_name']."##".$provinceName['province_code']."'>".ucwords($provinceName['province_name'])."</option>";
            }
$facility = '';
$facility.="<option value=''> -- Select -- </option>";
foreach($fResult as $fDetails){
  $facility .= "<option value='".$fDetails['facility_id']."'>".ucwords($fDetails['facility_name'])."</option>";
}
//sample code
if($arr['sample_code']=='MMYY'){
    $mnthYr = date('my');
    $start_date = date('Y-m-01');
$end_date = date('Y-m-31');
  }else if($arr['sample_code']=='YY'){
    $mnthYr = date('y');
    $start_date = date('Y-01-01');
    $end_date = date('Y-12-31');
}

//$svlQuery='select MAX(sample_code_key) FROM vl_request_form as vl where vl.vlsm_country_id="2" AND vl.sample_code_title="'.$arr['sample_code'].'"  AND DATE(vl.request_created_datetime) >= "'.$start_date.'" AND DATE(vl.request_created_datetime) <= "'.$end_date.'"';
$svlQuery='SELECT sample_code_key FROM vl_request_form as vl WHERE DATE(vl.request_created_datetime) >= "'.$start_date.'" AND DATE(vl.request_created_datetime) <= "'.$end_date.'" ORDER BY vl_sample_id DESC LIMIT 1';
$svlResult=$db->query($svlQuery);

  $prefix = $arr['sample_code_prefix'];
  if($svlResult[0]['sample_code_key']!='' && $svlResult[0]['sample_code_key']!=NULL){
 $maxId = $svlResult[0]['sample_code_key']+1;
 $strparam = strlen($maxId);
 $zeros = substr("000", $strparam);
 $maxId = $zeros.$maxId;
}else{
 $maxId = '001';
}

//lab no increament
$labvlQuery='select MAX(lab_code) FROM vl_request_form as vl where vl.vlsm_country_id="2" AND DATE(vl.request_created_datetime) >= "'.$start_date.'" AND DATE(vl.request_created_datetime) <= "'.$end_date.'"';
$labvlResult=$db->query($labvlQuery);
  if($labvlResult[0]['MAX(lab_code)']!='' && $labvlResult[0]['MAX(lab_code)']!=NULL){
 $maxLabId = $labvlResult[0]['MAX(lab_code)']+1;
}else{
 $maxLabId = '1';
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
$sampleReceivedDate = '';
$labNameId = '';
if(isset($_SESSION['treamentId']) && $_SESSION['treamentId']!=''){
  //facility details
 $facilityQuery="SELECT * from facility_details where facility_id='".$_SESSION['facilityId']."'";
 $facilityResult=$db->query($facilityQuery);
 
 $stateName = $facilityResult[0]['facility_state'];
 $stateQuery="SELECT * from province_details where province_name='".$stateName."'";
 $stateResult=$db->query($stateQuery);
 
 //district details
 $districtQuery="SELECT DISTINCT facility_district from facility_details where facility_state='".$stateName."'";
 $districtResult=$db->query($districtQuery);
 
 $vlQuery = 'select vl.test_urgency,vl.sample_collected_by,vl.sample_collection_date,vl.sample_received_at_vl_lab_datetime,vl.lab_contact_person,vl.sample_code_key,vl.sample_code_format,vl.lab_id from vl_request_form as vl where vl.vl_sample_id="'.$_SESSION['treamentId'].'"';
 $vlResult=$db->query($vlQuery);
 $urgency = $vlResult[0]['test_urgency'];
 $cBy = $vlResult[0]['sample_collected_by'];
 $clinicianName = $vlResult[0]['lab_contact_person'];
 $labNameId = $vlResult[0]['lab_id'];
 
 $sKey = $vlResult[0]['sample_code_key']+1;
 $strparam = strlen($sKey);
 $zeros = substr("000", $strparam);
 $sKey = $zeros.$sKey;
 $sFormat = $vlResult[0]['sample_code_format'];
 $sCodeValue = $vlResult[0]['sample_code_format'].$sKey;
 
 if($arr['sample_code']=='MMYY' || $arr['sample_code']=='YY'){
 if($arr['sample_code']=='MMYY'){
    $mnthYr = date('my');
  }else if($arr['sample_code']=='YY'){
    $mnthYr = date('y');
  }
  $prefix = $arr['sample_code_prefix'];
  $sFormat = $prefix.$mnthYr;
  $sCodeValue = $sFormat.$sKey;
 }
 
 if(isset($vlResult[0]['sample_collection_date']) && trim($vlResult[0]['sample_collection_date'])!='' && $vlResult[0]['sample_collection_date']!='0000-00-00 00:00:00'){
  $expStr=explode(" ",$vlResult[0]['sample_collection_date']);
  $vlResult[0]['sample_collection_date']=$general->humanDateFormat($expStr[0])." ".$expStr[1];
 }else{
  $vlResult[0]['sample_collection_date']='';
 }
 $sDate = $vlResult[0]['sample_collection_date'];
 
 if(isset($vlResult[0]['sample_received_at_vl_lab_datetime']) && trim($vlResult[0]['sample_received_at_vl_lab_datetime'])!='' && $vlResult[0]['sample_received_at_vl_lab_datetime']!='0000-00-00 00:00:00'){
  $expStr=explode(" ",$vlResult[0]['sample_received_at_vl_lab_datetime']);
  $vlResult[0]['sample_received_at_vl_lab_datetime']=$general->humanDateFormat($expStr[0])." ".$expStr[1];
 }else{
  $vlResult[0]['sample_received_at_vl_lab_datetime']='';
 }
 $sampleReceivedDate = $vlResult[0]['sample_received_at_vl_lab_datetime'];
}
if($urgency==''){
  $urgency= 'normal';
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
      <h1><i class="fa fa-edit"></i> VIRAL LOAD LABORATORY REQUEST FORM</h1>
      <ol class="breadcrumb">
        <li><a href="/"><i class="fa fa-dashboard"></i> Home</a></li>
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
                          <label for="serialNo">Form Serial No <span class="mandatory">*</span></label>
                          <input type="text" class="form-control serialNo <?php echo $numeric;?> isRequired removeValue" id="" name="serialNo" placeholder="Enter Form Serial No." title="" style="width:100%;" value="<?php echo $sCodeValue;?>" onblur="checkNameValidation('vl_request_form','serial_no',this,null,'This serial number already exists.Try another number',null)" />
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
                        <label for="province">Province <span class="mandatory">*</span></label>
                          <select class="form-control isRequired" name="province" id="province" title="Please choose province" style="width:100%;" onchange="getfacilityDetails(this);">
                          <?php if($facilityResult!='') { ?>
                            <option value=""> -- Select -- </option>
                            <?php foreach($pdResult as $provinceName){ ?>
                            <option value="<?php echo $provinceName['province_name']."##".$provinceName['province_code'];?>" <?php echo ($facilityResult[0]['facility_state']."##".$stateResult[0]['province_code']==$provinceName['province_name']."##".$provinceName['province_code'])?"selected='selected'":""?>><?php echo ucwords($provinceName['province_name']);?></option>;
                            <?php } } else { echo $province;  } ?>
                          </select>
                        </div>
                      </div>
                      <div class="col-xs-3 col-md-3">
                        <div class="form-group">
                        <label for="District">District  <span class="mandatory">*</span></label>
                          <select class="form-control isRequired" name="district" id="district" title="Please choose district" style="width:100%;" onchange="getfacilityDistrictwise(this);">
                            <option value=""> -- Select -- </option>
                            <?php
                            if($districtResult!=''){
                            foreach($districtResult as $districtName){
                              ?>
                              <option value="<?php echo $districtName['facility_district'];?>" <?php echo ($facilityResult[0]['facility_district']==$districtName['facility_district'])?"selected='selected'":""?>><?php echo ucwords($districtName['facility_district']);?></option>
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
                    <label for="clinicName">Clinic Name <span class="mandatory">*</span></label>
                      <select class="form-control isRequired" id="clinicName" name="clinicName" title="Please select clinic name" style="width:100%;" onchange="getfacilityProvinceDetails(this)">
                      <?php if($facilityResult!=''){ ?>
		      <option value=""> -- Select -- </option>
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
                    <label for="sampleCollectionDate">Sample Collection Date <span class="mandatory">*</span></label>
                    <input type="text" class="form-control isRequired" style="width:100%;" name="sampleCollectionDate" id="sampleCollectionDate" placeholder="Sample Collection Date" title="Please select sample collection date" value="<?php echo $sDate;?>" onchange="checkSampleReceviedDate();checkSampleTestingDate();">
                    </div>
                  </div>
                  <div class="col-xs-3 col-md-3">
                    <div class="form-group">
                    <label for="">Sample Received Date</label>
                    <input type="text" class="form-control" style="width:100%;" name="sampleReceivedDate" id="sampleReceivedDate" placeholder="Sample Received Date" value="<?php echo $sampleReceivedDate; ?>" onchange="checkSampleReceviedDate();">
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-xs-3 col-md-3 col-lg-3">
                    <div class="form-group">
                    <label for="collectedBy">Collected by (Initials)</label>
                    <input type="text" class="form-control" name="collectedBy" id="collectedBy" style="width:100%;" title="Enter Collected by (Initials)" placeholder="Enter Collected by (Initials)" value="<?php echo $cBy;?>">
                    </div>
                  </div>
                </div>
                <br/>
                <input style="width:30%;" type="text" name="artPatientNo" id="artPatientNo" class="" placeholder="Enter ART Number or Patient Name" title="Enter art number or patient name" />&nbsp;&nbsp;
                <a style="margin-top:-0.35%;" href="javascript:void(0);" class="btn btn-default btn-sm" onclick="showPatientList();"><i class="fa fa-search">&nbsp;</i>Search</a><span id="showEmptyResult" style="display:none;color: #ff0000;font-size: 15px;"><b>&nbsp;No Result</b></span>
                    <table class="table" style="width:100%">
                      <tr>
                        <!--<td style="width:18%">
                        <label for="sampleCode">Sample Code  </label>
                        </td>
                        <td style="width:20%">
                          <input type="text" class="form-control  " name="sampleCode" id="sampleCode" placeholder="Sample Code" title="Enter Sample Code"  style="width:100%;" value="< ?php echo $sCodeValue;?>">
                        </td>-->
                        <td style="width:16%">
                        <label for="patientFname">Patient First Name   <span class="mandatory">*</span></label>
                        </td>
                        <td style="width:20%">
                          <input type="text" class="form-control isRequired " name="patientFname" id="patientFname" placeholder="First Name" title="Enter First Name"  style="width:100%;" >
                        </td>
                        <td style="width:10%">
                        <label for="surName">Surname  <span class="mandatory">*</span></label>
                        </td>
                        <td style="width:20%">
                          <input type="text" class="form-control isRequired" name="surName" id="surName" placeholder="Surname" title="Enter Surname"  style="width:100%;" >
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
                          <input type="text" class="form-control date" placeholder="DOB" name="dob" id="dob" title="Please choose DOB" style="width:100%;" onchange="getDateOfBirth();checkARTInitiationDate();">
                        </td>
                        <td><label for="ageInYears">Age in years</label></td>
                        <td>
                          <input type="text" class="form-control" name="ageInYears" id="ageInYears" placeholder="If DOB Unkown" title="Enter age in years" style="width:100%;" >
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
                           <input type="radio" class="" id="pregYes" name="patientPregnant" value="yes" title="Please check Is Patient Pregnant" onclick="checkPatientIsPregnant(this.value);"> Yes
                          </label>
                          <label class="radio-inline">
                           <input type="radio" class="" id="pregNo" name="patientPregnant" value="no" title="Please check Is Patient Pregnant" onclick="checkPatientIsPregnant(this.value);"> No
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
                          <input type="text" class="form-control" name="patientArtNo" id="patientArtNo" placeholder="Patient OI/ART Number" title="Enter Patient OI/ART Number" style="width:100%;"  onchange="checkPatientDetails('vl_request_form','patient_art_no',this,null)">
                        </td>
                        <td><label for="dateOfArt">Date Of ART Initiation</label></td>
                        <td>
                          <input type="text" class="form-control date" name="dateOfArtInitiation" id="dateOfArtInitiation" placeholder="Date Of ART Initiation" title="Date Of ART Initiation" onchange="checkARTInitiationDate();checkLastVLTestDate();" style="width:100%;" >
                        </td>
                        <td><label for="artRegimen">ART Regimen</label></td>
                        <td>
                          <select class="form-control" id="artRegimen" name="artRegimen" placeholder="Enter ART Regimen" title="Please choose ART Regimen" onchange="checkValue();">
                         <option value=""> -- Select -- </option>
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
                        <td><input type="text" class="form-control date" id="lastViralLoadTestDate" name="lastViralLoadTestDate" placeholder="Enter Date Of Last Viral Load Test" title="Enter Date Of Last Viral Load Test" onchange="checkLastVLTestDate();" style="width:100%;" /></td>
                        <td><label for="lastViralLoadResult">Result Of Last Viral Load</label></td>
                        <td><input type="text" class="form-control" id="lastViralLoadResult" name="lastViralLoadResult" placeholder="Enter Result Of Last Viral Load" title="Enter Result Of Last Viral Load" style="width:100%;" /></td>
                        <td><label for="viralLoadLog">Viral Load Log</label></td>
                        <td><input type="text" class="form-control" id="viralLoadLog" name="viralLoadLog" placeholder="Enter Viral Load Log" title="Enter Viral Load Log" style="width:100%;" /></td>
                      </tr>
                      <tr>
                        <td><label for="vlTestReason">Reason For VL Test</label></td>
                        <td>
                          <select name="vlTestReason" id="vlTestReason" class="form-control" title="Please choose Reason For VL test" style="width:200px;">
                            <option value=""> -- Select -- </option>
                            <?php
                            foreach($testReason as $reason){
                              ?>
                              <option value="<?php echo $reason['test_reason_name'];?>"><?php echo ucwords($reason['test_reason_name']);?></option>
                              <?php
                            }
                            ?>
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
                        <td><label for="serialNo">Form Serial No. <span class="mandatory">*</span></label></td>
                        <td><input type="text" class="form-control serialNo1 <?php echo $numeric;?> isRequired removeValue" id="" name="serialNo" placeholder="Enter Form Serial No." title="Please enter serial No" style="width:100%;" value="<?php echo $sCodeValue;?>" onblur="checkNameValidation('vl_request_form','serial_no',this,null,'This serial number already exists.Try another number',null)" /></td>
                        <td><label for="sampleCode">Request Barcode <span class="mandatory">*</span></label></td>
                        <td>
                          <input type="text" class="form-control  reqBarcode <?php echo $numeric;?> isRequired removeValue" name="reqBarcode" id="reqBarcode" placeholder="Request Barcode" title="Enter Request Barcode"  style="width:100%;" value="<?php echo $sCodeValue;?>" onblur="checkNameValidation('vl_request_form','serial_no',this,null,'This barcode already exists.Try another barcode',null)">
                          <!--<input type="hidden" class="form-control  sampleCode " name="sampleCode" id="sampleCode" placeholder="Request Barcode" title="Enter Request Barcode"  style="width:100%;" value="< ?php echo $sCodeValue;?>">-->
                        </td>
                        <td><label for="labId">Lab Name</label></td>
                        <td>
                          <select name="labId" id="labId" class="form-control" title="Please choose lab name">
                            <option value=""> -- Select -- </option>
                            <?php
                            foreach($lResult as $labName){
                              ?>
                              <option value="<?php echo $labName['facility_id'];?>" <?php echo ($labNameId==$labName['facility_id'])?"selected='selected'":""?>><?php echo ucwords($labName['facility_name']);?></option>
                              <?php
                            }
                            ?>
                          </select>
                        </td>
                      </tr>
                      <tr>
                        <td><label for="labNo">LAB No</label></td>
                        <td><input type="text" class="form-control checkNum" id="labNo" name="labNo" placeholder="Enter LAB No." title="Please enter patient Phone No" style="width:100%;" value="<?php echo $maxLabId;?>"/></td>
                        <td><label for="testingPlatform">VL Testing Platform</label></td>
                        <td>
                          <select name="testingPlatform" id="testingPlatform" class="form-control" title="Please choose VL Testing Platform">
                            <option value="">-- Select --</option>
                            <?php foreach($importResult as $mName) { ?>
                              <option value="<?php echo $mName['machine_name'].'##'.$mName['lower_limit'].'##'.$mName['higher_limit'];?>"><?php echo $mName['machine_name'];?></option>
                              <?php
                            }
                            ?>
                          </select>
                        </td>
                        <td><?php if(isset($arr['sample_type']) && trim($arr['sample_type']) == "enabled"){ ?><label for="specimenType">Specimen type</label><?php } ?></td>
                        <td>
                          <?php if(isset($arr['sample_type']) && trim($arr['sample_type']) == "enabled"){ ?>
                            <select name="specimenType" id="specimenType" class="form-control" title="Please choose Specimen type">
                                <option value=""> -- Select -- </option>
                                <?php
                                foreach($sResult as $name){
                                 ?>
                                 <option value="<?php echo $name['sample_id'];?>"><?php echo ucwords($name['sample_name']);?></option>
                                 <?php
                                }
                                ?>
                            </select>
                          <?php } ?>
                        </td>
                      </tr>
                      <tr>
                        <td><label for="sampleTestingDateAtLab">Sample Testing Date</label></td>
                        <td><input type="text" class="form-control " id="sampleTestingDateAtLab" name="sampleTestingDateAtLab" placeholder="Enter Sample Testing Date." title="Please enter Sample Testing Date" onchange="checkSampleTestingDate();" style="width:100%;" /></td>
                        <td><label for="vlResult">Viral Load Result<br/> (copiesl/ml)</label></td>
                        <td><input type="text" class="form-control" id="vlResult" name="vlResult" placeholder="Enter Viral Load Result" title="Please enter viral load result" style="width:100%;" onchange="calculateLogValue(this)"/></td>
                        <td><label for="vlLog">Viral Load Log</label></td>
                        <td><input type="text" class="form-control" id="vlLog" name="vlLog" placeholder="Enter Viral Load Log" title="Please enter viral load log" style="width:100%;" onchange="calculateLogValue(this)"/></td>
                      </tr>
                      <tr class="noResult">
                        <td><label>If no result</label></td>
                        <td colspan="2">
                          <label class="radio-inline noResult">
                             <input type="radio" class="" id="noResultRejected" name="noResult" value="sample_rejected" title="Choose result" onclick="checkRejectedReason();"> Sample Rejected
                          </label>
                          <label class="radio-inline noResult" style="margin-left: 0px;">
                                  <input type="radio" class="" id="noResultError" name="noResult" value="technical_error" title="Choose result" onclick="checkRejectedReason();"> Lab testing Technical Error
                          </label>
                        </td>
                        <td><label class="noResult">Rejection Reason</label></td>
                        <td colspan="2"><select name="rejectionReason" id="rejectionReason" class="form-control" title="Please choose reason" style="width:200px;">
                        <option value="">-- Select --</option>
                          <?php
                          foreach($rejectionResult as $reject){
                            ?>
                            <option value="<?php echo $reject['rejection_reason_id'];?>"><?php echo ucwords($reject['rejection_reason_name']);?></option>
                            <?php
                          }
                          ?>
                        </select></td>
                        
                      </tr>
                      <tr>
                        <td><label>Reviewed By</label></td>
                        <!--<td><input type="text" class="form-control" id="reviewedBy" name="reviewedBy" placeholder="Enter Reviewed By" title="Please enter reviewed by" style="width:100%;" /></td>-->
                        <td>
                          <select name="reviewedBy" id="reviewedBy" class="form-control" title="Please choose reviewed by" >
                            <option value="">-- Select --</option>
                            <?php
                            foreach($userResult as $uName){
                              ?>
                              <option value="<?php echo $uName['user_id'];?>" <?php echo ($uName['user_id']==$_SESSION['userId'])?"selected=selected":""; ?>><?php echo ucwords($uName['user_name']);?></option>
                              <?php
                            }
                            ?>
                          </select>
                         </td>
                        <?php
                        if($autoApprovalFieldStatus == 'show'){ ?>
                         <td><label>Approved By</label></td>
                         <!--<td><input type="text" class="form-control" id="approvedBy" name="approvedBy" placeholder="Enter Approved By" title="Please enter approved by" style="width:100%;" /></td>-->
                         <td>
                          <select name="approvedBy" id="approvedBy" class="form-control" title="Please choose approved by">
                            <option value="">-- Select --</option>
                            <?php
                            foreach($userResult as $uName){
                              ?>
                              <option value="<?php echo $uName['user_id'];?>" <?php echo ($uName['user_id']==$_SESSION['userId'])?"selected=selected":""; ?>><?php echo ucwords($uName['user_name']);?></option>
                              <?php
                            }
                            ?>
                          </select>
                         </td>
                         
                        <?php } else { ?>
                         <td colspan="2"></td>
                        <?php } ?>
                      </tr>
                      <tr>
                        <td><label for="labComments">Laboratory <br/>Scientist Comments</label></td>
                        <td colspan="5"><textarea class="form-control" name="labComments" id="labComments" title="Enter lab comments" style="width:100%"></textarea></td>
                        <!--<td><label for="dateOfReceivedStamp">Date Of Result</label></td>
                        <td><input type="text" class="form-control date" id="dateOfReceivedStamp" name="dateOfReceivedStamp" placeholder="Enter Date Received Stamp." title="Please enter date received stamp" style="width:100%;" /></td>-->
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
                <input type="hidden" name="sampleCodeTitle" id="sampleCodeTitle" value="<?php echo $arr['sample_code'];?>"/>
                <?php if($arr['sample_code']=='auto' || $arr['sample_code']=='YY' || $arr['sample_code']=='MMYY'){ ?>
                <input type="hidden" name="sampleCodeFormat" id="sampleCodeFormat" value="<?php echo $sFormat;?>"/>
                <input type="hidden" name="sampleCodeKey" id="sampleCodeKey" value="<?php echo $sKey;?>"/>
                <?php } ?>
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
  machineName = true;
  function validateNow(){
    flag = deforayValidator.init({
        formId: 'vlRequestForm'
    });
    $('.isRequired').each(function () {
            ($(this).val() == '') ? $(this).css('background-color', '#FFFF99') : $(this).css('background-color', '#FFFFFF')
    });
    $("#saveNext").val('save');
    if(flag){
      getMachineName();
      if(machineName){
        //check approve and review by name
        rBy = $("#reviewedBy").val();
        aBy = $("#approvedBy").val();
        globalValue = '<?php echo $arr["user_review_approve"];?>';
        if(aBy==rBy && (rBy!='' && aBy!='') && globalValue=='yes'){
          conf = confirm("Same person is reviewing and approving result!");
          if(conf){}else{
            return false;
          }
        }else if(aBy==rBy && (rBy!='' && aBy!='') && globalValue=='no'){
          alert("Same person is reviewing and approving result!");
          return false;
        }
      $.blockUI();
      document.getElementById('vlRequestForm').submit();
    }
    }
  }
  function validateSaveNow(){
    flag = deforayValidator.init({
        formId: 'vlRequestForm'
    });
    $('.isRequired').each(function () {
            ($(this).val() == '') ? $(this).css('background-color', '#FFFF99') : $(this).css('background-color', '#FFFFFF') 
    });
    $("#saveNext").val('next');
    if(flag){
      getMachineName();
      if(machineName){
        //check approve and review by name
        rBy = $("#reviewedBy").val();
        aBy = $("#approvedBy").val();
        globalValue = '<?php echo $arr["user_review_approve"];?>';
        if(aBy==rBy && (rBy!='' && aBy!='') && globalValue=='yes'){
          conf = confirm("Same person is reviewing and approving result!");
          if(conf){}else{
            return false;
          }
        }else if(aBy==rBy && (rBy!='' && aBy!='') && globalValue=='no'){
          alert("Same person is reviewing and approving result!");
          return false;
        }
      $.blockUI();
      document.getElementById('vlRequestForm').submit();
      }
    }
  }
  function getfacilityDetails(obj)
  {
    $.blockUI();
      var cName = $("#clinicName").val();
      var pName = $("#province").val();
      if(pName!='' && provinceName && facilityName){
        facilityName = false;
      }
    if(pName!=''){
      if(provinceName){
      $.post("../includes/getFacilityForClinic.php", { pName : pName},
      function(data){
	  if(data != ""){
            details = data.split("###");
            $("#clinicName").html(details[0]);
            $("#district").html(details[1]);
            $("#clinicianName").val(details[2]);
	  }
      });
      }
      <?php
      if($arr['sample_code']=='auto'){
        ?>
        pNameVal = pName.split("##");
        sCode = '<?php echo date('ymd');?>';
        sCodeKey = '<?php echo $maxId;?>';
        $(".serialNo1,.serialNo,.reqBarcode").val(pNameVal[1]+sCode+sCodeKey);
        $("#sampleCodeFormat").val(pNameVal[1]+sCode);
        $("#sampleCodeKey").val(sCodeKey);
        <?php
      }else if($arr['sample_code']=='YY' || $arr['sample_code']=='MMYY'){ ?>
        $(".serialNo1,.serialNo,.reqBarcode").val('<?php echo $prefix.$mnthYr.$maxId;?>');
        $("#sampleCodeFormat").val('<?php echo $prefix.$mnthYr;?>');
        $("#sampleCodeKey").val('<?php echo $maxId;?>');
        <?php
      }
      ?>
    }else if(pName=='' && cName==''){
      provinceName = true;
      facilityName = true;
      $("#province").html("<?php echo $province;?>");
      $("#clinicName").html("<?php echo $facility;?>");
    }
    $.unblockUI();
  }
  function getfacilityDistrictwise(obj)
  {
    $.blockUI();
    var dName = $("#district").val();
    var cName = $("#clinicName").val();
    if(dName!=''){
      $.post("../includes/getFacilityForClinic.php", {dName:dName,cliName:cName},
      function(data){
	  if(data != ""){
            $("#clinicName").html(data);
	  }
      });
    }
    $.unblockUI();
  }
  function getfacilityProvinceDetails(obj)
  {
    $.blockUI();
     //check facility name
      var cName = $("#clinicName").val();
      var pName = $("#province").val();
      if(cName!='' && provinceName && facilityName){
        provinceName = false;
      }
    if(cName!='' && facilityName){
      $.post("../includes/getFacilityForClinic.php", { cName : cName},
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
    $.unblockUI();
  }
  $(document).ready(function() {
    
  
  $("#vlResult").bind("keyup change", function(e) {
      if($("#vlResult").val() == "" && $("#vlLog").val() == "" ){
        $(".noResult").show();
      }else{
        $( "#noResultRejected" ).prop( "checked", false );
        $( "#noResultError" ).prop( "checked", false );
        $("#rejectionReason").removeClass("isRequired");
        $("#rejectionReason").val("");
        $(".noResult").hide();
      }
  });
  $("#vlLog").bind("keyup change", function(e) {
      if($("#vlResult").val() == "" && $("#vlLog").val() == "" ){
        $(".noResult").show();
      }else{
        $( "#noResultRejected" ).prop( "checked", false );
        $( "#noResultError" ).prop( "checked", false );
        $("#rejectionReason").removeClass("isRequired");
        $("#rejectionReason").val("");
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
   $('#sampleCollectionDate,#sampleReceivedDate,#sampleTestingDateAtLab').mask('99-aaa-9999 99:99');
   
   $('#sampleCollectionDate,#sampleReceivedDate,#sampleTestingDateAtLab').datetimepicker({
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
  
  function checkRejectedReason(){
    $("#rejectionReason").addClass("isRequired");
  }
  
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
      dobMonth = (dobMonth<10) ? '0'+dobMonth: dobMonth;
      var dobDate = (splitDob[0]<10) ? '0'+splitDob[0]: splitDob[0];
      
      var date1 = new Date(yyyy,mm,dd);
      var date2 = new Date(dobYear,dobMonth,dobDate);
      var diff = new Date(date1.getTime() - date2.getTime());
      if((diff.getUTCFullYear() - 1970) == 0){
        $("#ageInMonths").val(diff.getUTCMonth()); // Gives month count of difference
      }else{
        $("#ageInMonths").val("");
      }
      $("#ageInYears").val((diff.getUTCFullYear() - 1970)); // Gives difference as year
      //console.log(diff.getUTCDate() - 1); // Gives day count of difference
  }
  function setFacilityLabDetails(fDetails){
      $("#labId").val("");
      facilityArray = fDetails.split("##");
      $("#labId").val(facilityArray[0]);
    }
  function checkNameValidation(tableName,fieldName,obj,fnct,alrt,callback)
    {
        var removeDots=obj.value.replace(/\./g,"");
        var removeDots=removeDots.replace(/\,/g,"");
        //str=obj.value;
        removeDots = removeDots.replace(/\s{2,}/g,' ');

        $.post("../includes/checkDuplicate.php", { tableName: tableName,fieldName : fieldName ,value : removeDots.trim(),fnct : fnct, format: "html"},
        function(data){
            if(data==='1'){
                alert(alrt);
                duplicateName=false;
                $(".removeValue").val('');
            }
        });
    }
    
    function checkPatientIsPregnant(value){
      if(value=='yes'){
        $("select option[value*='pregnant_mother']").prop('disabled',false);
      }else{
        if($("#vlTestReason").val()=='pregnant_mother'){
          $("#vlTestReason").val('');
        }
        $("select option[value*='pregnant_mother']").prop('disabled',true);
      }
    }
    
    function checkSampleReceviedDate(){
      var sampleCollectionDate = $("#sampleCollectionDate").val();
      var sampleReceivedDate = $("#sampleReceivedDate").val();
      if($.trim(sampleCollectionDate)!= '' && $.trim(sampleReceivedDate)!= '') {
        //Set sample coll. datetime
        splitSampleCollDateTime = sampleCollectionDate.split(" ");
        splitSampleCollDate = splitSampleCollDateTime[0].split("-");
        var sampleCollOn = new Date(splitSampleCollDate[1] + splitSampleCollDate[2]+", "+splitSampleCollDate[0]);
        var monthDigit = sampleCollOn.getMonth();
        var smplCollYear = splitSampleCollDate[2];
        var smplCollMonth = isNaN(monthDigit) ? 0 : (parseInt(monthDigit)+parseInt(1));
        smplCollMonth = (smplCollMonth<10) ? '0'+smplCollMonth: smplCollMonth;
        var smplCollDate = splitSampleCollDate[0];
        sampleCollDateTime = smplCollYear+"-"+smplCollMonth+"-"+smplCollDate+" "+splitSampleCollDateTime[1]+":00";
        //Set sample rece. datetime
        splitSampleReceivedDateTime = sampleReceivedDate.split(" ");
        splitSampleReceivedDate = splitSampleReceivedDateTime[0].split("-");
        var sampleReceivedOn = new Date(splitSampleReceivedDate[1] + splitSampleReceivedDate[2]+", "+splitSampleReceivedDate[0]);
        var monthDigit = sampleReceivedOn.getMonth();
        var smplReceivedYear = splitSampleReceivedDate[2];
        var smplReceivedMonth = isNaN(monthDigit) ? 0 : (parseInt(monthDigit)+parseInt(1));
        smplReceivedMonth = (smplReceivedMonth<10) ? '0'+smplReceivedMonth: smplReceivedMonth;
        var smplReceivedDate = splitSampleReceivedDate[0];
        sampleReceivedDateTime = smplReceivedYear+"-"+smplReceivedMonth+"-"+smplReceivedDate+" "+splitSampleReceivedDateTime[1]+":00";
        //Check diff
        if(moment(sampleCollDateTime).diff(moment(sampleReceivedDateTime)) > 0) {
          alert("Sample Received Date could not be earlier than Sample Collection Date!");
          $("#sampleReceivedDate").val("");
        }
      }
    }
    
    function checkSampleTestingDate(){
      var sampleCollectionDate = $("#sampleCollectionDate").val();
      var sampleTestingDate = $("#sampleTestingDateAtLab").val();
      if($.trim(sampleCollectionDate)!= '' && $.trim(sampleTestingDate)!= '') {
        //Set sample coll. date
        splitSampleCollDateTime = sampleCollectionDate.split(" ");
        splitSampleCollDate = splitSampleCollDateTime[0].split("-");
        var sampleCollOn = new Date(splitSampleCollDate[1] + splitSampleCollDate[2]+", "+splitSampleCollDate[0]);
        var monthDigit = sampleCollOn.getMonth();
        var smplCollYear = splitSampleCollDate[2];
        var smplCollMonth = isNaN(monthDigit) ? 0 : (parseInt(monthDigit)+parseInt(1));
        smplCollMonth = (smplCollMonth<10) ? '0'+smplCollMonth: smplCollMonth;
        var smplCollDate = splitSampleCollDate[0];
        sampleCollDateTime = smplCollYear+"-"+smplCollMonth+"-"+smplCollDate+" "+splitSampleCollDateTime[1]+":00";
        //Set sample testing date
        splitSampleTestedDateTime = sampleTestingDate.split(" ");
        splitSampleTestedDate = splitSampleTestedDateTime[0].split("-");
        var sampleTestingOn = new Date(splitSampleTestedDate[1] + splitSampleTestedDate[2]+", "+splitSampleTestedDate[0]);
        var monthDigit = sampleTestingOn.getMonth();
        var smplTestingYear = splitSampleTestedDate[2];
        var smplTestingMonth = isNaN(monthDigit) ? 0 : (parseInt(monthDigit)+parseInt(1));
        smplTestingMonth = (smplTestingMonth<10) ? '0'+smplTestingMonth: smplTestingMonth;
        var smplTestingDate = splitSampleTestedDate[0];
        sampleTestingAtLabDateTime = smplTestingYear+"-"+smplTestingMonth+"-"+smplTestingDate+" "+splitSampleTestedDateTime[1]+":00";
        //Check diff
        if(moment(sampleCollDateTime).diff(moment(sampleTestingAtLabDateTime)) > 0) {
          alert("Sample Testing Date could not be earlier than Sample Collection Date!");
          $("#sampleTestingDateAtLab").val("");
        }
      }
    }
    
    function checkARTInitiationDate(){
      var dob = $("#dob").val();
      var artInitiationDate = $("#dateOfArtInitiation").val();
      if($.trim(dob)!= '' && $.trim(artInitiationDate)!= '') {
        //Set DOB date
        splitDob = dob.split("-");
        var dobDate = new Date(splitDob[1] + splitDob[2]+", "+splitDob[0]);
        var monthDigit = dobDate.getMonth();
        var dobYear = splitDob[2];
        var dobMonth = isNaN(monthDigit) ? 0 : (parseInt(monthDigit)+parseInt(1));
        dobMonth = (dobMonth<10) ? '0'+dobMonth: dobMonth;
        var dobDate = splitDob[0];
        dobDate = dobYear+"-"+dobMonth+"-"+dobDate;
        //Set ART initiation date
        splitArtIniDate = artInitiationDate.split("-");
        var artInigOn = new Date(splitArtIniDate[1] + splitArtIniDate[2]+", "+splitArtIniDate[0]);
        var monthDigit = artInigOn.getMonth();
        var artIniYear = splitArtIniDate[2];
        var artIniMonth = isNaN(monthDigit) ? 0 : (parseInt(monthDigit)+parseInt(1));
        artIniMonth = (artIniMonth<10) ? '0'+artIniMonth: artIniMonth;
        var artIniDate = splitArtIniDate[0];
        artIniDate = artIniYear+"-"+artIniMonth+"-"+artIniDate;
        //Check diff
        if(moment(dobDate).isAfter(artIniDate)) {
          alert("ART Initiation Date could not be earlier than DOB!");
          $("#dateOfArtInitiation").val("");
        }
      }
    }
    
    function checkLastVLTestDate(){
      var artInitiationDate = $("#dateOfArtInitiation").val();
      var dateOfLastVLTest = $("#lastViralLoadTestDate").val();
      if($.trim(artInitiationDate)!= '' && $.trim(dateOfLastVLTest)!= '') {
        //Set ART initiation date
        splitArtIniDate = artInitiationDate.split("-");
        var artInigOn = new Date(splitArtIniDate[1] + splitArtIniDate[2]+", "+splitArtIniDate[0]);
        var monthDigit = artInigOn.getMonth();
        var artIniYear = splitArtIniDate[2];
        var artIniMonth = isNaN(monthDigit) ? 0 : (parseInt(monthDigit)+parseInt(1));
        artIniMonth = (artIniMonth<10) ? '0'+artIniMonth: artIniMonth;
        var artIniDate = splitArtIniDate[0];
        artIniDate = artIniYear+"-"+artIniMonth+"-"+artIniDate;
        //Set Last VL Test date
        splitLastVLTestDate = dateOfLastVLTest.split("-");
        var lastVLTestOn = new Date(splitLastVLTestDate[1] + splitLastVLTestDate[2]+", "+splitLastVLTestDate[0]);
        var monthDigit = lastVLTestOn.getMonth();
        var lastVLTestYear = splitLastVLTestDate[2];
        var lastVLTestMonth = isNaN(monthDigit) ? 0 : (parseInt(monthDigit)+parseInt(1));
        lastVLTestMonth = (lastVLTestMonth<10) ? '0'+lastVLTestMonth: lastVLTestMonth;
        var lastVLTestDate = splitLastVLTestDate[0];
        lastVLTestDate = lastVLTestYear+"-"+lastVLTestMonth+"-"+lastVLTestDate;
        console.log(artIniDate);
        console.log(lastVLTestDate);
        //Check diff
        if(moment(artIniDate).isAfter(lastVLTestDate)) {
          alert("Last Viral Load Test Date could not be earlier than ART initiation date!");
          $("#lastViralLoadTestDate").val("");
        }
      }
    }
    
    //function checkReviewedApprovedBy(){
    //  var reviewedBy = $("#reviewedBy").val();
    //  var approvedBy = $("#approvedBy").val();
    //  if($.trim(reviewedBy)!= '' && $.trim(approvedBy)!= ''){
    //    if($.trim(reviewedBy) == $.trim(approvedBy)!= ''){
    //      alert("Same person is reviewing and approving result!");
    //    }
    //  }
    //}
    //check machine name and limit
    function getMachineName(){
      machineName = true;
      var mName = $("#testingPlatform").val();
      var absValue = $("#vlResult").val();
      if(mName!='' && absValue!=''){
        //split the value
        var result = mName.split("##");
        if(result[0]=='Roche' && absValue!='<20' && absValue!='>10000000'){
          var lowLimit = result[1];
          var highLimit = result[2];
            if(lowLimit!='' && lowLimit!=0 && parseInt(absValue) < 20){
              alert("Value outside machine detection limit");
              $("#vlResult").css('background-color', '#FFFF99');
              machineName = false;
            }else if(highLimit!='' && highLimit!=0 && parseInt(absValue) > 10000000){
              alert("Value outside machine detection limit");
              $("#vlResult").css('background-color', '#FFFF99');
              machineName  = false;
            }else{
              lessSign = absValue.split("<");
              greaterSign = absValue.split(">");
              if(lessSign.length>1){
                if(parseInt(lessSign[1])<parseInt(lowLimit)){
                alert("Invalid value.Value Lesser than machine detection limit.");
                $("#vlResult").css('background-color', '#FFFF99');
                }else if(parseInt(lessSign[1])>parseInt(highLimit)){
                  alert("Invalid value.Value Greater than machine detection limit.");
                  $("#vlResult").css('background-color', '#FFFF99');
                }else{
                  alert("Invalid value.");  
                }
                $("#vlResult").css('background-color', '#FFFF99');
                machineName = false;
              }else if(greaterSign.length>1){
                if(parseInt(greaterSign[1])<parseInt(lowLimit)){
                alert("Invalid value.Value Lesser than machine detection limit.");  
                }else if(parseInt(greaterSign[1])>parseInt(highLimit)){
                  alert("Invalid value.Value Greater than machine detection limit");  
                }else{
                  alert("Invalid value.");  
                }
                $("#vlResult").css('background-color', '#FFFF99')
                machineName = false;
              }
            }
        }
      }
    }
    
    function calculateLogValue(obj){
      if(obj.id=="vlResult") {
        absValue = $("#vlResult").val();
        if(absValue!='' && absValue!=0){
          $("#vlLog").val(Math.round(Math.log10(absValue) * 100) / 100);
        }
      }
      if(obj.id=="vlLog") {
        logValue = $("#vlLog").val();
        if(logValue!='' && logValue!=0){
          var absVal = Math.round(Math.pow(10,logValue) * 100) / 100;
          if(absVal!='Infinity'){
          $("#vlResult").val(Math.round(Math.pow(10,logValue) * 100) / 100);
          }else{
            $("#vlResult").val('');
          }
        }
      }
    }
    
    function checkPatientDetails(tableName,fieldName,obj,fnct)
    {
      if($.trim(obj.value)!=''){
        $.post("../includes/checkDuplicate.php", { tableName: tableName,fieldName : fieldName ,value : obj.value,fnct : fnct, format: "html"},
        function(data){
            if(data==='1'){
                showModal('patientModal.php?artNo='+obj.value,900,520);
            }
        });
      }
    }
  function setPatientDetails(pDetails){
      patientArray = pDetails.split("##");
      $("#patientFname").val(patientArray[0]);
      $("#surName").val(patientArray[1]);
      $("#patientPhoneNumber").val(patientArray[8]);
      if($.trim(patientArray[3])!=''){
        $("#dob").val(patientArray[3]);
        getAge();
      }else if($.trim(patientArray[4])!='' && $.trim(patientArray[4]) != 0){
        $("#ageInYears").val(patientArray[4]);
      }else if($.trim(patientArray[5])!=''){
        $("#ageInMonths").val(patientArray[5]);
      }
      if($.trim(patientArray[2])!=''){
        if(patientArray[2] == 'male' || patientArray[2] == 'not_recorded'){
        $('.femaleElements').hide();
        $('input[name="breastfeeding"]').prop('checked', false);
        $('input[name="patientPregnant"]').prop('checked', false);
          if(patientArray[2] == 'male'){
            $("#genderMale").prop('checked', true);
          }else{
            $("#genderNotRecorded").prop('checked', true);
          }
        }else if(patientArray[2] == 'female'){
          $('.femaleElements').show();
          $("#genderFemale").prop('checked', true);
          if($.trim(patientArray[6])!=''){
            if($.trim(patientArray[6])=='yes'){
              $("#pregYes").prop('checked', true);
            }else if($.trim(patientArray[6])=='no'){
              $("#pregNo").prop('checked', true);
            }
          }
          if($.trim(patientArray[7])!=''){
            if($.trim(patientArray[7])=='yes'){
              $("#breastfeedingYes").prop('checked', true);
            }else if($.trim(patientArray[7])=='no'){
              $("#breastfeedingNo").prop('checked', true);
            }
          }
        }
      }
      if($.trim(patientArray[9])!=''){
        if(patientArray[9] == 'yes'){
          $("#receivesmsYes").prop('checked', true);
          $("#patientPhoneNumber").removeAttr("disabled");
        }else if(patientArray[9] == 'no'){
          $("#receivesmsNo").prop('checked', true);
          $("#patientPhoneNumber").attr("disabled","disabled");
        }
      }
      if($.trim(patientArray[15])!=''){
      $("#patientArtNo").val($.trim(patientArray[15]));
      }
  }
  function getAge(){
    var dob = $("#dob").val();
    if($.trim(dob) == ""){
      $("#ageInMonths").val("");
      $("#ageInYears").val("");
      return false;
    }
    //calculate age
    splitDob = dob.split("-");
    var dobDate = new Date(splitDob[1] + splitDob[2]+", "+splitDob[0]);
    var monthDigit = dobDate.getMonth();
    var dobMonth = isNaN(monthDigit) ? 1 : (parseInt(monthDigit)+parseInt(1));
    dobMonth = (dobMonth<10) ? '0'+dobMonth: dobMonth;
    dob = splitDob[2]+'-'+dobMonth+'-'+splitDob[0];
    var years = moment().diff(dob, 'years',false);
    var months = (years == 0)?moment().diff(dob, 'months',false):'';
    $("#ageInYears").val(years); // Gives difference as years
    $("#ageInMonths").val(months); // Gives difference as months
  }
  function showPatientList()
  {
    $("#showEmptyResult").hide();
      if($.trim($("#artPatientNo").val())!=''){
        $.post("checkPatientExist.php", { artPatientNo : $("#artPatientNo").val()},
        function(data){
            if(data >= '1'){
                showModal('patientModal.php?artNo='+$.trim($("#artPatientNo").val()),900,520);
            }else{
              $("#showEmptyResult").show();
            }
        });
      }
  }
  
</script>
  
 <?php
 //include('../footer.php');
 ?>
