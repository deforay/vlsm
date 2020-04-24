<?php

// imported in /covid-19/results/covid-19-update-result.php based on country in global config

ob_start();

//Funding source list
$fundingSourceQry = "SELECT * FROM r_funding_sources WHERE funding_source_status='active' ORDER BY funding_source_name ASC";
$fundingSourceList = $db->query($fundingSourceQry);

//Implementing partner list
$implementingPartnerQry = "SELECT * FROM r_implementation_partners WHERE i_partner_status='active' ORDER BY i_partner_name ASC";
$implementingPartnerList = $db->query($implementingPartnerQry);


$covid19Results = $general->getCovid19Results();


// Getting the list of Provinces, Districts and Facilities

$rKey = '';
$pdQuery = "SELECT * FROM province_details";
if ($sarr['user_type'] == 'remoteuser') {
    $sampleCodeKey = 'remote_sample_code_key';
    $sampleCode = 'remote_sample_code';
    //check user exist in user_facility_map table
    $chkUserFcMapQry = "SELECT user_id FROM vl_user_facility_map where user_id='" . $_SESSION['userId'] . "'";
    $chkUserFcMapResult = $db->query($chkUserFcMapQry);
    if ($chkUserFcMapResult) {
        $pdQuery = "SELECT * FROM province_details as pd JOIN facility_details as fd ON fd.facility_state=pd.province_name JOIN vl_user_facility_map as vlfm ON vlfm.facility_id=fd.facility_id where user_id='" . $_SESSION['userId'] . "' group by province_name";
    }
    $rKey = 'R';
} else {
    $sampleCodeKey = 'sample_code_key';
    $sampleCode = 'sample_code';
    $rKey = '';
}
$pdResult = $db->query($pdQuery);
$province = "";
$province .= "<option value=''> -- Select -- </option>";
foreach ($pdResult as $provinceName) {
    $province .= "<option value='" . $provinceName['province_name'] . "##" . $provinceName['province_code'] . "'>" . ucwords($provinceName['province_name']) . "</option>";
}
//$facility = "";
$facility = "<option value=''> -- Select -- </option>";
foreach ($fResult as $fDetails) {
    $selected = "";
    if ($covid19Info['facility_id'] == $fDetails['facility_id']) {
        $selected = " selected='selected' ";
    }
    $facility .= "<option value='" . $fDetails['facility_id'] . "' $selected>" . ucwords(addslashes($fDetails['facility_name'])) . "</option>";
}


?>


<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1><i class="fa fa-edit"></i> COVID-19 LABORATORY REQUEST FORM</h1>
        <ol class="breadcrumb">
            <li><a href="/"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Covid-19 Request</li>
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

                <div class="box-body">
                    <div class="box box-default disabledForm">
                        <div class="box-body">
                            <div class="box-header with-border">
                                <h3 class="box-title">SITE INFORMATION</h3>
                            </div>
                            <div class="box-header with-border">
                                <h3 class="box-title" style="font-size:1em;">To be filled by requesting Clinician/Nurse</h3>
                            </div>
                            <table class="table" style="width:100%">
                                <tr>
                                    <?php if ($sarr['user_type'] == 'remoteuser') { ?>
                                        <td><label for="sampleCode">Sample ID </label></td>
                                        <td>
                                            <span id="sampleCodeInText" style="width:100%;border-bottom:1px solid #333;"><?php echo ($sCode != '') ? $sCode : $covid19Info[$sampleCode]; ?></span>
                                            <input type="hidden" class="<?php echo $sampleClass; ?>" id="sampleCode" name="sampleCode" value="<?php echo ($sCode != '') ? $sCode : $covid19Info[$sampleCode]; ?>" />
                                        </td>
                                    <?php } else { ?>
                                        <td><label for="sampleCode">Sample ID </label><span class="mandatory">*</span></td>
                                        <td>
                                            <input type="text" readonly value="<?php echo $covid19Info['sample_code'] ?>" class="form-control isRequired" id="sampleCode" name="sampleCode" placeholder="Échantillon ID" title="Please enter échantillon id" style="width:100%;" onchange="" />
                                        </td>
                                    <?php } ?>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td><label for="province">Province </label><span class="mandatory">*</span></td>
                                    <td>
                                        <select class="form-control isRequired" name="province" id="province" title="Please choose province" onchange="getfacilityDetails(this);" style="width:100%;">
                                            <?php echo $province; ?>
                                        </select>
                                    </td>
                                    <td><label for="district">District </label><span class="mandatory">*</span></td>
                                    <td>
                                        <select class="form-control isRequired" name="district" id="district" title="Please choose district" style="width:100%;" onchange="getfacilityDistrictwise(this);">
                                            <option value=""> -- Select -- </option>
                                        </select>
                                    </td>
                                    <td><label for="facilityId">Health Facility </label><span class="mandatory">*</span></td>
                                    <td>
                                        <select class="form-control isRequired " name="facilityId" id="facilityId" title="Please choose service provider" style="width:100%;" onchange="getfacilityProvinceDetails(this);">
                                            <?php echo $facility; ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td><label for="supportPartner">Implementing Partner </label></td>
                                    <td>

                                        <select class="form-control" name="implementingPartner" id="implementingPartner" title="Please choose implementing partner" style="width:100%;">
                                            <option value=""> -- Select -- </option>
                                            <?php
                                            foreach ($implementingPartnerList as $implementingPartner) {
                                            ?>
                                                <option value="<?php echo ($implementingPartner['i_partner_id']); ?>" <?php echo ($covid19Info['implementing_partner'] == $implementingPartner['i_partner_id']) ? "selected='selected'" : ""; ?>><?php echo ucwords($implementingPartner['i_partner_name']); ?></option>
                                            <?php } ?>
                                        </select>
                                    </td>
                                    <td><label for="fundingSource">Funding Partner</label></td>
                                    <td>
                                        <select class="form-control" name="fundingSource" id="fundingSource" title="Please choose funding source" style="width:100%;">
                                            <option value=""> -- Select -- </option>
                                            <?php
                                            foreach ($fundingSourceList as $fundingSource) {
                                            ?>
                                                <option value="<?php echo ($fundingSource['funding_source_id']); ?>" <?php echo ($covid19Info['funding_source'] == $fundingSource['funding_source_id']) ? "selected='selected'" : ""; ?>><?php echo ucwords($fundingSource['funding_source_name']); ?></option>
                                            <?php } ?>
                                        </select>
                                    </td>
                                    <?php if ($sarr['user_type'] == 'remoteuser') { ?>
                                        <!-- <tr> -->
                                        <td><label for="labId">Lab Name <span class="mandatory">*</span></label> </td>
                                        <td>
                                            <select name="labId" id="labId" class="form-control isRequired" title="Lab Name" style="width:100%;">
                                                <option value=""> -- Select -- </option>
                                                <?php foreach ($lResult as $labName) { ?>
                                                    <option value="<?php echo $labName['facility_id']; ?>" <?php echo ($covid19Info['lab_id'] == $labName['facility_id']) ? "selected='selected'" : ""; ?>><?php echo ucwords($labName['facility_name']); ?></option>
                                                <?php } ?>
                                            </select>
                                        </td>
                                        <!-- </tr> -->
                                    <?php } ?>
                                </tr>
                            </table>
                            <br>
                            <hr style="border: 1px solid #ccc;">

                            <div class="box-header with-border">
                                <h3 class="box-title">PATIENT INFORMATION</h3>
                            </div>
                            <table class="table" style="width:100%">

                                <tr>
                                    <th style="width:15% !important"><label for="firstName">First Name <span class="mandatory">*</span> </label></th>
                                    <td style="width:35% !important">
                                        <input type="text" class="form-control isRequired" id="firstName" name="firstName" placeholder="First Name" title="Please enter patient first name" style="width:100%;" value="<?php echo $covid19Info['patient_name']; ?>" />
                                    </td>
                                    <th style="width:15% !important"><label for="lastName">Last name </label></th>
                                    <td style="width:35% !important">
                                        <input type="text" class="form-control " id="lastName" name="lastName" placeholder="Last name" title="Please enter patient last name" style="width:100%;" value="<?php echo $covid19Info['patient_surname']; ?>" />
                                    </td>
                                </tr>
                                <tr>
                                    <th style="width:15% !important"><label for="patientId">Patient ID <span class="mandatory">*</span> </label></th>
                                    <td style="width:35% !important">
                                        <input type="text" class="form-control isRequired" id="patientId" name="patientId" placeholder="Patient Identification" title="Please enter Patient ID" style="width:100%;" value="<?php echo $covid19Info['patient_id']; ?>" />
                                    </td>
                                    <th><label for="patientDob">Date of Birth <span class="mandatory">*</span> </label></th>
                                    <td>
                                        <input type="text" class="form-control isRequired" id="patientDob" name="patientDob" placeholder="Date of Birth" title="Please enter Date of birth" style="width:100%;" onchange="calculateAgeInYears();" value="<?php echo $general->humanDateFormat($covid19Info['patient_dob']); ?>" />
                                    </td>
                                </tr>
                                <tr>
                                    <th>Patient Age (years)</th>
                                    <td><input type="number" max="150" maxlength="3" oninput="this.value=this.value.slice(0,$(this).attr('maxlength'))" class="form-control " id="patientAge" name="patientAge" placeholder="Patient Age (in years)" title="Patient Age" style="width:100%;" value="<?php echo $covid19Info['patient_age']; ?>" /></td>
                                    <th><label for="patientGender">Gender <span class="mandatory">*</span> </label></th>
                                    <td>
                                        <select class="form-control isRequired" name="patientGender" id="patientGender">
                                            <option value=''> -- Select -- </option>
                                            <option value='male' <?php echo ($covid19Info['patient_gender'] == 'male') ? "selected='selected'" : ""; ?>> Male </option>
                                            <option value='female' <?php echo ($covid19Info['patient_gender'] == 'female') ? "selected='selected'" : ""; ?>> Female </option>
                                            <option value='other' <?php echo ($covid19Info['patient_gender'] == 'other') ? "selected='selected'" : ""; ?>> Other </option>

                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Phone number</th>
                                    <td><input type="text" class="form-control " id="patientPhoneNumber" name="patientPhoneNumber" placeholder="Patient Phone Number" title="Patient Phone Number" style="width:100%;" value="<?php echo $covid19Info['patient_phone_number']; ?>" /></td>

                                    <th>Patient address</th>
                                    <td><textarea class="form-control " id="patientAddress" name="patientAddress" placeholder="Patient Address" title="Patient Address" style="width:100%;" onchange=""><?php echo $covid19Info['patient_address']; ?></textarea></td>

                                </tr>


                            </table>



                            <br><br>
                            <table class="table">
                                <tr>
                                    <th colspan=4 style="border-top:#ccc 2px solid;">
                                        <h4>SPECIMEN INFORMATION</h4>
                                    </th>
                                </tr>
                                <tr>
                                    <td colspan=4>
                                        <ul>
                                            <li>All specimens collected should be regarded as potentially infectious and you <u>MUST CONTACT</u> the reference laboratory before sending samples.</li>
                                            <li>All samples must be sent in accordance with category B transport requirements.</li>
                                        </ul>

                                    </td>
                                </tr>
                                <tr>
                                    <th style="width:15% !important">Sample Collection Date <span class="mandatory">*</span> </th>
                                    <td style="width:35% !important;">
                                        <input class="form-control isRequired" type="text" name="sampleCollectionDate" id="sampleCollectionDate" placeholder="Sample Collection Date" value="<?php echo ($covid19Info['sample_collection_date']); ?>" />
                                    </td>
                                    <th>Specimen Type <span class="mandatory">*</span></th>
                                    <td>
                                        <select name="specimenType" id="specimenType" class="form-control isRequired" title="Please choose specimen type" style="width:100%">
                                            <option value="">-- Select --</option>
                                            <?php foreach ($specimenTypeResult as $name) { ?>
                                                <option value="<?php echo $name['sample_id']; ?>" <?php echo ($covid19Info['specimen_type'] == $name['sample_id']) ? "selected='selected'" : ""; ?>><?php echo ucwords($name['sample_name']); ?></option>
                                            <?php } ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Is clinical sample post-mortem ?</th>
                                    <td>
                                        <select name="isSamplePostMortem" id="isSamplePostMortem" class="form-control" title="Is Clinical Sample Post-mortem ?" style="width:100%">
                                            <option value="">-- Select --</option>
                                            <option value='yes' <?php echo ($covid19Info['is_sample_post_mortem'] == 'yes') ? "selected='selected'" : ""; ?>> Yes </option>
                                            <option value='no' <?php echo ($covid19Info['is_sample_post_mortem'] == 'no') ? "selected='selected'" : ""; ?>> No </option>
                                        </select>
                                    </td>
                                    <th>Priority Status</th>
                                    <td>
                                        <select name="priorityStatus" id="priorityStatus" class="form-control" title="Priority Status" style="width:100%">
                                            <option value="">-- Select --</option>
                                            <option value='high' <?php echo ($covid19Info['priority_status'] == 'high') ? "selected='selected'" : ""; ?>> High </option>
                                            <option value='medium' <?php echo ($covid19Info['priority_status'] == 'medium') ? "selected='selected'" : ""; ?>> Medium </option>
                                            <option value='low' <?php echo ($covid19Info['priority_status'] == 'low') ? "selected='selected'" : ""; ?>> Low </option>
                                        </select>
                                    </td>
                                </tr>

                            </table>

                            <br><br>
                            <table class="table">
                                <tr>
                                    <th colspan=4 style="border-top:#ccc 2px solid;">
                                        <h4>CLINICAL DETAILS</h4>
                                    </th>
                                </tr>
                                <tr>
                                    <th style="width:15% !important">Date of Symptom Onset <span class="mandatory">*</span> </th>
                                    <td style="width:35% !important;">
                                        <input class="form-control date isRequired" type="text" name="dateOfSymptomOnset" id="dateOfSymptomOnset" placeholder="Symptom Onset Date" value="<?php echo $general->humanDateFormat($covid19Info['date_of_symptom_onset']); ?> " />
                                    </td>
                                    <th style="width:15% !important">Has the patient had contact with a confirmed case? <span class="mandatory">*</span></th>
                                    <td style="width:25% !important;">
                                        <select name="contactWithConfirmedCase" id="contactWithConfirmedCase" class="form-control isRequired" title="Please choose if the patient has had a contact with confirmed case" style="width:100%">
                                            <option value="">-- Select --</option>
                                            <option value='yes' <?php echo ($covid19Info['contact_with_confirmed_case'] == 'yes') ? "selected='selected'" : ""; ?>> Yes </option>
                                            <option value='no' <?php echo ($covid19Info['contact_with_confirmed_case'] == 'no') ? "selected='selected'" : ""; ?>> No </option>
                                            <option value='unknown' <?php echo ($covid19Info['contact_with_confirmed_case'] == 'unknown') ? "selected='selected'" : ""; ?>> Unknown </option>
                                            <option value='other' <?php echo ($covid19Info['contact_with_confirmed_case'] == 'other') ? "selected='selected'" : ""; ?>> Other Exposure </option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th colspan=2>Has the patient had a recent history of travelling to an affected area? <span class="mandatory">*</span></th>
                                    <td style="width:25% !important;">
                                        <select name="hasRecentTravelHistory" id="hasRecentTravelHistory" class="form-control isRequired" title="Please choose if the patient has had a recent history of travelling to an affected area" style="width:100%">
                                            <option value="">-- Select --</option>
                                            <option value='yes' <?php echo ($covid19Info['has_recent_travel_history'] == 'yes') ? "selected='selected'" : ""; ?>> Yes </option>
                                            <option value='no' <?php echo ($covid19Info['has_recent_travel_history'] == 'no') ? "selected='selected'" : ""; ?>> No </option>
                                            <option value='unknown' <?php echo ($covid19Info['has_recent_travel_history'] == 'unknown') ? "selected='selected'" : ""; ?>> Unknown </option>
                                        </select>
                                    </td>
                                </tr>
                                <tr class="historyfield">
                                    <th>If Yes, Country Name(s)</th>
                                    <td>
                                        <input class="historyfield form-control" type="text" name="countryName" id="countryName" placeholder="Country Name(s)" value="<?php echo $covid19Info['travel_country_names']; ?>" />
                                    </td>
                                    <th>Return Date</th>
                                    <td>
                                        <input class="historyfield form-control date" type="text" name="returnDate" id="returnDate" placeholder="Return Date" value="<?php echo $general->humanDateFormat($covid19Info['travel_return_date']); ?>" />
                                    </td>
                                </tr>

                            </table>


                        </div>
                    </div>

                    <form class="form-horizontal" method="post" name="editCovid19RequestForm" id="editCovid19RequestForm" autocomplete="off" action="covid-19-update-result-helper.php">
                        <div class="box box-primary">
                            <div class="box-body">
                                <div class="box-header with-border">
                                    <h3 class="box-title">B. Reserved for Laboratory Use </h3>
                                </div>
                                <table class="table" style="width:100%">
                                    <tr>
                                        <th><label for="">Sample Received Date <span class="mandatory">*</span></label></th>
                                        <td>
                                            <input type="text" class="form-control isRequired" id="sampleReceivedDate" name="sampleReceivedDate" placeholder="e.g 09-Jan-1992 05:30" title="Please enter sample receipt date" value="<?php echo $general->humanDateFormat($covid19Info['sample_received_at_vl_lab_datetime']) ?>" onchange="" style="width:100%;" />
                                        </td>
                                        <td></td>
                                        <td></td>
                                    <tr>
                                        <th>Is Sample Rejected ? <span class="mandatory">*</span></th>
                                        <td>
                                            <select class="form-control isRequired" name="isSampleRejected" id="isSampleRejected">
                                                <option value=''> -- Select -- </option>
                                                <option value="yes" <?php echo ($covid19Info['is_sample_rejected'] == 'yes') ? "selected='selected'" : ""; ?>> Yes </option>
                                                <option value="no" <?php echo ($covid19Info['is_sample_rejected'] == 'no') ? "selected='selected'" : ""; ?>> No </option>
                                            </select>
                                        </td>

                                        <th>Reason for Rejection</th>
                                        <td>
                                            <select class="form-control" name="sampleRejectionReason" id="sampleRejectionReason">
                                                <option value="">-- Select --</option>
                                                <?php foreach ($rejectionTypeResult as $type) { ?>
                                                    <optgroup label="<?php echo ucwords($type['rejection_type']); ?>">
                                                        <?php
                                                        foreach ($rejectionResult as $reject) {
                                                            if ($type['rejection_type'] == $reject['rejection_type']) { ?>
                                                                <option value="<?php echo $reject['rejection_reason_id']; ?>" <?php echo ($covid19Info['reason_for_sample_rejection'] == $reject['rejection_reason_id']) ? 'selected="selected"' : ''; ?>><?php echo ucwords($reject['rejection_reason_name']); ?></option>
                                                        <?php }
                                                        } ?>
                                                    </optgroup>
                                                <?php }  ?>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="width:25%;"><label for="">Sample Test Date </label></td>
                                        <td style="width:25%;">
                                            <input type="text" class="form-control dateTime" id="sampleTestedDateTime" name="sampleTestedDateTime" placeholder="e.g 09-Jan-1992 05:30" title="Sample Tested Date and Time" onchange="" value="<?php echo $general->humanDateFormat($covid19Info['sample_tested_datetime']) ?>" style="width:100%;" />
                                        </td>


                                        <th>Result</th>
                                        <td>
                                            <select class="form-control" name="result" id="result">
                                                <option value=''> -- Select -- </option>
                                                <?php foreach ($covid19Results as $covid19ResultKey => $covid19ResultValue) { ?>
                                                    <option value="<?php echo $covid19ResultKey; ?>" <?php echo ($covid19Info['result'] == $covid19ResultKey) ? "selected='selected'" : ""; ?>> <?php echo $covid19ResultValue; ?> </option>
                                                <?php } ?>
                                            </select>
                                        </td>
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
                    <input type="hidden" name="formId" id="formId" value="7" />
                    <input type="hidden" name="covid19SampleId" id="covid19SampleId" value="<?php echo ($covid19Info['covid19_id']); ?>" />
                    <input type="hidden" name="sampleCodeTitle" id="sampleCodeTitle" value="<?php echo $arr['sample_code']; ?>" />
                    <input type="hidden" id="sampleCode" name="sampleCode" value="<?php echo $covid19Info['sample_code'] ?>" />
                    <a href="/covid-10/requests/covid-19-manual-results.php" class="btn btn-default"> Cancel</a>
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
            $.post("/includes/getFacilityForClinic.php", {
                    pName: pName
                },
                function(data) {
                    if (data != "") {
                        details = data.split("###");
                        $("#facilityId").html(details[0]);
                        $("#district").html(details[1]);
                        $("#clinicianName").val(details[2]);
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
            $.post("/includes/getFacilityForClinic.php", {
                    dName: dName,
                    cliName: cName
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
            $.post("/includes/getFacilityForClinic.php", {
                    cName: cName
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
        flag = deforayValidator.init({
            formId: 'editCovid19RequestForm'
        });
        if (flag) {
            document.getElementById('editCovid19RequestForm').submit();
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


        $('.disabledForm input, .disabledForm select , .disabledForm textarea ').attr('disabled', true);

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
      

    });
</script>