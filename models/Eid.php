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
        $globalConfig = $general->getGlobalConfig();
        $systemConfig = $general->getSystemConfig();

        
        
        $remotePrefix = '';
        $sampleCodeKeyCol = 'sample_code_key';
        $sampleCodeCol = 'sample_code';
        if ($systemConfig['user_type'] == 'remoteuser') {
            $remotePrefix = 'R';
            $sampleCodeKeyCol = 'remote_sample_code_key';
            $sampleCodeCol = 'remote_sample_code';
        }
        $sampleColDateTimeArray = explode(" ", $sampleCollectionDate);
        $sampleCollectionDate = $general->dateFormat($sampleColDateTimeArray[0]);
        $sampleColDateArray = explode("-", $sampleCollectionDate);
        $samColDate = substr($sampleColDateArray[0], -2);
        $start_date = $sampleColDateArray[0] . '-01-01';
        $end_date = $sampleColDateArray[0] . '-12-31';
        $mnthYr = $samColDate[0];
        // Checking if eid_sample_code is empty then we set by default 'MMYY'
        $globalConfig['eid_sample_code'] = isset($globalConfig['eid_sample_code']) ? $globalConfig['eid_sample_code'] : 'MMYY';

        if ($globalConfig['eid_sample_code'] == 'MMYY') {
            $mnthYr = $sampleColDateArray[1] . $samColDate;
        } else if ($globalConfig['eid_sample_code'] == 'YY') {
            $mnthYr = $samColDate;
        }

        $autoFormatedString = $samColDate . $sampleColDateArray[1] . $sampleColDateArray[2];
        // If it is PNG form
        if ($globalConfig['vl_form'] == 5) {
            if (empty($provinceId)) {
                $provinceId = $general->getProvinceIDFromCode($provinceCode);
            }

            $remotePrefix = $remotePrefix . "R"; // PNG format has an additional R in prefix
            $svlQuery = 'SELECT ' . $sampleCodeKeyCol . ' FROM eid_form as vl WHERE DATE(vl.sample_collection_date) >= "' . $start_date . '" AND DATE(vl.sample_collection_date) <= "' . $end_date . '" AND province_id=' . $provinceId . ' ORDER BY ' . $sampleCodeKeyCol . ' DESC LIMIT 1';

            $svlResult = $this->db->rawQueryOne($svlQuery);

            //var_dump($svlResult);

            if (isset($svlResult[$sampleCodeKeyCol]) && $svlResult[$sampleCodeKeyCol] != '' && $svlResult[$sampleCodeKeyCol] != null) {
                $maxId = $svlResult[$sampleCodeKeyCol] + 1;
                $strparam = strlen($maxId);
                $zeros = (isset($globalConfig['eid_sample_code']) && trim($globalConfig['eid_sample_code']) == 'auto2') ? substr("0000", $strparam) : substr("000", $strparam);
                $maxId = $zeros . $maxId;

                //echo $maxId;die;
            } else {
                $maxId = (isset($globalConfig['eid_sample_code']) && trim($globalConfig['eid_sample_code']) == 'auto2') ? '0001' : '001';
            }
            // $sampleCode = $remotePrefix . "R" . date('y') . $provinceCode . "EID" . $maxId;
            // $j = 1;
            // do {
            //     $sQuery = "SELECT sample_code FROM eid_form as vl where sample_code='" . $sampleCode . "'";
            //     $svlResult = $this->db->query($sQuery);
            //     if (!$svlResult) {
            //         $maxId;
            //         break;
            //     } else {
            //         $x = $maxId + 1;
            //         $strparam = strlen($x);
            //         $zeros = (isset($globalConfig['sample_code']) && trim($globalConfig['sample_code']) == 'auto2') ? substr("0000", $strparam) : substr("000", $strparam);
            //         $maxId = $zeros . $x;
            //         $sampleCode = $remotePrefix . "R" . date('y') . $provinceCode . "EID" . $maxId;
            //     }
            // } while ($sampleCode);
        } else {
            $svlQuery = 'SELECT ' . $sampleCodeKeyCol . ' FROM eid_form as vl WHERE DATE(vl.sample_collection_date) >= "' . $start_date . '" AND DATE(vl.sample_collection_date) <= "' . $end_date . '" AND ' . $sampleCodeCol . '!="" ORDER BY ' . $sampleCodeKeyCol . ' DESC LIMIT 1';

            $svlResult = $this->db->query($svlQuery);
            if (isset($svlResult[0][$sampleCodeKeyCol]) && $svlResult[0][$sampleCodeKeyCol] != '' && $svlResult[0][$sampleCodeKeyCol] != null) {
                $maxId = $svlResult[0][$sampleCodeKeyCol] + 1;
                $strparam = strlen($maxId);
                $zeros = (isset($globalConfig['eid_sample_code']) && trim($globalConfig['eid_sample_code']) == 'auto2') ? substr("0000", $strparam) : substr("000", $strparam);
                $maxId = $zeros . $maxId;
            } else {
                $maxId = (isset($globalConfig['eid_sample_code']) && trim($globalConfig['eid_sample_code']) == 'auto2') ? '0001' : '001';
            }
            //$sampleCode = ($remotePrefix . $provinceCode . $autoFormatedString . $maxId);
            // do {
            //     $sQuery = "SELECT sample_code FROM eid_form as vl where sample_code='" . $sampleCode . "'";
            //     $svlResult = $this->db->query($sQuery);
            //     if (!$svlResult) {
            //         $maxId;
            //         break;
            //     } else {
            //         $x = $maxId + 1;
            //         $strparam = strlen($x);
            //         $zeros = (isset($globalConfig['eid_sample_code']) && trim($globalConfig['eid_sample_code']) == 'auto2') ? substr("0000", $strparam) : substr("000", $strparam);
            //         $maxId = $zeros . $x;
            //         $sampleCode = ($remotePrefix . $provinceCode . $autoFormatedString . $maxId);
            //     }
            // } while ($sampleCode);
        }


        $sCodeKey = (array('maxId' => $maxId, 'mnthYr' => $mnthYr, 'auto' => $autoFormatedString));


        //$autoFormatedString = $sCodeKey['autoFormatedString'];
        if ($globalConfig['eid_sample_code'] == 'auto') {
            $sCodeKey['sampleCode'] = ($remotePrefix . $provinceCode . $autoFormatedString . $sCodeKey['maxId']);
            $sCodeKey['sampleCodeInText'] = ($remotePrefix . $provinceCode . $autoFormatedString . $sCodeKey['maxId']);
            $sCodeKey['sampleCodeFormat'] = ($remotePrefix . $provinceCode . $autoFormatedString);
            $sCodeKey['sampleCodeKey'] = ($sCodeKey['maxId']);
        } else if ($globalConfig['eid_sample_code'] == 'auto2') {
            $sCodeKey['sampleCode'] = $remotePrefix . date('y', strtotime($sampleCollectionDate)) . $provinceCode . 'EID' . $sCodeKey['maxId'];
            $sCodeKey['sampleCodeInText'] = $remotePrefix . date('y', strtotime($sampleCollectionDate)) . $provinceCode . 'EID' . $sCodeKey['maxId'];
            $sCodeKey['sampleCodeFormat'] = $remotePrefix . $provinceCode . $autoFormatedString;
            $sCodeKey['sampleCodeKey'] = $sCodeKey['maxId'];
        } else if ($globalConfig['eid_sample_code'] == 'YY' || $globalConfig['eid_sample_code'] == 'MMYY') {
            $sCodeKey['sampleCode'] = $remotePrefix . $globalConfig['eid_sample_code_prefix'] . $sCodeKey['mnthYr'] . $sCodeKey['maxId'];
            $sCodeKey['sampleCodeInText'] = $remotePrefix . $globalConfig['eid_sample_code_prefix'] . $sCodeKey['mnthYr'] . $sCodeKey['maxId'];
            $sCodeKey['sampleCodeFormat'] = $remotePrefix . $globalConfig['eid_sample_code_prefix'] . $sCodeKey['mnthYr'];
            $sCodeKey['sampleCodeKey'] = ($sCodeKey['maxId']);
        }

        $checkQuery = "SELECT sample_code FROM " . $this->table . " where sample_code='" . $sCodeKey['sampleCode'] . "'";
        $checkResult = $this->db->rawQueryOne($checkQuery);
        if ($checkResult !== null) {
            $this->generateEIDSampleCode($provinceCode, $sampleCollectionDate, $sampleFrom, $provinceId);
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
