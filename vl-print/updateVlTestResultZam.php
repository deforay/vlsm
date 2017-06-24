<?php
ob_start();
include('../General.php');
$general=new Deforay_Commons_General();
//set province
$pdQuery="SELECT * from province_details";
$pdResult=$db->query($pdQuery);
$province = '';
$province.="<option value=''> -- Select -- </option>";
  foreach($pdResult as $provinceName){
    $province .= "<option value='".$provinceName['province_name']."##".$provinceName['province_code']."'>".ucwords($provinceName['province_name'])."</option>";
  }
  
$district = '';
$district.="<option value=''> -- Select -- </option>";

$facility = '';
$facility.="<option data-code='' data-emails='' data-mobile-nos='' data-contact-person='' value=''> -- Select -- </option>";
//set lab facilities
$lQuery="SELECT * FROM facility_details where facility_type='2' AND status='active'";
$lResult = $db->rawQuery($lQuery);
//get active sample types
$sampleTypeQuery="SELECT * from r_sample_type where status='active'";
$sampleTypeResult=$db->query($sampleTypeQuery);
//sample rejection reason
$rejectionQuery="SELECT * FROM r_sample_rejection_reasons WHERE rejection_reason_status ='active'";
$rejectionResult = $db->rawQuery($rejectionQuery);
//rejection type
$rejectionTypeQuery="SELECT DISTINCT rejection_type FROM r_sample_rejection_reasons WHERE rejection_reason_status ='active'";
$rejectionTypeResult = $db->rawQuery($rejectionTypeQuery);
//get active users
$userQuery="SELECT * FROM user_details where status='active'";
$userResult = $db->rawQuery($userQuery);
//get suspected treatment failure at
$suspectedTreatmentFailureAtQuery="SELECT DISTINCT vl_sample_suspected_treatment_failure_at FROM vl_request_form where vlsm_country_id='4'";
$suspectedTreatmentFailureAtResult = $db->rawQuery($suspectedTreatmentFailureAtQuery);
//get art regimen
$artQuery="SELECT * from r_art_code_details where nation_identifier='zam' AND art_status ='active'";
$artResult=$db->query($artQuery);
//get vl test reasons
$testReasonQuery="SELECT * FROM r_vl_test_reasons";
$testReasonResult = $db->rawQuery($testReasonQuery);
//edit section
$vlQuery="SELECT * from vl_request_form where vl_sample_id=$id";
$vlQueryInfo=$db->query($vlQuery);
//facility details
$efacilityQuery="SELECT * from facility_details where facility_id='".$vlQueryInfo[0]['facility_id']."'";
$efacilityResult=$db->query($efacilityQuery);
if(!isset($efacilityResult[0]['facility_code'])){
  $efacilityResult[0]['facility_code'] = '';
}
if(!isset($efacilityResult[0]['facility_state'])){
  $efacilityResult[0]['facility_state'] = '';
}
if(!isset($efacilityResult[0]['facility_district'])){
  $efacilityResult[0]['facility_district'] = '';
}
//province details
if(trim($efacilityResult[0]['facility_state'])!= ''){
  $estateQuery="SELECT * from province_details where province_name='".$efacilityResult[0]['facility_state']."'";
  $estateResult=$db->query($estateQuery);
}
if(!isset($estateResult[0]['province_code'])){
  $estateResult[0]['province_code'] = '';
}
//province's districts
$edistrictResult = array();
if(trim($efacilityResult[0]['facility_state'])!= ''){
  $edistrictQuery="SELECT DISTINCT facility_district from facility_details where facility_state='".$efacilityResult[0]['facility_state']."' AND status='active'";
  $edistrictResult=$db->query($edistrictQuery);
}
//facility lists
$efQuery="SELECT * FROM facility_details where facility_district = '".$efacilityResult[0]['facility_district']."' AND status='active'";
$efResult = $db->rawQuery($efQuery);
//dob
if(isset($vlQueryInfo[0]['patient_dob']) && trim($vlQueryInfo[0]['patient_dob'])!='' && $vlQueryInfo[0]['patient_dob']!= null && $vlQueryInfo[0]['patient_dob']!='0000-00-00'){
 $vlQueryInfo[0]['patient_dob']=$general->humanDateFormat($vlQueryInfo[0]['patient_dob']);
}else{
 $vlQueryInfo[0]['patient_dob']='';
}
//art initiation date
if(isset($vlQueryInfo[0]['treatment_initiated_date']) && trim($vlQueryInfo[0]['treatment_initiated_date'])!='' && $vlQueryInfo[0]['treatment_initiated_date']!= null && $vlQueryInfo[0]['treatment_initiated_date']!='0000-00-00'){
 $vlQueryInfo[0]['treatment_initiated_date']=$general->humanDateFormat($vlQueryInfo[0]['treatment_initiated_date']);
}else{
 $vlQueryInfo[0]['treatment_initiated_date']='';
}
//last viral load test date
if(isset($vlQueryInfo[0]['last_viral_load_date']) && trim($vlQueryInfo[0]['last_viral_load_date'])!='' && $vlQueryInfo[0]['last_viral_load_date']!= null && $vlQueryInfo[0]['last_viral_load_date']!='0000-00-00'){
 $vlQueryInfo[0]['last_viral_load_date']=$general->humanDateFormat($vlQueryInfo[0]['last_viral_load_date']);
}else{
 $vlQueryInfo[0]['last_viral_load_date']='';
}
//sample collected date
if(isset($vlQueryInfo[0]['sample_collection_date']) && trim($vlQueryInfo[0]['sample_collection_date'])!='' && $vlQueryInfo[0]['sample_collection_date']!= null && $vlQueryInfo[0]['sample_collection_date']!='0000-00-00 00:00:00'){
 $expStr=explode(" ",$vlQueryInfo[0]['sample_collection_date']);
 $vlQueryInfo[0]['sample_collection_date']=$general->humanDateFormat($expStr[0])." ".$expStr[1];
}else{
 $vlQueryInfo[0]['sample_collection_date']='';
}
//sample received date
if(isset($vlQueryInfo[0]['sample_received_at_vl_lab_datetime']) && trim($vlQueryInfo[0]['sample_received_at_vl_lab_datetime'])!='' && $vlQueryInfo[0]['sample_received_at_vl_lab_datetime']!= null && $vlQueryInfo[0]['sample_received_at_vl_lab_datetime']!='0000-00-00 00:00:00'){
 $expStr=explode(" ",$vlQueryInfo[0]['sample_received_at_vl_lab_datetime']);
 $vlQueryInfo[0]['sample_received_at_vl_lab_datetime']=$general->humanDateFormat($expStr[0])." ".$expStr[1];
}else{
 $vlQueryInfo[0]['sample_received_at_vl_lab_datetime']='';
}
//sample tested datetime
if(isset($vlQueryInfo[0]['sample_tested_datetime']) && trim($vlQueryInfo[0]['sample_tested_datetime'])!='' && $vlQueryInfo[0]['sample_tested_datetime']!= null && $vlQueryInfo[0]['sample_tested_datetime']!='0000-00-00 00:00:00'){
 $expStr=explode(" ",$vlQueryInfo[0]['sample_tested_datetime']);
 $vlQueryInfo[0]['sample_tested_datetime']=$general->humanDateFormat($expStr[0])." ".$expStr[1];
}else{
 $vlQueryInfo[0]['sample_tested_datetime']='';
}
//reviewed datetime
if(isset($vlQueryInfo[0]['result_reviewed_datetime']) && trim($vlQueryInfo[0]['result_reviewed_datetime'])!='' && $vlQueryInfo[0]['result_reviewed_datetime']!= null && $vlQueryInfo[0]['result_reviewed_datetime']!='0000-00-00 00:00:00'){
 $expStr=explode(" ",$vlQueryInfo[0]['result_reviewed_datetime']);
 $vlQueryInfo[0]['result_reviewed_datetime']=$general->humanDateFormat($expStr[0])." ".$expStr[1];
}else{
 $vlQueryInfo[0]['result_reviewed_datetime']='';
}
//get active sample test status
$statusQuery="SELECT * FROM r_sample_status where status = 'active'";
$statusResult = $db->rawQuery($statusQuery);
$disabled = "disabled = 'disabled'";
?>
<style>
  :disabled {background:white;}
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
      .table > tbody > tr > td{
        border-top:none;
      }
      .form-control,.form-group{
        width:100% !important;
      }
      .row{
        margin-top:6px;
      }
</style>
<!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1><i class="fa fa-edit"></i> VIRAL LOAD LABORATORY REQUEST FORM </h1>
      <ol class="breadcrumb">
        <li><a href="../dashboard/index.php"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Enter Result</li>
      </ol>
    </section>
    <!-- Main content -->
    <section class="content">
      <!-- SELECT2 EXAMPLE -->
      <div class="box box-default">
        <div class="box-header with-border">
          <div class="pull-right" style="font-size:15px;"><span class="mandatory">*</span> indicates required field &nbsp;</div>
        </div>
        <div class="box-body">
          <!-- form start -->
            <form class="form-inline" method="post" name="vlRequestFormZam" id="vlRequestFormZam" autocomplete="off" action="updateVlRequestHelperZam.php">
              <div class="box-body">
                 <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Section 1: Health Facility Information</h3>
                    </div>
                    <div class="box-body">
                      <div class="row">
                        <div class="col-xs-4 col-md-4">
                          <div class="form-group">
                            <label for="sampleCode">Sample ID <span class="mandatory">*</span></label>
                            <input type="text" class="form-control isRequired" id="sampleCode" name="sampleCode" placeholder="Enter Sample ID" title="Please enter sample id" value="<?php echo $vlQueryInfo[0]['sample_code']; ?>" <?php echo $disabled; ?> style="width:100%;"/>
                          </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-xs-4 col-md-4">
                          <div class="form-group">
                          <label for="province">Province <span class="mandatory">*</span></label>
                            <select class="form-control isRequired" name="province" id="province" title="Please choose province" <?php echo $disabled; ?> style="width:100%;" onchange="getProvinceDistricts(this);">
                              <option value=""> -- Select -- </option>
                              <?php foreach($pdResult as $provinceName){ ?>
                                <option value="<?php echo $provinceName['province_name']."##".$provinceName['province_code'];?>" <?php echo ($efacilityResult[0]['facility_state']."##".$estateResult[0]['province_code']==$provinceName['province_name']."##".$provinceName['province_code'])?"selected='selected'":""?>><?php echo ucwords($provinceName['province_name']);?></option>;
                              <?php } ?>
                            </select>
                          </div>
                        </div>
                        <div class="col-xs-4 col-md-4">
                          <div class="form-group">
                          <label for="district">District  <span class="mandatory">*</span></label>
                            <select class="form-control isRequired" name="district" id="district" title="Please choose district" <?php echo $disabled; ?> style="width:100%;" onchange="getFacilities(this);">
                              <option value=""> -- Select -- </option>
                                <?php
                                foreach($edistrictResult as $districtName){
                                  ?>
                                  <option value="<?php echo $districtName['facility_district'];?>" <?php echo ($efacilityResult[0]['facility_district']==$districtName['facility_district'])?"selected='selected'":""?>><?php echo ucwords($districtName['facility_district']);?></option>
                                  <?php
                                }
                                ?>
                            </select>
                          </div>
                        </div>
                        <div class="col-xs-4 col-md-4">
                          <div class="form-group">
                            <label for="fName">Health Facility <span class="mandatory">*</span></label>
                              <select class="form-control isRequired" id="fName" name="fName" title="Please select health facility" <?php echo $disabled; ?> style="width:100%;" onchange="fillFacilityDetails();">
                                <option data-code="" data-emails="" data-mobile-nos="" value=""> -- Select -- </option>
                                <?php foreach($efResult as $fDetails){ ?>
                                  <option data-code="<?php echo $fDetails['facility_code']; ?>" data-emails="<?php echo $fDetails['facility_emails']; ?>" data-mobile-nos="<?php echo $fDetails['facility_mobile_numbers']; ?>" data-contact-person="<?php echo ucwords($fDetails['contact_person']); ?>" value="<?php echo $fDetails['facility_id'];?>" <?php echo ($vlQueryInfo[0]['facility_id']==$fDetails['facility_id'])?"selected='selected'":""?>><?php echo ucwords($fDetails['facility_name']);?></option>
                                <?php } ?>
                              </select>
                            </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-xs-4 col-md-4">
                          <div class="form-group">
                          <label for="fCode">Health Facility Code </label>
                            <input type="text" class="form-control" style="width:100%;" name="fCode" id="fCode" placeholder="Health Facility Code" title="Please enter health facility code" value="<?php echo $efacilityResult[0]['facility_code']; ?>" <?php echo $disabled; ?>>
                          </div>
                        </div>
                        <div class="col-xs-4 col-md-4">
                          <div class="form-group">
                          <label for="reqClinician">Requesting Officer </label>
                            <input type="text" class="form-control" style="width:100%;" name="reqClinician" id="reqClinician" placeholder="Requesting Officer" title="Please enter requesting officer" value="<?php echo $vlQueryInfo[0]['request_clinician_name']; ?>" <?php echo $disabled; ?>>
                          </div>
                        </div>
                        <div class="col-xs-4 col-md-4">
                          <div class="form-group">
                          <label for="reqClinicianPhoneNumber">Contact Number </label>
                            <input type="text" class="form-control checkNum" style="width:100%;" name="reqClinicianPhoneNumber" id="reqClinicianPhoneNumber" placeholder="Contact Number" title="Please enter contact number" maxlength="15" value="<?php echo $vlQueryInfo[0]['request_clinician_phone_number']; ?>" <?php echo $disabled; ?>>
                          </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-xs-4 col-md-4">
                          <div class="form-group">
                           <label for="labId">Referral Laboratory for Sample </label>
                            <select class="form-control" name="labId" id="labId" title="Please choose laboratory" style="width:100%;">
                              <option value=""> -- Select -- </option>
                                <?php
                                foreach($lResult as $lab){
                                  ?>
                                  <option value="<?php echo $lab['facility_id'];?>" <?php echo(trim($vlQueryInfo[0]['lab_id']) >0 && $vlQueryInfo[0]['lab_id'] == $lab['facility_id'])?'selected="selected"':''; ?>><?php echo ucwords($lab['facility_name']);?></option>
                                  <?php
                                }
                                ?>
                              <option value="other"> Other </option>
                            </select>
                            <input class="form-control newLab" name="newLab" id="newLab" placeholder="Lab Name" title="Please enter lab name" style="width:100%;margin-top: 2px;display:none;" type="text">
                          </div>
                        </div>
                      </div>
                    </div>
                </div>
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Section 2: Patient Information</h3>
                    </div>
                    <div class="box-body">
                      <div class="row">
                        <div class="col-xs-4 col-md-4">
                          <div class="form-group">
                          <label for="surName">Surname </label>
                            <input type="text" class="form-control" style="width:100%;" name="surName" id="surName" placeholder="Patient Surname" title="Please enter patient surname" value="<?php echo $vlQueryInfo[0]['patient_last_name']; ?>" <?php echo $disabled; ?>>
                          </div>
                        </div>
                        <div class="col-xs-4 col-md-4">
                          <div class="form-group">
                          <label for="patientFname">First Name </label>
                            <input type="text" class="form-control" style="width:100%;" name="patientFname" id="patientFname" placeholder="Patient First Name" title="Please enter patient first name" value="<?php echo $vlQueryInfo[0]['patient_first_name']; ?>" <?php echo $disabled; ?>>
                          </div>
                        </div>
                        <div class="col-xs-4 col-md-4">
                          <div class="form-group">
                          <label for="patientArtNo">ART No. <span class="mandatory">*</span></label>
                            <input type="text" class="form-control isRequired" style="width:100%;" name="patientArtNo" id="patientArtNo" placeholder="ART Number" title="Please enter patient ART number" value="<?php echo $vlQueryInfo[0]['patient_art_no']; ?>" <?php echo $disabled; ?>>
                          </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-xs-4 col-md-4">
                          <div class="form-group">
                          <label for="patientPhoneNumber">Contact No. </label>
                            <input type="text" class="form-control checkNum" style="width:100%;" name="patientPhoneNumber" id="patientPhoneNumber" placeholder="Patient Contact No." title="Please enter patient contact no." maxlength="15" value="<?php echo $vlQueryInfo[0]['patient_mobile_number']; ?>" <?php echo $disabled; ?>>
                          </div>
                        </div>
                        <div class="col-xs-4 col-md-4">
                          <div class="form-group">
                          <label for="">DOB </label>
                            <input type="text" class="form-control date" style="width:100%;" name="dob" id="dob" placeholder="DOB" title="Please enter patient date of birth" value="<?php echo $vlQueryInfo[0]['patient_dob']; ?>" <?php echo $disabled; ?> onchange="getAgeInWeeks();">
                          </div>
                        </div>
                        <div class="col-xs-4 col-md-4">
                          <div class="form-group">
                          <label for="ageInWeeks">Age(weeks) </label>
                            <input type="text" class="form-control checkNum" style="width:100%;" name="ageInWeeks" id="ageInWeeks" placeholder="Age in Weeks" title="Please enter age in weeks" <?php echo $disabled; ?>>
                            <input type="hidden" name="ageInYears" id="ageInYears">
                          </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-xs-4 col-md-4">
                          <div class="form-group">
                           <label for="gender">Sex</label><br>
                            <label class="radio-inline" style="margin-left:0px;">
                              <input class="" id="genderMale" name="gender" value="male" title="Please check gender" type="radio" <?php echo ($vlQueryInfo[0]['patient_gender']=='male')?"checked='checked'":""?> <?php echo $disabled; ?>>Male
                            </label>
                            <label class="radio-inline" style="margin-left:0px;">
                              <input class="" id="genderFemale" name="gender" value="female" type="radio" <?php echo ($vlQueryInfo[0]['patient_gender']=='female')?"checked='checked'":""?> <?php echo $disabled; ?>>Female
                            </label>
                            <label class="radio-inline" style="margin-left:0px;">
                              <input class="" id="genderNotRecorded" name="gender" value="not_recorded" type="radio" <?php echo ($vlQueryInfo[0]['patient_gender']=='not_recorded')?"checked='checked'":""?> <?php echo $disabled; ?>>Not Recorded
                            </label>
                          </div>
                        </div>
                        <div class="col-xs-4 col-md-4">
                          <div class="form-group">
                           <label for="">Is Patient Pregnant? </label><br>
                            <label class="radio-inline">
                              <input class="" id="pregYes" name="patientPregnant" value="yes" title="Please check one" type="radio" <?php echo ($vlQueryInfo[0]['is_patient_pregnant']=='yes')?"checked='checked'":""?> <?php echo (trim($vlQueryInfo[0]['patient_gender'])!='' && $vlQueryInfo[0]['patient_gender']!= null && $vlQueryInfo[0]['patient_gender']!='female')?'disabled':''; ?> <?php echo $disabled; ?>> Yes
                              </label>
                            <label class="radio-inline">
                              <input class="" id="pregNo" name="patientPregnant" value="no" type="radio" <?php echo ($vlQueryInfo[0]['is_patient_pregnant']=='no')?"checked='checked'":""?> <?php echo (trim($vlQueryInfo[0]['patient_gender'])!='' && $vlQueryInfo[0]['patient_gender']!= null && $vlQueryInfo[0]['patient_gender']!='female')?'disabled':''; ?> <?php echo $disabled; ?>> No
                            </label>
                          </div>
                        </div>
                        <div class="col-xs-4 col-md-4">
                          <div class="form-group">
                          <label for="lineOfTreatment">Treatment Stage </label>
                            <select name="lineOfTreatment" id="lineOfTreatment" class="form-control" title="Please choose treatment stage" <?php echo (trim($vlQueryInfo[0]['patient_gender'])!='' && $vlQueryInfo[0]['patient_gender']!= null && $vlQueryInfo[0]['patient_gender']!='female')?'disabled':''; ?> <?php echo $disabled; ?> style="width:100%;">
                              <option value=""> -- Select -- </option>
                              <option value="1" <?php echo ($vlQueryInfo[0]['line_of_treatment']=='1')?"selected='selected'":""?>>1st Line</option>
                              <option value="2" <?php echo ($vlQueryInfo[0]['line_of_treatment']=='2')?"selected='selected'":""?>>2nd Line</option>
                              <option value="3" <?php echo ($vlQueryInfo[0]['line_of_treatment']=='3')?"selected='selected'":""?>>3rd Line</option>
                            </select>
                          </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-xs-4 col-md-4">
                          <div class="form-group">
                           <label for="">Date of ART Initiation </label><br>
                             <input type="text" class="form-control date" style="width:100%;" name="dateOfArtInitiation" id="dateOfArtInitiation" placeholder="Date Of ART Initiated" title="Please enter date of art initiated" value="<?php echo $vlQueryInfo[0]['treatment_initiated_date']; ?>" <?php echo $disabled; ?>>
                          </div>
                        </div>
                        <div class="col-xs-4 col-md-4">
                          <div class="form-group">
                           <label for="">ART Regimen </label><br>
                             <select class="form-control" id="artRegimen" name="artRegimen" title="Please choose ART Regimen" <?php echo $disabled; ?> style="width:100%;" onchange="checkARTValue();">
                                 <option value=""> -- Select -- </option>
                                 <?php
                                 foreach($artResult as $regimen){
                                 ?>
                                  <option value="<?php echo $regimen['art_code']; ?>" <?php echo ($vlQueryInfo[0]['current_regimen']==$regimen['art_code'])?"selected='selected'":""?>><?php echo $regimen['art_code']; ?></option>
                                 <?php
                                 }
                                 ?>
                                 <option value="other">Other</option>
                            </select>
                            <input type="text" class="form-control newArtRegimen" name="newArtRegimen" id="newArtRegimen" placeholder="ART Regimen" title="Please enter art regimen" style="width:100%;display:none;margin-top:2px;" >
                          </div>
                        </div>
                        <div class="col-xs-4 col-md-4">
                          <div class="form-group">
                           <label for="">Reason VL Requested </label><br>
                            <select name="vlTestReason" id="vlTestReason" class="form-control" title="Please choose Reason For VL test" <?php echo $disabled; ?> style="width:100%;" onchange="checkTestReason();">
                              <option value=""> -- Select -- </option>
                              <?php
                              foreach($testReasonResult as $testReason){
                                ?>
                                <option value="<?php echo $testReason['test_reason_name'];?>" <?php echo ($vlQueryInfo[0]['reason_for_vl_testing']==$testReason['test_reason_name'])?"selected='selected'":""?>><?php echo ucwords($testReason['test_reason_name']);?></option>
                                <?php
                              }
                              ?>
                              <option value="other">Other</option>
                            </select>
                           <input type="text" class="form-control newVlTestReason" name="newVlTestReason" id="newVlTestReason" placeholder="VL Test Reason" title="Please enter VL Test Reason" style="width:100%;display:none;margin-top:2px;">
                          </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-xs-4 col-md-4">
                          <div class="form-group">
                           <label for="">Date Of Last Viral Load Test </label><br>
                             <input type="text" class="form-control date" style="width:100%;" name="lastViralLoadTestDate" id="lastViralLoadTestDate" placeholder="Date Of Last Viral Load Test" title="Please enter date of last viral load test" value="<?php echo $vlQueryInfo[0]['last_viral_load_date']; ?>" <?php echo $disabled; ?>>
                          </div>
                        </div>
                        <div class="col-xs-4 col-md-4">
                          <div class="form-group">
                           <label for="lastViralLoadResult">Result Of Last Viral Load(copies/ml) </label><br>
                             <input type="text" class="form-control checkNum" style="width:100%;" name="lastViralLoadResult" id="lastViralLoadResult" placeholder="Result Of Last Viral Load" title="Please enter result of last viral load test" value="<?php echo $vlQueryInfo[0]['last_viral_load_result']; ?>" <?php echo $disabled; ?>>
                          </div>
                        </div>
                        <div class="col-xs-4 col-md-4">
                          <div class="form-group">
                           <label for="enhancedSession">Enhanced Sessions </label>
                            <select name="enhancedSession" id="enhancedSession" class="form-control" title="Please choose enhanced session" <?php echo $disabled; ?> style="width:100%;">
                              <option value=""> -- Select -- </option>
                              <option value="1" <?php echo ($vlQueryInfo[0]['number_of_enhanced_sessions']=='1')?"selected='selected'":""?>>1</option>
                              <option value="2" <?php echo ($vlQueryInfo[0]['number_of_enhanced_sessions']=='2')?"selected='selected'":""?>>2</option>
                              <option value="3" <?php echo ($vlQueryInfo[0]['number_of_enhanced_sessions']=='3')?"selected='selected'":""?>>3</option>
                              <option value=">3" <?php echo ($vlQueryInfo[0]['number_of_enhanced_sessions']=='>3')?"selected='selected'":""?>> > 3</option>
                            </select>
                          </div>
                        </div>
                      </div>
                    </div>
                </div>
                <div class="box box-primary">
                  <div class="box-header with-border">
                    <h3 class="box-title">Section 3: Specimen Information</h3>
                  </div>
                  <div class="box-body">
                    <div class="row">
                      <div class="col-xs-4 col-md-4">
                          <div class="form-group">
                          <label for="specimenType">Sample Type <span class="mandatory">*</span></label>
                            <select name="specimenType" id="specimenType" class="form-control isRequired" title="Please choose specimen type" <?php echo $disabled; ?> style="width:100%;">
                              <option value=""> -- Select -- </option>
                              <?php
                              foreach($sampleTypeResult as $sampleType){
                               ?>
                               <option value="<?php echo $sampleType['sample_id'];?>" <?php echo ($vlQueryInfo[0]['sample_type']== $sampleType['sample_id'])?"selected='selected'":""?>><?php echo ucwords($sampleType['sample_name']);?></option>
                               <?php
                              }
                              ?>
                            </select>
                          </div>
                      </div>
                      <div class="col-xs-4 col-md-4">
                          <div class="form-group">
                           <label for="">Repeat Sample? (due to rejection) </label><br>
                            <label class="radio-inline">
                              <input id="sampleReorderedYes" name="sampleReordered" value="yes" title="Please check one" type="radio" <?php echo ($vlQueryInfo[0]['sample_reordered']=='yes')?"checked='checked'":""?> <?php echo $disabled; ?>> Yes
                              </label>
                            <label class="radio-inline">
                              <input id="sampleReorderedNo" name="sampleReordered" value="no" type="radio" <?php echo ($vlQueryInfo[0]['sample_reordered']=='no')?"checked='checked'":""?> <?php echo $disabled; ?>> No
                            </label>
                          </div>
                      </div>
                      <div class="col-xs-4 col-md-4">
                          <div class="form-group">
                           <label for="">Date Sample Collected <span class="mandatory">*</span></label><br>
                             <input type="text" class="form-control isRequired" style="width:100%;" name="sampleCollectionDate" id="sampleCollectionDate" placeholder="Sample Collected Date" title="Please enter sample collected date" value="<?php echo $vlQueryInfo[0]['sample_collection_date']; ?>" <?php echo $disabled; ?>>
                          </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-xs-4 col-md-4">
                          <div class="form-group">
                          <label for="visitType">Visit Type </label>
                            <select name="visitType" id="visitType" class="form-control" title="Please choose visit type" <?php echo $disabled; ?> style="width:100%;">
                              <option value=""> -- Select -- </option>
                              <option value="6month" <?php echo ($vlQueryInfo[0]['sample_visit_type']== '6month')?"selected='selected'":""?>>6 Months</option>
                              <option value="12month" <?php echo ($vlQueryInfo[0]['sample_visit_type']== '12month')?"selected='selected'":""?>>12 Months</option>
                              <option value="repeat" <?php echo ($vlQueryInfo[0]['sample_visit_type']== 'repeat')?"selected='selected'":""?>>Repeat</option>
                            </select>
                          </div>
                      </div>
                      <div class="col-xs-4 col-md-4">
                          <div class="form-group">
                          <label for="suspectedTreatmentFailureAt">Suspected Treatment Failure at </label>
                            <select name="suspectedTreatmentFailureAt" id="suspectedTreatmentFailureAt" class="form-control" title="Please choose suspected treatment failure at" <?php echo $disabled; ?> style="width:100%;">
                              <option value=""> -- Select -- </option>
                              <?php
                              foreach($suspectedTreatmentFailureAtResult as $stfat){
                              ?>
                                <option value="<?php echo $stfat['vl_sample_suspected_treatment_failure_at']; ?>" <?php echo ($vlQueryInfo[0]['vl_sample_suspected_treatment_failure_at']== $stfat['vl_sample_suspected_treatment_failure_at'])?"selected='selected'":""?>><?php echo ucwords($stfat['vl_sample_suspected_treatment_failure_at']); ?></option>
                              <?php } ?>
                              <option value="other">Other(Specify)</option>
                            </select>
                            <input class="form-control newSuspectedTreatmentFailureAt" name="newSuspectedTreatmentFailureAt" id="newSuspectedTreatmentFailureAt" placeholder="Treatment Failure At" title="Please enter treatment failure at" style="width:100%;margin-top: 2px;display:none;" type="text">
                          </div>
                      </div>
                      <div class="col-xs-4 col-md-4">
                          <div class="form-group">
                          <label for="collectedBy">Collected By </label>
                            <select name="collectedBy" id="collectedBy" class="form-control" title="Please choose collected by" <?php echo $disabled; ?> style="width:100%;">
                              <option value=""> -- Select -- </option>
                              <?php
                              foreach($userResult as $user){
                                ?>
                                <option value="<?php echo $user['user_id'];?>" <?php echo ($vlQueryInfo[0]['sample_collected_by']== $user['user_id'])?"selected='selected'":""?>><?php echo ucwords($user['user_name']);?></option>
                                <?php
                              }
                              ?>
                            </select>
                          </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-xs-8 col-md-8">
                          <div class="form-group">
                          <label for="facilityComments">Facility Comments </label>
                            <textarea class="form-control" name="facilityComments" id="facilityComments" placeholder="Facility Comments" title="Please enter comments" <?php echo $disabled; ?> style="width:100%"><?php echo $vlQueryInfo[0]['facility_comments']; ?></textarea>
                          </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="box box-primary">
                  <div class="box-header with-border">
                    <h3 class="box-title">Section 4: For Laboratory Use Only</h3>
                  </div>
                  <div class="box-body">
                    <div class="row">
                      <div class="col-xs-4 col-md-4">
                          <div class="form-group">
                           <label for="">Date Sample Received at the Lab </label><br>
                             <input type="text" class="form-control" style="width:100%;" name="sampleReceivedOn" id="sampleReceivedOn" placeholder="Sample Received Date" title="Please enter sample received date at lab" value="<?php echo $vlQueryInfo[0]['sample_received_at_vl_lab_datetime']; ?>">
                          </div>
                      </div>
                      <div class="col-xs-4 col-md-4">
                          <div class="form-group">
                           <label for="">Date Test Performed </label><br>
                             <input type="text" class="form-control" style="width:100%;" name="sampleTestingDateAtLab" id="sampleTestingDateAtLab" placeholder="Test Performed Date" title="Please enter test performed date" value="<?php echo $vlQueryInfo[0]['sample_tested_datetime']; ?>">
                          </div>
                      </div>
                      <div class="col-xs-4 col-md-4">
                        <div class="form-group">
                          <label for="sampleValidity">Sample Validity </label><br>
                            <label class="radio-inline">
                             <input class="" id="sampleValidityYes" name="sampleValidity" value="passed" title="Please check one" type="radio" <?php echo ($vlQueryInfo[0]['sample_test_quality']=='passed')?"checked='checked'":""?>> Accepted
                            </label>
                            <label class="radio-inline">
                             <input class="" id="sampleValidityNo" name="sampleValidity" value="invalid" title="Please check one" type="radio" <?php echo ($vlQueryInfo[0]['sample_test_quality']=='invalid')?"checked='checked'":""?>> Rejected
                            </label>
                        </div>
                      </div>
                    </div>
                    <div class="row rejectionSection" style="display:<?php echo (trim($vlQueryInfo[0]['sample_test_quality'])!='' && $vlQueryInfo[0]['sample_test_quality']!= null && $vlQueryInfo[0]['sample_test_quality']=='invalid')?'':'none'; ?>;">
                      <div class="col-xs-4 col-md-4">
                        <div class="form-group">
                         <label for="">If Rejected, Repeat Sample Collection </label><br>
                         <label class="radio-inline">
                           <input class="" id="repeatSampleCollectionYes" name="repeatSampleCollection" value="yes" title="Please check one" type="radio" <?php echo ($vlQueryInfo[0]['repeat_sample_collection']=='yes')?"checked='checked'":""?>> Yes
                           </label>
                         <label class="radio-inline">
                           <input class="" id="repeatSampleCollectionNo" name="repeatSampleCollection" value="no" type="radio" <?php echo ($vlQueryInfo[0]['repeat_sample_collection']=='no')?"checked='checked'":""?>> No
                         </label>
                        </div>
                      </div>
                      <div class="col-xs-4 col-md-4">
                        <div class="form-group">
                          <label for="rejectionReason">Reason For Rejection </label>
                          <select class="form-control" id="rejectionReason" name="rejectionReason" title="Please choose rejection reason">
                            <option value="">-- Select --</option>
                            <?php foreach($rejectionTypeResult as $type) { ?>
                            <optgroup label="<?php echo ucwords($type['rejection_type']); ?>">
                              <?php
                              foreach($rejectionResult as $reject){
                                if($type['rejection_type'] == $reject['rejection_type']){
                                ?>
                                <option value="<?php echo $reject['rejection_reason_id'];?>" <?php echo ($vlQueryInfo[0]['reason_for_sample_rejection']== $reject['rejection_reason_id'])?"selected='selected'":""?>><?php echo ucwords($reject['rejection_reason_name']);?></option>
                                <?php
                                }
                              }
                              ?>
                            </optgroup>
                            <?php } ?>
                            <option value="other">Other (Please Specify) </option>
                          </select>
                          <input type="text" class="form-control newRejectionReason" name="newRejectionReason" id="newRejectionReason" placeholder="Rejection Reason" title="Please enter rejection reason" style="width:100%;display:none;margin-top:2px;">
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="box box-primary">
                  <div class="box-header with-border">
                    <h3 class="box-title">Section 5: Results</h3>
                  </div>
                  <div class="box-body">
                    <div class="row">
                      <div class="col-xs-4 col-md-4">
                          <div class="form-group">
                          <label for="result">Results </label>
                            <select name="result" id="result" class="form-control" title="Please choose test result" style="width:100%;">
                              <option value=""> -- Select -- </option>
                              <option value="tnd" <?php echo ($vlQueryInfo[0]['result']== 'tnd')?"selected='selected'":""?>>Target Not Detected</option>
                              <option value="actual_copies" <?php echo (trim($vlQueryInfo[0]['result'])!= '' && is_numeric($vlQueryInfo[0]['result']))?"selected='selected'":""?>>Actual Copies</option>
                              <option value="invalid" <?php echo ($vlQueryInfo[0]['result']== 'invalid')?"selected='selected'":""?>>Invalid</option>
                              <option value="repeat" <?php echo ($vlQueryInfo[0]['result']== 'repeat')?"selected='selected'":""?>>Repeat Sample Collection</option>
                            </select>
                            <span class="vlResult" style="display:<?php echo (trim($vlQueryInfo[0]['result'])!='' && $vlQueryInfo[0]['result']!= null && is_numeric($vlQueryInfo[0]['result']))?'':'none'; ?>;"><input class="form-control checkNum" name="vlResult" id="vlResult" placeholder="Viral Load Result" title="Please enter vl result" style="width:75% !important;margin-top: 2px;" type="text" value="<?php echo $vlQueryInfo[0]['result_value_absolute']; ?>">&nbsp;(copiesl/ml)</span>
                          </div>
                      </div>
                      <div class="col-xs-4 col-md-4">
                          <div class="form-group">
                          <label for="reviewedBy">Reviewed By </label>
                            <select name="reviewedBy" id="reviewedBy" class="form-control" title="Please choose reviewed by" style="width:100%;">
                              <option value=""> -- Select -- </option>
                              <?php
                              foreach($userResult as $user){
                                ?>
                                <option value="<?php echo $user['user_id'];?>" <?php echo ($vlQueryInfo[0]['result_reviewed_by']== $user['user_id'])?"selected='selected'":""?>><?php echo ucwords($user['user_name']);?></option>
                                <?php
                              }
                              ?>
                            </select>
                          </div>
                      </div>
                      <div class="col-xs-4 col-md-4">
                          <div class="form-group">
                           <label for="">Reviewed By Datetime </label><br>
                             <input type="text" class="form-control" style="width:100%;" name="reviewedByDatetime" id="reviewedByDatetime" placeholder="Reviewed by Date" title="Please enter reviewed by date" value="<?php echo $vlQueryInfo[0]['result_reviewed_datetime']; ?>">
                          </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-xs-4 col-md-4">
                          <div class="form-group">
                           <label for="labContactPerson">Lab Staff Name </label><br>
                             <input type="text" class="form-control" style="width:100%;" name="labContactPerson" id="labContactPerson" placeholder="Lab Staff Name" title="Please enter lab staff name" value="<?php echo $vlQueryInfo[0]['lab_contact_person']; ?>">
                          </div>
                      </div>
                      <div class="col-xs-8 col-md-8">
                          <div class="form-group">
                          <label for="labComments">Laboratory Comments </label>
                            <textarea class="form-control" name="labComments" id="labComments" placeholder="Lab Comments" title="Please enter comments" style="width:100%"><?php echo $vlQueryInfo[0]['approver_comments']; ?></textarea>
                          </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-xs-4 col-md-4">
                          <div class="form-group">
                           <label for="status">Status <span class="mandatory">*</span></label><br>
                              <select class="form-control isRequired" id="status" name="status" title="Please select test status">
                                <option value="">-- Select --</option>
                                <?php
                                foreach($statusResult as $status){
                                ?>
                                  <option value="<?php echo $status['status_id']; ?>"<?php echo ($vlQueryInfo[0]['result_status'] == $status['status_id']) ? 'selected="selected"':'';?>><?php echo ucwords($status['status_name']); ?></option>
                                <?php } ?>
                              </select>
                          </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="box-footer">
                  <input type="hidden" name="vlSampleId" id="vlSampleId" value="<?php echo $vlQueryInfo[0]['vl_sample_id'];?>"/>
                  <a class="btn btn-primary" href="javascript:void(0);" onclick="validateNow();return false;">Save</a>&nbsp;
                  <a href="/vl-print/vlTestResult.php" class="btn btn-default"> Cancel</a>
                </div>
              </div>
            </form>
        </div>
      </div>
    </section>
  </div>
  <script>
    $(document).ready(function() {
        $('.date').datepicker({
           changeMonth: true,
           changeYear: true,
           dateFormat: 'dd-M-yy',
           timeFormat: "hh:mm TT",
           maxDate: "Today",
           yearRange: <?php echo (date('Y') - 100); ?> + ":" + "<?php echo (date('Y')) ?>"
          }).click(function(){
              $('.ui-datepicker-calendar').show();
         });
        $('#sampleCollectionDate,#sampleReceivedOn,#sampleTestingDateAtLab,#reviewedByDatetime').datetimepicker({
            changeMonth: true,
            changeYear: true,
            dateFormat: 'dd-M-yy',
            timeFormat: "HH:mm",
            maxDate: "Today",
            onChangeMonthYear: function(year, month, widget) {
                  setTimeout(function() {
                     $('.ui-datepicker-calendar').show();
                  });
            },
            yearRange: <?php echo (date('Y') - 100); ?> + ":" + "<?php echo (date('Y')) ?>"
            }).click(function(){
               $('.ui-datepicker-calendar').show();
            });
        $('.date').mask('99-aaa-9999');
        $('#sampleCollectionDate,#sampleReceivedOn,#sampleTestingDateAtLab,#reviewedByDatetime').mask('99-aaa-9999 99:99');
        getAgeInWeeks();
    });
    
    $('#labId').on('change',function(){
      if(this.value == 'other'){
        $("#newLab").show();
        $("#newLab").addClass("isRequired");
        $("#newLab").focus();
      }else{
        $("#newLab").hide();
        $("#newLab").removeClass("isRequired");
        $('#newLab').val("");
      }
    });
    
    function getAgeInWeeks(){
      var dob = $("#dob").val();
      if($.trim(dob) == ""){
        $("#age").val("");
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
      var weeks = moment().diff(dob, 'weeks',false);
      $("#ageInYears").val(years); // Gives difference as years
      $("#ageInWeeks").val(weeks); // Gives difference as weeks
    }
    
    $('#rejectionReason').on('change',function(){
      if(this.value == "other"){
        $("#newRejectionReason").show();
        $("#newRejectionReason").addClass("isRequired");
        $("#newRejectionReason").focus();
      }else{
        $("#newRejectionReason").hide();
        $("#newRejectionReason").removeClass("isRequired");
        $('#newRejectionReason').val("");
      }
    });
    
    $("input:radio[name=sampleValidity]").click(function() {
      if($(this).val() == 'passed'){
        $('input[name="repeatSampleCollection"]').prop('checked', false);
        $('#rejectionReason').val('');
        $('#newRejectionReason').hide();
        $('#newRejectionReason').val('');
        $('.rejectionSection').hide();
      }else if($(this).val() == 'invalid'){
        $('.rejectionSection').show();
      }
    });
    
    $('#result').on('change',function(){
      if(this.value == "actual_copies"){
        $(".vlResult").show();
        $("#vlResult").addClass("isRequired");
        $("#vlResult").focus();
      }else{
        $(".vlResult").hide();
        $("#vlResult").removeClass("isRequired");
        $('#vlResult').val("");
      }
    });
    
    function validateNow(){
      flag = deforayValidator.init({
          formId: 'vlRequestFormZam'
      });
      
      $('.isRequired').each(function () {
        ($(this).val() == '') ? $(this).css('background-color', '#FFFF99') : $(this).css('background-color', '#FFFFFF')
      });
      if(flag){
        $.blockUI();
        document.getElementById('vlRequestFormZam').submit();
      }
    }
  </script>