<?php

// imported in /eid/results/eid-update-result.php based on country in global config

use App\Registries\ContainerRegistry;
use App\Services\EidService;
use App\Utilities\DateUtility;


$eidObj = ContainerRegistry::get(EidService::class);


$eidResults = $eidObj->getEidResults();

$specimenTypeResult = $eidObj->getEidSampleTypes();
/* To get testing platform names */
$testPlatformResult = $general->getTestingPlatforms('eid');
foreach ($testPlatformResult as $row) {
    $testPlatformList[$row['machine_name']] = $row['machine_name'];
}
// Getting the list of Provinces, Districts and Facilities

$rKey = '';
$pdQuery = "SELECT * FROM geographical_divisions WHERE geo_parent = 0 and geo_status='active'";
if ($_SESSION['accessType'] == 'collection-site') {
    $sampleCodeKey = 'remote_sample_code_key';
    $sampleCode = 'remote_sample_code';
    if (!empty($eidInfo['remote_sample']) && $eidInfo['remote_sample'] == 'yes') {
        $sampleCode = 'remote_sample_code';
    } else {
        $sampleCode = 'sample_code';
    }
    $rKey = 'R';
} else {
    $sampleCodeKey = 'sample_code_key';
    $sampleCode = 'sample_code';
    $rKey = '';
}
//check user exist in user_facility_map table
$chkUserFcMapQry = "Select user_id from user_facility_map where user_id='" . $_SESSION['userId'] . "'";
$chkUserFcMapResult = $db->query($chkUserFcMapQry);
if ($chkUserFcMapResult) {
    $pdQuery = "SELECT DISTINCT gd.geo_name,gd.geo_id,gd.geo_code FROM geographical_divisions as gd JOIN facility_details as fd ON fd.facility_state_id=gd.geo_id JOIN user_facility_map as vlfm ON vlfm.facility_id=fd.facility_id where gd.geo_parent = 0 AND gd.geo_status='active' AND vlfm.user_id='" . $_SESSION['userId'] . "'";
}
$pdResult = $db->query($pdQuery);
$province = "<option value=''> -- Select -- </option>";
foreach ($pdResult as $provinceName) {
    $province .= "<option value='" . $provinceName['geo_name'] . "##" . $provinceName['geo_code'] . "'>" . ($provinceName['geo_name']) . "</option>";
}

$facility = $general->generateSelectOptions($healthFacilities, $eidInfo['facility_id'], '-- Select --');

$eidInfo['mother_treatment'] = isset($eidInfo['mother_treatment']) ? explode(",", (string) $eidInfo['mother_treatment']) : [];


?>


<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1><em class="fa-solid fa-pen-to-square"></em> <?php echo _translate("EARLY INFANT DIAGNOSIS (EID) LABORATORY REQUEST FORM"); ?></h1>
        <ol class="breadcrumb">
            <li><a href="/"><em class="fa-solid fa-chart-pie"></em> <?php echo _translate("Home"); ?></a></li>
            <li class="active"><?php echo _translate("Edit EID Request"); ?></li>
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

                <div class="box-body">
                    <div class="box box-default">
                        <div class="box-body disabledForm">
                            <div class="box-header with-border">
                                <h3 class="box-title">A. CHILD and MOTHER INFORMATION</h3>
                            </div>
                            <div class="box-header with-border">
                                <h3 class="box-title" style="font-size:1em;">To be filled by requesting Clinician/Nurse</h3>
                            </div>
                            <table aria-describedby="table" class="table" aria-hidden="true" style="width:100%">
                                <tr>
                                    <?php if ($_SESSION['accessType'] == 'collection-site') { ?>
                                        <td class="labels"><label for="sampleCode">Sample ID </label></td>
                                        <td>
                                            <span id="sampleCodeInText" style="width:100%;border-bottom:1px solid #333;"><?= htmlspecialchars((string) $eidInfo['sample_code']); ?></span>
                                            <input type="hidden" id="sampleCode" name="sampleCode" value="<?= htmlspecialchars((string) $eidInfo['sample_code']); ?>" />
                                        </td>
                                    <?php } else { ?>
                                        <td class="labels"><label for="sampleCode">Sample ID </label><span class="mandatory">*</span></td>
                                        <td>
                                            <input type="text" readonly value="<?= htmlspecialchars((string) $eidInfo['sample_code']); ?>" class="form-control isRequired" id="sampleCode" name="sampleCode" placeholder="Échantillon ID" title="Please enter échantillon id" style="width:100%;" onchange="" />
                                        </td>
                                    <?php } ?>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td class="labels"><label for="province">Health Facility/POE State </label><span class="mandatory">*</span></td>
                                    <td>
                                        <select class="form-control isRequired" name="province" id="province" title="Please choose province" onchange="getfacilityDetails(this);" style="width:100%;">
                                            <?php echo $province; ?>
                                        </select>
                                    </td>
                                    <td class="labels"><label for="district">Health Facility/POE County </label><span class="mandatory">*</span></td>
                                    <td>
                                        <select class="form-control isRequired" name="district" id="district" title="Please choose district" style="width:100%;" onchange="getfacilityDistrictwise(this);">
                                            <option value=""><?= _translate("-- Select --"); ?> </option>
                                        </select>
                                    </td>
                                    <td class="labels"><label for="facilityId">Health Facility/POE </label><span class="mandatory">*</span></td>
                                    <td>
                                        <select class="form-control isRequired " name="facilityId" id="facilityId" title="Please choose facility" style="width:100%;" onchange="getfacilityProvinceDetails(this);">
                                            <?php echo $facility; ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="labels"><label for="supportPartner">Implementing Partner </label></td>
                                    <td>
                                        <!-- <input type="text" class="form-control" id="supportPartner" name="supportPartner" placeholder="Partenaire d'appui" title="Please enter Partenaire d'appui" style="width:100%;"/> -->
                                        <select class="form-control" name="implementingPartner" id="implementingPartner" title="<?= _translate("Please choose implementing partner"); ?>" style="width:100%;">
                                            <option value=""> -- Select -- </option>
                                            <?php
                                            foreach ($implementingPartnerList as $implementingPartner) {
                                            ?>
                                                <option value="<?php echo base64_encode((string) $implementingPartner['i_partner_id']); ?>" <?php echo ($eidInfo['implementing_partner'] == $implementingPartner['i_partner_id']) ? "selected='selected'" : ""; ?>><?= $implementingPartner['i_partner_name']; ?></option>
                                            <?php } ?>
                                        </select>
                                    </td>
                                    <td class="labels"><label for="fundingSource">Funding Partner</label></td>
                                    <td>
                                        <select class="form-control" name="fundingSource" id="fundingSource" title="Please choose source de financement" style="width:100%;">
                                            <option value=""> -- Select -- </option>
                                            <?php
                                            foreach ($fundingSourceList as $fundingSource) {
                                            ?>
                                                <option value="<?php echo base64_encode((string) $fundingSource['funding_source_id']); ?>" <?php echo ($eidInfo['funding_source'] == $fundingSource['funding_source_id']) ? "selected='selected'" : ""; ?>><?= $fundingSource['funding_source_name']; ?></option>
                                            <?php } ?>
                                        </select>
                                    </td>
                                    <?php if ($general->isSTSInstance() && $_SESSION['accessType'] == 'collection-site') { ?>
                                        <th class="labels" scope="row">Testing Laboratory <span class="mandatory">*</span></th>
                                        <td>
                                            <select name="labId" id="labId" class="select2 form-control isRequired" title="Please select Testing Testing Laboratory" style="width:100%;">
                                                <?= $general->generateSelectOptions($testingLabs, $eidInfo['lab_id'], '-- Select --'); ?>
                                            </select>
                                        </td>
                                    <?php } ?>
                                </tr>
                                <tr>
                                    <th scope="row"><?= _translate('Requesting Clinician Name'); ?></th>
                                    <td> <input type="text" class="form-control" id="clinicianName" name="clinicianName" placeholder="Requesting Clinician Name" title="Please enter request clinician" value="<?php echo $eidInfo['clinician_name']; ?>" /></td>

                                </tr>
                            </table>
                            <br><br>
                            <table aria-describedby="table" class="table" aria-hidden="true" style="width:100%">

                                <tr>
                                    <th scope="row" class="labels" style="width:15% !important"><label for="childId">Exposed Infant Identification <span class="mandatory">*</span> </label></th>
                                    <td style="width:35% !important">
                                        <input type="text" class="form-control isRequired" id="childId" name="childId" placeholder="Exposed Infant Identification (Patient)" title="Please enter Exposed Infant Identification" style="width:100%;" value="<?php echo $eidInfo['child_id']; ?>" onchange="" />
                                    </td>
                                    <th scope="row" class="labels" style="width:15% !important"><label for="childName">Infant name </label></th>
                                    <td style="width:35% !important">
                                        <input type="text" class="form-control " id="childName" name="childName" placeholder="Infant name" title="Please enter Infant Name" style="width:100%;" value="<?= htmlspecialchars((string) $eidInfo['child_name']); ?>" onchange="" />
                                    </td>
                                </tr>
                                <tr>
                                    <th class="labels" scope="row"><label for="childDob">Date of Birth </label></th>
                                    <td>
                                        <input type="text" class="form-control date" id="childDob" name="childDob" placeholder="Date of birth" title="Please enter Date of birth" style="width:100%;" value="<?php echo DateUtility::humanReadableDateFormat($eidInfo['child_dob']) ?>" onchange="" />
                                    </td>
                                    <th class="labels" scope="row"><label for="childGender"><?= _translate("Sex"); ?> </label></th>
                                    <td>
                                        <select class="form-control " name="childGender" id="childGender">
                                            <option value=''> -- Select -- </option>
                                            <option value='male' <?php echo ($eidInfo['child_gender'] == 'male') ? "selected='selected'" : ""; ?>> Male </option>
                                            <option value='female' <?php echo ($eidInfo['child_gender'] == 'female') ? "selected='selected'" : ""; ?>> Female </option>
                                            <option value='unreported' <?php echo ($eidInfo['child_gender'] == 'unreported') ? "selected='selected'" : ""; ?>> Unreported </option>

                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="labels" scope="row">Infant Age (months)</th>
                                    <td><input type="number" max=9 maxlength="1" oninput="this.value=this.value.slice(0,$(this).attr('maxlength'))" class="form-control " id="childAge" name="childAge" placeholder="Age" title="Age" style="width:100%;" onchange="" value="<?= htmlspecialchars((string) $eidInfo['child_age']); ?>" /></td>
                                    <th class="labels" scope="row">Caretaker phone number</th>
                                    <td><input type="text" class="form-control phone-number" id="caretakerPhoneNumber" name="caretakerPhoneNumber" placeholder="Caretaker Phone Number" title="Caretaker Phone Number" style="width:100%;" value="<?= htmlspecialchars((string) $eidInfo['caretaker_phone_number']); ?>" onchange="" /></td>

                                </tr>
                            </table>

                            <br><br>
                            <table aria-describedby="table" class="table" aria-hidden="true" style="width:100%">
                                <tr>
                                    <th scope="row" colspan=4>
                                        <h4>Infant and Mother's Health Information</h4>
                                    </th>
                                </tr>
                                <tr>

                                    <th scope="row" style="width:15% !important" class="labels">Is Mother on ART? <span class="mandatory">*</span> </th>
                                    <td style="width:35% !important">
                                        <select class="form-control isRequired" name="motherTreatment" id="motherTreatment" onchange="showRegimen();">
                                            <option value=''> -- Select -- </option>
                                            <option value="yes" <?php echo ($eidInfo['mother_treatment'] == 'yes') ? "selected='selected'" : ""; ?>> Yes </option>
                                            <option value="no" <?php echo ($eidInfo['mother_treatment'] == 'no') ? "selected='selected'" : ""; ?>> No </option>
                                        </select>
                                    </td>
                                    <th scope="row" class="labels motherRegimen" style="display:none;">Mother's Regimen</th>
                                    <td class="motherRegimen" style="display:none;">
                                        <select class="form-control" id="motherRegimen" name="motherRegimen" title="Please choose Mother's ART Regimen" style="width:100%;" onchange="checkMotherARTRegimenValue();">
                                            <option value="">-- Select --</option>
                                            <?php foreach ($artRegimenResult as $heading) { ?>
                                                <optgroup label="<?= $heading['headings']; ?>">
                                                    <?php
                                                    foreach ($aResult as $regimen) {
                                                        if ($heading['headings'] == $regimen['headings']) {
                                                    ?>
                                                            <option value="<?php echo $regimen['art_code']; ?>" <?php echo ($eidInfo['mother_regimen'] == $regimen['art_code']) ? "selected='selected'" : "" ?>><?php echo $regimen['art_code']; ?></option>
                                                    <?php
                                                        }
                                                    }
                                                    ?>
                                                </optgroup>
                                            <?php }
                                            if ($general->isLISInstance() === false) { ?>
                                                <option value="other">Other</option>
                                            <?php } ?>
                                        </select>
                                        <input type="text" class="form-control newArtRegimen" name="newArtRegimen" id="newArtRegimen" placeholder="ART Regimen" title="Please enter art regimen" style="width:100%;display:none;margin-top:2px;">
                                    </td>

                                </tr>

                                <tr>
                                    <th class="labels" scope="row">Infant Rapid HIV Test Done</th>
                                    <td>
                                        <select class="form-control" name="rapidTestPerformed" id="rapidTestPerformed">
                                            <option value=''> -- Select -- </option>
                                            <option value="yes" <?php echo ($eidInfo['rapid_test_performed'] == 'yes') ? "selected='selected'" : ""; ?>> Yes </option>
                                            <option value="no" <?php echo ($eidInfo['rapid_test_performed'] == 'no') ? "selected='selected'" : ""; ?>> No </option>
                                            <option value="unknown" <?php echo ($eidInfo['rapid_test_performed'] == 'unknown') ? "selected='selected'" : ""; ?>> Unknown </option>
                                        </select>
                                    </td>

                                    <th class="labels" scope="row">If yes, test date :</th>
                                    <td>
                                        <input class="form-control date" type="text" name="rapidtestDate" id="rapidtestDate" placeholder="if yes, test date" value="<?php echo DateUtility::humanReadableDateFormat($eidInfo['rapid_test_date']); ?>">
                                    </td>
                                </tr>
                                <tr>
                                    <th class="labels" scope="row">Rapid Test Result</th>
                                    <td>
                                        <select class="form-control" name="rapidTestResult" id="rapidTestResult">
                                            <option value=''> -- Select -- </option>
                                            <?php foreach ($eidResults as $eidResultKey => $eidResultValue) { ?>
                                                <option value="<?php echo $eidResultKey; ?>" <?php echo ($eidInfo['rapid_test_result'] == $eidResultKey) ? "selected='selected'" : ""; ?>> <?php echo $eidResultValue; ?> </option>
                                            <?php } ?>

                                        </select>
                                    </td>

                                    <th class="labels" scope="row">Infant still breastfeeding?</th>
                                    <td>
                                        <select class="form-control" name="hasInfantStoppedBreastfeeding" id="hasInfantStoppedBreastfeeding">
                                            <option value=''> -- Select -- </option>
                                            <option value="yes" <?php echo ($eidInfo['has_infant_stopped_breastfeeding'] == 'yes') ? "selected='selected'" : ""; ?>> Yes </option>
                                            <option value="no" <?php echo ($eidInfo['has_infant_stopped_breastfeeding'] == 'no') ? "selected='selected'" : ""; ?>> No </option>
                                            <option value="unknown" <?php echo ($eidInfo['has_infant_stopped_breastfeeding'] == 'unknown') ? "selected='selected'" : ""; ?>> Unknown </option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="labels" scope="row">Age (months) breastfeeding stopped :</th>
                                    <td>
                                        <input type="number" class="form-control" style="max-width:200px;display:inline;" placeholder="Age (months) breastfeeding stopped" type="text" name="ageBreastfeedingStopped" id="ageBreastfeedingStopped" value="<?php echo $eidInfo['age_breastfeeding_stopped_in_months'] ?>" />
                                    </td>

                                    <th class="labels" scope="row">PCR test performed on child before :</th>
                                    <td>
                                        <select class="form-control" name="pcrTestPerformedBefore" id="pcrTestPerformedBefore">
                                            <option value=''> -- Select -- </option>
                                            <option value="yes" <?php echo ($eidInfo['pcr_test_performed_before'] == 'yes') ? "selected='selected'" : ""; ?>> Yes </option>
                                            <option value="no" <?php echo ($eidInfo['pcr_test_performed_before'] == 'no') ? "selected='selected'" : ""; ?>> No </option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="labels" scope="row">Previous PCR Test Result :</th>
                                    <td>
                                        <select class="form-control" name="prePcrTestResult" id="prePcrTestResult">
                                            <option value=''> -- Select -- </option>
                                            <option value="positive" <?php echo ($eidInfo['previous_pcr_result'] == 'positive') ? "selected='selected'" : ""; ?>> Positive </option>
                                            <option value="negative" <?php echo ($eidInfo['previous_pcr_result'] == 'negative') ? "selected='selected'" : ""; ?>> Negative </option>
                                            <option value="indeterminate" <?php echo ($eidInfo['previous_pcr_result'] == 'Indeterminate') ? "selected='selected'" : ""; ?>> Inderterminate </option>
                                        </select>
                                    </td>

                                    <th class="labels" scope="row">Previous PCR test date :</th>
                                    <td>
                                        <input class="form-control date" type="text" name="previousPCRTestDate" id="previousPCRTestDate" placeholder="if yes, test date" value="<?php echo DateUtility::humanReadableDateFormat($eidInfo['last_pcr_date']); ?>" />
                                    </td>
                                </tr>
                                <tr>
                                    <th class="labels" scope="row">Reason for 2nd PCR :</th>
                                    <td>
                                        <select class="form-control" name="pcrTestReason" id="pcrTestReason">
                                            <option value=''> -- Select -- </option>
                                            <option value="Confirmation of positive first EID PCR test result" <?php echo ($eidInfo['reason_for_pcr'] == 'Confirmation of positive first EID PCR test result') ? "selected='selected'" : ""; ?>> Confirmation of positive first EID PCR test result </option>
                                            <option value="Repeat EID PCR test 6 weeks after stopping breastfeeding for children < 9 months" <?php echo ($eidInfo['reason_for_pcr'] == 'Repeat EID PCR test 6 weeks after stopping breastfeeding for children < 9 months') ? "selected='selected'" : ""; ?>> Repeat EID PCR test 6 weeks after stopping breastfeeding for children < 9 months </option>
                                            <option value="Positive HIV rapid test result at 9 months or later"> Positive HIV rapid test result at 9 months or later </option>
                                            <option value="Other" <?php echo ($eidInfo['reason_for_pcr'] == 'Other') ? "selected='selected'" : ""; ?>> Other </option>
                                        </select>
                                    </td>
                                    <th scope="row"></th>
                                    <td></td>
                                </tr>
                            </table>

                            <br><br>
                            <table aria-describedby="table" class="table" aria-hidden="true">
                                <tr>
                                    <th scope="row" colspan=4>
                                        <h4>Sample Information</h4>
                                    </th>
                                </tr>
                                <tr>
                                    <th scope="row" class="labels" style="width:15% !important">Sample Collection Date <span class="mandatory">*</span> </th>
                                    <td style="width:35% !important;">
                                        <input class="form-control dateTime isRequired" type="text" name="sampleCollectionDate" id="sampleCollectionDate" placeholder="Sample Collection Date" value="<?php echo DateUtility::humanReadableDateFormat($eidInfo['sample_collection_date']); ?>" />
                                    </td>
                                    <th scope="row" class="labels" style="width:15% !important">Sample Type <span class="mandatory">*</span> </th>
                                    <td style="width:35% !important;">
                                        <select name="specimenType" id="specimenType" class="form-control isRequired" title="Please choose specimen type" style="width:100%">
                                            <?php echo $general->generateSelectOptions($specimenTypeResult, $eidInfo['specimen_type'], '-- Select --'); ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="labels" scope="row">Requesting Officer</th>
                                    <td>
                                        <input class="form-control" type="text" name="sampleRequestorName" id="sampleRequestorName" placeholder="Requesting Officer" value="<?= htmlspecialchars((string) $eidInfo['sample_requestor_name']); ?>" />
                                    </td>
                                    <th class="labels" scope="row">Sample Requestor Phone</th>
                                    <td>
                                        <input class="form-control" type="text" name="sampleRequestorPhone" id="sampleRequestorPhone" placeholder="Requesting Officer Phone" value="<?= htmlspecialchars((string) $eidInfo['sample_requestor_phone']); ?>" />
                                    </td>
                                </tr>
                                <tr>
                                    <th class="labels" scope="row">Sample Received Date (at Testing Lab) <span class="mandatory">*</span></th>
                                    <td>
                                        <input type="text" class="form-control dateTime" id="sampleReceivedDate" name="sampleReceivedDate" placeholder="<?= _translate("Please enter date"); ?>" title="Please enter sample receipt date" value="<?php echo DateUtility::humanReadableDateFormat($eidInfo['sample_received_at_lab_datetime']) ?>" onchange="" style="width:100%;" />
                                    </td>
                                    <th scope="row"></th>
                                    <td></td>
                                </tr>
                                <tr>
                                    <th scope="row" class="labels">Sample Dispatcher Name</th>
                                    <td>
                                        <input class="form-control" type="text" name="sampleDispatcherName" id="sampleDispatcherName" placeholder="Sample Dispatcher Name" value="<?= htmlspecialchars((string) $eidInfo['sample_dispatcher_name']); ?>" />
                                    </td>
                                    <th scope="row" class="labels">Sample Dispatcher Phone</th>
                                    <td>
                                        <input class="form-control phone-number" type="text" name="sampleDispatcherPhone" id="sampleDispatcherPhone" placeholder="Sample Dispatcher Phone" value="<?= htmlspecialchars((string) $eidInfo['sample_dispatcher_phone']); ?>" />
                                    </td>

                                </tr>
                            </table>
                        </div>
                    </div>

                    <form class="form-horizontal" method="post" name="editEIDRequestForm" id="editEIDRequestForm" autocomplete="off" action="eid-update-result-helper.php">
                        <div class="box box-primary">
                            <div class="box-body">
                                <div class="box-header with-border">
                                    <h3 class="box-title">B. Reserved for Laboratory Use </h3>
                                </div>
                                <table aria-describedby="table" class="table" aria-hidden="true" style="width:100%">
                                    <tr>
                                        <th class="labels" scope="row">Testing Laboratory <span class="mandatory">*</span></th>
                                        <td>
                                            <select name="labId" id="labId" class="select2 form-control isRequired" title="Please select the Testing Laboratory" style="width:100%;">
                                                <?= $general->generateSelectOptions($testingLabs, $eidInfo['lab_id'], '-- Select --'); ?>
                                            </select>
                                        </td>

                                        <th class="labels" scope="row">Testing Platform<span class="mandatory">*</span></th>
                                        <td><select name="eidPlatform" id="eidPlatform" class="form-control isRequired result-optional" title="Please select the testing platform">
                                                <?= $general->generateSelectOptions($testPlatformList, $eidInfo['eid_test_platform'], '-- Select --'); ?>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="labels" scope="row">Machine used to test<span class="mandatory">*</span> </th>
                                        <td><select name="machineName" id="machineName" class="form-control result-optional isRequired" title="Please select the machine name" ">
                                                <?= $general->generateSelectOptions($machine, $eidInfo['import_machine_name'], '-- Select --'); ?>
                                        </td>


                                        <th class=" labels" scope="row">Is Sample Rejected? <span class=" mandatory">*</span></th>
                                        <td>
                                            <select class="form-control isRequired" name="isSampleRejected" id="isSampleRejected" title="Please select if sample is rejected or not">
                                                <option value=''> -- Select -- </option>
                                                <option value="yes" <?php echo ($eidInfo['is_sample_rejected'] == 'yes') ? "selected='selected'" : ""; ?>> Yes </option>
                                                <option value="no" <?php echo ($eidInfo['is_sample_rejected'] == 'no') ? "selected='selected'" : ""; ?>> No </option>
                                            </select>
                                        </td>


                                    </tr>
                                    <tr class="show-rejection rejected" style="display:none;">
                                        <th scope="row" class="rejected labels" style="display: none;">Reason for Rejection</th>
                                        <td class="rejected" style="display: none;">
                                            <select class="form-control" name="sampleRejectionReason" id="sampleRejectionReason" title="Please choose the rejection reason" onchange="checkRejectionReason();">
                                                <option value="">-- Select --</option>
                                                <?php foreach ($rejectionTypeResult as $type) { ?>
                                                    <optgroup label="<?php echo strtoupper((string) $type['rejection_type']); ?>">
                                                        <?php
                                                        foreach ($rejectionResult as $reject) {
                                                            if ($type['rejection_type'] == $reject['rejection_type']) { ?>
                                                                <option value="<?php echo $reject['rejection_reason_id']; ?>" <?php echo ($eidInfo['reason_for_sample_rejection'] == $reject['rejection_reason_id']) ? 'selected="selected"' : ''; ?>><?= $reject['rejection_reason_name']; ?></option>
                                                        <?php }
                                                        } ?>
                                                    </optgroup>
                                                <?php } ?>
                                                <option value="other">Other (Please Specify) </option>
                                            </select>
                                            <input type="text" class="form-control newRejectionReason" name="newRejectionReason" id="newRejectionReason" placeholder="Rejection Reason" title="Please enter rejection reason" style="width:100%;display:none;margin-top:2px;">

                                        </td>
                                        <td class="rejected labels" style="display: none;">Recommended Corrective Action</td>
                                        <td class="rejected" style="display: none;">
                                            <select name="correctiveAction" id="correctiveAction" class="form-control" title="Please choose Recommended corrective action">
                                                <option value="">-- Select --</option>
                                                <?php foreach ($correctiveActions as $action) {
                                                ?>
                                                    <option value="<?php echo $action['recommended_corrective_action_id']; ?>" <?php echo ($eidInfo['recommended_corrective_action'] == $action['recommended_corrective_action_id']) ? 'selected="selected"' : ''; ?>><?= $action['recommended_corrective_action_name']; ?></option>
                                                <?php }
                                                ?>
                                            </select>
                                        </td>

                                    </tr>
                                    <tr class="show-rejection rejected" style="display:none;">
                                        <th class="labels" scope="row">Rejection Date<span class="mandatory">*</span></th>
                                        <td><input value="<?php echo DateUtility::humanReadableDateFormat($eidInfo['rejection_on']); ?>" class="form-control date rejection-date isRequired" type="text" name="rejectionDate" id="rejectionDate" placeholder="Select Rejection Date" title="Please enter sample rejection date" /></td>
                                    </tr>
                                    <tr>
                                        <th class="labels" style="width:25%;">Sample Test Date<span class="mandatory">*</span></th>
                                        <td style="width:25%;">
                                            <input type="text" class="form-control dateTime isRequired" id="sampleTestedDateTime" name="sampleTestedDateTime" placeholder="<?= _translate("Please enter date"); ?>" title="Please enter the Sample Tested Date and Time" <?php echo $labFieldDisabled; ?> onchange="" value="<?= DateUtility::humanReadableDateFormat($eidInfo['sample_tested_datetime']) ?>" style="width:100%;" />
                                        </td>
                                        <th scope="row">Result<span class="mandatory">*</span></th>
                                        <td>
                                            <select class="form-control isRequired" name="result" id="result" data-result="<?= $eidInfo['result']; ?>" title="Please enter the EID Test Result">
                                                <option value=''> -- Select -- </option>
                                                <?php foreach ($eidResults as $eidResultKey => $eidResultValue) { ?>
                                                    <option value="<?php echo $eidResultKey; ?>" <?php echo ($eidInfo['result'] == $eidResultKey) ? "selected='selected'" : ""; ?>> <?php echo $eidResultValue; ?> </option>
                                                <?php } ?>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="labels" scope="row">Tested By<span class="mandatory">*</span></th>
                                        <td>
                                            <select name="testedBy" id="testedBy" class="select2 form-control isRequired" title="Please choose tested by">
                                                <?= $general->generateSelectOptions($userInfo, $eidInfo['tested_by'], '-- Select --'); ?>
                                            </select>
                                        </td>
                                        <th class="labels" scope="row">Reviewed By <span class="mandatory review-approve-span" style="display: <?php echo ($eidInfo['is_sample_rejected'] != '') ? 'inline' : 'none'; ?>;">*</span></th>
                                        <td>
                                            <select name="reviewedBy" id="reviewedBy" class="select2 form-control isRequired" title="Please choose reviewed by" style="width: 100%;">
                                                <?= $general->generateSelectOptions($userInfo, $eidInfo['result_reviewed_by'], '-- Select --'); ?>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>

                                        <th class="labels" scope="row">Reviewed on <span class="mandatory review-approve-span" style="display: <?php echo ($eidInfo['is_sample_rejected'] != '') ? 'inline' : 'none'; ?>;">*</span></th>
                                        <td><input type="text" value="<?= DateUtility::humanReadableDateFormat($eidInfo['result_reviewed_datetime']); ?>" name="reviewedOn" id="reviewedOn" class="dateTime disabled-field form-control isRequired" placeholder="Reviewed on" title="Please enter reviewed on" /></td>
                                        <th class="labels" scope="row">Approved By <span class="mandatory review-approve-span" style="display: <?php echo ($eidInfo['is_sample_rejected'] != '') ? 'inline' : 'none'; ?>;">*</span></th>
                                        <td>
                                            <select name="approvedBy" id="approvedBy" class="form-control labSection isRequired" title="Please choose approved by">
                                                <?= $general->generateSelectOptions($userInfo, $eidInfo['result_approved_by'], '-- Select --'); ?>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>

                                        <th class="labels" style="width:25%;">Approved On <span class="mandatory review-approve-span" style="display: <?php echo ($eidInfo['is_sample_rejected'] != '') ? 'inline' : 'none'; ?>;">*</span></th>
                                        <td style="width:25%;">
                                            <input type="text" value="<?= DateUtility::humanReadableDateFormat($eidInfo['result_approved_datetime']); ?>" class="form-control dateTime isRequired" id="approvedOnDateTime" name="approvedOnDateTime" placeholder="<?= _translate("Please enter date"); ?>" title="Please enter approved on" <?php echo $labFieldDisabled; ?> style="width:100%;" />
                                        </td>
                                        <th class="labels" scope="row">Results Dispatched Date<span class="mandatory">*</span></th>
                                        <td>
                                            <input type="text" value="<?php echo $eidInfo['result_dispatched_datetime']; ?>" class="form-control dateTime isRequired" id="resultDispatchedOn" name="resultDispatchedOn" placeholder="Result Dispatch Date" title="Please select result dispatched date" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="labels" style="width:25%;">Lab Tech. Comments <span class="mandatory">*</span></th>
                                        <td style="width:25%;">
                                            <textarea class="form-control isRequired" id="labTechCmt" name="labTechCmt" <?php echo $labFieldDisabled; ?> style="width:100%;" placeholder="Comments from the Lab Technician " title="Please Comments from the Lab Technician "><?= htmlspecialchars((string) $eidInfo['lab_tech_comments']); ?></textarea>
                                        </td>
                                        <th scope="row" class="change-reason" style="display: none;">Reason for Changing <span class="mandatory">*</span></th>
                                        <td class="change-reason" style="display: none;"><textarea name="reasonForChanging" id="reasonForChanging" class="form-control isRequired" placeholder="Enter the reason for changing" title="Please enter the reason for changing"></textarea></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                </div>
                <!-- /.box-body -->
                <div class="box-footer">
                    <?php if ($arr['sample_code'] == 'auto' || $arr['sample_code'] == 'YY' || $arr['sample_code'] == 'MMYY') { ?>
                        <input type="hidden" name="sampleCodeFormat" id="sampleCodeFormat" value="<?php echo $sFormat; ?>" />
                        <input type="hidden" name="sampleCodeKey" id="sampleCodeKey" value="<?php echo $sKey; ?>" />
                    <?php } ?>
                    <a class="btn btn-primary" href="javascript:void(0);" onclick="validateNow();return false;">Save</a>
                    <input type="hidden" class="" id="sampleReceivedDate" name="sampleReceivedDate" placeholder="<?= _translate("Please enter date"); ?>" title="Please enter sample receipt date" value="<?php echo DateUtility::humanReadableDateFormat($eidInfo['sample_received_at_lab_datetime']) ?>" />
                    <input type="hidden" name="revised" id="revised" value="no" />
                    <input type="hidden" name="formId" id="formId" value="1" />
                    <input type="hidden" name="eidSampleId" id="eidSampleId" value="<?php echo ($eidInfo['eid_id']); ?>" />
                    <input type="hidden" name="sampleCodeTitle" id="sampleCodeTitle" value="<?php echo $arr['sample_code']; ?>" />
                    <input type="hidden" id="sampleCode" name="sampleCode" value="<?= htmlspecialchars((string) $eidInfo['sample_code']); ?>" />
                    <input type="hidden" id="childId" name="childId" value="<?php echo $eidInfo['child_id']; ?>" />
                    <a href="/eid/results/eid-manual-results.php" class="btn btn-default"> Cancel</a>
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
    changeProvince = true;
    changeFacility = true;
    provinceName = true;
    facilityName = true;
    machineName = true;

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
                    testType: 'eid'
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
            $("#province").html("<?php echo $province; ?>");
            $("#facilityId").html("<?php echo $facility; ?>");
            $("#facilityId").select2("val", "");
            $("#district").html("<option value=''> -- Sélectionner -- </option>");
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
                    testType: 'eid'
                },
                function(data) {
                    if (data != "") {
                        details = data.split("###");
                        $("#facilityId").html(details[0]);
                    }
                });
        } else {
            $("#facilityId").html("<option value=''> -- Sélectionner -- </option>");
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
                    testType: 'eid'
                },
                function(data) {
                    if (data != "") {
                        details = data.split("###");
                        $("#province").html(details[0]);
                        $("#district").html(details[1]);
                        //$("#clinicianName").val(details[2]);
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
        flag = deforayValidator.init({
            formId: 'editEIDRequestForm'
        });
        if (flag) {
            document.getElementById('editEIDRequestForm').submit();
        }
    }

    function updateMotherViralLoad() {
        var motherVl = $("#motherViralLoadCopiesPerMl").val();
        var motherVlText = $("#motherViralLoadText").val();
        if (motherVlText != '') {
            $("#motherViralLoadCopiesPerMl").val('');
        }
    }



    $(document).ready(function() {

        $('#result').change(function(e) {
            if ($(this).data("result") != "" && $(this).data("result") != $(this).val()) {
                $('.change-reason').show();
                $('#reasonForChanging').addClass('isRequired');
            } else {
                $('.change-reason').hide();
                $('#reasonForChanging').removeClass('isRequired');
            }
        });

        $('.disabledForm input, .disabledForm select , .disabledForm textarea ').attr('disabled', true);
        $('#labId').select2({
            width: '100%',
            placeholder: "Select Testing Lab"
        });
        $('#reviewedBy').select2({
            width: '100%',
            placeholder: "Select Reviewed By"
        });
        $('#testedBy').select2({
            width: '100%',
            placeholder: "Select Tested By"
        });
        $('#approvedBy').select2({
            width: '100%',
            placeholder: "Select Approved By"
        });
        $('#facilityId').select2({
            placeholder: "Select Clinic/Health Center"
        });
        $('#district').select2({
            placeholder: "District"
        });
        $('#province').select2({
            placeholder: "Province"
        });
        getfacilityProvinceDetails($("#facilityId").val());
        <?php if (isset($eidInfo['mother_treatment']) && in_array('Other', $eidInfo['mother_treatment'])) { ?>
            $('#motherTreatmentOther').prop('disabled', false);
        <?php } ?>

        <?php if (!empty($eidInfo['mother_vl_result'])) { ?>
            updateMotherViralLoad();
        <?php } ?>

        $("#motherViralLoadCopiesPerMl").on("change keyup paste", function() {
            var motherVl = $("#motherViralLoadCopiesPerMl").val();
            //var motherVlText = $("#motherViralLoadText").val();
            if (motherVl != '') {
                $("#motherViralLoadText").val('');
            }
        });

        $("#eidPlatform").on("change", function() {
            if (this.value != "") {
                getMachine(this.value);
            }
        });
        getMachine($("#eidPlatform").val());
    });

    function getMachine(value) {
        $.post("/instruments/get-machine-names-by-instrument.php", {
                instrumentId: value,
                machine: <?php echo !empty($eidInfo['import_machine_name']) ? $eidInfo['import_machine_name'] : '""'; ?>,
                testType: 'eid'
            },
            function(data) {
                $('#machineName').html('');
                if (data != "") {
                    $('#machineName').append(data);
                }
            });
    }

    function checkRejectionReason() {
        var rejectionReason = $("#sampleRejectionReason").val();
        if (rejectionReason == "other") {
            $("#newRejectionReason").show();
            $("#newRejectionReason").addClass("isRequired");
        } else {
            $("#newRejectionReason").hide();
            $("#newRejectionReason").removeClass("isRequired");
            $('#newRejectionReason').val("");
        }
    }
</script>
