<?php

namespace App\Services;

use Exception;
use SAMPLE_STATUS;
use App\Utilities\DateUtility;
use App\Abstracts\AbstractTestService;

class EidService extends AbstractTestService
{

    protected string $table = 'form_eid';
    protected string $shortCode = 'EID';

    public function getSampleCode($params)
    {
        if (empty($params['sampleCollectionDate'])) {
            return json_encode([]);
        } else {
            $globalConfig = $this->commonService->getGlobalConfig();
            $params['sampleCodeFormat'] = $globalConfig['eid_sample_code'] ?? 'MMYY';
            $params['prefix'] = $params['prefix'] ?? $globalConfig['eid_sample_code_prefix'] ?? $this->shortCode;
            return $this->generateSampleCode($this->table, $params);
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
            $formId = $this->commonService->getGlobalConfig('vl_form');

            $provinceCode = $params['provinceCode'] ?? null;
            $provinceId = $params['provinceId'] ?? null;
            $sampleCollectionDate = $params['sampleCollectionDate'] ?? null;

            // PNG FORM (formId = 5) CANNOT HAVE PROVINCE EMPTY
            // Sample Collection Date Cannot be Empty
            if (empty($sampleCollectionDate) || ($formId == 5 && empty($provinceId))) {
                return 0;
            }

            $sampleCodeParams = [];
            $sampleCodeParams['sampleCollectionDate'] = $sampleCollectionDate;
            $sampleCodeParams['provinceCode'] = $provinceCode;
            $sampleCodeParams['provinceId'] = $provinceId;
            $sampleCodeParams['maxCodeKeyVal'] = $params['oldSampleCodeKey'] ?? null;

            $sampleJson = $this->getSampleCode($sampleCodeParams);
            $sampleData = json_decode($sampleJson, true);

            $sQuery = "SELECT eid_id FROM form_eid ";
            if (!empty($sampleData['sampleCode'])) {
                $sQuery .= " WHERE (sample_code like '" . $sampleData['sampleCode'] . "' OR remote_sample_code like '" . $sampleData['sampleCode'] . "')";
            }
            $sQuery .= " LIMIT 1";

            $rowData = $this->db->rawQueryOne($sQuery);


            $id = 0;
            if (empty($rowData) && !empty($sampleData['sampleCode'])) {

                $tesRequestData = [
                    'vlsm_country_id' => $formId,
                    'unique_id' => $params['uniqueId'] ?? $this->commonService->generateUUID(),
                    'facility_id' => $params['facilityId'] ?? null,
                    'lab_id' => $params['labId'] ?? null,
                    'app_sample_code' => $params['appSampleCode'] ?? null,
                    'sample_collection_date' => DateUtility::isoDateFormat($sampleCollectionDate, true),
                    'vlsm_instance_id' => $_SESSION['instanceId'] ?? $this->commonService->getInstanceId() ?? null,
                    'province_id' => $provinceId,
                    'request_created_by' => $_SESSION['userId'] ?? $params['userId'] ?? null,
                    'form_attributes' => $params['formAttributes'] ?? "{}",
                    'request_created_datetime' => DateUtility::getCurrentDateTime(),
                    'last_modified_by' => $_SESSION['userId'] ?? $params['userId'] ?? null,
                    'last_modified_datetime' => DateUtility::getCurrentDateTime()
                ];

                $accessType = $_SESSION['accessType'] ?? $params['accessType'] ?? null;
                $instanceType = $_SESSION['instanceType'] ?? $params['instanceType'] ?? null;

                if ($instanceType === 'remoteuser') {
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

                $formAttributes = [
                    'applicationVersion' => $this->commonService->getSystemConfig('sc_version'),
                    'ip_address' => $this->commonService->getClientIpAddress()
                ];
                $tesRequestData['form_attributes'] = json_encode($formAttributes);
                $this->db->insert("form_eid", $tesRequestData);
                $id = $this->db->getInsertId();
                if ($this->db->getLastErrno() > 0) {
                    error_log($this->db->getLastError());
                }
            } else {
                // If this sample code exists, let us regenerate the sample code and insert
                $params['oldSampleCodeKey'] = $sampleData['sampleCodeKey'];
                return $this->insertSample($params);
            }
        } catch (Exception $e) {
            error_log('Insert EID Sample : ' . $this->db->getLastErrno());
            error_log('Insert EID Sample : ' . $this->db->getLastError());
            error_log('Insert EID Sample : ' . $this->db->getLastQuery());
            error_log('Insert EID Sample : ' . $e->getMessage());
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
