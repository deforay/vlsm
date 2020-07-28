<?php
require_once('../../startup.php');
include_once(APPLICATION_PATH . '/includes/MysqliDb.php');
include_once(APPLICATION_PATH.'/models/General.php');
$general = new General($db);
$sampleData = array();
$sampleQuery = 'SELECT * FROM r_covid19_symptoms where parent_symptom = "'. $_POST['symptomParent'].'"';
$sampleResult = $db->query($sampleQuery);

if(isset($_POST['covid19Id']) && $_POST['covid19Id'] != ''){
    $results = $db->rawQuery("SELECT * FROM covid19_patient_symptoms WHERE `covid19_id` = ".$_POST['covid19Id']);
    foreach ($results as $row) {
        $response[$row['symptom_id']] = $row['symptom_detected'];
    }
}
if(count($sampleResult) > 0){
    $index = 1;
    foreach($sampleResult as $key=>$sampleRow){
        $yes = (isset($response[$sampleRow['symptom_id']]) && $response[$sampleRow['symptom_id']] == 'yes')?"selected='selected'":'';
        $no = (isset($response[$sampleRow['symptom_id']]) && $response[$sampleRow['symptom_id']] == 'no')?"selected='selected'":'';
        $unknown = (isset($response[$sampleRow['symptom_id']]) && $response[$sampleRow['symptom_id']] == 'unknown')?"selected='selected'":'';

        echo '<tr class="symptomRow'.$_POST['symptomParent'].'" id="'.$_POST['symptomParent'].'">
                <th style="width:50%;padding-left:25px;">'.ucwords($sampleRow['symptom_name']).'</th>
                <td style="width:50%;">
                    <input name="symptomId[]" type="hidden" value="'.$sampleRow['symptom_id'].'">
                    <select name="symptomDetected[]" class="symptom-input form-control" title="Veuillez choisir la valeur pour '. $sampleRow['symptom_name'].'" style="width:80%" onchange="checkSubSymptoms(this,'.$sampleRow['symptom_id'].','.$index.');">
                        <option value="">-- Select --</option>
                        <option value="yes" '.$yes.'> Oui </option>
                        <option value="no" '.$no.'> Non </option>
                        <option value="unknown" '.$unknown.'> Inconnu </option>
                    </select>
                </td>
            </tr>';
    }
} else{
    echo "";
}