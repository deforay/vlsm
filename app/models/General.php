<?php

/**
 * General functions
 *
 * @author Amit
 */

namespace Vlsm\Models;

use Ramsey\Uuid\Uuid;
use Vlsm\Utilities\DateUtils;

class General
{

    protected $db = null;

    public function __construct($db = null)
    {
        $this->db = !empty($db) ? $db : \MysqliDb::getInstance();
    }

    public function setDb($db)
    {
        $this->db = $db;
    }

    public static function generateRandomString($length = 32)
    {
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $number = random_int(0, 36);
            $character = base_convert($number, 10, 36);
            $randomString .= $character;
        }
        return $randomString;
    }

    public function escape($inputArray, $db = null)
    {
        $db = !empty($db) ? $db : $this->db;
        $escapedArray = array();
        foreach ($inputArray as $key => $value) {
            $escapedArray[$key] = $db->escape($value);
        }
        return $escapedArray;
    }

    // Returns a UUID format string
    public function generateUUID($attachExtraString = false)
    {
        // $uuid = sprintf(
        //     '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        //     random_int(0, 0xffff),
        //     random_int(0, 0xffff),
        //     random_int(0, 0xffff),
        //     random_int(0, 0x0C2f) | 0x4000,
        //     random_int(0, 0x3fff) | 0x8000,
        //     random_int(0, 0x2Aff),
        //     random_int(0, 0xffD3),
        //     random_int(0, 0xff4B)
        // );

        $uuid = Uuid::uuid4();
        $uuid = $uuid->toString();

        if ($attachExtraString) {
            $uuid .= "-" . $this->generateRandomString(4);
        }
        return $uuid;
    }

    //This will return a hex token that is twice the specified length
    public function generateToken($length = 16)
    {
        return bin2hex(random_bytes($length));
    }

    /**
     * Used to format date from dd-mmm-yyyy to yyyy-mm-dd for storing in database
     *
     */
    public function isoDateFormat($date, $includeTime = false)
    {
        $utils = new DateUtils();
        return $utils->isoDateFormat($date, $includeTime);
    }

    public function humanReadableDateFormat($date, $includeTime = false)
    {
        $utils = new DateUtils();
        return $utils->humanReadableDateFormat($date, $includeTime);
    }

    public static function getCurrentDateTime($returnFormat = 'Y-m-d H:i:s')
    {
        $utils = new DateUtils();
        return $utils->getCurrentDateTime($returnFormat);
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

    // get data from the global_config table from database
    public function getGlobalConfig($name = null)
    {

        if ($this->db == null) {
            return false;
        }

        if (!empty($name)) {
            $this->db->where('name', $name);
            return $this->db->getValue("global_config", "value");
        } else {
            $garr = array();
            $globalConfigResult = $this->db->get('global_config');
            // now we create an associative array so that we can easily create view variables
            for ($i = 0; $i < sizeof($globalConfigResult); $i++) {
                $garr[$globalConfigResult[$i]['name']] = $globalConfigResult[$i]['value'];
            }

            return $garr;
        }
    }

    public function fetchDataFromTable($tableName = null, $condition = null, $fieldName = null)
    {
        if ($this->db == null || empty($tableName)) {
            return false;
        }

        $fieldName = ($fieldName != null) ? $fieldName : '*';

        $configQuery = "SELECT $fieldName from $tableName";

        if ($condition != null) {
            $configQuery .= " WHERE $condition ";
        }

        if ($tableName == "testing_labs") {
            $configQuery = "SELECT test_type, facility_id, updated_datetime, monthly_target, suppressed_monthly_target from $tableName";
            if ($condition != null) {
                $configQuery .= " WHERE $condition ";
            }
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

    public static function encrypt($message, $key)
    {
        $nonce = random_bytes(
            SODIUM_CRYPTO_SECRETBOX_NONCEBYTES
        );

        $cipher = sodium_bin2base64(
            $nonce .
                sodium_crypto_secretbox(
                    $message,
                    $nonce,
                    $key
                ),
            SODIUM_BASE64_VARIANT_URLSAFE
        );
        sodium_memzero($message);
        sodium_memzero($key);
        return $cipher;
    }

    public static function decrypt($encrypted, $key)
    {
        $decoded = sodium_base642bin($encrypted, SODIUM_BASE64_VARIANT_URLSAFE);
        if ($decoded === false) {
            throw new \Exception('The message encoding failed');
        }
        if (mb_strlen($decoded, '8bit') < (SODIUM_CRYPTO_SECRETBOX_NONCEBYTES + SODIUM_CRYPTO_SECRETBOX_MACBYTES)) {
            throw new \Exception('The message was truncated');
        }
        $nonce = mb_substr($decoded, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, '8bit');
        $ciphertext = mb_substr($decoded, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, null, '8bit');

        $plain = sodium_crypto_secretbox_open(
            $ciphertext,
            $nonce,
            $key
        );
        if ($plain === false) {
            throw new \Exception('The message was tampered with in transit');
        }
        sodium_memzero($ciphertext);
        sodium_memzero($key);
        return $plain;
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
            'user_id' => (!empty($_SESSION['userId'])) ? $_SESSION['userId'] : null,
            'date_time' => $this->getCurrentDateTime(),
            'ip_address' => $ipaddress,
        );

        $this->db->insert('activity_log', $data);
    }

    public function resultImportStats($numberOfResults, $importMode, $importedBy)
    {

        $data = array(
            'no_of_results_imported' => $numberOfResults,
            'imported_on' => $this->getCurrentDateTime(),
            'import_mode' => $importMode,
            'imported_by' => $importedBy,
        );

        $this->db->insert('result_import_stats', $data);
    }


    public function getFacilitiesByUser($userId = null)
    {

        $fQuery = "SELECT * FROM facility_details where status='active'";

        $facilityWhereCondition = '';

        if (!empty($userId)) {
            $userfacilityMapQuery = "SELECT GROUP_CONCAT(DISTINCT `facility_id` SEPARATOR ',') as `facility_id` FROM user_facility_map WHERE user_id='" . $userId . "'";
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

    public function getTbResults()
    {
        $results = $this->db->rawQuery("SELECT * FROM r_tb_results where status='active' ORDER BY result_id DESC");
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

        if (!empty($testType) && $testType == "tb") {
            $this->db->where("(JSON_SEARCH(supported_tests, 'all', 'tb') IS NOT NULL) AND (supported_tests IS NOT NULL)");
        } else if (!empty($testType)) {
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
        return str_pad(dechex(random_int(0, 255)), 2, '0', STR_PAD_LEFT);
    }

    public function random_color()
    {
        return $this->random_color_part() . $this->random_color_part() . $this->random_color_part();
    }

    public function ageInMonth($date)
    {
        $birthday = new \DateTimeImmutable($date);
        $diff = $birthday->diff(new \DateTimeImmutable());
        return $diff->format('%m') + 12 * $diff->format('%y');
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
        if ($testType == "tb") {
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

    public function getLocaleLists()
    {
        $path = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'locale';
        $localeLists = scandir($path);
        foreach (array(".", "..", ".DS_Store") as $delVal) {
            if (($key = array_search($delVal, $localeLists)) !== false) {
                unset($localeLists[$key]);
            }
        }
        return $localeLists;
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
                    /* Previous code updated */
                    $str = explode("/", $index);
                    $str = explode("-", $str[1]);
                    $str = ucwords($str[2]) . "-" . ucwords($str[3]);
                    $value = substr($str, 0, strpos($str, ".php"));
                    /* $cut = str_replace("-", "", substr($index, strpos($index, "result-pdf-" . $countryCode . "-") - 15));
                    $value = substr($cut, 0, strpos($cut, ".php")); */
                    $list[$index] = strtoupper($value);
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
        if ($module != null) {
            return $cntId[$module];
        }
        return $cntId;
    }

    public function trackQrViewPage($type, $typeId, $sampleCode)
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'];

        $data = array(
            'test_type' => $type,
            'test_type_id' => $typeId,
            'sample_code' => $sampleCode,
            'browser' => $this->getBrowser($userAgent),
            'operating_system' => $this->getOperatingSystem($userAgent),
            'date_time' => $this->getCurrentDateTime(),
            'ip_address' => $this->getIPAddress(),
        );

        $this->db->insert('track_qr_code_page', $data);
    }

    public function getIPAddress()
    {
        $ipaddress = '';
        if (getenv('HTTP_CLIENT_IP'))
            $ipaddress = getenv('HTTP_CLIENT_IP');
        else if (getenv('HTTP_X_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
        else if (getenv('HTTP_X_FORWARDED'))
            $ipaddress = getenv('HTTP_X_FORWARDED');
        else if (getenv('HTTP_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_FORWARDED_FOR');
        else if (getenv('HTTP_FORWARDED'))
            $ipaddress = getenv('HTTP_FORWARDED');
        else if (getenv('REMOTE_ADDR'))
            $ipaddress = getenv('REMOTE_ADDR');
        else
            $ipaddress = 'UNKNOWN';
        return $ipaddress;
    }

    public function getOperatingSystem($userAgent = null)
    {
        $osPlatform = "Unknown OS - " . $userAgent;

        $osArray =  array(
            '/windows nt 6.3/i'     =>  'Windows 8.1',
            '/windows nt 6.2/i'     =>  'Windows 8',
            '/windows nt 6.1/i'     =>  'Windows 7',
            '/windows nt 6.0/i'     =>  'Windows Vista',
            '/windows nt 5.2/i'     =>  'Windows Server 2003/XP x64',
            '/windows nt 5.1/i'     =>  'Windows XP',
            '/windows xp/i'         =>  'Windows XP',
            '/windows nt 5.0/i'     =>  'Windows 2000',
            '/windows me/i'         =>  'Windows ME',
            '/win98/i'              =>  'Windows 98',
            '/win95/i'              =>  'Windows 95',
            '/win16/i'              =>  'Windows 3.11',
            '/macintosh|mac os x/i' =>  'Mac OS X',
            '/mac_powerpc/i'        =>  'Mac OS 9',
            '/linux/i'              =>  'Linux',
            '/ubuntu/i'             =>  'Ubuntu',
            '/iphone/i'             =>  'iPhone',
            '/ipod/i'               =>  'iPod',
            '/ipad/i'               =>  'iPad',
            '/android/i'            =>  'Android',
            '/blackberry/i'         =>  'BlackBerry',
            '/webos/i'              =>  'Mobile'
        );

        foreach ($osArray as $regex => $value) {
            if (preg_match($regex, $userAgent)) {
                $osPlatform    =   $value;
            }
        }
        return $osPlatform;
    }

    public function getBrowser($userAgent = null)
    {

        $browser        =   "Unknown Browser - " . $userAgent;
        $browserArray  =   array(
            '/msie/i'       =>  'Internet Explorer',
            '/firefox/i'    =>  'Firefox',
            '/safari/i'     =>  'Safari',
            '/chrome/i'     =>  'Chrome',
            '/opera/i'      =>  'Opera',
            '/netscape/i'   =>  'Netscape',
            '/maxthon/i'    =>  'Maxthon',
            '/konqueror/i'  =>  'Konqueror',
            '/mobile/i'     =>  'Handheld Browser'
        );

        foreach ($browserArray as $regex => $value) {

            if (preg_match($regex, $userAgent)) {
                $browser    =   $value;
            }
        }

        return $browser;
    }
    public function getLastSyncDateTime()
    {
        if (isset($_SESSION['instanceType']) && $_SESSION['instanceType'] == 'remoteuser') {
            $dateTime = $this->db->rawQueryOne("SELECT MAX(`requested_on`) AS `dateTime` FROM `track_api_requests`");
        } else {
            $dateTime = $this->db->rawQueryOne("SELECT GREATEST(COALESCE(last_remote_requests_sync, 0), COALESCE(last_remote_results_sync, 0), COALESCE(last_remote_reference_data_sync, 0)) AS dateTime FROM s_vlsm_instance");
        }
        return (isset($dateTime['dateTime']) && $dateTime['dateTime'] != "") ? date('d-M-Y h:i:s a', strtotime($dateTime['dateTime'])) : null;
    }

    public function existBatchCode($code)
    {
        $this->db->where("batch_code", $code);
        return $this->db->getOne("batch_details");
    }

    public function createBatchCode($start, $end)
    {
        $batchQuery = 'SELECT MAX(batch_code_key) FROM batch_details as bd WHERE DATE(bd.request_created_datetime) = CURRENT_DATE';
        $batchResult = $this->db->query($batchQuery);

        if ($batchResult[0]['MAX(batch_code_key)'] != '' && $batchResult[0]['MAX(batch_code_key)'] != NULL) {
            $code = $batchResult[0]['MAX(batch_code_key)'] + 1;
            $length = strlen($code);
            if ($length == 1) {
                $code = "00" . $code;
            } else if ($length == 2) {
                $code = "0" . $code;
            }
        } else {
            $code = '001';
        }
        // $this->db->where("DATE(request_created_datetime) = CURRENT_DATE AND batch_code_key = $code");
        // $exist = $this->db->getOne("batch_details");

        // if ($exist) {
        //     $code = $code + 1;
        //     $length = strlen($code);
        //     if ($length == 1) {
        //         $code = "00" . $code;
        //     } else if ($length == 2) {
        //         $code = "0" . $code;
        //     } else if ($length == 3) {
        //         $code = $code;
        //     }
        // }
        return $code;
    }

    function excelColumnRange($lower, $upper)
    {
        ++$upper;
        for ($i = $lower; $i !== $upper; ++$i) {
            yield $i;
        }
    }

    public function fileExists($filePath): bool
    {
        return (!empty($filePath) && file_exists($filePath) && !is_dir($filePath) && filesize($filePath) > 0);
    }


    // This function removes control characters from the strings in the CSV file.
    // https://en.wikipedia.org/wiki/Control_character#ASCII_control_characters
    // Also checks UTF-8 encoding and converts if needed
    public function removeCntrlCharsAndEncode($inputString, $encodeToUTF8 = true): string
    {
        $inputString = preg_replace('/[[:cntrl:]]/', '',  $inputString);
        if ($encodeToUTF8 && mb_detect_encoding($inputString, 'UTF-8', true) === false) {
            $inputString = utf8_encode($inputString);
        }
        return $inputString;
    }

    //dump the contents of a variable to the error log in a readable format
    public function var_error_log($object = null): void
    {
        ob_start();
        var_dump($object);
        error_log(ob_get_clean());
    }

    public function addApiTracking($user, $records, $type, $testType, $url = null, $requestData = null, $responseData = null, $format = null, $facilityId = null)
    {
        $data = array(
            'requested_by'      => $user ?? 'vlsm-system',
            'requested_on'      => $this->getCurrentDateTime(),
            'number_of_records' => $records ?? 0,
            'request_type'      => $type ?? null,
            'test_type'         => $testType ?? null,
            'api_url'           => $url ?? null,
            'request_data'      => $requestData ?? null,
            'response_data'     => $responseData ?? null,
            'facility_id'       => $facilityId ?? null,
            'data_format'       => $format ?? null
        );
        return $this->db->insert("track_api_requests", $data);
    }
}
