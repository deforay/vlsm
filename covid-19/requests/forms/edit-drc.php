<?php
// imported in covid-19-add-request.php based on country in global config

ob_start();

//Funding source list
$fundingSourceQry = "SELECT * FROM r_funding_sources WHERE funding_source_status='active' ORDER BY funding_source_name ASC";
$fundingSourceList = $db->query($fundingSourceQry);

//Implementing partner list
$implementingPartnerQry = "SELECT * FROM r_implementation_partners WHERE i_partner_status='active' ORDER BY i_partner_name ASC";
$implementingPartnerList = $db->query($implementingPartnerQry);


// $configQuery = "SELECT * from global_config";
// $configResult = $db->query($configQuery);
// $arr = array();
// $prefix = $arr['sample_code_prefix'];

// Getting the list of Provinces, Districts and Facilities

$covid19Obj = new Model_Covid19($db);


$covid19Results = $covid19Obj->getCovid19Results();
$specimenTypeResult = $covid19Obj->getCovid19SampleTypes();
$covid19ReasonsForTesting = $covid19Obj->getCovid19ReasonsForTesting();
$covid19Symptoms = $covid19Obj->getCovid19SymptomsDRC();
$covid19Comorbidities = $covid19Obj->getCovid19Comorbidities();


$rKey = '';
$sKey = '';
$sFormat = '';
$pdQuery = "SELECT * from province_details";
if ($sarr['user_type'] == 'remoteuser') {
    $sampleCodeKey = 'remote_sample_code_key';
    $sampleCode = 'remote_sample_code';
    //check user exist in user_facility_map table
    $chkUserFcMapQry = "SELECT user_id FROM vl_user_facility_map WHERE user_id='" . $_SESSION['userId'] . "'";
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
    $province .= "<option data-code='" . $provinceName['province_code'] . "' data-province-id='" . $provinceName['province_id'] . "' data-name='" . $provinceName['province_name'] . "' value='" . $provinceName['province_name'] . "##" . $provinceName['province_code'] . "'>" . ucwords($provinceName['province_name']) . "</option>";
}
//$facility = "";
$facility = "<option value=''> -- Select -- </option>";
foreach ($fResult as $fDetails) {
    $facility .= "<option value='" . $fDetails['facility_id'] . "'>" . ucwords(addslashes($fDetails['facility_name'])) . "</option>";
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
        <pre><?php print_r($covid19Info);?></pre>
        <div class="box box-default">
            <div class="box-header with-border">

                <div class="pull-right" style="font-size:15px;"><span class="mandatory">*</span> indique un champ obligatoire &nbsp;</div>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
                <!-- form start -->
                <form class="form-horizontal" method="post" name="addCovid19RequestForm" id="addCovid19RequestForm" autocomplete="off" action="covid-19-add-request-helper.php">
                    <div class="box-body">
                        <div class="box box-default">
                            <div class="box-body">
                                <div class="box-header with-border sectionHeader">
                                    <h3 class="box-title">INFORMATIONS SUR LE SITE</h3>
                                </div>
                                <div class="box-header with-border">
                                    <h3 class="box-title" style="font-size:1em;">À remplir par le clinicien / infirmier demandeur</h3>
                                </div>
                                <table class="table" style="width:100%">
                                    <tr>
                                        <?php if ($sarr['user_type'] == 'remoteuser') { ?>
                                            <td><label for="sampleCode">N°EPID </label></td>
                                            <td>
                                                <span id="sampleCodeInText" style="width:100%;border-bottom:1px solid #333;"></span>
                                                <input type="hidden" value="" id="sampleCode" name="sampleCode" />
                                            </td>
                                        <?php } else { ?>
                                            <td><label for="sampleCode">N°EPID </label><span class="mandatory">*</span></td>
                                            <td>
                                                <input type="text" class="form-control isRequired" id="sampleCode" name="sampleCode" readonly="readonly" placeholder="N°EPID" title="N°EPID" style="width:100%;" onchange="checkSampleNameValidation('form_covid19','<?php echo $sampleCode; ?>',this.id,null,'The sample id that you entered already exists. Please try another sample id',null)" />
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
                                            <select class="form-control isRequired" name="province" id="province" title="Province" onchange="getfacilityDetails(this);" style="width:100%;">
                                                <?php echo $province; ?>
                                            </select>
                                        </td>
                                        <td><label for="district">Zone de Santé </label><span class="mandatory">*</span></td>
                                        <td>
                                            <select class="form-control isRequired" name="district" id="district" title="Zone de Santé" style="width:100%;" onchange="getfacilityDistrictwise(this);">
                                                <option value=""> -- Select -- </option>
                                            </select>
                                        </td>
                                        <td><label for="facilityId">Nom de l'installation </label><span class="mandatory">*</span></td>
                                        <td>
                                            <select class="form-control isRequired " name="facilityId" id="facilityId" title="Nom de Structure" style="width:100%;" onchange="getfacilityProvinceDetails(this);">
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
                                                        <option value="<?php echo $labName['facility_id']; ?>"><?php echo ucwords($labName['facility_name']); ?></option>
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
                                            <input type="text" class="form-control isRequired" id="firstName" name="firstName" placeholder="Prénom" title="Prénom" style="width:100%;" onchange="" />
                                        </td>
                                        <th style="width:15% !important"><label for="lastName">Nom de famille </label></th>
                                        <td style="width:35% !important">
                                            <input type="text" class="form-control " id="lastName" name="lastName" placeholder="Nom de famille" title="Nom de famille" style="width:100%;" onchange="" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <th style="width:15% !important"><label for="patientId">Code Patient <span class="mandatory">*</span> </label></th>
                                        <td style="width:35% !important">
                                            <input type="text" class="form-control isRequired" id="patientId" name="patientId" placeholder="Code Patient" title="Code Patient" style="width:100%;" onchange="" />
                                        </td>
                                        <th><label for="patientDob">Date de naissance <span class="mandatory">*</span> </label></th>
                                        <td>
                                            <input type="text" class="form-control isRequired" id="patientDob" name="patientDob" placeholder="Date de naissance" title="Date de naissance" style="width:100%;" onchange="calculateAgeInYears();" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Age (years)</th>
                                        <td><input type="number" max="150" maxlength="3" oninput="this.value=this.value.slice(0,$(this).attr('maxlength'))" class="form-control " id="patientAge" name="patientAge" placeholder="Age (years)" title="Age (years)" style="width:100%;" onchange="" /></td>
                                        <th><label for="patientGender">Sexe <span class="mandatory">*</span> </label></th>
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
                                        <th>Numéro de téléphone</th>
                                        <td><input type="text" class="form-control " id="patientPhoneNumber" name="patientPhoneNumber" placeholder="Numéro de téléphone" title="Numéro de téléphone" style="width:100%;" onchange="" /></td>

                                        <th>Adresse du patient</th>
                                        <td><textarea class="form-control " id="patientAddress" name="patientAddress" placeholder="Adresse du patient" title="Adresse du patient" style="width:100%;" onchange=""></textarea></td>
                                    </tr>
                                                                        
                                    <tr>
                                        <th>Province du patient</th>
                                        <td><input type="text" class="form-control " id="patientProvince" name="patientProvince" placeholder="Province du patient" title="Province du patient" style="width:100%;" /></td>

                                        <th>District des patients</th>
                                        <td><input class="form-control" id="patientDistrict" name="patientDistrict" placeholder="District des patients" title="District des patients" style="width:100%;"></td>
                                    </tr>

                                    <tr>
                                        <th>Pays de résidence</th>
                                        <td><input type="text" class="form-control" id="patientNationality" name="patientNationality" placeholder="Pays de résidence" title="Pays de résidence" style="width:100%;" /></td>

                                        <th></th>
                                        <td></td>
                                    </tr> 
                                    <tr>
                                        <td colspan="4"><h4>Les détails du vol</h4></td>
                                    </tr>
                                    <tr>
                                        <th>Compagnie aérienne</th>
                                        <td><input type="text" class="form-control " id="airline" name="airline" placeholder="Compagnie aérienne" title="Compagnie aérienne" style="width:100%;" /></td>

                                        <th>Numéro de siège</th>
                                        <td><input type="text" class="form-control " id="seatNo" name="seatNo" placeholder="Numéro de siège" title="Numéro de siège" style="width:100%;" /></td>
                                    </tr>                                    
                                    <tr>
                                        <th>Date et heure d'arrivée</th>
                                        <td><input type="text" class="form-control dateTime" id="arrivalDateTime" name="arrivalDateTime" placeholder="Date et heure d'arrivée" title="Date et heure d'arrivée" style="width:100%;" /></td>

                                        <th>Aeroport DE DEPART</th>
                                        <td><input type="text" class="form-control " id="airportOfDeparture" name="airportOfDeparture" placeholder="Aeroport DE DEPART" title="Aeroport DE DEPART" style="width:100%;" /></td>
                                    </tr>
                                    <tr>
                                        <th>Transit</th>
                                        <td><input type="text" class="form-control" id="transit" name="transit" placeholder="Transit" title="Transit" style="width:100%;" /></td>
                                        <th>Raison de la visite (le cas échéant)</th>
                                        <td><input type="text" class="form-control" id="reasonOfVisit" name="reasonOfVisit" placeholder="Raison de la visite (le cas échéant)" title="Raison de la visite (le cas échéant)" style="width:100%;" /></td>

                                    </tr> 
                                </table>

                                <div class="box-header with-border sectionHeader">
                                    <h3 class="box-title">
                                        Définition de cas
                                    </h3>
                                </div>
                                <table class="table">
                                    <tr>
                                        <th style="width:10% !important;"><label for="suspectedCase">Cas suspect </label></th>
                                        <td style="width:50% !important;">
                                            <select class="form-control" id="suspectedCase" name="suspectedCase" title="Cas suspect de covdid 19">
                                                <option value="">--Select--</option>
                                                <option value="Fièvre d'accès brutal (Inferieur ou égale à 38°C, vérifié à la salle d'urgence, la consultation externe, ou l'hôpital) ET (cochez une ou deux des cases suivantes)">Fièvre d'accès brutal (Inferieur ou égale à 38°C, vérifié à la salle d'urgence, la consultation externe, ou l'hôpital) ET (cochez une ou deux des cases suivantes)</option>
                                                <option value="Toux">Toux</option>
                                                <option value="Rhume">Rhume</option>
                                                <option value="Mal de gorge">Mal de gorge</option>
                                                <option value="Difficulté respiratoire">Difficulté respiratoire</option>
                                                <option value="Notion de séjour ou voyage dans les zones a épidémie a COVID-19 dans les 14 jours précédant les symptômes ci-dessous">Notion de séjour ou voyage dans les zones a épidémie a COVID-19 dans les 14 jours précédant les symptômes ci-dessous</option>
                                                <option value="IRA d'intensité variable (simple a sévère) ayant été en contact étroite avec cas probable ou un cas confirmé de la maladie a COVID-19">IRA d'intensité variable (simple a sévère) ayant été en contact étroite avec cas probable ou un cas confirmé de la maladie a COVID-19</option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th style="width:10% !important;"><label for="probableCase">Cas probable </label></th>
                                        <td style="width:50% !important;">
                                            <select class="form-control" id="probableCase" name="probableCase" title="Cas probable de covid 19">
                                                <option value="">--Select--</option>
                                                <option value="Tout cas suspects dont le résultat de laboratoire pour le diagnostic de COVID-19 n'est pas concluant (indéterminé)">Tout cas suspects dont le résultat de laboratoire pour le diagnostic de COVID-19 n'est pas concluant (indéterminé)</option>
                                                <option value="Tout décès dans un tableau d'IRA pour lequel il n'a pas été possible d'obtenir des échantillons biologiques pour confirmation au">Tout décès dans un tableau d'IRA pour lequel il n'a pas été possible d'obtenir des échantillons biologiques pour confirmation au</option>
                                                <option value="Laboratoire mais dont les investigations ont révélé un lien épidémiologique avec un cas confirmé ou probable ">Laboratoire mais dont les investigations ont révélé un lien épidémiologique avec un cas confirmé ou probable </option>
                                                <option value="Notion de séjour ou voyage dans les 14 jours précédant le décès dans les zones a épidémie de la maladie a COVID-19">Notion de séjour ou voyage dans les 14 jours précédant le décès dans les zones a épidémie de la maladie a COVID-19</option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th style="width:10% !important;"><label for="confirmeCase">Cas confirme </label></th>
                                        <td style="width:50% !important;">
                                            <select class="form-control" id="confirmeCase" name="confirmeCase" title="Cas confirme de covid 19">
                                                <option value="">--Select--</option>
                                                <option value="Toute personne avec une confirmation en laboratoire de l'infection au COVID-19, quelles que soient les signes et symptômes cliniques">Toute personne avec une confirmation en laboratoire de l'infection au COVID-19, quelles que soient les signes et symptômes cliniques</option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th style="width:10% !important;"><label for="contactCase">Non cas contact </label></th>
                                        <td style="width:50% !important;">
                                            <select class="form-control" id="contactCase" name="contactCase" title="Non cas contact">
                                                <option value="">--Select--</option>
                                                <option value="Tout cas suspects avec deux résultats de laboratoire négatifs au COVID-19 a au moins 48 heures d'intervalle">Tout cas suspects avec deux résultats de laboratoire négatifs au COVID-19 a au moins 48 heures d'intervalle</option>
                                            </select>
                                        </td>
                                    </tr>
                                </table>

                                <div class="box-header with-border sectionHeader">
                                    <h3 class="box-title">
                                        Signes vitaux du patient 
                                    </h3>
                                </div>
                                <table class="table">
                                    <tr>
                                        <th style="width: 15% !important;"><label for="respiratoryRateSelect">Fréquence respiratoire </label></th>
                                        <td style="width:35% !important;">
                                            <select class="form-control" id="respiratoryRateSelect" name="respiratoryRateSelect" title="Fréquence respiratoire">
                                                <option value="">--Select--</option>
                                                <option value="yes">Oui</option>
                                                <option value="unknown">Inconnu</option>
                                            </select>
                                            <div style=" margin-top: 15px !important;display: none; " class="respiratory-rate">
                                                <input type="text" class="form-control" name="respiratoryRate" id="respiratoryRate" style=" border: none;border-bottom: 1px solid;width: 20%;height: 18px; "> /  Minute (Comptez pendant une minute entière)
                                            </div>
                                        </td>
                                        <th style="width: 15% !important;"><label for="oxygenSaturationSelect">Saturation en oxygène </label></th>
                                        <td style="width:35% !important;">
                                            <select class="form-control" id="oxygenSaturationSelect" name="oxygenSaturationSelect" title="Saturation en oxygène">
                                                <option value="">--Select--</option>
                                                <option value="yes">Oui</option>
                                                <option value="unknown">Inconnu</option>
                                            </select>
                                            <div style=" margin-top: 15px !important;display: none; " class="oxygen-saturation">
                                                <input type="text" class="form-control" name="oxygenSaturation" id="oxygenSaturation" style=" border: none;border-bottom: 1px solid;width: 20%;height: 18px; "> / %
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th style="width: 15%;"><label for="specimenType"> Type(s) d' échantillon(s) dans le tube (cochez au moins une des cases suivants) <span class="mandatory">*</span></label></th>
                                        <td style="width: 35%;">
                                            <select class="form-control isRequired" id="specimenType" name="specimenType" title="Type(s) d' échantillon(s) dans le tube">
                                                <option value="">--Select--</option>
                                                <option value="oropharyngeal">Oropharyngée</option>
                                                <option value="nasal-both">Nasale / Les Deux</option>
                                                <option value="sputum">Expectorations</option>
                                                <option value="alveolar-broncho-wash">Lavage broncho alvéolaire</option>
                                                <option value="tracheal-aspiration">Aspiration trachéale</option>
                                                <option value="serum">Sérum</option>
                                            </select>
                                        </td>
                                        <th style="width:15% !important"><label for="specimenType">Date de prélèvement de l'échantillon <span class="mandatory">*</span></label></th>
                                        <td style="width:35% !important;">
                                            <input class="form-control isRequired" type="text" name="sampleCollectionDate" id="sampleCollectionDate" placeholder="Date de prélèvement de l'échantillon" title="Date de prélèvement de l'échantillon" onchange="sampleCodeGeneration();" />
                                        </td>
                                    </tr>
                                </table>
                                
                                <div class="box-header with-border sectionHeader">
                                    <h3 class="box-title">
                                        Histoire Clinique du patient
                                    </h3>
                                </div>
                                <table class="table">
                                    <tr>
                                        <th style="width: 15% !important;"><label for="sickDays">Depuis combien de jours êtes-vous malade? </label></th>
                                        <td style="width:35% !important;">
                                                <input type="text" placeholder="Depuis combien de jours êtes-vous malade" class="form-control" title="Depuis combien de jours êtes-vous malade?" name="sickDays" id="sickDays">
                                        </td>
                                        <th style="width: 15% !important;"><label for="onsetIllnessDate">Date de début de la maladie </label></th>
                                        <td style="width:35% !important;">
                                            <div style=" display: inline-flex; margin-top: 15px !important; ">
                                                <input type="text" class="form-control date" placeholder="e.g 09-Jan-1992" name="onsetIllnessDate" id="onsetIllnessDate">
                                            </div>
                                        </td>
                                    </tr>
                                    
                                    <tr>
                                        <th style="width: 15% !important;"><label for="medicalBackground">Antécédents Médicaux </label></th>
                                        <td style="width:35% !important;">
                                            <select class="form-control" id="medicalBackground" name="medicalBackground" title="Antécédents Médicaux">
                                                <option value="">--Select--</option>
                                                <option value="yes">Oui</option>
                                                <option value="no">Non</option>
                                            </select>
                                        </td>
                                        <th colspan="2" class="medical-background-info" style="display: none;">Si Oui, Cochez la ou les cases cidessous</th>
                                    </tr>
                                    <tr class="medical-background-yes" style="display: none;">
                                        <th style="width: 15% !important;"><label for="heartDiseaseMbg">Maladie cardiaque (Cardiopathie) </label></th>
                                        <td style="width:35% !important;">
                                            <select class="form-control" id="heartDiseaseMbg" name="medicalBg['heart_disease_mbg_drc']" title="Maladie cardiaque">
                                                <option value="">--Select--</option>
                                                <option value="yes">Oui</option>
                                                <option value="no">Non</option>
                                                <option value="unknown">Inconnu</option>
                                            </select>
                                        </td>
                                        <th style="width: 15% !important;"><label for="dyspneaChronicRespiratoryMbg">Difficulté /Dyspnée Respiratoire chronique </label></th>
                                        <td style="width:35% !important;">
                                            <select class="form-control" id="dyspneaChronicRespiratoryMbg" name="medicalBg['dyspnea_chronic_respiratory_mbg_drc']" title="Difficulté /Dyspnée Respiratoire chronique">
                                                <option value="">--Select--</option>
                                                <option value="yes">Oui</option>
                                                <option value="no">Non</option>
                                                <option value="unknown">Inconnu</option>
                                            </select>
                                        </td>
                                    </tr>
                                    
                                    <tr class="medical-background-yes" style="display: none;">
                                        <th style="width: 15% !important;"><label for="recurrentChestPainMbg">Douleur récurrentes du thorax </label></th>
                                        <td style="width:35% !important;">
                                            <select class="form-control" id="recurrentChestPainMbg" name="medicalBg['recurrent_chest_pain_mbg_drc']" title="Douleur récurrentes du thorax">
                                                <option value="">--Select--</option>
                                                <option value="yes">Oui</option>
                                                <option value="no">Non</option>
                                                <option value="unknown">Inconnu</option>
                                            </select>
                                        </td>
                                        <th style="width: 15% !important;"><label for="asthmaMbg">Asthme Asthma </label></th>
                                        <td style="width:35% !important;">
                                            <select class="form-control" id="asthmaMbg" name="medicalBg['asthma_mbg_drc']" title="Asthme Asthma">
                                                <option value="">--Select--</option>
                                                <option value="yes">Oui</option>
                                                <option value="no">Non</option>
                                                <option value="unknown">Inconnu</option>
                                            </select>
                                        </td>
                                    </tr>
                                    
                                    <tr class="medical-background-yes" style="display: none;">
                                        <th style="width: 15% !important;"><label for="cancerMbg">Cancer</label></th>
                                        <td style="width:35% !important;">
                                            <select class="form-control" id="cancerMbg" name="medicalBg['cancer_mbg_drc']" title="Cancer">
                                                <option value="">--Select--</option>
                                                <option value="yes">Oui</option>
                                                <option value="no">Non</option>
                                                <option value="unknown">Inconnu</option>
                                            </select>
                                        </td>
                                        <th style="width: 15% !important;"><label for="chronicCoughMbg">Toux Chronique (inférieur ou égal à 3 mois sur 2 années consécutives) </label></th>
                                        <td style="width:35% !important;">
                                            <select class="form-control" id="chronicCoughMbg" name="medicalBg['chronic_cough_mbg_drc']" title="Toux Chronique">
                                                <option value="">--Select--</option>
                                                <option value="yes">Oui</option>
                                                <option value="no">Non</option>
                                                <option value="unknown">Inconnu</option>
                                            </select>
                                        </td>
                                    </tr>
                                    
                                    <tr class="medical-background-yes" style="display: none;">
                                        <th style="width: 15% !important;"><label for="activeTuberculosisMbg">Tuberculose active</label></th>
                                        <td style="width:35% !important;">
                                            <select class="form-control" id="activeTuberculosisMbg" name="medicalBg['active_tuberculosis_mbg_drc']" title="Tuberculose active">
                                                <option value="">--Select--</option>
                                                <option value="yes">Oui</option>
                                                <option value="no">Non</option>
                                                <option value="unknown">Inconnu</option>
                                            </select>
                                        </td>
                                        <th style="width: 15% !important;"><label for="ancientTuberculosisMbg">Tuberculose ancienne </label></th>
                                        <td style="width:35% !important;">
                                            <select class="form-control" id="ancientTuberculosisMbg" name="medicalBg['ancient_tuberculosis_mbg_drc']" title="Tuberculose ancienne">
                                                <option value="">--Select--</option>
                                                <option value="yes">Oui</option>
                                                <option value="no">Non</option>
                                                <option value="unknown">Inconnu</option>
                                            </select>
                                        </td>
                                    </tr>
                                    
                                    <tr class="medical-background-yes" style="display: none;">
                                        <th style="width: 15% !important;"><label for="hospitalizedMbg">Avez-vous été hospitalisé durant les 12 derniers mois? Have you been hospitalized in the past 12 months?</label></th>
                                        <td style="width:35% !important;">
                                            <select class="form-control" id="hospitalizedMbg" name="medicalBg['hospitalized_mbg_drc']" title="Avez-vous été hospitalisé durant les 12 derniers mois? Have you been hospitalized in the past 12 months?">
                                                <option value="">--Select--</option>
                                                <option value="yes">Oui</option>
                                                <option value="no">Non</option>
                                                <option value="unknown">Inconnu</option>
                                            </select>
                                        </td>
                                        <th style="width: 15% !important;"><label for="liveChildrensMbg">Habitez-vous avec les enfants ? </label></th>
                                        <td style="width:35% !important;">
                                            <select class="form-control" id="liveChildrensMbg" name="medicalBg['live_childrens_mbg_drc']" title="Habitez-vous avec les enfants ?">
                                                <option value="">--Select--</option>
                                                <option value="yes">Oui</option>
                                                <option value="no">Non</option>
                                                <option value="unknown">Inconnu</option>
                                            </select>
                                        </td>
                                    </tr>
                                    
                                    <tr class="medical-background-yes" style="display: none;">
                                        <th style="width: 15% !important;"><label for="takeCraeOfChildrensMbg">Prenez-vous soins des enfants?</label></th>
                                        <td style="width:35% !important;">
                                            <select class="form-control" id="takeCraeOfChildrensMbg" name="medicalBg['take_crae_childrens_mbg_drc']" title="Prenez-vous soins des enfants?">
                                                <option value="">--Select--</option>
                                                <option value="yes">Oui</option>
                                                <option value="no">Non</option>
                                                <option value="unknown">Inconnu</option>
                                            </select>
                                        </td>
                                        <th style="width: 15% !important;"><label for="past3WeeksMbg">Avez-vous eu des contacts étroits avec toute personne une maladie similaire a la vôtre durant ces 3 derniers semaines? </label></th>
                                        <td style="width:35% !important;">
                                            <select class="form-control" id="past3WeeksMbg" name="medicalBg['past3_weeks_mbg_drc']" title="Avez-vous eu des contacts étroits avec toute personne une maladie similaire a la vôtre durant ces 3 derniers semaines?">
                                                <option value="">--Select--</option>
                                                <option value="yes">Oui</option>
                                                <option value="no">Non</option>
                                                <option value="unknown">Inconnu</option>
                                            </select>
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
                                                            <select name="symptomDetected[]" class="form-control isRequired" title="Veuillez choisir la valeur pour <?php echo $symptomName; ?>" style="width:100%" onchange="checkSubSymptoms(this,<?php echo $symptomId;?>,<?php echo $index;?>);">
                                                                <option value="">-- Select --</option>
                                                                <option value='yes'> Oui </option>
                                                                <option value='no'> Non </option>
                                                                <option value='unknown'> Inconnu </option>
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
                                                <option value="yes">Oui</option>
                                                <option value="no">Non</option>
                                                <option value="unknown">Inconnu</option>
                                            </select>
                                        </td>                                        
                                        <th style="width: 15% !important;"><label for="countryName">Si oui, dans quels pays?</label></th>
                                        <td style="width:35% !important;">
                                            <input type="text" class="form-control" id="countryName" name="countryName" placeholder="Si oui, dans quels pays ?" title="Si oui, dans quels pays?"/>
                                        </td>
                                    </tr>

                                    <tr>
                                        <th style="width: 15% !important;"><label for="returnDate">Date de retour</label></th>
                                        <td style="width:35% !important;">
                                            <input type="text" class="form-control date" id="returnDate" name="returnDate" placeholder="e.g 09-Jan-1992" title="Date de retour"/>
                                        </td>
                                        <th style="width: 15% !important;"><label for="conacted14Days">Avez-vous été en contact avec un cas confirmé de COVID-19 au cours  de ces 14 derniers jours?</label></th>
                                        <td style="width:35% !important;">
                                            <select class="form-control" id="conacted14Days" name="conacted14Days" title="Avez-vous été en contact avec un cas confirmé de COVID-19 au cours  de ces 14 derniers jours?">
                                                <option value="">--Select--</option>
                                                <option value="yes">Oui</option>
                                                <option value="no">Non</option>
                                                <option value="unknown">Inconnu</option>
                                            </select>
                                        </td>                                        
                                    </tr>
                                    <tr>
                                        <td colspan="2"><h5 class="box-title">Laissez cette question ci-dessous pour les adultes</h5></td>
                                    </tr>
                                    <tr>
                                        <th style="width: 15% !important;"><label for="smoke">Fumez-vous?</label></th>
                                        <td style="width:35% !important;">
                                            <select class="form-control" id="smoke" name="smoke" title="Fumez-vous?">
                                                <option value="">--Select--</option>
                                                <option value="yes">Oui</option>
                                                <option value="no">Non</option>
                                                <option value="unknown">Inconnu</option>
                                            </select>
                                        </td>
                                        <th style="width: 15% !important;"><label for="profession">Profession</label></th>
                                        <td style="width:35% !important;">
                                            <select class="form-control" id="profession" name="profession" title="Profession">
                                                <option value="">--Select--</option>
                                                <option value="faller">Abatteur</option>
                                                <option value="laboratory-staff">Personnel de laboratoire</option>
                                                <option value="personal health">Personnel de santé</option>
                                                <option value="traditional-healer">Guérisseur traditionnel</option>
                                                <option value="veterinary">Vétérinaire</option>
                                                <option value="butcher">Boucher</option>
                                                <option value="other">Autre (précisez)</option>
                                            </select>
                                        </td>
                                    </tr>
                                </table>

                                <div class="box-header with-border sectionHeader">
                                    <h3 class="box-title">Résultats de laboratoire</h3>
                                </div>
                                <table class="table">
                                    <tr>
                                        <th style="width:15% !important"><label for="sampleCondtion">Condition de l'échantillon</label></th>
                                        <td style="width:35% !important;">
                                            <select class="form-control" id="smoke" name="smoke" title="Condition de l'échantillon">
                                                <option value="">--Select--</option>
                                                <option value="adequate">Adéquat</option>
                                                <option value="not-adequate">Non Adéquat</option>
                                                <option value="other">Autres</option>
                                            </select>
                                        </td>
                                        
                                        <th style="width:15% !important"><label for="confirmationLab">Méthode de confirmation en labo</label></th>
                                        <td style="width:35% !important;">
                                            <select class="form-control" id="confirmationLab" name="confirmationLab" title="Méthode de confirmation en labo">
                                                <option value="">--Select--</option>
                                                <option value="PCR/RT-PCR">PCR/RT-PCR</option>
                                                <option value="RdRp-SARS CoV-2">RdRp-SARS CoV-2</option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th style="width: 15% !important;"><label for="resultPcr">Date de Result PCR</label></th>
                                        <td style="width:35% !important;">
                                            <input type="text" class="form-control date" id="resultPcr" name="resultPcr" placeholder="e.g 09-Jan-1992" title="Date de Result PCR"/>
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
                                            <input class="form-control date isRequired" type="text" name="dateOfSymptomOnset" id="dateOfSymptomOnset" placeholder="Date d'apparition des symptômes" title="Date d'apparition des symptômes"/>
                                        </td>
                                        <th style="width:15% !important">Date de la consultation initiale</th>
                                        <td style="width:35% !important;">
                                            <input class="form-control date" type="text" name="dateOfInitialConsultation" id="dateOfInitialConsultation" placeholder="Date de la consultation initiale" title="Date de la consultation initiale" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <th style="width:15% !important">Fièvre / température (&deg;C) <span class="mandatory">*</span> </th>
                                        <td style="width:35% !important;">
                                            <input class="form-control isRequired" type="number" name="feverTemp" id="feverTemp" placeholder="Fièvre / température (en &deg;Celcius)" title="Fièvre / température (en &deg;Celcius)"/>
                                        </td>
                                        <th style="width:15% !important"></th>
                                        <td style="width:35% !important;"></td>
                                    </tr>
                                </table>

                                <div class="box-header with-border sectionHeader">
                                    <h3 class="box-title">FACTEURS DE RISQUE ÉPIDÉMIOLOGIQUE ET EXPOSITIONS</h3>
                                </div>
                                <table class="table">
                                    <tr>
                                        <th style="width:15% !important">Contacts étroits du patient <span class="mandatory">*</span></th>
                                        <td colspan="3">
                                            <textarea name="closeContacts" class="form-control" style="width:100%;min-height:100px;" placeholder="Contacts étroits du patient" title="Contacts étroits du patient"></textarea>
                                            <span class="text-danger">
                                                Add close contact names and phone numbers (household, family members, friends you have been in contact with in the last 14 days)
                                            </span>
                                        </td>

                                    </tr>
                                    <tr>
                                        <th>Occupation du patient</th>
                                        <td>
                                            <input class="form-control" type="text" name="patientOccupation" id="patientOccupation" placeholder="Occupation du patient" title="Occupation du patient" />
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
                                                <input type="text" class="form-control" id="sampleReceivedDate" name="sampleReceivedDate" placeholder="e.g 09-Jan-1992 05:30" title="Veuillez saisir la date de réception de l'échantillon" <?php echo (isset($labFieldDisabled) && trim($labFieldDisabled) != '') ? $labFieldDisabled : ''; ?> onchange="" style="width:100%;" />
                                            </td>
                                            <td class="lab-show"><label for="labId">Nom du laboratoire </label> </td>
                                            <td class="lab-show">
                                                <select name="labId" id="labId" class="form-control" title="Nom du laboratoire" style="width:100%;">
                                                    <option value=""> -- Select -- </option>
                                                    <?php foreach ($lResult as $labName) { ?>
                                                        <option value="<?php echo $labName['facility_id']; ?>"><?php echo ucwords($labName['facility_name']); ?></option>
                                                    <?php } ?>
                                                </select>
                                            </td>
                                        <tr>
                                            <th>L'échantillon est-il rejeté?</th>
                                            <td>
                                                <select class="form-control" name="isSampleRejected" id="isSampleRejected" title="L'échantillon est-il rejeté?">
                                                    <option value=''> -- Select -- </option>
                                                    <option value="yes"> Yes </option>
                                                    <option value="no"> No </option>
                                                </select>
                                            </td>

                                            <th class="show-rejection" style="display:none;">Reason for Rejection</th>
                                            <td class="show-rejection" style="display:none;">
                                                <select class="form-control" name="sampleRejectionReason" id="sampleRejectionReason" title="Raison du rejet">
                                                    <option value=''> -- Select -- </option>
                                                    <?php echo $rejectionReason; ?>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr class="show-rejection" style="display:none;">
                                            <th>Rejection Date<span class="mandatory">*</span></th>
                                            <td><input class="form-control date rejection-date" type="text" name="rejectionDate" id="rejectionDate" placeholder="Date de rejet" title="Date de rejet"/></td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td colspan="4">
                                                <table class="table table-bordered table-striped">
                                                    <thead>
                                                        <tr>
                                                            <th class="text-center">Test non</th>
                                                            <th class="text-center">Name of the Testkit (or) Test Method used</th>
                                                            <th class="text-center">Date du test</th>
                                                            <th class="text-center">Résultat du test</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="testKitNameTable">
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
                                                    </tbody>
                                                    <tfoot>
                                                        <tr>
                                                            <th colspan="3" class="text-right">Résultat final</th>
                                                            <td>
                                                                <select class="form-control" name="result" id="result" title="Résultat final">
                                                                    <option value=''> -- Select -- </option>
                                                                    <?php foreach ($covid19Results as $c19ResultKey => $c19ResultValue) { ?>
                                                                        <option value="<?php echo $c19ResultKey; ?>"> <?php echo $c19ResultValue; ?> </option>
                                                                    <?php } ?>
                                                                </select>
                                                            </td>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </td>
                                        </tr>
                                        <tr>

                                            <th>Le résultat est-il autorisé?</th>
                                            <td>
                                                <select name="isResultAuthorized" id="isResultAuthorized" class="disabled-field form-control" title="Le résultat est-il autorisé?" style="width:100%">
                                                    <option value="">-- Select --</option>
                                                    <option value='yes'> Yes </option>
                                                    <option value='no'> No </option>
                                                </select>
                                            </td>
                                            <th>Autorisé par</th>
                                            <td><input type="text" name="authorizedBy" id="authorizedBy" class="disabled-field form-control" placeholder="Autorisé par" title="Autorisé par"/></td>

                                        </tr>
                                        <tr>

                                            <th>Autorisé le</td>
                                            <td><input type="text" name="authorizedOn" id="authorizedOn" class="disabled-field form-control date" placeholder="Autorisé le" title="Autorisé le" /></td>
                                            <th></th>
                                            <td></td>

                                        </tr>
                                        <tr>
                                            <!-- <td style="width:25%;"><label for="">Sample Test Date </label></td>
                                            <td style="width:25%;">
                                                <input type="text" class="form-control dateTime" id="sampleTestedDateTime" name="sampleTestedDateTime" placeholder="e.g 09-Jan-1992 05:30" title="Sample Tested Date and Time" <?php echo (isset($labFieldDisabled) && trim($labFieldDisabled)) ? $labFieldDisabled : ''; ?> onchange="" style="width:100%;" />
                                            </td> -->


                                            <td></td>
                                            <td></td>
                                        </tr>

                                    </table>
                                </div>
                            </div>
                        <?php } ?>

                    </div>
                    <!-- /.box-body -->
                    <div class="box-footer">
                        <?php if ($arr['covid19_sample_code'] == 'auto' || $arr['covid19_sample_code'] == 'YY' || $arr['covid19_sample_code'] == 'MMYY') { ?>
                            <input type="hidden" name="sampleCodeFormat" id="sampleCodeFormat" value="<?php echo $sFormat; ?>" />
                            <input type="hidden" name="sampleCodeKey" id="sampleCodeKey" value="<?php echo $sKey; ?>" />
                            <input type="hidden" name="saveNext" id="saveNext" />
                            <!-- <input type="hidden" name="pageURL" id="pageURL" value="<?php echo $_SERVER['PHP_SELF']; ?>" /> -->
                        <?php } ?>
                        <a class="btn btn-primary" href="javascript:void(0);" onclick="validateNow();return false;">Sauver</a>
                        <a class="btn btn-primary" href="javascript:void(0);" onclick="validateNow();$('#saveNext').val('next');return false;">Enregistrer et suivant</a>
                        <input type="hidden" name="formId" id="formId" value="<?php echo $arr['vl_form']; ?>" />
                        <input type="hidden" name="covid19SampleId" id="covid19SampleId" value="" />
                        <a href="/covid-19/requests/covid-19-requests.php" class="btn btn-default"> Annuler</a>
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
    tableRowId = 2;

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
                        //$("#clinicianName").val(details[2]);
                    }
                });
            //}
            sampleCodeGeneration();
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

    function sampleCodeGeneration() {
        var pName = $("#province").val();
        var sDate = $("#sampleCollectionDate").val();
        if (pName != '' && sDate != '') {
            $.post("/covid-19/requests/generateSampleCode.php", {
                    sDate: sDate,
                    pName: pName
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

    function checkSubSymptoms(obj, parent, row){
        if(obj.value == 'yes'){
            $.post("getSymptomsByParentId.php", {
                symptomParent: parent
            },
            function(data) {
                if (data != "") {
                    // $(".row"+row).append(data);
                    $("#symptomsTable").find("tr:eq("+row+")").after(data);
                }
            });
        } else{
            $("#symptomsTable").find("tr:eq("+row+")").after("");
        }
    }


    function validateNow() {
        flag = deforayValidator.init({
            formId: 'addCovid19RequestForm'
        });
        if (flag) {
            //$.blockUI();
            <?php
            if ($arr['covid19_sample_code'] == 'auto' || $arr['covid19_sample_code'] == 'YY' || $arr['covid19_sample_code'] == 'MMYY') {
            ?>
                insertSampleCode('addCovid19RequestForm', 'covid19SampleId', 'sampleCode', 'sampleCodeKey', 'sampleCodeFormat', 3, 'sampleCollectionDate');
            <?php
            } else {
            ?>
                document.getElementById('addCovid19RequestForm').submit();
            <?php
            } ?>
        }
    }


    $(document).ready(function() {

        $('#facilityId').select2({
            placeholder: "Nom de l'installation"
        });
        // $('#district').select2({
        //     placeholder: "District"
        // });
        // $('#province').select2({
        //     placeholder: "Province"
        // });
        $('#isResultAuthorized').change(function(e){
            checkIsResultAuthorized();
        });
        $('#medicalBackground').change(function(e){
            if(this.value == 'yes'){
                $('.medical-background-info').css('display','table-cell');
                $('.medical-background-info').css('color','red');
                $('.medical-background-yes').css('display','table-row');
            } else{
                $('.medical-background-yes,.medical-background-info').css('display','none');
            }
        });

        $('#respiratoryRateSelect').change(function(e){
            if(this.value == 'yes'){
                $('.respiratory-rate').css('display','inline-flex ');
            } else{
                $('.respiratory-rate').css('display','none');
            }
        });
        
        $('#oxygenSaturationSelect').change(function(e){
            if(this.value == 'yes'){
                $('.oxygen-saturation').css('display','inline-flex');
            } else{
                $('.oxygen-saturation').css('display','none');
            }
        });
        <?php if(isset($arr['covid19_positive_confirmatory_tests_required_by_central_lab']) && $arr['covid19_positive_confirmatory_tests_required_by_central_lab'] == 'yes'){ ?>
        $('.test-result,#result').change(function(e){
            checkPostive();
        });
        <?php }?>

    });

    function insRow() {
        rl = document.getElementById("testKitNameTable").rows.length;
        tableRowId = (rl + 1);
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
        c.innerHTML = '<input type="text" name="testName[]" id="testName' + tableRowId + '" class="form-control test-name-table-input" placeholder="Nom du test" title="Veuillez sélectionner le nom du test pour la ligne ' + tableRowId + '"/>';
        d.innerHTML = '<input type="text" name="testDate[]" id="testDate' + tableRowId + '" class="form-control test-name-table-input dateTime" placeholder="Testé sur"  title="Veuillez sélectionner la date du test pour la ligne ' + tableRowId + '"/>';
        e.innerHTML = '<select class="form-control test-result test-name-table-input" name="testResult[]" id="testResult' + tableRowId + '" title="Veuillez sélectionner le résultat pour la ligne ' + tableRowId + '"><option value=""> -- Select -- </option><?php foreach ($covid19Results as $c19ResultKey => $c19ResultValue) { ?> <option value="<?php echo $c19ResultKey; ?>"> <?php echo $c19ResultValue; ?> </option> <?php } ?> </select>';
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
        if($('#isResultAuthorized').val() == 'no'){
            $('#authorizedBy,#authorizedOn').val('');
            $('#authorizedBy,#authorizedOn').prop('disabled',true);
            $('#authorizedBy,#authorizedOn').addClass('disabled');
            $('#authorizedBy,#authorizedOn').removeClass('isRequired');
            return false;
        }else{
            $('#authorizedBy,#authorizedOn').prop('disabled',false);
            $('#authorizedBy,#authorizedOn').removeClass('disabled');
            $('#authorizedBy,#authorizedOn').addClass('isRequired');
        }
    }
</script>