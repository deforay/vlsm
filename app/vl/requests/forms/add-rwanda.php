<?php

use App\Registries\ContainerRegistry;
use App\Services\CommonService;
use App\Services\DatabaseService;

/** @var DatabaseService $db */
$db = ContainerRegistry::get(DatabaseService::class);

/** @var CommonService $general */
$general = ContainerRegistry::get(CommonService::class);

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


$province = $general->getUserMappedProvinces($_SESSION['facilityMap']);
$facility = $general->generateSelectOptions($healthFacilities, null, '-- Select --');


$sKey = '';
$sFormat = '';
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
          <h1><em class="fa-solid fa-pen-to-square"></em> VIRAL LOAD LABORATORY REQUEST FORM </h1>
          <ol class="breadcrumb">
               <li><a href="/dashboard/index.php"><em class="fa-solid fa-chart-pie"></em> Home</a></li>
               <li class="active">Add VL Request</li>
          </ol>
     </section>
     <!-- Main content -->
     <section class="content">
          <div class="box box-default">
               <div class="box-header with-border">
                    <div class="pull-right" style="font-size:15px;"><span class="mandatory">*</span> <?= _translate("indicates required fields"); ?> &nbsp;</div>
               </div>
               <div class="box-body">
                    <!-- form start -->
                    <form class="form-inline" method="post" name="vlRequestFormRwd" id="vlRequestFormRwd" autocomplete="off" action="addVlRequestHelper.php">
                         <div class="box-body">
                              <div class="box box-primary">
                                   <div class="box-header with-border">
                                        <h3 class="box-title">Clinic Information: (To be filled by requesting Clinican/Nurse)</h3>
                                   </div>
                                   <div class="box-body">
                                        <div class="row">
                                             <div class="col-xs-3 col-md-3">
                                                  <div class="">
                                                       <?php if ($general->isSTSInstance()) { ?>
                                                            <label for="sampleCode">Sample ID </label><br>
                                                            <span id="sampleCodeInText" style="width:100%;border-bottom:1px solid #333;"></span>
                                                            <input type="hidden" class="<?php echo $sampleClass; ?>" id="sampleCode" name="sampleCode" />
                                                       <?php } else { ?>
                                                            <label for="sampleCode">Sample ID <span class="mandatory">*</span></label>
                                                            <input type="text" class="form-control isRequired <?php echo $sampleClass; ?>" id="sampleCode" name="sampleCode" readonly="readonly" <?php echo $maxLength; ?> placeholder="Enter Sample ID" title="<?= _translate("Please make sure you have selected Sample Collection Date and Requesting Facility"); ?>" style="width:100%;" onblur="checkSampleNameValidation('form_vl','<?php echo $sampleCode; ?>',this.id,null,'This sample id already exists. Try another',null)" />
                                                       <?php } ?>
                                                  </div>
                                             </div>
                                             <div class="col-xs-3 col-md-3">
                                                  <div class="">
                                                       <label for="sampleReordered">
                                                            <input type="checkbox" class="" id="sampleReordered" name="sampleReordered" value="yes" title="Please indicate if this is a reordered sample"> Sample Reordered
                                                       </label>
                                                  </div>
                                             </div>
                                             <!-- BARCODESTUFF START -->
                                             <?php if (isset($global['bar_code_printing']) && $global['bar_code_printing'] != "off") { ?>
                                                  <div class="col-xs-3 col-md-3 pull-right">
                                                       <div class="">
                                                            <label for="printBarCode">Print Barcode Label <span class="mandatory">*</span> </label>
                                                            <input type="checkbox" class="" id="printBarCode" name="printBarCode" checked />
                                                       </div>
                                                  </div>
                                             <?php } ?>
                                             <!-- BARCODESTUFF END -->
                                        </div>
                                        <div class="row">
                                             <div class="col-xs-3 col-md-3">
                                                  <div class="">
                                                       <label for="province">Province <span class="mandatory">*</span></label>
                                                       <select class="form-control isRequired" name="province" id="province" title="Please choose a province" style="width:100%;" onchange="getProvinceDistricts(this);">
                                                            <?php echo $province; ?>
                                                       </select>
                                                  </div>
                                             </div>
                                             <div class="col-xs-3 col-md-3">
                                                  <div class="">
                                                       <label for="district">District <span class="mandatory">*</span></label>
                                                       <select class="form-control isRequired" name="district" id="district" title="Please choose a district" style="width:100%;" onchange="getFacilities(this);">
                                                            <option value=""> -- Select -- </option>
                                                       </select>
                                                  </div>
                                             </div>
                                             <div class="col-xs-3 col-md-3">
                                                  <div class="">
                                                       <label for="facilityId">Clinic/Health Center <span class="mandatory">*</span></label>
                                                       <select class="form-control isRequired" id="facilityId" name="facilityId" title="Please select a clinic/health center name" style="width:100%;" onchange="fillFacilityDetails();">
                                                            <?php echo $facility; ?>
                                                       </select>
                                                  </div>
                                             </div>
                                             <div class="col-xs-3 col-md-3">
                                                  <div class="">
                                                       <label for="facilityCode">Clinic/Health Center Code </label>
                                                       <input type="text" class="form-control" style="width:100%;" name="facilityCode" id="facilityCode" placeholder="Clinic/Health Center Code" title="Please enter clinic/health center code">
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
                                        <div class="row">
                                             <div class="col-xs-3 col-md-3">
                                                  <div class="">
                                                       <label for="labId">VL Testing Hub <span class="mandatory">*</span></label>
                                                       <select name="labId" id="labId" class="form-control isRequired" title="Please choose a VL testing hub" style="width:100%;">
                                                            <?= $general->generateSelectOptions($testingLabs, null, '-- Select --'); ?>
                                                       </select>
                                                  </div>
                                             </div>
                                        </div>
                                   </div>
                              </div>
                              <div class="box box-primary">
                                   <div class="box-header with-border">
                                        <h3 class="box-title">Patient Information</h3>&nbsp;&nbsp;&nbsp;
                                        <input style="width:30%;" type="text" name="artPatientNo" id="artPatientNo" class="" placeholder="Enter ART Number or Patient Name" title="Enter art number or patient name" />&nbsp;&nbsp;
                                        <a style="margin-top:-0.35%;" href="javascript:void(0);" class="btn btn-default btn-sm" onclick="showPatientList();"><em class="fa-solid fa-magnifying-glass"></em>Search</a><span id="showEmptyResult" style="display:none;color: #ff0000;font-size: 15px;"><strong>&nbsp;No Patient Found</strong></span>
                                   </div>
                                   <div class="box-body">
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
                                             <div class="col-xs-3 col-md-3">
                                                  <div class="form-group">
                                                       <label for="artNo">ART (TRACNET) No. <span class="mandatory">*</span></label>
                                                       <input type="text" name="artNo" id="artNo" class="form-control isRequired patientId" placeholder="Enter ART Number" title="Enter art number" onchange="checkPatientDetails('form_vl','patient_art_no',this,null)" />
                                                       <span class="artNoGroup" id="artNoGroup"></span>
                                                  </div>
                                             </div>
                                             <div class="col-xs-3 col-md-3">
                                                  <div class="form-group">
                                                       <label for="dob">Date of Birth <?php echo ($general->isSTSInstance()) ? "<span class='mandatory'>*</span>" : ''; ?></label>
                                                       <input type="text" name="dob" id="dob" class="form-control date <?php echo ($general->isSTSInstance()) ? "isRequired" : ''; ?>" placeholder="Enter DOB" title="Enter dob" onchange="getAge();checkARTInitiationDate();" />
                                                  </div>
                                             </div>
                                             <div class="col-xs-3 col-md-3">
                                                  <div class="form-group">
                                                       <label for="ageInYears">If DOB unknown, Age in Year(s) </label>
                                                       <input type="text" name="ageInYears" id="ageInYears" class="form-control forceNumeric" maxlength="2" placeholder="Age in Year(s)" title="Enter age in years" />
                                                  </div>
                                             </div>
                                             <div class="col-xs-3 col-md-3">
                                                  <div class="form-group">
                                                       <label for="ageInMonths">If Age
                                                            < 1, Age in Month(s) </label> <input type="text" name="ageInMonths" id="ageInMonths" class="form-control forceNumeric" maxlength="2" placeholder="Age in Month(s)" title="Enter age in months" />
                                                  </div>
                                             </div>
                                        </div>
                                        <div class="row">
                                             <div class="col-xs-3 col-md-3">
                                                  <div class="form-group">
                                                       <label for="patientFirstName">Patient Name / Code </label>
                                                       <input type="text" name="patientFirstName" id="patientFirstName" class="form-control" placeholder="Enter Patient Name" title="Enter patient name" />
                                                  </div>
                                             </div>
                                             <div class="col-xs-3 col-md-3">
                                                  <div class="form-group">
                                                       <label for="gender"><?= _translate("Sex"); ?> <span class="mandatory">*</span></label><br>
                                                       <label class="radio-inline" style="margin-left:0px;">
                                                            <input type="radio" class="isRequired" id="genderMale" name="gender" value="male" title="Please select sex">Male
                                                       </label>&nbsp;&nbsp;
                                                       <label class="radio-inline" style="margin-left:0px;">
                                                            <input type="radio" id="genderFemale" name="gender" value="female" title="Please select sex">Female
                                                       </label>&nbsp;&nbsp;
                                                       <!--<label class="radio-inline" style="margin-left:0px;">
                                                       <input type="radio" class="" id="genderNotRecorded" name="gender" value="not_recorded" title="Please choose sex">Not Recorded
                                                  </label>-->
                                                  </div>
                                             </div>
                                             <div class="col-xs-3 col-md-3">
                                                  <div class="form-group">
                                                       <label for="patientPhoneNumber">Phone Number</label>
                                                       <input type="text" name="patientPhoneNumber" id="patientPhoneNumber" class="form-control phone-number" maxlength="15" placeholder="Enter Phone Number" title="Enter phone number" />
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
                                                            <input type="text" class="form-control isRequired dateTime" style="width:100%;" name="sampleCollectionDate" id="sampleCollectionDate" placeholder="Sample Collection Date" title="Please select sample collection date" onchange="generateSampleCode(); checkCollectionDate(this.value);">
                                                            <span class="expiredCollectionDate" style="color:red; display:none;"></span>
                                                       </div>
                                                  </div>
                                                  <div class="col-xs-3 col-md-3">
                                                       <div class="form-group">
                                                            <label for="specimenType">Sample Type <span class="mandatory">*</span></label>
                                                            <select name="specimenType" id="specimenType" class="form-control isRequired" title="Please choose sample type">
                                                                 <option value=""> -- Select -- </option>
                                                                 <?php
                                                                 $selected = '';
                                                                 if (count($sResult) == 1) {
                                                                      $selected = "selected='selected'";
                                                                 }
                                                                 foreach ($sResult as $name) { ?>
                                                                      <option <?= $selected; ?> value="<?php echo $name['sample_id']; ?>"><?= $name['sample_name']; ?></option>
                                                                 <?php } ?>
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
                                                                 <input type="text" class="form-control date" name="dateOfArtInitiation" id="dateOfArtInitiation" placeholder="Date of Treatment Initiation" title="Date of treatment initiation" style="width:100%;" onchange="checkARTInitiationDate();">
                                                            </div>
                                                       </div>
                                                       <div class="col-xs-3 col-md-3">
                                                            <div class="form-group">
                                                                 <label for="artRegimen">Current Regimen <?php echo ($general->isSTSInstance()) ? "<span class='mandatory'>*</span>" : ''; ?></label>
                                                                 <select class="form-control  <?php echo ($general->isSTSInstance()) ? "isRequired" : ''; ?>" id="artRegimen" name="artRegimen" title="Please choose an ART Regimen" style="width:100%;" onchange="checkARTRegimenValue();">
                                                                      <option value="">-- ART Codes --</option>
                                                                      <?php foreach ($artRegimenResult as $heading) { ?>
                                                                           <optgroup label="<?= $heading['headings']; ?>">
                                                                                <?php
                                                                                foreach ($aResult as $regimen) {

                                                                                     if ($heading['headings'] == $regimen['headings']) { ?>
                                                                                          <option value="<?php echo $regimen['art_code']; ?>"><?php echo $regimen['art_code']; ?></option>
                                                                                <?php
                                                                                     }
                                                                                }
                                                                                ?>
                                                                           </optgroup>
                                                                      <?php }
                                                                      if ($general->isLISInstance() === false) { ?>
                                                                           <!-- <option value="other">Other</option> -->
                                                                      <?php } ?>
                                                                      <option value="not_reported">No Information Provided</option>
                                                                 </select>
                                                                 <input type="text" class="form-control newArtRegimen" name="newArtRegimen" id="newArtRegimen" placeholder="ART Regimen" title="Please enter ART Regimen" style="width:100%;display:none;margin-top:2px;">
                                                            </div>
                                                       </div>
                                                       <div class="col-xs-3 col-md-3">
                                                            <div class="form-group">
                                                                 <label for="" class="curRegimenDate">Date of Initiation of Current Regimen<?php echo ($general->isSTSInstance()) ? "<span class='mandatory'>*</span>" : ''; ?></label>
                                                                 <input type="text" class="form-control date <?php echo ($general->isSTSInstance()) ? "isRequired" : ''; ?>" style="width:100%;" name="regimenInitiatedOn" id="regimenInitiatedOn" placeholder="Current Regimen Initiated On" title="Please enter current regimen initiated on">
                                                            </div>
                                                       </div>
                                                       <div class="col-xs-3 col-md-3">
                                                            <div class="form-group">
                                                                 <label for="arvAdherence">ARV Adherence <?php echo ($general->isSTSInstance()) ? "<span class='mandatory'>*</span>" : ''; ?></label>
                                                                 <select name="arvAdherence" id="arvAdherence" class="form-control <?php echo ($general->isSTSInstance()) ? "isRequired" : ''; ?>" title="Please choose adherence">
                                                                      <option value=""> -- Select -- </option>
                                                                      <option value="good">Good >= 95%</option>
                                                                      <option value="fair">Fair (85-94%)</option>
                                                                      <option value="poor">Poor < 85%</option>
                                                                      <option value="not_reported">No Information Provided</option>
                                                                 </select>
                                                            </div>
                                                       </div>
                                                  </div>
                                                  <div class="row femaleSection" style="display:none;">
                                                       <div class="col-xs-3 col-md-3">
                                                            <div class="form-group">
                                                                 <label for="patientPregnant">Is Patient Pregnant? <span class="mandatory">*</span></label><br>
                                                                 <label class="radio-inline">
                                                                      <input type="radio" class="" id="pregYes" name="patientPregnant" value="yes" title="Please check if patient is pregnant"> Yes
                                                                 </label>
                                                                 <label class="radio-inline">
                                                                      <input type="radio" class="" id="pregNo" name="patientPregnant" value="no"> No
                                                                 </label>
                                                            </div>
                                                       </div>
                                                       <div class="col-xs-3 col-md-3">
                                                            <div class="form-group">
                                                                 <label for="breastfeeding">Is Patient Breastfeeding? <span class="mandatory">*</span></label><br>
                                                                 <label class="radio-inline">
                                                                      <input type="radio" class="" id="breastfeedingYes" name="breastfeeding" value="yes" title="Please check if patient is breastfeeding"> Yes
                                                                 </label>
                                                                 <label class="radio-inline">
                                                                      <input type="radio" class="" id="breastfeedingNo" name="breastfeeding" value="no"> No
                                                                 </label>
                                                            </div>
                                                       </div>
                                                  </div>
                                             </div>
                                             <div class="box box-primary">
                                                  <div class="box-header with-border">
                                                       <h3 class="box-title">Indication for Viral Load Testing <span class="mandatory">*</span></h3><small> (Please pick one): (To be completed by clinician)</small>
                                                  </div>
                                                  <div class="box-body">
                                                       <div class="row">
                                                            <div class="col-md-6">
                                                                 <div class="form-group">
                                                                      <div class="col-lg-12">
                                                                           <label class="radio-inline">
                                                                                <input type="radio" class="isRequired" id="rmTesting" name="reasonForVLTesting" value="routine" title="Please check viral load indication testing type" onclick="showTesting('rmTesting');">
                                                                                <strong>Routine Monitoring</strong>
                                                                           </label>
                                                                      </div>
                                                                 </div>
                                                            </div>
                                                       </div>
                                                       <div class="row rmTesting hideTestData well" style="display:none;">
                                                            <div class="col-md-6">
                                                                 <label class="col-lg-5 control-label">Last VL date if available</label>
                                                                 <div class="col-lg-7">
                                                                      <input type="text" class="form-control date viralTestData" id="rmTestingLastVLDate" name="rmTestingLastVLDate" placeholder="Select Last VL Date" title="Please select Last VL Date" />
                                                                 </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                 <label for="rmTestingVlValue" class="col-lg-3 control-label">Result Value</label>
                                                                 <div class="col-lg-7">
                                                                      <input type="text" class="form-control forceNumeric viralTestData" id="rmTestingVlValue" name="rmTestingVlValue" placeholder="Enter VL Result" title="Please enter VL Result" />
                                                                      (copies/mL)<br>
                                                                      <input type="checkbox" id="rmTestingVlCheckValuelt20" name="rmTestingVlValue" value="<20" title="Please check VL Result">
                                                                      < 20<br>
                                                                           <input type="checkbox" id="rmTestingVlCheckValueTnd" name="rmTestingVlValue" value="tnd" title="Please check VL Result"> Target Not Detected
                                                                 </div>
                                                            </div>
                                                       </div>
                                                       <div class="row">
                                                            <div class="col-md-6">
                                                                 <div class="form-group">
                                                                      <div class="col-lg-12">
                                                                           <label class="radio-inline">
                                                                                <input type="radio" class="" id="suspendTreatment" name="reasonForVLTesting" value="suspect" title="Please check viral load indication testing type" onclick="showTesting('suspendTreatment');">
                                                                                <strong>Suspect Treatment Failure</strong>
                                                                           </label>
                                                                      </div>
                                                                 </div>
                                                            </div>
                                                       </div>
                                                       <div class="row suspendTreatment hideTestData well" style="display: none;margin-bottom:20px;">
                                                            <div class="col-md-6">
                                                                 <label class="col-lg-5 control-label">Last VL date if available</label>
                                                                 <div class="col-lg-7">
                                                                      <input type="text" class="form-control date viralTestData" id="suspendTreatmentLastVLDate" name="suspendTreatmentLastVLDate" placeholder="Select Last VL Date" title="Please select Last VL Date" />
                                                                 </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                 <label for="suspendTreatmentVlValue" class="col-lg-3 control-label">Result Value</label>
                                                                 <div class="col-lg-7">
                                                                      <input type="text" class="form-control forceNumeric viralTestData" id="suspendTreatmentVlValue" name="suspendTreatmentVlValue" placeholder="Enter VL Result" title="Please enter VL Result" />
                                                                      (copies/mL)<br>
                                                                      <input type="checkbox" id="suspendTreatmentVlCheckValuelt20" name="suspendTreatmentVlValue" value="<20" title="Please check VL Result">
                                                                      < 20<br>
                                                                           <input type="checkbox" id="suspendTreatmentVlCheckValueTnd" name="suspendTreatmentVlValue" value="tnd" title="Please check VL Result"> Target Not Detected
                                                                 </div>
                                                            </div>
                                                       </div>
                                                       <div class="row">
                                                            <div class="col-md-8">
                                                                 <div class="form-group">
                                                                      <div class="col-lg-12">
                                                                           <label class="radio-inline">
                                                                                <input type="radio" class="" id="repeatTesting" name="reasonForVLTesting" value="failure" title="Please check viral load indication testing type" onclick="showTesting('repeatTesting');">
                                                                                <strong>Control VL test after adherence counselling addressing suspected treatment failure </strong>
                                                                           </label>
                                                                      </div>
                                                                 </div>
                                                            </div>
                                                       </div>
                                                       <div class="row repeatTesting hideTestData well" style="display:none;">
                                                            <div class="col-md-6">
                                                                 <label class="col-lg-5 control-label">Last VL date if available</label>
                                                                 <div class="col-lg-7">
                                                                      <input type="text" class="form-control date viralTestData" id="repeatTestingLastVLDate" name="repeatTestingLastVLDate" placeholder="Select Last VL Date" title="Please select Last VL Date" />
                                                                 </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                 <label for="repeatTestingVlValue" class="col-lg-3 control-label"> Result Value</label>
                                                                 <div class="col-lg-7">
                                                                      <input type="text" class="form-control forceNumeric viralTestData" id="repeatTestingVlValue" name="repeatTestingVlValue" placeholder="Enter VL Result" title="Please enter VL Result" />
                                                                      (copies/mL)<br>
                                                                      <input type="checkbox" id="repeatTestingVlCheckValuelt20" name="repeatTestingVlValue" value="<20" title="Please check VL Result">
                                                                      < 20<br>
                                                                           <input type="checkbox" id="repeatTestingVlCheckValueTnd" name="repeatTestingVlValue" value="tnd" title="Please check VL Result"> Target Not Detected
                                                                 </div>
                                                            </div>
                                                       </div>

                                                        <div class="row">
                                                            <div class="col-md-8">
                                                                 <div class="form-group">
                                                                      <div class="col-lg-12">
                                                                           <label class="radio-inline">
                                                                                <input type="radio" class="" id="confirmRecencyTesting" name="reasonForVLTesting" value="recency" title="Please check viral load indication testing type" onclick="showTesting('confirmRecencyTesting');">
                                                                                <strong> Confirmation Test for Recency </strong>
                                                                           </label>
                                                                      </div>
                                                                 </div>
                                                            </div>
                                                       </div>
                                                     <!---  <div class="row confirmRecencyTesting hideTestData well" style="display:none;">
                                                            <div class="col-md-6">
                                                                 <label class="col-lg-5 control-label">Date of Last VL Test</label>
                                                                 <div class="col-lg-7">
                                                                      <input type="text" class="form-control date viralTestData" id="confirmRecencyTestingLastVLDate" name="confirmRecencyTestingLastVLDate" placeholder="Select Last VL Date" title="Please select Last VL Date" />
                                                                 </div>
                                                            </div>
                                                             <div class="col-md-6">
                                                                 <label for="confirmRecencyTestingVlValue" class="col-lg-3 control-label">VL Result</label>
                                                                 <div class="col-lg-7">
                                                                      <input type="text" class="form-control forceNumeric viralTestData" id="confirmRecencyTestingVlValue" name="confirmRecencyTestingVlValue" placeholder="Enter VL Result" title="Please enter VL Result" />
                                                                      (copies/mL)<br>
                                                                      <input type="checkbox" id="confirmRecencyTestingVlCheckValuelt20" name="confirmRecencyTestingVlValue" value="<20" title="Please check VL Result">
                                                                      < 20<br>
                                                                           <input type="checkbox" id="confirmRecencyTestingVlCheckValueTnd" name="confirmRecencyTestingVlValue" value="tnd" title="Please check VL Result"> Target Not Detected
                                                                 </div>
                                                            </div> -->
                                                       </div>

                                                       <?php if (isset(SYSTEM_CONFIG['recency']['vlsync']) && SYSTEM_CONFIG['recency']['vlsync']) { ?>
                                                            <div class="row">
                                                                 <div class="col-md-6">
                                                                      <div class="form-group">
                                                                           <div class="col-lg-12">
                                                                                <label class="radio-inline">
                                                                                     <input type="radio" class="" id="recencyTest" name="reasonForVLTesting" value="recency" title="Please check viral load indication testing type" onclick="showTesting('recency')">
                                                                                     <strong>Confirmation Test for Recency</strong>
                                                                                </label>
                                                                           </div>
                                                                      </div>
                                                                 </div>
                                                            </div>
                                                       <?php } ?>
                                                       <hr>
                                                       <div class="row">
                                                            <div class="col-md-6">
                                                                 <label for="reqClinician" class="col-lg-5 control-label">Request Clinician <?php echo ($general->isSTSInstance()) ? "<span class='mandatory'>*</span>" : ''; ?></label>
                                                                 <div class="col-lg-7">
                                                                      <input type="text" class="form-control <?php echo ($general->isSTSInstance()) ? "isRequired" : ''; ?>" id="reqClinician" name="reqClinician" placeholder="Requesting Clinician Name" title="Please enter request clinician" />
                                                                 </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                 <label for="reqClinicianPhoneNumber" class="col-lg-5 control-label">Phone Number <?php echo ($general->isSTSInstance()) ? "<span class='mandatory'>*</span>" : ''; ?></label>
                                                                 <div class="col-lg-7">
                                                                      <input type="text" class="form-control phone-number <?php echo ($general->isSTSInstance()) ? "isRequired" : ''; ?>" id="reqClinicianPhoneNumber" name="reqClinicianPhoneNumber" maxlength="<?= strlen((string) $countryCode) + (int) $maxNumberOfDigits; ?>" placeholder="Phone Number" title="Please enter request clinician phone number" />
                                                                 </div>
                                                            </div>
                                                       </div>
                                                       <div class="row">
                                                            <div class="col-md-6">
                                                                 <label for="vlFocalPerson" class="col-lg-5 control-label">Shipper Name<?php echo ($general->isSTSInstance()) ? "<span class='mandatory'>*</span>" : ''; ?></label>
                                                                 <div class="col-lg-7">
                                                                      <input type="text" class="form-control <?php echo ($general->isSTSInstance()) ? "isRequired" : ''; ?>" id="vlFocalPerson" name="vlFocalPerson" placeholder="Shipper Name" title="Please enter shipper name" />
                                                                 </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                 <label for="vlFocalPersonPhoneNumber" class="col-lg-5 control-label">VL Shipper Phone Number<?php echo ($general->isSTSInstance()) ? "<span class='mandatory'>*</span>" : ''; ?></label>
                                                                 <div class="col-lg-7">
                                                                      <input type="text" class="form-control phone-number <?php echo ($general->isSTSInstance()) ? "isRequired" : ''; ?>" id="vlFocalPersonPhoneNumber" name="vlFocalPersonPhoneNumber" maxlength="<?= strlen((string) $countryCode) + (int) $maxNumberOfDigits; ?>" placeholder="Phone Number" title="Please enter vl shipper phone number" />
                                                                 </div>
                                                            </div>
                                                            <!-- <div class="col-md-4">
                                                                 <label class="col-lg-5 control-label" for="requestDate">Request Date <?php echo ($general->isSTSInstance()) ? "<span class='mandatory'>*</span>" : ''; ?></label>
                                                                 <div class="col-lg-7">
                                                                      <input type="text" class="form-control date <?php echo ($general->isSTSInstance()) ? "isRequired" : ''; ?>" id="requestDate" name="requestDate" placeholder="Request Date" title="Please select request date" />
                                                                 </div>
                                                            </div>-->
                                                            <!-- <div class="col-md-4">
                                                                 <label class="col-lg-5 control-label" for="emailHf">Email for HF</label>
                                                                 <div class="col-lg-7">
                                                                      <input type="text" class="form-control isEmail" id="emailHf" name="emailHf" placeholder="Email for HF" title="Please enter email for hf" />
                                                                 </div>
                                                            </div>-->
                                                       </div>
                                                  </div>
                                             </div>
                                             <?php if (_isAllowed('/vl/results/updateVlTestResult.php') && $_SESSION['accessType'] != 'collection-site') { ?>
                                                  <div class="box box-primary">
                                                       <div class="box-header with-border">
                                                            <h3 class="box-title">Laboratory Information</h3>
                                                       </div>
                                                       <div class="box-body">
                                                            <div class="row">
                                                                 <div class="col-md-6">
                                                                      <label for="testingPlatform" class="col-lg-5 control-label">VL Testing Platform </label>
                                                                      <div class="col-lg-7">
                                                                           <select name="testingPlatform" id="testingPlatform" class="form-control" title="Please choose VL Testing Platform" <?php echo $labFieldDisabled; ?> onchange="hivDetectionChange();">
                                                                                <option value="">-- Select --</option>
                                                                                <?php foreach ($importResult as $mName) { ?>
                                                                                     <option value="<?php echo $mName['machine_name'] . '##' . $mName['lower_limit'] . '##' . $mName['higher_limit'] . '##' . $mName['instrument_id']; ?>"><?php echo $mName['machine_name']; ?></option>
                                                                                <?php } ?>
                                                                           </select>
                                                                      </div>
                                                                 </div>
                                                                 <div class="col-md-6">
                                                                      <label class="col-lg-5 control-label" for="sampleReceivedDate">Date Sample Received at Testing Lab </label>
                                                                      <div class="col-lg-7">
                                                                           <input type="text" class="form-control dateTime" id="sampleReceivedDate" name="sampleReceivedDate" placeholder="Sample Received Date" title="Please select sample received date" <?php echo $labFieldDisabled; ?> />
                                                                      </div>
                                                                 </div>
                                                            </div>
                                                            <div class="row">
                                                                 <div class="col-md-6">
                                                                      <label class="col-lg-5 control-label" for="sampleTestingDateAtLab">Sample Testing Date </label>
                                                                      <div class="col-lg-7">
                                                                           <input type="text" class="form-control dateTime" id="sampleTestingDateAtLab" name="sampleTestingDateAtLab" placeholder="Sample Testing Date" title="Please select sample testing date" <?php echo $labFieldDisabled; ?> onchange="checkSampleTestingDate();" />
                                                                      </div>
                                                                 </div>

                                                                 <div class="col-md-6">
                                                                      <label class="col-lg-5 control-label" for="isSampleRejected">Is Sample Rejected? </label>
                                                                      <div class="col-lg-7">
                                                                           <select name="isSampleRejected" id="isSampleRejected" class="form-control" title="Please check if sample is rejected or not">
                                                                                <option value="">-- Select --</option>
                                                                                <option value="yes">Yes</option>
                                                                                <option value="no">No</option>
                                                                           </select>
                                                                      </div>
                                                                 </div>
                                                            </div>
                                                            <div class="row">


                                                                 <div class="col-md-6 rejectionReason" style="display:none;">
                                                                      <label class="col-lg-5 control-label" for="rejectionReason">Rejection Reason </label>
                                                                      <div class="col-lg-7">
                                                                           <select name="rejectionReason" id="rejectionReason" class="form-control" title="Please choose reason" <?php echo $labFieldDisabled; ?> onchange="checkRejectionReason();">
                                                                                <option value="">-- Select --</option>
                                                                                <?php foreach ($rejectionTypeResult as $type) { ?>
                                                                                     <optgroup label="<?php echo strtoupper((string) $type['rejection_type']); ?>">
                                                                                          <?php foreach ($rejectionResult as $reject) {
                                                                                               if ($type['rejection_type'] == $reject['rejection_type']) {
                                                                                          ?>
                                                                                                    <option value="<?php echo $reject['rejection_reason_id']; ?>"><?= $reject['rejection_reason_name']; ?></option>
                                                                                          <?php }
                                                                                          } ?>
                                                                                     </optgroup>
                                                                                <?php }
                                                                                if ($general->isLISInstance() === false) { ?>
                                                                                     <option value="other">Other (Please Specify) </option>
                                                                                <?php } ?>
                                                                           </select>
                                                                           <input type="text" class="form-control newRejectionReason" name="newRejectionReason" id="newRejectionReason" placeholder="Rejection Reason" title="Please enter rejection reason" style="width:100%;display:none;margin-top:2px;">
                                                                      </div>
                                                                 </div>
                                                                 <div class="col-md-6 vlResult">
                                                                      <label class="col-lg-5 control-label" for="vlResult">Viral Load Result (copies/mL) </label>
                                                                      <div class="col-lg-7 resultInputContainer">
                                                                           <input list="possibleVlResults" autocomplete="off" class="form-control" id="vlResult" name="vlResult" placeholder="Viral Load Result" title="Please enter viral load result" <?php echo $labFieldDisabled; ?> style="width:100%;" onchange="calculateLogValue(this)" disabled />
                                                                           <datalist id="possibleVlResults">

                                                                           </datalist>
                                                                      </div>
                                                                 </div>
                                                                 <div class="col-md-6 rejectionReason" style="display:none;">
                                                                      <label class="col-lg-5 control-label labels" for="rejectionDate">Rejection Date </label>
                                                                      <div class="col-lg-7">
                                                                           <input class="form-control date rejection-date" type="text" name="rejectionDate" id="rejectionDate" placeholder="Select Rejection Date" title="Please select rejection date" />
                                                                      </div>
                                                                 </div>
                                                            </div>
                                                            <div class="row">

                                                                 <div class="col-md-6 vlResult">
                                                                      <label class="col-lg-5 control-label" for="vlLog">Viral Load (Log) </label>
                                                                      <div class="col-lg-7">
                                                                           <input type="text" class="form-control" id="vlLog" name="vlLog" placeholder="Viral Load Log" title="Please enter viral load log" <?php echo $labFieldDisabled; ?> style="width:100%; margin-bottom:5px;" onchange="calculateLogValue(this);" />
                                                                      </div>
                                                                 </div>
                                                                 <div class="col-md-6">
                                                                      <label class="col-lg-5 control-label" for="reviewedOn">Reviewed On </label>
                                                                      <div class="col-lg-7">
                                                                           <input type="text" name="reviewedOn" id="reviewedOn" class="dateTime form-control" placeholder="Reviewed on" title="Please enter the Reviewed on" />
                                                                      </div>
                                                                 </div>
                                                                 <div class="col-md-6">
                                                                      <label class="col-lg-5 control-label" for="reviewedBy">Reviewed By </label>
                                                                      <div class="col-lg-7">
                                                                           <select name="reviewedBy" id="reviewedBy" class="select2 form-control" title="Please choose reviewed by" style="width: 100%; margin-top:5px;">
                                                                                <?= $general->generateSelectOptions($userInfo, null, '-- Select --'); ?>
                                                                           </select>
                                                                      </div>
                                                                 </div>
                                                            </div>
                                                            <div class="row">


                                                                 <div class="col-md-6">
                                                                      <label class="col-lg-5 control-label" for="approvedOnDateTime">Approved On </label>
                                                                      <div class="col-lg-7">
                                                                           <input type="text" name="approvedOnDateTime" id="approvedOnDateTime" class="dateTime form-control" placeholder="Approved on" title="Please enter the Approved on" />
                                                                      </div>
                                                                 </div>
                                                                 <div class="col-md-6">
                                                                      <label class="col-lg-5 control-label" for="approvedBy">Approved By </label>
                                                                      <div class="col-lg-7">
                                                                           <select name="approvedBy" id="approvedBy" class="form-control" title="Please choose approved by" <?php echo $labFieldDisabled; ?>>
                                                                                <option value="">-- Select --</option>
                                                                                <?php foreach ($userResult as $uName) { ?>
                                                                                     <option value="<?php echo $uName['user_id']; ?>"><?php echo ($uName['user_name']); ?></option>
                                                                                <?php } ?>
                                                                           </select>
                                                                      </div>
                                                                 </div>
                                                            </div>
                                                            <div class="row">
                                                                 <div class="col-md-6">
                                                                      <label class="col-lg-5 control-label" for="resultDispatchedOn">Date Results Dispatched </label>
                                                                      <div class="col-lg-7">
                                                                           <input type="text" class="form-control dateTime" id="resultDispatchedOn" name="resultDispatchedOn" placeholder="Result Dispatched Date" title="Please select result dispatched date" <?php echo $labFieldDisabled; ?> />
                                                                      </div>
                                                                 </div>
                                                                 <div class="col-md-6">
                                                                      <label class="col-lg-5 control-label" for="labComments">Lab Tech. Comments </label>
                                                                      <div class="col-lg-7">
                                                                           <textarea class="form-control" name="labComments" id="labComments" placeholder="Lab comments" <?php echo $labFieldDisabled; ?>></textarea>
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
                                             <a class="btn btn-primary btn-disabled" href="javascript:void(0);" onclick="validateNow();return false;">Save</a>
                                             <input type="hidden" name="saveNext" id="saveNext" />
                                             <?php if ($arr['sample_code'] == 'auto' || $arr['sample_code'] == 'YY' || $arr['sample_code'] == 'MMYY') { ?>
                                                  <input type="hidden" name="sampleCodeFormat" id="sampleCodeFormat" value="<?php echo $sFormat; ?>" />
                                                  <input type="hidden" name="sampleCodeKey" id="sampleCodeKey" value="<?php echo $sKey; ?>" />
                                             <?php } ?>
                                             <input type="hidden" name="vlSampleId" id="vlSampleId" value="" />
                                             <input type="hidden" name="provinceId" id="provinceId" />
                                             <a class="btn btn-primary btn-disabled" href="javascript:void(0);" onclick="validateSaveNow();return false;">Save and Next</a>
                                             <a href="/vl/requests/vl-requests.php" class="btn btn-default"> Cancel</a>
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
          <script src="/assets/js/zebra-browserprint.js?v=<?= filemtime(WEB_ROOT . "/assets/js/zebra-browserprint.js") ?>"></script>
          <script src="/uploads/barcode-formats/zebra-format.js?v=<?= filemtime(WEB_ROOT . "/uploads/barcode-formats/zebra-format.js") ?>"></script>
          <script src="/assets/js/zebra-print.js?v=<?= filemtime(WEB_ROOT . "/assets/js/zebra-print.js") ?>"></script>
<?php
     }
}
?>

<!-- BARCODESTUFF END -->
<script>
     provinceName = true;
     facilityName = true;

     $(document).ready(function() {
          Utilities.autoSelectSingleOption('facilityId');
          Utilities.autoSelectSingleOption('specimenType');

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

          $('#facilityId').select2({
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
          $('#artRegimen').select2({
               placeholder: "ART Code"
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

     function showTesting(chosenClass) {
          $(".viralTestData").val('');
          $(".hideTestData").hide();
          $("." + chosenClass).show();
          if ($("#selectedSample").val() != "") {
               patientInfo = JSON.parse($("#selectedSample").val());

               if ($.trim(patientInfo['sample_tested_datetime']) != '') {
                    $("#rmTestingLastVLDate").val($.trim(patientInfo['sample_tested_datetime']));
                    $("#repeatTestingLastVLDate").val($.trim(patientInfo['sample_tested_datetime']));
                    $("#suspendTreatmentLastVLDate").val($.trim(patientInfo['sample_tested_datetime']));
                    $("#confirmRecencyTestingLastVLDate").val($.trim(patientInfo['sample_tested_datetime']));

               }

               if ($.trim(patientInfo['result']) != '') {
                    $("#rmTestingVlValue").val($.trim(patientInfo['result']));
                    $("#repeatTestingVlValue").val($.trim(patientInfo['result']));
                    $("#suspendTreatmentVlValue").val($.trim(patientInfo['result']));
                    $("#confirmRecencyTestingVlValue").val($.trim(patientInfo['result']));

               }
          }
     }

     function getProvinceDistricts(obj) {
          $.blockUI();
          var cName = $("#facilityId").val();
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
                              $("#facilityId").html("<option data-code='' data-emails='' data-mobile-nos='' data-contact-person='' value=''> -- Select -- </option>");
                              $(".facilityDetails").hide();
                              $(".facilityEmails").html('');
                              $(".facilityMobileNumbers").html('');
                              $(".facilityContactPerson").html('');
                         }
                    });
               //}
               generateSampleCode();
          } else if (pName == '') {
               provinceName = true;
               facilityName = true;
               $("#province").html("<?php echo $province; ?>");
               $("#district").html("<option value=''> -- Select -- </option>");
               $("#facilityId").html("<?php echo $facility; ?>");
               $("#facilityId").select2("val", "");
          }
          $.unblockUI();
     }

     function getFacilities(obj) {
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
               $("#facilityId").html("<?php echo $facility; ?>");
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

     $("#isSampleRejected").on("change", function() {
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


     $('#confirmRecencyTestingVlValue').on('input', function(e) {
          if (this.value != '') {
               $('#confirmRecencyTestingVlCheckValuelt20').attr('disabled', true);
               $('#confirmRecencyTestingVlCheckValueTnd').attr('disabled', true);
          } else {
               $('#confirmRecencyTestingVlCheckValuelt20').attr('disabled', false);
               $('#confirmRecencyTestingVlCheckValueTnd').attr('disabled', false);
          }
     });

     $('#confirmRecencyTestingVlCheckValuelt20').change(function() {
          if ($('#confirmRecencyTestingVlCheckValuelt20').is(':checked')) {
               $('#confirmRecencyTestingVlValue').attr('readonly', true);
               $('#confirmRecencyTestingVlCheckValueTnd').attr('disabled', true);
          } else {
               $('#confirmRecencyTestingVlValue').attr('readonly', false);
               $('#confirmRecencyTestingVlCheckValueTnd').attr('disabled', false);
          }
     });

     $('#confirmRecencyTestingVlCheckValueTnd').change(function() {
          if ($('#confirmRecencyTestingVlCheckValueTnd').is(':checked')) {
               $('#confirmRecencyTestingVlValue').attr('readonly', true);
               $('#confirmRecencyTestingVlCheckValuelt20').attr('disabled', true);
          } else {
               $('#confirmRecencyTestingVlValue').attr('readonly', false);
               $('#confirmRecencyTestingVlCheckValuelt20').attr('disabled', false);
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

     function validateNow() {

          clearDatePlaceholderValues('input.date, input.dateTime');

          $("#provinceId").val($("#province").find(":selected").attr("data-province-id"));
          var format = '<?php echo $arr['sample_code']; ?>';
          var sCodeLentgh = $("#sampleCode").val();

          var minLength = '<?php echo $arr['min_length']; ?>';
          if ((format == 'alphanumeric' || format == 'numeric') && sCodeLentgh.length < minLength && sCodeLentgh != '') {
               alert("Sample ID length must be a minimum length of " + minLength + " characters");
               return false;
          }

          flag = deforayValidator.init({
               formId: 'vlRequestFormRwd'
          });
          $('.isRequired').each(function() {
               ($(this).val() == '') ? $(this).css('background-color', '#FFFF99'): $(this).css('background-color', '#FFFFFF')
          });

          if ($.trim($("#dob").val()) == '' && $.trim($("#ageInYears").val()) == '' && $.trim($("#ageInMonths").val()) == '') {
               alert("<?= _translate("Please enter the Patient Date of Birth or Age", true); ?>");
               return false;
          }

          $("#saveNext").val('save');
          if (flag) {
               $('.btn-disabled').attr('disabled', 'yes');
               $(".btn-disabled").prop("onclick", null).off("click");
               $.blockUI();
               <?php if ($arr['sample_code'] == 'auto' || $arr['sample_code'] == 'YY' || $arr['sample_code'] == 'MMYY') { ?>
                    insertSampleCode('vlRequestFormRwd', 'vlSampleId', 'sampleCode', 'sampleCodeKey', 'sampleCodeFormat', 7, 'sampleCollectionDate');
               <?php } else { ?>
                    document.getElementById('vlRequestFormRwd').submit();
               <?php } ?>
          }
     }

     function validateSaveNow() {
          var format = '<?php echo $arr['sample_code']; ?>';
          var sCodeLentgh = $("#sampleCode").val();

          var minLength = '<?php echo $arr['min_length']; ?>';
          if ((format == 'alphanumeric' || format == 'numeric') && sCodeLentgh.length < minLength && sCodeLentgh != '') {
               alert("Sample ID length must be a minimum length of " + minLength + " characters");
               return false;
          }

          flag = deforayValidator.init({
               formId: 'vlRequestFormRwd'
          });
          $('.isRequired').each(function() {
               ($(this).val() == '') ? $(this).css('background-color', '#FFFF99'): $(this).css('background-color', '#FFFFFF')
          });
          $("#saveNext").val('next');
          if (flag) {
               $('.btn-disabled').attr('disabled', 'yes');
               $(".btn-disabled").prop("onclick", null).off("click");
               $.blockUI();
               <?php if ($arr['sample_code'] == 'auto' || $arr['sample_code'] == 'YY' || $arr['sample_code'] == 'MMYY') { ?>
                    insertSampleCode('vlRequestFormRwd', 'vlSampleId', 'sampleCode', 'sampleCodeKey', 'sampleCodeFormat', 7, 'sampleCollectionDate');
               <?php } else { ?>
                    document.getElementById('vlRequestFormRwd').submit();
               <?php } ?>
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
</script>
