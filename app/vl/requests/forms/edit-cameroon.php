<?php



use App\Utilities\DateUtility;
use App\Registries\ContainerRegistry;

/** @var MysqliDb $db */
$db = ContainerRegistry::get('db');




if ($arr['sample_code'] == 'auto' || $arr['sample_code'] == 'alphanumeric') {
     $sampleClass = '';
     $maxLength = '';
     if ($arr['max_length'] != '' && $arr['sample_code'] == 'alphanumeric') {
          $maxLength = $arr['max_length'];
          $maxLength = "maxlength=" . $maxLength;
     }
} else {
     $sampleClass = '';
     $maxLength = '';
     if ($arr['max_length'] != '') {
          $maxLength = $arr['max_length'];
          $maxLength = "maxlength=" . $maxLength;
     }
}
//check remote user
$pdQuery = "SELECT * FROM geographical_divisions WHERE geo_parent = 0 and geo_status='active'";
if ($_SESSION['instanceType'] == 'remoteuser') {
     $sampleCode = 'remote_sample_code';
     if (!empty($vlQueryInfo['remote_sample']) && $vlQueryInfo['remote_sample'] == 'yes') {
          $sampleCode = 'remote_sample_code';
     } else {
          $sampleCode = 'sample_code';
     }
     //check user exist in user_facility_map table
     $chkUserFcMapQry = "Select user_id from user_facility_map where user_id='" . $_SESSION['userId'] . "'";
     $chkUserFcMapResult = $db->query($chkUserFcMapQry);
     if ($chkUserFcMapResult) {
          $pdQuery = "SELECT DISTINCT gd.geo_name,gd.geo_id,gd.geo_code
                         FROM geographical_divisions as gd
                         JOIN facility_details as fd ON fd.facility_state_id=gd.geo_id
                         JOIN user_facility_map as vlfm ON vlfm.facility_id=fd.facility_id
                         WHERE gd.geo_parent = 0 AND gd.geo_status='active'
                         AND vlfm.user_id='" . $_SESSION['userId'] . "'";
     }
} else {
     $sampleCode = 'sample_code';
}
$pdResult = $db->query($pdQuery);
$province = "<option value=''> <?= _('-- Select --'); ?> </option>";
foreach ($pdResult as $provinceName) {
     $province .= "<option value='" . $provinceName['geo_name'] . "##" . $provinceName['geo_code'] . "'>" . ($provinceName['geo_name']) . "</option>";
}
$facility = $general->generateSelectOptions($healthFacilities, $vlQueryInfo['facility_id'], '<?= _("-- Select --"); ?>');
//regimen heading
$artRegimenQuery = "SELECT DISTINCT headings FROM r_vl_art_regimen";
$artRegimenResult = $db->rawQuery($artRegimenQuery);
$aQuery = "SELECT * from r_vl_art_regimen where art_status ='active'";
$aResult = $db->query($aQuery);
//facility details
if (isset($vlQueryInfo['facility_id']) && $vlQueryInfo['facility_id'] > 0) {
     $facilityQuery = "SELECT * FROM facility_details WHERE facility_id= ? AND status='active'";
     $facilityResult = $db->rawQuery($facilityQuery, array($vlQueryInfo['facility_id']));
}

$facilityCode = $facilityResult[0]['facility_code'] ?? '';
$facilityMobileNumbers = $facilityResult[0]['facility_mobile_numbers'] ?? '';
$contactPerson = $facilityResult[0]['contact_person'] ?? '';
$facilityEmails = $facilityResult[0]['facility_emails'] ?? '';
$facilityState = $facilityResult[0]['facility_state'] ?? '';
$facilityDistrict = $facilityResult[0]['facility_district'] ?? '';

if (trim($facilityResult[0]['facility_state']) != '') {
     $stateQuery = "SELECT * FROM geographical_divisions where geo_name='" . $facilityResult[0]['facility_state'] . "'";
     $stateResult = $db->query($stateQuery);
}
if (!isset($stateResult[0]['geo_code'])) {
     $stateResult[0]['geo_code'] = '';
}
//district details
$districtResult = [];
if (trim($facilityResult[0]['facility_state']) != '') {
     $districtQuery = "SELECT DISTINCT facility_district from facility_details
                         WHERE facility_state=? AND status='active'";
     $districtResult = $db->rawQuery($districtQuery, [$facilityResult[0]['facility_state']]);
}


//set reason for changes history
$rch = '';
if (isset($vlQueryInfo['reason_for_vl_result_changes']) && $vlQueryInfo['reason_for_vl_result_changes'] != '' && $vlQueryInfo['reason_for_vl_result_changes'] != null) {
     $rch .= '<h4>Result Changes History</h4>';
     $rch .= '<table style="width:100%;">';
     $rch .= '<thead><tr style="border-bottom:2px solid #d3d3d3;"><th style="width:20%;">USER</th><th style="width:60%;">MESSAGE</th><th style="width:20%;text-align:center;">DATE</th></tr></thead>';
     $rch .= '<tbody>';
     $splitChanges = explode('vlsm', $vlQueryInfo['reason_for_vl_result_changes']);
     for ($c = 0; $c < count($splitChanges); $c++) {
          $getData = explode("##", $splitChanges[$c]);
          $expStr = explode(" ", $getData[2]);
          $changedDate = DateUtility::humanReadableDateFormat($expStr[0]) . " " . $expStr[1];
          $rch .= '<tr><td>' . ($getData[0]) . '</td><td>' . ($getData[1]) . '</td><td style="text-align:center;">' . $changedDate . '</td></tr>';
     }
     $rch .= '</tbody>';
     $rch .= '</table>';
}
?>
<style>
     .table>tbody>tr>td {
          border-top: none;
     }

     .form-control {
          width: 100% !important;
     }

     .row {
          margin-top: 6px;
     }
</style>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
     <!-- Content Header (Page header) -->
     <section class="content-header">
          <h1><em class="fa-solid fa-pen-to-square"></em> <?= _('VIRAL LOAD LABORATORY REQUEST FORM'); ?> </h1>
          <ol class="breadcrumb">
               <li><a href="/dashboard/index.php"><em class="fa-solid fa-chart-pie"></em> <?= _('Home'); ?></a></li>
               <li class="active"><?= _('Edit VL Request'); ?></li>
          </ol>
     </section>
     <!-- Main content -->
     <section class="content">
          <div class="box box-default">
               <div class="box-header with-border">
                    <div class="pull-right" style="font-size:15px;"><span class="mandatory">*</span> <?= _('indicates required field'); ?> &nbsp;</div>
               </div>
               <div class="box-body">
                    <!-- form start -->
                    <form class="form-inline" method="post" name="vlRequestFormCameroon" id="vlRequestFormCameroon" autocomplete="off" action="editVlRequestHelper.php">
                         <div class="box-body">
                              <div class="box box-primary">
                                   <div class="box-header with-border">
                                        <h3 class="box-title"><?= _('Clinic Information: (To be filled by requesting Clinican/Nurse)'); ?></h3>
                                   </div>
                                   <div class="box-body">
                                        <div class="row">
                                             <div class="col-xs-3 col-md-3">
                                                  <div class="">
                                                       <?php if ($_SESSION['instanceType'] == 'remoteuser') { ?>
                                                            <label for="sampleCode"><?= _('Sample ID'); ?> </label><br>
                                                            <span id="sampleCodeInText" style="width:100%;border-bottom:1px solid #333;"><?php echo $vlQueryInfo[$sampleCode]; ?></span>
                                                                 <input type="hidden" class="<?php echo $sampleClass; ?>" id="sampleCode" name="sampleCode" value="<?php echo $vlQueryInfo[$sampleCode]; ?>" />
                                                       <?php } else { ?>
                                                            <label for="sampleCode"><?= _('Sample ID'); ?> <span class="mandatory">*</span></label>
                                                            <input type="text" value="<?= ($vlQueryInfo['sample_code']); ?>"  class="form-control isRequired <?php echo $sampleClass; ?>" id="sampleCode" name="sampleCode" readonly="readonly" <?php echo $maxLength; ?> placeholder="<?= _('Enter Sample ID'); ?>" title="<?= _('Please enter sample id'); ?>" style="width:100%;" onblur="checkSampleNameValidation('form_vl','<?php echo $sampleCode; ?>',this.id,null,'This sample code already exists. Try another',null)" />
                                                       <?php } ?>
                                                  </div>
                                             </div>
                                             <div class="col-xs-3 col-md-3">
                                                  <div class="">
                                                       <label for="sampleReordered">
                                                            <input type="checkbox" class="" id="sampleReordered" name="sampleReordered" value="yes" title="<?= _('Please indicate if this is a reordered sample'); ?>"> <?= _('Sample Reordered'); ?>
                                                       </label>
                                                  </div>
                                             </div>
                                             <!-- BARCODESTUFF START -->
                                             <?php if (isset($global['bar_code_printing']) && $global['bar_code_printing'] != "off") { ?>
                                                  <div class="col-xs-3 col-md-3 pull-right">
                                                       <div class="">
                                                            <label for="printBarCode"><?= _('Print Barcode Label'); ?> <span class="mandatory">*</span> </label>
                                                            <input type="checkbox" class="" id="printBarCode" name="printBarCode" checked />
                                                       </div>
                                                  </div>
                                             <?php } ?>
                                             <!-- BARCODESTUFF END -->
                                        </div>
                                        <div class="row">
                                             <div class="col-xs-3 col-md-3">
                                                  <div class="">
                                                       <label for="province"><?= _('Province'); ?> <span class="mandatory">*</span></label>
                                                       <select class="form-control isRequired" name="province" id="province" title="<?= _('Please choose a province'); ?>" style="width:100%;" onchange="getProvinceDistricts(this);">
                                                            <?php echo $province; ?>
                                                       </select>
                                                  </div>
                                             </div>
                                             <div class="col-xs-3 col-md-3">
                                                  <div class="">
                                                       <label for="district"><?= _('District'); ?> <span class="mandatory">*</span></label>
                                                       <select class="form-control isRequired" name="district" id="district" title="<?= _('Please choose a district'); ?>" style="width:100%;" onchange="getFacilities(this);">
                                                            <option value=""> <?= _('-- Select --'); ?> </option>
                                                       </select>
                                                  </div>
                                             </div>
                                             <div class="col-xs-3 col-md-3">
                                                  <div class="">
                                                       <label for="fName"><?= _('Clinic/Health Center'); ?> <span class="mandatory">*</span></label>
                                                       <select class="form-control isRequired" id="fName" name="fName" title="<?= _('Please select a clinic/health center name'); ?>" style="width:100%;" onchange="fillFacilityDetails();">
                                                            <?php echo $facility;  ?>
                                                       </select>
                                                  </div>
                                             </div>
                                             <div class="col-xs-3 col-md-3">
                                                  <div class="">
                                                       <label for="fCode"><?= _('Clinic/Health Center Code'); ?> </label>
                                                       <input type="text" class="form-control" style="width:100%;" name="fCode" id="fCode" placeholder="<?= _('Clinic/Health Center Code'); ?>" title="<?= _('Please enter clinic/health center code'); ?>" value="<?php echo $facilityResult[0]['facility_code']; ?>">
                                                  </div>
                                             </div>
                                        </div>
                                        <div class="row facilityDetails" style="display:none;">
                                             <div class="col-xs-2 col-md-2 femails" style="display:none;"><strong>Clinic Email(s) -</strong></div>
                                             <div class="col-xs-2 col-md-2 femails facilityEmails" style="display:none;"></div>
                                             <div class="col-xs-2 col-md-2 fmobileNumbers" style="display:none;"><strong>Clinic Mobile No.(s) -</strong></div>
                                             <div class="col-xs-2 col-md-2 fmobileNumbers facilityMobileNumbers" style="display:none;"></div>
                                             <div class="col-xs-2 col-md-2 fContactPerson" style="display:none;"><strong>Clinic Contact Person -</strong></div>
                                             <div class="col-xs-2 col-md-2 fContactPerson facilityContactPerson" style="display:none;"></div>
                                        </div>
                                        
                                   </div>
                              </div>
                              <div class="box box-primary">
                                   <div class="box-header with-border">
                                        <h3 class="box-title"><?= _('Patient Information'); ?></h3>&nbsp;&nbsp;&nbsp;
                                        <input style="width:30%;" type="text" name="artPatientNo" id="artPatientNo" class="" placeholder="<?= _('Enter Unique ART Number or Patient Name'); ?>" title="<?= _('Enter art number or patient name'); ?>" />&nbsp;&nbsp;
                                        <a style="margin-top:-0.35%;" href="javascript:void(0);" class="btn btn-default btn-sm" onclick="showPatientList();"><em class="fa-solid fa-magnifying-glass"></em>Search</a><span id="showEmptyResult" style="display:none;color: #ff0000;font-size: 15px;"><strong>&nbsp;No Patient Found</strong></span>
                                   </div>
                                   <div class="box-body">
                                        <div class="row">
                                             <div class="col-xs-3 col-md-3">
                                                  <div class="form-group">
                                                       <label for="artNo"><?= _('Unique ART (TRACNET) No.'); ?> <span class="mandatory">*</span></label>
                                                       <input type="text" name="artNo" id="artNo" value="<?= $vlQueryInfo['patient_art_no'] ?>" class="form-control isRequired" placeholder="<?= _('Enter ART Number'); ?>" title="<?= _('Enter art number'); ?>" onchange="checkPatientDetails('form_vl','patient_art_no',this,null)" />
                                                       <span class="artNoGroup" id="artNoGroup"></span>
                                                  </div>
                                             </div>
                                             <div class="col-xs-3 col-md-3">
                                                  <div class="form-group">
                                                       <label for="dob"><?= _('Date of Birth'); ?> <?php echo ($_SESSION['instanceType'] == 'remoteuser') ? "<span class='mandatory'>*</span>" : ''; ?></label>
                                                       <input type="text" name="dob" id="dob" value="<?= $vlQueryInfo['patient_dob'] ?>" class="form-control date <?php echo ($_SESSION['instanceType'] == 'remoteuser') ? "isRequired" : ''; ?>" placeholder="<?= _('Enter DOB'); ?>" title="Enter dob" onchange="getAge();checkARTInitiationDate();" />
                                                  </div>
                                             </div>
                                             <div class="col-xs-3 col-md-3">
                                                  <div class="form-group">
                                                       <label for="ageInYears"><?= _('If DOB unknown, Age in Year(s)'); ?> </label>
                                                       <input type="text" name="ageInYears" id="ageInYears" value="<?= $vlQueryInfo['patient_age_in_years'] ?>" class="form-control forceNumeric" maxlength="2" placeholder="<?= _('Age in Year(s)'); ?>" title="<?= _('Enter age in years'); ?>" />
                                                  </div>
                                             </div>
                                             <div class="col-xs-3 col-md-3">
                                                  <div class="form-group">
                                                       <label for="ageInMonths"><?= _('If Age
                                                            < 1, Age in Month(s)'); ?> </label> <input type="text" name="ageInMonths" id="ageInMonths"  value="<?= $vlQueryInfo['patient_age_in_months'] ?>" class="form-control forceNumeric" maxlength="2" placeholder="<?= _('Age in Month(s)'); ?>" title="<?= _('Enter age in months'); ?>" />
                                                  </div>
                                             </div>
                                        </div>
                                        <div class="row">
                                             <div class="col-xs-3 col-md-3">
                                                  <div class="form-group">
                                                       <label for="patientFirstName"><?= _('Patient First Name'); ?> </label>
                                                       <input type="text" name="patientFirstName" id="patientFirstName"  value="<?= $vlQueryInfo['patient_first_name'] ?>" class="form-control" placeholder="<?= _('Enter Patient First Name'); ?>" title="<?= _('Enter patient First name'); ?>" />
                                                  </div>
                                             </div>
                                             <div class="col-xs-3 col-md-3">
                                                  <div class="form-group">
                                                       <label for="patientLastName"><?= _('Patient Last Name'); ?> </label>
                                                       <input type="text" name="patientLastName" id="patientLastName" value="<?= $vlQueryInfo['patient_last_name'] ?>" class="form-control" placeholder="<?= _('Enter Patient Last Name'); ?>" title="<?= ('Enter patient last name'); ?>" />
                                                  </div>
                                             </div>
                                             <div class="col-xs-3 col-md-3">
                                                  <div class="form-group">
                                                       <label for="gender"><?= _('Gender'); ?> <span class="mandatory">*</span></label><br>
                                                       <label class="radio-inline" style="margin-left:0px;">
                                                            <input type="radio" class="isRequired" id="genderMale" name="gender" value="male" title="<?= _('Please choose gender'); ?>" <?php echo ($vlQueryInfo['patient_gender'] == 'male') ? "checked='checked'" : "" ?>><?= _('Male'); ?>
                                                       </label>&nbsp;&nbsp;
                                                       <label class="radio-inline" style="margin-left:0px;">
                                                            <input type="radio" id="genderFemale" name="gender" value="female" title="<?= _('Please choose gender'); ?>" <?php echo ($vlQueryInfo['patient_gender'] == 'female') ? "checked='checked'" : "" ?>><?= _('Female'); ?>
                                                       </label>&nbsp;&nbsp;
                                                       <!--<label class="radio-inline" style="margin-left:0px;">
                                                       <input type="radio" class="" id="genderNotRecorded" name="gender" value="not_recorded" title="Please check gender">Not Recorded
                                                  </label>-->
                                                  </div>
                                             </div>
                                             <div class="col-xs-3 col-md-3">
                                                  <div class="form-group">
                                                       <label for="patientPhoneNumber"><?= _('Phone Number'); ?></label>
                                                       <input type="text" name="patientPhoneNumber" id="patientPhoneNumber" value="<?= ($vlQueryInfo['patient_mobile_number']); ?>" class="form-control forceNumeric" maxlength="15" placeholder="<?= _('Enter Phone Number'); ?>" title="<?= _('Enter phone number'); ?>" />
                                                  </div>
                                             </div>
                                             
                                        </div>
                                        <div class="row femaleSection" style="display:<?php echo ($vlQueryInfo['patient_gender'] == 'female') ? "" : "none" ?>" ;>
                                                       <div class="col-xs-3 col-md-3">
                                                            <div class="form-group">
                                                                 <label for="patientPregnant"><?= _('Is Patient Pregnant?');?> <span class="mandatory">*</span></label><br>
                                                                 <label class="radio-inline">
                                                                 <input type="radio" class="<?php echo ($vlQueryInfo['patient_gender'] == 'female') ? "isRequired" : ""; ?>" id="pregYes" name="patientPregnant" value="yes" title="<?= _('Please check if patient is pregnant'); ?>" <?php echo ($vlQueryInfo['is_patient_pregnant'] == 'yes') ? "checked='checked'" : "" ?>> <?= _('Yes'); ?>
                                                                 </label>
                                                                 <label class="radio-inline">
                                                                 <input type="radio" class="" id="pregNo" name="patientPregnant" value="no" <?php echo ($vlQueryInfo['is_patient_pregnant'] == 'no') ? "checked='checked'" : "" ?>> <?= _('No'); ?>
                                                                 </label>
                                                            </div>
                                                       </div>
                                                       <div class="col-xs-3 col-md-3">
                                                            <div class="form-group">
                                                                 <label for="patientPregnant"><?= _('If Yes, Number of weeks of pregnancy?');?> </label>
                                                                <input type="text" class="forceNumeric form-control" id="noOfPregnancyWeeks" name="noOfPregnancyWeeks" value="<?= ($vlQueryInfo['no_of_pregnancy_weeks']); ?>" title="<?= _('Number of weeks of pregnancy'); ?>" placeholder="<?= _('Number of weeks of pregnancy'); ?>">
                                                            </div>
                                                       </div>
                                                       <div class="col-xs-3 col-md-3">
                                                            <div class="form-group">
                                                                 <label for="breastfeeding"><?= _('Is Patient Breastfeeding?'); ?> <span class="mandatory">*</span></label><br>
                                                                 <label class="radio-inline">
                                                                 <input type="radio" class="<?php echo ($vlQueryInfo['patient_gender'] == 'female') ? "isRequired" : ""; ?>" id="breastfeedingYes" name="breastfeeding" value="yes" title="<?= _('Please check if patient is breastfeeding'); ?>" <?php echo ($vlQueryInfo['is_patient_breastfeeding'] == 'yes') ? "checked='checked'" : "" ?>> <?= _('Yes'); ?>
                                                                 </label>
                                                                 <label class="radio-inline">
                                                                 <input type="radio" class="" id="breastfeedingNo" name="breastfeeding" value="no" <?php echo ($vlQueryInfo['is_patient_breastfeeding'] == 'no') ? "checked='checked'" : "" ?>> <?= _('No'); ?>
                                                                 </label>
                                                            </div>
                                                       </div>
                                                       <div class="col-xs-3 col-md-3">
                                                            <div class="form-group">
                                                                 <label for="patientPregnant"><?= _('If Yes, For how many weeks?');?> </label>
                                                                <input type="text" class="forceNumeric form-control" id="noOfBreastfeedingWeeks" name="noOfBreastfeedingWeeks" value="<?= ($vlQueryInfo['no_of_breastfeeding_weeks']); ?>" title="<?= _('Number of weeks of breastfeeding'); ?>" placeholder="<?= _('Number of weeks of breastfeeding'); ?>">
                                                            </div>
                                                       </div>
                                                  </div>
                                   </div>
                                   <div class="box box-primary">
                                        <div class="box-header with-border">
                                             <h3 class="box-title"><?= _('Sample Information'); ?></h3>
                                        </div>
                                        <div class="box-body">
                                                <div class="row">
                                                    <div class="col-md-3">
                                                       <div class="form-group">
                                                            <label for=""><?= _('Date of Sample Collection'); ?> <span class="mandatory">*</span></label>
                                                            <input type="text" class="form-control isRequired dateTime" style="width:100%;" name="sampleCollectionDate" id="sampleCollectionDate" placeholder="<?= _('Sample Collection Date'); ?>" title="<?= _('Please select sample collection date'); ?>" value="<?php echo $vlQueryInfo['sample_collection_date']; ?>" onchange="checkSampleReceviedDate();checkSampleTestingDate();">
                                                       </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                       <div class="form-group">
                                                            <label for="specimenType"><?= _('Sample Type'); ?> <span class="mandatory">*</span></label>
                                                            <select name="specimenType" id="specimenType" class="form-control isRequired" title="<?= _('Please choose sample type'); ?>">
                                                                 <option value=""> <?= _('-- Select --'); ?> </option>
                                                                 <?php
                                                                 $selected = '';
                                                                 if (count($sResult) == 1)
                                                                      $selected = "selected='selected'";
                                                                 foreach ($sResult as $name) { ?>
                                                                      <option <?= $selected; ?> <?php if($name['sample_id']==$vlQueryInfo['sample_type']) echo "selected='selected'"; else echo ""; ?> value="<?php echo $name['sample_id']; ?>"><?= $name['sample_name']; ?></option>
                                                                 <?php } ?>
                                                            </select>
                                                       </div>
                                                  </div>
                                               
                                                            <div class="col-md-3">
                                                            <div class="form-group">
                                                                 <label for="reqClinician" class=""><?= _('Name of health personnel collecting sample'); ?></label>
                                                                
                                                                      <input type="text" class="form-control" id="reqClinician" name="reqClinician" value="<?= $vlQueryInfo['request_clinician_name']; ?>" placeholder="<?= _('Request Clinician name'); ?>" title="<?= _('Please enter request clinician'); ?>" />
                                                                      </div>     
                                                            </div>
                                                            <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label for="reqClinicianPhoneNumber" class=""><?= _('Contact Number'); ?> </label>
                                                                      <input type="text" class="form-control forceNumeric" id="reqClinicianPhoneNumber" value="<?= $vlQueryInfo['request_clinician_phone_number']; ?>" name="reqClinicianPhoneNumber" maxlength="15" placeholder="<?= _('Phone Number'); ?>" title="<?= _('Please enter request clinician phone number'); ?>" />
                                                                </div>
                                                            </div>
                                                       </div>
                                        </div>
                                        <div class="box box-primary">
                                             <div class="box-header with-border">
                                                  <h3 class="box-title"><?= _('Treatment Information'); ?></h3>
                                             </div>
                                             <div class="box-body">
                                                  <div class="row">
                                                       <div class="col-xs-3 col-md-3">
                                                            <div class="form-group">
                                                                 <label for=""><?= _('Date of ART Initiation'); ?></label>
                                                                 <input type="text" class="form-control date" name="dateOfArtInitiation" id="dateOfArtInitiation" value="<?= $vlQueryInfo['treatment_initiated_date']; ?>" placeholder="<?= _('Date of ART Initiation'); ?>" title="<?= _('Date of treatment initiation'); ?>" style="width:100%;" onchange="checkARTInitiationDate();">
                                                            </div>
                                                       </div>
                                                       <div class="col-xs-3 col-md-3">
                                                            <div class="form-group">
                                                            <label for="lineOfTreatment" class="labels"><?= _('Line of Treatment'); ?> </label>                                                                
                                                            <select class="form-control" name="lineOfTreatment" id="lineOfTreatment" title="<?= _('Line Of Treatment'); ?>">
                                                                <option value="">--Select--</option>
                                                                <option value="1" <?php echo ($vlQueryInfo['line_of_treatment'] == '1') ? "selected='selected' " : "" ?>><?= _('1st Line'); ?></option>
                                                                <option value="2" <?php echo ($vlQueryInfo['line_of_treatment'] == '2') ? "selected='selected' " : "" ?>><?= _('2nd Line'); ?></option>
                                                                <option value="3" <?php echo ($vlQueryInfo['line_of_treatment'] == '3') ? "selected='selected' " : "" ?>><?= _('3rd Line'); ?></option>
                                                                <option value="n/a" <?php echo ($vlQueryInfo['line_of_treatment'] == 'n/a') ? "selected='selected' " : "" ?>><?= _('N/A'); ?></option>
                                                            </select>
                                                            </div>
                                                       </div>
                                                       <div class="col-xs-3 col-md-3">
                                                            <div class="form-group">
                                                                 <label for=""> <?= _('Current ARV Protocol'); ?></label>
                                                                 <input type="text" class="form-control" style="width:100%;" value="<?php echo ($vlQueryInfo['current_arv_protocol']); ?>" name="currentArvProtocol" id="currentArvProtocol" placeholder="<?= _('Current ARV Protocol'); ?>" title="<?= _('Please enter current ARV protocol'); ?>">
                                                            </div>
                                                       </div>
                                                     <!--  <div class="col-xs-3 col-md-3">
                                                            <div class="form-group">
                                                                 <label for="arvAdherence"><?= _('Reason of Request of the Viral Load'); ?></label>
                                                                 <select name="reasonForVLTesting" id="reasonForVLTesting" class="form-control" title="<?= _('Please choose reason of request of VL'); ?>" onchange="checkreasonForVLTesting();">
                                                                    <option value="">  <?= _("-- Select --"); ?>  </option>
                                                                    <?php
                                                                    foreach ($vlTestReasonResult as $tReason) {
                                                                    ?>
                                                                        <option value="<?php echo $tReason['test_reason_id']; ?>" <?php echo ($vlQueryInfo['reason_for_vl_testing'] == $tReason['test_reason_id']) ? 'selected="selected"' : ''; ?>><?php echo ($tReason['test_reason_name']); ?></option>
                                                                    <?php } ?>
                                                                    <option value="other">Other</option>
                                                                </select>
                                                                <input type="text" class="form-control" name="newreasonForVLTesting" id="newreasonForVLTesting" placeholder="<?= _('Enter new reason of testing'); ?>" title="<?= _('Enter new reason of testing'); ?>" style="width:100%; display:none;">
                                                            </div>
                                                       </div>-->
                                                  </div>
                                             </div>
                                             <div class="box box-primary">
                                                  <div class="box-header with-border">
                                                       <h3 class="box-title"><?= _('Reason of Request of the Viral Load'); ?> <span class="mandatory">*</span></h3><small> <?= _('(Please pick one): (To be completed by clinician)'); ?></small>
                                                  </div>
                                                  <div class="box-body">
                                                       <div class="row">
                                                            <div class="col-md-6">
                                                                 <div class="form-group">
                                                                      <div class="col-lg-12">
                                                                           <label class="radio-inline">
                                                                           <?php
                                                                                     $vlTestReasonQueryRow = "SELECT * from r_vl_test_reasons where test_reason_id='" . trim($vlQueryInfo['reason_for_vl_testing']) . "' OR test_reason_name = '" . trim($vlQueryInfo['reason_for_vl_testing']) . "'";
                                                                                     $vlTestReasonResultRow = $db->query($vlTestReasonQueryRow);
                                                                                     $checked = '';
                                                                                     $display = '';
                                                                                     $vlValue = '';
                                                                                     if (trim($vlQueryInfo['reason_for_vl_testing']) == 'controlVlTesting' || isset($vlTestReasonResultRow[0]['test_reason_id']) && $vlTestReasonResultRow[0]['test_reason_name'] == 'controlVlTesting') {
                                                                                          $checked = 'checked="checked"';
                                                                                          $display = 'block';
                                                                                         
                                                                                     } else {
                                                                                          $checked = '';
                                                                                          $display = 'none';
                                                                                     }
                                                                                     ?>
                                                                                <input type="radio" class="isRequired" id="rmTesting" name="reasonForVLTesting" value="controlVlTesting" title="<?= _('Please check viral load indication testing type'); ?>" <?php echo $checked; ?> onclick="showTesting('rmTesting');">
                                                                                <strong><?= _('Control VL Testing'); ?></strong>
                                                                           </label>
                                                                      </div>
                                                                 </div>
                                                            </div>
                                                       </div>
                                                       <div class="row rmTesting hideTestData well"  style="display:<?php echo $display; ?>;">
                                                            <div class="col-md-6">
                                                                 <label class="col-lg-5 control-label"><?= _('Types Of Control VL Testing'); ?></label>
                                                                 <div class="col-lg-7">
                                        
                                                                      <select name="controlVlTestingType" id="controlVlType" class="form-control" title="<?= _('Please choose reason of request of VL'); ?>" onchange="checkreasonForVLTesting();">
                                                                           <option value="">  <?= _("-- Select --"); ?>  </option>
                                                                           <option value="6 Months" <?php echo ($vlQueryInfo['control_vl_testing_type'] == '6 Months') ? "selected='selected' " : "" ?>><?= _('6 Months'); ?></option>
                                                                           <option value="12 Months" <?php echo ($vlQueryInfo['control_vl_testing_type'] == '12 Months') ? "selected='selected' " : "" ?>><?= _('12 Months'); ?></option>
                                                                           <option value="24 Months" <?php echo ($vlQueryInfo['control_vl_testing_type'] == '24 Months') ? "selected='selected' " : "" ?>><?= _('24 Months'); ?></option>
                                                                           <option value="36 Months(3 Years)" <?php echo ($vlQueryInfo['control_vl_testing_type'] == '36 Months(3 Years)') ? "selected='selected' " : "" ?>><?= _('36 Months(3 Years)'); ?></option>
                                                                           <option value=">= 4 years" <?php echo ($vlQueryInfo['control_vl_testing_type'] == '>= 4 years') ? "selected='selected' " : "" ?>><?= _('>= 4 years'); ?></option>
                                                                           <option value="3 months after a VL > 1000cp/ml" <?php echo ($vlQueryInfo['control_vl_testing_type'] == '3 months after a VL > 1000cp/ml') ? "selected='selected' " : "" ?>><?= _('3 months after a VL > 1000cp/ml'); ?></option>
                                                                           <option value="Suspected Treatment Failure" <?php echo ($vlQueryInfo['control_vl_testing_type'] == 'Suspected Treatment Failure') ? "selected='selected' " : "" ?>><?= _('Suspected Treatment Failure'); ?></option>
                                                                           <option value="VL Pregnant Woman" <?php echo ($vlQueryInfo['control_vl_testing_type'] == 'VL Pregnant Woman') ? "selected='selected' " : "" ?>><?= _('VL Pregnant Woman'); ?></option>
                                                                           <option value="VL Breastfeeding woman" <?php echo ($vlQueryInfo['control_vl_testing_type'] == 'VL Breastfeeding woman') ? "selected='selected' " : "" ?>><?= _('VL Breastfeeding woman'); ?></option>
                                                                      </select>
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
                                                                                     $vlValue = '';
                                                                                     if (trim($vlQueryInfo['reason_for_vl_testing']) == 'coinfection' || isset($vlTestReasonResultRow[0]['test_reason_id']) && $vlTestReasonResultRow[0]['test_reason_name'] == 'coinfection') {
                                                                                          $checked = 'checked="checked"';
                                                                                          $display = 'block';
                                                                                         
                                                                                     } else {
                                                                                          $checked = '';
                                                                                          $display = 'none';
                                                                                     }
                                                                                     ?>
                                                                                <input type="radio" class="" id="suspendTreatment" name="reasonForVLTesting" value="coinfection" title="Please check viral load indication testing type" <?= $checked; ?> onclick="showTesting('suspendTreatment');">
                                                                                <strong><?= _('Co-infection'); ?></strong>
                                                                           </label>
                                                                      </div>
                                                                 </div>
                                                            </div>
                                                       </div>
                                                       <div class="row suspendTreatment hideTestData well" style="display: <?php echo $display; ?>;">
                                                            <div class="col-md-6">
                                                                 <label class="col-lg-5 control-label"><?= _('Types of Co-infection'); ?></label>
                                                                 <div class="col-lg-7">
                                                                 <select name="coinfectionType" id="coinfectionType" class="form-control" title="<?= _('Please choose reason of request of VL'); ?>" onchange="checkreasonForVLTesting();">
                                                                           <option value="">  <?= _("-- Select --"); ?>  </option>
                                                                           <option value="Tuberculosis" <?php echo ($vlQueryInfo['coinfection_type'] == 'Tuberculosis') ? "selected='selected' " : "" ?>><?= _('Tuberculosis'); ?></option>
                                                                           <option value="Viral Hepatitis" <?php echo ($vlQueryInfo['coinfection_type'] == 'Viral Hepatitis') ? "selected='selected' " : "" ?>><?= _('Viral Hepatitis'); ?></option>
                                                                    </select>
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
                                                                                     $vlValue = '';
                                                                                     if (trim($vlQueryInfo['reason_for_vl_testing']) == 'other' || isset($vlTestReasonResultRow[0]['test_reason_id']) && $vlTestReasonResultRow[0]['test_reason_name'] == 'other') {
                                                                                          $checked = 'checked="checked"';
                                                                                          $display = 'block';
                                                                                         
                                                                                     } else {
                                                                                          $checked = '';
                                                                                          $display = 'none';
                                                                                     }
                                                                                     ?>
                                                                                <input type="radio" class="" id="repeatTesting" name="reasonForVLTesting" value="other" title="<?= _('Please check reason for viral load request'); ?>" <?= $checked; ?> onclick="showTesting('repeatTesting');">
                                                                                <strong><?= _('Other reasons') ?> </strong>
                                                                           </label>
                                                                      </div>
                                                                 </div>
                                                            </div>
                                                       </div>
                                                       <div class="row repeatTesting hideTestData well" style="display: <?php echo $display; ?>;">
                                                            <div class="col-md-6">
                                                                 <label class="col-lg-5 control-label"><?= _('Please specify other reasons'); ?></label>
                                                                 <div class="col-lg-7">
                                                                      <input type="text" value="<?php echo ($vlQueryInfo['reason_for_vl_testing_other']); ?>" class="form-control" id="newreasonForVLTesting" name="newreasonForVLTesting" placeholder="<?= _('Please specify other test reason') ?>" title="<?= _('Please specify other test reason') ?>" />
                                                                 </div>
                                                            </div>
                                                           
                                                       </div>

                                                       <?php if (isset(SYSTEM_CONFIG['recency']['vlsync']) && SYSTEM_CONFIG['recency']['vlsync']) {  ?>
                                                            <div class="row">
                                                                 <div class="col-md-6">
                                                                      <div class="form-group">
                                                                           <div class="col-lg-12">
                                                                                <label class="radio-inline">
                                                                                     <input type="radio" class="" id="recencyTest" name="reasonForVLTesting" value="recency" title="Please check viral load indication testing type" onclick="showTesting('recency')">
                                                                                     <strong><?= _('Confirmation Test for Recency'); ?></strong>
                                                                                </label>
                                                                           </div>
                                                                      </div>
                                                                 </div>
                                                            </div>
                                                       <?php }  ?>
                                                       <hr>
                                                  
                                                  </div>
                                             </div>
                                             
                                             <?php if ($usersService->isAllowed('/vl/results/updateVlTestResult.php') && $_SESSION['accessType'] != 'collection-site') { ?>
                                                  <div class="box box-primary">
                                                       <div class="box-header with-border">
                                                            <h3 class="box-title"><?= _('Laboratory Information'); ?></h3>
                                                       </div>
                                                       <div class="box-body">
                                                            <div class="row">
                                                                 <div class="col-md-6">
                                                                      <label for="testingPlatform" class="col-lg-5 control-label"><?= _('VL Testing Platform'); ?> </label>
                                                                      <div class="col-lg-7">
                                                                           <select name="testingPlatform" id="testingPlatform" class="form-control" title="<?= _('Please choose VL Testing Platform'); ?>" <?php echo $labFieldDisabled; ?> onchange="hivDetectionChange();">
                                                                                <option value=""><?= _('-- Select --'); ?></option>
                                                                                <?php foreach ($importResult as $mName) { ?>
                                                                                     <option value="<?php echo $mName['machine_name'] . '##' . $mName['lower_limit'] . '##' . $mName['higher_limit'] . '##' . $mName['config_id']; ?>" <?php echo ($vlQueryInfo['vl_test_platform'] == $mName['machine_name']) ? 'selected="selected"' : ''; ?>><?php echo $mName['machine_name']; ?></option>
                                                                                <?php } ?>
                                                                           </select>
                                                                      </div>
                                                                 </div>
                                                                 <div class="col-md-6">
                                                                      <label class="col-lg-5 control-label" for="sampleReceivedDate"><?= _('Date Sample Received at Testing Lab'); ?> </label>
                                                                      <div class="col-lg-7">
                                                                           <input type="text" class="form-control dateTime" id="sampleReceivedDate" name="sampleReceivedDate" placeholder="<?= _('Sample Received Date'); ?>" value="<?php echo $vlQueryInfo['sample_received_at_vl_lab_datetime']; ?>" title="<?= _('Please select sample received date'); ?>" <?php echo $labFieldDisabled; ?> onchange="checkSampleReceviedDate()" />
                                                                      </div>
                                                                 </div>
                                                            </div>
                                                            <div class="row">
                                                                 <div class="col-md-6">
                                                                      <label class="col-lg-5 control-label" for="sampleTestingDateAtLab"><?= _('Sample Testing Date'); ?> </label>
                                                                      <div class="col-lg-7">
                                                                           <input type="text" class="form-control dateTime" id="sampleTestingDateAtLab" name="sampleTestingDateAtLab" placeholder="<?= _('Sample Testing Date'); ?>" value="<?php echo $vlQueryInfo['sample_tested_datetime']; ?>" title="<?= _('Please select sample testing date'); ?>" <?php echo $labFieldDisabled; ?> onchange="checkSampleTestingDate();" />
                                                                      </div>
                                                                 </div>
                                                                 <div class="col-md-6">
                                                                      <label class="col-lg-5 control-label" for="resultDispatchedOn"><?= _('Date Results Dispatched'); ?> </label>
                                                                      <div class="col-lg-7">
                                                                           <input type="text" class="form-control dateTime" id="resultDispatchedOn" name="resultDispatchedOn" placeholder="<?= _('Result Dispatched Date'); ?>" value="<?php echo $vlQueryInfo['result_dispatched_datetime']; ?>" title="<?= _('Please select result dispatched date'); ?>" <?php echo $labFieldDisabled; ?> />
                                                                      </div>
                                                                 </div>
                                                            </div>
                                                            <div class="row">
                                                                 <div class="col-md-6">
                                                                      <label class="col-lg-5 control-label" for="noResult"><?= _('Sample Rejection'); ?> </label>
                                                                      <div class="col-lg-7">
                                                                           <select name="noResult" id="noResult" class="form-control" title="<?= _('Please check if sample is rejected or not'); ?>">
                                                                                <option value=""><?= _('-- Select --'); ?></option>
                                                                                <option value="yes" <?php echo ($vlQueryInfo['is_sample_rejected'] == 'yes') ? 'selected="selected"' : ''; ?>><?= _('Yes'); ?></option>
                                                                                <option value="no" <?php echo ($vlQueryInfo['is_sample_rejected'] == 'no') ? 'selected="selected"' : ''; ?>><?= _('No'); ?></option>
                                                                           </select>
                                                                      </div>
                                                                 </div>

                                                                 <div class="col-md-6 rejectionReason" style="display:<?php echo ($vlQueryInfo['is_sample_rejected'] == 'yes') ? '' : 'none'; ?>;">
                                                                      <label class="col-lg-5 control-label" for="rejectionReason"><?= _('Rejection Reason'); ?> </label>
                                                                      <div class="col-lg-7">
                                                                           <select name="rejectionReason" id="rejectionReason" class="form-control" title="<?= _('Please choose reason'); ?>" <?php echo $labFieldDisabled; ?> onchange="checkRejectionReason();">
                                                                                <option value=""><?= _('-- Select --'); ?></option>
                                                                                <?php foreach ($rejectionTypeResult as $type) { ?>
                                                                                     <optgroup label="<?php echo ($type['rejection_type']); ?>">
                                                                                          <?php foreach ($rejectionResult as $reject) {
                                                                                               if ($type['rejection_type'] == $reject['rejection_type']) {
                                                                                          ?>
                                                                                                    <option value="<?php echo $reject['rejection_reason_id']; ?>" <?php echo ($vlQueryInfo['reason_for_sample_rejection'] == $reject['rejection_reason_id']) ? 'selected="selected"' : ''; ?>> <?= $reject['rejection_reason_name']; ?></option>
                                                                                          <?php }
                                                                                          } ?>
                                                                                     </optgroup>
                                                                                <?php } ?>
                                                                               
                                                                           </select>
                                                                      </div>
                                                                 </div>
                                                                 <div class="col-md-6 vlResult">
                                                                      <label class="col-lg-5 control-label" for="vlResult"><?= _('Viral Load Result (copies/ml)'); ?> </label>
                                                                      <div class="col-lg-7 resultInputContainer">
                                                                           <input list="possibleVlResults" autocomplete="off" class="form-control" id="vlResult" name="vlResult" placeholder="<?= _('Viral Load Result'); ?>" title="<?= _('Please enter viral load result'); ?>" value="<?= ($vlQueryInfo['result']); ?>" <?php echo $labFieldDisabled; ?> style="width:100%;" onchange="calculateLogValue(this)" disabled />
                                                                           <datalist id="possibleVlResults">

                                                                           </datalist>
                                                                           <!--   <input type="checkbox" class="labSection specialResults" name="lt20" value="yes" title="Please check <20">
                                                                           &lt; 20<br>
                                                                           <input type="checkbox" class="labSection specialResults" name="lt40" value="yes" title="Please check <40">
                                                                           &lt; 40<br>
                                                                           <input type="checkbox" class="specialResults" name="tnd" value="yes" title="Please check tnd" <?php echo $labFieldDisabled; ?>> Target Not Detected<br>
                                                                           <input type="checkbox" class="specialResults" name="bdl" value="yes" title="Please check bdl" <?php echo $labFieldDisabled; ?>> Below Detection Level<br>
                                                                           <input type="checkbox" class="specialResults" name="failed" value="yes" title="Please check failed" <?php echo $labFieldDisabled; ?>> Failed<br>
                                                                           <input type="checkbox" class="specialResults" name="invalid" value="yes" title="Please check invalid" <?php echo $labFieldDisabled; ?>> Invalid-->
                                                                      </div>
                                                                 </div>
                                                            </div>
                                                            <div class="row">
                                                                 <div class="col-md-6 rejectionReason" style="display:none;">
                                                                      <label class="col-lg-5 control-label labels" for="rejectionDate"><?= _('Rejection Date'); ?> </label>
                                                                      <div class="col-lg-7">
                                                                           <input class="form-control date rejection-date" type="text" name="rejectionDate" id="rejectionDate" placeholder="<?= _('Select Rejection Date'); ?>" title="<?= _('Please select rejection date'); ?>" />
                                                                      </div>
                                                                 </div>
                                                                 <div class="col-md-6 vlResult" style="display:<?php echo ($vlQueryInfo['is_sample_rejected'] == 'yes') ? 'none' : 'block'; ?>;">
                                                                      <label class="col-lg-5 control-label" for="vlLog"><?= _('Viral Load (Log)'); ?> </label>
                                                                      <div class="col-lg-7">
                                                                           <input type="text" class="form-control" id="vlLog" name="vlLog" placeholder="<?= _('Viral Load Log'); ?>" title="<?= _('Please enter viral load log'); ?>" <?php echo $labFieldDisabled; ?> style="width:100%;" onchange="calculateLogValue(this);" />
                                                                      </div>
                                                                 </div>
                                                                 <div class="col-md-6">
                                                                      <label class="col-lg-5 control-label" for="reviewedOn"><?= _('Reviewed On'); ?> </label>
                                                                      <div class="col-lg-7">
                                                                           <input type="text" name="reviewedOn" id="reviewedOn" value="<?php echo $vlQueryInfo['result_reviewed_datetime']; ?>" class="dateTime form-control" placeholder="<?= _('Reviewed on'); ?>" title="<?= _('Please enter the Reviewed on'); ?>" />
                                                                      </div>
                                                                 </div>
                                                            </div>
                                                            <div class="row">
                                                                 <div class="col-md-6">
                                                                      <label class="col-lg-5 control-label" for="reviewedBy"><?= _('Reviewed By'); ?> </label>
                                                                      <div class="col-lg-7">
                                                                      <select name="reviewedBy" id="reviewedBy" class="select2 form-control" title="<?= _('Please choose reviewed by'); ?>" style="width: 100%;">
                                                                                     <?= $general->generateSelectOptions($userInfo, $vlQueryInfo['result_reviewed_by'], '<?= _("-- Select --"); ?>'); ?>
                                                                                </select>
                                                                      </div>
                                                                 </div>

                                                                 <div class="col-md-6">
                                                                      <label class="col-lg-5 control-label" for="approvedOnDateTime"><?= _('Approved On'); ?> </label>
                                                                      <div class="col-lg-7">
                                                                           <input type="text" name="approvedOnDateTime" id="approvedOnDateTime" value="<?php echo $vlQueryInfo['result_approved_datetime']; ?>" class="dateTime form-control" placeholder="Approved on" title="Please enter the Approved on" />
                                                                      </div>
                                                                 </div>
                                                            </div>
                                                            <div class="row">
                                                                 <div class="col-md-6">
                                                                      <label class="col-lg-5 control-label" for="approvedBy"><?= _('Approved By'); ?> </label>
                                                                      <div class="col-lg-7">
                                                                           <select name="approvedBy" id="approvedBy" class="form-control" title="Please choose approved by" <?php echo $labFieldDisabled; ?>>
                                                                            <option value=""><?= _('-- Select --'); ?></option>
                                                                                     <?php foreach ($userResult as $uName) { ?>
                                                                                          <option value="<?php echo $uName['user_id']; ?>" <?php echo ($vlQueryInfo['result_approved_by'] == $uName['user_id']) ? "selected=selected" : ""; ?>><?php echo ($uName['user_name']); ?></option>
                                                                                     <?php } ?>
                                                                           </select>
                                                                      </div>
                                                                 </div>
                                                                 <div class="col-md-6">
                                                                      <label class="col-lg-5 control-label" for="labComments"><?= _('Lab Tech. Comments'); ?> </label>
                                                                      <div class="col-lg-7">
                                                                           <textarea class="form-control" name="labComments" id="labComments" placeholder="<?= _('Lab comments'); ?>" <?php echo $labFieldDisabled; ?>><?php echo trim($vlQueryInfo['lab_tech_comments']); ?></textarea>
                                                                      </div>
                                                                 </div>
                                                            </div>
                                                       </div>
                                                  </div>
                                             <?php } ?>
                                        </div>
                                        <div class="box-footer">
                                             <!-- BARCODESTUFF START -->
                                             <?php if (isset($global['bar_code_printing']) && $global['bar_code_printing'] == 'zebra-printer') { ?>
                                                  <div id="printer_data_loading" style="display:none"><span id="loading_message">Loading Printer Details...</span><br />
                                                       <div class="progress" style="width:100%">
                                                            <div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%">
                                                            </div>
                                                       </div>
                                                  </div> <!-- /printer_data_loading -->
                                                  <div id="printer_details" style="display:none">
                                                       <span id="selected_printer">No printer selected!</span>
                                                       <button type="button" class="btn btn-success" onclick="changePrinter()">Change/Retry</button>
                                                  </div><br /> <!-- /printer_details -->
                                                  <div id="printer_select" style="display:none">
                                                       Zebra Printer Options<br />
                                                       Printer: <select id="printers"></select>
                                                  </div> <!-- /printer_select -->
                                             <?php } ?>
                                             <!-- BARCODESTUFF END -->
                                             <a class="btn btn-primary btn-disabled" href="javascript:void(0);" onclick="validateNow();return false;"><?= _('Save'); ?></a>
                                             <input type="hidden" name="saveNext" id="saveNext" />
                                             <?php if ($arr['sample_code'] == 'auto' || $arr['sample_code'] == 'YY' || $arr['sample_code'] == 'MMYY') { ?>
                                                  <input type="hidden" name="sampleCodeFormat" id="sampleCodeFormat" value="<?php echo $sFormat; ?>" />
                                                  <input type="hidden" name="sampleCodeKey" id="sampleCodeKey" value="<?php echo $sKey; ?>" />
                                             <?php } ?>
                                             <input type="hidden" name="vlSampleId" id="vlSampleId" value="<?= ($vlQueryInfo['vl_sample_id']); ?>" />
                                             <input type="hidden" name="isRemoteSample" value="<?= ($vlQueryInfo['remote_sample']); ?>" />
                                             <input type="hidden" name="oldStatus" value="<?= ($vlQueryInfo['result_status']); ?>" />
                                             <input type="hidden" name="countryFormId" id="countryFormId" value="<?php echo $arr['vl_form']; ?>" />

                                             <input type="hidden" name="provinceId" id="provinceId" />
                                             <a class="btn btn-primary btn-disabled" href="javascript:void(0);" onclick="validateSaveNow();return false;"><?= _('Save and Next'); ?></a>
                                             <a href="vlRequest.php" class="btn btn-default"> <?= _('Cancel'); ?></a>
                                        </div>
                                        <input type="hidden" id="selectedSample" value="" name="selectedSample" class="" />
                                        <input type="hidden" name="countryFormId" id="countryFormId" value="<?php echo $arr['vl_form']; ?>" />

                    </form>
               </div>
     </section>
</div>
<!-- BARCODESTUFF START -->
<?php
if (isset($global['bar_code_printing']) && $global['bar_code_printing'] != "off") {
     if ($global['bar_code_printing'] == 'dymo-labelwriter-450') {
?>
          <script src="/assets/js/DYMO.Label.Framework.js"></script>
          <script src="/configs/dymo-format.js"></script>
          <script src="/assets/js/dymo-print.js"></script>
     <?php
     } else if ($global['bar_code_printing'] == 'zebra-printer') {
     ?>
          <script src="/assets/js/zebra-browserprint.js.js"></script>
          <script src="/configs/zebra-format.js"></script>
          <script src="/assets/js/zebra-print.js"></script>
<?php
     }
}
?>

<!-- BARCODESTUFF END -->
<script>
     provinceName = true;
     facilityName = true;
     $(document).ready(function() {

          if ($(".specialResults:checked").length > 0) {
               $('#vlResult, #vlLog').val('');
               $('#vlResult,#vlLog').attr('readonly', true);
               $('#vlResult, #vlLog').removeClass('isRequired');
               $(".specialResults").attr('disabled', false);
               $(".specialResults").not($(".specialResults:checked")).attr('disabled', true);
               $('.specialResults').not($(".specialResults:checked")).prop('checked', false).removeAttr('checked');
          }
          if ($('#vlResult, #vlLog').val() != '') {
               $('.specialResults').prop('checked', false).removeAttr('checked');
               $(".specialResults").attr('disabled', true);
               $('#vlResult').addClass('isRequired');
          }
          $('#fName').select2({
               placeholder: "Select Clinic/Health Center"
          });
          $('#labId').select2({
               placeholder: "Select Lab Name"
          });
          $('#reviewedBy').select2({
               placeholder: "Select Reviewed By"
          });
          $('#approvedBy').select2({
               placeholder: "Select Approved By"
          });

          getfacilityProvinceDetails($("#fName").val());

          getAge();
          __clone = $("#vlRequestFormCameroon .labSection").clone();
          reason = ($("#reasonForResultChanges").length) ? $("#reasonForResultChanges").val() : '';
          result = ($("#vlResult").length) ? $("#vlResult").val() : '';
          //logVal = ($("#vlLog").length)?$("#vlLog").val():'';

          $("#vlLog").on('keyup keypress blur change paste', function() {
               if ($(this).val() != '') {
                    if ($(this).val() != $(this).val().replace(/[^\d\.]/g, "")) {
                         $(this).val('');
                         alert('Please enter only numeric values for Viral Load Log Result')
                    }
               }
          });
          //hivDetectionChange();
     });

     function hivDetectionChange() {

          var text = $('#testingPlatform').val();
          if (!text) {
               $("#vlResult").attr("disabled", true);
               return;
          }
          var str1 = text.split("##");
          var str = str1[0];

          $(".vlResult, .vlLog").show();
          $("#noResult").val("");
          //Get VL results by platform id
          var platformId = str1[3];
          $("#possibleVlResults").html('');
          $.post("/vl/requests/getVlResults.php", {
                    instrumentId: platformId,
               },
               function(data) {
                    // alert(data);
                    if (data != "") {
                         $("#possibleVlResults").html(data);
                         $("#vlResult").attr("disabled", false);
                    }
               });

     }

     function showTesting(chosenClass) {
          $(".viralTestData").val('');
          $(".hideTestData").hide();
          $("." + chosenClass).show();
     }

     function getProvinceDistricts(obj) {
          $.blockUI();
          var cName = $("#fName").val();
          var pName = $("#province").val();
          if (pName != '' && provinceName && facilityName) {
               facilityName = false;
          }
          if (pName != '') {
               //if (provinceName) {
               $.post("/includes/siteInformationDropdownOptions.php", {
                         pName: pName,
                         testType: 'vl'
                    },
                    function(data) {
                         if (data != "") {
                              details = data.split("###");
                              $("#district").html(details[1]);
                              $("#fName").html("<option data-code='' data-emails='' data-mobile-nos='' data-contact-person='' value=''> <?= _('-- Select --'); ?> </option>");
                              $(".facilityDetails").hide();
                              $(".facilityEmails").html('');
                              $(".facilityMobileNumbers").html('');
                              $(".facilityContactPerson").html('');
                         }
                    });
               //}

          } else if (pName == '') {
               provinceName = true;
               facilityName = true;
               $("#province").html("<?php echo $province; ?>");
               $("#district").html("<option value=''> <?= _('-- Select --'); ?> </option>");
               $("#fName").html("<?php echo $facility; ?>");
               $("#fName").select2("val", "");
          }
          $.unblockUI();
     }

     function getFacilities(obj) {
          $.blockUI();
          var dName = $("#district").val();
          var cName = $("#fName").val();
          if (dName != '') {
               $.post("/includes/siteInformationDropdownOptions.php", {
                         dName: dName,
                         cliName: cName,
                         testType: 'vl'
                    },
                    function(data) {
                         if (data != "") {
                              details = data.split("###");
                              $("#fName").html(details[0]);
                              //$("#labId").html(details[1]);
                              $(".facilityDetails").hide();
                              $(".facilityEmails").html('');
                              $(".facilityMobileNumbers").html('');
                              $(".facilityContactPerson").html('');
                         }
                    });
          }
          $.unblockUI();
     }

     function getfacilityProvinceDetails(obj) {
          $.blockUI();
          //check facility name
          var cName = $("#fName").val();
          var pName = $("#province").val();
          if (cName != '' && provinceName && facilityName) {
               provinceName = false;
          }
          if (cName != '' && facilityName) {
               $.post("/includes/siteInformationDropdownOptions.php", {
                         cName: cName,
                         testType: 'vl'
                    },
                    function(data) {
                         if (data != "") {
                              details = data.split("###");
                              $("#province").html(details[0]);
                              $("#district").html(details[1]);
                              $("#clinicianName").val(details[2]);
                         }
                    });
          } else if (pName == '' && cName == '') {
               provinceName = true;
               facilityName = true;
               $("#province").html("<?php echo $province; ?>");
               $("#facilityId").html("<?php echo $facility; ?>");
          }
          $.unblockUI();
     }

     function fillFacilityDetails(obj) {
          getfacilityProvinceDetails(obj)
          $("#fCode").val($('#fName').find(':selected').data('code'));
          var femails = $('#fName').find(':selected').data('emails');
          var fmobilenos = $('#fName').find(':selected').data('mobile-nos');
          var fContactPerson = $('#fName').find(':selected').data('contact-person');
          if ($.trim(femails) != '' || $.trim(fmobilenos) != '' || fContactPerson != '') {
               $(".facilityDetails").show();
          } else {
               $(".facilityDetails").hide();
          }
          ($.trim(femails) != '') ? $(".femails").show(): $(".femails").hide();
          ($.trim(femails) != '') ? $(".facilityEmails").html(femails): $(".facilityEmails").html('');
          ($.trim(fmobilenos) != '') ? $(".fmobileNumbers").show(): $(".fmobileNumbers").hide();
          ($.trim(fmobilenos) != '') ? $(".facilityMobileNumbers").html(fmobilenos): $(".facilityMobileNumbers").html('');
          ($.trim(fContactPerson) != '') ? $(".fContactPerson").show(): $(".fContactPerson").hide();
          ($.trim(fContactPerson) != '') ? $(".facilityContactPerson").html(fContactPerson): $(".facilityContactPerson").html('');
     }

     $("input:radio[name=gender]").click(function() {
          if ($(this).val() == 'male' || $(this).val() == 'not_recorded') {
               $('.femaleSection').hide();
               $('input[name="breastfeeding"]').prop('checked', false);
               $('input[name="patientPregnant"]').prop('checked', false);
               $('#breastfeedingYes').removeClass('isRequired');
               $('#pregYes').removeClass('isRequired');
          } else if ($(this).val() == 'female') {
               $('.femaleSection').show();
               $('#breastfeedingYes').addClass('isRequired');
               $('#pregYes').addClass('isRequired');
          }
     });

     $("#noResult").change(function() {

          if ($(this).val() == 'yes') {
               $('.rejectionReason').show();
               $('.vlResult').css('display', 'none');
               $('#rejectionReason').addClass('isRequired');
               $('#vlResult').removeClass('isRequired');
          } else {
               $('.vlResult').css('display', 'block');
               $('.rejectionReason').hide();
               $('#rejectionReason').removeClass('isRequired');
               // $('#vlResult').addClass('isRequired');
               // if any of the special results like tnd,bld are selected then remove isRequired from vlResult
               if ($('.specialResults:checkbox:checked').length) {
                    $('#vlResult').removeClass('isRequired');
               }
               $('#rejectionReason').val('');
          }
     });


     $('.specialResults').change(function() {
          if ($(this).is(':checked')) {
               $('#vlResult,#vlLog').attr('readonly', true);
               $('#vlResult').removeClass('isRequired');
               $(".specialResults").not(this).attr('disabled', true);
               $("#sampleTestingDateAtLab").addClass('isRequired');
          } else {
               $('#vlResult,#vlLog').attr('readonly', false);
               $(".specialResults").not(this).attr('disabled', false);
               if ($('#noResultNo').is(':checked')) {
                    $('#vlResult').addClass('isRequired');
                    //$("#sampleTestingDateAtLab").addClass('isRequired');
               }
          }
     });

     $('#vlResult,#vlLog').on('keyup keypress blur change paste input', function(e) {
          if (this.value != '') {
               $(".specialResults").not(this).attr('disabled', true);
               $("#sampleTestingDateAtLab").addClass('isRequired');
          } else {
               $(".specialResults").not(this).attr('disabled', false);
               $("#sampleTestingDateAtLab").removeClass('isRequired');
          }
     });

     $('#rmTestingVlValue').on('input', function(e) {
          if (this.value != '') {
               $('#rmTestingVlCheckValuelt20').attr('disabled', true);
               $('#rmTestingVlCheckValueTnd').attr('disabled', true);
          } else {
               $('#rmTestingVlCheckValuelt20').attr('disabled', false);
               $('#rmTestingVlCheckValueTnd').attr('disabled', false);
          }
     });

     $('#rmTestingVlCheckValuelt20').change(function() {
          if ($('#rmTestingVlCheckValuelt20').is(':checked')) {
               $('#rmTestingVlValue').attr('readonly', true);
               $('#rmTestingVlCheckValueTnd').attr('disabled', true);
          } else {
               $('#rmTestingVlValue').attr('readonly', false);
               $('#rmTestingVlCheckValueTnd').attr('disabled', false);
          }
     });

     $('#rmTestingVlCheckValueTnd').change(function() {
          if ($('#rmTestingVlCheckValueTnd').is(':checked')) {
               $('#rmTestingVlValue').attr('readonly', true);
               $('#rmTestingVlCheckValuelt20').attr('disabled', true);
          } else {
               $('#rmTestingVlValue').attr('readonly', false);
               $('#rmTestingVlCheckValuelt20').attr('disabled', false);
          }
     });

     $('#repeatTestingVlValue').on('input', function(e) {
          if (this.value != '') {
               $('#repeatTestingVlCheckValuelt20').attr('disabled', true);
               $('#repeatTestingVlCheckValueTnd').attr('disabled', true);
          } else {
               $('#repeatTestingVlCheckValuelt20').attr('disabled', false);
               $('#repeatTestingVlCheckValueTnd').attr('disabled', false);
          }
     });

     $('#repeatTestingVlCheckValuelt20').change(function() {
          if ($('#repeatTestingVlCheckValuelt20').is(':checked')) {
               $('#repeatTestingVlValue').attr('readonly', true);
               $('#repeatTestingVlCheckValueTnd').attr('disabled', true);
          } else {
               $('#repeatTestingVlValue').attr('readonly', false);
               $('#repeatTestingVlCheckValueTnd').attr('disabled', false);
          }
     });

     $('#repeatTestingVlCheckValueTnd').change(function() {
          if ($('#repeatTestingVlCheckValueTnd').is(':checked')) {
               $('#repeatTestingVlValue').attr('readonly', true);
               $('#repeatTestingVlCheckValuelt20').attr('disabled', true);
          } else {
               $('#repeatTestingVlValue').attr('readonly', false);
               $('#repeatTestingVlCheckValuelt20').attr('disabled', false);
          }
     });

     $('#suspendTreatmentVlValue').on('input', function(e) {
          if (this.value != '') {
               $('#suspendTreatmentVlCheckValuelt20').attr('disabled', true);
               $('#suspendTreatmentVlCheckValueTnd').attr('disabled', true);
          } else {
               $('#suspendTreatmentVlCheckValuelt20').attr('disabled', false);
               $('#suspendTreatmentVlCheckValueTnd').attr('disabled', false);
          }
     });

     $('#suspendTreatmentVlCheckValuelt20').change(function() {
          if ($('#suspendTreatmentVlCheckValuelt20').is(':checked')) {
               $('#suspendTreatmentVlValue').attr('readonly', true);
               $('#suspendTreatmentVlCheckValueTnd').attr('disabled', true);
          } else {
               $('#suspendTreatmentVlValue').attr('readonly', false);
               $('#suspendTreatmentVlCheckValueTnd').attr('disabled', false);
          }
     });

     $('#suspendTreatmentVlCheckValueTnd').change(function() {
          if ($('#suspendTreatmentVlCheckValueTnd').is(':checked')) {
               $('#suspendTreatmentVlValue').attr('readonly', true);
               $('#suspendTreatmentVlCheckValuelt20').attr('disabled', true);
          } else {
               $('#suspendTreatmentVlValue').attr('readonly', false);
               $('#suspendTreatmentVlCheckValuelt20').attr('disabled', false);
          }
     });

     $(".labSection").on("change", function() {
          if ($.trim(result) != '') {
               if ($(".labSection").serialize() == $(__clone).serialize()) {
                    $(".reasonForResultChanges").css("display", "none");
                    $("#reasonForResultChanges").removeClass("isRequired");
               } else {
                    $(".reasonForResultChanges").css("display", "block");
                    $("#reasonForResultChanges").addClass("isRequired");
               }
          }
     });

     function checkRejectionReason() {
          var rejectionReason = $("#rejectionReason").val();
          if (rejectionReason == "other") {
               $("#newRejectionReason").show();
               $("#newRejectionReason").addClass("isRequired");
          } else {
               $("#newRejectionReason").hide();
               $("#newRejectionReason").removeClass("isRequired");
               $('#newRejectionReason').val("");
          }
     }

     function calculateLogValue(obj) {
          if (obj.id == "vlResult") {
               absValue = $("#vlResult").val();
               absValue = Number.parseFloat(absValue).toFixed();
               if (absValue != '' && absValue != 0 && !isNaN(absValue)) {
                    //$("#vlResult").val(absValue);
                    $("#vlLog").val(Math.round(Math.log10(absValue) * 100) / 100);
               } else {
                    $("#vlLog").val('');
               }
          }
          if (obj.id == "vlLog") {
               logValue = $("#vlLog").val();
               if (logValue != '' && logValue != 0 && !isNaN(logValue)) {
                    var absVal = Math.round(Math.pow(10, logValue) * 100) / 100;
                    if (absVal != 'Infinity' && !isNaN(absVal)) {
                         $("#vlResult").val(Math.round(Math.pow(10, logValue) * 100) / 100);
                    }
               } else {
                    $("#vlResult").val('');
               }
          }
     }

     function validateNow() {
          var ARTlength = $("#artNo").val();
          if (ARTlength.length < 10) {
               alert("<?= _("Patient ART No. should be at least 10 characters long"); ?>");
               //return false;
          }
          flag = deforayValidator.init({
               formId: 'vlRequestFormCameroon'
          });

          $('.isRequired').each(function() {
               ($(this).val() == '') ? $(this).css('background-color', '#FFFF99'): $(this).css('background-color', '#FFFFFF')
          });
          var userType = "<?php echo $sarr['sc_user_type']; ?>";
          if (userType != 'remoteuser') {
               if ($.trim($("#dob").val()) == '' && $.trim($("#ageInYears").val()) == '' && $.trim($("#ageInMonths").val()) == '') {
                    alert("Please make sure to enter DOB or Age");
                    return false;
               }
          }
          if (flag) {
               $.blockUI();
               document.getElementById('vlRequestFormCameroon').submit();
          }
     }
</script>