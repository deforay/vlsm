<?php

// imported in eid-edit-request.php based on country in global config

ob_start();

//Funding source list
$fundingSourceQry = "SELECT * FROM r_funding_sources WHERE funding_source_status='active' ORDER BY funding_source_name ASC";
$fundingSourceList = $db->query($fundingSourceQry);

//Implementing partner list
$implementingPartnerQry = "SELECT * FROM r_implementation_partners WHERE i_partner_status='active' ORDER BY i_partner_name ASC";
$implementingPartnerList = $db->query($implementingPartnerQry);


$eidResults = $general->getEidResults();


// Getting the list of Provinces, Districts and Facilities

$rKey = '';
$pdQuery = "SELECT * from province_details";
if ($sarr['sc_user_type'] == 'remoteuser') {
    $sampleCodeKey = 'remote_sample_code_key';
    $sampleCode = 'remote_sample_code';
    //check user exist in user_facility_map table
    $chkUserFcMapQry = "Select user_id from vl_user_facility_map where user_id='" . $_SESSION['userId'] . "'";
    $chkUserFcMapResult = $db->query($chkUserFcMapQry);
    if ($chkUserFcMapResult) {
        $pdQuery = "SELECT * from province_details as pd JOIN facility_details as fd ON fd.facility_state=pd.province_name JOIN vl_user_facility_map as vlfm ON vlfm.facility_id=fd.facility_id where user_id='" . $_SESSION['userId'] . "' group by province_name";
    }
    $rKey = 'R';
} else {
    $sampleCodeKey = 'sample_code_key';
    $sampleCode = 'sample_code';
    $rKey = '';
}
$pdResult = $db->query($pdQuery);
$province = "";
$province .= "<option value=''> -- Selecione -- </option>";
foreach ($pdResult as $provinceName) {
    $province .= "<option value='" . $provinceName['province_name'] . "##" . $provinceName['province_code'] . "'>" . ucwords($provinceName['province_name']) . "</option>";
}

$facility = $general->generateSelectOptions($healthFacilities, $eidInfo['facility_id'], '-- Selecione --');

//$eidInfo['mother_treatment'] = isset($eidInfo['mother_treatment']) ? explode(",", $eidInfo['mother_treatment']) : array();
//$eidInfo['child_treatment'] = isset($eidInfo['child_treatment']) ? explode(",", $eidInfo['child_treatment']) : array();



?>


<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1><i class="fa fa-edit"></i> SOLICITAÇÃO DE QUANTIFICAÇÃO DE DIAGNÓSTICO PRECOCE INFANTIL DO VIH</h1>
        <ol class="breadcrumb">
            <li><a href="/"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Edit EID Request</li>
        </ol>
    </section>
    <!-- Main content -->
    <section class="content">

        <div class="box box-default">
            <div class="box-header with-border">
                <div class="pull-right" style="font-size:15px;"><span class="mandatory">*</span> indicates required field &nbsp;</div>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
                <!-- form start -->
                <form class="form-horizontal" method="post" name="editEIDRequestForm" id="editEIDRequestForm" autocomplete="off" action="eid-edit-request-helper.php">
                    <div class="box-body">
                        <div class="box box-default">
                            <div class="box-body">
                                <div class="box-header with-border">
                                    <h3 class="box-title">UNIDADE DE SOLICITAÇÃO</h3>
                                </div>
                                <div class="box-header with-border">
                                    <h3 class="box-title" style="font-size:1em;">To be filled by requesting Clinician/Nurse</h3>
                                </div>
                                <table class="table" style="width:100%">
                                    <tr>
                                        <?php if ($sarr['sc_user_type'] == 'remoteuser') { ?>
                                            <td><label for="sampleCode">Nº de amostra </label></td>
                                            <td>
                                                <span id="sampleCodeInText" style="width:100%;border-bottom:1px solid #333;"><?php echo $eidInfo['sample_code'] ?></span>
                                                <input type="hidden" id="sampleCode" name="sampleCode" value="<?php echo $eidInfo['sample_code'] ?>" />
                                            </td>
                                        <?php } else { ?>
                                            <td><label for="sampleCode">Nº de amostra </label><span class="mandatory">*</span></td>
                                            <td>
                                                <input type="text" readonly value="<?php echo $eidInfo['sample_code'] ?>" class="form-control isRequired" id="sampleCode" name="sampleCode" placeholder="Échantillon ID" title="Please enter échantillon id" style="width:100%;" onchange="" />
                                            </td>
                                        <?php } ?>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td><label for="province">Província </label><span class="mandatory">*</span></td>
                                        <td>
                                            <select class="form-control isRequired" name="province" id="province" title="Please choose province" onchange="getfacilityDetails(this);" style="width:100%;">
                                                <?php echo $province; ?>
                                            </select>
                                        </td>
                                        <td><label for="district">Município </label><span class="mandatory">*</span></td>
                                        <td>
                                            <select class="form-control isRequired" name="district" id="district" title="Please choose district" style="width:100%;" onchange="getfacilityDistrictwise(this);">
                                                <option value=""> -- Selecione -- </option>
                                            </select>
                                        </td>
                                        <td><label for="facilityId">Nome da Unidade </label><span class="mandatory">*</span></td>
                                        <td>
                                            <select class="form-control isRequired " name="facilityId" id="facilityId" title="Please choose service provider" style="width:100%;" onchange="getfacilityProvinceDetails(this);">
                                                <?php echo $facility; ?>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><label for="supportPartner">Implementing Partner </label></td>
                                        <td>
                                            <!-- <input type="text" class="form-control" id="supportPartner" name="supportPartner" placeholder="Partenaire dappui" title="Please enter partenaire dappui" style="width:100%;"/> -->
                                            <select class="form-control" name="implementingPartner" id="implementingPartner" title="Please choose partenaire de mise en œuvre" style="width:100%;">
                                                <option value=""> -- Selecione -- </option>
                                                <?php
                                                foreach ($implementingPartnerList as $implementingPartner) {
                                                ?>
                                                    <option value="<?php echo ($implementingPartner['i_partner_id']); ?>" <?php echo ($eidInfo['implementing_partner'] == $implementingPartner['i_partner_id']) ? "selected='selected'" : ""; ?>><?php echo ucwords($implementingPartner['i_partner_name']); ?></option>
                                                <?php } ?>
                                            </select>
                                        </td>
                                        <td><label for="fundingSource">Funding Partner</label></td>
                                        <td>
                                            <select class="form-control" name="fundingSource" id="fundingSource" title="Please choose source de financement" style="width:100%;">
                                                <option value=""> -- Selecione -- </option>
                                                <?php
                                                foreach ($fundingSourceList as $fundingSource) {
                                                ?>
                                                    <option value="<?php echo ($fundingSource['funding_source_id']); ?>" <?php echo ($eidInfo['funding_source'] == $fundingSource['funding_source_id']) ? "selected='selected'" : ""; ?>><?php echo ucwords($fundingSource['funding_source_name']); ?></option>
                                                <?php } ?>
                                            </select>
                                        </td>
                                        <td><label for="sector">Serviço/Sector </label> </td>
                                        <td>
                                            <input type="text" class="form-control" id="sector" name="sector" placeholder="Serviço/Sector" title="Sector" style="width:100%;" value="<?php echo $eidInfo['sector']; ?>" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <?php if ($sarr['sc_user_type'] == 'remoteuser') { ?>
                                            <!-- <tr> -->
                                            <td><label for="labId">Lab Name <span class="mandatory">*</span></label> </td>
                                            <td>
                                                <select name="labId" id="labId" class="form-control isRequired" title="Please select Testing Lab name" style="width:100%;">
                                                    <?= $general->generateSelectOptions($testingLabs, $eidInfo['lab_id'], '-- Selecione --'); ?>
                                                </select>
                                            </td>
                                            <!-- </tr> -->
                                        <?php } ?>
                                    </tr>
                                </table>
                                <br>
                                <hr style="border: 1px solid #ccc;">

                                <div class="box-header with-border">
                                    <h3 class="box-title">DADOS DO PACIENTE</h3>
                                </div>
                                <table class="table" style="width:100%">

                                    <tr>
                                        <th style="width:15% !important"><label for="childName">Nome da Criança </label></th>
                                        <td style="width:35% !important">
                                            <input type="text" class="form-control " id="childName" name="childName" placeholder="Nome da Criança" title="Nome da Criança" style="width:100%;" value="<?php echo $eidInfo['child_name']; ?>" onchange="" />
                                        </td>
                                        <th style="width:15% !important"><label for="childId">Nº Processo Clínico <span class="mandatory">*</span> </label></th>
                                        <td style="width:35% !important">
                                            <input type="text" class="form-control isRequired" id="childId" name="childId" placeholder="Nº Processo Clínico" title="Please enter Exposed Infant Identification" style="width:100%;" value="<?php echo $eidInfo['child_id']; ?>" onchange="" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <th><label for="childDob">Data de Nascimento <span class="mandatory">*</span> </label></th>
                                        <td>
                                            <input type="text" class="form-control isRequired" id="childDob" name="childDob" placeholder="Date of birth" title="Please enter Date of birth" style="width:100%;" value="<?php echo $general->humanDateFormat($eidInfo['child_dob']) ?>" onchange="calculateAgeInMonths();" />
                                        </td>
                                        <th><label for="childGender">Género <span class="mandatory">*</span> </label></th>
                                        <td>
                                            <select class="form-control isRequired" name="childGender" id="childGender">
                                                <option value=''> -- Selecione -- </option>
                                                <option value='male' <?php echo ($eidInfo['child_gender'] == 'male') ? "selected='selected'" : ""; ?>> Masculino </option>
                                                <option value='female' <?php echo ($eidInfo['child_gender'] == 'female') ? "selected='selected'" : ""; ?>> Feminino </option>

                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Idade da criança (em meses)</th>
                                        <td><input type="number" max="24" maxlength="2" oninput="this.value=this.value.slice(0,$(this).attr('maxlength'))" class="form-control " id="childAge" name="childAge" placeholder="Age" title="Age" style="width:100%;" onchange="" value="<?php echo $eidInfo['child_age']; ?>" /></td>
                                        <th>Profilaxia da Criança</th>
                                        <td>
                                            <select class="form-control" name="childTreatment[]" id="childTreatment">
                                                <option value=''> -- Selecione -- </option>
                                                <option value='NVP por 12 semanas' <?php echo ('NVP por 12 semanas' == $eidInfo['child_treatment']) ? "selected='selected'" : ""; ?>> NVP por 12 semanas </option>
                                                <option value='NVP + AZT' <?php echo ('NVP + AZT' == $eidInfo['child_treatment']) ? "selected='selected'" : ""; ?>> NVP + AZT </option>
                                                <option value='Nenhuma' <?php echo ('Nenhuma' == $eidInfo['child_treatment']) ? "selected='selected'" : ""; ?>> Nenhuma </option>
                                                <option value='Other' <?php echo ('Other' == $eidInfo['child_treatment']) ? "selected='selected'" : ""; ?>> Outra (especifique) </option>
                                            </select>
                                        </td>

                                    </tr>
                                    <tr>
                                        <th>Alimentação da Criança</th>
                                        <td>
                                            <select class="form-control" name="choiceOfFeeding" id="choiceOfFeeding">
                                                <option value=''> -- Selecione -- </option>
                                                <optgroup label="0-6 meses">
                                                    <option value='Apenas leite materno' <?php echo ($eidInfo['choice_of_feeding'] == 'Apenas leite materno') ? "selected='selected'" : ""; ?>>Apenas leite materno </option>
                                                    <option value='Apenas leite artificial/substituto' <?php echo ($eidInfo['choice_of_feeding'] == 'Apenas leite artificial/substituto') ? "selected='selected'" : ""; ?>>Apenas leite artificial/substituto</option>
                                                    <option value='Mista (materno + artificial)' <?php echo ($eidInfo['choice_of_feeding'] == 'Mista (materno + artificial)') ? "selected='selected'" : ""; ?>>Mista (materno + artificial)</option>
                                                </optgroup>
                                                <optgroup label="> 6 meses">
                                                    <option value='Com leite materno' <?php echo ($eidInfo['choice_of_feeding'] == 'Com leite materno') ? "selected='selected'" : ""; ?>>Com leite materno</option>
                                                    <option value='Sem leite materno' <?php echo ($eidInfo['choice_of_feeding'] == 'Sem leite materno') ? "selected='selected'" : ""; ?>>Sem leite materno</option>
                                                </optgroup>
                                            </select>
                                        </td>
                                        <th> </th>
                                        <td></td>

                                    </tr>


                                    <tr>
                                        <th>Nome da Mãe </th>
                                        <td><input type="text" class="form-control " id="mothersName" name="mothersName" placeholder="Nome da Mãe" title="Nome da Mãe" style="width:100%;" value="<?php echo $eidInfo['mother_name'] ?>" /></td>
                                        <th>Nº Processo Clínico</th>
                                        <td><input type="text" class="form-control " id="mothersId" name="mothersId" placeholder="Mother ART Number" title="Mother ART Number" style="width:100%;" value="<?php echo $eidInfo['mother_id'] ?>" /></td>
                                    </tr>
                                    <tr>
                                        <th>Mãe da Criança Autoriza Contacto</th>
                                        <td>
                                            <select class="form-control" name="motherConsentForContact" id="motherConsentForContact">
                                                <option value=''> -- Selecione -- </option>
                                                <option value='yes' <?php echo ($eidInfo['caretaker_contact_consent'] == 'yes') ? "selected='selected'" : ""; ?>> Sim </option>
                                                <option value='no' <?php echo ($eidInfo['caretaker_contact_consent'] == 'no') ? "selected='selected'" : ""; ?>> Não </option>
                                            </select>
                                        </td>
                                        <th>Telemóvel</th>
                                        <td><input type="text" class="form-control " id="caretakerPhoneNumber" name="caretakerPhoneNumber" placeholder="Telemóvel" title="Caretaker Phone Number" style="width:100%;" value="<?php echo $eidInfo['caretaker_phone_number'] ?>" /></td>
                                    </tr>

                                    <tr>
                                        <th>Tratamento ARV da Mãe</th>
                                        <td><input type="text" class="form-control " id="motherTreatment" name="motherTreatment[]" placeholder="Tratamento ARV da Mãe" title="Tratamento ARV da Mãe" style="width:100%;" value="<?php echo $eidInfo['mother_treatment'] ?>" /></td>
                                        <th>Data de início</th>
                                        <td><input type="text" class="form-control date" id="motherTreatmentInitiationDate" name="motherTreatmentInitiationDate" placeholder="Data de início" title="Data de início" style="width:100%;" value="<?php echo $general->humanDateFormat($eidInfo['mother_treatment_initiation_date']); ?>" /></td>
                                    </tr>


                                </table>

                                <br><br>
                                <table class="table" style="">
                                    <tr>
                                        <th colspan=4 style="border-top:#ccc 2px solid;">
                                            <h4>Sample Information</h4>
                                        </th>
                                    </tr>
                                    <tr>
                                        <th style="width:15% !important">Data da Colheita <span class="mandatory">*</span> </th>
                                        <td style="width:35% !important;">
                                            <input class="form-control dateTime isRequired" type="text" name="sampleCollectionDate" id="sampleCollectionDate" placeholder="Sample Collection Date" value="<?php echo ($eidInfo['sample_collection_date']); ?>" />
                                        </td>

                                        <th style="width:14%;"> Tipo de amostra <span class="mandatory">*</span> </th>
                                        <td style="width:35%;">
                                            <select name="specimenType" id="specimenType" class="form-control isRequired" title="Please choose Tipo de amostra" style="width:100%">
                                                <option value="">-- Selecione --</option>
                                                <?php foreach ($sampleResult as $name) { ?>
                                                    <option value="<?php echo $name['sample_id']; ?>" <?php echo ($eidInfo['specimen_type'] == $name['sample_id']) ? "selected='selected'" : ""; ?>><?php echo ucwords($name['sample_name']); ?></option>
                                                <?php } ?>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Técnico Responsável pela Colheita </th>
                                        <td>
                                            <input class="form-control" type="text" name="sampleRequestorName" id="sampleRequestorName" placeholder="Técnico Responsável pela Colheita" value="<?php echo $eidInfo['sample_requestor_name']; ?>" />
                                        </td>
                                        <th>Contacto</th>
                                        <td>
                                            <input class="form-control" type="text" name="sampleRequestorPhone" id="sampleRequestorPhone" placeholder="Contacto" value="<?php echo $eidInfo['sample_requestor_phone']; ?>" />
                                        </td>
                                    </tr>

                                </table>


                            </div>
                        </div>
                        <?php if ($sarr['sc_user_type'] != 'remoteuser') { ?>
                            <div class="box box-primary">
                                <div class="box-body">
                                    <div class="box-header with-border">
                                        <h3 class="box-title">Informações laboratoriais </h3>
                                    </div>
                                    <table class="table" style="width:100%">
                                        <tr>
                                            <td><label for="labId">Lab Name </label> </td>
                                            <td>
                                                <select name="labId" id="labId" class="form-control" title="Please select Testing Lab name" style="width:100%;">
                                                    <?= $general->generateSelectOptions($testingLabs, $eidInfo['lab_id'], '-- Selecione --'); ?>
                                                </select>
                                            </td>
                                            <th></th>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <th><label for="">Data e Hora da Recepção da Amostra </label></th>
                                            <td>
                                                <input type="text" class="form-control dateTime" id="sampleReceivedDate" name="sampleReceivedDate" placeholder="e.g 09-Jan-1992 05:30" title="Data e Hora da Recepção da Amostra" <?php echo $labFieldDisabled; ?> onchange="" style="width:100%;" value="<?php echo $general->humanDateFormat($eidInfo['sample_received_at_vl_lab_datetime']) ?>" />
                                            </td>
                                            <th><label for="">Responsável da recepção </label></th>
                                            <td>
                                                <input type="text" class="form-control" id="labReceptionPerson" name="labReceptionPerson" placeholder="Responsável da recepção" title="Técnico Responsável pela Recepção da Amostra " <?php echo $labFieldDisabled; ?> onchange="" style="width:100%;" value="<?php echo $eidInfo['lab_reception_person']; ?>" />
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Rejeição da amostra ? </th>
                                            <td>
                                                <select class="form-control" name="isSampleRejected" id="isSampleRejected" title="Rejeição da amostra ?">
                                                    <option value=''> -- Selecione -- </option>
                                                    <option value="yes" <?php echo ($eidInfo['is_sample_rejected'] == 'yes') ? "selected='selected'" : ""; ?>> Yes </option>
                                                    <option value="no" <?php echo ($eidInfo['is_sample_rejected'] == 'no') ? "selected='selected'" : ""; ?>> No </option>
                                                </select>
                                            </td>

                                            <th class="rejected" style="display: none;">Razão de rejeição</th>
                                            <td class="rejected" style="display: none;">
                                                <select class="form-control" name="sampleRejectionReason" id="sampleRejectionReason">
                                                    <option value="">-- Selecione --</option>
                                                    <?php foreach ($rejectionTypeResult as $type) { ?>
                                                        <optgroup label="<?php echo ucwords($type['rejection_type']); ?>">
                                                            <?php
                                                            foreach ($rejectionResult as $reject) {
                                                                if ($type['rejection_type'] == $reject['rejection_type']) { ?>
                                                                    <option value="<?php echo $reject['rejection_reason_id']; ?>" <?php echo ($eidInfo['reason_for_sample_rejection'] == $reject['rejection_reason_id']) ? 'selected="selected"' : ''; ?>><?php echo ucwords($reject['rejection_reason_name']); ?></option>
                                                            <?php }
                                                            } ?>
                                                        </optgroup>
                                                    <?php } ?>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="width:25%;"><label for="">Data da Quantificação </label></td>
                                            <td style="width:25%;">
                                                <input type="text" class="form-control dateTime" id="sampleTestedDateTime" name="sampleTestedDateTime" placeholder="e.g 09-Jan-1992 05:30" title="Data da Quantificação" <?php echo $labFieldDisabled; ?> onchange="" style="width:100%;" value="<?php echo $general->humanDateFormat($eidInfo['sample_tested_datetime']) ?>" />
                                            </td>

                                            <th>Resultado</th>
                                            <td>
                                                <select class="form-control result-focus" name="result" id="result">
                                                    <option value=''> -- Selecione -- </option>
                                                    <?php foreach ($eidResults as $eidResultKey => $eidResultValue) { ?>
                                                        <option value="<?php echo $eidResultKey; ?>" <?php echo ($eidInfo['result'] == $eidResultKey) ? "selected='selected'" : ""; ?>> <?php echo $eidResultValue; ?> </option>
                                                    <?php } ?>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr class="change-reason">
                                            <th class="change-reason" style="display: none;">Razão para mudar <span class="mandatory">*</span></td>
                                            <td class="change-reason" style="display: none;"><textarea type="text" name="reasonForChanging" id="reasonForChanging" class="form-control date" placeholder="Insira o motivo da mudança" title="Por favor, indique o motivo da mudança"></textarea></td>
                                            <th></th>
                                            <td></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        <?php } ?>

                    </div>
                    <!-- /.box-body -->
                    <div class="box-footer">

                        <a class="btn btn-primary" href="javascript:void(0);" onclick="validateNow();return false;">Save</a>
                        <input type="hidden" name="revised" id="revised" value="no" />
                        <input type="hidden" name="formId" id="formId" value="8" />
                        <input type="hidden" name="eidSampleId" id="eidSampleId" value="<?php echo ($eidInfo['eid_id']); ?>" />
                        <input type="hidden" name="sampleCodeCol" id="sampleCodeCol" value="<?php echo $eidInfo['sample_code']; ?>" />
                        <input type="hidden" name="oldStatus" id="oldStatus" value="<?php echo $eidInfo['result_status']; ?>" />
                        <input type="hidden" name="provinceCode" id="provinceCode" />
                        <input type="hidden" name="provinceId" id="provinceId" />
                        <a href="/eid/requests/eid-requests.php" class="btn btn-default"> Cancel</a>
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
            $("#district").html("<option value=''> -- Selecione -- </option>");
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
            $("#facilityId").html("<option value=''> -- Selecione -- </option>");
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

        <?php if (isset($eidInfo['mother_vl_result']) && !empty($eidInfo['mother_vl_result'])) { ?>
            updateMotherViralLoad();
        <?php } ?>

        $("#motherViralLoadCopiesPerMl").on("change keyup paste", function() {
            var motherVl = $("#motherViralLoadCopiesPerMl").val();
            //var motherVlText = $("#motherViralLoadText").val();
            if (motherVl != '') {
                $("#motherViralLoadText").val('');
            }
        });

    });
</script>