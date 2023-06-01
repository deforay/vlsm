<?php

use App\Exceptions\SystemException;
use App\Services\ApiService;
use App\Services\EidService;
use App\Registries\ContainerRegistry;
use App\Services\CommonService;
use App\Services\UsersService;
use App\Utilities\DateUtility;

session_unset(); // no need of session in json response
ini_set('memory_limit', -1);

try {

    /** @var Slim\Psr7\Request $request */
    $request = $GLOBALS['request'];

    $origJson = (string) $request->getBody();
    $input = $request->getParsedBody();


    /** @var MysqliDb $db */
    $db = ContainerRegistry::get('db');

    /** @var CommonService $general */
    $general = ContainerRegistry::get(CommonService::class);

    /** @var ApiService $app */
    $app = ContainerRegistry::get(ApiService::class);

    /** @var UsersService $usersService */
    $usersService = ContainerRegistry::get(UsersService::class);

    /** @var EidService $eidService */
    $eidService = ContainerRegistry::get(EidService::class);

    $transactionId = $general->generateUUID();
    $globalConfig = $general->getGlobalConfig();
    $vlsmSystemConfig = $general->getSystemConfig();
    $user = null;

    if (empty($input) || empty($input['data'])) {
        throw new SystemException("Invalid request");
    }

    /* For API Tracking params */
    $requestUrl = $_SERVER['HTTP_HOST'];
    $requestUrl .= $_SERVER['REQUEST_URI'];
    $authToken = $general->getAuthorizationBearerToken();
    $user = $usersService->getUserByToken($authToken);
    $roleUser = $usersService->getUserRole($user['user_id']);

    $instanceId = $general->getInstanceId();
    $formId = $general->getGlobalConfig('vl_form');

    /* Update form attributes */
    $version = $general->getSystemConfig('sc_version');
    $deviceId = $general->getHeader('deviceId');

    $responseData = [];
    foreach ($input['data'] as $rootKey => $data) {

        $mandatoryFields = ['sampleCollectionDate', 'facilityId', 'appSampleCode'];

        if ($formId == 5) {
            $mandatoryFields[] = 'provinceId';
        }

        if ($app->checkIfNullOrEmpty(array_intersect_key($data, array_flip($mandatoryFields)))) {
            $responseData[$rootKey] = array(
                'transactionId' => $transactionId,
                'appSampleCode' => $data['appSampleCode'] ?? null,
                'status' => 'failed',
                'message' => _("Missing required fields")
            );
            continue;
        }


        if (!empty($data['provinceId']) && !is_numeric($data['provinceId'])) {
            $province = explode("##", $data['provinceId']);
            if (!empty($province)) {
                $data['provinceId'] = $province[0];
            }
            $data['provinceId'] = $general->getValueByName($data['provinceId'], 'geo_name', 'geographical_divisions', 'geo_id', true);
        }
        if (!is_numeric($data['implementingPartner'])) {
            $data['implementingPartner'] = $general->getValueByName($data['implementingPartner'], 'i_partner_name', 'r_implementation_partners', 'i_partner_id');
        }
        if (!is_numeric($data['fundingSource'])) {
            $data['fundingSource'] = $general->getValueByName($data['fundingSource'], 'funding_source_name', 'r_funding_sources', 'funding_source_id');
        }

        $data['api'] = "yes";

        $provinceCode = (!empty($data['provinceCode'])) ? $data['provinceCode'] : null;
        $provinceId = (!empty($data['provinceId'])) ? $data['provinceId'] : null;
        $sampleCollectionDate = $data['sampleCollectionDate'] = DateUtility::isoDateFormat($data['sampleCollectionDate'], true);

        $update = "no";
        $rowData = null;
        $uniqueId = null;
        if (!empty($data['uniqueId']) || !empty($data['appSampleCode'])) {

            $sQuery = "SELECT eid_id,
                            unique_id,
                            sample_code,
                            sample_code_format,
                            sample_code_key,
                            remote_sample_code,
                            remote_sample_code_format,
                            remote_sample_code_key,
                            result_status,
                            locked
                            FROM form_eid ";

            $sQueryWhere = [];

            if (!empty($data['uniqueId'])) {
                $uniqueId = $data['uniqueId'];
                $sQueryWhere[] = " unique_id like '" . $data['uniqueId'] . "'";
            }

            if (!empty($data['appSampleCode'])) {
                $sQueryWhere[] = " app_sample_code like '" . $data['appSampleCode'] . "'";
            }

            if (!empty($sQueryWhere)) {
                $sQuery .= " WHERE " . implode(" AND ", $sQueryWhere);
            }
            $rowData = $db->rawQueryOne($sQuery);
            if (!empty($rowData)) {
                if ($rowData['result_status'] == 7 || $rowData['locked'] == 'yes') {
                    $responseData[$rootKey] = array(
                        'transactionId' => $transactionId,
                        'appSampleCode' => $data['appSampleCode'] ?? null,
                        'status' => 'failed',
                        'error' => _("Sample Locked or Finalized")
                    );
                    continue;
                }
                $update = "yes";
                $uniqueId = $data['uniqueId'] = $rowData['unique_id'];
            }
        }

        if (empty($uniqueId) || $uniqueId === 'undefined' || $uniqueId === 'null') {
            $uniqueId = $data['uniqueId'] = $general->generateUUID();
        }
        $formAttributes = array(
            'applicationVersion'    => $version,
            'apiTransactionId'      => $transactionId,
            'mobileAppVersion'      => $input['appVersion'],
            'deviceId'              => $deviceId
        );
        $formAttributes = json_encode($formAttributes);

        if (!empty($rowData)) {
            $data['eidSampleId'] = $rowData['eid_id'];
        } else {
            $params['appSampleCode'] = $data['appSampleCode'] ?? null;
            $params['provinceCode'] = $provinceCode;
            $params['provinceId'] = $provinceId;
            $params['uniqueId'] = $uniqueId;
            $params['sampleCollectionDate'] = $sampleCollectionDate;
            $params['userId'] = $user['user_id'];
            $params['facilityId'] = $data['facilityId'] ?? null;
            $params['labId'] = $data['labId'] ?? null;

            $data['eidSampleId'] = $eidService->insertSampleCode($params);
        }

        $status = 6;
        if ($roleUser['access_type'] != 'testing-lab') {
            $status = 9;
        }

        if (isset($data['isSampleRejected']) && $data['isSampleRejected'] == "yes") {
            $data['result'] = null;
            $status = 4;
        } else if (
            isset($globalConfig['eid_auto_approve_api_results']) &&
            $globalConfig['eid_auto_approve_api_results'] == "yes" &&
            (isset($data['isSampleRejected']) && $data['isSampleRejected'] == "no") &&
            (!empty($data['result']))
        ) {
            $status = 7;
        } else if ((isset($data['isSampleRejected']) && $data['isSampleRejected'] == "no") && (!empty($data['result']))) {
            $status = 8;
        }

        if (isset($data['approvedOn']) && trim($data['approvedOn']) != "") {
            $data['approvedOn'] = DateUtility::isoDateFormat($data['approvedOn'], true);
        } else {
            $data['approvedOn'] = null;
        }

        //Set sample received date
        if (!empty($data['sampleReceivedDate']) && trim($data['sampleReceivedDate']) != "") {
            $data['sampleReceivedDate'] = DateUtility::isoDateFormat($data['sampleReceivedDate'], true);
        } else {
            $data['sampleReceivedDate'] = null;
        }
        if (!empty($data['sampleTestedDateTime']) && trim($data['sampleTestedDateTime']) != "") {
            $data['sampleTestedDateTime'] = DateUtility::isoDateFormat($data['sampleTestedDateTime'], true);
        } else {
            $data['sampleTestedDateTime'] = null;
        }

        if (isset($data['rapidtestDate']) && trim($data['rapidtestDate']) != "") {
            $data['rapidtestDate'] = DateUtility::isoDateFormat($data['rapidtestDate']);
        } else {
            $data['rapidtestDate'] = null;
        }

        if (isset($data['childDob']) && trim($data['childDob']) != "") {
            $data['childDob'] = DateUtility::isoDateFormat($data['childDob']);
        } else {
            $data['childDob'] = null;
        }

        if (isset($data['mothersDob']) && trim($data['mothersDob']) != "") {
            $data['mothersDob'] = DateUtility::isoDateFormat($data['mothersDob']);
        } else {
            $data['mothersDob'] = null;
        }


        if (isset($data['motherTreatmentInitiationDate']) && trim($data['motherTreatmentInitiationDate']) != "") {
            $data['motherTreatmentInitiationDate'] = DateUtility::isoDateFormat($data['motherTreatmentInitiationDate']);
        } else {
            $data['motherTreatmentInitiationDate'] = null;
        }

        if (isset($data['previousPCRTestDate']) && trim($data['previousPCRTestDate']) != "") {
            $data['previousPCRTestDate'] = DateUtility::isoDateFormat($data['previousPCRTestDate']);
        } else {
            $data['previousPCRTestDate'] = null;
        }

        if (isset($data['motherViralLoadCopiesPerMl']) && $data['motherViralLoadCopiesPerMl'] != "") {
            $motherVlResult = $data['motherViralLoadCopiesPerMl'];
        } else if (isset($data['motherViralLoadText']) && $data['motherViralLoadText'] != "") {
            $motherVlResult = $data['motherViralLoadText'];
        } else {
            $motherVlResult = null;
        }
        if (isset($data['reviewedOn']) && trim($data['reviewedOn']) != "") {
            $data['reviewedOn'] = DateUtility::isoDateFormat($data['reviewedOn']);
        } else {
            $data['reviewedOn'] = null;
        }

        if (isset($data['resultDispatchedOn']) && trim($data['resultDispatchedOn']) != "") {
            $data['resultDispatchedOn'] = DateUtility::isoDateFormat($data['resultDispatchedOn'], true);
        } else {
            $data['resultDispatchedOn'] = null;
        }

        if (isset($data['sampleDispatchedOn']) && trim($data['sampleDispatchedOn']) != "") {
            $data['sampleDispatchedOn'] = DateUtility::isoDateFormat($data['sampleDispatchedOn'], true);
        } else {
            $data['sampleDispatchedOn'] = null;
        }

        if (!empty($data['revisedOn']) && trim($data['revisedOn']) != "") {
            $data['revisedOn'] = DateUtility::isoDateFormat($data['revisedOn'], true);
        } else {
            $data['revisedOn'] = null;
        }

        $eidData = array(
            'vlsm_instance_id'                                  => $instanceId,
            'vlsm_country_id'                                   => $formId,
            'unique_id'                                         => $uniqueId,
            'app_sample_code'                                   => $data['appSampleCode'] ?? null,
            'facility_id'                                       => $data['facilityId'] ?? null,
            'province_id'                                       => $data['provinceId'] ?? null,
            'lab_id'                                            => $data['labId'] ?? null,
            'implementing_partner'                              => $data['implementingPartner'] ?? null,
            'funding_source'                                    => $data['fundingSource'] ?? null,
            'mother_id'                                         => $data['mothersId'] ?? null,
            'caretaker_contact_consent'                         => $data['caretakerConsentForContact'] ?? null,
            'caretaker_phone_number'                            => $data['caretakerPhoneNumber'] ?? null,
            'caretaker_address'                                 => $data['caretakerAddress'] ?? null,
            'mother_name'                                       => (!empty($data['mothersName']) && $data['mothersName'] != 'undefined') ? $data['mothersName'] : null,
            'mother_dob'                                        => $data['mothersDob'] ?? null,
            'mother_marital_status'                             => $data['mothersMaritalStatus'] ?? null,
            'mother_treatment'                                  => isset($data['motherTreatment']) ? implode(",", $data['motherTreatment']) : null,
            'mother_treatment_other'                            => $data['motherTreatmentOther'] ?? null,
            'mother_treatment_initiation_date'                  => $data['motherTreatmentInitiationDate'] ?? null,
            'child_id'                                          => $data['childId'] ?? null,
            'child_name'                                        => $data['childName'] ?? null,
            'child_surname'                                     => $data['childSurName'] ?? null,
            'child_dob'                                         => $data['childDob'] ?? null,
            'child_gender'                                      => $data['childGender'] ?? null,
            'child_age'                                         => $data['childAge'] ?? null,
            'child_treatment'                                   => isset($data['childTreatment']) ? implode(",", $data['childTreatment']) : null,
            'child_treatment_other'                             => isset($data['childTreatmentOther']) ? implode(",", $data['childTreatmentOther']) : null,
            'mother_cd4'                                        => $data['mothercd4'] ?? null,
            'mother_vl_result'                                  => $motherVlResult,
            'mother_hiv_status'                                 => $data['mothersHIVStatus'] ?? null,
            'pcr_test_performed_before'                         => $data['pcrTestPerformedBefore'] ?? null,
            'previous_pcr_result'                               => $data['prePcrTestResult'] ?? null,
            'last_pcr_date'                                     => $data['previousPCRTestDate'] ?? null,
            'reason_for_pcr'                                    => $data['pcrTestReason'] ?? null,
            'has_infant_stopped_breastfeeding'                  => $data['hasInfantStoppedBreastfeeding'] ?? null,
            'age_breastfeeding_stopped_in_months'               => $data['ageBreastfeedingStopped'] ?? null,
            'choice_of_feeding'                                 => $data['choiceOfFeeding'] ?? null,
            'is_cotrimoxazole_being_administered_to_the_infant' => $data['isCotrimoxazoleBeingAdministered'] ?? null,
            'specimen_type'                                     => $data['specimenType'] ?? null,
            'sample_collection_date'                            => $sampleCollectionDate,
            'sample_dispatched_datetime'                        => $data['sampleDispatchedOn'],
            'result_dispatched_datetime'                        => $data['resultDispatchedOn'],
            'sample_requestor_phone'                            => $data['sampleRequestorPhone'] ?? null,
            'sample_requestor_name'                             => $data['sampleRequestorName'] ?? null,
            'rapid_test_performed'                              => $data['rapidTestPerformed'] ?? null,
            'rapid_test_date'                                   => $data['rapidtestDate'] ?? null,
            'rapid_test_result'                                 => $data['rapidTestResult'] ?? null,
            'lab_reception_person'                              => $data['labReceptionPerson'] ?? null,
            'sample_received_at_vl_lab_datetime'                => $data['sampleReceivedDate'] ?? null,
            'eid_test_platform'                                 => $data['eidPlatform'] ?? null,
            'import_machine_name'                               => $data['machineName'] ?? null,
            'sample_tested_datetime'                            => $data['sampleTestedDateTime'] ?? null,
            'is_sample_rejected'                                => $data['isSampleRejected'] ?? null,
            'result'                                            => $data['result'] ?? null,
            'tested_by'                                         => (isset($data['testedBy']) && $data['testedBy'] != '') ? $data['testedBy'] :  $user['user_id'],
            'result_approved_by'                                => (isset($data['approvedBy']) && $data['approvedBy'] != '') ? $data['approvedBy'] :  null,
            'result_approved_datetime'                          => (isset($data['approvedOn']) && $data['approvedOn'] != '') ? $data['approvedOn'] :  null,
            'lab_tech_comments'                                 => !empty($data['approverComments']) ? $data['approverComments'] : null,
            'result_reviewed_by'                                => (isset($data['reviewedBy']) && $data['reviewedBy'] != "") ? $data['reviewedBy'] : null,
            'result_reviewed_datetime'                          => (isset($data['reviewedOn']) && $data['reviewedOn'] != "") ? $data['reviewedOn'] : null,
            'revised_by'                                        => (isset($data['revisedBy']) && $data['revisedBy'] != "") ? $data['revisedBy'] : "",
            'revised_on'                                        => (isset($data['revisedOn']) && $data['revisedOn'] != "") ? $data['revisedOn'] : "",
            'reason_for_changing'                               => (!empty($data['reasonForEidResultChanges'])) ? $data['reasonForEidResultChanges'] : null,
            'result_status'                                     => $status,
            'data_sync'                                         => 0,
            'reason_for_sample_rejection'                       => $data['sampleRejectionReason'] ?? null,
            'rejection_on'                                      => (isset($data['rejectionDate']) && $data['isSampleRejected'] == 'yes') ? DateUtility::isoDateFormat($data['rejectionDate']) : null,
            'source_of_request'                                 => $data['sourceOfRequest'] ?? "API",
            'form_attributes'                                   => $db->func($general->jsonToSetString($formAttributes, 'form_attributes'))
        );

        if (!empty($rowData)) {
            $eidData['last_modified_datetime']  = (!empty($data['updatedOn'])) ? DateUtility::isoDateFormat($data['updatedOn'], true) : DateUtility::getCurrentDateTime();
            $eidData['last_modified_by']  = $user['user_id'];
        } else {
            $eidData['request_created_datetime']  = (!empty($data['createdOn'])) ? DateUtility::isoDateFormat($data['createdOn'], true) : DateUtility::getCurrentDateTime();
            $eidData['sample_registered_at_lab']  = DateUtility::getCurrentDateTime();
            $eidData['request_created_by']  = $user['user_id'];
        }

        $id = false;
        if (!empty($data['eidSampleId'])) {
            $db = $db->where('eid_id', $data['eidSampleId']);
            $id = $db->update('form_eid', $eidData);
            error_log($db->getLastError());
        }

        if ($id === true) {
            $sQuery = "SELECT sample_code,
                            remote_sample_code
                            FROM form_eid
                            WHERE eid_id = ?";
            $sampleRow = $db->rawQueryOne($sQuery, [$data['eidSampleId']]);

            $eidSampleCode = $sampleRow['sample_code'] ?? $sampleRow['remote_sample_code'] ?? null;

            $responseData[$rootKey] = [
                'status' => 'success',
                'sampleCode' => $eidSampleCode,
                'transactionId' => $transactionId,
                'uniqueId' => $uniqueId,
                'appSampleCode' => $data['appSampleCode'] ?? null,
            ];
        } else {
            $responseData[$rootKey] = [
                'transactionId' => $transactionId,
                'status' => 'failed',
                'appSampleCode' => $data['appSampleCode'] ?? null,
                'error' => $db->getLastError()
            ];
        }
    }

    $payload = [
        'status' => 'success',
        'transactionId' => $transactionId,
        'timestamp' => time(),
        'data'  => $responseData ?? []
    ];
} catch (SystemException $exc) {

    http_response_code(400);
    $payload = [
        'status' => 'failed',
        'timestamp' => time(),
        'error' => $exc->getMessage(),
        'data' => []
    ];

    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}
$payload = json_encode($payload);
$general->addApiTracking($transactionId, $user['user_id'], count($input['data']), 'save-request', 'eid', $_SERVER['REQUEST_URI'], $origJson, $payload, 'json');
echo $payload;
