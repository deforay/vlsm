<?php
require_once(dirname(__FILE__) . "/../startup.php");
include_once(APPLICATION_PATH . '/models/General.php');

/**
 * General functions
 *
 * @author Amit
 */

class Model_Covid19
{

    protected $db = null;
    protected $table = 'form_covid19';

    public function __construct($db = null)
    {
        $this->db = $db;
    }

    public function generateCovid19SampleCode($provinceCode, $sampleCollectionDate, $sampleFrom = null, $provinceId = '')
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
        // Checking if covid19_sample_code is empty then we set by default 'MMYY'
        $globalConfig['covid19_sample_code'] = isset($globalConfig['covid19_sample_code']) ? $globalConfig['covid19_sample_code'] : 'MMYY';

        if ($globalConfig['covid19_sample_code'] == 'MMYY') {
            $mnthYr = $sampleColDateArray[1] . $samColDate;
        } else if ($globalConfig['covid19_sample_code'] == 'YY') {
            $mnthYr = $samColDate;
        }

        $autoFormatedString = $samColDate . $sampleColDateArray[1] . $sampleColDateArray[2];
        // If it is PNG form then we check province-wise
        if ($globalConfig['vl_form'] == 5) {
            if (empty($provinceId)) {
                $provinceId = $general->getProvinceIDFromCode($provinceCode);
            }
            $remotePrefix = $remotePrefix . "R"; // PNG format has an additional R in prefix
            $svlQuery = 'SELECT ' . $sampleCodeKeyCol . ' FROM form_covid19 as c19 WHERE DATE(c19.sample_collection_date) >= "' . $start_date . '" AND DATE(c19.sample_collection_date) <= "' . $end_date . '" AND province_id=' . $provinceId . ' ORDER BY ' . $sampleCodeKeyCol . ' DESC LIMIT 1';

            $svlResult = $this->db->rawQueryOne($svlQuery);

            //var_dump($svlResult);

            if (isset($svlResult[$sampleCodeKeyCol]) && $svlResult[$sampleCodeKeyCol] != '' && $svlResult[$sampleCodeKeyCol] != null) {
                $maxId = $svlResult[$sampleCodeKeyCol] + 1;
                $strparam = strlen($maxId);
                $zeros = (isset($globalConfig['covid19_sample_code']) && trim($globalConfig['covid19_sample_code']) == 'auto2') ? substr("0000", $strparam) : substr("000", $strparam);
                $maxId = $zeros . $maxId;

                //echo $maxId;die;
            } else {
                $maxId = (isset($globalConfig['covid19_sample_code']) && trim($globalConfig['covid19_sample_code']) == 'auto2') ? '0001' : '001';
            }
            // $sampleCode = $remotePrefix . "R" . date('y') . $provinceCode . "C19" . $maxId;
            // $j = 1;
            // do {
            //     $sQuery = "SELECT sample_code FROM form_covid19 AS c19 WHERE sample_code='" . $sampleCode . "'";
            //     $svlResult = $this->db->query($sQuery);
            //     if (!$svlResult) {
            //         $maxId;
            //         break;
            //     } else {
            //         $x = $maxId + 1;
            //         $strparam = strlen($x);
            //         $zeros = (isset($globalConfig['sample_code']) && trim($globalConfig['sample_code']) == 'auto2') ? substr("0000", $strparam) : substr("000", $strparam);
            //         $maxId = $zeros . $x;
            //         $sampleCode = $remotePrefix . "R" . date('y') . $provinceCode . "C19" . $maxId;
            //     }
            // } while ($sampleCode);
        } else {
            $svlQuery = 'SELECT ' . $sampleCodeKeyCol . ' FROM form_covid19 as c19 WHERE DATE(c19.sample_collection_date) >= "' . $start_date . '" AND DATE(c19.sample_collection_date) <= "' . $end_date . '" AND ' . $sampleCodeCol . '!="" ORDER BY ' . $sampleCodeKeyCol . ' DESC LIMIT 1';

            $svlResult = $this->db->query($svlQuery);
            if (isset($svlResult[0][$sampleCodeKeyCol]) && $svlResult[0][$sampleCodeKeyCol] != '' && $svlResult[0][$sampleCodeKeyCol] != null) {
                $maxId = $svlResult[0][$sampleCodeKeyCol] + 1;
                $strparam = strlen($maxId);
                $zeros = (isset($globalConfig['covid19_sample_code']) && trim($globalConfig['covid19_sample_code']) == 'auto2') ? substr("0000", $strparam) : substr("000", $strparam);
                $maxId = $zeros . $maxId;
            } else {
                $maxId = (isset($globalConfig['covid19_sample_code']) && trim($globalConfig['covid19_sample_code']) == 'auto2') ? '0001' : '001';
            }
        }


        $sCodeKey = (array('maxId' => $maxId, 'mnthYr' => $mnthYr, 'auto' => $autoFormatedString));


        //$autoFormatedString = $sCodeKey['autoFormatedString'];
        if ($globalConfig['covid19_sample_code'] == 'auto') {
            $sCodeKey['sampleCode'] = ($remotePrefix . $provinceCode . $autoFormatedString . $sCodeKey['maxId']);
            $sCodeKey['sampleCodeInText'] = ($remotePrefix . $provinceCode . $autoFormatedString . $sCodeKey['maxId']);
            $sCodeKey['sampleCodeFormat'] = ($remotePrefix . $provinceCode . $autoFormatedString);
            $sCodeKey['sampleCodeKey'] = ($sCodeKey['maxId']);
        } else if ($globalConfig['covid19_sample_code'] == 'auto2') {
            $sCodeKey['sampleCode'] = $remotePrefix . date('y', strtotime($sampleCollectionDate)) . $provinceCode . 'C19' . $sCodeKey['maxId'];
            $sCodeKey['sampleCodeInText'] = $remotePrefix . date('y', strtotime($sampleCollectionDate)) . $provinceCode . 'C19' . $sCodeKey['maxId'];
            $sCodeKey['sampleCodeFormat'] = $remotePrefix . $provinceCode . $autoFormatedString;
            $sCodeKey['sampleCodeKey'] = $sCodeKey['maxId'];
        } else if ($globalConfig['covid19_sample_code'] == 'YY' || $globalConfig['covid19_sample_code'] == 'MMYY') {
            $sCodeKey['sampleCode'] = $remotePrefix . $globalConfig['covid19_sample_code_prefix'] . $sCodeKey['mnthYr'] . $sCodeKey['maxId'];
            $sCodeKey['sampleCodeInText'] = $remotePrefix . $globalConfig['covid19_sample_code_prefix'] . $sCodeKey['mnthYr'] . $sCodeKey['maxId'];
            $sCodeKey['sampleCodeFormat'] = $remotePrefix . $globalConfig['covid19_sample_code_prefix'] . $sCodeKey['mnthYr'];
            $sCodeKey['sampleCodeKey'] = ($sCodeKey['maxId']);
        }

        $checkQuery = "SELECT sample_code FROM " . $this->table . " where sample_code='" . $sCodeKey['sampleCode'] . "'";
        $checkResult = $this->db->rawQueryOne($checkQuery);
        if ($checkResult !== null) {
            $this->generateCovid19SampleCode($provinceCode, $sampleCollectionDate, $sampleFrom, $provinceId);
        }

        return json_encode($sCodeKey);
    }


    public function getCovid19SampleTypes()
    {
        $results = $this->db->rawQuery("SELECT * FROM r_covid19_sample_type where status='active'");
        $response = array();
        foreach ($results as $row) {
            $response[$row['sample_id']] = $row['sample_name'];
        }
        return $response;
    }

    public function insertCovid19Tests($covid19SampleId, $testKitName = null, $labId = null, $sampleTestedDatetime = null, $result = null)
    {
        $covid19TestData = array(
            'covid19_id'            => $covid19SampleId,
            'test_name'                => $testKitName,
            'facility_id'           => $labId,
            'sample_tested_datetime' => $sampleTestedDatetime,
            'result'                => $result
        );
        return $this->db->insert("covid19_tests", $covid19TestData);
    }

    public function checkAllCovid19TestsForPositive($covid19SampleId)
    {
        $response = $this->db->rawQuery("SELECT * FROM covid19_tests WHERE `covid19_id` = $covid19SampleId ORDER BY test_id ASC");

        foreach ($response as $row) {
            if ($row['result'] == 'positive') return true;
        }

        return false;
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

    public function getCovid19ReasonsForTesting()
    {
        $results = $this->db->rawQuery("SELECT * FROM r_covid19_test_reasons WHERE `test_reason_status` LIKE 'active'");
        $response = array();
        foreach ($results as $row) {
            $response[$row['test_reason_id']] = $row['test_reason_name'];
        }
        return $response;
    }
    
    public function getCovid19ReasonsForTestingDRC()
    {
        $results = $this->db->rawQuery("SELECT * FROM r_covid19_test_reasons WHERE `test_reason_status` LIKE 'active' AND (parent_reason IS NULL OR parent_reason = 0)");
        $response = array();
        foreach ($results as $row) {
            $response[$row['test_reason_id']] = $row['test_reason_name'];
        }
        return $response;
    }
    public function getCovid19Symptoms()
    {
        $results = $this->db->rawQuery("SELECT * FROM r_covid19_symptoms WHERE `symptom_status` LIKE 'active'");
        $response = array();
        foreach ($results as $row) {
            $response[$row['symptom_id']] = $row['symptom_name'];
        }
        return $response;
    }
    
    public function getCovid19SymptomsDRC()
    {
        $results = $this->db->rawQuery("SELECT * FROM r_covid19_symptoms WHERE `symptom_status` LIKE 'active' AND (parent_symptom IS NULL OR parent_symptom = 0)");
        $response = array();
        foreach ($results as $row) {
            $response[$row['symptom_id']] = $row['symptom_name'];
        }
        return $response;
    }

    public function getCovid19Comorbidities()
    {
        $results = $this->db->rawQuery("SELECT * FROM r_covid19_comorbidities WHERE `comorbidity_status` LIKE 'active'");
        $response = array();
        foreach ($results as $row) {
            $response[$row['comorbidity_id']] = $row['comorbidity_name'];
        }
        return $response;
    }


    public function getCovid19TestsByFormId($formId)
    {
        if (empty($formId)) {
            return null;
        }

        $response = array();

        // Using this in sync requests/results
        if (is_array($formId)) {
            $results = $this->db->rawQuery("SELECT * FROM covid19_tests WHERE `covid19_id` IN (" . implode(",", $formId) . ") ORDER BY test_id ASC");

            foreach ($results as $row) {
                $response[$row['covid19_id']][$row['test_id']] = $row;
            }
        } else {
            $response = $this->db->rawQuery("SELECT * FROM covid19_tests WHERE `covid19_id` = $formId ORDER BY test_id ASC");
        }

        return $response;
    }
    public function getCovid19SymptomsByFormId($formId)
    {
        if (empty($formId)) {
            return null;
        }

        $response = array();

        // Using this in sync requests/results
        if (is_array($formId)) {
            $results = $this->db->rawQuery("SELECT * FROM covid19_patient_symptoms WHERE `covid19_id` IN (" . implode(",", $formId) . ")");

            foreach ($results as $row) {
                $response[$row['covid19_id']][$row['symptom_id']] = $row['symptom_detected'];
            }
        } else {
            $results = $this->db->rawQuery("SELECT * FROM covid19_patient_symptoms WHERE `covid19_id` = $formId");

            foreach ($results as $row) {
                $response[$row['symptom_id']] = $row['symptom_detected'];
            }
        }

        return $response;
    }


    public function getCovid19ComorbiditiesByFormId($formId)
    {
        if (empty($formId)) {
            return null;
        }

        $response = array();

        // Using this in sync requests/results
        if (is_array($formId)) {

            $results = $this->db->rawQuery("SELECT * FROM covid19_patient_comorbidities WHERE `covid19_id` IN (" . implode(",", $formId) . ")");

            foreach ($results as $row) {
                $response[$row['covid19_id']][$row['comorbidity_id']] = $row['comorbidity_detected'];
            }
        } else {

            $results = $this->db->rawQuery("SELECT * FROM covid19_patient_comorbidities WHERE `covid19_id` = $formId");

            foreach ($results as $row) {
                $response[$row['comorbidity_id']] = $row['comorbidity_detected'];
            }
        }


        return $response;
    }
}
