<?php
// imported in tb-add-request.php based on country in global config

use App\Registries\ContainerRegistry;
use App\Services\TbService;


// Nationality
$nationalityQry = "SELECT * FROM `r_countries` ORDER BY `iso_name` ASC";
$nationalityResult = $db->query($nationalityQry);

foreach ($nationalityResult as $nrow) {
    $nationalityList[$nrow['id']] = ($nrow['iso_name']) . ' (' . $nrow['iso3'] . ')';
}


$pResult = $general->fetchDataFromTable('geographical_divisions', "geo_parent = 0 AND geo_status='active'");

// Getting the list of Provinces, Districts and Facilities

/** @var TbService $tbService */
$tbService = ContainerRegistry::get(TbService::class);


$tbXPertResults = $tbService->getTbResults('x-pert');
$tbLamResults = $tbService->getTbResults('lam');
$specimenTypeResult = $tbService->getTbSampleTypes();
$tbReasonsForTesting = $tbService->getTbReasonsForTesting();


$rKey = '';
$sKey = '';
$sFormat = '';
if ($_SESSION['accessType'] == 'collection-site') {
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
$microscope = array("No AFB" => "No AFB", "1+" => "1+", "2+" => "2+", "3+" => "3+");
?>
<style>
    .th-label {
        width: 15%;
    }

    .td-input {
        width: 35%;
    }
</style>
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1><em class="fa-solid fa-pen-to-square"></em> <?= _translate("TB LABORATORY TEST REQUEST FORM"); ?></h1>
        <ol class="breadcrumb">
            <li><a href="/"><em class="fa-solid fa-chart-pie"></em> <?= _translate("Home"); ?></a></li>
            <li class="active"><?= _translate("Add New Request"); ?></li>
        </ol>
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="box box-default">
            <div class="box-header with-border">

                <div class="pull-right" style="font-size:15px;"><span class="mandatory">*</span> <?= _translate("indicates required fields"); ?> &nbsp;</div>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
                <!-- form start -->
                <form class="form-horizontal" method="post" name="addTbRequestForm" id="addTbRequestForm" autocomplete="off" action="tb-add-request-helper.php">
                    <div class="box-body">
                        <div class="box box-default">
                            <div class="box-body">
                                <div class="box-header with-border sectionHeader">
                                    <h3 class="box-title"><?= _translate("REFERRING HEALTH FACILITY INFORMATION"); ?></h3>
                                </div>
                                <table aria-describedby="table" class="table" aria-hidden="true" style="width:100%">
                                    <tr>
                                        <?php if ($_SESSION['accessType'] == 'collection-site') { ?>
                                            <th scope="row" class="th-label"><label class="label-control" for="sampleCode"><?= _translate("Sample ID"); ?> </label></th>
                                            <td class="td-input">
                                                <span id="sampleCodeInText" style="width:100%;border-bottom:1px solid #333;"></span>
                                                <input type="hidden" id="sampleCode" name="sampleCode" />
                                            </td>
                                        <?php } else { ?>
                                            <th scope="row" class="th-label"><label class="label-control" for="sampleCode"><?= _translate("Sample ID"); ?> </label><span class="mandatory">*</span></th>
                                            <td class="td-input">
                                                <input type="text" class="form-control isRequired" id="sampleCode" name="sampleCode" readonly="readonly" placeholder="Sample ID" title="<?= _translate("Please make sure you have selected Sample Collection Date and Requesting Facility"); ?>" style="width:100%;" onchange="checkSampleNameValidation('form_tb','<?php echo $sampleCode; ?>',this.id,null,'The Sample ID that you entered already exists. Please try another Sample ID',null)" />
                                            </td>
                                        <?php } ?>
                                        <th class="th-label"><label class="label-control" for="province"><?= _translate("Health Facility/POE State"); ?> </label><span class="mandatory">*</span></th>
                                        <td class="td-input">
                                            <select class="form-control select2 isRequired" name="province" id="province" title="Please choose State" onchange="getfacilityDetails(this);" style="width:100%;">
                                                <?php echo $province; ?>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="th-label"><label class="label-control" for="district"><?= _translate("Health Facility/POE County"); ?> </label><span class="mandatory">*</span></th>
                                        <td class="td-input">
                                            <select class="form-control select2 isRequired" name="district" id="district" title="Please choose County" style="width:100%;" onchange="getfacilityDistrictwise(this);">
                                                <option value=""> -- <?= _translate("Select"); ?> -- </option>
                                            </select>
                                        </td>
                                        <th class="th-label"><label class="label-control" for="facilityId"><?= _translate("Health Facility/POE"); ?> </label><span class="mandatory">*</span></th>
                                        <td class="td-input">
                                            <select class="form-control isRequired " name="facilityId" id="facilityId" title="Please choose facility" style="width:100%;" onchange="getfacilityProvinceDetails(this);">
                                                <?php echo $facility; ?>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="th-label" scope="row"><label for="requestedDate"><?= _translate("Date of request"); ?> <span class="mandatory">*</span></label></th>
                                        <td class="td-input">
                                            <input type="text" class="date-time form-control" id="requestedDate" name="sampleReceivedDate" placeholder="<?= _translate("Please enter date"); ?>" title="Please enter date of request date" style="width:100%;" />
                                        </td>
                                        <th class="th-label"><label class="label-control" for="referringUnit"><?= _translate("Referring Unit"); ?> </label></th>
                                        <td class="td-input">
                                            <select class="form-control " name="referringUnit" id="referringUnit" title="Please choose referring unit" onchange="showOther(this.value, 'typeOfReferringUnit');" style="width:100%;">
                                                <option value="">-- Select --</option>
                                                <option value="art">ART</option>
                                                <option value="opd">OPD</option>
                                                <option value="tb">TB</option>
                                                <option value="pmtct">PMTCT</option>
                                                <option value="medical"><?= _translate("Medical"); ?></option>
                                                <option value="paediatric"><?= _translate("Paediatric"); ?></option>
                                                <option value="nutrition"><?= _translate("Nutrition"); ?></option>
                                                <option value="other"><?= _translate("Others"); ?></option>
                                            </select>
                                        </td>
                                        <td class="td-input">
                                            <input type="text" class="form-control typeOfReferringUnit" id="typeOfReferringUnit" name="typeOfReferringUnit" placeholder="Enter other of referring unit if others" title="Please enter other of referring unit if others" style="display: none;" />
                                        </td>
                                    </tr>
                                    <?php if ($_SESSION['accessType'] == 'collection-site') { ?>
                                        <tr>
                                            <th class="th-label"><label class="label-control" for="labId"><?= _translate("Testing Laboratory"); ?> <span class="mandatory">*</span></label> </th>
                                            <td class="td-input">
                                                <select name="labId" id="labId" class="form-control select2 isRequired" title="Please select Testing Testing Laboratory" style="width:100%;">
                                                    <?= $general->generateSelectOptions($testingLabs, null, '-- Select --'); ?>
                                                </select>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </table>


                                <div class="box-header with-border sectionHeader">
                                    <h3 class="box-title"><?= _translate("PATIENT INFORMATION"); ?></h3>
                                </div>
                                <div class="box-header with-border">
                                    <input type="text" name="artPatientNo" id="artPatientNo" placeholder="Enter Patient ID or Patient Name" title="Enter art number or patient name" />&nbsp;&nbsp;
                                    <a style="margin-top:-0.35%;" href="javascript:void(0);" class="btn btn-default btn-sm" onclick="showPatientList();"><em class="fa-solid fa-magnifying-glass"></em><?= _translate("Search"); ?></a><span id="showEmptyResult" style="display:none;color: #ff0000;font-size: 15px;"><strong>&nbsp;No Patient Found</strong></span>
                                </div>
                                <table aria-describedby="table" class="table" aria-hidden="true" style="width:100%">
                                    <tr class="encryptPIIContainer">
                                        <th scope="row" class="th-label"><label for="childId"><?= _translate('Encrypt PII'); ?> </label></th>
                                        <td class="td-input">
                                            <select name="encryptPII" id="encryptPII" class="form-control" title="<?= _translate('Encrypt Patient Identifying Information'); ?>">
                                                <option value=""><?= _translate('--Select--'); ?></option>
                                                <option value="no" selected='selected'><?= _translate('No'); ?></option>
                                                <option value="yes"><?= _translate('Yes'); ?></option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="th-label"><label for="patientId"><?= _translate("Unique ART Number"); ?></label></th>
                                        <td class="td-input">
                                            <input type="text" class="form-control patientId" id="patientId" name="patientId" placeholder="Patient Identification" title="Please enter Patient ID" style="width:100%;" />
                                        </td>
                                        <th scope="row" class="th-label"><label for="firstName"><?= _translate("First Name"); ?> <span class="mandatory">*</span> </label></th>
                                        <td class="td-input">
                                            <input type="text" class="form-control isRequired" id="firstName" name="firstName" placeholder="First Name" title="Please enter First name" style="width:100%;" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="th-label"><label for="lastName"><?= _translate("Surname"); ?> </label></th>
                                        <td class="td-input">
                                            <input type="text" class="form-control " id="lastName" name="lastName" placeholder="Last name" title="Please enter Last name" style="width:100%;" />
                                        </td>
                                        <th scope="row" class="th-label"><label for="dob"><?= _translate("Date of Birth"); ?> </label></th>
                                        <td class="td-input">
                                            <input type="text" class="form-control date" id="dob" name="dob" placeholder="Date of Birth" title="Please enter Date of birth" style="width:100%;" onchange="calculateAgeInYears('dob', 'patientAge');" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="th-label"><?= _translate("Age (years)"); ?></th>
                                        <td class="td-input"><input type="number" max="150" maxlength="3" oninput="this.value=this.value.slice(0,$(this).attr('maxlength'))" class="form-control " id="patientAge" name="patientAge" placeholder="Patient Age (in years)" title="Patient Age" style="width:100%;" /></td>
                                        <th scope="row" class="th-label"><label for="patientGender"><?= _translate("Sex"); ?> <span class="mandatory">*</span> </label></th>
                                        <td class="td-input">
                                            <select class="form-control isRequired" name="patientGender" id="patientGender" title="Please choose sex">
                                                <option value=''> -- <?= _translate("Select"); ?> -- </option>
                                                <option value='male'> <?= _translate("Male"); ?> </option>
                                                <option value='female'> <?= _translate("Female"); ?> </option>
                                                <option value='unreported'> <?= _translate("Unreported"); ?> </option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr class="femaleSection" style="display:none;">
                                        <th scope="row" class="th-label"><?= _translate("Is Patient Pregnant?"); ?></th>
                                        <td class="td-input">
                                            <label class="radio-inline">
                                                <input type="radio" class="" id="pregYes" name="patientPregnant" value="yes" title="<?= _translate('Please check if patient is pregnant'); ?>"> <?= _translate('Yes'); ?>
                                            </label>
                                            <label class="radio-inline">
                                                <input type="radio" class="" id="pregNo" name="patientPregnant" value="no"> <?= _translate('No'); ?>
                                            </label>
                                        </td>
                                        <th scope="row" class="th-label"><?= _translate("Is Patient Breastfeeding?"); ?></th>
                                        <td class="td-input">
                                            <label class="radio-inline">
                                                <input type="radio" class="" id="breastfeedingYes" name="breastfeeding" value="yes" title="<?= _translate('Please check if patient is breastfeeding'); ?>"> <?= _translate('Yes'); ?>
                                            </label>
                                            <label class="radio-inline">
                                                <input type="radio" class="" id="breastfeedingNo" name="breastfeeding" value="no"> <?= _translate('No'); ?>
                                            </label>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="th-label"><?= _translate("Weight"); ?> <small>(<?= _translate("kg"); ?>)</small></th>
                                        <td class="td-input"><input type="number" class="form-control" id="patientWeight" name="patientWeight" placeholder="Enter the tatient weight" title="Please enter the patient weight" style="width:100%;" /></td>
                                        <th scope="row" class="th-label"><label for="displacedPopulation"><?= _translate("Displaced Population"); ?></th>
                                        <td class="td-input">
                                            <select class="form-control" name="displacedPopulation" id="displacedPopulation" title="Please select the displaced population">
                                                <option value=''> -- <?= _translate("Select"); ?> -- </option>
                                                <option value='yes'> <?= _translate("Yes"); ?> </option>
                                                <option value='no'> <?= _translate("No"); ?> </option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="th-label"><label for="typeOfPatient"><?= _translate("Type of patient"); ?><span class="mandatory">*</span> </label></th>
                                        <td class="td-input">
                                            <select class="select2 form-control isRequired" name="typeOfPatient[]" id="typeOfPatient" title="Please select the type of patient" onchange="showOther(this.value,'typeOfPatientOther');" multiple style="width:100%;">
                                                <option value=''> -- <?= _translate("Select"); ?> -- </option>
                                                <option value='new'> <?= _translate("New"); ?> </option>
                                                <option value='loss-to-follow-up'> <?= _translate("Loss to Follow Up"); ?> </option>
                                                <option value='treatment-failure'> <?= _translate("Treatment Failure"); ?> </option>
                                                <option value='relapse'> <?= _translate("Relapse"); ?> </option>
                                                <option value='other'> <?= _translate("Other"); ?> </option>
                                            </select>
                                            <input type="text" class="form-control typeOfPatientOther" id="typeOfPatientOther" name="typeOfPatientOther" placeholder="Enter type of patient if others" title="Please enter type of patient if others" style="display: none;" />
                                        </td>
                                        <th scope="row" class="th-label"><label for="isReferredByCommunityActor"><?= _translate("Referred By Community Actor"); ?></th>
                                        <td class="td-input">
                                            <select class="form-control" name="isReferredByCommunityActor" id="isReferredByCommunityActor" title="Please select the referred by community actor">
                                                <option value=''> -- <?= _translate("Select"); ?> -- </option>
                                                <option value='yes'> <?= _translate("Yes"); ?> </option>
                                                <option value='no'> <?= _translate("No"); ?> </option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row" colspan="4"><label for="reasonForExamination"><?= _translate("Reason for Examination"); ?><span class="mandatory">*</span></th>
                                    </tr>
                                    <tr style=" border: 1px solid #8080804f; ">
                                        <td>
                                            <label class="radio-inline" style="margin-left:0;">
                                                <input type="radio" class="isRequired diagnosis-check" id="reasonForTbTest1" name="reasonForTbTest[reason]" value="diagnosis" title="Select reason for examination" onchange="checkSubReason(this,'diagnosis','followup-uncheck');">
                                                <strong><?= _translate("Diagnosis"); ?></strong>
                                            </label>
                                        </td>
                                        <td style="float: left;text-align: center;">
                                            <div class="diagnosis hide-reasons" style="display: none;">
                                                <ul style=" display: inline-flex; list-style: none; padding: 0px; ">
                                                    <li>
                                                        <label class="radio-inline" style="width:4%;margin-left:0;">
                                                            <input type="checkbox" class="diagnosis-check reason-checkbox" id="presumptiveTb" name="reasonForTbTest[elaboration][diagnosis][Presumptive TB]" value="yes">
                                                        </label>
                                                        <label class="radio-inline" for="presumptiveTb" style="padding-left:17px !important;margin-left:0;"><?= _translate("Presumptive TB"); ?></label>
                                                    </li>
                                                    <li>
                                                        <label class="radio-inline" style="width:4%;margin-left:0;">
                                                            <input type="checkbox" class="diagnosis-check reason-checkbox" id="rifampicinResistantTb" name="reasonForTbTest[elaboration][diagnosis][Rifampicin-resistant TB]" value="yes">
                                                        </label>
                                                        <label class="radio-inline" for="rifampicinResistantTb" style="padding-left:17px !important;margin-left:0;"><?= _translate("Rifampicin-resistant TB"); ?></label>
                                                    </li>
                                                    <li>
                                                        <label class="radio-inline" style="width:4%;margin-left:0;">
                                                            <input type="checkbox" class="diagnosis-check reason-checkbox" id="mdrtb" name="reasonForTbTest[elaboration][diagnosis][MDR-TB]" value="yes">
                                                        </label>
                                                        <label class="radio-inline" for="mdrtb" style="padding-left:17px !important;margin-left:0;"><?= _translate("MDR-TB"); ?></label>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                        <td>
                                            <label class="radio-inline" style="margin-left:0;">
                                                <input type="radio" class="isRequired followup-uncheck" id="reasonForTbTest1" name="reasonForTbTest[reason]" value="followup" title="Select reason for examination" onchange="checkSubReason(this,'follow-up','diagnosis-check');">
                                                <strong><?= _translate("Follow Up"); ?></strong>
                                            </label>
                                        </td>
                                        <td style="float: left;text-align: center;">
                                            <div class="follow-up hide-reasons" style="display: none;">
                                                <input type="text" class="form-control followup-uncheck reason-checkbox" id="followUp" name="reasonForTbTest[elaboration][followup][value]" placeholder="Enter the follow up" title="Please enter the follow up">
                                            </div>
                                        </td>
                                    </tr>
                                </table>

                                <div class="box-header with-border sectionHeader">
                                    <h3 class="box-title"><?= _translate("SPECIMEN INFORMATION"); ?></h3>
                                </div>
                                <table aria-describedby="table" class="table" aria-hidden="true">
                                    <tr>
                                        <th scope="row" class="th-label"><label class="label-control" for="sampleCollectionDate"><?= _translate("Date Specimen Collected"); ?> <span class="mandatory">*</span></label></th>
                                        <td class="td-input">
                                            <input class="form-control isRequired" type="text" name="sampleCollectionDate" id="sampleCollectionDate" placeholder="Sample Collection Date" onchange="generateSampleCode(); checkCollectionDate(this.value);" />
                                            <span class="expiredCollectionDate" style="color:red; display:none;"></span>
                                        </td>
                                        <th scope="row" class="th-label"><label class="label-control" for="specimenType"><?= _translate("Specimen Type"); ?> <span class="mandatory">*</span></label></th>
                                        <td class="td-input">
                                            <select name="specimenType" id="specimenType" class="form-control isRequired" title="Please choose specimen type" style="width:100%" onchange="showOther(this.value,'specimenTypeOther')">
                                                <?php echo $general->generateSelectOptions($specimenTypeResult, null, '-- Select --'); ?>
                                                <option value='other'> <?= _translate("Other"); ?> </option>
                                            </select>
                                        </td>
                                        <td class="td-input">
                                            <input type="text" class="form-control specimenTypeOther" id="specimenTypeOther" name="specimenTypeOther" placeholder="Enter specimen type of others" title="Please enter the specimen type if others" style="display: none;" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                            <label class="label-control th-label" for="testNumber"><?= _translate("Specimen Number"); ?></label>
                                        </th>
                                        <td class="td-input">
                                            <select class="form-control" name="testNumber" id="testNumber" title="Prélévement" style="width:100%;">
                                                <option value="">--Select--</option>
                                                <?php foreach (range(1, 5) as $element) {
                                                    echo '<option value="' . $element . '">' . $element . '</option>';
                                                } ?>
                                            </select>
                                        </td>
                                        <th scope="row" class="th-label">
                                            <label class="label-control" for="testTypeRequested"><?= _translate("Test(s) requested"); ?> </label>
                                        </th>
                                        <td class="td-input">
                                            <select name="testTypeRequested[]" id="testTypeRequested" class="select2 form-control" title="Please choose type of test request" style="width:100%" multiple>
                                                <optgroup label="Microscopy">
                                                    <option value="ZN">ZN</option>
                                                    <option value="FM">FM</option>
                                                </optgroup>
                                                <optgroup label="Xpert MTB">
                                                    <option value="MTB/RIF"><?= _translate("MTB/RIF"); ?></option>
                                                    <option value="MTB/RIF ULTRA"><?= _translate("MTB/RIF ULTRA"); ?></option>
                                                    <option value="TB LAM"><?= _translate("TB LAM"); ?></option>
                                                </optgroup>
                                            </select>
                                        </td>
                                    </tr>
                                    <?php if ($general->isLISInstance()) { ?>
                                        <tr>
                                            <th scope="row" class="th-label"><label class="label-control" for="sampleReceivedDate"><?= _translate("Date of Reception"); ?> </label></th>
                                            <td class="td-input">
                                                <input type="text" class="date-time form-control" id="sampleReceivedDate" name="sampleReceivedDate" placeholder="<?= _translate("Please enter date"); ?>" title="Please enter sample receipt date" style="width:100%;" />
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </table>
                            </div>
                        </div>
                        <?php if (_isAllowed('/tb/results/tb-update-result.php') || $_SESSION['accessType'] != 'collection-site') { ?>
                            <?php // if (false) {
                            ?>
                            <div class="box box-primary">
                                <div class="box-body">
                                    <div class="box-header with-border">
                                        <h3 class="box-title"><?= _translate("Results (To be completed in the Laboratory)"); ?> </h3>
                                    </div>
                                    <table aria-describedby="table" class="table" aria-hidden="true" style="width:100%">
                                        <tr>
                                            <th scope="row" class="th-label"><label class="label-control" for="sampleTestedDateTime"><?= _translate("Date of Sample Tested"); ?></label></th>
                                            <td class="td-input">
                                                <input type="text" class="date-time form-control" id="sampleTestedDateTime" name="sampleTestedDateTime" placeholder="<?= _translate("Please enter date"); ?>" title="Please enter sample tested" style="width:100%;" />
                                            </td>
                                            <th scope="row" class="th-label"><label class="label-control" for="sampleDispatchedDate"><?= _translate("Sample Dispatched On"); ?></label></th>
                                            <td class="td-input">
                                                <input type="text" class="date-time form-control" id="sampleDispatchedDate" name="sampleDispatchedDate" placeholder="<?= _translate("Please enter date"); ?>" title="Please choose sample dispatched date" style="width:100%;" />
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row" class="th-label"><label class="label-control" for="testedBy"><?= _translate("Tested By"); ?></label></th>
                                            <td class="td-input">
                                                <select name="testedBy" id="testedBy" class="select2 form-control" title="Please choose approved by" style="width: 100%;">
                                                    <?= $general->generateSelectOptions($userInfo, null, '-- Select --'); ?>
                                                </select>
                                            </td>
                                            <th scope="row" class="th-label"><label class="label-control" for="isSampleRejected"><?= _translate("Is Sample Rejected?"); ?></label></th>
                                            <td class="td-input">
                                                <select class="form-control" name="isSampleRejected" id="isSampleRejected" title="Please select the Is sample rejected?">
                                                    <option value=''> -- <?= _translate("Select"); ?> -- </option>
                                                    <option value="yes"> <?= _translate("Yes"); ?> </option>
                                                    <option value="no"> <?= _translate("No"); ?> </option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr class="show-rejection" style="display:none;">
                                            <th scope="row" class="show-rejection th-label" style="display:none;"><label class="label-control" for="sampleRejectionReason"><?= _translate("Reason for Rejection"); ?><span class="mandatory">*</span></label></th>
                                            <td class="show-rejection td-input" style="display:none;">
                                                <select class="form-control" name="sampleRejectionReason" id="sampleRejectionReason" title="Please select the reason for rejection">
                                                    <option value=''> -- <?= _translate("Select"); ?> -- </option>
                                                    <?php echo $rejectionReason; ?>
                                                </select>
                                            </td>
                                            <th scope="row" class="th-label"><label class="label-control" for="rejectionDate"><?= _translate("Rejection Date"); ?><span class="mandatory">*</span></label></th>
                                            <td class="td-input"><input class="form-control date rejection-date" type="text" name="rejectionDate" id="rejectionDate" placeholder="Select rejection date" title="Please select the rejection date" /></td>
                                        </tr>
                                        <tr class="platform microscopy">
                                            <td colspan="4">
                                                <table aria-describedby="table" class="table table-bordered table-striped" aria-hidden="true">
                                                    <thead>
                                                        <tr>
                                                            <th scope="row" colspan="3" style="text-align: center;"><?= _translate("Microscopy Test Results"); ?></th>
                                                        </tr>
                                                        <tr>
                                                            <th scope="row" style="width: 10%;" class="text-center"><?= _translate("Test #"); ?></th>
                                                            <th scope="row" style="width: 40%;" class="text-center"><?= _translate("Result"); ?></th>
                                                            <th scope="row" style="width: 40%;" class="text-center"><?= _translate("Actual Number"); ?></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="testKitNameTable">
                                                        <?php foreach (range(1, 3) as $no) { ?>
                                                            <tr>
                                                                <td class="text-center"><?php echo $no; ?></td>
                                                                <td>
                                                                    <select class="form-control test-result test-name-table-input" name="testResult[]" id="testResult<?php echo $no; ?>" title="Please select the result for row <?php echo $no; ?>">
                                                                        <?= $general->generateSelectOptions($microscope, null, '-- Select --'); ?>
                                                                    </select>
                                                                </td>
                                                                <td>
                                                                    <input type="text" class="form-control test-name-table-input" id="actualNo<?php echo $no; ?>" name="actualNo[]" placeholder="Enter the actual number" title="Please enter the actual number" />
                                                                </td>
                                                            </tr>
                                                        <?php } ?>
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th></th>
                                            <td></td>
                                            <th scope="row" class="th-label platform xpert"><label class="label-control" for="xPertMTMResult"><?= _translate("Xpert MTB Result"); ?></label></th>
                                            <td class="platform xpert td-input">
                                                <select class="form-control" name="xPertMTMResult" id="xPertMTMResult" title="Please select the Xpert MTM Result">
                                                    <?= $general->generateSelectOptions($tbXPertResults, null, '-- Select --'); ?>
                                                </select>
                                            </td>
                                            <!-- <th scope="row" class="platform lam th-label"><label class="label-control" for="result"><?= _translate("TB LAM Result"); ?></label></th>
                                            <td class="platform lam td-input">
                                                <select class="form-control" name="result" id="result" title="Please select the TB LAM result">
                                                    <?= $general->generateSelectOptions($tbLamResults, null, '-- Select --'); ?>
                                                </select>
                                            </td> -->
                                        </tr>
                                        <tr>
                                            <th scope="row" class="th-label"><label class="label-control" for="reviewedBy"><?= _translate("Reviewed By"); ?></label></th>
                                            <td class="td-input">
                                                <select name="reviewedBy" id="reviewedBy" class="select2 form-control" title="Please choose reviewed by" style="width: 100%;">
                                                    <?= $general->generateSelectOptions($userInfo, null, '-- Select --'); ?>
                                                </select>
                                            </td>
                                            <th scope="row" class="th-label"><label class="label-control" for="reviewedOn"><?= _translate("Reviewed on"); ?></label></th>
                                            <td class="td-input"><input type="text" name="reviewedOn" id="reviewedOn" class="dateTime disabled-field form-control" placeholder="Reviewed on" title="Please enter the Reviewed on" /></td>
                                        </tr>
                                        <tr>
                                            <th scope="row" class="th-label"><label class="label-control" for="approvedBy"><?= _translate("Approved By"); ?></label></th>
                                            <td class="td-input">
                                                <select name="approvedBy" id="approvedBy" class="select2 form-control" title="Please choose approved by" style="width: 100%;">
                                                    <?= $general->generateSelectOptions($userInfo, null, '-- Select --'); ?>
                                                </select>
                                            </td>
                                            <th scope="row" class="th-label"><label class="label-control" for="approvedOn"><?= _translate("Approved on"); ?></label></th>
                                            <td class="td-input"><input type="text" name="approvedOn" id="approvedOn" class="dateTime form-control" placeholder="Approved on" title="Please enter the approved on" /></td>
                                        </tr>

                                    </table>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                    <!-- /.box-body -->
                    <div class="box-footer">
                        <?php if ($arr['tb_sample_code'] == 'auto' || $arr['tb_sample_code'] == 'YY' || $arr['tb_sample_code'] == 'MMYY') { ?>
                            <input type="hidden" name="sampleCodeFormat" id="sampleCodeFormat" value="<?php echo $sFormat; ?>" />
                            <input type="hidden" name="sampleCodeKey" id="sampleCodeKey" value="<?php echo $sKey; ?>" />
                            <input type="hidden" name="saveNext" id="saveNext" />
                        <?php } ?>
                        <a class="btn btn-primary btn-disabled" href="javascript:void(0);" onclick="validateNow();return false;"><?= _translate("Save"); ?></a>
                        <a class="btn btn-primary btn-disabled" href="javascript:void(0);" onclick="validateNow();$('#saveNext').val('next');return false;"><?= _translate("Save and Next"); ?></a>
                        <input type="hidden" name="formId" id="formId" value="1" />
                        <input type="hidden" name="tbSampleId" id="tbSampleId" value="" />
                        <a href="/tb/requests/tb-requests.php" class="btn btn-default"> <?= _translate("Cancel"); ?></a>
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

    function checkNameValidation(tableName, fieldName, obj, fnct, alrt, callback) {
        var removeDots = obj.value.replace(/\./g, "");
        removeDots = removeDots.replace(/\,/g, "");
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

    function getTestingPoints() {
        var labId = $("#labId").val();
        var selectedTestingPoint = null;
        if (labId) {
            $.post("/includes/getTestingPoints.php", {
                    labId: labId,
                    selectedTestingPoint: selectedTestingPoint
                },
                function(data) {
                    if (data != "") {
                        $(".testingPointField").show();
                        $("#testingPoint").html(data);
                    } else {
                        $(".testingPointField").hide();
                        $("#testingPoint").html('');
                    }
                });
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
            $.post("/includes/siteInformationDropdownOptions.php", {
                    pName: pName,
                    testType: 'tb'
                },
                function(data) {
                    if (data != "") {
                        details = data.split("###");
                        $("#facilityId").html(details[0]);
                        $("#district").html(details[1]);
                    }
                });
            generateSampleCode();
        } else if (pName == '') {
            provinceName = true;
            facilityName = true;
            $("#province").html("<?php echo $province; ?>");
            $("#facilityId").html("<?php echo $facility; ?>");
            $("#facilityId").select2("val", "");
            $("#district").html("<option value=''> -- <?= _translate("Select"); ?> -- </option>");
        }
        $.unblockUI();
    }

    function getPatientDistrictDetails(obj) {

        $.blockUI();
        var pName = obj.value;
        if ($.trim(pName) != '') {
            $.post("/includes/siteInformationDropdownOptions.php", {
                    pName: pName,
                    testType: 'tb'
                },
                function(data) {
                    if (data != "") {
                        details = data.split("###");
                        $("#patientDistrict").html(details[1]);
                    }
                });
        } else if (pName == '') {
            $(obj).html("<?php echo $province; ?>");
            $("#patientDistrict").html("<option value=''> -- <?= _translate("Select"); ?> -- </option>");
        }
        $.unblockUI();
    }

    function setPatientDetails(pDetails) {
        patientArray = JSON.parse(pDetails);
        $("#firstName").val(patientArray['firstname']);
        $("#lastName").val(patientArray['lastname']);
        $("#patientGender").val(patientArray['gender']);
        $("#patientAge").val(patientArray['age']);
        $("#dob").val(patientArray['dob']);
        $("#patientId").val(patientArray['patient_id']);
    }

    function generateSampleCode() {
        var pName = $("#province").val();
        var sDate = $("#sampleCollectionDate").val();
        var provinceCode = $("#province").find(":selected").attr("data-code");

        if (pName != '' && sDate != '') {
            $.post("/tb/requests/generate-sample-code.php", {
                    sampleCollectionDate: sDate,
                    provinceCode: provinceCode
                },
                function(data) {
                    var sCodeKey = JSON.parse(data);
                    $("#sampleCode").val(sCodeKey.sampleCode);
                    $("#sampleCodeInText").html(sCodeKey.sampleCodeInText);
                    $("#sampleCodeFormat").val(sCodeKey.sampleCodeFormat);
                    $("#sampleCodeKey").val(sCodeKey.sampleCodeKey);
                });
        }
    }

    function getfacilityDistrictwise(obj) {
        $.blockUI();
        var dName = $("#district").val();
        var cName = $("#facilityId").val();
        if (dName != '') {
            $.post("/includes/siteInformationDropdownOptions.php", {
                    dName: dName,
                    cliName: cName,
                    testType: 'tb'
                },
                function(data) {
                    if (data != "") {
                        details = data.split("###");
                        $("#facilityId").html(details[0]);
                    }
                });
        } else {
            $("#facilityId").html("<option value=''> -- <?= _translate("Select"); ?> -- </option>");
        }
        $.unblockUI();
    }

    function getfacilityProvinceDetails(obj) {
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
                    testType: 'tb'
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


    function validateNow() {
        if ($('#isResultAuthorized').val() != "yes") {
            $('#authorizedBy,#authorizedOn').removeClass('isRequired');
        }
        flag = deforayValidator.init({
            formId: 'addTbRequestForm'
        });
        if (flag) {
            $('.btn-disabled').attr('disabled', 'yes');
            $(".btn-disabled").prop("onclick", null).off("click");
            <?php
            if ($arr['tb_sample_code'] == 'auto' || $arr['tb_sample_code'] == 'YY' || $arr['tb_sample_code'] == 'MMYY') {
            ?>
                insertSampleCode('addTbRequestForm', 'tbSampleId', 'sampleCode', 'sampleCodeKey', 'sampleCodeFormat', 3, 'sampleCollectionDate');
            <?php
            } else {
            ?>
                document.getElementById('addTbRequestForm').submit();
            <?php
            } ?>
        }
    }

    $(document).ready(function() {

        $("#patientGender").change(function() {
            if ($(this).val() == 'male' || $(this).val() == 'unreported') {
                $('.femaleSection').hide();
                $('input[name="breastfeeding"]').prop('checked', false);
                $('input[name="patientPregnant"]').prop('checked', false);
            } else if ($(this).val() == 'female') {
                $('.femaleSection').show();
            }
        });


        $("#labId,#facilityId,#sampleCollectionDate").on('change', function() {
            if ($("#labId").val() != '' && $("#labId").val() == $("#facilityId").val() && $("#sampleDispatchedDate").val() == "") {
                $('#sampleDispatchedDate').datetimepicker("setDate", new Date($('#sampleCollectionDate').datetimepicker('getDate')));
            }
            if ($("#labId").val() != '' && $("#labId").val() == $("#facilityId").val() && $("#sampleReceivedDate").val() == "") {
                // $('#sampleReceivedDate').datetimepicker("setDate", new Date($('#sampleCollectionDate').datetimepicker('getDate')));
            }
        });

        $(".select2").select2();
        $(".select2").select2({
            tags: true
        });
        $('#typeOfPatient').select2({
            placeholder: "Select Type of Patient"
        });
        $('#reasonForTbTest').select2({
            placeholder: "Select Test Reqest Type"
        });
        $('#testTypeRequested').select2({
            placeholder: "Select Type of Examination"
        });

        $('#facilityId').select2({
            placeholder: "Select Clinic/Health Center"
        });
        $('#labTechnician').select2({
            placeholder: "Select Lab Technician"
        });

        $('#patientNationality').select2({
            placeholder: "Select Nationality"
        });

        $('#patientProvince').select2({
            placeholder: "Select Patient State"
        });

        $('#isResultAuthorized').change(function(e) {
            checkIsResultAuthorized();
        });

        $('#sourceOfAlertPOE').change(function(e) {
            if (this.value == 'others') {
                $('.show-alert-poe').show();
                $('#alertPoeOthers').addClass('isRequired');
            } else {
                $('.show-alert-poe').hide();
                $('#alertPoeOthers').removeClass('isRequired');
            }
        });
        <?php if (isset($arr['tb_positive_confirmatory_tests_required_by_central_lab']) && $arr['tb_positive_confirmatory_tests_required_by_central_lab'] == 'yes') { ?>
            $(document).on('change', '.test-result, #result', function(e) {
                checkPostive();
            });
        <?php } ?>
        $("#labId").change(function(e) {
            if ($(this).val() != "") {
                $.post("/tb/requests/get-attributes-data.php", {
                        id: this.value,
                    },
                    function(data) {
                        //console.log(data);
                        if (data != "" && data != false) {
                            _data = jQuery.parseJSON(data);
                            $(".platform").hide();
                            $.each(_data, function(index, value) {
                                $("." + value).show();
                            });
                        }
                    });
            }
        });
    });

    function checkIsResultAuthorized() {
        if ($('#isResultAuthorized').val() == 'no') {
            $('#authorizedBy,#authorizedOn').val('');
            $('#authorizedBy,#authorizedOn').prop('disabled', true);
            $('#authorizedBy,#authorizedOn').addClass('disabled');
            $('#authorizedBy,#authorizedOn').removeClass('isRequired');
        } else {
            $('#authorizedBy,#authorizedOn').prop('disabled', false);
            $('#authorizedBy,#authorizedOn').removeClass('disabled');
            $('#authorizedBy,#authorizedOn').addClass('isRequired');
        }
    }

    function showOther(obj, othersId) {
        if (obj == 'other') {
            $('.' + othersId).show();
        } else {
            $('.' + othersId).hide();
        }
    }

    function checkSubReason(obj, show, opUncheck) {
        $('.reason-checkbox').prop("checked", false);
        if (opUncheck == "followup-uncheck") {
            $('#followUp').val("");
            $("#xPertMTMResult").prop('disabled', false);
        } else {
            $("#xPertMTMResult").prop('disabled', true);
        }
        $('.' + opUncheck).prop("checked", false);
        if ($(obj).prop("checked", true)) {
            $('.' + show).show(300);
            $('.' + show).removeClass('hide-reasons');
            $('.hide-reasons').hide(300);
            $('.' + show).addClass('hide-reasons');
        }
    }
</script>
