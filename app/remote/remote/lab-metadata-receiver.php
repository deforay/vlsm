<?php

use JsonMachine\Items;
use App\Services\ApiService;
use App\Utilities\DateUtility;
use App\Utilities\JsonUtility;
use App\Utilities\MiscUtility;
use App\Registries\AppRegistry;
use App\Services\CommonService;
use App\Utilities\LoggerUtility;
use App\Services\DatabaseService;
use App\Exceptions\SystemException;
use App\Registries\ContainerRegistry;
use JsonMachine\JsonDecoder\ExtJsonDecoder;

require_once(dirname(__FILE__) . "/../../../bootstrap.php");

ini_set('memory_limit', -1);
set_time_limit(0);
ini_set('max_execution_time', 300000);

/** @var DatabaseService $db */
$db = ContainerRegistry::get(DatabaseService::class);

/** @var CommonService $general */
$general = ContainerRegistry::get(CommonService::class);

/** @var ApiService $apiService */
$apiService = ContainerRegistry::get(ApiService::class);

try {
    $db->beginTransaction();

    /** @var Laminas\Diactoros\ServerRequest $request */
    $request = AppRegistry::get('request');
    $jsonResponse = $apiService->getJsonFromRequest($request);
    $counter = 0;

    $labId = null;
    if (!empty($jsonResponse) && $jsonResponse != '[]' && JsonUtility::isJSON($jsonResponse)) {

        $data = [];
        $options = [
            'decoder' => new ExtJsonDecoder(true)
        ];
        $parsedData = Items::fromString($jsonResponse, $options);
        $tableInfo = [];
        $i = 1;
        foreach ($parsedData as $name => $data) {
            if ($name === 'transactionId') {
                $transactionId = $data;
            } elseif ($name === 'labId') {
                $labId = $data;
            } elseif ($name === 'labStorage') {
                $tableInfo['primaryKey'][$i] = 'storage_id';
                $tableInfo['table'][$i] = 'lab_storage';
            } elseif ($name === 'labStorageHistory') {
                $tableInfo['primaryKey'][$i] = 'history_id';
                $tableInfo['table'][$i] = 'lab_storage_history';
            } elseif ($name === 'instruments') {
                $tableInfo['primaryKey'][$i] = 'instrument_id';
                $tableInfo['table'][$i] = 'instruments';
            } elseif ($name === 'instrumentMachines') {
                $tableInfo['primaryKey'][$i] = 'config_machine_id';
                $tableInfo['table'][$i] = 'instrument_machines';
            } elseif ($name === 'instrumentControls') {
                $tableInfo['primaryKey'][$i] = 'instrument_id';
                $tableInfo['table'][$i] = 'instrument_controls';
            } elseif ($name === 'patients') {
                $tableInfo['primaryKey'][$i] = 'system_patient_code';
                $tableInfo['table'][$i] = 'patients';
            } elseif ($name === 'users') {
                $tableInfo['primaryKey'][$i] = 'user_id';
                $tableInfo['table'][$i] = 'user_details';
            }
            $tableInfo['data'][$i] = $data;
            $i++;
        }

        $transactionId ??= MiscUtility::generateUUID();
        if (!empty($tableInfo)) {
            foreach ($tableInfo['table'] as $j => $table) {
                $emptyTableArray = $general->getTableFieldsAsArray($table);
                if (empty($emptyTableArray)) {
                    continue;
                }
                $deletedId = [];
                foreach ($tableInfo['data'][$j] as $key => $resultRow) {
                    $counter++;
                    $data = MiscUtility::updateFromArray($emptyTableArray, $resultRow);
                    $data['updated_datetime'] = DateUtility::getCurrentDateTime();
                    $primaryKey = $checkColumn = $tableInfo['primaryKey'][$j];
                    $tableName = $tableInfo['table'][$j];
                    try {
                        if ($tableName == 'instrument_controls' || $tableName == 'instrument_machines') {
                            if ((in_array($data['instrument_id'], $deletedId)) == false) {
                                $deletedId[] = $data['instrument_id'];
                                $db->where('instrument_id', $data['instrument_id']);
                                $db->delete($tableName);
                            }
                            $id = $db->insert($tableName, $data);
                        } else {
                            if ($tableName == 'user_details' && !empty($data['signature_image_content']) && !empty($data['signature_image_filename'])) {
                                $signatureImagePathBase = UPLOAD_PATH . DIRECTORY_SEPARATOR . "users-signature";
                                MiscUtility::makeDirectory($signatureImagePathBase);
                                $signatureImagePathBase = realpath($signatureImagePathBase);

                                $signatureImage = base64_decode($data['signature_image_content']);
                                $signatureImagePath = $signatureImagePathBase . DIRECTORY_SEPARATOR . $data['signature_image_filename'];
                                file_put_contents($signatureImagePath, $signatureImage);
                                unset($data['signature_image_content']);
                                unset($data['signature_image_filename']);
                            }

                            $sResult = [];
                            if (!empty($data[$checkColumn])) {
                                $sQuery = "SELECT $primaryKey FROM $tableName WHERE $checkColumn = ?";
                                $sResult = $db->rawQueryOne($sQuery, $data[$checkColumn]);
                            }
                            if (!empty($sResult)) {
                                $db->where($primaryKey, $sResult[$primaryKey]);
                                $id = $db->update($tableName, $data);
                            } else {
                                $id = $db->insert($tableName, $data);
                            }
                        }
                    } catch (Throwable $e) {
                        LoggerUtility::log('error', (__FILE__ . ":" . __LINE__ . ":" . $db->getLastErrno()));
                        LoggerUtility::log('error', (__FILE__ . ":" . __LINE__ . ":" . $db->getLastError()));
                        LoggerUtility::log('error', (__FILE__ . ":" . __LINE__ . ":" . $db->getLastQuery()));
                        LoggerUtility::log('error', $e->getFile() . ":" . $e->getLine() . " - " . $e->getMessage());
                        continue;
                    }
                }
            }
        }
    }

    $payload = json_encode([
        'status' => 'success',
        'message' => 'Metadata synced successfully'
    ]);

    $general->addApiTracking($transactionId, 'vlsm-system', $counter, 'system-metadata-sync', 'common', $_SERVER['REQUEST_URI'], $jsonResponse, $payload, 'json', $labId);
    $db->commitTransaction();
} catch (Throwable $e) {
    $db->rollbackTransaction();

    $payload = json_encode([]);

    if (!empty($db->getLastError())) {
        error_log('Error in lab-metadata-receiver.php : ' . $db->getLastErrno());
        error_log('Error in lab-metadata-receiver.php : ' . $db->getLastError());
        error_log('Error in lab-metadata-receiver.php : ' . $db->getLastQuery());
    }
    throw new SystemException($e->getFile() . ":" . $e->getLine() . " - " . $e->getMessage(), $e->getCode(), $e);
}

echo $apiService->sendJsonResponse($payload);
