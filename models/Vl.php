<?php
require_once(dirname(__FILE__) . "/../startup.php");
include_once(APPLICATION_PATH . '/models/General.php');


/**
 * General functions
 *
 * @author Amit
 */

class Model_Vl
{

    protected $db = null;
    protected $table = 'vl_request_form';

    public function __construct($db = null)
    {
        $this->db = $db;
    }

    public function generateVLSampleID($provinceCode, $sampleCollectionDate, $sampleFrom = null, $provinceId = '')
    {

        $general = new General($this->db);
        $globalConfig = $general->getGlobalConfig();
        $systemConfig = $general->getSystemConfig();
        $sampleID = '';


        $rKey = '';
        $sampleCodeKey = 'sample_code_key';
        $sampleCode = 'sample_code';
        if ($systemConfig['user_type'] == 'remoteuser') {
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

        if ($globalConfig['sample_code'] == 'MMYY') {
            $mnthYr = $sampleColDateArray[1] . $samColDate;
        } else if ($globalConfig['sample_code'] == 'YY') {
            $mnthYr = $samColDate;
        }

        $auto = $samColDate . $sampleColDateArray[1] . $sampleColDateArray[2];
        if (isset($sampleFrom) && $sampleFrom != null) {
            if(empty($provinceId)){
                $provinceId = $general->getProvinceIDFromCode($provinceCode);
            }
            
            $svlQuery = 'SELECT ' . $sampleCodeKey . ' FROM vl_request_form as vl WHERE DATE(vl.sample_collection_date) >= "' . $start_date . '" AND DATE(vl.sample_collection_date) <= "' . $end_date . '" AND province_id=' . $provinceId . ' AND ' . $sampleCode . ' IS NOT NULL AND ' . $sampleCode . '!= "" ORDER BY ' . $sampleCodeKey . ' DESC LIMIT 1';

            $svlResult = $this->db->query($svlQuery);

            if (isset($svlResult[0][$sampleCodeKey]) && $svlResult[0][$sampleCodeKey] != '' && $svlResult[0][$sampleCodeKey] != null) {
                $maxId = $svlResult[0][$sampleCodeKey] + 1;
                $strparam = strlen($maxId);
                $zeros = (isset($globalConfig['sample_code']) && trim($globalConfig['sample_code']) == 'auto2') ? substr("0000", $strparam) : substr("000", $strparam);
                $maxId = $zeros . $maxId;
            } else {
                $maxId = (isset($globalConfig['sample_code']) && trim($globalConfig['sample_code']) == 'auto2') ? '0001' : '001';
            }
            $sCode = $rKey . "R" . date('y') . $provinceCode . "VL" . $maxId;
            $j = 1;
            do {
                $sQuery = "SELECT sample_code FROM vl_request_form as vl where sample_code='" . $sCode . "'";
                $svlResult = $this->db->query($sQuery);
                if (!$svlResult) {
                    $maxId;
                    break;
                } else {
                    $x = $maxId + 1;
                    $strparam = strlen($x);
                    $zeros = (isset($globalConfig['sample_code']) && trim($globalConfig['sample_code']) == 'auto2') ? substr("0000", $strparam) : substr("000", $strparam);
                    $maxId = $zeros . $x;
                    $sCode = $rKey . "R" . date('y') . $provinceCode . "VL" . $maxId;
                }
            } while ($sCode);
        } else {

            $svlQuery = "SELECT $sampleCodeKey FROM vl_request_form AS vl WHERE DATE(vl.sample_collection_date) >= '" . $start_date . "' AND DATE(vl.sample_collection_date) <= '" . $end_date . "' AND $sampleCode !='' ORDER BY $sampleCodeKey DESC LIMIT 1";

            $svlResult = $this->db->query($svlQuery);
            if (isset($svlResult[0][$sampleCodeKey]) && $svlResult[0][$sampleCodeKey] != '' && $svlResult[0][$sampleCodeKey] != null) {
                $maxId = $svlResult[0][$sampleCodeKey] + 1;
                $strparam = strlen($maxId);
                $zeros = (isset($globalConfig['sample_code']) && trim($globalConfig['sample_code']) == 'auto2') ? substr("0000", $strparam) : substr("000", $strparam);
                $maxId = $zeros . $maxId;
            } else {
                $maxId = (isset($globalConfig['sample_code']) && trim($globalConfig['sample_code']) == 'auto2') ? '0001' : '001';
            }
        }

        

        //echo $svlQuery;die;

        $sCodeKey = (array('maxId' => $maxId, 'mnthYr' => $mnthYr, 'auto' => $auto));

        $sCode = $sCodeKey['auto'];
        if ($globalConfig['sample_code'] == 'auto') {
            //$pNameVal = explode("##", $provinceCode);
            $sCodeKey['sampleCode'] = ($rKey . $provinceCode . $sCode . $sCodeKey['maxId']);
            $sCodeKey['sampleCodeInText'] = ($rKey . $provinceCode . $sCode . $sCodeKey['maxId']);
            $sCodeKey['sampleCodeFormat'] = ($rKey . $provinceCode . $sCode);
            $sCodeKey['sampleCodeKey'] = ($sCodeKey['maxId']);
        } else if ($globalConfig['sample_code'] == 'auto2') {
            $sCodeKey['sampleCode'] = $rKey . 'R' . date('y', strtotime($sampleCollectionDate)) . $provinceCode . 'VL' . $sCodeKey['maxId'];
            $sCodeKey['sampleCodeInText'] = $rKey . 'R' . date('y', strtotime($sampleCollectionDate)) . $provinceCode . 'VL' . $sCodeKey['maxId'];
            $sCodeKey['sampleCodeFormat'] = $rKey . $provinceCode . $sCode;
            $sCodeKey['sampleCodeKey'] = $sCodeKey['maxId'];
        } else if ($globalConfig['sample_code'] == 'YY' || $globalConfig['sample_code'] == 'MMYY') {
            $sCodeKey['sampleCode'] = $rKey . $globalConfig['sample_code_prefix'] . $sCodeKey['mnthYr'] . $sCodeKey['maxId'];
            $sCodeKey['sampleCodeInText'] = $rKey . $globalConfig['sample_code_prefix'] . $sCodeKey['mnthYr'] . $sCodeKey['maxId'];
            $sCodeKey['sampleCodeFormat'] = $rKey . $globalConfig['sample_code_prefix'] . $sCodeKey['mnthYr'];
            $sCodeKey['sampleCodeKey'] = ($sCodeKey['maxId']);
        }

        return json_encode($sCodeKey);
    }
}
