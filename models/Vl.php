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

        if ($globalConfig['sample_code'] == 'MMYY') {
            $mnthYr = $sampleColDateArray[1] . $samColDate;
        } else if ($globalConfig['sample_code'] == 'YY') {
            $mnthYr = $samColDate;
        }

        $auto = $samColDate . $sampleColDateArray[1] . $sampleColDateArray[2];

        // If it is PNG form
        if ($globalConfig['vl_form'] == 5) {
            if (empty($provinceId) && !empty($provinceCode)) {
                $provinceId = $general->getProvinceIDFromCode($provinceCode);
            }

            // PNG format has an additional R in prefix
            $remotePrefix = $remotePrefix . "R";

            if (!empty($provinceId)) {
                $this->db->where('province_id', $provinceId);
            }
        }

        $this->db->where('DATE(sample_collection_date)', array($start_date, $end_date), 'BETWEEN');
        $this->db->where($sampleCodeCol, NULL, 'IS NOT');
        $this->db->orderBy($sampleCodeKeyCol, "DESC");
        $svlResult = $this->db->getOne($this->table, array($sampleCodeKeyCol));

        //var_dump($svlResult);die;


        if (isset($svlResult[$sampleCodeKeyCol]) && $svlResult[$sampleCodeKeyCol] != '' && $svlResult[$sampleCodeKeyCol] != null) {
            $maxId = $svlResult[$sampleCodeKeyCol] + 1;
            $strparam = strlen($maxId);
            $zeros = (isset($globalConfig['sample_code']) && trim($globalConfig['sample_code']) == 'auto2') ? substr("0000", $strparam) : substr("000", $strparam);
            $maxId = $zeros . $maxId;
        } else {
            $maxId = (isset($globalConfig['sample_code']) && trim($globalConfig['sample_code']) == 'auto2') ? '0001' : '001';
        }

        //echo $svlQuery;die;

        $sCodeKey = (array('maxId' => $maxId, 'mnthYr' => $mnthYr, 'auto' => $auto));

        $sCode = $sCodeKey['auto'];
        if ($globalConfig['sample_code'] == 'auto') {
            //$pNameVal = explode("##", $provinceCode);
            $sCodeKey['sampleCode'] = ($remotePrefix . $provinceCode . $sCode . $sCodeKey['maxId']);
            $sCodeKey['sampleCodeInText'] = ($remotePrefix . $provinceCode . $sCode . $sCodeKey['maxId']);
            $sCodeKey['sampleCodeFormat'] = ($remotePrefix . $provinceCode . $sCode);
            $sCodeKey['sampleCodeKey'] = ($sCodeKey['maxId']);
        } else if ($globalConfig['sample_code'] == 'auto2') {
            $sCodeKey['sampleCode'] = $remotePrefix . date('y', strtotime($sampleCollectionDate)) . $provinceCode . 'VL' . $sCodeKey['maxId'];
            $sCodeKey['sampleCodeInText'] = $remotePrefix . date('y', strtotime($sampleCollectionDate)) . $provinceCode . 'VL' . $sCodeKey['maxId'];
            $sCodeKey['sampleCodeFormat'] = $remotePrefix . $provinceCode . $sCode;
            $sCodeKey['sampleCodeKey'] = $sCodeKey['maxId'];
        } else if ($globalConfig['sample_code'] == 'YY' || $globalConfig['sample_code'] == 'MMYY') {
            $sCodeKey['sampleCode'] = $remotePrefix . $globalConfig['sample_code_prefix'] . $sCodeKey['mnthYr'] . $sCodeKey['maxId'];
            $sCodeKey['sampleCodeInText'] = $remotePrefix . $globalConfig['sample_code_prefix'] . $sCodeKey['mnthYr'] . $sCodeKey['maxId'];
            $sCodeKey['sampleCodeFormat'] = $remotePrefix . $globalConfig['sample_code_prefix'] . $sCodeKey['mnthYr'];
            $sCodeKey['sampleCodeKey'] = ($sCodeKey['maxId']);
        }

        $checkQuery = "SELECT sample_code FROM " . $this->table . " where sample_code='" . $sCodeKey['sampleCode'] . "'";
        $checkResult = $this->db->rawQueryOne($checkQuery);
        if ($checkResult !== null) {
            $this->generateVLSampleID($provinceCode, $sampleCollectionDate, $sampleFrom, $provinceId);
        }

        return json_encode($sCodeKey);
    }
}
