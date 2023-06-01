<?php

namespace App\Services;


use MysqliDb;
use Exception;
use DateTimeImmutable;
use App\Utilities\DateUtility;
use App\Services\CommonService;
use App\Registries\ContainerRegistry;
use App\Services\GeoLocationsService;

/**
 * Generic tests functions
 *
 * @author Amit
 */

class GenericTestsService
{

    protected ?MysqliDb $db = null;
    protected string $table = 'form_generic';
    protected string $shortCode = 'LAB';
    protected CommonService $commonService;

    public function __construct($db = null, $commonService = null)
    {
        $this->db = $db ?? ContainerRegistry::get('db');
        $this->commonService = $commonService;
    }

    public function generateGenericSampleID($provinceCode, $sampleCollectionDate, $sampleFrom = null, $provinceId = '', $maxCodeKeyVal = null, $user = null, $testType = null)
    {

        if (!empty($maxCodeKeyVal)) {
            error_log(" ===== MAXX Code ====== " . $maxCodeKeyVal);
        }

        $globalConfig = $this->commonService->getGlobalConfig();
        $vlsmSystemConfig = $this->commonService->getSystemConfig();

        if (DateUtility::verifyIfDateValid($sampleCollectionDate) === false) {
            $sampleCollectionDate = 'now';
        }
        $dateObj = new DateTimeImmutable($sampleCollectionDate);

        $year = $dateObj->format('y');
        $month = $dateObj->format('m');
        $day = $dateObj->format('d');

        $remotePrefix = '';
        $sampleCodeKeyCol = 'sample_code_key';
        $sampleCodeCol = 'sample_code';
        if ($vlsmSystemConfig['sc_user_type'] == 'remoteuser') {
            $remotePrefix = 'R';
            $sampleCodeKeyCol = 'remote_sample_code_key';
            $sampleCodeCol = 'remote_sample_code';
        }

        $mnthYr = $month . $year;
        // Checking if sample code format is empty then we set by default 'MMYY'
        $sampleCodeFormat = $globalConfig['sample_code'] ?? 'MMYY';
        $prefixFromConfig = $globalConfig['sample_code_prefix'] ?? '';
        if (isset($testType) && !empty($testType)) {
            $prefixFromConfig = $testType;
        }
        if ($sampleCodeFormat == 'MMYY') {
            $mnthYr = $month . $year;
        } elseif ($sampleCodeFormat == 'YY') {
            $mnthYr = $year;
        }

        $autoFormatedString = $year . $month . $day;


        if (empty($maxCodeKeyVal)) {
            // If it is PNG form
            if ($globalConfig['vl_form'] == 5) {

                if (empty($provinceId) && !empty($provinceCode)) {
                    /** @var GeoLocations $geoLocations */
                    $geoLocationsService = ContainerRegistry::get(GeoLocationsService::class);
                    $provinceId = $geoLocationsService->getProvinceIDFromCode($provinceCode);
                }

                if (!empty($provinceId)) {
                    $this->db->where('province_id', $provinceId);
                }
            }

            $this->db->where('YEAR(sample_collection_date) = ?', array($dateObj->format('Y')));
            $maxCodeKeyVal = $this->db->getValue($this->table, "MAX($sampleCodeKeyCol)");
        }


        if (!empty($maxCodeKeyVal) && $maxCodeKeyVal > 0) {
            $maxId = $maxCodeKeyVal + 1;
        } else {
            $maxId = 1;
        }

        $maxId = sprintf("%04d", (int) $maxId);

        $sampleCodeGenerator = (array('maxId' => $maxId, 'mnthYr' => $mnthYr, 'auto' => $autoFormatedString));

        if ($globalConfig['vl_form'] == 5) {
            // PNG format has an additional R in prefix
            $remotePrefix = $remotePrefix . "R";
        }
        if ($sampleCodeFormat == 'auto') {
            if (isset($testType) && !empty($testType)) {
                $remotePrefix = $remotePrefix . $testType;
            }
            $sampleCodeGenerator['sampleCode'] = ($remotePrefix . $provinceCode . $autoFormatedString . $sampleCodeGenerator['maxId']);
            $sampleCodeGenerator['sampleCodeInText'] = ($remotePrefix . $provinceCode . $autoFormatedString . $sampleCodeGenerator['maxId']);
            $sampleCodeGenerator['sampleCodeFormat'] = ($remotePrefix . $provinceCode . $autoFormatedString);
            $sampleCodeGenerator['sampleCodeKey'] = ($sampleCodeGenerator['maxId']);
        } elseif ($sampleCodeFormat == 'auto2') {
            if (isset($testType) && !empty($testType)) {
                $remotePrefix = $remotePrefix . $testType;
            }
            $sampleCodeGenerator['sampleCode'] = $remotePrefix . $year . $provinceCode . $this->shortCode . $sampleCodeGenerator['maxId'];
            $sampleCodeGenerator['sampleCodeInText'] = $remotePrefix . $year . $provinceCode . $this->shortCode . $sampleCodeGenerator['maxId'];
            $sampleCodeGenerator['sampleCodeFormat'] = $remotePrefix . $provinceCode . $autoFormatedString;
            $sampleCodeGenerator['sampleCodeKey'] = $sampleCodeGenerator['maxId'];
        } elseif ($sampleCodeFormat == 'YY' || $sampleCodeFormat == 'MMYY') {
            $sampleCodeGenerator['sampleCode'] = $remotePrefix . $prefixFromConfig . $sampleCodeGenerator['mnthYr'] . $sampleCodeGenerator['maxId'];
            $sampleCodeGenerator['sampleCodeInText'] = $remotePrefix . $prefixFromConfig . $sampleCodeGenerator['mnthYr'] . $sampleCodeGenerator['maxId'];
            $sampleCodeGenerator['sampleCodeFormat'] = $remotePrefix . $prefixFromConfig . $sampleCodeGenerator['mnthYr'];
            $sampleCodeGenerator['sampleCodeKey'] = ($sampleCodeGenerator['maxId']);
        }

        $checkQuery = "SELECT $sampleCodeCol, $sampleCodeKeyCol FROM " . $this->table . " WHERE $sampleCodeCol='" . $sampleCodeGenerator['sampleCode'] . "'";
        $checkResult = $this->db->rawQueryOne($checkQuery);
        if (!empty($checkResult)) {
            error_log("DUP::: Sample Code ====== " . $sampleCodeGenerator['sampleCode']);
            error_log("DUP::: Sample Key Code ====== " . $maxId);
            error_log('DUP::: ' . $this->db->getLastQuery());
            return $this->generateGenericSampleID($provinceCode, $sampleCollectionDate, $sampleFrom, $provinceId, $maxId, $user);
        }
        return json_encode($sampleCodeGenerator);
    }

    public function generateSampleIDGenericTest($provinceCode, $sampleCollectionDate, $sampleFrom = null, $provinceId = '', $maxCodeKeyVal = null, $user = null, $testType = null)
    {

        if (!empty($maxCodeKeyVal)) {
            error_log(" ===== MAXX Code ====== " . $maxCodeKeyVal);
        }

        $globalConfig = $this->commonService->getGlobalConfig();
        $vlsmSystemConfig = $this->commonService->getSystemConfig();

        if (DateUtility::verifyIfDateValid($sampleCollectionDate) === false) {
            $sampleCollectionDate = 'now';
        }
        $dateObj = new DateTimeImmutable($sampleCollectionDate);

        $year = $dateObj->format('y');
        $month = $dateObj->format('m');
        $day = $dateObj->format('d');

        $remotePrefix = '';
        $sampleCodeKeyCol = 'sample_code_key';
        $sampleCodeCol = 'sample_code';
        if ($vlsmSystemConfig['sc_user_type'] == 'remoteuser') {
            $remotePrefix = 'R';
            $sampleCodeKeyCol = 'remote_sample_code_key';
            $sampleCodeCol = 'remote_sample_code';
        }

        // if (isset($user['access_type']) && !empty($user['access_type']) && $user['access_type'] != 'testing-lab') {
        //     $remotePrefix = 'R';
        //     $sampleCodeKeyCol = 'remote_sample_code_key';
        //     $sampleCodeCol = 'remote_sample_code';
        // }

        $mnthYr = $month . $year;
        // Checking if sample code format is empty then we set by default 'MMYY'
        $sampleCodeFormat = $globalConfig['sample_code'] ?? 'MMYY';
        $prefixFromConfig = $globalConfig['sample_code_prefix'] ?? '';
        if (isset($testType) && !empty($testType)) {
            $prefixFromConfig = $testType;
        }
        if ($sampleCodeFormat == 'MMYY') {
            $mnthYr = $month . $year;
        } elseif ($sampleCodeFormat == 'YY') {
            $mnthYr = $year;
        }

        $autoFormatedString = $year . $month . $day;


        if (empty($maxCodeKeyVal)) {
            // If it is PNG form
            if ($globalConfig['vl_form'] == 5) {

                if (empty($provinceId) && !empty($provinceCode)) {
                    /** @var GeoLocations $geoLocations */
                    $geoLocationsService = ContainerRegistry::get(GeoLocationsService::class);
                    $provinceId = $geoLocationsService->getProvinceIDFromCode($provinceCode);
                }

                if (!empty($provinceId)) {
                    $this->db->where('province_id', $provinceId);
                }
            }

            $this->db->where('YEAR(sample_collection_date) = ?', array($dateObj->format('Y')));
            $maxCodeKeyVal = $this->db->getValue($this->table, "MAX($sampleCodeKeyCol)");
        }


        if (!empty($maxCodeKeyVal) && $maxCodeKeyVal > 0) {
            $maxId = $maxCodeKeyVal + 1;
        } else {
            $maxId = 1;
        }

        $maxId = sprintf("%04d", (int) $maxId);

        $sampleCodeGenerator = (array('maxId' => $maxId, 'mnthYr' => $mnthYr, 'auto' => $autoFormatedString));

        if ($globalConfig['vl_form'] == 5) {
            // PNG format has an additional R in prefix
            $remotePrefix = $remotePrefix . "R";
        }

        if ($sampleCodeFormat == 'auto') {
            if (isset($testType) && !empty($testType)) {
                $remotePrefix = $remotePrefix . $testType;
            }
            $sampleCodeGenerator['sampleCode'] = ($remotePrefix . $provinceCode . $autoFormatedString . $sampleCodeGenerator['maxId']);
            $sampleCodeGenerator['sampleCodeInText'] = ($remotePrefix . $provinceCode . $autoFormatedString . $sampleCodeGenerator['maxId']);
            $sampleCodeGenerator['sampleCodeFormat'] = ($remotePrefix . $provinceCode . $autoFormatedString);
            $sampleCodeGenerator['sampleCodeKey'] = ($sampleCodeGenerator['maxId']);
        } elseif ($sampleCodeFormat == 'auto2') {
            if (isset($testType) && !empty($testType)) {
                $remotePrefix = $remotePrefix . $testType;
            }
            $sampleCodeGenerator['sampleCode'] = $remotePrefix . $year . $provinceCode . $this->shortCode . $sampleCodeGenerator['maxId'];
            $sampleCodeGenerator['sampleCodeInText'] = $remotePrefix . $year . $provinceCode . $this->shortCode . $sampleCodeGenerator['maxId'];
            $sampleCodeGenerator['sampleCodeFormat'] = $remotePrefix . $provinceCode . $autoFormatedString;
            $sampleCodeGenerator['sampleCodeKey'] = $sampleCodeGenerator['maxId'];
        } elseif ($sampleCodeFormat == 'YY' || $sampleCodeFormat == 'MMYY') {
            $sampleCodeGenerator['sampleCode'] = $remotePrefix . $prefixFromConfig . $sampleCodeGenerator['mnthYr'] . $sampleCodeGenerator['maxId'];
            $sampleCodeGenerator['sampleCodeInText'] = $remotePrefix . $prefixFromConfig . $sampleCodeGenerator['mnthYr'] . $sampleCodeGenerator['maxId'];
            $sampleCodeGenerator['sampleCodeFormat'] = $remotePrefix . $prefixFromConfig . $sampleCodeGenerator['mnthYr'];
            $sampleCodeGenerator['sampleCodeKey'] = ($sampleCodeGenerator['maxId']);
        }
        $checkQuery = "SELECT $sampleCodeCol, $sampleCodeKeyCol FROM form_generic WHERE $sampleCodeCol='" . $sampleCodeGenerator['sampleCode'] . "'";
        $checkResult = $this->db->rawQueryOne($checkQuery);
        // if (!empty($checkResult)) {
        //     $sampleCodeGenerator['sampleCode'] = $remotePrefix . $prefixFromConfig . $sampleCodeGenerator['mnthYr'] . ($sampleCodeGenerator['maxId'] + 1);
        //     $sampleCodeGenerator['sampleCodeInText'] = $remotePrefix . $prefixFromConfig . $sampleCodeGenerator['mnthYr'] . ($sampleCodeGenerator['maxId'] + 1);
        //     $sampleCodeGenerator['sampleCodeFormat'] = $remotePrefix . $prefixFromConfig . $sampleCodeGenerator['mnthYr'];
        //     $sampleCodeGenerator['sampleCodeKey'] = ($sampleCodeGenerator['maxId'] + 1);
        // }
        if (!empty($checkResult)) {
            error_log("DUP::: Sample Code ====== " . $sampleCodeGenerator['sampleCode']);
            error_log("DUP::: Sample Key Code ====== " . $maxId);
            error_log('DUP::: ' . $this->db->getLastQuery());
            return $this->generateGenericSampleID($provinceCode, $sampleCollectionDate, $sampleFrom, $provinceId, $maxId, $user);
        }
        return json_encode($sampleCodeGenerator);
    }

    public function getGenericSampleTypesByName($name = "")
    {
        $where = "";
        if (!empty($name)) {
            $where = " AND sample_type_name LIKE '$name%'";
        }
        $query = "SELECT * FROM r_generic_sample_types where sample_type_status='active '$where";
        return $this->db->rawQuery($query);
    }

    public function getGenericSampleTypes($updatedDateTime = null): array
    {
        $query = "SELECT * FROM r_generic_sample_types where sample_type_status='active'";
        if ($updatedDateTime) {
            $query .= " AND updated_datetime >= '$updatedDateTime' ";
        }
        $results = $this->db->rawQuery($query);
        $response = [];
        foreach ($results as $row) {
            $response[$row['sample_type_id']] = $row['sample_type_name'];
        }
        return $response;
    }

    public function getGenericResults(): array
    {
        return array(
            'positive' => 'Positive',
            'negative' => 'Negative',
            'invalid'  => 'Invalid'
        );
    }

    public function insertSampleCode($params)
    {
        try {

            $globalConfig = $this->commonService->getGlobalConfig();
            $vlsmSystemConfig = $this->commonService->getSystemConfig();

            $testType = $params['testType'] ?? null;
            $provinceCode = $params['provinceCode'] ?? null;
            $provinceId = $params['provinceId'] ?? null;
            $sampleCollectionDate = $params['sampleCollectionDate'] ?? null;

            if (
                empty($testType) ||
                empty($sampleCollectionDate) ||
                ($globalConfig['vl_form'] == 5 && empty($provinceId))
            ) {
                return 0;
            }

            $oldSampleCodeKey = $params['oldSampleCodeKey'] ?? null;

            $sampleJson = $this->generateGenericSampleID($provinceCode, $sampleCollectionDate, null, $provinceId, $oldSampleCodeKey, null, $testType);
            $sampleData = json_decode($sampleJson, true);
            $sampleCollectionDate = DateUtility::isoDateFormat($sampleCollectionDate, true);

            $tesRequestData = [
                'vlsm_country_id' => $globalConfig['vl_form'],
                'unique_id' => $params['uniqueId'] ?? $this->commonService->generateUUID(),
                'facility_id' => $params['facilityId'] ?? null,
                'lab_id' => $params['labId'] ?? null,
                'app_sample_code' => $params['appSampleCode'] ?? null,
                'sample_collection_date' => $sampleCollectionDate,
                'vlsm_instance_id' => $_SESSION['instanceId'] ?? $this->commonService->getInstanceId() ?? null,
                'province_id' => $provinceId,
                'test_type' => $testType,
                'request_created_by' => $_SESSION['userId'] ?? $params['userId'] ?? null,
                'form_attributes' => $params['formAttributes'] ?? "{}",
                'request_created_datetime' => DateUtility::getCurrentDateTime(),
                'last_modified_by' => $_SESSION['userId'] ?? $params['userId'] ?? null,
                'last_modified_datetime' => DateUtility::getCurrentDateTime()
            ];

            $oldSampleCodeKey = null;
            if ($vlsmSystemConfig['sc_user_type'] === 'remoteuser') {
                $tesRequestData['remote_sample_code'] = $sampleData['sampleCode'];
                $tesRequestData['remote_sample_code_format'] = $sampleData['sampleCodeFormat'];
                $tesRequestData['remote_sample_code_key'] = $sampleData['sampleCodeKey'];
                $tesRequestData['remote_sample'] = 'yes';
                $tesRequestData['result_status'] = 9;
                if ($_SESSION['accessType'] === 'testing-lab') {
                    $tesRequestData['sample_code'] = $sampleData['sampleCode'];
                    $tesRequestData['result_status'] = 6;
                }
            } else {
                $tesRequestData['sample_code'] = $sampleData['sampleCode'];
                $tesRequestData['sample_code_format'] = $sampleData['sampleCodeFormat'];
                $tesRequestData['sample_code_key'] = $sampleData['sampleCodeKey'];
                $tesRequestData['remote_sample'] = 'no';
                $tesRequestData['result_status'] = 6;
            }
            $sQuery = "SELECT sample_id,
                            sample_code,
                            sample_code_format,
                            sample_code_key,
                            remote_sample_code,
                            remote_sample_code_format,
                            remote_sample_code_key
                            FROM form_generic ";
            if (isset($sampleData['sampleCode']) && !empty($sampleData['sampleCode'])) {
                $sQuery .= " WHERE (sample_code like '" . $sampleData['sampleCode'] . "' OR remote_sample_code like '" . $sampleData['sampleCode'] . "')";
            }
            $sQuery .= " LIMIT 1";
            $rowData = $this->db->rawQueryOne($sQuery);
            $id = 0;
            if (empty($rowData) && !empty($sampleData['sampleCode'])) {
                $formAttributes = [
                    'applicationVersion'  => $this->commonService->getSystemConfig('sc_version'),
                    'ip_address'    => $this->commonService->getClientIpAddress()
                ];
                $tesRequestData['form_attributes'] = json_encode($formAttributes);
                $id = $this->db->insert("form_generic", $tesRequestData);
                if ($this->db->getLastErrno() > 0) {
                    error_log($this->db->getLastError());
                }
            } else {
                // If this sample code exists, let us regenerate the sample code and insert
                $params['oldSampleCodeKey'] = $sampleData['sampleCodeKey'];
                return $this->insertSampleCode($params);
            }
            return $id > 0 ? $id : 0;
        } catch (Exception $e) {
            error_log('Insert lab tests Sample : ' . $this->db->getLastErrno());
            error_log('Insert lab tests Sample : ' . $this->db->getLastError());
            error_log('Insert lab tests Sample : ' . $this->db->getLastQuery());
            error_log('Insert lab tests Sample : ' . $e->getMessage());
            return 0;
        }
    }

    public function getDynamicFields($genericTestId): array
    {
        $return = [];
        if ($genericTestId > 0) {
            $labelsResponse = $dynamicJson = [];
            $this->db->where("sample_id", $genericTestId);
            $generic = $this->db->getOne('form_generic');
            if ($generic['test_type_form']) {
                $dynamicJson = (array)json_decode($generic['test_type_form']);
                $this->db->where('test_type_id', $generic['test_type']);
                $testTypes = $this->db->getOne('r_test_types');
                $labels = json_decode($testTypes['test_form_config'], true);

                foreach ($labels['field_id'] as $key => $le) {
                    $labelsResponse[$le] = $labels['field_name'][$key];
                }
            }
            $return = array('dynamicValue' => $dynamicJson, 'dynamicLabel' => $labelsResponse);
        }
        return $return;
    }

    public function getReasonForFailure($option = true, $updatedDateTime = null)
    {
        $result = [];
        $this->db->where('test_failure_reason_status', 'active');
        if ($updatedDateTime) {
            $this->db->where('updated_datetime >= "' . $updatedDateTime . '"');
        }
        $results = $this->db->get('r_generic_test_failure_reasons');
        if ($option) {
            foreach ($results as $row) {
                $result[$row['test_failure_reason_id']] = $row['test_failure_reason'];
            }
            return $result;
        } else {
            return $results;
        }
    }

    public function getInterpretationResults($testType, $result)
    {
        if (!isset($result) || empty($result)) {
            return null;
        }
        if (!isset($testType) || empty($testType)) {
            return null;
        }
        $this->db->where('test_type_id', $testType);
        $testTypeResult = $this->db->getOne('r_test_types');
        if (isset($testTypeResult['test_results_config']) && !empty($testTypeResult['test_results_config'])) {
            $resultConfig = json_decode($testTypeResult['test_results_config'], true);
            if (isset($resultConfig['result_type']) && $resultConfig['result_type'] == 'quantitative') {
                if (is_numeric($result)) {
                    if ($result >= $resultConfig['high_value']) {
                        return ucwords($resultConfig['above_threshold']);
                    }
                    if ($result == $resultConfig['threshold_value']) {
                        return ucwords($resultConfig['at_threshold']);
                    }
                    if ($result < $resultConfig['low_value']) {
                        return ucwords($resultConfig['below_threshold']);
                    }
                } else {
                    $resultIndex =  (isset($result) && isset($resultConfig['quantitative_result']) && in_array($result, $resultConfig['quantitative_result'])) ? array_search(strtolower($result), array_map('strtolower', $resultConfig['quantitative_result'])) : '';
                    return ucwords($resultConfig['quantitative_result_interpretation'][$resultIndex]);
                }
            } else if (isset($resultConfig['result_type']) && $resultConfig['result_type'] == 'qualitative') {
                $resultIndex =  (isset($result) && isset($resultConfig['result']) && in_array($result, $resultConfig['result'])) ? array_search(strtolower($result), array_map('strtolower', $resultConfig['result'])) : '';
                return ucwords($resultConfig['result_interpretation'][$resultIndex]);
            }
        }
    }

    public function getGenericTestsByFormId($genId = ""): array
    {
        $response = [];

        // Using this in sync requests/results
        if (is_array($genId) && !empty($genId)) {
            $results = $this->db->rawQuery("SELECT * FROM generic_test_results WHERE `generic_id` IN (" . implode(",", $genId) . ") ORDER BY test_id ASC");

            foreach ($results as $row) {
                $response[$row['generic_id']][$row['test_id']] = $row;
            }
        } elseif (!empty($genId) && $genId != "" && !is_array($genId)) {
            $response = $this->db->rawQuery("SELECT * FROM generic_test_results WHERE `generic_id` = $genId ORDER BY test_id ASC");
        } elseif (!is_array($genId)) {
            $response = $this->db->rawQuery("SELECT * FROM generic_test_results ORDER BY test_id ASC");
        }

        return $response;
    }

    // Quickly insert data in dynamic
    public function quickInsert($table, $fields, $values)
    {
        // echo "<pre>";print_r(array_combine($fields, $values));die;
        return $this->db->insert($table, array_combine($fields, $values));
    }
}
