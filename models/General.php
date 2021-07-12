<?php

/**
 * General functions
 *
 * @author Amit
 */

namespace Vlsm\Models;

class General
{

    protected $db = null;

    public function __construct($db = null)
    {
        $this->db = $db;
    }

    public static function generateRandomString($length = 8, $type = 'alphanum')
    {

        // Possible seeds
        $seeds['alpha'] = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwqyzABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwqyzABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwqyz';
        $seeds['numeric'] = '01234567890123456789012345678901234567890123456789';
        $seeds['alphanum'] = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwqyz0123456789abcdefghijklmnopqrstuvwqyz0123456789abcdefghijklmnopqrstuvwqyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $seeds['hexidec'] = '0123456789abcdef';

        if (isset($seeds[$type])) {
            $keyspace = $seeds[$type];
        }

        $pieces = [];
        $max = mb_strlen($keyspace, '8bit') - 1;
        for ($i = 0; $i < $length; ++$i) {
            $pieces[] = $keyspace[random_int(0, $max)];
        }
        return implode('', $pieces);
    }

    public function generateUserID($length = 16)
    {
        return bin2hex(random_bytes($length));
    }

    /**
     * Used to format date from dd-mmm-yyyy to yyyy-mm-dd for storing in database
     *
     */
    public function dateFormat($date)
    {
        $date = trim($date);
        if (!isset($date) || $date == null || $date == "" || $date == "0000-00-00") {
            return null;
        } else {
            $dateArray = explode('-', $date);
            if (sizeof($dateArray) == 0) {
                return;
            }
            $newDate = $dateArray[2] . "-";

            $monthsArray = array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
            $mon = 1;
            $mon += array_search(ucfirst($dateArray[1]), $monthsArray);

            if (strlen($mon) == 1) {
                $mon = "0" . $mon;
            }
            return $newDate .= $mon . "-" . $dateArray[0];
        }
    }

    public function humanDateFormat($date, $returnTimePart = true)
    {
        $date = trim($date);
        if ($date == null || $date == "" || $date == "0000-00-00" || substr($date, 0, strlen("0000-00-00")) === "0000-00-00") {
            return null;
        } else {

            $dateTimeArray = explode(' ', $date);

            $dateArray = explode('-', $dateTimeArray[0]);
            $newDate = $dateArray[2] . "-";

            $monthsArray = array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
            $mon = $monthsArray[$dateArray[1] - 1];

            $newDate .= $mon . "-" . $dateArray[0];

            if ($returnTimePart && isset($dateTimeArray[1]) && $dateTimeArray[1] != '') {
                $newDate .= " " . $dateTimeArray[1];
            }

            return $newDate;
        }
    }

    public static function getDateTime()
    {
        $date = new \DateTime(date('Y-m-d H:i:s'));
        return $date->format('Y-m-d H:i:s');
    }

    public function removeDirectory($dirname)
    {
        // Sanity check
        if (!file_exists($dirname)) {
            return false;
        }

        // Simple delete for a file
        if (is_file($dirname) || is_link($dirname)) {
            return unlink($dirname);
        }

        // Loop through the folder
        $dir = dir($dirname);
        while (false !== $entry = $dir->read()) {
            // Skip pointers
            if ($entry == '.' || $entry == '..') {
                continue;
            }

            // Recurse
            $this->removeDirectory($dirname . DIRECTORY_SEPARATOR . $entry);
        }

        // Clean up
        $dir->close();
        return rmdir($dirname);
    }

    // get data from the system_config table from database
    public function getSystemConfig($name = null)
    {
        if ($this->db == null) {
            return false;
        }

        if (!empty($name)) {
            $this->db->where('name', $name);
        }

        $systemConfigResult = $this->db->get('system_config');

        $sarr = array();
        // now we create an associative array so that we can easily create view variables
        for ($i = 0; $i < sizeof($systemConfigResult); $i++) {
            $sarr[$systemConfigResult[$i]['name']] = $systemConfigResult[$i]['value'];
        }

        if ($name == null) {
            return $sarr;
        } else {
            if (isset($sarr[$name])) {
                return $sarr[$name];
            } else {
                return null;
            }
        }
    }

    // get province id from the province table
    public function getProvinceIDFromCode($code)
    {
        if ($this->db == null) {
            return false;
        }

        $pQuery = "SELECT * FROM province_details WHERE province_code like ?";
        $pResult = $this->db->rawQueryOne($pQuery, array($code));

        if ($pQuery) {
            return $pResult['province_id'];
        } else {
            return null;
        }
    }

    // get data from the global_config table from database
    public function getGlobalConfig($name = null)
    {

        if ($this->db == null) {
            return false;
        }

        if (!empty($name)) {
            $this->db->where('name', $name);
        }

        $globalConfigResult = $this->db->get('global_config');

        $garr = array();
        // now we create an associative array so that we can easily create view variables
        for ($i = 0; $i < sizeof($globalConfigResult); $i++) {
            $garr[$globalConfigResult[$i]['name']] = $globalConfigResult[$i]['value'];
        }

        // if no name was specified, we will send the full dataset
        if ($name == null) {
            return $garr;
        } else {
            if (isset($garr[$name])) {
                return $garr[$name];
            } else {
                return null;
            }
        }
    }

    public function fetchDataFromTable($tableName = null, $condition = null, $fieldName = null)
    {
        if ($this->db == null || $tableName == null) {
            return false;
        }

        $fieldName = ($fieldName != null) ? $fieldName : '*';
        if ($condition == null) {
            $configQuery = "SELECT $fieldName from $tableName";
        } else {
            $configQuery = "SELECT $fieldName from $tableName WHERE $condition";
        }
        return $this->db->query($configQuery);
    }

    // checking if the provided field list has any empty or null values
    public function checkMandatoryFields($field)
    {
        foreach ($field as $chkField) {
            if (empty(trim($chkField))) {
                return true;
            }
        }

        return false;
    }

    public function crypto($action, $inputString, $secretIv)
    {

        return $inputString;

        if (empty($inputString)) {
            return "";
        }

        $output = false;
        $encrypt_method = "AES-256-CBC";
        $secret_key = 'rXBCNkAzkHXGBKEReqrTfPhGDqhzxgDRQ7Q0XqN6BVvuJjh1OBVvuHXGBKEReqrTfPhGDqhzxgDJjh1OB4QcIGAGaml';

        // hash
        $key = hash('sha256', $secret_key);

        if (empty($secretIv)) {
            $secretIv = 'sd893urijsdf8w9eurj';
        }
        // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
        $iv = substr(hash('sha256', $secretIv), 0, 16);

        if ($action == 'encrypt') {
            $output = openssl_encrypt($inputString, $encrypt_method, $key, 0, $iv);
            $output = base64_encode($output);
        } else if ($action == 'decrypt') {
            $output = openssl_decrypt(base64_decode($inputString), $encrypt_method, $key, 0, $iv);
        }
        return $output;
    }

    public function activityLog($eventType, $action, $resource)
    {

        $ipaddress = '';
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        } else if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else if (isset($_SERVER['HTTP_X_FORWARDED'])) {
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        } else if (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        } else if (isset($_SERVER['HTTP_FORWARDED'])) {
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        } else if (isset($_SERVER['REMOTE_ADDR'])) {
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        } else {
            $ipaddress = 'UNKNOWN';
        }

        $data = array(
            'event_type' => $eventType,
            'action' => $action,
            'resource' => $resource,
            'date_time' => $this->getDateTime(),
            'ip_address' => $ipaddress,
        );

        $this->db->insert('activity_log', $data);
    }

    public function resultImportStats($numberOfResults, $importMode, $importedBy)
    {

        $data = array(
            'no_of_results_imported' => $numberOfResults,
            'imported_on' => $this->getDateTime(),
            'import_mode' => $importMode,
            'imported_by' => $importedBy,
        );

        $this->db->insert('result_import_stats', $data);
    }

    public function getLowVLResultTextFromImportConfigs($machineFile = null)
    {
        if ($this->db == null) {
            return false;
        }

        if (!empty($machineFile)) {
            $this->db->where('import_machine_file_name', $machineFile);
        }

        $this->db->where("low_vl_result_text", NULL, 'IS NOT');
        $this->db->where("status", 'active', 'like');
        return $this->db->getValue('import_config', 'low_vl_result_text', null);
    }

    public function getFacilitiesByUser($userId = null)
    {

        $fQuery = "SELECT * FROM facility_details where status='active'";

        $facilityWhereCondition = '';

        if (!empty($userId)) {
            $userfacilityMapQuery = "SELECT GROUP_CONCAT(DISTINCT `facility_id` SEPARATOR ',') as `facility_id` FROM vl_user_facility_map WHERE user_id='" . $userId . "'";
            $userfacilityMapresult = $this->db->rawQuery($userfacilityMapQuery);
            if ($userfacilityMapresult[0]['facility_id'] != null && $userfacilityMapresult[0]['facility_id'] != '') {
                $facilityWhereCondition = " AND facility_id IN (" . $userfacilityMapresult[0]['facility_id'] . ") ";
            }
        }

        return $this->db->rawQuery($fQuery . $facilityWhereCondition . " ORDER BY facility_name ASC");
    }

    public function getEidResults()
    {
        $results = $this->db->rawQuery("SELECT * FROM r_eid_results where status='active' ORDER BY result_id DESC");
        $response = array();
        foreach ($results as $row) {
            $response[$row['result_id']] = $row['result'];
        }
        return $response;
    }

    public function getCovid19Results()
    {
        $results = $this->db->rawQuery("SELECT * FROM r_covid19_results where status='active' ORDER BY result_id DESC");
        $response = array();
        foreach ($results as $row) {
            $response[$row['result_id']] = $row['result'];
        }
        return $response;
    }

    public function startsWith($string, $startString)
    {
        $len = strlen($startString);
        return (substr($string, 0, $len) === $startString);
    }

    public function generateSelectOptions($optionList, $selectedOptions = array(), $emptySelectText = false)
    {

        if (empty($optionList)) {
            return '';
        }
        $response = '';
        if ($emptySelectText !== false) {
            $response .= "<option value=''>$emptySelectText</option>";
        }

        foreach ($optionList as $optId => $optName) {
            $selectedText = '';
            if (!empty($selectedOptions)) {
                if (is_array($selectedOptions) && in_array($optId, $selectedOptions)) {
                    $selectedText = "selected='selected'";
                } else if ($optId == $selectedOptions) {
                    $selectedText = "selected='selected'";
                }
            }
            $response .= "<option value='" . addslashes($optId) . "' $selectedText>" . addslashes($optName) . "</option>";
        }
        return $response;
    }

    public function getLastModifiedDateTime($tableName, $modifiedDateTimeColName = 'updated_datetime')
    {
        $query = "SELECT $modifiedDateTimeColName FROM $tableName ORDER BY $modifiedDateTimeColName DESC LIMIT 1";

        $result = $this->db->rawQueryOne($query);

        if (isset($result[$modifiedDateTimeColName]) && $result[$modifiedDateTimeColName] != '' && $result[$modifiedDateTimeColName] != NULL && !$this->startsWith($result[$modifiedDateTimeColName], '0000-00-00')) {
            return $result[$modifiedDateTimeColName];
        } else {
            return null;
        }
    }

    public function getHeader($key)
    {
        $headers = apache_request_headers();
        foreach ($headers as $header => $value) {
            if (strtolower($key) == strtolower($header)) {
                return $value;
            }
        }
    }

    public function getHttpValue($key)
    {
        // print_r($_SERVER);die;
        foreach ($_SERVER as $header => $value) {
            if (substr($header, 0, 5) == "HTTP_") {
                $header = str_replace(" ", "-", ucwords(strtolower(str_replace("_", " ", substr($header, 5)))));
                if (strtolower($key) == strtolower($header)) {
                    return $value;
                }
            }
        }
        // return $out; 
    }

    public function getTestingPlatforms($testType = null)
    {

        if (!empty($testType)) {
            $this->db->where("(JSON_SEARCH(supported_tests, 'all', '$testType') IS NOT NULL) OR (supported_tests IS NULL)");
        }
        $this->db->where("status", "active");
        $this->db->orderBy('machine_name', "ASC");
        return $this->db->get('import_config');
    }

    public function getDuplicateDataFromField($tablename, $fieldname, $fieldValue, $lab = "")
    {
        $query = 'SELECT * FROM ' . $tablename . ' WHERE ' . $fieldname . ' =  "' . $fieldValue . '"';
        if ($lab != "") {
            $query .= " AND $lab like 2";
        }
        $query .= " LIMIT 1";
        return $this->db->rawQueryOne($query);
    }

    public function random_color_part()
    {
        return str_pad(dechex(mt_rand(0, 255)), 2, '0', STR_PAD_LEFT);
    }

    public function random_color()
    {
        return $this->random_color_part() . $this->random_color_part() . $this->random_color_part();
    }

    public function ageInMonth($date)
    {
        $birthday = new \DateTime($date);
        $diff = $birthday->diff(new \DateTime());
        return $diff->format('%m') + 12 * $diff->format('%y');
    }

    public function ageInYearMonthDays($date)
    {
        $bday = new \DateTime($date); // Your date of birth
        $today = new \Datetime(date('m.d.y'));
        $diff = $today->diff($bday);
        // printf(' Your age : %d years, %d month, %d days', $diff->y, $diff->m, $diff->d);
        return array("year" => $diff->y, "months" => $diff->m, "days" => $diff->d);
    }

    public function getRejectionReasons($testType)
    {
        $rejArray = array('general', 'whole blood', 'plasma', 'dbs', 'testing');
        if ($testType == "vl") {
            $rejArray = array('general', 'whole blood', 'plasma', 'dbs', 'testing');
        }
        if ($testType == "eid") {
            $rejArray = array('general', 'whole blood', 'plasma', 'dbs', 'testing');
        }
        if ($testType == "covid19") {
            $rejArray = array('general', 'whole blood', 'plasma', 'dbs', 'testing');
        }
        if ($testType == "hepatitis") {
            $rejArray = array('general', 'whole blood', 'plasma', 'dbs', 'testing');
        }
        foreach ($rejArray as $rej) {
            $rejReaons[$rej] = ucwords($rej);
        }
        return $rejReaons;
    }

    public function getValueByName($name = "", $condtionField, $tableName, $id, $occurate = false)
    {
        $where = "";
        if (!empty($name)) {
            if ($occurate) {
                $where = $condtionField . " LIKE '%$name%'";
            } else {
                $where = $condtionField . " LIKE '$name%'";
            }

            $query = "SELECT " . $id . " FROM " . $tableName . " where " . $where;
            $result =  $this->db->rawQuery($query);
            return $result[0][$id];
        } else {
            return null;
        }
    }

    public function activeReportFormats($module = "vl", $countryCode = "southsudan", $format = "", $list = true)
    {
        $list = array();
        if ($module == 'vl') {

            if (isset($format) && $format != null) {
                $path = APPLICATION_PATH . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . 'results/pdf/resultPdf' . $countryCode . '-' . $format . '.php';
            } else {
                $path = APPLICATION_PATH . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . 'results/pdf/resultPdf' . $countryCode . '*.php';
            }
            $pdfFormat = glob($path, true);
            if (isset($pdfFormat) && sizeof($pdfFormat) > 0) {
                foreach ($pdfFormat as $formatPath) {
                    $index = substr($formatPath, strpos($formatPath, "results/") + 8);
                    $cut = str_replace("-", "", substr($index, strpos($index, "resultPdf" . $countryCode . "-") + 14));
                    $value = substr($cut, 0, strpos($cut, ".php"));
                    $list[$index] = ucwords($value);
                }
            } else {
                $list['pdf/resultPdf-' . $countryCode . '.pdf'] = "Default";
            }
        } else {

            if (isset($format) && $format != null) {
                $path = APPLICATION_PATH . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . 'results/pdf/result-pdf-' . $countryCode . '-' . $format . '.php';
            } else {
                $path = APPLICATION_PATH . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . 'results/pdf/result-pdf-' . $countryCode . '*.php';
            }
            $pdfFormat = glob($path, true);
            if (isset($pdfFormat) && sizeof($pdfFormat) > 0) {
                foreach ($pdfFormat as $formatPath) {
                    $index = substr($formatPath, strpos($formatPath, "results/") + 8);
                    $cut = str_replace("-", "", substr($index, strpos($index, "result-pdf-" . $countryCode . "-") - 15));
                    $value = substr($cut, 0, strpos($cut, ".php"));
                    $list[$index] = ucwords($value);
                }
            } else {
                $list['pdf/result-pdf-' . $countryCode . '.pdf'] = "Default";
            }
        }
        return $list;
    }

    public function reportPdfNames($module = null)
    {
        $arr = $this->getGlobalConfig();
        $cntId = array();
        if ($arr['vl_form'] == 1) {
            $cntId['covid19'] = 'ssudan';
            $cntId['eid'] = 'ssudan';
            $cntId['vl'] = 'SouthSudan';
        } else if ($arr['vl_form'] == 2) {
            $cntId['vl'] = 'Zm';
            $cntId['covid19'] = 'zm';
        } else if ($arr['vl_form'] == 3) {
            $cntId['vl'] = 'Drc';
            $cntId['eid'] = 'drc';
            $cntId['covid19'] = 'drc';
        } else if ($arr['vl_form'] == 4) {
            $cntId['vl'] = 'Zam';
            $cntId['covid19'] = 'zam';
        } else if ($arr['vl_form'] == 5) {
            $cntId['vl'] = 'Png';
            $cntId['covid19'] = 'png';
        } else if ($arr['vl_form'] == 6) {
            $cntId['vl'] = 'Who';
            $cntId['covid19'] = 'who';
        } else if ($arr['vl_form'] == 7) {
            $cntId['vl'] = 'Rwd';
            $cntId['hepatitis'] = 'rwanda';
            $cntId['eid'] = 'rwanda';
            $cntId['covid19'] = 'rwanda';            
        } else if ($arr['vl_form'] == 8) {
            $cntId['vl'] = 'Ang';
            $cntId['eid'] = 'angola';
            $cntId['covid19'] = 'angola';
        }
        if($module != null){
            return $cntId[$module];
        }
        return $cntId;
    }
}
