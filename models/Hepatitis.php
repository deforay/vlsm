<?php

namespace Vlsm\Models;

/**
 * General functions
 *
 * @author Amit
 */

class Hepatitis
{

    protected $db = null;
    protected $table = 'form_hepatitis';
    protected $shortCode = 'H';
    public $suppressedArray = array(
        'target not detected',
        'tnd',
        'not detected',
        'below detection limit',
        'below detection level',
        'bdl',
        'suppressed',
        '< 20',
        '<20',
        '< 40',
        '<40',
        '< 839',
        '<839',
        '< Titer min',
        'negative',
        'negat'
    );

    public function __construct($db = null)
    {
        $this->db = !empty($db) ? $db : \MysqliDb::getInstance();
    }

    public function generateHepatitisSampleCode($prefix, $provinceCode, $sampleCollectionDate, $sampleFrom = null, $provinceId = '', $maxCodeKeyVal = null)
    {

        $general = new \Vlsm\Models\General($this->db);

        $globalConfig = $general->getGlobalConfig();
        $vlsmSystemConfig = $general->getSystemConfig();
        $sampleID = '';


        $remotePrefix = '';
        $sampleCodeKeyCol = 'sample_code_key';
        $sampleCodeCol = 'sample_code';
        if ($vlsmSystemConfig['sc_user_type'] == 'remoteuser') {
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
        // Checking if sample code format is empty then we set by default 'MMYY'
        $sampleCodeFormat = isset($globalConfig['hepatitis_sample_code']) ? $globalConfig['hepatitis_sample_code'] : 'MMYY';
        $prefixFromConfig = isset($globalConfig['hepatitis_sample_code_prefix']) ? $globalConfig['hepatitis_sample_code_prefix'] : '';

        if ($sampleCodeFormat == 'MMYY') {
            $mnthYr = $sampleColDateArray[1] . $samColDate;
        } else if ($sampleCodeFormat == 'YY') {
            $mnthYr = $samColDate;
        }

        $autoFormatedString = $samColDate . $sampleColDateArray[1] . $sampleColDateArray[2];


        if ($maxCodeKeyVal == null) {
            // If it is PNG form
            if ($globalConfig['vl_form'] == 5) {

                if (empty($provinceId) && !empty($provinceCode)) {
                    $provinceId = $general->getProvinceIDFromCode($provinceCode);
                }

                if (!empty($provinceId)) {
                    $this->db->where('province_id', $provinceId);
                }
            }

            $this->db->where('DATE(sample_collection_date)', array($start_date, $end_date), 'BETWEEN');
            $this->db->where($sampleCodeCol, NULL, 'IS NOT');
            $this->db->orderBy($sampleCodeKeyCol, "DESC");
            $svlResult = $this->db->getOne($this->table, array($sampleCodeKeyCol));
            if ($svlResult) {
                $maxCodeKeyVal = $svlResult[$sampleCodeKeyCol];
            } else {
                $maxCodeKeyVal = null;
            }
        }


        if (!empty($maxCodeKeyVal)) {
            $maxId = $maxCodeKeyVal + 1;
            $strparam = strlen($maxId);
            $zeros = (isset($sampleCodeFormat) && trim($sampleCodeFormat) == 'auto2') ? substr("0000", $strparam) : substr("000", $strparam);
            $maxId = $zeros . $maxId;
        } else {
            $maxId = (isset($sampleCodeFormat) && trim($sampleCodeFormat) == 'auto2') ? '0001' : '001';
        }

        //error_log($maxCodeKeyVal);

        $sCodeKey = (array('maxId' => $maxId, 'mnthYr' => $mnthYr, 'auto' => $autoFormatedString));



        if ($globalConfig['vl_form'] == 5) {
            // PNG format has an additional R in prefix
            $remotePrefix = $remotePrefix . "R";
            //$sampleCodeFormat = 'auto2';
        }
        if (isset($prefix) && $prefix != "") {
            $prefixFromConfig = $prefix;
        }
        if ($sampleCodeFormat == 'auto') {
            //$pNameVal = explode("##", $provinceCode);
            $sCodeKey['sampleCode'] = ($remotePrefix . $prefixFromConfig . $provinceCode . $autoFormatedString . $sCodeKey['maxId']);
            $sCodeKey['sampleCodeInText'] = ($remotePrefix . $prefixFromConfig . $provinceCode . $autoFormatedString . $sCodeKey['maxId']);
            $sCodeKey['sampleCodeFormat'] = ($remotePrefix . $prefixFromConfig . $provinceCode . $autoFormatedString);
            $sCodeKey['sampleCodeKey'] = ($sCodeKey['maxId']);
        } else if ($sampleCodeFormat == 'auto2') {
            $sCodeKey['sampleCode'] = $remotePrefix . $prefixFromConfig . date('y', strtotime($sampleCollectionDate)) . $provinceCode . $this->shortCode . $sCodeKey['maxId'];
            $sCodeKey['sampleCodeInText'] = $remotePrefix . $prefixFromConfig . date('y', strtotime($sampleCollectionDate)) . $provinceCode . $this->shortCode . $sCodeKey['maxId'];
            $sCodeKey['sampleCodeFormat'] = $remotePrefix . $prefixFromConfig . $provinceCode . $autoFormatedString;
            $sCodeKey['sampleCodeKey'] = $sCodeKey['maxId'];
        } else if ($sampleCodeFormat == 'YY' || $sampleCodeFormat == 'MMYY') {
            $sCodeKey['sampleCode'] = $remotePrefix . $prefixFromConfig . $sCodeKey['mnthYr'] . $sCodeKey['maxId'];
            $sCodeKey['sampleCodeInText'] = $remotePrefix . $prefixFromConfig . $sCodeKey['mnthYr'] . $sCodeKey['maxId'];
            $sCodeKey['sampleCodeFormat'] = $remotePrefix . $prefixFromConfig . $sCodeKey['mnthYr'];
            $sCodeKey['sampleCodeKey'] = ($sCodeKey['maxId']);
        }

        $checkQuery = "SELECT $sampleCodeCol, $sampleCodeKeyCol FROM " . $this->table . " where $sampleCodeCol='" . $sCodeKey['sampleCode'] . "'";
        $checkResult = $this->db->rawQueryOne($checkQuery);
        if ($checkResult !== null) {
            return $this->generateHepatitisSampleCode($provinceCode, $sampleCollectionDate, $sampleFrom, $provinceId, $checkResult[$sampleCodeKeyCol]);
        }

        return json_encode($sCodeKey);
    }

    public function getComorbidityByHepatitisId($formId, $allData = false)
    {

        if (empty($formId)) {
            return null;
        }

        $response = array();
        // Using this in sync requests/results
        if (is_array($formId)) {

            $results = $this->db->rawQuery("SELECT * FROM hepatitis_patient_comorbidities WHERE `hepatitis_id` IN (" . implode(",", $formId) . ")");
            if ($allData) return $results;
            foreach ($results as $row) {
                $response[$row['hepatitis_id']][$row['comorbidity_id']] = $row['comorbidity_detected'];
            }
        } else {

            $results = $this->db->rawQuery("SELECT * FROM hepatitis_patient_comorbidities WHERE `hepatitis_id` = $formId");
            if ($allData) return $results;
            foreach ($results as $row) {
                $response[$row['comorbidity_id']] = $row['comorbidity_detected'];
            }
        }
        return $response;
    }

    public function getRiskFactorsByHepatitisId($formId, $allData = false)
    {

        if (empty($formId)) {
            return null;
        }

        $response = array();
        // Using this in sync requests/results
        if (is_array($formId)) {

            $results = $this->db->rawQuery("SELECT * FROM hepatitis_risk_factors WHERE `hepatitis_id` IN (" . implode(",", $formId) . ")");
            if ($allData) return $results;
            foreach ($results as $row) {
                $response[$row['hepatitis_id']][$row['riskfactors_id']] = $row['riskfactors_detected'];
            }
        } else {

            $results = $this->db->rawQuery("SELECT * FROM hepatitis_risk_factors WHERE `hepatitis_id` = $formId");
            if ($allData) return $results;
            foreach ($results as $row) {
                $response[$row['riskfactors_id']] = $row['riskfactors_detected'];
            }
        }
        return $response;
    }

    public function getHepatitisResults()
    {
        $results = $this->db->rawQuery("SELECT result_id,result FROM r_hepatitis_results where status='active' ORDER BY result_id DESC");
        $response = array();
        foreach ($results as $row) {
            $response[$row['result_id']] = $row['result'];
        }
        return $response;
    }

    public function getHepatitisSampleTypes()
    {
        $results = $this->db->rawQuery("SELECT * FROM r_hepatitis_sample_type where status='active'");
        $response = array();
        foreach ($results as $row) {
            $response[$row['sample_id']] = $row['sample_name'];
        }
        return $response;
    }    

    public function getHepatitisReasonsForTesting()
    {
        $results = $this->db->rawQuery("SELECT test_reason_id,test_reason_name FROM r_hepatitis_test_reasons WHERE `test_reason_status` LIKE 'active'");
        $response = array();
        foreach ($results as $row) {
            $response[$row['test_reason_id']] = $row['test_reason_name'];
        }
        return $response;
    }

    public function insertSampleCode($params)
    {
        $general = new \Vlsm\Models\General();

        $globalConfig = $general->getGlobalConfig();
        $vlsmSystemConfig = $general->getSystemConfig();

        try {
            $provinceCode = (isset($params['provinceCode']) && !empty($params['provinceCode'])) ? $params['provinceCode'] : null;
            $provinceId = (isset($params['provinceId']) && !empty($params['provinceId'])) ? $params['provinceId'] : null;
            $sampleCollectionDate = (isset($params['sampleCollectionDate']) && !empty($params['sampleCollectionDate'])) ? $params['sampleCollectionDate'] : null;
            $prefix = (isset($params['prefix']) && !empty($params['prefix'])) ? $params['prefix'] : null;

            if (empty($sampleCollectionDate)) {
                echo 0;
                exit();
            }

            // PNG FORM CANNOT HAVE PROVINCE EMPTY
            if ($globalConfig['vl_form'] == 5) {
                if (empty($provinceId)) {
                    echo 0;
                    exit();
                }
            }
            $sampleJson = $this->generateHepatitisSampleCode($prefix, $provinceCode, $sampleCollectionDate, null, $provinceId);
            $sampleData = json_decode($sampleJson, true);

            $sampleDate = explode(" ", $params['sampleCollectionDate']);
            $sampleCollectionDate = $general->dateFormat($sampleDate[0]) . " " . $sampleDate[1];

            if (!isset($params['countryId']) || empty($params['countryId'])) {
                $params['countryId'] = null;
            }

            $hepatitisData = array();
            $hepatitisData = array(
                'vlsm_country_id' => $params['countryId'],
                'sample_collection_date' => $sampleCollectionDate,
                'vlsm_instance_id' => $_SESSION['instanceId'],
                'hepatitis_test_type' => $prefix,
                'province_id' => $provinceId,
                'request_created_by' => $_SESSION['userId'],
                'request_created_datetime' => $this->db->now(),
                'last_modified_by' => $_SESSION['userId'],
                'last_modified_datetime' => $this->db->now()
            );

            if ($vlsmSystemConfig['sc_user_type'] == 'remoteuser') {
                $hepatitisData['remote_sample_code'] = $sampleData['sampleCode'];
                $hepatitisData['remote_sample_code_format'] = $sampleData['sampleCodeFormat'];
                $hepatitisData['remote_sample_code_key'] = $sampleData['sampleCodeKey'];
                $hepatitisData['remote_sample'] = 'yes';
                $hepatitisData['result_status'] = 9;
            } else {
                $hepatitisData['sample_code'] = $sampleData['sampleCode'];
                $hepatitisData['sample_code_format'] = $sampleData['sampleCodeFormat'];
                $hepatitisData['sample_code_key'] = $sampleData['sampleCodeKey'];
                $hepatitisData['remote_sample'] = 'no';
                $hepatitisData['result_status'] = 6;
            }
            $sQuery = "SELECT hepatitis_id, sample_code, sample_code_format, sample_code_key, remote_sample_code, remote_sample_code_format, remote_sample_code_key FROM form_hepatitis ";
            if (isset($sampleData['sampleCode']) && !empty($sampleData['sampleCode'])) {
                $sQuery .= " WHERE (sample_code like '" . $sampleData['sampleCode'] . "' OR remote_sample_code like '" . $sampleData['sampleCode'] . "')";
            }
            $sQuery .= " LIMIT 1";
            $rowData = $this->db->rawQueryOne($sQuery);

            $id = 0;
            if ($rowData) {
                // $this->db = $this->db->where('hepatitis_id', $rowData['hepatitis_id']);
                // $id = $this->db->update("form_hepatitis", $hepatitisData);

                // If this sample code exists, let us regenerate
                return $this->insertSampleCode($params);
            } else {
                if (isset($params['sampleCode']) && $params['sampleCode'] != '' && $params['sampleCollectionDate'] != null && $params['sampleCollectionDate'] != '') {
                    $hepatitisData['unique_id'] = $general->generateRandomString(32);
                    $id = $this->db->insert("form_hepatitis", $hepatitisData);
                }
            }
            if ($id > 0) {
                return  $id;
            } else {
                return 0;
            }
        } catch (\Exception $e) {
            error_log('Insert Hepatitis Sample : ' . $this->db->getLastError());
            error_log('Insert Hepatitis Sample : ' . $e->getMessage());
        }
    }
}
