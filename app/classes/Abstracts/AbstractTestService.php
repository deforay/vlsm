<?php

namespace App\Abstracts;

use COUNTRY;
use Exception;
use DateTimeImmutable;
use App\Utilities\DateUtility;
use App\Services\CommonService;
use App\Utilities\LoggerUtility;
use App\Services\DatabaseService;
use App\Exceptions\SystemException;
use App\Services\GeoLocationsService;

abstract class AbstractTestService
{
    protected DatabaseService $db;
    protected CommonService $commonService;
    protected GeoLocationsService $geoLocationsService;
    protected int $maxTries = 5; // Max tries for generating sample code

    public function __construct(
        DatabaseService $db,
        CommonService $commonService,
        GeoLocationsService $geoLocationsService
    ) {
        $this->db = $db;
        $this->commonService = $commonService;
        $this->geoLocationsService = $geoLocationsService;
    }
    abstract public function getSampleCode($params);
    abstract public function insertSample($params, $returnSampleData = false);

    public function generateSampleCode($testTable, $params, $tryCount = 0): bool|string
    {

        if ($tryCount >= $this->maxTries) {
            throw new SystemException("Exceeded maximum number of tries ($this->maxTries) for generating sample code");
        }

        $sampleCodeGenerator = [];
        $formId = $this->commonService->getGlobalConfig('vl_form');
        $userType = $this->commonService->getSystemConfig('sc_user_type');

        $insertOperation = $params['insertOperation'] ?? false;

        $sampleCollectionDate = $params['sampleCollectionDate'] ?? null;
        $provinceCode = $params['provinceCode'] ?? null;
        //$provinceId = $params['provinceId'] ?? null;
        $sampleCodeFormat = $params['sampleCodeFormat'] ?? 'MMYY';
        $prefix = $params['prefix'] ?? 'T';
        $testType = $params['testType'];
        $existingMaxId = $params['existingMaxId'] ?? null;

        if (empty($sampleCollectionDate) || DateUtility::isDateValid($sampleCollectionDate) === false) {
            $sampleCollectionDate = 'now';
        }
        $dateObj = new DateTimeImmutable($sampleCollectionDate);

        $year = $dateObj->format('y');
        $month = $dateObj->format('m');
        $day = $dateObj->format('d');

        $autoFormatedString = $year . $month . $day;

        $remotePrefix = '';
        $sampleCodeKeyCol = 'sample_code_key';
        $sampleCodeType = 'sample_code';
        if (!empty($userType) && $userType == 'remoteuser') {
            $remotePrefix = 'R';
            $sampleCodeKeyCol = 'remote_sample_code_key';
            $sampleCodeType = 'remote_sample_code';
        }

        try {
            $yearData = [];
            $currentYear = $dateObj->format('Y');
            if (!empty($existingMaxId) && $existingMaxId > 0) {
                $maxId = $existingMaxId + 1;
            } else {
                // Determine the sequence number
                $sql = "SELECT max_sequence_number FROM sequence_counter
                        WHERE year = ? AND
                        test_type = ? AND
                        code_type = ?";

                if ($insertOperation) {
                    $sql .= " FOR UPDATE";
                }
                $yearData = $this->db->rawQueryOne($sql, [
                    $currentYear,
                    $testType,
                    $sampleCodeType
                ]);

                if (!empty($yearData)) {
                    $maxId = $yearData['max_sequence_number'] + 1;
                } else {
                    $maxId = 1;
                }
            }

            //$maxId = sprintf("%04d", (int) $maxId);

            $sampleCodeGenerator = [
                'sampleCode' => $remotePrefix . $prefix . $year . $maxId,
                'sampleCodeInText' => $remotePrefix . $prefix . $year . $maxId,
                'sampleCodeFormat' => $sampleCodeFormat,
                'sampleCodeKey' => $maxId,
                'maxId' => $maxId,
                'mnthYr' => $month . $year,
                'auto' => $year . $month . $day
            ];

            // PNG format has an additional R in prefix
            if ($formId == COUNTRY\PNG) {
                $remotePrefix = $remotePrefix . "R";
            }


            if ($sampleCodeFormat == 'auto') {
                $sampleCodeGenerator['sampleCode'] = ($remotePrefix . $provinceCode . $autoFormatedString . $sampleCodeGenerator['maxId']);
                $sampleCodeGenerator['sampleCodeInText'] = ($remotePrefix . $provinceCode . $autoFormatedString . $sampleCodeGenerator['maxId']);
                $sampleCodeGenerator['sampleCodeFormat'] = ($remotePrefix . $provinceCode . $autoFormatedString);
                $sampleCodeGenerator['sampleCodeKey'] = ($sampleCodeGenerator['maxId']);
            } elseif ($sampleCodeFormat == 'auto2') {
                $sampleCodeGenerator['sampleCode'] = $remotePrefix . $year . $provinceCode . $prefix . $sampleCodeGenerator['maxId'];
                $sampleCodeGenerator['sampleCodeInText'] = $remotePrefix . $year . $provinceCode . $prefix . $sampleCodeGenerator['maxId'];
                $sampleCodeGenerator['sampleCodeFormat'] = $remotePrefix . $provinceCode . $autoFormatedString;
                $sampleCodeGenerator['sampleCodeKey'] = $sampleCodeGenerator['maxId'];
            } elseif ($sampleCodeFormat == 'YY' || $sampleCodeFormat == 'MMYY') {
                $sampleCodeGenerator['sampleCode'] = $remotePrefix . $prefix . $sampleCodeGenerator['mnthYr'] . $sampleCodeGenerator['maxId'];
                $sampleCodeGenerator['sampleCodeInText'] = $remotePrefix . $prefix . $sampleCodeGenerator['mnthYr'] . $sampleCodeGenerator['maxId'];
                $sampleCodeGenerator['sampleCodeFormat'] = $remotePrefix . $prefix . $sampleCodeGenerator['mnthYr'];
                $sampleCodeGenerator['sampleCodeKey'] = ($sampleCodeGenerator['maxId']);
            }

            // We check for duplication only if we are inserting a new record
            if ($insertOperation) {
                $checkQuery = "SELECT $sampleCodeType, $sampleCodeKeyCol
                                FROM $testTable
                                WHERE $sampleCodeType= ?";
                $checkResult = $this->db->rawQueryOne($checkQuery, [$sampleCodeGenerator['sampleCode']]);
                if (!empty($checkResult)) {
                    LoggerUtility::log('info', "DUPLICATE ::: Sample ID/Sample Key Code in $testTable ::: " . $sampleCodeGenerator['sampleCode'] . " / " . $maxId);
                    $params['existingMaxId'] = $maxId;
                    return $this->generateSampleCode($testTable, $params, $tryCount + 1);
                }

                //Insert or update the sequence counter for this test type and year
                $data = [
                    'test_type' => $testType,
                    'year' => $currentYear,
                    'max_sequence_number' => $maxId,
                    'code_type' => $sampleCodeType
                ];

                $onDuplicateKeyUpdateData = [
                    'max_sequence_number' => $maxId
                ];

                $this->db->onDuplicate($onDuplicateKeyUpdateData)
                    ->insert('sequence_counter', $data);
            }
        } catch (Exception | SystemException $exception) {
            // LoggerUtility::log('error', "Error while generating Sample Code for $testTable : " . $exception->getMessage(), [
            //     'exception' => $exception,
            //     'file' => $exception->getFile(), // File where the error occurred
            //     'line' => $exception->getLine(), // Line number of the error
            //     'stacktrace' => $exception->getTraceAsString()
            // ]);
            throw new SystemException("Error while generating Sample Code for $testTable : " . $exception->getMessage(), $exception->getCode(), $exception);
        }

        return json_encode($sampleCodeGenerator);
    }
}
