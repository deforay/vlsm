<?php

// imported in covid-19-edit-request.php based on country in global config

ob_start();


//Funding source list
$fundingSourceQry = "SELECT * FROM r_funding_sources WHERE funding_source_status='active' ORDER BY funding_source_name ASC";
$fundingSourceList = $db->query($fundingSourceQry);

//Implementing partner list
$implementingPartnerQry = "SELECT * FROM r_implementation_partners WHERE i_partner_status='active' ORDER BY i_partner_name ASC";
$implementingPartnerList = $db->query($implementingPartnerQry);


$covid19Obj = new Model_Covid19($db);


$covid19Results = $covid19Obj->getCovid19Results();
$specimenTypeResult = $covid19Obj->getCovid19SampleTypes();

$covid19Symptoms = $covid19Obj->getCovid19SymptomsDRC();
$covid19SelectedSymptoms = $covid19Obj->getCovid19SymptomsByFormId($covid19Info['covid19_id']);

$covid19ReasonsForTesting = $covid19Obj->getCovid19ReasonsForTestingDRC();
$covid19SelectedReasonsForTesting = $covid19Obj->getCovid19ReasonsForTestingByFormId($covid19Info['covid19_id']);

$covid19Comorbidities = $covid19Obj->getCovid19Comorbidities();
$covid19SelectedComorbidities = $covid19Obj->getCovid19ComorbiditiesByFormId($covid19Info['covid19_id']);


// Getting the list of Provinces, Districts and Facilities

$rKey = '';
$pdQuery = "SELECT * FROM province_details";


if ($sarr['user_type'] == 'remoteuser') {
    $sampleCodeKey = 'remote_sample_code_key';
    $sampleCode = 'remote_sample_code';
    //check user exist in user_facility_map table
    $chkUserFcMapQry = "SELECT user_id from vl_user_facility_map where user_id='" . $_SESSION['userId'] . "'";
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
$province .= "<option value=''> -- Select -- </option>";
foreach ($pdResult as $provinceName) {
    $province .= "<option data-code='" . $provinceName['province_code'] . "' data-province-id='" . $provinceName['province_id'] . "' data-name='" . $provinceName['province_name'] . "' value='" . $provinceName['province_name'] . "##" . $provinceName['province_code'] . "'>" . ucwords($provinceName['province_name']) . "</option>";
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


//suggest N°EPID when lab user add request sample
$sampleSuggestion = '';
$sampleSuggestionDisplay = 'display:none;';
$sCode = (isset($_GET['c']) && $_GET['c'] != '') ? $_GET['c'] : '';
if ($sarr['user_type'] == 'vluser' && $sCode != '') {
    $vlObj = new Model_Covid19($db);
    $sampleCollectionDate = explode(" ", $sampleCollectionDate);
    $sampleCollectionDate = $general->humanDateFormat($sampleCollectionDate[0]);
    $sampleSuggestionJson = $vlObj->generateCovid19SampleCode($stateResult[0]['province_code'], $sampleCollectionDate, 'png');
    $sampleCodeKeys = json_decode($sampleSuggestionJson, true);
    $sampleSuggestion = $sampleCodeKeys['sampleCode'];
    $sampleSuggestionDisplay = 'display:block;';
}

?>


<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1><i class="fa fa-edit"></i> COVID-19 VIRUS LABORATORY TEST DRC REQUEST FORM</h1>
        <ol class="breadcrumb">
            <li><a href="/"><i class="fa fa-dashboard"></i> Accueil</a></li>
            <li class="active">Ajouter une nouvelle demande</li>
        </ol>
    </section>
    <!-- Main content -->
    <section class="content">
        <!-- SELECT2 EXAMPLE -->
        <div class="box box-default">
            <div class="box-header with-border">
                <div class="pull-right" style="font-size:15px;"><span class="mandatory">*</span> indique un champ obligatoire &nbsp;</div>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
                <!-- form start -->
                <form class="form-horizontal" method="post" name="editCovid19RequestForm" id="editCovid19RequestForm" autocomplete="off" action="covid-19-edit-request-helper.php">
                    <div class="box-body">
                        <div class="box box-default">
                            <div class="box-body disabledForm">
                                <div class="box-header with-border sectionHeader">
                                    <h3 class="box-title">INFORMATIONS SUR LE SITE</h3>
                                </div>
                                <div class="box-header with-border">
                                    <h3 class="box-title" style="font-size:1em;">À remplir par le clinicien / infirmier demandeur</h3>
                                </div>
                                <table class="table" style="width:100%">
                                    <?php if ($covid19Info['remote_sample'] == 'yes') { ?>
                                        <tr>
                                            <?php
                                            if ($covid19Info['sample_code'] != '') {
                                            ?>
                                                <td colspan="4"> <label for="sampleSuggest" class="text-danger">&nbsp;&nbsp;&nbsp;Veuillez noter que cet exemple distant a déjà été importé avec VLSM N°EPID </td>
                                                <td colspan="2" align="left"> <?php echo $covid19Info['sample_code']; ?></label> </td>
                                            <?php
                                            } else {
                                            ?>
                                                <td colspan="4"> <label for="sampleSuggest">N°EPID (peut changer lors de la soumission du formulaire)</label></td>
                                                <td colspan="2" align="left"> <?php echo $sampleSuggestion; ?></td>
                                            <?php } ?>
                                        </tr>
                                    <?php } ?>
                                    <tr>
                                        <?php if ($sarr['user_type'] == 'remoteuser') { ?>
                                            <td><label for="sampleCode">N°EPID </label> </td>
                                            <td colspan="5">
                                                <span id="sampleCodeInText" style="width:30%;border-bottom:1px solid #333;"><?php echo ($sCode != '') ? $sCode : $covid19Info[$sampleCode]; ?></span>
                                                <input type="hidden" class="<?php echo $sampleClass; ?>" id="sampleCode" name="sampleCode" value="<?php echo ($sCode != '') ? $sCode : $covid19Info[$sampleCode]; ?>" />
                                            </td>
                                        <?php } else { ?>
                                            <td><label for="sampleCode">N°EPID </label><span class="mandatory">*</span> </td>
                                            <td colspan="5">
                                                <input type="text" readonly value="<?php echo ($sCode != '') ? $sCode : $covid19Info[$sampleCode]; ?>" class="form-control isRequired" id="sampleCode" name="sampleCode" placeholder="N°EPID" title="N°EPID" style="width:30%;" onchange="" />
                                            </td>
                                        <?php } ?>
                                    </tr>
                                    <tr>
                                        <td><label for="province">Province </label><span class="mandatory">*</span></td>
                                        <td>
                                            <select class="form-control isRequired" name="province" id="province" title="Province" onchange="getfacilityDetails(this);" style="width:100%;">
                                                <?php echo $province; ?>
                                            </select>
                                        </td>
                                        <td><label for="district">Zone de Santé  </label><span class="mandatory">*</span></td>
                                        <td>
                                            <select class="form-control isRequired" name="district" id="district" title="Zone de Santé " style="width:100%;" onchange="getfacilityDistrictwise(this);">
                                                <option value=""> -- Select -- </option>
                                            </select>
                                        </td>
                                        <td><label for="facilityId">Nom de l'installation </label><span class="mandatory">*</span></td>
                                        <td>
                                            <select class="form-control isRequired " name="facilityId" id="facilityId" title="Nom de l'installation" style="width:100%;" onchange="getfacilityProvinceDetails(this);">
                                                <?php echo $facility; ?>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <?php if ($sarr['user_type'] == 'remoteuser') { ?>
                                            <!-- <tr> -->
                                            <td><label for="labId">LAB ID <span class="mandatory">*</span></label> </td>
                                            <td>
                                                <select name="labId" id="labId" class="form-control isRequired" title="LAB ID" style="width:100%;">
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
                                
                                <div class="box-header with-border sectionHeader">
                                    <h3 class="box-title">INFORMATION PATIENT</h3>
                                </div>
                                <table class="table" style="width:100%">

                                    <tr>
                                        <th style="width:15% !important"><label for="firstName">Prénom <span class="mandatory">*</span> </label></th>
                                        <td style="width:35% !important">
                                            <input type="text" class="form-control isRequired" id="firstName" name="firstName" placeholder="Prénom" title="Prénom" style="width:100%;" value="<?php echo $covid19Info['patient_name']; ?>" />
                                        </td>
                                        <th style="width:15% !important"><label for="lastName">Nom de famille</label></th>
                                        <td style="width:35% !important">
                                            <input type="text" class="form-control " id="lastName" name="lastName" placeholder="Nom de famille" title="Nom de famille" style="width:100%;" value="<?php echo $covid19Info['patient_surname']; ?>" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <th style="width:15% !important"><label for="patientId">Code Patient <span class="mandatory">*</span> </label></th>
                                        <td style="width:35% !important">
                                            <input type="text" class="form-control isRequired" id="patientId" name="patientId" placeholder="Code Patient" title="Code Patient" style="width:100%;" value="<?php echo $covid19Info['patient_id']; ?>" />
                                        </td>
                                        <th><label for="patientDob">Date de naissance <span class="mandatory">*</span> </label></th>
                                        <td>
                                            <input type="text" class="form-control isRequired" id="patientDob" name="patientDob" placeholder="Date de naissance" title="Date de naissance" style="width:100%;" onchange="calculateAgeInYears();" value="<?php echo $general->humanDateFormat($covid19Info['patient_dob']); ?>" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Age (years)</th>
                                        <td><input type="number" max="150" maxlength="3" oninput="this.value=this.value.slice(0,$(this).attr('maxlength'))" class="form-control " id="patientAge" name="patientAge" placeholder="Age (years)" title="Age (years)" style="width:100%;" value="<?php echo $covid19Info['patient_age']; ?>" /></td>
                                        <th><label for="patientGender">Sexe <span class="mandatory">*</span> </label></th>
                                        <td>
                                            <select class="form-control isRequired" name="patientGender" id="patientGender">
                                                <option value=''> -- Select -- </option>
                                                <option value='male' <?php echo ($covid19Info['patient_gender'] == 'male') ? "selected='selected'" : ""; ?>> Homme </option>
                                                <option value='female' <?php echo ($covid19Info['patient_gender'] == 'female') ? "selected='selected'" : ""; ?>> Femme </option>
                                                <option value='other' <?php echo ($covid19Info['patient_gender'] == 'other') ? "selected='selected'" : ""; ?>> Other </option>

                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th><label for="isPatientPregnant">Enceinte</label></th>
                                        <td>
                                            <select class="form-control" name="isPatientPregnant" id="isPatientPregnant">
                                                <option value=''> -- Select -- </option>
                                                <option value='yes' <?php echo ($covid19Info['is_patient_pregnant'] == 'yes') ? "selected='selected'" : ""; ?>> Enceinte </option>
                                                <option value='no' <?php echo ($covid19Info['is_patient_pregnant'] == 'no') ? "selected='selected'" : ""; ?>> Pas Enceinte </option>
                                                <option value='unknown' <?php echo ($covid19Info['is_patient_pregnant'] == 'unknown') ? "selected='selected'" : ""; ?>> Inconnue </option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Numéro de téléphone</th>
                                        <td><input type="text" class="form-control " id="patientPhoneNumber" name="patientPhoneNumber" placeholder="Numéro de téléphone" title="Numéro de téléphone" style="width:100%;" value="<?php echo $covid19Info['patient_phone_number']; ?>" /></td>

                                        <th>Adresse du patient</th>
                                        <td><textarea class="form-control " id="patientAddress" name="patientAddress" placeholder="Adresse du patient" title="Adresse du patient" style="width:100%;" onchange=""><?php echo $covid19Info['patient_address']; ?></textarea></td>
                                    </tr>
                                    <tr>
                                        <th>Province du patient</th>
                                        <td><input type="text" value="<?php echo $covid19Info['patient_province']; ?>" class="form-control " id="patientProvince" name="patientProvince" placeholder="Province du patient" title="Province du patient" style="width:100%;" /></td>

                                        <th>District des patients</th>
                                        <td><input class="form-control" value="<?php echo $covid19Info['patient_district']; ?>" id="patientDistrict" name="patientDistrict" placeholder="District des patients" title="District des patients" style="width:100%;"></td>
                                    </tr>
                                    <tr>
                                        <th>Pays de résidence</th>
                                        <td><input type="text" class="form-control" value="<?php echo $covid19Info['patient_nationality']; ?>" id="patientNationality" name="patientNationality" placeholder="Pays de résidence" title="Pays de résidence" style="width:100%;" /></td>

                                        <th></th>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td colspan="4">
                                            <h4>Les détails du vol</h4>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Compagnie aérienne</th>
                                        <td><input type="text" class="form-control " value="<?php echo $covid19Info['flight_airline']; ?>" id="airline" name="airline" placeholder="Compagnie aérienne" title="Compagnie aérienne" style="width:100%;" /></td>

                                        <th>Numéro de siège</th>
                                        <td><input type="text" class="form-control " value="<?php echo $covid19Info['flight_seat_no']; ?>" id="seatNo" name="seatNo" placeholder="Numéro de siège" title="Numéro de siège" style="width:100%;" /></td>
                                    </tr>
                                    <tr>
                                        <th>Date et heure d'arrivée</th>
                                        <td><input type="text" class="form-control dateTime" value="<?php echo $general->humanDateFormat($covid19Info['flight_arrival_datetime']); ?>" id="arrivalDateTime" name="arrivalDateTime" placeholder="Date et heure d'arrivée" title="Date et heure d'arrivée" style="width:100%;" /></td>

                                        <th>Aeroport DE DEPART</th>
                                        <td><input type="text" class="form-control" value="<?php echo $covid19Info['flight_airport_of_departure']; ?>" id="airportOfDeparture" name="airportOfDeparture" placeholder="Aeroport DE DEPART" title="Aeroport DE DEPART" style="width:100%;" /></td>
                                    </tr>
                                    <tr>
                                        <th>Transit</th>
                                        <td><input type="text" class="form-control" value="<?php echo $covid19Info['flight_transit']; ?>" id="transit" name="transit" placeholder="Transit" title="Transit" style="width:100%;" /></td>
                                        <th>Raison de la visite (le cas échéant)</th>
                                        <td><input type="text" class="form-control" value="<?php echo $covid19Info['reason_of_visit']; ?>" id="reasonOfVisit" name="reasonOfVisit" placeholder="Raison de la visite (le cas échéant)" title="Raison de la visite (le cas échéant)" style="width:100%;" /></td>

                                    </tr>
                                </table>
                                <div class="box-header with-border sectionHeader">
                                    <h3 class="box-title">
                                        Signes vitaux du patient 
                                    </h3>
                                </div>
                                <table class="table">
                                    <tr>
                                        <th style="width: 15%;"><label for="specimenType"> Type(s) d' échantillon(s) dans le tube (cochez au moins une des cases suivants) <span class="mandatory">*</span></label></th>
                                        <td style="width: 35%;">
                                            <select class="form-control isRequired" id="specimenType" name="specimenType" title="Type(s) d' échantillon(s) dans le tube">
                                                <option value="">--Select--</option>
                                                <!-- <option value="oropharyngeal">Oropharyngée</option>
                                                <option value="nasal-both">Nasale / Les Deux</option>
                                                <option value="sputum">Expectorations</option>
                                                <option value="alveolar-broncho-wash">Lavage broncho alvéolaire</option>
                                                <option value="tracheal-aspiration">Aspiration trachéale</option>
                                                <option value="serum">Sérum</option> -->
                                                <?php echo $general->generateSelectOptions($specimenTypeResult, $covid19Info['specimen_type']); ?>
                                            </select>
                                        </td>
                                        <th style="width:15% !important"><label for="specimenType">Date de prélèvement de l'échantillon <span class="mandatory">*</span></label></th>
                                        <td style="width:35% !important;">
                                            <input class="form-control isRequired" value="<?php echo date('d-M-Y H:i:s',strtotime($covid19Info['sample_collection_date']));?>" type="text" name="sampleCollectionDate" id="sampleCollectionDate" placeholder="Date de prélèvement de l'échantillon" title="Date de prélèvement de l'échantillon" onchange="sampleCodeGeneration();" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <th colspan="4"style="width:15% !important">Symptômes <span class="mandatory">*</span> </th>
                                    </tr>
                                    <tr>
                                        <td colspan="4">
                                            <table  id="symptomsTable" class="table table-bordered">
                                                <?php $index = 0; foreach ($covid19Symptoms as $symptomId => $symptomName) { ?>
                                                    <tr class="row<?php echo $index;?>">
                                                        <th style="width:50%;"><?php echo $symptomName; ?></th>
                                                        <td style="width:50%;">
                                                            <input name="symptomId[]" type="hidden" value="<?php echo $symptomId; ?>">
                                                            <select name="symptomDetected[]" id="symptomDetected<?php echo $symptomId; ?>" class="form-control isRequired" title="Veuillez choisir la valeur pour <?php echo $symptomName; ?>" style="width:100%" onchange="checkSubSymptoms(this.value,<?php echo $symptomId;?>,<?php echo $index;?>);">
                                                                <option value="">-- Select --</option>
                                                                <option value='yes' <?php echo (isset($covid19SelectedSymptoms[$symptomId]) && $covid19SelectedSymptoms[$symptomId] == 'yes') ? "selected='selected'" : ""; ?>> Oui </option>
                                                                <option value='no' <?php echo (isset($covid19SelectedSymptoms[$symptomId]) && $covid19SelectedSymptoms[$symptomId] == 'no') ? "selected='selected'" : ""; ?>> Non </option>
                                                                <option value='unknown' <?php echo (isset($covid19SelectedSymptoms[$symptomId]) && $covid19SelectedSymptoms[$symptomId] == 'unknown') ? "selected='selected'" : ""; ?>> Inconnu </option>
                                                            </select>
                                                        </td>
                                                    </tr>
                                                <?php $index++; } ?>
                                            </table>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th colspan="4"style="width:15% !important">Antécédents Médicaux <span class="mandatory">*</span> </th>
                                    </tr>
                                    <tr>
                                        <td colspan="4">
                                            <table  id="symptomsTable" class="table table-bordered">
                                                <?php $index = 0; foreach ($covid19Comorbidities as $comorbiditiesId => $comorbiditiesName) { ?>
                                                    <tr class="row<?php echo $index;?>">
                                                        <th style="width:50%;"><?php echo $comorbiditiesName; ?></th>
                                                        <td style="width:50%;">
                                                            <input name="comorbidityId[]" type="hidden" value="<?php echo $comorbiditiesId; ?>">
                                                            <select name="comorbidityDetected[]" class="form-control isRequired" title="Antécédents Médicaux <?php echo $comorbiditiesName; ?>" style="width:100%">
                                                                <option value="">-- Select --</option>
                                                                <option value='yes' <?php echo (isset($covid19SelectedComorbidities[$comorbiditiesId]) && $covid19SelectedComorbidities[$comorbiditiesId] == 'yes') ? "selected='selected'" : ""; ?>> Oui </option>
                                                                <option value='no' <?php echo (isset($covid19SelectedComorbidities[$comorbiditiesId]) && $covid19SelectedComorbidities[$comorbiditiesId] == 'no') ? "selected='selected'" : ""; ?>> Non </option>
                                                                <option value='unknown' <?php echo (isset($covid19SelectedComorbidities[$comorbiditiesId]) && $covid19SelectedComorbidities[$comorbiditiesId] == 'unknown') ? "selected='selected'" : ""; ?>> Inconnu </option>
                                                            </select>
                                                        </td>
                                                    </tr>
                                                <?php $index++; } ?>
                                            </table>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th colspan="4"style="width:15% !important">Définition de cas <span class="mandatory">*</span> </th>
                                    </tr>
                                    <tr>
                                        <td colspan="4">
                                            <table  id="responseTable" class="table table-bordered">
                                                <?php $index = 0; foreach ($covid19ReasonsForTesting as $reasonId => $responseName) { ?>
                                                    <tr class="row<?php echo $index;?>">
                                                        <th style="width:50%;"><?php echo $responseName; ?></th>
                                                        <td style="width:50%;">
                                                            <input name="responseId[]" type="hidden" value="<?php echo $reasonId; ?>">
                                                            <select name="responseDetected[]" class="form-control isRequired" title="Définition de cas <?php echo $responseName; ?>" style="width:100%" onchange="checkSubResponse(this.value,<?php echo $reasonId;?>,<?php echo $index;?>);">
                                                                <option value="">-- Select --</option>
                                                                <option value='yes' <?php echo (isset($covid19SelectedReasonsForTesting[$reasonId]) && $covid19SelectedReasonsForTesting[$reasonId] == 'yes') ? "selected='selected'" : ""; ?>> Oui </option>
                                                                <option value='no' <?php echo (isset($covid19SelectedReasonsForTesting[$reasonId]) && $covid19SelectedReasonsForTesting[$reasonId] == 'no') ? "selected='selected'" : ""; ?>> Non </option>
                                                                <option value='unknown' <?php echo (isset($covid19SelectedReasonsForTesting[$reasonId]) && $covid19SelectedReasonsForTesting[$reasonId] == 'unknown') ? "selected='selected'" : ""; ?>> Inconnu </option>
                                                            </select>
                                                        </td>
                                                    </tr>
                                                <?php $index++; } ?>
                                            </table>
                                        </td>
                                    </tr>
                                </table>

                                <div class="box-header with-border sectionHeader">
                                    <h3 class="box-title">
                                        VOYAGE ET CONTACT 
                                    </h3>
                                </div>
                                <table class="table">
                                    <tr>
                                        <th style="width: 15% !important;"><label for="hasRecentTravelHistory">Avez-vous voyagé au cours des 14 derniers jours ?  </label></th>
                                        <td style="width:35% !important;">
                                            <select class="form-control" id="hasRecentTravelHistory" name="hasRecentTravelHistory" title="Avez-vous voyagé au cours des 14 derniers jours ?">
                                                <option value="">--Select--</option>
                                                <option value="yes" <?php echo ($covid19Info['has_recent_travel_history'] == 'yes') ? "selected='selected'" : ""; ?>>Oui</option>
                                                <option value="no" <?php echo ($covid19Info['has_recent_travel_history'] == 'no') ? "selected='selected'" : ""; ?>>Non</option>
                                                <option value="unknown" <?php echo ($covid19Info['has_recent_travel_history'] == 'unknown') ? "selected='selected'" : ""; ?>>Inconnu</option>
                                            </select>
                                        </td>                                        
                                        <th style="width: 15% !important;"><label for="countryName">Si oui, dans quels pays?</label></th>
                                        <td style="width:35% !important;">
                                            <input type="text" value="<?php echo $covid19Info['travel_country_names'];?>" class="form-control" id="countryName" name="countryName" placeholder="Si oui, dans quels pays ?" title="Si oui, dans quels pays?"/>
                                        </td>
                                    </tr>

                                    <tr>
                                        <th style="width: 15% !important;"><label for="returnDate">Date de retour</label></th>
                                        <td style="width:35% !important;">
                                            <input type="text" value="<?php echo $general->humanDateFormat($covid19Info['travel_return_date']);?>" class="form-control date" id="returnDate" name="returnDate" placeholder="e.g 09-Jan-1992" title="Date de retour"/>
                                        </td>
                                    </tr>
                                </table>

                                <div class="box-header with-border sectionHeader">
                                    <h3 class="box-title">Résultats de laboratoire</h3>
                                </div>
                                <table class="table">
                                    <tr>
                                        <th style="width:15% !important"><label for="sampleCondition">Condition de l'échantillon</label></th>
                                        <td style="width:35% !important;">
                                            <select class="form-control" id="sampleCondition" name="sampleCondition" title="Condition de l'échantillon">
                                                <option value="">--Select--</option>
                                                <option value="adequate" <?php echo ($covid19Info['sample_condition'] == 'adequate') ? "selected='selected'" : ""; ?>>Adéquat</option>
                                                <option value="not-adequate" <?php echo ($covid19Info['sample_condition'] == 'not-adequate') ? "selected='selected'" : ""; ?>>Non Adéquat</option>
                                                <option value="other" <?php echo ($covid19Info['sample_condition'] == 'other') ? "selected='selected'" : ""; ?>>Autres</option>
                                            </select>
                                        </td>
                                        
                                        <th style="width:15% !important"><label for="confirmationLab">Méthode de confirmation en labo</label></th>
                                        <td style="width:35% !important;">
                                            <select class="form-control" id="confirmationLab" name="confirmationLab" title="Méthode de confirmation en labo">
                                                <option value="">--Select--</option>
                                                <option value="PCR/RT-PCR" <?php echo ($covid19Info['medical_history'] == 'PCR/RT-PCR') ? "selected='selected'" : ""; ?>>PCR/RT-PCR</option>
                                                <option value="RdRp-SARS CoV-2" <?php echo ($covid19Info['medical_history'] == 'RdRp-SARS CoV-2') ? "selected='selected'" : ""; ?>>RdRp-SARS CoV-2</option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th style="width: 15% !important;"><label for="resultPcr">Date de Result PCR</label></th>
                                        <td style="width:35% !important;">
                                            <input type="text" value="<?php echo $covid19Info['number_of_days_sick'];?>" class="form-control date" id="resultPcr" name="resultPcr" placeholder="e.g 09-Jan-1992" title="Date de Result PCR"/>
                                        </td>
                                        <th style="width: 15% !important;"><label for="numberOfDaysSick">Depuis combien de jours êtes-vous malade?</label></th>
                                        <td style="width:35% !important;">
                                            <input type="text" value="<?php echo $covid19Info['number_of_days_sick'];?>" class="form-control" id="numberOfDaysSick" name="numberOfDaysSick" placeholder="Depuis combien de jours êtes-vous malade?" title="Date de Result PCR"/>
                                        </td>
                                    </tr>
                                </table>

                                <div class="box-header with-border sectionHeader">
                                    <h3 class="box-title">SIGNES ET SYMPTÔMES CLINIQUES</h3>
                                </div>
                                <table class="table">
                                    <tr>
                                        <th style="width:15% !important">Date d'apparition des symptômes <span class="mandatory">*</span> </th>
                                        <td style="width:35% !important;">
                                            <input class="form-control date isRequired" type="text" name="dateOfSymptomOnset" id="dateOfSymptomOnset" placeholder="Date d'apparition des symptômes" value="<?php echo $general->humanDateFormat($covid19Info['date_of_symptom_onset']); ?> " />
                                        </td>
                                        <th style="width:15% !important">Date de la consultation initiale</th>
                                        <td style="width:35% !important;">
                                            <input class="form-control date" type="text" name="dateOfInitialConsultation" id="dateOfInitialConsultation" placeholder="Date of Initial Consultation" value="<?php echo $general->humanDateFormat($covid19Info['date_of_initial_consultation']); ?> " />
                                        </td>
                                    </tr>
                                    <tr>
                                        <th style="width:15% !important"><label for="medicalHistory"></label>Antécédents Médicaux</th>
                                        <td style="width:35% !important;">
                                            <select name="medicalHistory" id="medicalHistory" class="form-control" title="Antécédents Médicaux">
                                                <option value="">--Select--</option>
                                                <option value="yes" <?php echo ($covid19Info['medical_history'] == 'yes') ? "selected='selected'" : ""; ?>>Oui</option>
                                                <option value="no" <?php echo ($covid19Info['medical_history'] == 'no') ? "selected='selected'" : ""; ?>>Non</option>
                                                <option value="unknown" <?php echo ($covid19Info['medical_history'] == 'unknown') ? "selected='selected'" : ""; ?>>Inconnu</option>
                                            </select>
                                        </td>
                                        <th style="width:15% !important"><label for="recentHospitalization"></label>Avez-vous été hospitalisé durant les 12 derniers mois ? Have you been hospitalized in the past 12 months ? </th>
                                        <td style="width:35% !important;">
                                            <select name="recentHospitalization" id="recentHospitalization" class="form-control" title="Avez-vous été hospitalisé durant les 12 derniers mois ? Have you been hospitalized in the past 12 months ? ">
                                                <option value="">--Select--</option>
                                                <option value="yes" <?php echo ($covid19Info['recent_hospitalization'] == 'yes') ? "selected='selected'" : ""; ?>>Oui</option>
                                                <option value="no" <?php echo ($covid19Info['recent_hospitalization'] == 'no') ? "selected='selected'" : ""; ?>>Non</option>
                                                <option value="unknown" <?php echo ($covid19Info['recent_hospitalization'] == 'unknown') ? "selected='selected'" : ""; ?>>Inconnu</option>
                                            </select>
                                        </td>
                                    </tr>
                                    
                                    <tr>
                                        <th style="width:15% !important"><label for="patientLivesWithChildren"></label>Habitez-vous avec les enfants ?</th>
                                        <td style="width:35% !important;">
                                            <select name="patientLivesWithChildren" id="patientLivesWithChildren" class="form-control" title="Habitez-vous avec les enfants ?">
                                                <option value="">--Select--</option>
                                                <option value="yes" <?php echo ($covid19Info['patient_lives_with_children'] == 'yes') ? "selected='selected'" : ""; ?>>Oui</option>
                                                <option value="no" <?php echo ($covid19Info['patient_lives_with_children'] == 'no') ? "selected='selected'" : ""; ?>>Non</option>
                                                <option value="unknown" <?php echo ($covid19Info['patient_lives_with_children'] == 'unknown') ? "selected='selected'" : ""; ?>>Inconnu</option>
                                            </select>
                                        </td>
                                        <th style="width:15% !important"><label for="patientCaresForChildren"></label>prenez-vous soins des enfants ?</th>
                                        <td style="width:35% !important;">
                                            <select name="patientCaresForChildren" id="patientCaresForChildren" class="form-control" title="prenez-vous soins des enfants ?">
                                                <option value="">--Select--</option>
                                                <option value="yes" <?php echo ($covid19Info['patient_cares_for_children'] == 'yes') ? "selected='selected'" : ""; ?>>Oui</option>
                                                <option value="no" <?php echo ($covid19Info['patient_cares_for_children'] == 'no') ? "selected='selected'" : ""; ?>>Non</option>
                                                <option value="unknown" <?php echo ($covid19Info['patient_cares_for_children'] == 'unknown') ? "selected='selected'" : ""; ?>>Inconnu</option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th style="width:15% !important">Fever/Temperature (&deg;C) <span class="mandatory">*</span> </th>
                                        <td style="width:35% !important;">
                                            <input class="form-control isRequired" type="number" value="<?php echo $covid19Info['fever_temp']; ?>" name="feverTemp" id="feverTemp" placeholder="Fever/Temperature (in &deg;Celcius)" />
                                        </td>
                                        <th style="width:15% !important"><label for="temperatureMeasurementMethod">Température</label></th>
                                        <td style="width:35% !important;">
                                            <select name="temperatureMeasurementMethod" id="temperatureMeasurementMethod" class="form-control" title="Température">
                                                <option value="">--Select--</option>
                                                <option value="auxillary" <?php echo ($covid19Info['temperature_measurement_method'] == 'auxillary') ? "selected='selected'" : ""; ?>>Axillaire</option>
                                                <option value="oral" <?php echo ($covid19Info['temperature_measurement_method'] == 'oral') ? "selected='selected'" : ""; ?>>Orale</option>
                                                <option value="rectal" <?php echo ($covid19Info['temperature_measurement_method'] == 'rectal') ? "selected='selected'" : ""; ?>>Rectale</option>
                                                <option value="unknown" <?php echo ($covid19Info['temperature_measurement_method'] == 'unknown') ? "selected='selected'" : ""; ?>>Inconnu</option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th style="width:15% !important"><label for="respiratoryRate"> Fréquence Respiratoire</label></th>
                                        <td style="width:35% !important;">
                                            <input class="form-control" type="number" value="<?php echo $covid19Info['respiratory_rate'];?>" name="respiratoryRate" id="respiratoryRate" placeholder="Fréquence Respiratoire" title="Fréquence Respiratoire"/>
                                        </td>
                                        <th style="width:15% !important"><label for="oxygenSaturation"> Saturation en oxygène</label></th>
                                        <td style="width:35% !important;">
                                            <input class="form-control" type="number" value="<?php echo $covid19Info['oxygen_saturation'];?>" name="oxygenSaturation" id="oxygenSaturation" placeholder="Saturation en oxygène" title="Saturation en oxygène"/>
                                        </td>
                                    </tr>
                                </table>

                                <div class="box-header with-border sectionHeader">
                                    <h3 class="box-title">FACTEURS DE RISQUE ÉPIDÉMIOLOGIQUE ET EXPOSITIONS</h3>
                                </div>
                                <table class="table">
                                    <tr>
                                        <th style="width:15% !important">Contacts étroits du patient <span class="mandatory">*</span></th>
                                        <td colspan="3">
                                            <textarea name="closeContacts" class="form-control" placeholder="Contacts étroits du patient" title="Contacts étroits du patient" style="width:100%;min-height:100px;"><?php echo $covid19Info['close_contacts']; ?></textarea>
                                            <span class="text-danger">
                                            Ajoutez les noms et numéros de téléphone des contacts proches (foyer, membres de la famille, amis avec lesquels vous avez été en contact au cours des 14 derniers jours)
                                            </span>
                                        </td>

                                    </tr>
                                    <tr>
                                        <th>Occupation du patient</th>
                                        <td>
                                            <input class="form-control" value="<?php echo $covid19Info['patient_occupation']; ?>" type="text" name="patientOccupation" id="patientOccupation" placeholder="Occupation du patient" title="Occupation du patient" />
                                        </td>
                                        <th></th>
                                        <td></td>

                                    </tr>

                                </table>
                            </div>
                        </div>
                        <?php if ($sarr['user_type'] != 'remoteuser') { ?>
                            <div class="box box-primary">
                                <div class="box-body">
                                    <div class="box-header with-border">
                                        <h3 class="box-title">Réservé à une utilisation en laboratoire </h3>
                                    </div>
                                    <table class="table" style="width:100%">
                                        <tr>
                                            <th><label for="">Date de réception de l'échantillon </label></th>
                                            <td>
                                                <input type="text" class="form-control" id="sampleReceivedDate" name="sampleReceivedDate" placeholder="e.g 09-Jan-1992 05:30" title="Date de réception de l'échantillon" value="<?php echo $general->humanDateFormat($covid19Info['sample_received_at_vl_lab_datetime']) ?>" onchange="" style="width:100%;" />
                                            </td>
                                            <th><label for="sampleCondition">Condition de l'échantillon</label></th>
                                            <td>
                                                <select class="form-control" name="sampleCondition" id="sampleCondition" title="Condition de l'échantillon">
                                                    <option value=''> -- Select -- </option>
                                                    <option value="adequate" <?php echo ($covid19Info['sample_condition'] == 'adequate') ? "selected='selected'" : ""; ?>> Adéquat </option>
                                                    <option value="not-adequate" <?php echo ($covid19Info['sample_condition'] == 'not-adequate') ? "selected='selected'" : ""; ?>> Non Adéquat </option>
                                                    <option value="autres" <?php echo ($covid19Info['sample_condition'] == 'autres') ? "selected='selected'" : ""; ?>> Autres </option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="lab-show"><label for="labId">Nom du laboratoire</label> </td>
                                            <td class="lab-show">
                                                <select name="labId" id="labId" class="form-control" title="Nom du laboratoire" style="width:100%;">
                                                    <option value=""> -- Select -- </option>
                                                    <?php foreach ($lResult as $labName) { ?>
                                                        <option value="<?php echo $labName['facility_id']; ?>" <?php echo ($covid19Info['lab_id'] == $labName['facility_id']) ? "selected='selected'" : ""; ?>><?php echo ucwords($labName['facility_name']); ?></option>
                                                    <?php } ?>
                                                </select>
                                            </td>
                                            <th>L'échantillon est-il rejeté?</th>
                                            <td>
                                                <select class="form-control result-focus" name="isSampleRejected" id="isSampleRejected" title="L'échantillon est-il rejeté?">
                                                    <option value=''> -- Select -- </option>
                                                    <option value="yes" <?php echo ($covid19Info['is_sample_rejected'] == 'yes') ? "selected='selected'" : ""; ?>> Oui </option>
                                                    <option value="no" <?php echo ($covid19Info['is_sample_rejected'] == 'no') ? "selected='selected'" : ""; ?>> Non </option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th class="show-rejection" style="display:none;">Raison du rejet</th>
                                            <td class="show-rejection" style="display:none;">
                                                <select class="form-control" name="sampleRejectionReason" id="sampleRejectionReason" title="Raison du rejet">
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
                                            <th class="show-rejection" style="display: none;">Date de rejet<span class="mandatory">*</span></th>
                                            <td class="show-rejection" style="display: none;"><input value="<?php echo $general->humanDateFormat($covid19Info['rejection_on']); ?>" class="form-control date rejection-date" type="text" name="rejectionDate" id="rejectionDate" placeholder="Date de rejet" title="Date de rejet"/></td>
                                        </tr>
                                        <tr>
                                            <td colspan="4">
                                                <table class="table table-bordered table-striped" id="testNameTable">
                                                    <thead>
                                                        <tr>
                                                            <th class="text-center">Test non</th>
                                                            <th class="text-center">Nom du Testkit (ou) Méthode de test utilisée</th>
                                                            <th class="text-center">Date de l'analyse</th>
                                                            <th class="text-center">Résultat du test</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="testKitNameTable">
                                                        <?php if (isset($covid19TestInfo) && count($covid19TestInfo) > 0) {
                                                            foreach ($covid19TestInfo as $indexKey => $rows) { ?>
                                                                <tr>
                                                                    <td class="text-center"><?php echo ($indexKey + 1); ?><input type="hidden" name="testId[]" value="<?php echo base64_encode($covid19TestInfo[$indexKey]['test_id']); ?>"></td>
                                                                    <td><input type="text" value="<?php echo $covid19TestInfo[$indexKey]['test_name']; ?>" name="testName[]" id="testName<?php echo ($indexKey + 1); ?>" class="form-control test-name-table-input" placeholder="Nom du test" title="Veuillez saisir le nom du test pour la ligne<?php echo ($indexKey + 1); ?>" /></td>
                                                                    <td><input type="text" value="<?php echo $general->humanDateFormat($covid19TestInfo[$indexKey]['sample_tested_datetime']); ?>" name="testDate[]" id="testDate<?php echo ($indexKey + 1); ?>" class="form-control test-name-table-input dateTime" placeholder="Testé sur" title="Veuillez saisir la ligne testée pour<?php echo ($indexKey + 1); ?>" /></td>
                                                                    <td><select class="form-control test-result test-name-table-input result-focus" name="testResult[]" id="testResult<?php echo ($indexKey + 1); ?>" title="Veuillez sélectionner le résultat pour la ligne<?php echo ($indexKey + 1); ?>">
                                                                            <option value=''> -- Select -- </option>
                                                                            <?php foreach ($covid19Results as $c19ResultKey => $c19ResultValue) { ?>
                                                                                <option value="<?php echo $c19ResultKey; ?>" <?php echo ($covid19TestInfo[$indexKey]['result'] == $c19ResultKey) ? "selected='selected'" : ""; ?>> <?php echo $c19ResultValue; ?> </option>
                                                                            <?php } ?>
                                                                        </select>
                                                                    </td>
                                                                    <td style="vertical-align:middle;text-align: center;">
                                                                        <a class="btn btn-xs btn-primary test-name-table" href="javascript:void(0);" onclick="insRow();"><i class="fa fa-plus"></i></a>&nbsp;
                                                                        <a class="btn btn-xs btn-default test-name-table" href="javascript:void(0);" onclick="removeAttributeRow(this.parentNode.parentNode);deleteRow('<?php echo base64_encode($covid19TestInfo[$indexKey]['test_id']); ?>');"><i class="fa fa-minus"></i></a>
                                                                    </td>
                                                                </tr>
                                                            <?php }
                                                        } else { ?>
                                                            <tr>
                                                                <td class="text-center">1</td>
                                                                <td><input type="text" name="testName[]" id="testName1" class="form-control test-name-table-input" placeholder="Nom du test" title="Veuillez saisir le nom du test pour les lignes 1" /></td>
                                                                <td><input type="text" name="testDate[]" id="testDate1" class="form-control test-name-table-input dateTime" placeholder="Testé sur" title="Veuillez saisir le test pour la ligne 1" /></td>
                                                                <td><select class="form-control test-result test-name-table-input" name="testResult[]" id="testResult1" title="Veuillez sélectionner le résultat pour la ligne 1">
                                                                        <option value=''> -- Select -- </option>
                                                                        <?php foreach ($covid19Results as $c19ResultKey => $c19ResultValue) { ?>
                                                                            <option value="<?php echo $c19ResultKey; ?>"> <?php echo $c19ResultValue; ?> </option>
                                                                        <?php } ?>
                                                                    </select>
                                                                </td>
                                                                <td style="vertical-align:middle;text-align: center;">
                                                                    <a class="btn btn-xs btn-primary test-name-table" href="javascript:void(0);" onclick="insRow();"><i class="fa fa-plus"></i></a>&nbsp;
                                                                    <a class="btn btn-xs btn-default test-name-table" href="javascript:void(0);" onclick="removeAttributeRow(this.parentNode.parentNode);"><i class="fa fa-minus"></i></a>
                                                                </td>
                                                            </tr>
                                                        <?php } ?>
                                                    </tbody>
                                                    <tfoot>
                                                        <tr>
                                                            <th colspan="3" class="text-right">Résultat final</th>
                                                            <td>
                                                                <select class="form-control result-focus" name="result" id="result" title="Résultat final">
                                                                    <option value=''> -- Select -- </option>
                                                                    <?php foreach ($covid19Results as $c19ResultKey => $c19ResultValue) { ?>
                                                                        <option value="<?php echo $c19ResultKey; ?>" <?php echo ($covid19Info['result'] == $c19ResultKey) ? "selected='selected'" : ""; ?>> <?php echo $c19ResultValue; ?> </option>
                                                                    <?php } ?>
                                                                </select>
                                                            </td>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </td>
                                        </tr>
                                        <tr class="change-reason" style="display: none;">
                                            <th>Raison du changement<span class="mandatory">*</span></td>
                                            <td colspan="3"><textarea type="text" name="reasonForChanging" id="reasonForChanging" class="form-control date" placeholder="Raison du changement" title="Raison du changement"></textarea></td>
                                        </tr>
                                        <tr>
                                            <th>Le résultat est-il autorisé?</th>
                                            <td>
                                                <select name="isResultAuthorized" id="isResultAuthorized" class="disabled-field form-control" title="Le résultat est-il autorisé?" style="width:100%">
                                                    <option value="">-- Select --</option>
                                                    <option value='yes' <?php echo ($covid19Info['is_result_authorised'] == 'yes') ? "selected='selected'" : ""; ?>> Oui </option>
                                                    <option value='no' <?php echo ($covid19Info['is_result_authorised'] == 'no') ? "selected='selected'" : ""; ?>> Non </option>
                                                </select>
                                            </td>
                                            <th>Autorisé par</th>
                                            <td><input type="text" value="<?php echo $covid19Info['authorized_by']; ?>" name="authorizedBy" id="authorizedBy" class="disabled-field form-control" placeholder="Autorisé par" title="Autorisé par"/></td>
                                        </tr>
                                        <tr>
                                            <th>Autorisé le</td>
                                            <td><input type="text" value="<?php echo $general->humanDateFormat($covid19Info['authorized_on']); ?>" name="authorizedOn" id="authorizedOn" class="disabled-field form-control date" placeholder="Autorisé le" title="Autorisé le"/></td>
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
                        <?php if ($arr['sample_code'] == 'auto' || $arr['sample_code'] == 'YY' || $arr['sample_code'] == 'MMYY') { ?>
                            <input type="hidden" name="sampleCodeFormat" id="sampleCodeFormat" value="<?php echo (isset($sFormat) && $sFormat != '') ? $sFormat : ''; ?>" />
                            <input type="hidden" name="sampleCodeKey" id="sampleCodeKey" value="<?php echo (isset($sKey) && $sKey != '') ? $sKey : ''; ?>" />
                        <?php } ?>
                        <a class="btn btn-primary" href="javascript:void(0);" onclick="validateNow();return false;">Save</a>
                        <input type="hidden" name="formId" id="formId" value="7" />
                        <input type="hidden" name="deletedRow" id="deletedRow" value="" />
                        <input type="hidden" name="covid19SampleId" id="covid19SampleId" value="<?php echo $covid19Info['covid19_id']; ?>" />
                        <input type="hidden" name="sampleCodeTitle" id="sampleCodeTitle" value="<?php echo $arr['sample_code']; ?>" />

                        <input type="hidden" name="sampleCodeTitle" id="sampleCodeTitle" value="<?php echo $arr['sample_code']; ?>" />
                        <input type="hidden" name="oldStatus" id="oldStatus" value="<?php echo $covid19Info['result_status']; ?>" />
                        <input type="hidden" name="provinceCode" id="provinceCode" />
                        <input type="hidden" name="provinceId" id="provinceId" />
                        <a href="/covid-19/requests/covid-19-requests.php" class="btn btn-default"> Cancel</a>
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
    tableRowId = <?php echo (isset($covid19TestInfo) && count($covid19TestInfo) > 0) ? (count($covid19TestInfo) + 1) : 2; ?>;
    deletedRow = [];

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
            $("#district").html("<option value=''> -- Select -- </option>");
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
        $("#provinceCode").val($("#province").find(":selected").attr("data-code"));
        $("#provinceId").val($("#province").find(":selected").attr("data-province-id"));
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
        $('.result-focus').change(function(e) {
            $('.change-reason').show(500);
            $('#reasonForChanging').addClass('isRequired');
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
        <?php if (isset($covid19Info['mother_treatment']) && in_array('Other', $covid19Info['mother_treatment'])) { ?>
            $('#motherTreatmentOther').prop('disabled', false);
        <?php } ?>

        <?php if (isset($covid19Info['mother_vl_result']) && !empty($covid19Info['mother_vl_result'])) { ?>
            updateMotherViralLoad();
        <?php } ?>

        $("#motherViralLoadCopiesPerMl").on("change keyup paste", function() {
            var motherVl = $("#motherViralLoadCopiesPerMl").val();
            //var motherVlText = $("#motherViralLoadText").val();
            if (motherVl != '') {
                $("#motherViralLoadText").val('');
            }
        });
        <?php if(isset($covid19Info['result']) && $covid19Info['result'] != ""){ ?>
            $("#updateCovid19ConfirmatoryRequestForm :input").prop("disabled", true);
            $('.submit-btn').remove();
        <?php }else{?>
        $('.disabledForm input, .disabledForm select , .disabledForm textarea, .test-name-table-input').attr('disabled', true);
        $('.test-name-table-input').prop('disabled',true);
        insRow();
        <?php }?>

        $('#isResultAuthorized').change(function(e){
            checkIsResultAuthorized();
        });
        <?php if(isset($arr['covid19_positive_confirmatory_tests_required_by_central_lab']) && $arr['covid19_positive_confirmatory_tests_required_by_central_lab'] == 'yes'){ ?>
        $('.test-result,#result').change(function(e){
            checkPostive();
        });
        checkPostive();
        <?php }?>
        
        checkIsResultAuthorized();

        <?php $index = 0; if(isset($covid19Symptoms) && count($covid19Symptoms) > 0){ 
            foreach ($covid19Symptoms as $symptomId => $symptomName) { ?>
            checkSubSymptoms($('#symptomDetected<?php echo $symptomId;?>').val(),<?php echo $symptomId;?>,<?php echo $index;?>);
        <?php $index++; } }?>
        
        <?php $index = 0; if(isset($covid19ReasonsForTesting) && count($covid19ReasonsForTesting) > 0){ 
            foreach ($covid19ReasonsForTesting as $reasonId => $responseName) { ?>
            checkSubResponse($('#symptomDetected<?php echo $reasonId;?>').val(),<?php echo $reasonId;?>,<?php echo $index;?>);
        <?php $index++; } }?>
    });

    function checkSubSymptoms(val, parent, row){
        if(val == 'yes'){
            $.post("getSymptomsByParentId.php", {
                symptomParent: parent,
                covid19Id : <?php echo $covid19Info['covid19_id'];?>
            },
            function(data) {
                if (data != "") {
                    // $(".row"+row).append(data);
                    $("#symptomsTable").find("tr:eq("+row+")").after(data);
                }
            });
        } else{
            $('.symptomRow'+parent).remove();
        }
    }
    
    function checkSubResponse(val, parent, row){
        if(val == 'yes'){
            $.post("getResponseByParentId.php", {
                responseParent: parent,
                covid19Id : <?php echo $covid19Info['covid19_id'];?>
            },
            function(data) {
                if (data != "") {
                    // $(".row"+row).append(data);
                    $("#responseTable").find("tr:eq("+row+")").after(data);
                }
            });
        } else{
            $('.responseRow'+parent).remove();
        }
    }

    function insRow() {
        rl = document.getElementById("testKitNameTable").rows.length;
        var a = document.getElementById("testKitNameTable").insertRow(rl);
        a.setAttribute("style", "display:none");
        var b = a.insertCell(0);
        var c = a.insertCell(1);
        var d = a.insertCell(2);
        var e = a.insertCell(3);
        var f = a.insertCell(4);
        f.setAttribute("align", "center");
        b.setAttribute("align", "center");
        f.setAttribute("style", "vertical-align:middle");

        b.innerHTML = tableRowId;
        c.innerHTML = '<input type="text" name="testName[]" id="testName' + tableRowId + '" class="form-control test-name-table-input" placeholder="Test name" title="Please enter the test name for row ' + tableRowId + '"/>';
        d.innerHTML = '<input type="text" name="testDate[]" id="testDate' + tableRowId + '" class="form-control test-name-table-input dateTime" placeholder="Tested on"  title="Please enter the tested on for row ' + tableRowId + '"/>';
        e.innerHTML = '<select class="form-control test-result test-name-table-input" name="testResult[]" id="testResult' + tableRowId + '" title="Please select the result for row ' + tableRowId + '"><option value=""> -- Select -- </option><?php foreach ($covid19Results as $c19ResultKey => $c19ResultValue) { ?> <option value="<?php echo $c19ResultKey; ?>"> <?php echo $c19ResultValue; ?> </option> <?php } ?> </select>';
        f.innerHTML = '<a class="btn btn-xs btn-primary test-name-table" href="javascript:void(0);" onclick="insRow();"><i class="fa fa-plus"></i></a>&nbsp;<a class="btn btn-xs btn-default test-name-table" href="javascript:void(0);" onclick="removeAttributeRow(this.parentNode.parentNode);"><i class="fa fa-minus"></i></a>';
        $(a).fadeIn(800);
        $('.dateTime').datetimepicker({
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
        }).click(function() {
            $('.ui-datepicker-calendar').show();
        });
        tableRowId++;

        <?php if(isset($arr['covid19_positive_confirmatory_tests_required_by_central_lab']) && $arr['covid19_positive_confirmatory_tests_required_by_central_lab'] == 'yes'){ ?>
        $('.test-result,#result').change(function(e){
            checkPostive();
        });
        <?php }?>
    }

    function removeAttributeRow(el) {
        $(el).fadeOut("slow", function() {
            el.parentNode.removeChild(el);
            rl = document.getElementById("testKitNameTable").rows.length;
            if (rl == 0) {
                insRow();
            }
        });
    }

    function deleteRow(id) {
        deletedRow.push(id);
        $('#deletedRow').val(deletedRow);
    }

    function checkPostive(){
        var itemLength = document.getElementsByName("testResult[]");
        for (i = 0; i < itemLength.length; i++) {
            
            if(itemLength[i].value == 'positive'){
                $('#result,.disabled-field').val('');
                $('#result,.disabled-field').prop('disabled',true);
                $('#result,.disabled-field').addClass('disabled');
                $('#result,.disabled-field').removeClass('isRequired');
                return false;
            }else{
                $('#result,.disabled-field').prop('disabled',false);
                $('#result,.disabled-field').removeClass('disabled');
                $('#result,.disabled-field').addClass('isRequired');
            }
            if(itemLength[i].value != ''){
                $('#labId').addClass('isRequired');
            }
        }
    }
    
    function checkIsResultAuthorized(){
        if($('#isResultAuthorized').val() == 'yes'){
            $('#authorizedBy,#authorizedOn').prop('disabled',false);
            $('#authorizedBy,#authorizedOn').removeClass('disabled');
            $('#authorizedBy,#authorizedOn').addClass('isRequired');
        }else{
            $('#authorizedBy,#authorizedOn').val('');
            $('#authorizedBy,#authorizedOn').prop('disabled',true);
            $('#authorizedBy,#authorizedOn').addClass('disabled');
            $('#authorizedBy,#authorizedOn').removeClass('isRequired');
        }
    }
</script>