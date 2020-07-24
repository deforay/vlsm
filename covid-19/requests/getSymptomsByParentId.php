<?php
require_once('../../startup.php');
include_once(APPLICATION_PATH . '/includes/MysqliDb.php');
include_once(APPLICATION_PATH.'/models/General.php');
$general = new General($db);
$sampleData = array();
$sampleQuery = 'SELECT * FROM r_covid19_symptoms where parent_symptom = "'. $_POST['symptomParent'].'"';
$sampleResult = $db->query($sampleQuery);
if(count($sampleResult) > 0){
    foreach($sampleResult as $sampleRow){ 
        echo '<tr>
                <th style="width:50%;">'.ucwords($sampleRow['symptom_name']).'</th>
                <td style="width:50%;">
                    <select name="symptomDetected[]" class="form-control" title="Veuillez choisir la valeur pour '. $sampleRow['symptom_name'].'" style="width:100%">
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