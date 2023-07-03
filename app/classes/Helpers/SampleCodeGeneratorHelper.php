<?php

namespace App\Helpers;

use MysqliDb;
use DateTimeImmutable;
use App\Utilities\DateUtility;
use App\Services\CommonService;
use App\Registries\ContainerRegistry;

class SampleCodeGeneratorHelper
{
    private ?MysqliDb $db;
    private CommonService $commonService;

    public function __construct(?MysqliDb $db, CommonService $commonService)
    {
        $this->db = $db;
        $this->commonService = $commonService;
    }

    public function generateSampleCode($testTable, $params)
    {

        $formId = $this->commonService->getGlobalConfig('form_id');
        $userType = $this->commonService->getSystemConfig('sc_user_type');

        $sampleCollectionDate = $params['sampleCollectionDate'] ?? null;
        $provinceCode = $params['provinceCode'] ?? null;
        $provinceId = $params['provinceId'] ?? null;
        $maxCodeKeyVal = $params['maxCodeKeyVal'] ?? null;
        $sampleCodeFormat = $params['sampleCodeFormat'] ?? 'MMYY';
        $prefix = $params['prefix'] ?? 'T';

        if (empty($sampleCollectionDate) || DateUtility::verifyIfDateValid($sampleCollectionDate) === false) {
            $sampleCollectionDate = 'now';
        }
        $dateObj = new DateTimeImmutable($sampleCollectionDate);

        $year = $dateObj->format('y');
        $month = $dateObj->format('m');
        $day = $dateObj->format('d');

        $remotePrefix = '';
        $sampleCodeKeyCol = 'sample_code_key';
        $sampleCodeCol = 'sample_code';
        if (!empty($userType) && $userType == 'remoteuser') {
            $remotePrefix = 'R';
            $sampleCodeKeyCol = 'remote_sample_code_key';
            $sampleCodeCol = 'remote_sample_code';
        }

        $mnthYr = $month . $year;

        if ($sampleCodeFormat == 'MMYY') {
            $mnthYr = $month . $year;
        } elseif ($sampleCodeFormat == 'YY') {
            $mnthYr = $year;
        }

        $autoFormatedString = $year . $month . $day;


        if (empty($maxCodeKeyVal)) {
            // If it is PNG form
            if ($formId == 5) {

                if (empty($provinceId) && !empty($provinceCode)) {
                    /** @var GeoLocationsService $geoLocationsService */
                    $geoLocationsService = ContainerRegistry::get(GeoLocationsService::class);
                    $params['provinceId'] = $provinceId = $geoLocationsService->getProvinceIDFromCode($provinceCode);
                }

                if (!empty($provinceId)) {
                    $this->db->where('province_id', $provinceId);
                }
            }

            $this->db->where('YEAR(sample_collection_date) = ?', [$dateObj->format('Y')]);
            $maxCodeKeyVal = $this->db->getValue($testTable, "MAX($sampleCodeKeyCol)");
        }


        if (!empty($maxCodeKeyVal) && $maxCodeKeyVal > 0) {
            $maxId = $maxCodeKeyVal + 1;
        } else {
            $maxId = 1;
        }

        $maxId = sprintf("%04d", (int) $maxId);


        $sampleCodeGenerator = [
            'sampleCode' => '',
            'sampleCodeInText' => '',
            'sampleCodeFormat' => '',
            'sampleCodeKey' => '',
            'maxId' => $maxId,
            'mnthYr' => $mnthYr,
            'auto' => $autoFormatedString
        ];

        // PNG format has an additional R in prefix
        if ($formId == 5) {
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

        $checkQuery = "SELECT $sampleCodeCol, $sampleCodeKeyCol
                        FROM $testTable
                        WHERE $sampleCodeCol= ?";
        $checkResult = $this->db->rawQueryOne($checkQuery, [$sampleCodeGenerator['sampleCode']]);
        if (!empty($checkResult)) {
            error_log("DUP::: Sample Code ====== " . $sampleCodeGenerator['sampleCode']);
            error_log("DUP::: Sample Key Code ====== " . $maxId);
            $params['maxCodeKeyVal'] = $maxId;
            return $this->generateSampleCode($testTable, $params);
        }

        return json_encode($sampleCodeGenerator);
    }
}