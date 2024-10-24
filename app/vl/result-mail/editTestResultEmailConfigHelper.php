<?php

use App\Services\SecurityService;
use App\Utilities\LoggerUtility;





$tableName = "other_config";
try {
    foreach ($_POST as $fieldName => $fieldValue) {
        if (trim($fieldName) != '') {
            if ($fieldName == 'rs_field') {
                if (count($fieldValue) > 0) {
                    $fieldValue = implode(',', $fieldValue);
                } else {
                    $fieldValue = '';
                }
            }
            $data = array('value' => $fieldValue);
            $db->where('name', $fieldName);
            $db->update($tableName, $data);
        }
    }
    $_SESSION['alertMsg'] = _translate("Test Result Email Config values updated successfully.");
    SecurityService::redirect("/vl/result-mail/testResultEmailConfig.php");
} catch (Throwable $exc) {
    LoggerUtility::log('error', $exc->getMessage());
}
