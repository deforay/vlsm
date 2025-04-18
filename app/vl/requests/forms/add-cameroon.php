<?php

use App\Utilities\DateUtility;
use App\Services\CommonService;
use App\Services\DatabaseService;
use App\Services\FacilitiesService;
use App\Registries\ContainerRegistry;

/** @var DatabaseService $db */
$db = ContainerRegistry::get(DatabaseService::class);

/** @var CommonService $general */
$general = ContainerRegistry::get(CommonService::class);
$cpyReq = $general->getGlobalConfig('vl_copy_request_save_and_next');
$arr = $general->getGlobalConfig();

if ($arr['sample_code'] == 'auto' || $arr['sample_code'] == 'alphanumeric' || $arr['sample_code'] == 'MMYY' || $arr['sample_code'] == 'YY') {
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
$rKey = '';

if ($general->isSTSInstance()) {
     $sampleCodeKey = 'remote_sample_code_key';
     $sampleCode = 'remote_sample_code';
     $rKey = 'R';
} else {
     $sampleCodeKey = 'sample_code_key';
     $sampleCode = 'sample_code';
     $rKey = '';
}


/** @var FacilitiesService $facilitiesService */
$facilitiesService = ContainerRegistry::get(FacilitiesService::class);

$lResult = $facilitiesService->getTestingLabs('vl', byPassFacilityMap: true, allColumns: true);
$province = $general->getUserMappedProvinces($_SESSION['facilityMap']);
$facility = $general->generateSelectOptions($healthFacilities, null, '<?= _translate("-- Select --"); ?>');
$testReasonsResultDetails = $general->getDataByTableAndFields("r_vl_test_reasons", array('test_reason_id', 'test_reason_name', 'parent_reason'), false, " test_reason_status like 'active' ");
$subTestReasons = $testReasonsResult = [];
foreach ($testReasonsResultDetails as $row) {
     if ($row['parent_reason'] == 0) {
          $testReasonsResult[$row['test_reason_id']] = $row['test_reason_name'];
     } else {
          $subTestReasons[$row['parent_reason']][$row['test_reason_id']] = $row['test_reason_name'];
     }
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
          <h1><em class="fa-solid fa-pen-to-square"></em> <?= _translate('VIRAL LOAD LABORATORY REQUEST FORM'); ?> </h1>
          <ol class="breadcrumb">
               <li><a href="/dashboard/index.php"><em class="fa-solid fa-chart-pie"></em> <?= _translate('Home'); ?></a></li>
               <li class="active"><?= _translate('Add VL Request'); ?></li>
          </ol>
     </section>
     <!-- Main content -->
     <section class="content">
          <div class="box box-default">
               <div class="box-header with-border">
                    <div class="pull-right" style="font-size:15px;"><span class="mandatory">*</span> <?= _translate('indicates required fields'); ?> &nbsp;</div>
               </div>
               <div class="box-body">
                    <!-- form start -->
                    <form class="form-inline" method="post" name="vlRequestFormCameroon" id="vlRequestFormCameroon" autocomplete="off" action="addVlRequestHelper.php">
                         <div class="box-body">
                              <div class="box box-primary">
                                   <div class="box-header with-border">
                                        <h3 class="box-title"><?= _translate('Clinic Information: (To be filled by requesting Clinican/Nurse)'); ?></h3>
                                   </div>
                                   <div class="box-body">
                                        <div class="row">
                                             <div class="col-xs-3 col-md-3">
                                                  <div class="">
                                                       <?php if ($general->isSTSInstance()) { ?>
                                                            <label for="sampleCode"><?= _translate('Sample ID'); ?> </label><br>
                                                            <span id="sampleCodeInText" style="width:100%;border-bottom:1px solid #333;"></span>
                                                            <input type="hidden" class="<?php echo $sampleClass; ?>" id="sampleCode" name="sampleCode" />
                                                       <?php } else { ?>
                                                            <label for="sampleCode"><?= _translate('Sample ID'); ?> <span class="mandatory">*</span></label>
                                                            <input type="text" class="form-control isRequired <?php echo $sampleClass; ?>" id="sampleCode" name="sampleCode" readonly="readonly" <?php echo $maxLength; ?> placeholder="<?= _translate('Enter Sample ID'); ?>" title="<?= _translate("Please make sure you have selected Sample Collection Date and Requesting Facility"); ?>" style="width:100%;" onblur="checkSampleNameValidation('form_vl','<?php echo $sampleCode; ?>',this.id,null,'This sample id already exists. Try another',null)" />
                                                       <?php } ?>
                                                  </div>
                                             </div>
                                             <div class="col-xs-3 col-md-3">
                                                  <div class="">
                                                       <label for="sampleReordered">
                                                            <input type="checkbox" class="" id="sampleReordered" name="sampleReordered" value="yes" title="<?= _translate('Please indicate if this is a reordered sample'); ?>"> <?= _translate('Sample Reordered'); ?>
                                                       </label>
                                                  </div>
                                             </div>
                                             <!-- BARCODESTUFF START -->
                                             <?php if (isset($global['bar_code_printing']) && $global['bar_code_printing'] != "off") { ?>
                                                  <div class="col-xs-3 col-md-3 pull-right">
                                                       <div class="">
                                                            <label for="printBarCode"><?= _translate('Print Barcode Label'); ?> <span class="mandatory">*</span> </label>
                                                            <input type="checkbox" class="" id="printBarCode" name="printBarCode" checked />
                                                       </div>
                                                  </div>
                                             <?php } ?>
                                             <!-- BARCODESTUFF END -->
                                        </div>
                                        <div class="row">
                                             <div class="col-xs-3 col-md-3">
                                                  <div class="">
                                                       <label for="province"><?= _translate('Region'); ?> <span class="mandatory">*</span></label>
                                                       <select class="form-control isRequired" name="province" id="province" title="<?= _translate('Please select a province'); ?>" style="width:100%;" onchange="getfacilityDetails(this);">
                                                            <?php echo $province; ?>
                                                       </select>
                                                  </div>
                                             </div>
                                             <div class="col-xs-3 col-md-3">
                                                  <div class="">
                                                       <label for="district"><?= _translate('District'); ?> <span class="mandatory">*</span></label>
                                                       <select class="form-control isRequired" name="district" id="district" title="<?= _translate('Please select a district'); ?>" style="width:100%;" onchange="getfacilityDistrictwise(this);">
                                                            <option value=""> <?= _translate('-- Select --'); ?> </option>
                                                       </select>
                                                  </div>
                                             </div>
                                             <div class="col-xs-3 col-md-3">
                                                  <div class="">
                                                       <label for="facilityId"><?= _translate('Facility'); ?> <span class="mandatory">*</span></label>
                                                       <select class="form-control isRequired" id="facilityId" name="facilityId" title="<?= _translate('Please select a clinic/health center name'); ?>" style="width:100%;" onchange="getfacilityProvinceDetails(this),fillFacilityDetails();">
                                                            <option value=""> <?= _translate('-- Select --'); ?> </option>
                                                            <?php foreach ($healthFacilitiesAllColumns as $hFacility) { ?>
                                                                 <option value="<?php echo $hFacility['facility_id']; ?>" data-code="<?php echo $hFacility['facility_code']; ?>" <?php echo (isset($_SESSION['vlData']['facility_id']) && $_SESSION['vlData']['facility_id'] == $hFacility['facility_id']) ? 'selected="selected"' : ''; ?>><?php echo $hFacility['facility_name']; ?></option>
                                                            <?php } ?>
                                                       </select>
                                                  </div>
                                             </div>
                                             <div class="col-xs-3 col-md-3">
                                                  <div class="">
                                                       <label for="facilityCode"><?= _translate('Facility code'); ?> </label>
                                                       <input type="text" class="form-control" style="width:100%;" name="facilityCode" id="facilityCode" placeholder="<?= _translate('Clinic/Health Center Code'); ?>" title="<?= _translate('Please enter clinic/health center code'); ?>">
                                                  </div>
                                             </div>
                                        </div>
                                        <div class="row facilityDetails" style="display:none;">
                                             <div class="col-xs-2 col-md-2 femails" style="display:none;"><strong><?= _translate('Clinic Email(s) -'); ?></strong></div>
                                             <div class="col-xs-2 col-md-2 femails facilityEmails" style="display:none;"></div>
                                             <div class="col-xs-2 col-md-2 fmobileNumbers" style="display:none;"><strong><?= _translate('Clinic Mobile No.(s) -'); ?></strong></div>
                                             <div class="col-xs-2 col-md-2 fmobileNumbers facilityMobileNumbers" style="display:none;"></div>
                                             <div class="col-xs-2 col-md-2 fContactPerson" style="display:none;"><strong><?= _translate('Clinic Contact Person -'); ?></strong></div>
                                             <div class="col-xs-2 col-md-2 fContactPerson facilityContactPerson" style="display:none;"></div>
                                        </div>
                                        <div class="row">
                                             <div class="col-xs-3 col-md-3">
                                                  <div class="">
                                                       <label for="fundingSource">Project Name</label>
                                                       <select class="form-control" name="fundingSource" id="fundingSource" title="Please choose implementing partner" style="width:100%;">
                                                            <option value=""> -- Select -- </option>
                                                            <?php
                                                            foreach ($fundingSourceList as $fundingSource) {
                                                            ?>
                                                                 <option value="<?php echo base64_encode((string) $fundingSource['funding_source_id']); ?>" <?php echo (isset($_SESSION['vlData']['funding_source']) && $_SESSION['vlData']['funding_source'] == $fundingSource['funding_source_id']) ? 'selected="selected"' : ''; ?>><?= $fundingSource['funding_source_name']; ?></option>
                                                            <?php } ?>
                                                       </select>
                                                  </div>
                                             </div>
                                             <div class="col-xs-3 col-md-3">
                                                  <div class="form-group">
                                                       <label for="implementingPartner"><?= _translate("Implementing Partner"); ?></label>
                                                       <select class="form-control" name="implementingPartner" id="implementingPartner" title="<?= _translate('Please choose implementing partner'); ?>" style="width:100%;">
                                                            <option value=""> <?= _translate('-- Select --'); ?> </option>
                                                            <?php
                                                            foreach ($implementingPartnerList as $implementingPartner) {
                                                            ?>
                                                                 <option value="<?php echo base64_encode((string) $implementingPartner['i_partner_id']); ?>" <?php echo (isset($_SESSION['vlData']['implementing_partner']) && $_SESSION['vlData']['implementing_partner'] == $implementingPartner['i_partner_id']) ? 'selected="selected"' : ''; ?>><?= $implementingPartner['i_partner_name']; ?></option>
                                                            <?php } ?>
                                                       </select>
                                                  </div>
                                             </div>
                                             <div class="col-xs-3 col-md-3">
                                                  <label for="labId">Testing Lab <span class="mandatory">*</span></label>
                                                  <select name="labId" id="labId" class="select2 form-control isRequired" title="Please choose lab" style="width:100%;">
                                                       <option value="">-- Select --</option>
                                                       <?php foreach ($lResult as $labName) { ?>
                                                            <option data-focalperson="<?php echo $labName['contact_person']; ?>" data-focalphone="<?php echo $labName['facility_mobile_numbers']; ?>" value="<?php echo $labName['facility_id']; ?>" <?php echo (isset($_SESSION['vlData']['lab_id']) && $_SESSION['vlData']['lab_id'] == $labName['facility_id']) ? 'selected="selected"' : ''; ?>><?= $labName['facility_name']; ?></option>
                                                       <?php } ?>
                                                  </select>
                                             </div>
                                        </div>
                                        <?php if ($general->isLISInstance()) { ?>
                                             <div class="row">
                                                  <div class="col-xs-3 col-md-3">
                                                       <label for="labAssignedCode"><?= _translate('Lab Assigned Code'); ?> </label>
                                                       <input name="labAssignedCode" value="<?php echo $_SESSION['vlData']['lab_assigned_code'] ?? null; ?>" id="labAssignedCode" class="form-control" placeholder="<?= _translate('Enter Lab Assigned Code'); ?>" title="<?= _translate('Please enter Lab Assigned Code'); ?>" onblur='checkNameValidation("form_vl","lab_assigned_code",this,null,"<?php echo _translate("The Lab Assigned Code that you entered already exists.Enter another Lab Assigned Code"); ?>",null)'>
                                                  </div>
                                             </div>
                                        <?php } ?>
                                   </div>
                              </div>
                              <div class="box box-primary">
                                   <div class="box-header with-border">
                                        <h3 class="box-title"><?= _translate('Patient Information'); ?></h3>&nbsp;&nbsp;&nbsp;
                                        <input style="width:30%;" type="text" name="artPatientNo" id="artPatientNo" class="" placeholder="<?= _translate('Enter Unique ART Number or Patient Name'); ?>" title="<?= _translate('Enter art number or patient name'); ?>" />&nbsp;&nbsp;
                                        <a style="margin-top:-0.35%;" href="javascript:void(0);" class="btn btn-default btn-sm" onclick="showPatientList();"><em class="fa-solid fa-magnifying-glass"></em>Search</a><span id="showEmptyResult" style="display:none;color: #ff0000;font-size: 15px;"><strong>&nbsp;<?= _translate('No Patient Found'); ?></strong></span>
                                   </div>

                                   <div class="row">

                                        <div class="col-md-12 encryptPIIContainer">
                                             <label class="col-lg-5 control-label" for="encryptPII"><?= _translate('Patient is from Defence Forces (Patient Name and Patient ID will not be synced between LIS and STS)'); ?> <span class="mandatory">*</span></label>
                                             <div class="col-lg-5">
                                                  <select name="encryptPII" id="encryptPII" class="form-control" title="<?= _translate('Encrypt Patient Identifying Information'); ?>">
                                                       <option value=""><?= _translate('--Select--'); ?></option>
                                                       <option value="no" selected='selected'><?= _translate('No'); ?></option>
                                                       <option value="yes"><?= _translate('Yes'); ?></option>
                                                  </select>
                                             </div>
                                        </div>
                                   </div>

                                   <div class="box-body">
                                        <div class="row">
                                             <div class="col-xs-3 col-md-3">
                                                  <div class="form-group">
                                                       <label for="artNo"><?= _translate('Patient ID'); ?> <span class="mandatory">*</span></label>
                                                       <input type="text" name="artNo" id="artNo" class="form-control isRequired patientId" placeholder="<?= _translate('Enter ART Number'); ?>" title="<?= _translate('Enter art number'); ?>" onchange="checkPatientDetails('form_vl','patient_art_no',this,null)" />
                                                       <span class="artNoGroup" id="artNoGroup"></span>
                                                  </div>
                                             </div>
                                             <div class="col-xs-3 col-md-3">
                                                  <div class="form-group">
                                                       <label for="dob"><?= _translate('Date of Birth'); ?> <span class='mandatory'>*</span></label>
                                                       <input type="text" name="dob" id="dob" class="form-control date" placeholder="<?= _translate('Enter DOB'); ?>" title="<?= _translate('Enter dob'); ?>" onchange="getAge();checkARTInitiationDate();" />
                                                       <?php if ($general->isLISInstance()) { ?>
                                                            <input type="checkbox" name="ageUnreported" id="ageUnreported" onclick="updateAgeInfo();" /> <label for="dob"><?= _translate('Unreported'); ?> </label>
                                                       <?php } ?>
                                                  </div>
                                             </div>
                                             <div class="col-xs-3 col-md-3">
                                                  <div class="form-group">
                                                       <label for="ageInYears"><?= _translate('If DOB unknown, Age in Year(s)'); ?><span class="mandatory">*</span> </label>
                                                       <input type="text" name="ageInYears" id="ageInYears" class="form-control forceNumeric" maxlength="2" placeholder="<?= _translate('Age in Year(s)'); ?>" title="<?= _translate('Enter age in years'); ?>" />

                                                  </div>
                                             </div>
                                             <div class="col-xs-3 col-md-3">
                                                  <div class="form-group">
                                                       <label for="ageInMonths"><?= _translate('If Age < 1, Age in Month(s)'); ?> </label> <input type="text" name="ageInMonths" id="ageInMonths" class="form-control forceNumeric" maxlength="2" placeholder="<?= _translate('Age in Month(s)'); ?>" title="<?= _translate('Enter age in months'); ?>" />
                                                  </div>
                                             </div>
                                        </div>
                                        <div class="row">
                                             <div class="col-xs-3 col-md-3">
                                                  <div class="form-group">
                                                       <label for="patientFirstName"><?= _translate('Patient First Name'); ?> </label>
                                                       <input type="text" name="patientFirstName" id="patientFirstName" class="form-control" placeholder="<?= _translate('Enter Patient First Name'); ?>" title="<?= _translate('Enter patient First name'); ?>" />
                                                  </div>
                                             </div>
                                             <div class="col-xs-3 col-md-3">
                                                  <div class="form-group">
                                                       <label for="patientLastName"><?= _translate('Patient Last Name'); ?> </label>
                                                       <input type="text" name="patientLastName" id="patientLastName" class="form-control" placeholder="<?= _translate('Enter Patient Last Name'); ?>" title="<?= _translate('Enter patient last name'); ?>" />
                                                  </div>
                                             </div>
                                             <div class="col-xs-3 col-md-3">
                                                  <div class="form-group">
                                                       <label for="gender"><?= _translate('Sex'); ?> <span class="mandatory">*</span></label><br>
                                                       <label class="radio-inline" style="margin-left:0px;">
                                                            <input type="radio" class="isRequired" id="genderMale" name="gender" value="male" title="<?= _translate('Please select sex'); ?>"><?= _translate('Male'); ?>
                                                       </label>&nbsp;&nbsp;
                                                       <label class="radio-inline" style="margin-left:0px;">
                                                            <input type="radio" id="genderFemale" name="gender" value="female" title="<?= _translate('Please select sex'); ?>"><?= _translate('Female'); ?>
                                                       </label>&nbsp;&nbsp;
                                                       <label class="radio-inline" style="margin-left:0px;">
                                                            <input type="radio" id="genderUnreported" name="gender" value="unreported" title="<?= _translate('Please select sex'); ?>"><?= _translate('Unreported'); ?>
                                                       </label>
                                                  </div>
                                             </div>
                                             <div class="col-xs-3 col-md-3">
                                                  <div class="form-group">
                                                       <label for="healthInsuranceCode"><?= _translate('Universal Health Coverage'); ?></label>
                                                       <input type="text" name="healthInsuranceCode" id="healthInsuranceCode" class="form-control" placeholder="<?= _translate('Enter Universal Health Coverage'); ?>" title="<?= _translate('Enter Universal Health Coverage'); ?>" maxlength="32" />
                                                  </div>
                                             </div>
                                        </div>
                                        <div class="row">
                                             <div class="col-xs-3 col-md-3">
                                                  <div class="form-group">
                                                       <label for="patientPhoneNumber"><?= _translate('Phone Number'); ?></label>
                                                       <input type="text" name="patientPhoneNumber" id="patientPhoneNumber" class="form-control phone-number" placeholder="<?= _translate('Enter Phone Number'); ?>" maxlength="<?php echo strlen((string) $countryCode) + (int) $maxNumberOfDigits; ?>" title="<?= _translate('Enter phone number'); ?>" />
                                                  </div>
                                             </div>

                                        </div>
                                        <div class="row femaleSection" style="display:none;">
                                             <div class="col-xs-3 col-md-3">
                                                  <div class="form-group">
                                                       <label for="patientPregnant"><?= _translate('Is Patient Pregnant?'); ?> <span class="mandatory">*</span></label><br>
                                                       <label class="radio-inline">
                                                            <input type="radio" class="" id="pregYes" name="patientPregnant" value="yes" title="<?= _translate('Please check if patient is pregnant'); ?>"> <?= _translate('Yes'); ?>
                                                       </label>
                                                       <label class="radio-inline">
                                                            <input type="radio" class="" id="pregNo" name="patientPregnant" value="no"> <?= _translate('No'); ?>
                                                       </label>
                                                  </div>
                                             </div>
                                             <div class="col-xs-3 col-md-3">
                                                  <div class="form-group">
                                                       <label for="patientPregnant"><?= _translate('If Yes, Number of weeks of pregnancy?'); ?> </label>
                                                       <input type="text" class="forceNumeric form-control" id="noOfPregnancyWeeks" name="noOfPregnancyWeeks" title="<?= _translate('Number of weeks of pregnancy'); ?>" placeholder="<?= _translate('Number of weeks of pregnancy'); ?>">
                                                  </div>
                                             </div>
                                             <div class="col-xs-3 col-md-3">
                                                  <div class="form-group">
                                                       <label for="breastfeeding"><?= _translate('Is Patient Breastfeeding?'); ?> <span class="mandatory">*</span></label><br>
                                                       <label class="radio-inline">
                                                            <input type="radio" class="" id="breastfeedingYes" name="breastfeeding" value="yes" title="<?= _translate('Please check if patient is breastfeeding'); ?>"> <?= _translate('Yes'); ?>
                                                       </label>
                                                       <label class="radio-inline">
                                                            <input type="radio" class="" id="breastfeedingNo" name="breastfeeding" value="no"> <?= _translate('No'); ?>
                                                       </label>
                                                  </div>
                                             </div>
                                             <div class="col-xs-3 col-md-3">
                                                  <div class="form-group">
                                                       <label for="patientPregnant"><?= _translate('If Yes, For how many weeks?'); ?> </label>
                                                       <input type="text" class="forceNumeric form-control" id="noOfBreastfeedingWeeks" name="noOfBreastfeedingWeeks" title="<?= _translate('Number of weeks of breastfeeding'); ?>" placeholder="<?= _translate('Number of weeks of breastfeeding'); ?>">
                                                  </div>
                                             </div>
                                        </div>
                                   </div>
                                   <div class="box box-primary">
                                        <div class="box-header with-border">
                                             <h3 class="box-title"><?= _translate('Sample Information'); ?></h3>
                                        </div>
                                        <div class="box-body">
                                             <div class="row">
                                                  <div class="col-md-3">
                                                       <div class="form-group">
                                                            <label for=""><?= _translate('Date of Sample Collection'); ?> <span class="mandatory">*</span></label>
                                                            <input type="text" class="form-control isRequired dateTime" value="<?php if (isset($_SESSION['vlData']['sample_collection_date'])) echo DateUtility::humanReadableDateFormat($_SESSION['vlData']['sample_collection_date'], true); ?>" style="width:100%;" name="sampleCollectionDate" id="sampleCollectionDate" placeholder="<?= _translate('Sample Collection Date'); ?>" title="<?= _translate('Please select sample collection date'); ?>" onchange="generateSampleCode(); checkCollectionDate(this.value);">
                                                            <span class="expiredCollectionDate" style="color:red; display:none;"></span>
                                                       </div>
                                                  </div>
                                                  <div class="col-md-3">
                                                       <div class="form-group">
                                                            <label for="specimenType"><?= _translate('Sample Type'); ?> <span class="mandatory">*</span></label>
                                                            <select name="specimenType" id="specimenType" class="form-control isRequired" title="<?= _translate('Please choose sample type'); ?>">
                                                                 <option value=""> <?= _translate('-- Select --'); ?> </option>
                                                                 <?php
                                                                 $selected = '';
                                                                 if (count($sResult) == 1) {
                                                                      $selected = "selected='selected'";
                                                                 }
                                                                 foreach ($sResult as $name) { ?>
                                                                      <option <?= $selected; ?> value="<?php echo $name['sample_id']; ?>" <?php echo (isset($_SESSION['vlData']['specimen_type']) && $_SESSION['vlData']['specimen_type'] == $name['sample_id']) ? 'selected="selected"' : ''; ?>><?= _translate($name['sample_name']); ?></option>
                                                                 <?php } ?>
                                                            </select>
                                                       </div>
                                                  </div>

                                                  <div class="col-md-3">
                                                       <div class="form-group">
                                                            <label for="reqClinician" class=""><?= _translate('Name Of Requester'); ?> <i class="fa fa-pen-to-square" aria-hidden="true"></i></label>
                                                            <select class="form-control editableSelectClinician" id="reqClinician" value="<?php echo $_SESSION['vlData']['request_clinician_name'] ?? null; ?>" name="reqClinician" title="<?= _translate('Please enter request clinician'); ?>">
                                                                 <option value=""> --Choose Request Clinician--</option>
                                                            </select>
                                                       </div>
                                                  </div>
                                                  <div class="col-md-3">
                                                       <div class="form-group">
                                                            <label for="reqClinicianPhoneNumber" class=""><?= _translate('Contact Number'); ?> </label>
                                                            <input type="text" class="form-control phone-number" value="<?php echo $_SESSION['vlData']['request_clinician_phone_number'] ?? null; ?>" id="reqClinicianPhoneNumber" name="reqClinicianPhoneNumber" maxlength="<?php echo strlen((string) $countryCode) + (int) $maxNumberOfDigits; ?>" placeholder="<?= _translate('Phone Number'); ?>" title="<?= _translate('Please enter request clinician phone number'); ?>" />

                                                       </div>
                                                  </div>
                                             </div>
                                             <?php if ($general->isLISInstance()) { ?>
                                                  <div class="row">
                                                       <div class="col-md-3">
                                                            <div class="form-group">
                                                                 <label class="" for="sampleReceivedDate"><?= _translate('Date Sample Received at Testing Lab'); ?> </label>
                                                                 <input type="text" class="form-control dateTime" value="<?php if (isset($_SESSION['vlData']['sample_received_at_lab_datetime'])) echo DateUtility::humanReadableDateFormat($_SESSION['vlData']['sample_received_at_lab_datetime'], true); ?>" id="sampleReceivedDate" name="sampleReceivedDate" placeholder="<?= _translate('Sample Received Date'); ?>" title="<?= _translate('Please select sample received date'); ?>" />

                                                            </div>
                                                       </div>

                                                  </div>
                                             <?php } ?>
                                             <br>
                                        </div>
                                        <div class="box box-primary">
                                             <div class="box-header with-border">
                                                  <h3 class="box-title"><?= _translate('Treatment Information'); ?></h3>
                                             </div>
                                             <div class="box-body">
                                                  <div class="row">
                                                       <div class="col-xs-3 col-md-3">
                                                            <div class="form-group">
                                                                 <label for=""><?= _translate('Treatment Start Date'); ?></label>
                                                                 <input type="text" class="form-control date" name="dateOfArtInitiation" id="dateOfArtInitiation" placeholder="<?= _translate('Treatment Start Date'); ?>" title="<?= _translate('Treatment Start Date'); ?>" style="width:100%;" onchange="checkARTInitiationDate();">
                                                            </div>
                                                       </div>

                                                       <div class="col-xs-3 col-md-3">
                                                            <div class="form-group">
                                                                 <label for=""> <?= _translate('Current ARV Protocol'); ?></label>
                                                                 <select class="select2 form-control" id="artRegimen" name="artRegimen" title="<?= _translate('Please choose ART Regimen'); ?>" style="width:100%;" onchange="checkARTRegimenValue(); getTreatmentLine(this.value)">
                                                                      <option value=""><?= _translate('-- Select --'); ?></option>
                                                                      <?php foreach ($artRegimenResult as $heading) { ?>
                                                                           <optgroup label="<?= $heading['headings']; ?>">
                                                                                <?php
                                                                                foreach ($aResult as $regimen) {
                                                                                     if ($heading['headings'] == $regimen['headings']) {
                                                                                ?>
                                                                                          <option value="<?php echo $regimen['art_code']; ?>"><?php echo $regimen['art_code']; ?></option>
                                                                                <?php
                                                                                     }
                                                                                }
                                                                                ?>
                                                                           </optgroup>
                                                                      <?php } ?>
                                                                 </select>
                                                            </div>
                                                       </div>
                                                       <div class="col-xs-3 col-md-3">
                                                            <div class="">
                                                                 <label for="lineOfTreatment" class="labels"><?= _translate('Line of Treatment'); ?> </label>
                                                                 <select class="form-control" name="lineOfTreatment" id="lineOfTreatment" title="<?= _translate('Line Of Treatment'); ?>">
                                                                      <option value=""><?= _translate('--Select--'); ?></option>
                                                                      <option value="1"><?= _translate('1st Line'); ?></option>
                                                                      <option value="2"><?= _translate('2nd Line'); ?></option>
                                                                      <option value="3"><?= _translate('3rd Line'); ?></option>
                                                                      <option value="n/a"><?= _translate('N/A'); ?></option>
                                                                 </select>
                                                            </div>
                                                       </div>
                                                  </div>
                                             </div>
                                             <div class="box box-primary">
                                                  <div class="box-header with-border">
                                                       <h3 class="box-title"><?= _translate('Reason for Viral Load Testing'); ?> <span class="mandatory">*</span></h3><small> <?= _translate('(Please pick one): (To be completed by clinician)'); ?></small>
                                                  </div>
                                                  <div class="box-body">
                                                       <?php if (isset($testReasonsResult) && !empty($testReasonsResult)) {
                                                            foreach ($testReasonsResult as $key => $title) { ?>
                                                                 <div class="row">
                                                                      <div class="col-md-6">
                                                                           <div class="form-group">
                                                                                <div class="col-lg-12">
                                                                                     <label class="radio-inline">
                                                                                          <input type="radio" class="isRequired" id="rmTesting<?php echo $key; ?>" name="reasonForVLTesting" value="<?php echo $key; ?>" title="<?= _translate('Please check viral load indication testing type'); ?>" onclick="showTesting('rmTesting<?php echo $key; ?>', <?php echo $key; ?>);">
                                                                                          <strong><?= _translate($title); ?></strong>
                                                                                     </label>
                                                                                </div>
                                                                           </div>
                                                                      </div>
                                                                 </div>
                                                                 <?php if ($key == 5) { ?>
                                                                      <div class="row rmTesting5 hideTestData well" style="display:none;">
                                                                           <div class="col-md-6">
                                                                                <label class="col-lg-5 control-label"><?= _translate('Please specify other reasons'); ?></label>
                                                                                <div class="col-lg-7">
                                                                                     <input type="text" class="form-control" id="newreasonForVLTesting" name="newreasonForVLTesting" placeholder="<?= _translate('Please specify other test reason') ?>" title="<?= _translate('Please specify other test reason') ?>" />
                                                                                </div>
                                                                           </div>
                                                                      </div>
                                                                 <?php } ?>
                                                                 <?php if (isset($subTestReasons[$key]) && !empty($subTestReasons[$key])) { ?>
                                                                      <div class="row rmTesting<?php echo $key; ?> hideTestData well" style="display:none;">
                                                                           <div class="col-md-6">
                                                                                <label class="col-lg-5 control-label"><?= _translate('Choose reason for testing'); ?></label>
                                                                                <div class="col-lg-7">
                                                                                     <select name="controlVlTestingType[<?php echo $key; ?>]" id="controlVlType<?php echo $key; ?>" class="form-control controlVlTypeFields" title="<?= _translate('Please choose a reason for VL testing'); ?>" onchange="checkreasonForVLTesting();">
                                                                                          <option value=""> <?= _translate("-- Select --"); ?> </option>
                                                                                          <?php foreach ($subTestReasons[$key] as $testReasonId => $row) { ?>
                                                                                               <option value="<?php echo $testReasonId; ?>"><?php echo _translate($row); ?></option>
                                                                                          <?php } ?>
                                                                                     </select>
                                                                                </div>
                                                                           </div>
                                                                      </div>
                                                       <?php
                                                                 }
                                                            }
                                                       } ?>
                                                       <hr>
                                                  </div>
                                             </div>

                                             <?php if (_isAllowed('/vl/results/updateVlTestResult.php') && $_SESSION['accessType'] != 'collection-site') { ?>
                                                  <div class="box box-primary">
                                                       <div class="box-header with-border">
                                                            <h3 class="box-title"><?= _translate('Laboratory Information'); ?></h3>
                                                       </div>
                                                       <div class="box-body">
                                                            <div class="row">
                                                                 <div class="col-md-6">
                                                                      <label for="cvNumber" class="col-lg-5 control-label"><?= _translate('CV Number'); ?> </label>
                                                                      <div class="col-lg-7">
                                                                           <input name="cvNumber" id="cvNumber" class="form-control" placeholder="<?= _translate('Enter CV Number'); ?>" title="<?= _translate('Please enter CV Number'); ?>">
                                                                      </div>
                                                                 </div>
                                                                 <div class="col-md-6">
                                                                      <label for="testingPlatform" class="col-lg-5 control-label"><?= _translate('VL Testing Platform'); ?> </label>
                                                                      <div class="col-lg-7">
                                                                           <select name="testingPlatform" id="testingPlatform" class="form-control" title="<?= _translate('Please choose VL Testing Platform'); ?>" <?php echo $labFieldDisabled; ?> onchange="hivDetectionChange();">
                                                                                <option value=""><?= _translate('-- Select --'); ?></option>
                                                                                <?php foreach ($importResult as $mName) { ?>
                                                                                     <option value="<?php echo $mName['machine_name'] . '##' . $mName['lower_limit'] . '##' . $mName['higher_limit'] . '##' . $mName['instrument_id']; ?>"><?php echo $mName['machine_name']; ?></option>
                                                                                <?php } ?>
                                                                           </select>
                                                                      </div>
                                                                 </div>
                                                                 <!-- <div class="col-md-6">
                                                                      <label for="serialNo" class="col-lg-5 control-label"><?= _translate('Lab Sample Code'); ?> </label>
                                                                      <div class="col-lg-7">
                                                                           <input name="serialNo" id="serialNo" class="form-control" placeholder="<?= _translate('Enter Lab Sample Code'); ?>" title="<?= _translate('Please enter Lab Sample Code'); ?>">
                                                                      </div>
                                                                 </div> -->
                                                            </div>

                                                            <div class="row">
                                                                 <div class="col-md-6">
                                                                      <label class="col-lg-5 control-label" for="sampleTestingDateAtLab"><?= _translate('Sample Testing Date'); ?> </label>
                                                                      <div class="col-lg-7">
                                                                           <input type="text" class="form-control dateTime" id="sampleTestingDateAtLab" name="sampleTestingDateAtLab" placeholder="<?= _translate('Sample Testing Date'); ?>" title="<?= _translate('Please select sample testing date'); ?>" <?php echo $labFieldDisabled; ?> onchange="checkSampleTestingDate();" />
                                                                      </div>
                                                                 </div>
                                                                 <div class="col-md-6">
                                                                      <label class="col-lg-5 control-label" for="isSampleRejected"><?= _translate('Is Sample Rejected?'); ?> </label>
                                                                      <div class="col-lg-7">
                                                                           <select name="isSampleRejected" id="isSampleRejected" class="form-control" title="<?= _translate('Please check if sample is rejected or not'); ?>">
                                                                                <option value=""><?= _translate('-- Select --'); ?></option>
                                                                                <option value="yes"> <?= _translate('Yes'); ?> </option>
                                                                                <option value="no"> <?= _translate('No'); ?> </option>
                                                                           </select>
                                                                      </div>
                                                                 </div>
                                                            </div>
                                                            <div class="row rejectionReason" style="display:none;">
                                                                 <div class="col-md-6 rejectionReason" style="display:none;">
                                                                      <label class="col-lg-5 control-label" for="rejectionReason"><?= _translate('Rejection Reason'); ?> </label>
                                                                      <div class="col-lg-7">
                                                                           <select name="rejectionReason" id="rejectionReason" class="form-control" title="<?= _translate('Please choose reason'); ?>" <?php echo $labFieldDisabled; ?> onchange="checkRejectionReason();">
                                                                                <option value=""><?= _translate('-- Select --'); ?></option>
                                                                                <?php foreach ($rejectionTypeResult as $type) { ?>
                                                                                     <optgroup label="<?php echo strtoupper((string) $type['rejection_type']); ?>">
                                                                                          <?php foreach ($rejectionResult as $reject) {
                                                                                               if ($type['rejection_type'] == $reject['rejection_type']) {
                                                                                          ?>
                                                                                                    <option value="<?php echo $reject['rejection_reason_id']; ?>"><?= $reject['rejection_reason_name']; ?></option>
                                                                                          <?php }
                                                                                          } ?>
                                                                                     </optgroup>
                                                                                <?php } ?>

                                                                           </select>
                                                                      </div>
                                                                 </div>
                                                                 <div class="col-md-6 rejectionReason" style="display:none;">
                                                                      <label class="col-lg-5 control-label labels" for="rejectionDate"><?= _translate('Rejection Date'); ?> </label>
                                                                      <div class="col-lg-7">
                                                                           <input class="form-control date rejection-date" type="text" name="rejectionDate" id="rejectionDate" placeholder="<?= _translate('Select Rejection Date'); ?>" title="<?= _translate('Please select rejection date'); ?>" />
                                                                      </div>
                                                                 </div>
                                                            </div>

                                                            <div class="row">
                                                                 <div class="col-md-6 vlResult">
                                                                      <label class="col-lg-5 control-label" for="vlResult"><?= _translate('Viral Load Result (copies/ml)'); ?> </label>
                                                                      <div class="col-lg-7 resultInputContainer">
                                                                           <input list="possibleVlResults" autocomplete="off" class="form-control" id="vlResult" name="vlResult" placeholder="<?= _translate('Viral Load Result'); ?>" title="<?= _translate('Please enter viral load result'); ?>" <?php echo $labFieldDisabled; ?> style="width:100%;" onchange="calculateLogValue(this)" disabled />
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
                                                                 <div class="col-md-6 vlResult">
                                                                      <label class="col-lg-5 control-label" for="vlLog"><?= _translate('Viral Load (Log)'); ?> </label>
                                                                      <div class="col-lg-7">
                                                                           <input type="text" class="form-control" id="vlLog" name="vlLog" placeholder="<?= _translate('Viral Load Log'); ?>" title="<?= _translate('Please enter viral load log'); ?>" <?php echo $labFieldDisabled; ?> style="width:100%;" onchange="calculateLogValue(this);" />
                                                                      </div>
                                                                 </div>
                                                            </div>
                                                            <div class="row">
                                                                 <div class="col-md-6">
                                                                      <label class="col-lg-5 control-label" for="reviewedOn"><?= _translate('Reviewed On'); ?> </label>
                                                                      <div class="col-lg-7">
                                                                           <input type="text" name="reviewedOn" id="reviewedOn" class="dateTime form-control" placeholder="<?= _translate('Reviewed on'); ?>" title="<?= _translate('Please enter the Reviewed on'); ?>" />
                                                                      </div>
                                                                 </div>
                                                                 <div class="col-md-6">
                                                                      <label class="col-lg-5 control-label" for="reviewedBy"><?= _translate('Reviewed By'); ?> </label>
                                                                      <div class="col-lg-7">
                                                                           <select name="reviewedBy" id="reviewedBy" class="select2 form-control" title="<?= _translate('Please choose reviewed by'); ?>" style="width: 100%;">
                                                                                <?= $general->generateSelectOptions($userInfo, null, '<?= _translate("-- Select --"); ?>'); ?>
                                                                           </select>
                                                                      </div>
                                                                 </div>
                                                            </div>
                                                            <div class="row">
                                                                 <div class="col-md-6">
                                                                      <label class="col-lg-5 control-label" for="approvedOnDateTime"><?= _translate('Approved On'); ?> </label>
                                                                      <div class="col-lg-7">
                                                                           <input type="text" name="approvedOnDateTime" id="approvedOnDateTime" class="dateTime form-control" placeholder="<?= _translate('Approved on'); ?>" title="<?= _translate('Please enter the Approved on'); ?>" />
                                                                      </div>
                                                                 </div>
                                                                 <div class="col-md-6">
                                                                      <label class="col-lg-5 control-label" for="approvedBy"><?= _translate('Approved By'); ?> </label>
                                                                      <div class="col-lg-7">
                                                                           <select name="approvedBy" id="approvedBy" class="form-control" title="<?= _translate('Please choose approved by'); ?>" <?php echo $labFieldDisabled; ?>>
                                                                                <option value=""><?= _translate('-- Select --'); ?></option>
                                                                                <?php foreach ($userResult as $uName) { ?>
                                                                                     <option value="<?php echo $uName['user_id']; ?>"><?php echo ($uName['user_name']); ?></option>
                                                                                <?php } ?>
                                                                           </select>
                                                                      </div>
                                                                 </div>
                                                            </div>
                                                            <div class="row">

                                                                 <div class="col-md-6">
                                                                      <label class="col-lg-5 control-label" for="resultDispatchedOn"><?= _translate('Date Results Dispatched'); ?> </label>
                                                                      <div class="col-lg-7">
                                                                           <input type="text" class="form-control dateTime" id="resultDispatchedOn" name="resultDispatchedOn" placeholder="<?= _translate('Result Dispatched Date'); ?>" title="<?= _translate('Please select result dispatched date'); ?>" <?php echo $labFieldDisabled; ?> />
                                                                      </div>
                                                                 </div>
                                                                 <div class="col-md-6">
                                                                      <label class="col-lg-5 control-label" for="testedBy"><?= _translate('Tested By'); ?> </label>
                                                                      <div class="col-lg-7">
                                                                           <select name="testedBy" id="testedBy" class="select2 form-control" title="Please choose tested by" style="width: 100%;">
                                                                                <?= $general->generateSelectOptions($userInfo, null, '-- Select --'); ?>
                                                                           </select>
                                                                      </div>
                                                                 </div>
                                                            </div>
                                                            <div class="row">
                                                                 <div class="col-md-6">
                                                                      <label class="col-lg-5 control-label" for="labComments"><?= _translate('Lab Tech. Comments'); ?> </label>
                                                                      <div class="col-lg-7">
                                                                           <textarea class="form-control" name="labComments" id="labComments" placeholder="<?= _translate('Lab comments'); ?>" <?php echo $labFieldDisabled; ?>></textarea>
                                                                      </div>
                                                                 </div>
                                                            </div>

                                                       </div>
                                                  </div>
                                             <?php }
                                             ?>
                                        </div>
                                        <div class="box-footer">
                                             <!-- BARCODESTUFF START -->
                                             <?php if (isset($global['bar_code_printing']) && $global['bar_code_printing'] == 'zebra-printer') { ?>
                                                  <div id="printer_data_loading" style="display:none"><span id="loading_message"><?= _translate('Loading Printer Details...'); ?></span><br />
                                                       <div class="progress" style="width:100%">
                                                            <div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%">
                                                            </div>
                                                       </div>
                                                  </div> <!-- /printer_data_loading -->
                                                  <div id="printer_details" style="display:none">
                                                       <span id="selected_printer"><?= _translate('No printer selected!'); ?></span>
                                                       <button type="button" class="btn btn-success" onclick="changePrinter()"><?= _translate('Change/Retry'); ?></button>
                                                  </div><br /> <!-- /printer_details -->
                                                  <div id="printer_select" style="display:none">
                                                       <?= _translate('Zebra Printer Options'); ?><br />
                                                       <?= _translate('Printer:'); ?> <select id="printers"></select>
                                                  </div> <!-- /printer_select -->
                                             <?php } ?>
                                             <!-- BARCODESTUFF END -->
                                             <a class="btn btn-primary btn-disabled" href="javascript:void(0);" onclick="validateNow('save');return false;"><?= _translate('Save'); ?></a>
                                             <input type="hidden" name="saveNext" id="saveNext" />
                                             <?php if ($arr['sample_code'] == 'auto' || $arr['sample_code'] == 'YY' || $arr['sample_code'] == 'MMYY') { ?>
                                                  <input type="hidden" name="sampleCodeFormat" id="sampleCodeFormat" value="" />
                                                  <input type="hidden" name="sampleCodeKey" id="sampleCodeKey" value="" />
                                             <?php } ?>
                                             <input type="hidden" name="vlSampleId" id="vlSampleId" value="" />
                                             <input type="hidden" name="provinceId" id="provinceId" />
                                             <a class="btn btn-primary btn-disabled" href="javascript:void(0);" onclick="validateNow('next');return false;"><?= _translate('Save and Next'); ?></a>
                                             <a href="/vl/requests/vl-requests.php" class="btn btn-default"> <?= _translate('Cancel'); ?></a>
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
          <script src="/uploads/barcode-formats/dymo-format.js"></script>
          <script src="/assets/js/dymo-print.js"></script>
     <?php
     } else if ($global['bar_code_printing'] == 'zebra-printer') {
     ?>
          <script src="/assets/js/zebra-browserprint.js.js"></script>
          <script src="/uploads/barcode-formats/zebra-format.js"></script>
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
          editableSelectClinician('reqClinician', 'request_clinician_name', 'form_vl');

          //Utilities.autoSelectSingleOption('facilityId');
          Utilities.autoSelectSingleOption('specimenType');
          $("#sampleCollectionDate").trigger('change');

          $("#artNo").on('input', function() {

               let artNo = $.trim($(this).val());


               if (artNo.length > 3) {

                    $.post("/common/patient-last-request-details.php", {
                              testType: 'vl',
                              patientId: artNo,
                         },
                         function(data) {
                              if (data != "0") {
                                   obj = $.parseJSON(data);
                                   if (obj.no_of_req_time != null && obj.no_of_req_time > 0) {
                                        $("#artNoGroup").html("<small style='color: red'><?= _translate("No. of times Test Requested for this Patient", true); ?> : " + obj.no_of_req_time + "</small>");
                                   }
                                   if (obj.request_created_datetime != null) {
                                        $("#artNoGroup").append("<br><small style='color:red'><?= _translate("Last Test Request Added On LIS/STS", true); ?> : " + obj.request_created_datetime + "</small>");
                                   }
                                   if (obj.sample_collection_date != null) {
                                        $("#artNoGroup").append("<br><small style='color:red'><?= _translate("Sample Collection Date for Last Request", true); ?> : " + obj.sample_collection_date + "</small>");
                                   }
                                   if (obj.no_of_tested_time != null && obj.no_of_tested_time > 0) {
                                        $("#artNoGroup").append("<br><small style='color:red'><?= _translate("Total No. of times Patient tested for HIV VL", true); ?> : " + obj.no_of_tested_time + "</small >");
                                   }
                              } else {

                                   $("#artNoGroup").html('');
                              }
                         });
               }

          });

          $("#vlLog").on('keyup keypress blur change paste', function() {
               if ($(this).val() != '') {
                    if ($(this).val() != $(this).val().replace(/[^\d\.]/g, "")) {
                         $(this).val('');
                         alert('Please enter only numeric values for Viral Load Log Result')
                    }
               }
          });
          $('#province').select2({
               placeholder: "<?= _translate('Select Province'); ?>"
          });
          $('#district').select2({
               placeholder: "<?= _translate('Select District'); ?>"
          });

          $('#facilityId').select2({
               placeholder: "<?= _translate('Select Clinic/Health Center'); ?>"
          });
          $('#labId').select2({
               placeholder: "<?= _translate('Select Lab Name'); ?>"
          });
          $('#reviewedBy').select2({
               placeholder: "<?= _translate('Select Reviewed By'); ?>"
          });
          $('#approvedBy').select2({
               placeholder: "<?= _translate('Select Approved By'); ?>"
          });
          $('#testedBy').select2({
               placeholder: "<?= _translate('Select Tested By'); ?>"
          });
          $('#artRegimen').select2({
               placeholder: "<?= _translate('Select Current ARV Protocol'); ?>"
          });
          // BARCODESTUFF START
          <?php
          if (isset($_GET['barcode']) && $_GET['barcode'] == 'true') {
               $sampleCode = htmlspecialchars($_GET['s']);
               $facilityCode = htmlspecialchars($_GET['f']);
               $patientID = htmlspecialchars($_GET['p']);
               echo "printBarcodeLabel('$sampleCode','$facilityCode','$patientID');";
          }
          ?>
          // BARCODESTUFF END
          <?php if (isset($cpyReq) && !empty($cpyReq) && $cpyReq == 'yes') {
               unset($_SESSION['vlData']); ?>
               fillFacilityDetails();
          <?php } ?>
     });

     function editableSelectClinician(id, _fieldName, table, _placeholder) {
          $("#" + id).select2({
               placeholder: _placeholder,
               minimumInputLength: 0,
               width: '100%',
               allowClear: true,
               id: function(bond) {
                    return bond._id;
               },
               ajax: {
                    placeholder: "<?= _translate("Type one or more character to search", escapeTextOrContext: true); ?>",
                    url: "/includes/get-data-list-for-generic.php",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                         return {
                              fieldName: _fieldName,
                              tableName: table,
                              q: params.term, // search term
                              page: params.page,
                              facilityId: $("#facilityId").val(),
                         };
                    },
                    processResults: function(data, params) {
                         params.page = params.page || 1;
                         return {
                              results: data.result,
                              pagination: {
                                   more: (params.page * 30) < data.total_count
                              }
                         };
                    },
                    //cache: true
               },
               escapeMarkup: function(markup) {
                    return markup;
               }
          });
     }



     function hivDetectionChange() {

          var text = $('#testingPlatform').val();
          if (!text) {
               $("#vlResult").attr("disabled", true);
               return;
          }
          var str1 = text.split("##");
          var str = str1[0];

          $(".vlResult, .vlLog").show();
          $("#isSampleRejected").val("");
          //Get VL results by platform id
          var platformId = str1[3];
          $("#possibleVlResults").html('');
          $.post("/vl/requests/getVlResults.php", {
                    instrumentId: platformId,
               },
               function(data) {
                    $("#vlResult").attr("disabled", false);
                    if (data != "") {
                         $("#possibleVlResults").html(data);
                    }
               });

     }

     function showTesting(chosenClass, id) {
          $('.controlVlTypeFields').removeClass('isRequired');
          if ($('#controlVlType' + id).length) {
               $('#controlVlType' + id).addClass('isRequired');
          }
          $(".viralTestData").val('');
          $(".hideTestData").hide();
          $("." + chosenClass).show();
          if ($("#selectedSample").val() != "") {
               patientInfo = JSON.parse($("#selectedSample").val());

               if ($.trim(patientInfo['sample_tested_datetime']) != '') {
                    $("#rmTestingLastVLDate").val($.trim(patientInfo['sample_tested_datetime']));
                    $("#repeatTestingLastVLDate").val($.trim(patientInfo['sample_tested_datetime']));
                    $("#suspendTreatmentLastVLDate").val($.trim(patientInfo['sample_tested_datetime']));
               }

               if ($.trim(patientInfo['result']) != '') {
                    $("#rmTestingVlValue").val($.trim(patientInfo['result']));
                    $("#repeatTestingVlValue").val($.trim(patientInfo['result']));
                    $("#suspendTreatmentVlValue").val($.trim(patientInfo['result']));
               }
          }
     }

     function getfacilityDetails(obj) {

          $.blockUI();
          var cName = $("#facilityId").val();
          var pName = $("#province").val();
          if (pName != '' && provinceName && facilityName) {
               facilityName = false;
          }
          if ($.trim(pName) != '') {
               //if (provinceName) {
               $.post("/includes/siteInformationDropdownOptions.php", {
                         pName: pName,
                         testType: 'vl'
                    },
                    function(data) {
                         if (data != "") {
                              details = data.split("###");
                              $("#facilityId").html(details[0]);
                              $("#district").html(details[1]);
                              //$("#clinicianName").val(details[2]);
                         }
                    });
               //}

          } else if (pName == '') {
               provinceName = true;
               facilityName = true;
               $("#province").html("<?= $province; ?>");
               $("#facilityId").html("<?= $facility; ?>");
               $("#facilityId").select2("val", "");
               $("#district").html("<option value=''> -- Select -- </option>");
          }
          $.unblockUI();
     }


     function getfacilityDistrictwise(obj) {
          $.blockUI();
          var dName = $("#district").val();
          var cName = $("#facilityId").val();
          if (dName != '') {
               $.post("/includes/siteInformationDropdownOptions.php", {
                         dName: dName,
                         cliName: cName,
                         testType: 'vl'
                    },
                    function(data) {
                         if (data != "") {
                              details = data.split("###");
                              $("#facilityId").html(details[0]);
                         }
                    });
          } else {
               $("#facilityId").html("<option value=''> -- Select -- </option>");
          }
          $.unblockUI();
     }






     function fillFacilityDetails() {
          $.blockUI();
          //check facility name
          var cName = $("#facilityId").val();
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
               $("#facilityId").html("<?php echo ((string) $facility); ?>");
          }
          $.unblockUI();
          $("#facilityCode").val($('#facilityId').find(':selected').data('code'));
          var femails = $('#facilityId').find(':selected').data('emails');
          var fmobilenos = $('#facilityId').find(':selected').data('mobile-nos');
          var fContactPerson = $('#facilityId').find(':selected').data('contact-person');
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
          if ($(this).val() == 'male' || $(this).val() == 'unreported') {
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

     $("#isSampleRejected").change(function() {
          console.log(this.value);
          if ($(this).val() == 'yes') {
               $('.rejectionReason').show();
               $('.vlResult').css('display', 'none');
               $('#rejectionReason').addClass('isRequired');
          } else {
               $('.vlResult').css('display', 'block');
               $('.rejectionReason').hide();
               $('#rejectionReason').removeClass('isRequired');
               $('#rejectionReason').val('');
          }
     });

     $('.specialResults').change(function() {
          if ($(this).is(':checked')) {
               $('#vlResult, #vlLog').val('');
               $('#vlResult,#vlLog').attr('readonly', true);
               $(".specialResults").not(this).attr('disabled', true);
               $("#sampleTestingDateAtLab").addClass('isRequired');
          } else {
               $('#vlResult,#vlLog').attr('readonly', false);
               $(".specialResults").not(this).attr('disabled', false);
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


     function validateNow(nextStep) {

          $("#saveNext").val(nextStep);

          if ($('#isSampleRejected').val() == "yes") {
               $('.vlResult, #vlResult').removeClass('isRequired');
          }
          $("#provinceId").val($("#province").find(":selected").attr("data-province-id"));
          let format = '<?php echo $arr['sample_code']; ?>';
          let sCodeLentgh = $("#sampleCode").val();
          let ARTlength = $("#artNo").val();
          let minLength = '<?php echo $arr['min_length']; ?>';
          if ((format == 'alphanumeric' || format == 'numeric') && sCodeLentgh.length < minLength && sCodeLentgh != '') {
               alert("Sample ID length must be a minimum length of " + minLength + " characters");
               return false;
          }

          if (!$('#ageUnreported').is(':checked')) {
               if ($.trim($("#dob").val()) == '' && $.trim($("#ageInYears").val()) == '' && $.trim($("#ageInMonths").val()) == '') {
                    alert("<?= _translate("Please enter the Patient Date of Birth or Age", true); ?>");
                    return false;
               }
          }
          let formId = 'vlRequestFormCameroon';
          flag = deforayValidator.init({
               formId: formId
          });

          if (flag) {
               $('.btn-disabled').attr('disabled', 'yes');
               $(".btn-disabled").prop("onclick", null).off("click");
               $.blockUI();
               insertSampleCode(formId);
          } else {
               $('.isRequired').each(function() {
                    ($(this).val() == '') ? $(this).css('background-color', '#FFFF99'): $(this).css('background-color', '#FFFFFF')
               });
          }
     }

     function setPatientDetails(pDetails) {
          $("#selectedSample").val(pDetails);
          var patientArray = JSON.parse(pDetails);
          $("#patientFirstName").val(patientArray['name']);
          $("#patientPhoneNumber").val(patientArray['mobile']);
          if ($.trim(patientArray['dob']) != '') {
               $("#dob").val(patientArray['dob']);
               getAge();
          } else if ($.trim(patientArray['age_in_years']) != '' && $.trim(patientArray['age_in_years']) != 0) {
               $("#ageInYears").val(patientArray['age_in_years']);
          } else if ($.trim(patientArray['age_in_months']) != '') {
               $("#ageInMonths").val(patientArray['age_in_years']);
          }

          if ($.trim(patientArray['gender']) != '') {
               $('#breastfeedingYes').removeClass('isRequired');
               $('#pregYes').removeClass('isRequired');
               if (patientArray['gender'] == 'male' || patientArray['gender'] == 'unreported') {
                    $('.femaleSection').hide();
                    $('input[name="breastfeeding"]').prop('checked', false);
                    $('input[name="patientPregnant"]').prop('checked', false);
                    if (patientArray['gender'] == 'male') {
                         $("#genderMale").prop('checked', true);
                    } else {
                         $("#genderUnreported").prop('checked', true);
                    }
               } else if (patientArray['gender'] == 'female') {
                    $('.femaleSection').show();
                    $("#genderFemale").prop('checked', true);
                    $('#breastfeedingYes').addClass('isRequired');
                    $('#pregYes').addClass('isRequired');
                    if ($.trim(patientArray['is_pregnant']) != '') {
                         if ($.trim(patientArray['is_pregnant']) == 'yes') {
                              $("#pregYes").prop('checked', true);
                         } else if ($.trim(patientArray['is_pregnant']) == 'no') {
                              $("#pregNo").prop('checked', true);
                         }
                    }
                    if ($.trim(patientArray['is_pregnant']) != '') {
                         if ($.trim(patientArray['is_pregnant']) == 'yes') {
                              $("#breastfeedingYes").prop('checked', true);
                         } else if ($.trim(patientArray['is_pregnant']) == 'no') {
                              $("#breastfeedingNo").prop('checked', true);
                         }
                    }
               }
          }
          if ($.trim(patientArray['consent_to_receive_sms']) != '') {
               if (patientArray['consent_to_receive_sms'] == 'yes') {
                    $("#receivesmsYes").prop('checked', true);
               } else if (patientArray['consent_to_receive_sms'] == 'no') {
                    $("#receivesmsNo").prop('checked', true);
               }
          }

          if ($.trim(patientArray['is_encrypted']) != '') {
               if (patientArray['is_encrypted'] == 'yes') {
                    $("#encryptPII").val('yes');
               } else {
                    $("#encryptPII").val('no');
               }
          }

          if ($.trim(patientArray['patient_art_no']) != '') {
               $("#artNo").val($.trim(patientArray['patient_art_no']));
          }

          if ($.trim(patientArray['treatment_initiated_date']) != '') {
               $("#dateOfArtInitiation").val($.trim(patientArray['treatment_initiated_date']));
          }

          if ($.trim(patientArray['current_regimen']) != '') {
               $("#artRegimen").val($.trim(patientArray['current_regimen']));
               $('#artRegimen').trigger('change');
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

     function checkreasonForVLTesting() {
          var reasonForVLTesting = $("#vlTestReason").val();
          if (reasonForVLTesting == "other") {
               $("#newreasonForVLTesting").show().addClass("isRequired");
          } else {
               $("#newreasonForVLTesting").hide().removeClass("isRequired");
          }
     }

     function checkNameValidation(tableName, fieldName, obj, fnct, alrt, callback) {
          var removeDots = obj.value.replace(/\./g, "");
          removeDots = removeDots.replace(/\,/g, "");
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



     $('#vlRequestFormCameroon').keypress((e) => {
          // Enter key corresponds to number 13
          if (e.which === 13) {
               e.preventDefault();
               //console.log('form submitted');
               validateNow('save'); // Trigger the validateNow function
          }
     });
     // Handle Enter key specifically for select2 elements
     $(document).on('keydown', '.select2-container--open', function(e) {
          if (e.which === 13) {
               e.preventDefault(); // Prevent the default form submission
               validateNow('save'); // Trigger the validateNow function
          }
     });

     function updateAgeInfo() {
          var isChecked = $("#ageUnreported").is(":checked");
          if (isChecked == true) {
               $("#dob").val("");
               $("#ageInYears").val("");
               $('#dob').prop('readonly', true);
               $('#ageInYears').prop('readonly', true);
               $('#dob').removeClass('isRequired');
          } else {
               $('#dob').prop('readonly', false);
               $('#ageInYears').prop('readonly', false);
               $('#dob').addClass('isRequired');
          }
     }
</script>
