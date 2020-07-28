<?php
#require_once('../../startup.php');
include_once(APPLICATION_PATH . '/includes/MysqliDb.php');
include_once(APPLICATION_PATH.'/models/General.php');
$general = new General($db);
$sampleData = array();
$sampleQuery = 'SELECT * FROM r_covid19_test_reasons where parent_reason = "'. $_POST['responseParent'].'"';
$sampleResult = $db->query($sampleQuery);
if(count($sampleResult) > 0){
    $index = 1;
    foreach($sampleResult as $key=>$sampleRow){ 
        echo '<tr class="responseRow'.$_POST['responseParent'].'" id="'.$_POST['responseParent'].'">
                <th style="width:50%;">'.ucwords($sampleRow['test_reason_name']).'</th>
                <td style="width:50%;">
                    <input name="responseId[]" type="hidden" value="'.$sampleRow['test_reason_id'].'">
                    <select name="responseDetected[]" class="form-control" title="Veuillez choisir la valeur pour '. $sampleRow['test_reason_name'].'" style="width:100%" onchange="checkSubResponse(this,'.$sampleRow['test_reason_id'].','.$index.');">
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