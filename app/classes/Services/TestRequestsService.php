<?php

namespace App\Services;

use Exception;
use Throwable;
use SAMPLE_STATUS;
use App\Utilities\DateUtility;
use App\Utilities\LoggerUtility;
use App\Services\DatabaseService;
use App\Registries\ContainerRegistry;
use App\Abstracts\AbstractTestService;

final class TestRequestsService
{
    protected DatabaseService $db;
    protected CommonService $commonService;

    public function __construct(DatabaseService $db, CommonService $commonService)
    {
        $this->db = $db;
        $this->commonService = $commonService;
    }

    public function addToSampleCodeQueue(string $uniqueId, string $testType, string $sampleCollectionDate, ?string $provinceCode = null, ?string $sampleCodeFormat = null, ?string $prefix = null, ?string $accessType = null): bool
    {
        return $this->db->insert("queue_sample_code_generation", [
            'unique_id' => $uniqueId,
            'test_type' => $testType,
            'sample_collection_date' => DateUtility::isoDateFormat($sampleCollectionDate, true),
            'province_code' => $provinceCode,
            'sample_code_format' => $sampleCodeFormat,
            'prefix' => $prefix,
            'access_type' => $accessType
        ]);
    }


    public function processSampleCodeQueue($uniqueIds = [], $parallelProcess = false, $maxTries = 5, $interval = 5)
    {
        if ($parallelProcess === false) {
            $lockFile = TEMP_PATH . '/sample_code_generation.lock';

            // Check if another instance is already running
            if (file_exists($lockFile) && (filemtime($lockFile) > (time() - $interval * 2))) {
                exit(0);
            }

            // Create or update the lock file
            touch($lockFile);
        }

        $sampleCodeColumn = $this->commonService->isSTSInstance() ? 'remote_sample_code' : 'sample_code';
        $response = [];

        try {


            if (!empty($uniqueIds)) {
                $uniqueIds = is_array($uniqueIds) ? $uniqueIds : [$uniqueIds];
                $this->db->where('unique_id', $uniqueIds, 'IN');
            }
            $this->db->where('processed = 0');
            $queueItems = $this->db->get('queue_sample_code_generation', 100);

            if (!empty($queueItems)) {
                foreach ($queueItems as $item) {

                    if ($parallelProcess === false) {
                        // Touch the lock file to keep it live
                        touch($lockFile);
                    }

                    if (empty($item['test_type']) || empty($item['sample_collection_date']) || empty($item['unique_id'])) {
                        continue;
                    }

                    try {
                        $formTable = TestsService::getTestTableName($item['test_type']);
                        $primaryKey = TestsService::getTestPrimaryKeyColumn($item['test_type']);
                        $serviceClass = TestsService::getTestServiceClass($item['test_type']);

                        /** @var AbstractTestService $testTypeService */
                        $testTypeService = ContainerRegistry::get($serviceClass);

                        // Check if sample code already exists
                        $sQuery = "SELECT $sampleCodeColumn FROM $formTable WHERE unique_id = ?";
                        $rowData = $this->db->rawQueryOne($sQuery, [$item['unique_id']]);

                        if (!empty($rowData) && !empty($rowData[$sampleCodeColumn])) {
                            continue;
                        }

                        $this->db->beginTransaction();

                        $sampleCodeParams = [
                            'sampleCollectionDate' => $item['sample_collection_date'],
                            'provinceCode' => $item['province_code'] ?? null,
                            'testType' => $item['test_type'],
                            'sampleCodeFormat' => $item['sample_code_format'] ?? 'MMYY',
                            'prefix' => $item['prefix'] ?? $testTypeService->shortCode ?? 'T',
                            'insertOperation' => true,
                        ];

                        $tries = 0;
                        $sampleData = [];

                        do {
                            $sampleCodeParams['tries'] = $tries;
                            $sampleJson = $testTypeService->getSampleCode($sampleCodeParams);
                            $sampleData = json_decode((string)$sampleJson, true);

                            $rowData = [];
                            if (!empty($sampleData) && !empty($sampleData['sampleCode'])) {
                                $sQuery = "SELECT $primaryKey FROM $formTable WHERE $sampleCodeColumn = ?";
                                $rowData = $this->db->rawQueryOne($sQuery, [$sampleData['sampleCode']]);
                            }

                            $tries++;
                        } while (!empty($rowData) && $tries < $maxTries);

                        if ($tries >= $maxTries) {
                            throw new Exception("Maximum tries for generating sample code for {$item['unique_id']} exceeded");
                        }

                        $accessType = $item['access_type'] ?? null;
                        $tesRequestData = [];

                        if ($this->commonService->isSTSInstance()) {
                            $tesRequestData['remote_sample_code'] = $sampleData['sampleCode'];
                            $tesRequestData['remote_sample_code_format'] = $sampleData['sampleCodeFormat'];
                            $tesRequestData['remote_sample_code_key'] = $sampleData['sampleCodeKey'];
                            $tesRequestData['remote_sample'] = 'yes';
                            $tesRequestData['result_status'] = SAMPLE_STATUS\RECEIVED_AT_CLINIC;
                            if ($accessType === 'testing-lab') {
                                $tesRequestData['sample_code'] = $sampleData['sampleCode'];
                            }
                        } else {
                            $tesRequestData['sample_code'] = $sampleData['sampleCode'];
                            $tesRequestData['sample_code_format'] = $sampleData['sampleCodeFormat'];
                            $tesRequestData['sample_code_key'] = $sampleData['sampleCodeKey'];
                            $tesRequestData['remote_sample'] = 'no';
                        }

                        if (!empty($sampleData['sampleCode'])) {
                            $response[$item['unique_id']] = $tesRequestData;
                            $this->db->where('unique_id', $item['unique_id']);
                            $this->db->update($formTable, $tesRequestData);

                            $this->db->where('id', $item['id']);
                            $this->db->update('queue_sample_code_generation', ['processed' => 1]);
                        }

                        $this->db->commitTransaction();

                        return $response;
                    } catch (Throwable $e) {
                        $this->db->rollbackTransaction();
                        LoggerUtility::log('error', $e->getFile() . ":" . $e->getLine() . " - " . $e->getMessage(), [
                            'exception' => $e,
                            'file' => $e->getFile(),
                            'line' => $e->getLine(),
                            'stacktrace' => $e->getTraceAsString()
                        ]);
                    }
                }
            }
        } finally {
            if ($parallelProcess === false) {
                // Remove the lock file when the script ends
                unlink($lockFile);
            }
        }
    }
}
