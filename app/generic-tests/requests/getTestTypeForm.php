<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$testTypeForm = [];
if (isset($_POST['testTypeForm']) && !empty($_POST['testTypeForm'])) {
    $testTypeForm = json_decode(base64_decode($_POST['testTypeForm']), true);
}
$disabled = "";
if (isset($_POST['formType']) && !empty($_POST['formType']) && $_POST['formType'] == 'update-form') {
    $disabled = ' disabled ';
}
$testTypeQuery = "SELECT * FROM r_test_types WHERE test_type_id= ?";
$testTypeResult = $db->rawQuery($testTypeQuery, [$_POST['testType']]);
$testAttribute = json_decode($testTypeResult[0]['test_form_config'], true);
$testResultsAttribute = json_decode($testTypeResult[0]['test_results_config'], true);

$facilityForm = [];
$patientForm = [];
$specimenForm = [];
$labForm = [];
$resultForm = [];
$otherForm = [];
$others = [];
$otherSection = [];
$content = [];
$n = count($testAttribute['field_name']);
if ($n > 0) {
    for ($i = 0; $i < $n; $i++) {
        $isRequired = "";
        $mandatory = "";
        $fieldType = "";
        if ($testAttribute['mandatory_field'][$i] == 'yes') {
            $isRequired = "isRequired";
            $mandatory = '<span class="mandatory">*</span>';
        }
        $value = "";
        if (isset($testTypeForm[$testAttribute['field_id'][$i]]) && !empty($testTypeForm[$testAttribute['field_id'][$i]])) {
            $value = $testTypeForm[$testAttribute['field_id'][$i]];
        }
        if ($testAttribute['field_type'][$i] == 'number') {
            $fieldType = " forceNumeric";
        } elseif ($testAttribute['field_type'][$i] == 'date') {
            $fieldType = " date";
        }

        if (
            isset($_POST['formType']) &&
            !empty($_POST['formType']) &&
            $_POST['formType'] == 'update-form' &&
            $testAttribute['section'][$i] != 'lab'
        ) {
            $isRequired = "";
        }
        if ($testAttribute['section'][$i] == 'facility') {
            $facilityForm[] = '<div class="col-xs-3 col-md-3" id="clinicDynamicFormInput"><div class="form-group"><label>' . $testAttribute['field_name'][$i] . $mandatory . '</label><input type="text" class="form-control ' . $isRequired . $fieldType . $disabled . '" placeholder="' . $testAttribute['field_name'][$i] . '" id="' . $testAttribute['field_id'][$i] . '" name="dynamicFields[' . $testAttribute['field_id'][$i] . ']" value="' . $value . '" ' . $disabled . '><input type="hidden" class="form-control" name="testTypeId[]" value="' . $testAttribute['field_id'][$i] . '"></div></div>';
        } elseif ($testAttribute['section'][$i] == 'patient') {
            $patientForm[] = '<div class="col-xs-3 col-md-3" id="patientDynamicFormInput"><div class="form-group"><label>' . $testAttribute['field_name'][$i] . $mandatory . '</label><input type="text" class="form-control ' . $isRequired . $fieldType . $disabled . '" placeholder="' . $testAttribute['field_name'][$i] . '" id="' . $testAttribute['field_id'][$i] . '" name="dynamicFields[' . $testAttribute['field_id'][$i] . ']" value="' . $value . '" ' . $disabled . '><input type="hidden" class="form-control" name="testTypeId[]" value="' . $testAttribute['field_id'][$i] . '"></div></div>';
        } elseif ($testAttribute['section'][$i] == 'specimen') {
            $specimenForm[] = '<div class="col-xs-3 col-md-3" id="specimenDynamicFormInput"><div class="form-group"><label>' . $testAttribute['field_name'][$i] . $mandatory . '</label><input type="text" class="form-control ' . $isRequired . $fieldType . $disabled . '" placeholder="' . $testAttribute['field_name'][$i] . '" id="' . $testAttribute['field_id'][$i] . '" name="dynamicFields[' . $testAttribute['field_id'][$i] . ']" value="' . $value . '" ' . $disabled . '><input type="hidden" class="form-control" name="testTypeId[]" value="' . $testAttribute['field_id'][$i] . '"></div></div>';
        } elseif ($testAttribute['section'][$i] == 'lab') {
            $labForm[]      = '<div class="col-md-6" id="lapDynamicFormInput"><label class="col-lg-5 control-label labels">' . $testAttribute['field_name'][$i] . $mandatory . '</label><div class="col-lg-7"><input type="text" class="form-control ' . $isRequired . $fieldType . $disabled . '" placeholder="' . $testAttribute['field_name'][$i] . '" id="' . $testAttribute['field_id'][$i] . '" name="dynamicFields[' . $testAttribute['field_id'][$i] . ']" value="' . $value . '"><input type="hidden" class="form-control" name="testTypeId[]" value="' . $testAttribute['field_id'][$i] . '"></div></div>';
        } elseif ($testAttribute['section'][$i] == 'other') {
            if(in_array($testAttribute['section_other'][$i], $otherSection)){
                if(!isset($s[trim(strtolower($testAttribute['section_other'][$i]))]))
                    $s[trim(strtolower($testAttribute['section_other'][$i]))] = $i;
            }else{
                $s[trim(strtolower($testAttribute['section_other'][$i]))] = $i;
                $otherSection[] = $testAttribute['section_other'][$i];
            }
            $title = '<div class="box-header with-border"><h3 class="box-title">' . $testAttribute['section_other'][$i] . '</h3></div>';
            $content[trim(strtolower($testAttribute['section_other'][$i]))] .= '<div class="col-xs-3 col-md-3"><div class="form-group"><label>' . $testAttribute['field_name'][$i] . $mandatory . '</label><input type="text" class="form-control ' . $isRequired . $fieldType . $disabled . '" placeholder="' . $testAttribute['field_name'][$i] . '" id="' . $testAttribute['field_id'][$i] . '" name="dynamicFields[' . $testAttribute['field_id'][$i] . ']" value="' . $value . '" ' . $disabled . '><input type="hidden" class="form-control" name="testTypeId[]" value="' . $testAttribute['field_id'][$i] . '"></div></div>';
            $others[$s[trim(strtolower($testAttribute['section_other'][$i]))]] = $title . '<div class="box-body"><div class="row">' .$content[trim(strtolower($testAttribute['section_other'][$i]))] .'</div></div>';
        }
    }
    $key = 0;
    foreach($others as $form){
        $otherForm[$key] = "<div class='box box-primary' style='margin-top: 0px; margin-left: -1px;'>" . $form . "</div></div>";
        $key++;
    }
}
// echo "<pre>";
// print_r($testResultsAttribute);die;
if(isset($testResultsAttribute) && !empty($testResultsAttribute)){
    if($testResultsAttribute['result_type'] = 'qualitative'){
        $resultSection = '<select class="form-control result-select" name="result" id="result">';
        $resultSection .= '<option value="">-- Select --</option>';
        if(isset($testResultsAttribute['result']) && !empty($testResultsAttribute['result'])){
            foreach($testResultsAttribute['result'] as $row){
                $selected = (isset($_POST['result']) && $_POST['result'] != "" && $_POST['result'] == $row)? "selected":"";
                $resultSection .= '<option value="'.$row.'" '.$selected.'>'.ucwords($row).'</option>';
            }
        }
        $resultSection .= '</select>';
    }else{
        $resultSection = '<input type="text" id="result" name="result" class="form-control result-text" placeholder="Enter final result" title="Please enter final results">';
    }
    $resultForm[] = $resultSection;
}
$result = [
    'facility' => $facilityForm,
    'patient' => $patientForm,
    'specimen' => $specimenForm,
    'lab' => $labForm,
    'others' => $otherForm,
    'result' => $resultForm
];

echo json_encode($result);
