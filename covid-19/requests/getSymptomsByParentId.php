<?php
#require_once('../../startup.php');
include_once(APPLICATION_PATH . '/includes/MysqliDb.php');
include_once(APPLICATION_PATH.'/models/General.php');
$general = new General($db);
$sampleData = array();
$sampleQuery = 'SELECT * FROM r_covid19_symptoms where parent_symptom = "'. $_POST['symptomParent'].'"';
$sampleResult = $db->query($sampleQuery);
if(count($sampleResult) > 0){
    $index = 1;
    foreach($sampleResult as $key=>$sampleRow){ 
        echo '<tr class="symptomRow'.$_POST['symptomParent'].'" id="'.$_POST['symptomParent'].'">
                <th style="width:50%;">'.ucwords($sampleRow['symptom_name']).'</th>
                <td style="width:50%;">
                    <input name="symptomId[]" type="hidden" value="'.$sampleRow['symptom_id'].'">
                    <select name="symptomDetected[]" class="form-control" title="Veuillez choisir la valeur pour '. $sampleRow['symptom_name'].'" style="width:100%" onchange="checkSubSymptoms(this,'.$sampleRow['symptom_id'].','.$index.');">
                        <option value="">-- Select --</option>
                        <option value="yes"> Oui </option>
                        <option value="no"> Non </option>
                        <option value="unknown"> Inconnu </option>
                    </select>
                </td>
            </tr>';
    }
} else{
    echo "";
}