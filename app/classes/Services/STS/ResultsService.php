<?php

namespace App\Services\STS;

use App\Services\TestsService;
use App\Services\UsersService;
use App\Utilities\DateUtility;
use App\Services\CommonService;
use App\Services\Covid19Service;
use App\Services\DatabaseService;
use App\Services\HepatitisService;
use App\Registries\ContainerRegistry;
use App\Services\GenericTestsService;
use App\Abstracts\AbstractTestService;


final class ResultsService
{
    protected CommonService $commonService;
    protected DatabaseService $db;
    protected string $testType;
    protected string $tableName;
    protected string $primaryKeyName;

    /** @var AbstractTestService $testTypeService */
    protected $testTypeService;
    protected $usersService;

    protected $fieldsToRemoveForAcceptedResults = [];
    protected $unwantedColumns = [];

    public function __construct(DatabaseService $db, CommonService $commonService, UsersService $usersService)
    {
        $this->db = $db ?? ContainerRegistry::get(DatabaseService::class);
        $this->commonService = $commonService;
        $this->usersService = $usersService;
    }

    private function setTestType($testType)
    {
        $this->testType = $testType;
        $this->tableName = TestsService::getTestTableName($testType);
        $this->primaryKeyName = TestsService::getTestPrimaryKeyColumn($testType);
        $serviceClass = TestsService::getTestServiceClass($testType);

        $this->testTypeService = ContainerRegistry::get($serviceClass);
    }

    public function setFieldsToRemoveForAcceptedResults($fieldsToRemove)
    {
        $this->fieldsToRemoveForAcceptedResults = $fieldsToRemove;
    }

    public function setUnwantedColumns($unwantedColumns)
    {
        $this->unwantedColumns = $unwantedColumns;
    }

    public function getApproverName($approverId)
    {
        return $this->usersService->getOrCreateUser($approverId);
    }

    public function getResults($testType, $labId)
    {
        $this->setTestType($testType);
        $resultsData = [];

        $rResult = $this->runQuery($labId);

        //$resultsData = $this->returnResults($rResult);

        return $resultsData;
    }

    private function runQuery($labId)
    {
        // Start with selecting all columns
        $columnSelection = "*";

        if ($this->testType == 'vl') {
            // Alias and constant column logic specific to VL
            $aliasColumns = [
                'sample_type' => 'specimen_type',
                //'patient_art_no' => 'patient_id'
            ];

            $constantColumns = [
                'sample_code_title' => "'auto'"
            ];

            // Add alias columns
            foreach ($aliasColumns as $oldName => $newName) {
                $columnSelection .= ", $newName AS $oldName";
            }

            // Add constant columns
            foreach ($constantColumns as $columnName => $constantValue) {
                $columnSelection .= ", $constantValue AS $columnName";
            }
        }

        $condition = $this->buildCondition($labId, $facilityMapResult, $manifestCode);

        $sQuery = "SELECT $columnSelection FROM $this->tableName WHERE $condition";

        [$rResult, $resultCount] = $this->db->getQueryResultAndCount($sQuery, returnGenerator: false);

        return [$rResult, $resultCount];
    }

    private function returnResults(array $rResult, int $resultCount): array
    {
        $sampleIds = $facilityIds = [];
        if ($resultCount > 0) {
            $sampleIds = array_column($rResult, $this->primaryKeyName);
            $facilityIds = array_column($rResult, 'facility_id');
        }
        return [
            'sampleIds' => $sampleIds,
            'facilityIds' => $facilityIds,
            'requests' => $rResult
        ];
    }
}
