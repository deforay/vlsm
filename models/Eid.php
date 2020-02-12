<?php
session_start();

require_once(dirname(__FILE__) . "/../startup.php");
include_once(APPLICATION_PATH . '/models/General.php');

/**
 * General functions
 *
 * @author Amit
 */

class Model_Eid
{

    protected $db = null;
    protected $table = 'eid_form';

    public function __construct($db = null)
    {
        $this->db = $db;
    }

    public function generateEIDSampleCode($provinceCode, $sampleCollectionDate, $sampleFrom = null, $provinceId = '')
    {

        $general = new General($this->db);
        //global config
        $configQuery = "SELECT * from global_config";
        $configResult = $this->db->query($configQuery);
        $arr = array();
        // now we create an associative array so that we can easily create view variables
        for ($i = 0; $i < sizeof($configResult); $i++) {
            $arr[$configResult[$i]['name']] = $configResult[$i]['value'];
        }
        //system config
        $systemConfigQuery = "SELECT * from system_config";
        $systemConfigResult = $this->db->query($systemConfigQuery);
        $sarr = array();
        // now we create an associative array so that we can easily create view variables
        for ($i = 0; $i < sizeof($systemConfigResult); $i++) {
            $sarr[$systemConfigResult[$i]['name']] = $systemConfigResult[$i]['value'];
        }
        $rKey = '';
        $sampleCodeKey = 'sample_code_key';
        $sampleCode = 'sample_code';
        if ($sarr['user_type'] == 'remoteuser') {
            $rKey = 'R';
            $sampleCodeKey = 'remote_sample_code_key';
            $sampleCode = 'remote_sample_code';
        }
        $sampleColDateTimeArray = explode(" ", $sampleCollectionDate);
        $sampleCollectionDate = $general->dateFormat($sampleColDateTimeArray[0]);
        $sampleColDateArray = explode("-", $sampleCollectionDate);
        $samColDate = substr($sampleColDateArray[0], -2);
        $start_date = $sampleColDateArray[0] . '-01-01';
        $end_date = $sampleColDateArray[0] . '-12-31';
        $mnthYr = $samColDate[0];
        // Checking if eid_sample_code is empty then we set by default 'MMYY'
        $arr['eid_sample_code'] = isset($arr['eid_sample_code']) ? $arr['eid_sample_code'] : 'MMYY';
        
        if ($arr['eid_sample_code'] == 'MMYY') {
            $mnthYr = $sampleColDateArray[1] . $samColDate;
        } else if ($arr['eid_sample_code'] == 'YY') {
            $mnthYr = $samColDate;
        }

        $auto = $samColDate . $sampleColDateArray[1] . $sampleColDateArray[2];
        // If it is PNG form
        if ($arr['vl_form'] == 5) {
            if (empty($provinceId)) {
                $provinceId = $general->getProvinceIDFromCode($provinceCode);
            }
            $svlQuery = 'SELECT ' . $sampleCodeKey . ' FROM eid_form as vl WHERE DATE(vl.sample_collection_date) >= "' . $start_date . '" AND DATE(vl.sample_collection_date) <= "' . $end_date . '" AND province_id=' . $provinceId . ' ORDER BY ' . $sampleCodeKey . ' DESC LIMIT 1';

            $svlResult = $this->db->rawQueryOne($svlQuery);

            //var_dump($svlResult);

            if (isset($svlResult[$sampleCodeKey]) && $svlResult[$sampleCodeKey] != '' && $svlResult[$sampleCodeKey] != null) {
                $maxId = $svlResult[$sampleCodeKey] + 1;
                $strparam = strlen($maxId);
                $zeros = (isset($arr['sample_code']) && trim($arr['sample_code']) == 'auto2') ? substr("0000", $strparam) : substr("000", $strparam);
                $maxId = $zeros . $maxId;

                //echo $maxId;die;
            } else {
                $maxId = (isset($arr['sample_code']) && trim($arr['sample_code']) == 'auto2') ? '0001' : '001';
            }
            $sCode = $rKey . "R" . date('y') . $provinceCode . "EID" . $maxId;
            $j = 1;
            do {
                $sQuery = "select sample_code from eid_form as vl where sample_code='" . $sCode . "'";
                $svlResult = $this->db->query($sQuery);
                if (!$svlResult) {
                    $maxId;
                    break;
                } else {
                    $x = $maxId + 1;
                    $strparam = strlen($x);
                    $zeros = (isset($arr['sample_code']) && trim($arr['sample_code']) == 'auto2') ? substr("0000", $strparam) : substr("000", $strparam);
                    $maxId = $zeros . $x;
                    $sCode = $rKey . "R" . date('y') . $provinceCode . "EID" . $maxId;
                }
            } while ($sCode);
        } else {
            $svlQuery = 'SELECT ' . $sampleCodeKey . ' FROM eid_form as vl WHERE DATE(vl.sample_collection_date) >= "' . $start_date . '" AND DATE(vl.sample_collection_date) <= "' . $end_date . '" AND ' . $sampleCode . '!="" ORDER BY ' . $sampleCodeKey . ' DESC LIMIT 1';

            $svlResult = $this->db->query($svlQuery);
            if (isset($svlResult[0][$sampleCodeKey]) && $svlResult[0][$sampleCodeKey] != '' && $svlResult[0][$sampleCodeKey] != null) {
                $maxId = $svlResult[0][$sampleCodeKey] + 1;
                $strparam = strlen($maxId);
                $zeros = (isset($arr['sample_code']) && trim($arr['sample_code']) == 'auto2') ? substr("0000", $strparam) : substr("000", $strparam);
                $maxId = $zeros . $maxId;
            } else {
                $maxId = (isset($arr['sample_code']) && trim($arr['sample_code']) == 'auto2') ? '0001' : '001';
            }
        }


        $sCodeKey = (array('maxId' => $maxId, 'mnthYr' => $mnthYr, 'auto' => $auto));


        $sCode = $sCodeKey['auto'];
        if ($arr['eid_sample_code'] == 'auto') {
            //$pNameVal = explode("##", $provinceCode);
            $sCodeKey['sampleCode'] = ($rKey . $provinceCode . $sCode . $sCodeKey['maxId']);
            $sCodeKey['sampleCodeInText'] = ($rKey . $provinceCode . $sCode . $sCodeKey['maxId']);
            $sCodeKey['sampleCodeFormat'] = ($rKey . $provinceCode . $sCode);
            $sCodeKey['sampleCodeKey'] = ($sCodeKey['maxId']);
        } else if ($arr['eid_sample_code'] == 'auto2') {
            $sCodeKey['sampleCode'] = $rKey . 'R' . date('y', strtotime($sampleCollectionDate)) . $provinceCode . 'EID' . $sCodeKey['maxId'];
            $sCodeKey['sampleCodeInText'] = $rKey . 'R' . date('y', strtotime($sampleCollectionDate)) . $provinceCode . 'EID' . $sCodeKey['maxId'];
            $sCodeKey['sampleCodeFormat'] = $rKey . $provinceCode . $sCode;
            $sCodeKey['sampleCodeKey'] = $sCodeKey['maxId'];
        } else if ($arr['eid_sample_code'] == 'YY' || $arr['eid_sample_code'] == 'MMYY') {
            $sCodeKey['sampleCode'] = $rKey . $arr['eid_sample_code_prefix'] . $sCodeKey['mnthYr'] . $sCodeKey['maxId'];
            $sCodeKey['sampleCodeInText'] = $rKey . $arr['eid_sample_code_prefix'] . $sCodeKey['mnthYr'] . $sCodeKey['maxId'];
            $sCodeKey['sampleCodeFormat'] = $rKey . $arr['eid_sample_code_prefix'] . $sCodeKey['mnthYr'];
            $sCodeKey['sampleCodeKey'] = ($sCodeKey['maxId']);
        }

        return json_encode($sCodeKey);
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
}
