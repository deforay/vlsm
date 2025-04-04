<?php
// imported in hepatitis-add-request.php based on country in global config

use App\Services\FacilitiesService;
use App\Registries\ContainerRegistry;

/** @var FacilitiesService $facilitiesService */
$facilitiesService = ContainerRegistry::get(FacilitiesService::class);

$testingLabs = $facilitiesService->getTestingLabs('hepatitis');
$testingLabsDropdown = $general->generateSelectOptions($testingLabs, null, "-- Select --");


$rKey = '';
$sKey = '';
$sFormat = '';

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

?>

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1><em class="fa-solid fa-pen-to-square"></em> RWANDA HEPATITIS LABORATORY TEST REQUEST FORM</h1>
        <ol class="breadcrumb">
            <li><a href="/"><em class="fa-solid fa-chart-pie"></em> Home</a></li>
            <li class="active">Add New Request</li>
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
                <form class="form-horizontal" method="post" name="addHepatitisRequestForm" id="addHepatitisRequestForm" autocomplete="off" action="hepatitis-add-request-helper.php">
                    <div class="box-body">
                        <div class="box box-default">
                            <div class="box-body">
                                <div class="box-header with-border">
                                    <h3 class="box-title">SECTION 1. TO BE FILLED AT ENROLMENT FACILITY</h3>
                                </div>
                                <div class="box-header with-border">
                                    <h3 class="box-title" style="font-size:1em;">To be filled by requesting Clinician/Nurse</h3>
                                </div>
                                <table aria-describedby="table" class="table" aria-hidden="true" style="width:100%">
                                    <tr>
                                        <?php if ($general->isSTSInstance()) { ?>
                                            <td style="width:15%"><label for="sampleCode">Sample ID </label></td>
                                            <td style="width:35%">
                                                <span id="sampleCodeInText" style="width:100%;border-bottom:1px solid #333;">Will be Auto-Generated</span>
                                                <input type="hidden" id="sampleCode" name="sampleCode" />
                                            </td>
                                        <?php } else { ?>
                                            <td style="width:15%"><label for="sampleCode">Sample ID </label><span class="mandatory">*</span></td>
                                            <td style="width:35%">
                                                <input type="text" class="form-control isRequired" id="sampleCode" name="sampleCode" placeholder="Sample ID" title="<?= _translate("Please make sure you have selected Sample Collection Date and Requesting Facility"); ?>" style="width:100%;" onchange="checkSampleNameValidation('form_hepatitis','<?php echo $sampleCode; ?>',this.id,null,'The sample id that you entered already exists. Please try another sample id',null)" readonly />
                                            </td>
                                        <?php } ?>
                                        <div class="col-md-12 encryptPIIContainer">
                                            <label class="col-lg-2 control-label" for="encryptPII"><?= _translate('Encrypt PII'); ?> </label>
                                            <div class="col-lg-3">
                                                <select name="encryptPII" id="encryptPII" class="form-control" title="<?= _translate('Encrypt Patient Identifying Information'); ?>">
                                                    <option value=""><?= _translate('--Select--'); ?></option>
                                                    <option value="no" selected='selected'><?= _translate('No'); ?></option>
                                                    <option value="yes"><?= _translate('Yes'); ?></option>
                                                </select>
                                            </div>
                                        </div>
                                        <th style="width:15%" scope="row"><label for="patientId">Patient Code <span class="mandatory">*</span> </label></th>
                                        <td style="width:35%">
                                            <input type="text" class="form-control isRequired patientId" id="patientId" name="patientId" placeholder="Patient Code" title="Please enter Patient Code" style="width:100%;" onchange="" />
                                            <span class="artNoGroup"></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="width:15%"><label for="hepatitisTestType">Hepatitis Test Type </label><span class="mandatory">*</span></td>
                                        <td style="width:35%">
                                            <select class="form-control isRequired" name="hepatitisTestType" id="hepatitisTestType" title="Please choose type hepatitis test" style="width:100%;" onchange="hepatitisTestTypeFn(this.value);">
                                                <option value="">--Select--</option>
                                                <option value="HBV">HBV</option>
                                                <option value="HCV">HCV</option>
                                            </select>
                                        </td>
                                        <td style="width:15%"><label for="province">Province </label><span class="mandatory">*</span></td>
                                        <td style="width:35%">
                                            <select class="form-control isRequired" name="province" id="province" title="Please choose province" onchange="getfacilityDetails(this);" style="width:100%;">
                                                <?php echo $province; ?>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="width:15%"><label for="district">District </label><span class="mandatory">*</span></td>
                                        <td style="width:35%">
                                            <select class="form-control isRequired" name="district" id="district" title="Please choose district" style="width:100%;" onchange="getfacilityDistrictwise(this);">
                                                <option value=""> -- Select -- </option>
                                            </select>
                                        </td>
                                        <td style="width:15%"><label for="facilityId">Health Facility </label><span class="mandatory">*</span></td>
                                        <td style="width:35%">
                                            <select class="form-control isRequired " name="facilityId" id="facilityId" title="Please choose facility" style="width:100%;" onchange="getfacilityProvinceDetails(this);">
                                                <?= $general->generateSelectOptions($healthFacilities, null, '-- Select --'); ?>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <?php if ($general->isSTSInstance()  && $_SESSION['accessType'] == 'collection-site') { ?>
                                            <td style="width:15%"><label for="labId">Lab Name <span class="mandatory">*</span></label> </td>
                                            <td style="width:35%">
                                                <select name="labId" id="labId" class="form-control isRequired" title="Please select Testing Lab name" style="width:100%;">
                                                    <option value=""> -- Select -- </option>
                                                    <?= $general->generateSelectOptions($testingLabs, null, '-- Select --'); ?>
                                                </select>
                                            </td>
                                        <?php } ?>
                                        <th scope="row" style="width:15%">Sample Collection Date <span class="mandatory">*</span> </th>
                                        <td style="width:35%">
                                            <input class="form-control isRequired" type="text" name="sampleCollectionDate" id="sampleCollectionDate" placeholder="Sample Collection Date" onchange="generateSampleCode(); checkCollectionDate(this.value);" />
                                            <span class="expiredCollectionDate" style="color:red; display:none;"></span>
                                        </td>
                                        <th scope="row" style="width:15%">DHIS2 Case ID </th>
                                        <td style="width:35%">
                                            <input class="form-control" type="text" name="externalSampleCode" id="externalSampleCode" placeholder="DHIS2 Case ID" />
                                        </td>

                                    </tr>
                                </table>
                                <br>
                                <hr style="border: 1px solid #ccc;">

                                <div class="box-header with-border">
                                    <h3 class="box-title">DEMOGRAPHICS</h3>
                                </div>
                                <table aria-describedby="table" class="table" aria-hidden="true" style="width:100%">

                                    <tr>
                                        <th scope="row" style="width:15%"><label for="firstName">First Name <span class="mandatory">*</span> </label></th>
                                        <td style="width:35%">
                                            <input type="text" class="form-control isRequired" id="firstName" name="firstName" placeholder="First Name" title="Please enter patient first name" style="width:100%;" onchange="" />
                                        </td>
                                        <th scope="row" style="width:15%"><label for="lastName">Last name </label></th>
                                        <td style="width:35%">
                                            <input type="text" class="form-control" id="lastName" name="lastName" placeholder="Last name" title="Please enter patient last name" style="width:100%;" onchange="" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><label for="patientPhoneNumber">Phone number</label></th>
                                        <td><input type="text" class="form-control phone-number" id="patientPhoneNumber" name="patientPhoneNumber" placeholder="Patient Phone Number" title="Patient Phone Number" style="width:100%;" onchange="" /></td>

                                        <th scope="row"><label for="patientDob">Date of Birth <span class="mandatory">*</span> </label></th>
                                        <td>
                                            <input type="text" class="form-control date isRequired" id="patientDob" name="dob" placeholder="Date of Birth" title="Please enter Date of birth" style="width:100%;" onchange="calculateAgeInYears();" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><label for="patientAge">Patient Age (years)</label></th>
                                        <td><input type="number" max="150" maxlength="3" oninput="this.value=this.value.slice(0,$(this).attr('maxlength'))" class="form-control " id="patientAge" name="patientAge" placeholder="Patient Age (in years)" title="Patient Age" style="width:100%;" onchange="" /></td>

                                        <th scope="row"><label for="patientGender">Sex <span class="mandatory">*</span> </label></th>
                                        <td>
                                            <select class="form-control isRequired" name="patientGender" id="patientGender">
                                                <option value=''> -- Select -- </option>
                                                <option value='male'> Male </option>
                                                <option value='female'> Female </option>
                                                <option value='other'> Other </option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><label for="patientProvince">Province</label></th>
                                        <td><input type="text" class="form-control " id="patientProvince" name="patientProvince" placeholder="Patient Province" title="Please enter the patient province" style="width:100%;" /></td>

                                        <th scope="row"><label for="patientDistrict">District</label></th>
                                        <td><input class="form-control" id="patientDistrict" name="patientDistrict" placeholder="Patient District" title="Please enter the patient district" style="width:100%;"></td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><label for="patientCity">Village</label></th>
                                        <td><input type="text" class="form-control " id="patientCity" name="patientCity" placeholder="Patient Village" title="Please enter the patient village" style="width:100%;" /></td>
                                        <th scope="row"><label for="socialCategory">Social Category</label></th>
                                        <td>
                                            <select class="form-control" name="socialCategory" id="socialCategory" title="Please select the social category">
                                                <option value=''> -- Select -- </option>
                                                <option value='A'> A </option>
                                                <option value='B'> B </option>
                                                <option value='C'> C </option>
                                                <option value='D'> D </option>
                                                <option value='others'>Others </option>

                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><label for="patientGender">Insurance</label></th>
                                        <td>
                                            <select class="form-control" name="insurance" id="insurance" title="Please select the insurance">
                                                <option value=''> -- Select -- </option>
                                                <option value='mutuelle'> Mutuelle </option>
                                                <option value='RAMA'> RAMA </option>
                                                <option value='MMI'> MMI </option>
                                                <option value='private'> Private </option>
                                                <option value='none'> None </option>

                                            </select>
                                        </td>
                                    </tr>

                                </table>
                                <br><br>
                            </div>
                        </div>
                        <div class="box-body">
                            <div class="box-header with-border">
                                <h3 class="box-title">TEST RESULTS FOR SCREENING BY RDTs</h3>
                            </div>
                            <table aria-describedby="table" class="table" aria-hidden="true" style="width:100%">
                                <tr>
                                    <th style="width:15%">Specimen Type <span class="mandatory">*</span></th>
                                    <td style="width:35%">
                                        <select name="specimenType" id="specimenType" class="form-control isRequired" title="Please choose specimen type" style="width:100%">
                                            <?= $general->generateSelectOptions($specimenResult, null, '-- Select --'); ?>
                                        </select>
                                    </td>
                                    <th style="width:15%" scope="row" class="hbvFields"><label for="HBsAg">HBsAg Result</label></th>
                                    <td class="hbvFields" style="width:35%">
                                        <select class="hbvFields form-control" name="HBsAg" id="HBsAg" title="Please choose HBsAg result">
                                            <option value=''> -- Select -- </option>
                                            <option value='positive'>Positive</option>
                                            <option value='negative'>Negative</option>
                                            <option value='intermediate'>Intermediate</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width:15%" class="hcvFields"><label for="antiHcv">Anti-HCV Result</label></td>
                                    <td class="hcvFields" style="width:35%">
                                        <select class="hcvFields form-control" name="antiHcv" id="antiHcv" title="Please choose Anti-HCV result">
                                            <option value=''> -- Select -- </option>
                                            <option value='positive'>Positive</option>
                                            <option value='negative'>Negative</option>
                                            <option value='intermediate'>Intermediate</option>
                                        </select>
                                    </td>
                                    <td style="width:15%"><label for="labTechnician">Lab Technician (screening test)</label></td>
                                    <td style="width:35%">
                                        <select name="labTechnician" id="labTechnician" class="form-control" title="Please select a Lab Technician" style="width:100%;">
                                            <?= $general->generateSelectOptions($labTechniciansResults, $_SESSION['userId'], '-- Select --'); ?>
                                        </select>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <?php if (_isAllowed('/hepatitis/results/hepatitis-update-result.php') && $_SESSION['accessType'] != 'collection-site') { ?>
                            <div class="box box-primary">
                                <div class="box-body">
                                    <div class="box-header with-border">
                                        <h3 class="box-title">SECTION 2. TO BE FILLED AT VIRAL LOAD TESTING SITE</h3>
                                    </div>
                                    <table aria-describedby="table" class="table" aria-hidden="true" style="width:100%">
                                        <tr>
                                            <th style="width:15%" scope="row"><label for="">Sample Received Date </label></th>
                                            <td style="width:35%">
                                                <input type="text" class="labSecInput form-control" id="sampleReceivedDate" name="sampleReceivedDate" placeholder="<?= _translate("Please enter date"); ?>" title="Please enter sample receipt date" style="width:100%;" />
                                            </td>
                                            <td style="width:15%"><label for="labId">Lab Name <span class="mandatory">*</span></label> </td>
                                            <td style="width:35%">
                                                <select name="labId" id="labId" class="form-control" title="Please select Testing Lab name" style="width:100%;">
                                                    <?= $general->generateSelectOptions($testingLabs, null, '-- Select --'); ?>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th style="width:15%" scope="row"><label for="sampleTestedDateTime">VL Testing Date</label></th>
                                            <td>
                                                <input type="text" class="labSecInput form-control" id="sampleTestedDateTime" name="sampleTestedDateTime" placeholder="<?= _translate("Please enter date"); ?>" title="Please enter testing date" style="width:100%;" />
                                            </td style="width:35%">
                                            <th style="width:15%" scope="row"><label for="vlTestingSite">VL Testing Site</label></th>
                                            <td style="width:35%">
                                                <select class="labSecInput form-control" id="vlTestingSite" name="vlTestingSite" title="Please select testing site" style="width:100%;">
                                                    <?= $testingLabsDropdown; ?>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th style="width:15%" scope="row"><label for="reasonVlTest">VL test purpose</label></th>
                                            <td style="width:35%">
                                                <select class="labSecInput form-control" name="reasonVlTest" id="reasonVlTest" title="Please choose VL test purpose">
                                                    <option value=''> -- Select -- </option>
                                                    <option value='Initial HCV VL'>Initial HCV VL</option>
                                                    <option value='SVR12 HCV VL'>SVR12 HCV VL</option>
                                                    <option value='SVR12 HCV VL - Second Line'>SVR12 HCV VL - Second Line</option>
                                                    <option value='Initial HBV VL'>Initial HBV VL</option>
                                                    <option value='Follow up HBV VL'>Follow up HBV VL</option>
                                                </select>
                                            </td>
                                            <th style="width:15%">Is Sample Rejected?</th>
                                            <td style="width:35%">
                                                <select class="labSecInput form-control" name="isSampleRejected" id="isSampleRejected">
                                                    <option value=''> -- Select -- </option>
                                                    <option value="yes"> Yes </option>
                                                    <option value="no"> No </option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr class="show-rejection" style="display:none;">
                                            <th scope="row" class="show-rejection" style="width:15%;display:none;">Reason for Rejection<span class="mandatory">*</span></th>
                                            <td class="show-rejection" style="width:35%;display:none;">
                                                <select class="form-control" name="sampleRejectionReason" id="sampleRejectionReason" title="Please choose reason for rejection">
                                                    <option value=''> -- Select -- </option>
                                                    <?php echo $rejectionReason; ?>
                                                </select>
                                            </td>
                                            <th style="width:15%" scope="row">Rejection Date<span class="mandatory">*</span></th>
                                            <td style="width:35%"><input class="form-control date rejection-show" type="text" name="rejectionDate" id="rejectionDate" placeholder="Select Rejection Date" /></td>
                                        </tr>
                                        <!-- <tr>
                                            <th style="width:15%" scope="row" class="hcvFields"><label for="hcv">HCV VL Result</label></th>
                                            <td class="hcvFields" style="width:35%">
                                                <select class="hcvFields labSecInput form-control rejected-input" name="hcv" id="hcv">
                                                    < ?= $general->generateSelectOptions($hepatitisResults, null, '-- Select --'); ?>
                                                </select>
                                            </td>
                                            <th style="width:15%" scope="row" class="hbvFields"><label for="hbv">HBV VL Result</label></th>
                                            <td class="hbvFields" style="width:35%">
                                                <select class="hbvFields labSecInput form-control rejected-input" name="hbv" id="hbv">
                                                    < ?= $general->generateSelectOptions($hepatitisResults, null, '-- Select --'); ?>
                                                </select>
                                            </td>
                                        </tr> -->
                                        <tr>
                                            <th style="width:15%" scope="row" class="hcvFields"><label for="hcvCount">HCV VL Count</label></th>
                                            <td style="width:35%" class="hcvFields">
                                                <input type="text" class="hcvFields labSecInput form-control rejected-input" placeholder="Enter HCV Count" title="Please enter HCV Count" name="hcvCount" id="hcvCount">
                                            </td>
                                            <th scope="row" class="hbvFields"><label for="hbvCount">HBV VL Count</label></th>
                                            <td style="width:35%" class="hbvFields">
                                                <input type="text" class="hbvFields labSecInput form-control rejected-input" placeholder="Enter HBV Count" title="Please enter HBV Count" name="hbvCount" id="hbvCount">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="width:15%"><label for="">Testing Platform </label></td>
                                            <td style="width:35%"><select name="hepatitisPlatform" id="hepatitisPlatform" class="labSecInput form-control rejected-input" title="Please select the testing platform">
                                                    <?= $general->generateSelectOptions($testPlatformList, null, '-- Select --'); ?>
                                                </select>
                                            </td>
                                            <td style="width:15%"><label for="">Machine used to test </label></td>
                                            <td style="width:35%">
                                                <select name="machineName" id="machineName" class="labSecInput form-control rejected-input" title="Please select the machine name">
                                                    <option value="">-- Select --</option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th style="width:15%">Is Result Authorized ?</th>
                                            <td style="width:35%">
                                                <select name=" isResultAuthorized" id="isResultAuthorized" class="labSecInput disabled-field form-control rejected-input" title="Is Result authorized ?" style="width:100%">
                                                    <option value="">-- Select --</option>
                                                    <option value='yes'> Yes </option>
                                                    <option value='no'> No </option>
                                                </select>
                                            </td>
                                            <th style="width:15%;">Authorized By</th>
                                            <td style="width:35%">
                                                <select name="authorizedBy" id="authorizedBy" class="disabled-field form-control" title="Please choose authorized by" style="width: 100%;">
                                                    <?= $general->generateSelectOptions($labTechniciansResults, null, '-- Select --'); ?>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th style="width:15%">Authorized on</th>
                                            <td style="width:35%"><input type="text" name="authorizedOn" id="authorizedOn" class="labSecInput disabled-field form-control date rejected-input" placeholder="Authorized on" title="Please select the authorized on" /></td>
                                            <th style="width:15%">Lab Technician (VL Testing)</th>
                                            <td style="width:35%">
                                                <select name="tested_by" id="tested_by" class="form-control" title="Please select a Lab Technician (VL Testing)" style="width:100%;">
                                                    <?= $general->generateSelectOptions($labTechniciansResults, $_SESSION['userId'], '-- Select --'); ?>
                                                </select>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        <?php } ?>

                    </div>
                    <!-- /.box-body -->
                    <div class="box-footer">
                        <?php if ($arr['hepatitis_sample_code'] == 'auto' || $arr['hepatitis_sample_code'] == 'YY' || $arr['hepatitis_sample_code'] == 'MMYY') { ?>
                            <input type="hidden" name="sampleCodeFormat" id="sampleCodeFormat" value="<?php echo $sFormat; ?>" />
                            <input type="hidden" name="sampleCodeKey" id="sampleCodeKey" value="<?php echo $sKey; ?>" />
                            <input type="hidden" name="saveNext" id="saveNext" />
                        <?php } ?>
                        <a class="btn btn-primary btn-disabled" href="javascript:void(0);" onclick="validateNow();return false;">Save</a>
                        <a class="btn btn-primary btn-disabled" href="javascript:void(0);" onclick="validateNow();$('#saveNext').val('next');return false;">Save and Next</a>
                        <input type="hidden" name="formId" id="formId" value="<?php echo $arr['vl_form']; ?>" />
                        <input type="hidden" name="hepatitisSampleId" id="hepatitisSampleId" value="" />
                        <a href="/hepatitis/requests/hepatitis-requests.php" class="btn btn-default"> Cancel</a>
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

    function comorbidity(obj, id) {
        if (obj.value == 'other') {
            $('.show-comorbidity' + id).show();
            $('#comorbidityOther').addClass('isRequired');
        } else {
            $('.show-comorbidity' + id).hide();
            $('#comorbidityOther').removeClass('isRequired');
        }
    }

    function riskfactor(obj, id) {
        if (obj.value == 'other') {
            $('.show-riskfactor' + id).show();
            $('#riskFactorsOther').addClass('isRequired');
        } else {
            $('.show-riskfactor' + id).hide();
            $('#riskFactorsOther').removeClass('isRequired');
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
                    testType: 'hepatitis'
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
            generateSampleCode();
        } else if (pName == '') {
            provinceName = true;
            facilityName = true;
            $("#province").html("<?php echo $province; ?>");
            $("#facilityId").html("<?php echo $facility; ?>");
            $("#facilityId").select2("val", "");
            $("#district").html("<option value=''> -- Select -- </option>");
        }
        $.unblockUI();
    }

    function generateSampleCode() {
        var pName = $("#province").val();
        var provinceCode = $("#province").find(":selected").attr("data-code");

        var sDate = $("#sampleCollectionDate").val();
        var hepatitisTestType = $("#hepatitisTestType").val();
        if (pName != '' && sDate != '' && hepatitisTestType != '') {
            $.post("/hepatitis/requests/generate-sample-code.php", {
                    sampleCollectionDate: sDate,
                    provinceCode: provinceCode,
                    prefix: hepatitisTestType
                },
                function(data) {
                    var sCodeKey = JSON.parse(data);
                    console.log(sCodeKey);
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
                    testType: 'hepatitis'
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
                    testType: 'hepatitis'
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
            formId: 'addHepatitisRequestForm'
        });
        if (flag) {

            if ($('#antiHcv').val() != "" || $('#HBsAg').val() != "") {

            } else {

                alert("Please select test result for screening");
                if ($('#hepatitisTestType').val() == 'HBV') {
                    $('#HBsAg').focus();
                }
                if ($('#hepatitisTestType').val() == 'HCV') {
                    $('#antiHcv').focus();
                }

                return false;
            }

            $('#labId').removeClass('isRequired');
            $('.labSecInput').each(function() {
                if ($(this).val()) {
                    $('#labId').addClass('isRequired');
                }
            });

            if ($('#isResultAuthorized').val() != "yes") {
                $('#authorizedBy,#authorizedOn').removeClass('isRequired');
            }

            <?php
            if ($arr['hepatitis_sample_code'] == 'auto' || $arr['hepatitis_sample_code'] == 'YY' || $arr['hepatitis_sample_code'] == 'MMYY') {
            ?>
                insertSampleCode('addHepatitisRequestForm', 'hepatitisTestType', 'hepatitisSampleId', 'sampleCode', 'sampleCodeKey', 'sampleCodeFormat', 3, 'sampleCollectionDate');
            <?php
            } else {
            ?>
                document.getElementById('addHepatitisRequestForm').submit();
            <?php
            } ?>


            // $('.btn-disabled').attr('disabled', 'yes');
            // $(".btn-disabled").prop("onclick", null).off("click");
        }
    }


    $(document).ready(function() {
        Utilities.autoSelectSingleOption('facilityId');
        Utilities.autoSelectSingleOption('specimenType');
        $("#patientId").on('input', function() {
            $.post("/common/patient-last-request-details.php", {
                    patientId: $.trim($(this).val()),
                    testType: 'hepatitis'
                },
                function(data) {
                    if (data != "0") {
                        obj = $.parseJSON(data);
                        $(".artNoGroup").html('<small style="color:red">No. of times Test Requested for this Patient : ' + obj.no_of_req_time +
                            '<br>Last Test Request Added On LIS/STS : ' + obj.request_created_datetime +
                            '<br>Sample Collection Date for Last Request : ' + obj.sample_collection_date +
                            '<br>Total No. of times Patient tested for Hepatitis : ' + obj.no_of_tested_time +
                            '</small>');
                    } else {
                        $(".artNoGroup").html('');
                    }
                });

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
        $('#labId').select2({
            placeholder: "Lab Name"
        });
        $("#vlTestingSite").select2({
            placeholder: "Select Vl Testing Site"
        });
        $("#labId").select2({
            placeholder: "Select Testing Lab"
        });
        $('#authorizedBy').select2({
            placeholder: "Select Authorized By"
        });

        $('#isResultAuthorized').change(function(e) {
            checkIsResultAuthorized();
        });

        $("#hepatitisPlatform").on("change", function() {
            if (this.value != "") {
                getMachine(this.value);
            }
        });
    });

    function checkIsResultAuthorized() {
        if ($('#isResultAuthorized').val() == 'no') {
            $('#authorizedBy').val(null).trigger('change');
            $('#authorizedOn').val('');
            $('#authorizedBy,#authorizedOn').prop('disabled', true);
            $('#authorizedBy,#authorizedOn').addClass('disabled');
            $('#authorizedBy,#authorizedOn').removeClass('isRequired');
        } else if ($('#isResultAuthorized').val() == 'yes') {
            $('#authorizedBy,#authorizedOn').prop('disabled', false);
            $('#authorizedBy,#authorizedOn').removeClass('disabled');
            $('#authorizedBy,#authorizedOn').addClass('isRequired');
        }
        if ($('#isResultAuthorized').val() == '') {
            $('#authorizedBy').val(null).trigger('change');
            $('#authorizedOn').val('');
            $('#authorizedBy,#authorizedOn').prop('disabled', false);
            $('#authorizedBy,#authorizedOn').removeClass('disabled');
        }
    }

    function hepatitisTestTypeFn(val) {
        if (val == 'HBV') {
            option1 = 'Initial HBV VL';
            option2 = 'Follow up HBV VL';
            $('.hcvFields,#reasonVlTest').val('');
            $('.hcvFields').hide();
            $('.hbvFields').show();
            $("#reasonVlTest option[value='Initial HCV VL']").remove();
            $("#reasonVlTest option[value='SVR12 HCV VL']").remove();

            if ($("#reasonVlTest option[value='" + option1 + "']").length == 0) {
                $('#reasonVlTest').append(`
                <option value="${option1}"> ${option1} </option>`);
            }
            if ($("#reasonVlTest option[value='" + option2 + "']").length == 0) {
                $('#reasonVlTest').append(`
                <option value="${option2}"> ${option2} </option>`);
            }
        } else if (val == 'HCV') {
            option1 = 'Initial HCV VL';
            option2 = 'SVR12 HCV VL';
            $('.hbvFields,#reasonVlTest').val('');
            $('.hbvFields').hide();
            $('.hcvFields').show();
            $("#reasonVlTest option[value='Initial HBV VL']").remove();
            $("#reasonVlTest option[value='Follow up HBV VL']").remove();

            if ($("#reasonVlTest option[value='" + option1 + "']").length == 0) {
                $('#reasonVlTest').append(`
                <option value="${option1}"> ${option1} </option>`);
            }
            if ($("#reasonVlTest option[value='" + option2 + "']").length == 0) {
                $('#reasonVlTest').append(`
                <option value="${option2}"> ${option2} </option>`);
            }
        }
    }
</script>
