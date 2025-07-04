<?php

use App\Exceptions\SystemException;
use App\Utilities\LoggerUtility;
use App\Services\DatabaseService;
use App\Registries\ContainerRegistry;

try {
    /** @var DatabaseService $db */
    $db = ContainerRegistry::get(DatabaseService::class);

    $importedBy = $_SESSION['userId'] ?? null;
    if (empty($importedBy)) {
        throw new SystemException('User ID is not set in session.');
    }

    // Update failed/error results to ON_HOLD
    $db->where('imported_by', $importedBy);
    $db->where("IFNULL(result,'') !=''");
    $db->where("(result LIKE 'fail%' OR result = 'failed' OR result LIKE 'err%' OR result LIKE 'error')");
    $db->update('temp_sample_import', [
        'result_status' => SAMPLE_STATUS\TEST_FAILED
    ]);

    // Update eligible rows to ACCEPTED
    $statusCodes = [
        SAMPLE_STATUS\PENDING_APPROVAL,
        SAMPLE_STATUS\RECEIVED_AT_TESTING_LAB,
        SAMPLE_STATUS\REORDERED_FOR_TESTING,
        SAMPLE_STATUS\RECEIVED_AT_CLINIC
    ];
    $statusCodes = implode(",", $statusCodes);
    $db->where('imported_by', $importedBy);
    $db->where("(IFNULL(result_status,'') = '' OR result_status IN ($statusCodes))");
    $id = $db->update('temp_sample_import', [
        'result_status' => SAMPLE_STATUS\ACCEPTED
    ]);
} catch (Throwable $e) {
    LoggerUtility::log("error", $e->getMessage(), [
        'code' => $e->getCode(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString(),
        'last_db_query' => $db?->getLastQuery(),
        'last_db_error' => $db?->getLastError(),
    ]);
}
