<?php
ob_start();
include('../General.php');
$general=new Deforay_Commons_General();
//global config
$cSampleQuery="SELECT * FROM global_config";
$cSampleResult=$db->query($cSampleQuery);
$arr = array();
// now we create an associative array so that we can easily create view variables
for ($i = 0; $i < sizeof($cSampleResult); $i++) {
  $arr[$cSampleResult[$i]['name']] = $cSampleResult[$i]['value'];
}

if($arr['sample_code']=='auto' || $arr['sample_code']=='alphanumeric'){
  $sampleClass = '';
  $maxLength = '';
  if($arr['max_length']!='' && $arr['sample_code']=='alphanumeric'){
    $maxLength = $arr['max_length'];
    $maxLength = "maxlength=".$maxLength;
  }
}else{
  $sampleClass = 'checkNum';
  $maxLength = '';
  if($arr['max_length']!=''){
    $maxLength = $arr['max_length'];
    $maxLength = "maxlength=".$maxLength;
  }
}
//get import config
$importQuery="SELECT * FROM import_config WHERE status = 'active'";
$importResult=$db->query($importQuery);

$fQuery="SELECT * FROM facility_details where status='active'";
$fResult = $db->rawQuery($fQuery);

$userQuery="SELECT * FROM user_details where status='active'";
$userResult = $db->rawQuery($userQuery);

//get lab facility details
$lQuery="SELECT * FROM facility_details where facility_type='2' AND status='active'";
$lResult = $db->rawQuery($lQuery);
//sample rejection reason
$rejectionQuery="SELECT * FROM r_sample_rejection_reasons where rejection_reason_status = 'active'";
$rejectionResult = $db->rawQuery($rejectionQuery);
//rejection type
$rejectionTypeQuery="SELECT DISTINCT rejection_type FROM r_sample_rejection_reasons WHERE rejection_reason_status ='active'";
$rejectionTypeResult = $db->rawQuery($rejectionTypeQuery);
//sample status
$statusQuery="SELECT * FROM r_sample_status where status = 'active'";
$statusResult = $db->rawQuery($statusQuery);

$pdQuery="SELECT * from province_details";
$pdResult=$db->query($pdQuery);
$province = '';
$province.="<option value=''> -- Select -- </option>";
    foreach($pdResult as $provinceName){
      $province .= "<option value='".$provinceName['province_name']."##".$provinceName['province_code']."'>".ucwords($provinceName['province_name'])."</option>";
    }
    
$facility = '';
$facility.="<option data-code='' data-emails='' data-mobile-nos='' data-contact-person='' value=''> -- Select -- </option>";
foreach($fResult as $fDetails){
  $facility .= "<option data-code='".$fDetails['facility_code']."' data-emails='".$fDetails['facility_emails']."' data-mobile-nos='".$fDetails['facility_mobile_numbers']."' data-contact-person='".ucwords($fDetails['contact_person'])."' value='".$fDetails['facility_id']."'>".ucwords($fDetails['facility_name'])."</option>";
}

$sQuery="SELECT * from r_sample_type where status='active'";
$sResult=$db->query($sQuery);

$aQuery="SELECT * from r_art_code_details where nation_identifier='rwd' AND art_status ='active'";
$aResult=$db->query($aQuery);

$vlQuery="SELECT * from vl_request_form where vl_sample_id=$id";
$vlQueryInfo=$db->query($vlQuery);
//facility details
$facilityQuery="SELECT * from facility_details where facility_id='".$vlQueryInfo[0]['facility_id']."' AND status='active'";
$facilityResult=$db->query($facilityQuery);
if(!isset($facilityResult[0]['facility_code'])){
  $facilityResult[0]['facility_code'] = '';
}
if(!isset($facilityResult[0]['facility_state']) || $facilityResult[0]['facility_state']==''){
  $facilityResult[0]['facility_state'] = 0;
}
if(!isset($facilityResult[0]['facility_district']) || $facilityResult[0]['facility_district']==''){
  $facilityResult[0]['facility_district'] = 0;
}
$stateName = $facilityResult[0]['facility_state'];
$stateQuery="SELECT * from province_details where province_name='".$stateName."'";
$stateResult=$db->query($stateQuery);
if(!isset($stateResult[0]['province_code']) || $stateResult[0]['province_code'] == ''){
  $stateResult[0]['province_code'] = 0;
}
//district details
$districtQuery="SELECT DISTINCT facility_district from facility_details where facility_state='".$stateName."' AND status='active'";
$districtResult=$db->query($districtQuery);

if(isset($vlQueryInfo[0]['patient_dob']) && trim($vlQueryInfo[0]['patient_dob'])!='' && $vlQueryInfo[0]['patient_dob']!='0000-00-00'){
 $vlQueryInfo[0]['patient_dob']=$general->humanDateFormat($vlQueryInfo[0]['patient_dob']);
}else{
 $vlQueryInfo[0]['patient_dob']='';
}

if(isset($vlQueryInfo[0]['sample_collection_date']) && trim($vlQueryInfo[0]['sample_collection_date'])!='' && $vlQueryInfo[0]['sample_collection_date']!='0000-00-00 00:00:00'){
 $expStr=explode(" ",$vlQueryInfo[0]['sample_collection_date']);
 $vlQueryInfo[0]['sample_collection_date']=$general->humanDateFormat($expStr[0])." ".$expStr[1];
}else{
 $vlQueryInfo[0]['sample_collection_date']='';
}

if(isset($vlQueryInfo[0]['treatment_initiated_date']) && trim($vlQueryInfo[0]['treatment_initiated_date'])!='' && $vlQueryInfo[0]['treatment_initiated_date']!='0000-00-00'){
 $vlQueryInfo[0]['treatment_initiated_date']=$general->humanDateFormat($vlQueryInfo[0]['treatment_initiated_date']);
}else{
 $vlQueryInfo[0]['treatment_initiated_date']='';
}

if(isset($vlQueryInfo[0]['date_of_initiation_of_current_regimen']) && trim($vlQueryInfo[0]['date_of_initiation_of_current_regimen'])!='' && $vlQueryInfo[0]['date_of_initiation_of_current_regimen']!='0000-00-00'){
 $vlQueryInfo[0]['date_of_initiation_of_current_regimen']=$general->humanDateFormat($vlQueryInfo[0]['date_of_initiation_of_current_regimen']);
}else{
 $vlQueryInfo[0]['date_of_initiation_of_current_regimen']='';
}

if(isset($vlQueryInfo[0]['test_requested_on']) && trim($vlQueryInfo[0]['test_requested_on'])!='' && $vlQueryInfo[0]['test_requested_on']!='0000-00-00'){
 $vlQueryInfo[0]['test_requested_on']=$general->humanDateFormat($vlQueryInfo[0]['test_requested_on']);
}else{
 $vlQueryInfo[0]['test_requested_on']='';
}

if(isset($vlQueryInfo[0]['sample_received_at_vl_lab_datetime']) && trim($vlQueryInfo[0]['sample_received_at_vl_lab_datetime'])!='' && $vlQueryInfo[0]['sample_received_at_vl_lab_datetime']!='0000-00-00 00:00:00'){
 $expStr=explode(" ",$vlQueryInfo[0]['sample_received_at_vl_lab_datetime']);
 $vlQueryInfo[0]['sample_received_at_vl_lab_datetime']=$general->humanDateFormat($expStr[0])." ".$expStr[1];
}else{
 $vlQueryInfo[0]['sample_received_at_vl_lab_datetime']='';
}

if(isset($vlQueryInfo[0]['sample_tested_datetime']) && trim($vlQueryInfo[0]['sample_tested_datetime'])!='' && $vlQueryInfo[0]['sample_tested_datetime']!='0000-00-00 00:00:00'){
 $expStr=explode(" ",$vlQueryInfo[0]['sample_tested_datetime']);
 $vlQueryInfo[0]['sample_tested_datetime']=$general->humanDateFormat($expStr[0])." ".$expStr[1];
}else{
 $vlQueryInfo[0]['sample_tested_datetime']='';
}

if(isset($vlQueryInfo[0]['result_dispatched_datetime']) && trim($vlQueryInfo[0]['result_dispatched_datetime'])!='' && $vlQueryInfo[0]['result_dispatched_datetime']!='0000-00-00 00:00:00'){
 $expStr=explode(" ",$vlQueryInfo[0]['result_dispatched_datetime']);
 $vlQueryInfo[0]['result_dispatched_datetime']=$general->humanDateFormat($expStr[0])." ".$expStr[1];
}else{
 $vlQueryInfo[0]['result_dispatched_datetime']='';
}
//set reason for changes history
$rch = '';
$allChange = array();
if(isset($vlQueryInfo[0]['reason_for_vl_result_changes']) && $vlQueryInfo[0]['reason_for_vl_result_changes']!= ''){
  $rch.='<h4>Result Changes History</h4>';
  $rch.='<table style="width:100%;">';
  $rch.='<thead><tr style="border-bottom:2px solid #d3d3d3;"><th style="width:20%;">USER</th><th style="width:60%;">MESSAGE</th><th style="width:20%;text-align:center;">DATE</th></tr></thead>';
  $rch.='<tbody>';
  $allChange = json_decode($vlQueryInfo[0]['reason_for_vl_result_changes'],true);
  $allChange = array_reverse($allChange);
  foreach($allChange as $change){
    $usrQuery="SELECT user_name FROM user_details where user_id='".$change['usr']."'";
    $usrResult = $db->rawQuery($usrQuery);
    $name = '';
    if(isset($usrResult[0]['user_name'])){
      $name = ucwords($usrResult[0]['user_name']);
    }
    $expStr = explode(" ",$change['dtime']);
    $changedDate = $general->humanDateFormat($expStr[0])." ".$expStr[1];
    $rch.='<tr><td>'.$name.'</td><td>'.ucfirst($change['msg']).'</td><td style="text-align:center;">'.$changedDate.'</td></tr>';
  }
  $rch.='</tbody>';
  $rch.='</table>';
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
      .table > tbody > tr > td{
        border-top:none;
      }
      .form-control{
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
        <li class="active">Edit Vl Request</li>
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
            <form class="form-inline" method="post" name="vlRequestFormRwd" id="vlRequestFormRwd" autocomplete="off" action="editVlRequestHelperRwd.php">
              <div class="box-body">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Clinic Information: (To be filled by requesting Clinican/Nurse)</h3>
                    </div>
                  <div class="box-body">
                    <div class="row">
                      <div class="col-xs-3 col-md-3">
                        <div class="form-group">
                          <label for="sampleCode">Sample ID <span class="mandatory">*</span></label>
                          <input type="text" class="form-control isRequired <?php echo $sampleClass;?>" id="sampleCode" name="sampleCode" <?php echo $maxLength;?> placeholder="Enter Sample ID" title="Please enter sample id" value="<?php echo $vlQueryInfo[0]['sample_code']; ?>" style="width:100%;"/>
                        </div>
                      </div>
                      <div class="col-xs-3 col-md-3">
                        <div class="form-group">
                          <label for="sampleReordered">
                            <input type="checkbox" class="" id="sampleReordered" name="sampleReordered" value="yes" <?php echo(trim($vlQueryInfo[0]['sample_reordered']) == 'yes')?'checked="checked"':'' ?> title="Please check sample reordered"> Sample Reordered
                          </label>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-xs-3 col-md-3">
                        <div class="form-group">
                        <label for="province">Province <span class="mandatory">*</span></label>
                          <select class="form-control isRequired" name="province" id="province" title="Please choose province" style="width:100%;" onchange="getProvinceDistricts(this);">
                            <option value=""> -- Select -- </option>
                            <?php foreach($pdResult as $provinceName){ ?>
                              <option value="<?php echo $provinceName['province_name']."##".$provinceName['province_code'];?>" <?php echo ($facilityResult[0]['facility_state']."##".$stateResult[0]['province_code']==$provinceName['province_name']."##".$provinceName['province_code'])?"selected='selected'":""?>><?php echo ucwords($provinceName['province_name']);?></option>;
                            <?php } ?>
                          </select>
                        </div>
                      </div>
                      <div class="col-xs-3 col-md-3">
                        <div class="form-group">
                        <label for="district">District  <span class="mandatory">*</span></label>
                          <select class="form-control isRequired" name="district" id="district" title="Please choose district" style="width:100%;" onchange="getFacilities(this);">
                             <option value=""> -- Select -- </option>
                              <?php
                              foreach($districtResult as $districtName){
                                ?>
                                <option value="<?php echo $districtName['facility_district'];?>" <?php echo ($facilityResult[0]['facility_district']==$districtName['facility_district'])?"selected='selected'":""?>><?php echo ucwords($districtName['facility_district']);?></option>
                                <?php
                              }
                             ?>
                          </select>
                        </div>
                      </div>
                      <div class="col-xs-3 col-md-3">
                        <div class="form-group">
                          <label for="fName">Clinic/Health Center <span class="mandatory">*</span></label>
                            <select class="form-control isRequired" id="fName" name="fName" title="Please select clinic/health center name" style="width:100%;" onchange="fillFacilityDetails();">
                              <option data-code="" data-emails="" data-mobile-nos="" value=""> -- Select -- </option>
                              <?php foreach($fResult as $fDetails){ ?>
                                <option data-code="<?php echo $fDetails['facility_code']; ?>" data-emails="<?php echo $fDetails['facility_emails']; ?>" data-mobile-nos="<?php echo $fDetails['facility_mobile_numbers']; ?>" data-contact-person="<?php echo ucwords($fDetails['contact_person']); ?>" value="<?php echo $fDetails['facility_id'];?>" <?php echo ($vlQueryInfo[0]['facility_id']==$fDetails['facility_id'])?"selected='selected'":""?>><?php echo ucwords($fDetails['facility_name']);?></option>
                              <?php } ?>
                            </select>
                          </div>
                      </div>
                      <div class="col-xs-3 col-md-3">
                        <div class="form-group">
                          <label for="fCode">Clinic/Health Center Code </label>
                            <input type="text" class="form-control" style="width:100%;" name="fCode" id="fCode" placeholder="Clinic/Health Center Code" title="Please enter clinic/health center code" value="<?php echo $facilityResult[0]['facility_code'];?>">
                          </div>
                      </div>
                    </div>
                    <div class="row facilityDetails" style="display:<?php echo(trim($facilityResult[0]['facility_emails']) != '' || trim($facilityResult[0]['facility_mobile_numbers']) != '' || trim($facilityResult[0]['contact_person']) != '')?'':'none'; ?>;">
                      <div class="col-xs-2 col-md-2 femails" style="display:<?php echo(trim($facilityResult[0]['facility_emails']) != '')?'':'none'; ?>;"><strong>Clinic Email(s)</strong></div>
                      <div class="col-xs-2 col-md-2 femails facilityEmails" style="display:<?php echo(trim($facilityResult[0]['facility_emails']) != '')?'':'none'; ?>;"><?php echo $facilityResult[0]['facility_emails']; ?></div>
                      <div class="col-xs-2 col-md-2 fmobileNumbers" style="display:<?php echo(trim($facilityResult[0]['facility_mobile_numbers']) != '')?'':'none'; ?>;"><strong>Clinic Mobile No.(s)</strong></div>
                      <div class="col-xs-2 col-md-2 fmobileNumbers facilityMobileNumbers" style="display:<?php echo(trim($facilityResult[0]['facility_mobile_numbers']) != '')?'':'none'; ?>;"><?php echo $facilityResult[0]['facility_mobile_numbers']; ?></div>
                      <div class="col-xs-2 col-md-2 fContactPerson" style="display:<?php echo(trim($facilityResult[0]['contact_person']) != '')?'':'none'; ?>;"><strong>Clinic Contact Person -</strong></div>
                      <div class="col-xs-2 col-md-2 fContactPerson facilityContactPerson" style="display:<?php echo(trim($facilityResult[0]['contact_person']) != '')?'':'none'; ?>;"><?php echo ucwords($facilityResult[0]['contact_person']); ?></div>
                    </div>
                  </div>
                </div>
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Patient Information</h3>
                    </div>
                  <div class="box-body">
                    <div class="row">
                      <div class="col-xs-3 col-md-3">
                        <div class="form-group">
                        <label for="artNo">ART (TRACNET) No. <span class="mandatory">*</span></label>
                          <input type="text" name="artNo" id="artNo" class="form-control isRequired" placeholder="Enter ART Number" title="Enter art number" value="<?php echo $vlQueryInfo[0]['patient_art_no'];?>"/>
                        </div>
                      </div>
                      <div class="col-xs-3 col-md-3">
                        <div class="form-group">
                        <label for="dob">Date of Birth </label>
                          <input type="text" name="dob" id="dob" class="form-control date" placeholder="Enter DOB" title="Enter dob" value="<?php echo $vlQueryInfo[0]['patient_dob']; ?>" onchange="getDateOfBirth();"/>
                        </div>
                      </div>
                      <div class="col-xs-3 col-md-3">
                        <div class="form-group">
                          <label for="ageInYears">If DOB unknown, Age in Year </label>
                            <input type="text" name="ageInYears" id="ageInYears" class="form-control checkNum" maxlength="2" placeholder="Age in Year" title="Enter age in years" value="<?php echo $vlQueryInfo[0]['patient_age_in_years'];?>"/>
                          </div>
                      </div>
                      <div class="col-xs-3 col-md-3">
                        <div class="form-group">
                          <label for="ageInMonths">If Age < 1, Age in Month </label>
                            <input type="text" name="ageInMonths" id="ageInMonths" class="form-control checkNum" maxlength="2" placeholder="Age in Month" title="Enter age in months" value="<?php echo $vlQueryInfo[0]['patient_age_in_months'];?>"/>
                          </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-xs-3 col-md-3">
                        <div class="form-group">
                          <label for="patientFirstName">Patient Name </label>
                            <input type="text" name="patientFirstName" id="patientFirstName" class="form-control" placeholder="Enter Patient Name" title="Enter patient name" value="<?php echo $vlQueryInfo[0]['patient_first_name'];?>"/>
                          </div>
                      </div>  
                      <div class="col-xs-3 col-md-3">
                        <div class="form-group">
                        <label for="gender">Gender</label><br>
                          <label class="radio-inline" style="margin-left:0px;">
                            <input type="radio" class="" id="genderMale" name="gender" value="male" title="Please check gender" <?php echo ($vlQueryInfo[0]['patient_gender']=='male')?"checked='checked'":""?>> Male
                            </label>
                          <label class="radio-inline" style="margin-left:0px;">
                            <input type="radio" class="" id="genderFemale" name="gender" value="female" title="Please check gender" <?php echo ($vlQueryInfo[0]['patient_gender']=='female')?"checked='checked'":""?>> Female
                          </label>
                          <label class="radio-inline" style="margin-left:0px;">
                            <input type="radio" class="" id="genderNotRecorded" name="gender" value="not_recorded" title="Please check gender" <?php echo ($vlQueryInfo[0]['patient_gender']=='not_recorded')?"checked='checked'":""?>>Not Recorded
                          </label>
                        </div>
                      </div>
                      <div class="col-xs-3 col-md-3">
                        <div class="form-group">
                        <label for="patientPhoneNumber">Phone Number</label>
                          <input type="text" name="patientPhoneNumber" id="patientPhoneNumber" class="form-control checkNum" maxlength="15" placeholder="Enter Phone Number" title="Enter phone number" value="<?php echo $vlQueryInfo[0]['patient_mobile_number'];?>"/>
                        </div>
                      </div>
                   </div>
                </div>
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Sample Information</h3>
                    </div>
                  <div class="box-body">
                    <div class="row">
                      <div class="col-xs-3 col-md-3">
                        <div class="form-group">
                        <label for="">Date of Sample Collection <span class="mandatory">*</span></label>
                          <input type="text" class="form-control isRequired" style="width:100%;" name="sampleCollectionDate" id="sampleCollectionDate" placeholder="Sample Collection Date" title="Please select sample collection date" value="<?php echo $vlQueryInfo[0]['sample_collection_date'];?>">
                        </div>
                      </div>
                      <div class="col-xs-3 col-md-3">
                          <div class="form-group">
                          <label for="specimenType">Sample Type <span class="mandatory">*</span></label>
                          <select name="specimenType" id="specimenType" class="form-control isRequired" title="Please choose sample type">
                                <option value=""> -- Select -- </option>
                                <?php
                                foreach($sResult as $name){
                                 ?>
                                 <option value="<?php echo $name['sample_id'];?>" <?php echo ($vlQueryInfo[0]['sample_type']==$name['sample_id'])?"selected='selected'":""?>><?php echo ucwords($name['sample_name']);?></option>
                                 <?php
                                }
                                ?>
                            </select>
                          </div>
                        </div>
                    </div>
                </div>
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Treatment Information</h3>
                    </div>
                  <div class="box-body">
                    <div class="row">
                      <div class="col-xs-3 col-md-3">
                        <div class="form-group">
                        <label for="">Date of Treatment Initiation</label>
                          <input type="text" class="form-control date" name="dateOfArtInitiation" id="dateOfArtInitiation" placeholder="Date Of Treatment Initiated" title="Date Of treatment initiated" value="<?php echo $vlQueryInfo[0]['treatment_initiated_date']; ?>" style="width:100%;">
                        </div>
                      </div>
                      <div class="col-xs-3 col-md-3">
                          <div class="form-group">
                          <label for="artRegimen">Current Regimen</label>
                            <select class="form-control" id="artRegimen" name="artRegimen" title="Please choose ART Regimen" style="width:100%;" onchange="checkARTValue();">
                                 <option value=""> -- Select -- </option>
                                 <?php
                                 foreach($aResult as $regimen){
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
                      <div class="col-xs-3 col-md-3">
                        <div class="form-group">
                        <label for="">Date of Initiation of Current Regimen </label>
                          <input type="text" class="form-control date" style="width:100%;" name="regimenInitiatedOn" id="regimenInitiatedOn" placeholder="Current Regimen Initiated On" title="Please enter current regimen initiated on" value="<?php echo $vlQueryInfo[0]['date_of_initiation_of_current_regimen']; ?>">
                        </div>
                      </div>
                      <div class="col-xs-3 col-md-3">
                        <div class="form-group">
                        <label for="arvAdherence">ARV Adherence </label>
                          <select name="arvAdherence" id="arvAdherence" class="form-control" title="Please choose adherence">
                            <option value=""> -- Select -- </option>
                            <option value="good" <?php echo ($vlQueryInfo[0]['arv_adherance_percentage']=='good')?"selected='selected'":""?>>Good >= 95%</option>
                            <option value="fair" <?php echo ($vlQueryInfo[0]['arv_adherance_percentage']=='fair')?"selected='selected'":""?>>Fair (85-94%)</option>
                            <option value="poor" <?php echo ($vlQueryInfo[0]['arv_adherance_percentage']=='poor')?"selected='selected'":""?>>Poor < 85%</option>
                          </select>
                        </div>
                      </div>
                    </div>
                    <div class="row femaleSection" style="display:<?php echo ($vlQueryInfo[0]['patient_gender']=='female')?"":"none"?>";>
                      <div class="col-xs-3 col-md-3">
                        <div class="form-group">
                        <label for="patientPregnant">Is Patient Pregnant? </label><br>
                          <label class="radio-inline">
                            <input type="radio" class="" id="pregYes" name="patientPregnant" value="yes" title="Please check one" <?php echo ($vlQueryInfo[0]['is_patient_pregnant']=='yes')?"checked='checked'":""?>> Yes
                            </label>
                          <label class="radio-inline">
                            <input type="radio" class="" id="pregNo" name="patientPregnant" value="no" <?php echo ($vlQueryInfo[0]['is_patient_pregnant']=='no')?"checked='checked'":""?>> No
                          </label>
                        </div>
                      </div>
                      <div class="col-xs-3 col-md-3">
                        <div class="form-group">
                        <label for="breastfeeding">Is Patient Breastfeeding? </label><br>
                          <label class="radio-inline">
                            <input type="radio" class="" id="breastfeedingYes" name="breastfeeding" value="yes" title="Please check one" <?php echo ($vlQueryInfo[0]['is_patient_breastfeeding']=='yes')?"checked='checked'":""?>> Yes
                            </label>
                          <label class="radio-inline">
                            <input type="radio" class="" id="breastfeedingNo" name="breastfeeding" value="no" <?php echo ($vlQueryInfo[0]['is_patient_breastfeeding']=='no')?"checked='checked'":""?>> No
                          </label>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="box box-primary">
                    <div class="box-header with-border">
                       <h3 class="box-title">Indication for Viral Load Testing</h3><small> (Please tick one):(To be completed by clinician)</small>
                    </div>
                    <div class="box-body">
                      <div class="row">                
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="col-lg-12">
                                <label class="radio-inline">
                                    <?php
                                    $checked = '';
                                    $display = '';
                                    if(trim($vlQueryInfo[0]['reason_for_vl_testing']) =='routine'){
                                      $checked = 'checked="checked"';
                                      $display = 'block';
                                    }else{
                                      $checked = '';
                                      $display = 'none';
                                    }
                                    ?>
                                    <input type="radio" class="" id="rmTesting" name="stViralTesting" value="routine" title="Please check routine monitoring" <?php echo $checked;?> onclick="showTesting('rmTesting');">
                                    <strong>Routine Monitoring</strong>
                                </label>						
                                </div>
                            </div>
                        </div>
                      </div>
                      <div class="row rmTesting hideTestData" style="display:<?php echo $display;?>;">
                        <div class="col-md-6">
                             <label class="col-lg-5 control-label">Date of last viral load test</label>
                             <div class="col-lg-7">
                             <input type="text" class="form-control date viralTestData" id="rmTestingLastVLDate" name="rmTestingLastVLDate" placeholder="Select Last VL Date" title="Please select Last VL Date" value="<?php echo $general->humanDateFormat($vlQueryInfo[0]['last_vl_date_routine']); ?>"/>
                         </div>
                        </div>
                        <div class="col-md-6">
                             <label for="rmTestingVlValue" class="col-lg-3 control-label">VL Value</label>
                             <div class="col-lg-7">
                             <input type="text" class="form-control checkNum viralTestData" id="rmTestingVlValue" name="rmTestingVlValue" placeholder="Enter VL Value" title="Please enter vl value" value="<?php echo $vlQueryInfo[0]['last_vl_result_routine']; ?>"/>
                             (copies/ml)
                         </div>
                       </div>                 
                      </div>
                      <div class="row">                
                        <div class="col-md-8">
                            <div class="form-group">
                                <div class="col-lg-12">
                                <label class="radio-inline">
                                    <?php
                                    $checked = '';
                                    $display = '';
                                    if(trim($vlQueryInfo[0]['reason_for_vl_testing']) =='failure'){
                                      $checked = 'checked="checked"';
                                      $display = 'block';
                                    }else{
                                      $checked = '';
                                      $display = 'none';
                                    }
                                    ?>
                                    <input type="radio" class="" id="repeatTesting" name="stViralTesting" value="failure" title="Repeat VL test after suspected treatment failure adherence counseling" <?php echo $checked;?> onclick="showTesting('repeatTesting');">
                                    <strong>Repeat VL test after suspected treatment failure adherence counselling </strong>
                                </label>						
                                </div>
                            </div>
                        </div>
                     </div>
                     <div class="row repeatTesting hideTestData" style="display: <?php echo $display;?>;">
                       <div class="col-md-6">
                            <label class="col-lg-5 control-label">Date of last viral load test</label>
                            <div class="col-lg-7">
                            <input type="text" class="form-control date viralTestData" id="repeatTestingLastVLDate" name="repeatTestingLastVLDate" placeholder="Select Last VL Date" title="Please select Last VL Date" value="<?php echo $general->humanDateFormat($vlQueryInfo[0]['last_vl_date_failure_ac']); ?>"/>
                            </div>
                      </div>
                       <div class="col-md-6">
                            <label for="repeatTestingVlValue" class="col-lg-3 control-label">VL Value</label>
                            <div class="col-lg-7">
                            <input type="text" class="form-control checkNum viralTestData" id="repeatTestingVlValue" name="repeatTestingVlValue" placeholder="Enter VL Value" title="Please enter vl value" value="<?php echo $vlQueryInfo[0]['last_vl_result_failure_ac']; ?>"/>
                            (copies/ml)
                            </div>
                      </div>                 
                     </div>
                     <div class="row">         
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="col-lg-12">
                                <label class="radio-inline">
                                    <?php
                                    $checked = '';
                                    $display = '';
                                    if(trim($vlQueryInfo[0]['reason_for_vl_testing']) =='suspect'){
                                      $checked = 'checked="checked"';
                                      $display = 'block';
                                    }else{
                                      $checked = '';
                                      $display = 'none';
                                    }
                                    ?>
                                    <input type="radio" class="" id="suspendTreatment" name="stViralTesting" value="suspect" title="Suspect Treatment Failure" <?php echo $checked;?> onclick="showTesting('suspendTreatment');">
                                    <strong>Suspect Treatment Failure</strong>
                                </label>						
                                </div>
                            </div>
                        </div>
                     </div>
                     <div class="row suspendTreatment hideTestData" style="display: <?php echo $display;?>;">
                        <div class="col-md-6">
                             <label class="col-lg-5 control-label">Date of last viral load test</label>
                             <div class="col-lg-7">
                             <input type="text" class="form-control date viralTestData" id="suspendTreatmentLastVLDate" name="suspendTreatmentLastVLDate" placeholder="Select Last VL Date" title="Please select Last VL Date" value="<?php echo $general->humanDateFormat($vlQueryInfo[0]['last_vl_date_failure']); ?>"/>
                             </div>
                       </div>
                        <div class="col-md-6">
                             <label for="suspendTreatmentVlValue" class="col-lg-3 control-label">VL Value</label>
                             <div class="col-lg-7">
                             <input type="text" class="form-control checkNum viralTestData" id="suspendTreatmentVlValue" name="suspendTreatmentVlValue" placeholder="Enter VL Value" title="Please enter vl value" value="<?php echo $vlQueryInfo[0]['last_vl_result_failure']; ?>"/>
                             (copies/ml)
                             </div>
                       </div>                 
                     </div>
                     <div class="row">
                        <div class="col-md-4">
                            <label for="reqClinician" class="col-lg-5 control-label">Request Clinician</label>
                            <div class="col-lg-7">
                               <input type="text" class="form-control" id="reqClinician" name="reqClinician" placeholder="Request Clinician" title="Please enter request clinician" value="<?php echo $vlQueryInfo[0]['request_clinician_name'];?>"/>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="reqClinicianPhoneNumber" class="col-lg-5 control-label">Phone Number</label>
                            <div class="col-lg-7">
                               <input type="text" class="form-control checkNum" id="reqClinicianPhoneNumber" name="reqClinicianPhoneNumber" maxlength="15" placeholder="Phone Number" title="Please enter request clinician phone number" value="<?php echo $vlQueryInfo[0]['request_clinician_phone_number']; ?>"/>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="col-lg-5 control-label" for="requestDate">Request Date </label>
                            <div class="col-lg-7">
                                <input type="text" class="form-control date" id="requestDate" name="requestDate" placeholder="Request Date" title="Please select request date" value="<?php echo $vlQueryInfo[0]['test_requested_on']; ?>"/>
                            </div>
                        </div>
                     </div>
                     <div class="row">
                        <div class="col-md-4">
                            <label for="vlFocalPerson" class="col-lg-5 control-label">VL Focal Person </label>
                            <div class="col-lg-7">
                               <input type="text" class="form-control" id="vlFocalPerson" name="vlFocalPerson" placeholder="VL Focal Person" title="Please enter vl focal person name" value="<?php echo $vlQueryInfo[0]['vl_focal_person']; ?>"/>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="vlFocalPersonPhoneNumber" class="col-lg-5 control-label">VL Focal Person Phone Number</label>
                            <div class="col-lg-7">
                               <input type="text" class="form-control checkNum" id="vlFocalPersonPhoneNumber" name="vlFocalPersonPhoneNumber" maxlength="15" placeholder="Phone Number" title="Please enter vl focal person phone number" value="<?php echo $vlQueryInfo[0]['vl_focal_person_phone_number']; ?>"/>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="col-lg-5 control-label" for="emailHf">Email for HF </label>
                            <div class="col-lg-7">
                                <input type="text" class="form-control isEmail" id="emailHf" name="emailHf" placeholder="Email for HF" title="Please enter email for hf" value="<?php echo $facilityResult[0]['facility_emails'];?>"/>
                            </div>
                        </div>
                     </div>
                    </div>
                  </div>
                  <div class="box box-primary">
                    <div class="box-header with-border">
                      <h3 class="box-title">Laboratory Information</h3>
                    </div>
                    <div class="box-body">
                      <div class="row">
                        <div class="col-md-4">
                            <label for="labId" class="col-lg-5 control-label">Lab Name </label>
                            <div class="col-lg-7">
                              <select name="labId" id="labId" class="form-control labSection" title="Please choose lab">
                                <option value="">-- Select --</option>
                                <?php
                                foreach($lResult as $labName){
                                  ?>
                                  <option value="<?php echo $labName['facility_id'];?>" <?php echo ($vlQueryInfo[0]['lab_id']==$labName['facility_id'])?"selected='selected'":""?>><?php echo ucwords($labName['facility_name']);?></option>
                                  <?php
                                }
                                ?>
                              </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="testingPlatform" class="col-lg-5 control-label">VL Testing Platform </label>
                            <div class="col-lg-7">
                              <select name="testingPlatform" id="testingPlatform" class="form-control labSection" title="Please choose VL Testing Platform">
                                <option value="">-- Select --</option>
                                <?php foreach($importResult as $mName) { ?>
                                  <option value="<?php echo $mName['machine_name'].'##'.$mName['lower_limit'].'##'.$mName['higher_limit'];?>" <?php echo($vlQueryInfo[0]['vl_test_platform'] == $mName['machine_name'])? 'selected="selected"':''; ?>><?php echo $mName['machine_name'];?></option>
                                  <?php
                                }
                                ?>
                              </select>
                            </div>
                        </div>
                        <!--<div class="col-md-4">
                            <label for="testMethods" class="col-lg-5 control-label">Test Methods</label>
                            <div class="col-lg-7">
                              <select name="testMethods" id="testMethods" class="form-control labSection" title="Please choose test methods">
                                <option value=""> -- Select -- </option>
                                <option value="individual" < ?php echo($vlQueryInfo[0]['test_methods'] == 'individual')? 'selected="selected"':''; ?>>Individual</option>
                                <option value="minipool" < ?php echo($vlQueryInfo[0]['test_methods'] == 'minipool')? 'selected="selected"':''; ?>>Minipool</option>
                                <option value="other pooling algorithm" < ?php echo($vlQueryInfo[0]['test_methods'] == 'other pooling algorithm')? 'selected="selected"':''; ?>>Other Pooling Algorithm</option>
                               </select>
                            </div>
                        </div>-->
                      </div>
                      <div class="row">
                        <div class="col-md-4">
                            <label class="col-lg-5 control-label" for="sampleReceivedOn">Date Sample Received at Testing Lab </label>
                            <div class="col-lg-7">
                                <input type="text" class="form-control labSection" id="sampleReceivedOn" name="sampleReceivedOn" placeholder="Sample Received Date" title="Please select sample received date" value="<?php echo $vlQueryInfo[0]['sample_received_at_vl_lab_datetime']; ?>"/>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="col-lg-5 control-label" for="sampleTestingDateAtLab">Sample Testing Date </label>
                            <div class="col-lg-7">
                                <input type="text" class="form-control labSection" id="sampleTestingDateAtLab" name="sampleTestingDateAtLab" placeholder="Sample Testing Date" title="Please select sample testing date" value="<?php echo $vlQueryInfo[0]['sample_tested_datetime']; ?>"/>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="col-lg-5 control-label" for="resultDispatchedOn">Date Results Dispatched </label>
                            <div class="col-lg-7">
                                <input type="text" class="form-control labSection" id="resultDispatchedOn" name="resultDispatchedOn" placeholder="Result Dispatched Date" title="Please select result dispatched date" value="<?php echo $vlQueryInfo[0]['result_dispatched_datetime']; ?>"/>
                            </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-md-4">
                            <label class="col-lg-5 control-label" for="noResult">Sample Rejection </label>
                            <div class="col-lg-7">
                              <label class="radio-inline">
                               <input class="labSection" id="noResultYes" name="noResult" value="yes" title="Please check one" type="radio" <?php echo($vlQueryInfo[0]['is_sample_rejected'] == 'yes')?'checked="checked"':''; ?>> Yes
                              </label>
                              <label class="radio-inline">
                               <input class="labSection" id="noResultNo" name="noResult" value="no" title="Please check one" type="radio" <?php echo($vlQueryInfo[0]['is_sample_rejected'] == 'no')?'checked="checked"':''; ?>> No
                              </label>
                            </div>
                        </div>
                        <div class="col-md-4 rejectionReason" style="display:<?php echo($vlQueryInfo[0]['is_sample_rejected'] == 'yes')?'':'none'; ?>;">
                            <label class="col-lg-5 control-label" for="rejectionReason">Rejection Reason </label>
                            <div class="col-lg-7">
                              <select name="rejectionReason" id="rejectionReason" class="form-control labSection" title="Please choose reason" onchange="checkRejectionReason();">
                                <option value="">-- Select --</option>
                                <?php foreach($rejectionTypeResult as $type) { ?>
                                <optgroup label="<?php echo ucwords($type['rejection_type']); ?>">
                                  <?php
                                  foreach($rejectionResult as $reject){
                                    if($type['rejection_type'] == $reject['rejection_type']){
                                    ?>
                                    <option value="<?php echo $reject['rejection_reason_id'];?>" <?php echo($vlQueryInfo[0]['reason_for_sample_rejection'] == $reject['rejection_reason_id'])?'selected="selected"':''; ?>><?php echo ucwords($reject['rejection_reason_name']);?></option>
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
                        <div class="col-md-4 vlResult" style="visibility:<?php echo($vlQueryInfo[0]['is_sample_rejected'] == 'yes')?'hidden':'visible'; ?>;">
                            <label class="col-lg-5 control-label" for="vlResult">Viral Load Result (copiesl/ml) </label>
                            <div class="col-lg-7">
                              <input type="text" class="form-control labSection" id="vlResult" name="vlResult" placeholder="Viral Load Result" title="Please enter viral load result" value="<?php echo $vlQueryInfo[0]['result_value_absolute'];?>" <?php echo($vlQueryInfo[0]['result'] == 'Target Not Detected')?'readonly="readonly"':''; ?> style="width:100%;" />
                              <input type="checkbox" class="labSection" id="tnd" name="tnd" value="yes" <?php echo($vlQueryInfo[0]['result'] == 'Target Not Detected')?'checked="checked"':''; ?> title="Please check tnd"> Target Not Detected
                            </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-md-4">
                            <label class="col-lg-5 control-label" for="approvedBy">Approved By </label>
                            <div class="col-lg-7">
                              <select name="approvedBy" id="approvedBy" class="form-control labSection" title="Please choose approved by">
                                <option value="">-- Select --</option>
                                <?php
                                foreach($userResult as $uName){
                                  ?>
                                  <option value="<?php echo $uName['user_id'];?>" <?php echo ($vlQueryInfo[0]['result_approved_by'] == $uName['user_id'])?"selected=selected":""; ?>><?php echo ucwords($uName['user_name']);?></option>
                                  <?php
                                }
                                ?>
                              </select>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <label class="col-lg-2 control-label" for="labComments">Laboratory Scientist Comments </label>
                            <div class="col-lg-10">
                              <textarea class="form-control labSection" name="labComments" id="labComments" placeholder="Lab comments" style="width:100%"><?php echo trim($vlQueryInfo[0]['approver_comments']); ?></textarea>
                            </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-md-4">
                            <label class="col-lg-5 control-label" for="status">Status <span class="mandatory">*</span></label>
                            <div class="col-lg-7">
                              <select class="form-control labSection isRequired" id="status" name="status" title="Please select test status">
                                <option value="">-- Select --</option>
                                <?php
                                foreach($statusResult as $status){
                                ?>
                                  <option value="<?php echo $status['status_id']; ?>"<?php echo ($vlQueryInfo[0]['result_status'] == $status['status_id']) ? 'selected="selected"':'';?>><?php echo ucwords($status['status_name']); ?></option>
                                <?php } ?>
                              </select>
                            </div>
                        </div>
                        <div class="col-md-8 reasonForResultChanges" style="visibility:hidden;">
                            <label class="col-lg-2 control-label" for="reasonForResultChanges">Reason For Changes in Result </label>
                            <div class="col-lg-10">
                              <textarea class="form-control" name="reasonForResultChanges" id="reasonForResultChanges" placeholder="Enter Reason For Result Changes" title="Please enter reason for result changes" style="width:100%;"></textarea>
                            </div>
                        </div>
                      </div>
                      <?php
                      if(count($allChange)>0){
                      ?>
                        <div class="row">
                          <div class="col-md-12"><?php echo $rch; ?></div>
                        </div>
                      <?php } ?>
                    </div>
                  </div>
               </div>
              <div class="box-footer">
                <input type="hidden" name="vlSampleId" id="vlSampleId" value="<?php echo $vlQueryInfo[0]['vl_sample_id'];?>"/>
                <input type="hidden" name="reasonForResultChangesHistory" id="reasonForResultChangesHistory" value="<?php echo base64_encode($vlQueryInfo[0]['reason_for_vl_result_changes']);?>"/>
                <a class="btn btn-primary" href="javascript:void(0);" onclick="validateNow();return false;">Save</a>&nbsp;
                <a href="vlRequest.php" class="btn btn-default"> Cancel</a>
              </div>
            </form>
      </div>
    </section>
  </div>
  <script>
    provinceName = true;
    facilityName = true;
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
        $('#sampleCollectionDate,#sampleReceivedOn,#sampleTestingDateAtLab,#resultDispatchedOn').datetimepicker({
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
        $('.date').mask('99-aaa-9999');
        $('#sampleCollectionDate,#sampleReceivedOn,#sampleTestingDateAtLab,#resultDispatchedOn').mask('99-aaa-9999 99:99');
        getDateOfBirth();
        __clone = $("#vlRequestFormRwd .labSection").clone();
        reason = ($("#reasonForResultChanges").length)?$("#reasonForResultChanges").val():'';
        result = ($("#vlResult").length)?$("#vlResult").val():'';
    });
    
    function showTesting(chosenClass){
      $(".viralTestData").val('');
      $(".hideTestData").hide();
      $("."+chosenClass).show();
    }
    
    function getProvinceDistricts(obj){
      $.blockUI();
      var cName = $("#fName").val();
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
              $("#district").html(details[1]);
              $("#fName").html("<option data-code='' data-emails='' data-mobile-nos='' data-contact-person='' value=''> -- Select -- </option>");
              $(".facilityDetails").hide();
              $(".facilityEmails").html('');
              $(".facilityMobileNumbers").html('');
              $(".facilityContactPerson").html('');
            }
        });
        }
        
      }else if(pName=='' && cName==''){
        provinceName = true;
        facilityName = true;
        $("#province").html("<?php echo $province;?>");
        $("#fName").html("<option data-code='' data-emails='' data-mobile-nos='' data-contact-person='' value=''> -- Select -- </option>");
      }
      $.unblockUI();
  }
  
  function getFacilities(obj){
    $.blockUI();
    var dName = $("#district").val();
    var cName = $("#fName").val();
    if(dName!=''){
      $.post("../includes/getFacilityForClinic.php", {dName:dName,cliName:cName},
      function(data){
	  if(data != ""){
            $("#fName").html(data);
            $(".facilityDetails").hide();
            $(".facilityEmails").html('');
            $(".facilityMobileNumbers").html('');
            $(".facilityContactPerson").html('');
	  }
      });
    }
    $.unblockUI();
  }
  
  function fillFacilityDetails(){
    $("#fCode").val($('#fName').find(':selected').data('code'));
    var femails = $('#fName').find(':selected').data('emails');
    var fmobilenos = $('#fName').find(':selected').data('mobile-nos');
    var fContactPerson = $('#fName').find(':selected').data('contact-person');
    if($.trim(femails) !='' || $.trim(fmobilenos) !='' || fContactPerson != ''){
      $(".facilityDetails").show();
    }else{
      $(".facilityDetails").hide();
    }
    ($.trim(femails) !='')?$(".femails").show():$(".femails").hide();
    ($.trim(femails) !='')?$(".facilityEmails").html(femails):$(".facilityEmails").html('');
    ($.trim(fmobilenos) !='')?$(".fmobileNumbers").show():$(".fmobileNumbers").hide();
    ($.trim(fmobilenos) !='')?$(".facilityMobileNumbers").html(fmobilenos):$(".facilityMobileNumbers").html('');
    ($.trim(fContactPerson) !='')?$(".fContactPerson").show():$(".fContactPerson").hide();
    ($.trim(fContactPerson) !='')?$(".facilityContactPerson").html(fContactPerson):$(".facilityContactPerson").html('');
  }
  
  $("input:radio[name=gender]").click(function() {
    if($(this).val() == 'male' || $(this).val() == 'not_recorded'){
      $('.femaleSection').hide();
      $('input[name="breastfeeding"]').prop('checked', false);
      $('input[name="patientPregnant"]').prop('checked', false);
    }else if($(this).val() == 'female'){
      $('.femaleSection').show();
    }
  });
  
  $("input:radio[name=noResult]").click(function() {
    if($(this).val() == 'yes'){
      $('.rejectionReason').show();
      $('.vlResult').css('visibility','hidden');
      $('#rejectionReason').addClass('isRequired');
    }else{
      $('.vlResult').css('visibility','visible');
      $('.rejectionReason').hide();
      $('#rejectionReason').removeClass('isRequired');
      $('#rejectionReason').val('');
    }
  });
  
  $('#tnd').change(function() {
    if($('#tnd').is(':checked')){
      $('#vlResult').attr('readonly',true);
    }else{
      $('#vlResult').attr('readonly',false);
    }
  });
  
  $('#vlResult').on('input',function(e){
    if(this.value != ''){
      $('#tnd').attr('disabled',true);
    }else{
      $('#tnd').attr('disabled',false);
    }
  });
  
  function checkARTValue(){
    var artRegimen = $("#artRegimen").val();
    if(artRegimen=='other'){
      $("#newArtRegimen").show();
      $("#newArtRegimen").addClass("isRequired");
    }else{
      $("#newArtRegimen").hide();
      $("#newArtRegimen").removeClass("isRequired");
      $('#newArtRegimen').val("");
    }
  }
  
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
  
  $("#vlRequestFormRwd .labSection").on("change", function() {
      if($.trim(result)!= ''){
        if($("#vlRequestFormRwd .labSection").serialize() == $(__clone).serialize()){
          $(".reasonForResultChanges").css("visibility","hidden");
          $("#reasonForResultChanges").removeClass("isRequired");
        }else{
          $(".reasonForResultChanges").css("visibility","visible");
          $("#reasonForResultChanges").addClass("isRequired");
        }
      }
  });
  
  function checkRejectionReason(){
    var rejectionReason = $("#rejectionReason").val();
    if(rejectionReason == "other"){
      $("#newRejectionReason").show();
      $("#newRejectionReason").addClass("isRequired");
    }else{
      $("#newRejectionReason").hide();
      $("#newRejectionReason").removeClass("isRequired");
      $('#newRejectionReason').val("");
    }
  }
  
  function validateNow(){
      var format = '<?php echo $arr['sample_code'];?>';
      var sCodeLentgh = $("#sampleCode").val();
      var minLength = '<?php echo $arr['min_length'];?>';
      if((format == 'alphanumeric' || format =='numeric') && sCodeLentgh.length < minLength && sCodeLentgh!=''){
        alert("Sample id length atleast "+minLength+" characters");
        return false;
      }
    
    flag = deforayValidator.init({
        formId: 'vlRequestFormRwd'
    });
    
    $('.isRequired').each(function () {
      ($(this).val() == '') ? $(this).css('background-color', '#FFFF99') : $(this).css('background-color', '#FFFFFF')
    });
    if(flag){
      $.blockUI();
      document.getElementById('vlRequestFormRwd').submit();
    }
  }
  </script>