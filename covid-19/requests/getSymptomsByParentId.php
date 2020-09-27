<?php
require_once('../../startup.php');


$general = new \Vlsm\Models\General($db);
$sampleData = array();
$symptomsQuery = 'SELECT * FROM r_covid19_symptoms where parent_symptom = "'. $_POST['symptomParent'].'"';
$covid19Symptoms = $db->query($symptomsQuery);

$disabled = (isset($_POST['from']) && $_POST['from'] == "update-result")?"disabled":"";

if(isset($_POST['covid19Id']) && $_POST['covid19Id'] != ''){
    $results = $db->rawQueryOne("SELECT * FROM covid19_patient_symptoms WHERE `covid19_id` = ".$_POST['covid19Id']);
    $symptomsArray = json_decode($results['symptom_details'], true);
}
if(count($covid19Symptoms) > 0){
    $index = 1;
    foreach($covid19Symptoms as $key=>$symptoms){
        $checked = (isset($results['symptom_id']) && $results['symptom_id'] == $_POST['symptomParent'] && in_array($symptoms['symptom_id'],$symptomsArray))?"checked":'';

        echo '<tr class="symptomRow'.$_POST['symptomParent'].' hide-symptoms" id="'.$_POST['symptomParent'].'">
                <td colspan="2" style="padding-left: 70px;display: flex;">
                    <label class="radio-inline" style="width:4%;padding-bottom:22px;margin-left:0;">
                        <input type="checkbox" class="reason-checkbox symptoms-checkbox" id="symptomDetails'.$symptoms['symptom_id'].'" name="symptomDetails[]" value="'.$symptoms['symptom_id'].'" title="Veuillez choisir la valeur pour '. $symptoms['symptom_name'].'" onclick="checkSubSymptoms(this,'.$symptoms['symptom_id'].','.$index.', "sub");" '.$checked.' ' .$disabled.'>
                    </label>
                    <label class="radio-inline" for="symptomDetails'.$symptoms['symptom_id'].'" style="padding-left:17px !important;margin-left:0;">'.ucwords($symptoms['symptom_name']).'</label>
                </td>
            </tr>';
    }
} else{
    echo "";
}
/* 
<th style="width:50%;padding-left:25px;">'.ucwords($sampleRow['symptom_name']).'</th>
<td style="width:50%;">
    <input name="symptomId[]" type="hidden" value="'.$sampleRow['symptom_id'].'">
    <select name="symptomDetected[]" class="symptom-input form-control" title="Veuillez choisir la valeur pour '. $sampleRow['symptom_name'].'" style="width:80%" onchange="checkSubSymptoms(this,'.$sampleRow['symptom_id'].','.$index.');">
        <option value="">-- Select --</option>
        <option value="yes" '.$yes.'> Oui </option>
        <option value="no" '.$no.'> Non </option>
        <option value="unknown" '.$unknown.'> Inconnu </option>
    </select>
</td> */