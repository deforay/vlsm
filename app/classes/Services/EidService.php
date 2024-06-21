<?php

namespace App\Services;

use App\Utilities\MiscUtility;
use COUNTRY;
use Throwable;
use SAMPLE_STATUS;
use App\Utilities\DateUtility;
use App\Utilities\LoggerUtility;
use App\Exceptions\SystemException;
use App\Abstracts\AbstractTestService;

final class EidService extends AbstractTestService
{
    protected string $testType = 'eid';


    public function getSampleCode($params)
    {
        if (empty($params['sampleCollectionDate'])) {
            return json_encode([]);
        } else {
            $globalConfig = $this->commonService->getGlobalConfig();
            $params['sampleCodeFormat'] = $globalConfig['eid_sample_code'] ?? 'MMYY';
            $params['prefix'] = $params['prefix'] ?? $globalConfig['eid_sample_code_prefix'] ?? $this->shortCode;

            try {
                return $this->generateSampleCode($this->table, $params);
            } catch (Throwable $e) {
                LoggerUtility::log('error', 'Generate Sample ID : ' . $e->getMessage(), [
                    'exception' => $e,
                    'file' => $e->getFile(), // File where the error occurred
                    'line' => $e->getLine(), // Line number of the error
                    'stacktrace' => $e->getTraceAsString()
                ]);
                return json_encode([]);
            }
        }
    }
    public function getEidResults($updatedDateTime = null): array
    {
        $query = "SELECT * FROM r_eid_results WHERE status='active' ";
        if ($updatedDateTime) {
            $query .= " AND updated_datetime >= '$updatedDateTime' ";
        }
        $query .= " ORDER BY result_id";
        $results = $this->db->rawQuery($query);
        $response = [];
        foreach ($results as $row) {
            $response[$row['result_id']] = $row['result'];
        }
        return $response;
    }

    public function getEidSampleTypes($updatedDateTime = null): array
    {
        $query = "SELECT * FROM r_eid_sample_type where status='active' ";
        if ($updatedDateTime) {
            $query .= " AND updated_datetime >= '$updatedDateTime' ";
        }
        $results = $this->db->rawQuery($query);
        $response = [];
        foreach ($results as $row) {
            $response[$row['sample_id']] = $row['sample_name'];
        }
        return $response;
    }

    public function insertSample($params, $returnSampleData = false)
    {
        try {

            // Start a new transaction (this starts a new transaction if not already started)
            // see the beginTransaction() function implementation to understand how this works
            $this->db->beginTransaction();

            $formId = (int) $this->commonService->getGlobalConfig('vl_form');

            $params['tries'] = $params['tries'] ?? 0;
            if ($params['tries'] >= $this->maxTries) {
                throw new SystemException("Exceeded maximum number of tries ($this->maxTries) for inserting sample");
            }

            $provinceCode = $params['provinceCode'] ?? null;
            $provinceId = $params['provinceId'] ?? null;
            $sampleCollectionDate = $params['sampleCollectionDate'] ?? null;

            // PNG FORM (formId = 5) CANNOT HAVE PROVINCE EMPTY
            // Sample Collection Date Cannot be Empty
            if (empty($sampleCollectionDate) || DateUtility::isDateValid($sampleCollectionDate) === false || ($formId == COUNTRY\PNG && empty($provinceId))) {
                return 0;
            }

            $sampleCodeParams = [];
            $sampleCodeParams['sampleCollectionDate'] = $sampleCollectionDate;
            $sampleCodeParams['provinceCode'] = $provinceCode;
            $sampleCodeParams['provinceId'] = $provinceId;
            $sampleCodeParams['existingMaxId'] = $params['oldSampleCodeKey'] ?? null;
            $sampleCodeParams['insertOperation'] = $params['insertOperation'] ?? false;

            $sampleJson = $this->getSampleCode($sampleCodeParams);
            $sampleData = json_decode((string) $sampleJson, true);

            if ($this->commonService->isSTSInstance()) {
                $sampleCodeColumn = 'remote_sample_code';
            } else {
                $sampleCodeColumn = 'sample_code';
            }

            $rowData = [];
            if (!empty($sampleData['sampleCode'])) {
                $sQuery = "SELECT {$this->primaryKey} FROM {$this->table} WHERE $sampleCodeColumn = ?";
                $rowData = $this->db->rawQueryOne($sQuery, [$sampleData['sampleCode']]);
            }

            if (empty($rowData) && !empty($sampleData['sampleCode'])) {

                $tesRequestData = [
                    'vlsm_country_id' => $formId,
                    'sample_reordered' => $params['sampleReordered'] ?? 'no',
                    'unique_id' => $params['uniqueId'] ?? MiscUtility::generateUUID(),
                    'facility_id' => $params['facilityId'] ?? null,
                    'lab_id' => $params['labId'] ?? null,
                    'app_sample_code' => $params['appSampleCode'] ?? null,
                    'sample_collection_date' => DateUtility::isoDateFormat($sampleCollectionDate, true),
                    'vlsm_instance_id' => $_SESSION['instanceId'] ?? $this->commonService->getInstanceId() ?? null,
                    'province_id' => _castVariable($provinceId, 'int'),
                    'request_created_by' => $_SESSION['userId'] ?? $params['userId'] ?? null,
                    //'form_attributes' => $params['formAttributes'] ?? "{}",
                    'request_created_datetime' => DateUtility::getCurrentDateTime(),
                    'last_modified_by' => $_SESSION['userId'] ?? $params['userId'] ?? null,
                    'last_modified_datetime' => DateUtility::getCurrentDateTime()
                ];

                $accessType = $_SESSION['accessType'] ?? $params['accessType'] ?? null;

                if ($this->commonService->isSTSInstance()) {
                    $tesRequestData['remote_sample_code'] = $sampleData['sampleCode'];
                    $tesRequestData['remote_sample_code_format'] = $sampleData['sampleCodeFormat'];
                    $tesRequestData['remote_sample_code_key'] = $sampleData['sampleCodeKey'];
                    $tesRequestData['remote_sample'] = 'yes';
                    $tesRequestData['result_status'] = SAMPLE_STATUS\RECEIVED_AT_CLINIC;
                    if ($accessType === 'testing-lab') {
                        $tesRequestData['sample_code'] = $sampleData['sampleCode'];
                        $tesRequestData['result_status'] = SAMPLE_STATUS\RECEIVED_AT_TESTING_LAB;
                    }
                } else {
                    $tesRequestData['sample_code'] = $sampleData['sampleCode'];
                    $tesRequestData['sample_code_format'] = $sampleData['sampleCodeFormat'];
                    $tesRequestData['sample_code_key'] = $sampleData['sampleCodeKey'];
                    $tesRequestData['remote_sample'] = 'no';
                    $tesRequestData['result_status'] = SAMPLE_STATUS\RECEIVED_AT_TESTING_LAB;
                }

                /* $formAttributes = [
                    'applicationVersion' => $this->commonService->getSystemConfig('sc_version'),
                    'ip_address' => $this->commonService->getClientIpAddress()
                ];
                $tesRequestData['form_attributes'] = json_encode($formAttributes);*/
                $this->db->insert("form_eid", $tesRequestData);
                $id = $this->db->getInsertId();
                // Commit the transaction after the successful insert
                $this->db->commitTransaction();
            } else {
                LoggerUtility::log('info', 'Sample ID exists already. Trying to regenerate Sample ID', [
                    'file' => __FILE__,
                    'line' => __LINE__,
                ]);

                // Rollback the current transaction to release locks and undo changes
                $this->db->rollbackTransaction();

                // If this sample id exists, let us regenerate the sample id and insert
                $params['tries']++;
                $params['oldSampleCodeKey'] = $sampleData['sampleCodeKey'];
                return $this->insertSample($params);
            }
        } catch (Throwable $e) {
            // Rollback the current transaction to release locks and undo changes
            $this->db->rollbackTransaction();

            LoggerUtility::log('error', $this->db->getLastErrno() . ":" . $this->db->getLastError());
            LoggerUtility::log('error', $this->db->getLastQuery());

            LoggerUtility::log('error', 'Insert EID Sample : ' . $e->getMessage(), [
                'exception' => $e,
                'file' => $e->getFile(), // File where the error occurred
                'line' => $e->getLine(), // Line number of the error
                'stacktrace' => $e->getTraceAsString()
            ]);
            $id = 0;
        }
        if ($returnSampleData === true) {
            return [
                'id' => max($id, 0),
                'uniqueId' => $tesRequestData['unique_id'] ?? null,
                'sampleCode' => $tesRequestData['sample_code'] ?? null,
                'remoteSampleCode' => $tesRequestData['remote_sample_code'] ?? null
            ];
        } else {
            return max($id, 0);
        }
    }
}
