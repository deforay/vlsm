<?php
/**
 * General functions
 *
 * @author Amit
 */
class General {

    protected $db = null;

    public function __construct($db = null){
        $this->db = $db;
    }

    public static function generateRandomString($length = 8, $seeds = 'alphanum') {
        // Possible seeds
        $seedings['alpha'] = 'abcdefghijklmnopqrstuvwqyz';
        $seedings['numeric'] = '0123456789';
        $seedings['alphanum'] = 'abcdefghijklmnopqrstuvwqyz0123456789';
        $seedings['hexidec'] = '0123456789abcdef';

        // Choose seed
        if (isset($seedings[$seeds])) {
            $seeds = $seedings[$seeds];
        }

        // Seed generator
        list($usec, $sec) = explode(' ', microtime());
        $seed = (float) $sec + ((float) $usec * 100000);
        mt_srand($seed);

        // Generate
        $str = '';
        $seeds_count = strlen($seeds);

        for ($i = 0; $length > $i; $i++) {
            $str .= $seeds{mt_rand(0, $seeds_count - 1)};
        }

        return $str;
    }

    /**
     * Used to format date from dd-mmm-yyyy to yyyy-mm-dd for storing in database
     *
    */
    public function dateFormat($date) {
        if (!isset($date) || $date == null || $date == "" || $date == "0000-00-00") {
            return "0000-00-00";
        } else {
            $dateArray = explode('-', $date);
            if(sizeof($dateArray) == 0 ){
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

    public function humanDateFormat($date) {

        if ($date == null || $date == "" || $date == "0000-00-00" || substr($date, 0, strlen("0000-00-00")) === "0000-00-00") {
            return "";
        } else {
            $dateArray = explode('-', $date);
            $newDate = $dateArray[2] . "-";

            $monthsArray = array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
            $mon = $monthsArray[$dateArray[1] - 1];

            return $newDate .= $mon . "-" . $dateArray[0];
        }
    }

    public function getZendDateFormat($date) {

        if ($date == null || $date == "" || $date == "0000-00-00") {
            return "";
        } else {
            $dateArray = explode('-', $date);

            $newDate = new Zend_date(array('year' => $dateArray[0], 'month' => $dateArray[1], 'day' => $dateArray[2]));

            return $newDate;
        }
    }

    public static function getDateTime() {
        $date = new DateTime(date('Y-m-d H:i:s'));
        return $date->format('Y-m-d H:i:s');
    }

    function removeDirectory($dirname) {
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
    public function getSystemConfig($name = null){

        if($this->db == null) return false;

        if($name == null){
            $systemConfigQuery ="SELECT * from system_config";
        }else{
            $systemConfigQuery ="SELECT * from system_config WHERE `name` = '$name'";
        }

        $systemConfigResult=$this->db->query($systemConfigQuery);
        $sarr = array();
        // now we create an associative array so that we can easily create view variables
        for ($i = 0; $i < sizeof($systemConfigResult); $i++) {
          $sarr[$systemConfigResult[$i]['name']] = $systemConfigResult[$i]['value'];
        }

        if($name == null){
            return $sarr;
        }else{
            if(isset($sarr[$name])){
                return $sarr[$name];
            }else{
                return null;
            }
        }
    }

    // get data from the global_config table from database
    public function getGlobalConfig($name = null){

        if($this->db == null) return false;

        if($name == null){
            $globalConfigQuery ="SELECT * from global_config";
        }else{
            $globalConfigQuery ="SELECT * from global_config WHERE `name` = '$name'";
        }

        $globalConfigResult=$this->db->query($globalConfigQuery);
        $garr = array();
        // now we create an associative array so that we can easily create view variables
        for ($i = 0; $i < sizeof($globalConfigResult); $i++) {
            $garr[$globalConfigResult[$i]['name']] = $globalConfigResult[$i]['value'];
        }

        if($name == null){
            return $garr;
        }else{
            if(isset($garr[$name])){
                return $garr[$name];
            }else{
                return null;
            }

        }
    }
    public function checkMandatoryField($field)
    {
        foreach($field as $chkField){
            if($chkField==''){
                return true;
            }
        }
    }

    function crypto($action, $inputString, $secretIv) {
        if (empty($inputString)) return "";

        $output = false;
        $encrypt_method = "AES-256-CBC";
        $secret_key = 'rXBCNkAzkHXGBKEReqrTfPhGDqhzxgDRQ7Q0XqN6BVvuJjh1OBVvuHXGBKEReqrTfPhGDqhzxgDJjh1OB4QcIGAGaml';

        // hash
        $key = hash('sha256', $secret_key);

          if(empty($secretIv)){
               $secretIv = 'sd893urijsdf8w9eurj';
          }
        // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
        $iv = substr(hash('sha256', $secretIv), 0, 16);

        if ( $action == 'encrypt' ) {
            $output = openssl_encrypt($inputString, $encrypt_method, $key, 0, $iv);
            $output = base64_encode($output);
        } else if( $action == 'decrypt' ) {
            $output = openssl_decrypt(base64_decode($inputString), $encrypt_method, $key, 0, $iv);

        }
        return $output;
    }

}
