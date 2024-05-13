<?php

use App\Utilities\DateUtility;
use App\Services\CommonService;
use App\Services\AppMenuService;
use App\Utilities\LoggerUtility;
use App\Services\DatabaseService;
use App\Registries\ContainerRegistry;

// only run from command line
if (php_sapi_name() !== 'cli') {
    exit(0);
}

require_once(__DIR__ . '/../bootstrap.php');

/** @var DatabaseService $db */
$db = ContainerRegistry::get(DatabaseService::class);

/** @var CommonService $general */
$general = ContainerRegistry::get(CommonService::class);

/** @var AppMenuService $appMenuService */
$appMenuService = ContainerRegistry::get(AppMenuService::class);

// Array of keys
$menuKeys = [
    'module',
    'sub_module',
    'is_header',
    'display_text',
    'link',
    'inner_pages',
    'show_mode',
    'icon',
    'has_children',
    'additional_class_names',
    'parent_id',
    'display_order',
    'status',
    'updated_datetime'
];


try {
    // $deleteCd4 = $db->rawQuery("DELETE FROM s_app_menu WHERE module='cd4'");
    // $getCD4Config = $db->rawQueryOne("SELECT id FROM s_app_menu WHERE display_text='CD4 Config'");
    // $getCD4ConfigIdDelete = $getCD4Config['id'] ?? null;

    // if (!empty($getCD4ConfigIdDelete)) {
    //     $deleteCd4ConfigChildren = $db->rawQuery("DELETE FROM s_app_menu WHERE parent_id = $getCD4ConfigIdDelete");
    //     $deleteCd4Config = $db->rawQuery("DELETE FROM s_app_menu WHERE id = $getCD4ConfigIdDelete");
    // }

    /** Insert CD4 Header Menu */
    //$insertHeader = $db->rawQuery("INSERT INTO `s_app_menu` (`id`, `module`, `sub_module`, `is_header`, `display_text`, `link`, `inner_pages`, `show_mode`, `icon`, `has_children`, `additional_class_names`, `parent_id`, `display_order`, `status`, `updated_datetime`) VALUES (NULL, 'cd4', NULL, 'yes', 'CLUSTERS OF DIFFERENTIATION 4', NULL, NULL, 'always', NULL, 'yes', 'header', '0', '179', 'active', DateUtility::getCurrentDateTime())");

    $menuValues = ['cd4', NULL, 'yes', 'CLUSTERS OF DIFFERENTIATION 4', '#cd4', NULL, 'always', NULL, 'yes', 'header', '0', '179', 'active', DateUtility::getCurrentDateTime()];
    $menuData = array_combine($menuKeys, $menuValues);

    $headerId = $appMenuService->insertMenu($menuData);

    /** Insert Submenu of CD4 Module */
    $menuValues = ['cd4', NULL, 'no', 'Request Management', '#cd4-request-management', NULL, 'always', 'fa-solid fa-pen-to-square', 'yes', 'treeview request', $headerId, 1, 'active', DateUtility::getCurrentDateTime()];
    $menuData = array_combine($menuKeys, $menuValues);
    $requestMenuId = $appMenuService->insertMenu($menuData);

    $menuValues = ['cd4', NULL, 'no', 'Test Result Management', '#cd4-result-management', NULL, 'always', 'fa-solid fa-list-check', 'yes', 'treeview test', $headerId, 2, 'active', DateUtility::getCurrentDateTime()];
    $menuData = array_combine($menuKeys, $menuValues);
    $resultMenuId = $appMenuService->insertMenu($menuData);

    $menuValues = ['cd4', NULL, 'no', 'Management', '#cd4-program-management', NULL, 'always', 'fa-solid fa-book', 'yes', 'treeview program', $headerId, 3, 'active', DateUtility::getCurrentDateTime()];
    $menuData = array_combine($menuKeys, $menuValues);
    $managementMenuId = $appMenuService->insertMenu($menuData);


    /** Adding Request Submenu */
    $menuValues = ['cd4', NULL, 'no', 'View Test Request', '/cd4/requests/cd4-requests.php', '/cd4/requests/cd4-edit-request.php', 'always', 'fa-solid fa-caret-right', 'no', 'allMenu cd4RequestMenu', $requestMenuId, 4, 'active', DateUtility::getCurrentDateTime()];
    $menuData = array_combine($menuKeys, $menuValues);
    $appMenuService->insertMenu($menuData);

    $menuValues = ['cd4', NULL, 'no', 'Add New Request', '/cd4/requests/cd4-add-request.php', NULL, 'always', 'fa-solid fa-caret-right', 'no', 'allMenu addVlRequestMenu', $requestMenuId, 5, 'active', DateUtility::getCurrentDateTime()];
    $menuData = array_combine($menuKeys, $menuValues);
    $appMenuService->insertMenu($menuData);

    $menuValues = ['cd4', NULL, 'no', 'Manage Batch', '/batch/batches.php?type=cd4', '/batch/add-batch.php?type=cd4,/batch/edit-batch.php?type=cd4,/batch/add-batch-position.php?type=cd4,/batch/edit-batch-position.php?type=cd4', 'always', 'fa-solid fa-caret-right', 'no', 'allMenu cd4BatchCodeMenu', $requestMenuId, 6, 'active', DateUtility::getCurrentDateTime()];
    $menuData = array_combine($menuKeys, $menuValues);
    $appMenuService->insertMenu($menuData);

    $menuValues = ['cd4', NULL, 'no', 'CD4 Manifest', '/specimen-referral-manifest/view-manifests.php?t=cd4', '/specimen-referral-manifest/add-manifest.php?t=cd4,/specimen-referral-manifest/edit-manifest.php?t=cd4,/specimen-referral-manifest/move-manifest.php?t=cd4', 'sts', 'fa-solid fa-caret-right', 'no', 'allMenu cd4BatchCodeMenu', $requestMenuId, 7, 'active', DateUtility::getCurrentDateTime()];
    $menuData = array_combine($menuKeys, $menuValues);
    $appMenuService->insertMenu($menuData);

    $menuValues = ['cd4', NULL, 'no', 'Add Samples from Manifest', '/cd4/requests/add-samples-from-manifest.php', NULL, 'lis', 'fa-solid fa-caret-right', 'no', 'allMenu addSamplesFromManifestMenu', $requestMenuId, 8, 'active', DateUtility::getCurrentDateTime()];
    $menuData = array_combine($menuKeys, $menuValues);
    $appMenuService->insertMenu($menuData);

    /** Adding Result Submenu */
    $menuValues = ['cd4', NULL, 'no', 'Enter Result Manually', '/cd4/results/cd4-manual-results.php', '/cd4/results/cd4-update-result.php', 'always', 'fa-solid fa-caret-right', 'no', 'allMenu cd4ResultStatus', $resultMenuId, 9, 'active', DateUtility::getCurrentDateTime()];
    $menuData = array_combine($menuKeys, $menuValues);
    $appMenuService->insertMenu($menuData);

    $menuValues = ['cd4', NULL, 'no', 'Manage Results Status', '/cd4/results/cd4-result-status.php', NULL, 'always', 'fa-solid fa-caret-right', 'no', 'allMenu batchCodeMenu', $resultMenuId, 10, 'active', DateUtility::getCurrentDateTime()];
    $menuData = array_combine($menuKeys, $menuValues);
    $appMenuService->insertMenu($menuData);

    $menuValues = ['cd4', NULL, 'no', 'Import Result From File', '/import-result/import-file.php?t=cd4', '/import-result/imported-results.php?t=cd4,/import-result/importedStatistics.php?t=cd4', 'always', 'fa-solid fa-caret-right', 'no', 'allMenu cd4ImportResultMenu', $resultMenuId, 11, 'active', DateUtility::getCurrentDateTime()];
    $menuData = array_combine($menuKeys, $menuValues);
    $appMenuService->insertMenu($menuData);

    $menuValues = ['cd4', NULL, 'no', 'Failed/Hold Samples', '/cd4/results/cd4-failed-results.php', NULL, 'always', 'fa-solid fa-caret-right', 'no', 'allMenu cd4FailedResultsMenu', $resultMenuId, 12, 'active', DateUtility::getCurrentDateTime()];
    $menuData = array_combine($menuKeys, $menuValues);
    $appMenuService->insertMenu($menuData);

    $menuValues = ['cd4', NULL, 'no', 'E-mail Test Result', '/cd4/results/email-results.php', NULL, 'always', 'fa-solid fa-caret-right', 'no', 'allMenu cd4ResultMailMenu', $resultMenuId, 13, 'active', DateUtility::getCurrentDateTime()];
    $menuData = array_combine($menuKeys, $menuValues);
    $appMenuService->insertMenu($menuData);


    /** Adding Management Submenu */
    $menuValues = ['cd4', NULL, 'no', 'Sample Status Report', '/cd4/management/cd4-sample-status.php', NULL, 'always', 'fa-solid fa-caret-right', 'no', 'allMenu cd4SampleStatus', $managementMenuId, 14, 'active', DateUtility::getCurrentDateTime()];
    $menuData = array_combine($menuKeys, $menuValues);
    $appMenuService->insertMenu($menuData);

    $menuValues = ['cd4', NULL, 'no', 'Export Results', '/cd4/management/cd4-export-data.php', NULL, 'always', 'fa-solid fa-caret-right', 'no', 'allMenu cd4ExportResult', $managementMenuId, 15, 'active', DateUtility::getCurrentDateTime()];
    $menuData = array_combine($menuKeys, $menuValues);
    $appMenuService->insertMenu($menuData);

    $menuValues = ['cd4', NULL, 'no', 'Print Result', '/cd4/results/cd4-print-results.php', NULL, 'always', 'fa-solid fa-caret-right', 'no', 'allMenu cd4PrintResults', $managementMenuId, 16, 'active', DateUtility::getCurrentDateTime()];
    $menuData = array_combine($menuKeys, $menuValues);
    $appMenuService->insertMenu($menuData);

    $menuValues = ['cd4', NULL, 'no', 'Sample Rejection Report', '/cd4/management/cd4-sample-rejection-report.php', NULL, 'always', 'fa-solid fa-caret-right', 'no', 'allMenu cd4SampleRejectionReport', $managementMenuId, 16, 'active', DateUtility::getCurrentDateTime()];
    $menuData = array_combine($menuKeys, $menuValues);
    $appMenuService->insertMenu($menuData);

    $menuValues = ['cd4', NULL, 'no', 'Clinic Report', '/cd4/management/cd4-clinic-report.php', NULL, 'always', 'fa-solid fa-caret-right', 'no', 'allMenu cd4ClinicReport', $managementMenuId, 17, 'active', DateUtility::getCurrentDateTime()];
    $menuData = array_combine($menuKeys, $menuValues);
    $appMenuService->insertMenu($menuData);


    /** Adding CD4 Config */
    $getAdminModule = $db->rawQueryOne("SELECT id FROM s_app_menu WHERE display_text='ADMIN'");
    $adminModuleId = $getAdminModule['id'];
    $menuValues = ['admin', 'cd4', 'no', 'CD4 Config', 'cd4-config', NULL, 'always', 'fa-solid fa-eyedropper', 'yes', 'treeview tb-reference-manage', $adminModuleId, 42, 'active', DateUtility::getCurrentDateTime()];
    $menuData = array_combine($menuKeys, $menuValues);
    $cd4ConfigId = $appMenuService->insertMenu($menuData);

    $menuValues = ['admin', 'cd4', 'no', 'Sample Type', '/cd4/reference/cd4-sample-type.php', '/cd4/reference/add-cd4-sample-type.php,/cd4/reference/edit-cd4-sample-type.php', 'always', 'fa-solid fa-caret-right', 'no', 'allMenu cd4-sample-type', $cd4ConfigId, 43, 'active', DateUtility::getCurrentDateTime()];
    $menuData = array_combine($menuKeys, $menuValues);
    $appMenuService->insertMenu($menuData);

    $menuValues = ['admin', 'cd4', 'no', 'Test Reasons', '/cd4/reference/cd4-test-reasons.php', '/cd4/reference/add-cd4-test-reasons.php,/cd4/reference/edit-cd4-test-reasons.php', 'always', 'fa-solid fa-caret-right', 'no', 'allMenu vl-test-reasons', $cd4ConfigId, 44, 'active', DateUtility::getCurrentDateTime()];
    $menuData = array_combine($menuKeys, $menuValues);
    $appMenuService->insertMenu($menuData);

    $menuValues = ['admin', 'cd4', 'no', 'Rejection Reasons', '/cd4/reference/cd4-sample-rejection-reasons.php', '/cd4/reference/add-cd4-sample-rejection-reasons.php,/cd4/reference/edit-cd4-sample-rejection-reasons.php', 'always', 'fa-solid fa-caret-right', 'no', 'allMenu cd4-test-reasons', $cd4ConfigId, 45, 'active', DateUtility::getCurrentDateTime()];
    $menuData = array_combine($menuKeys, $menuValues);
    $appMenuService->insertMenu($menuData);

    /** Adding Lab storage menu under system settings */
    $systemConfig = $db->rawQueryOne("SELECT id FROM s_app_menu WHERE display_text='System Configuration'");
    $systemConfigId = $systemConfig['id'];

    $menuValues = ['admin', NULL, 'no', 'Lab Storage', '/common/reference/lab-storage.php', '/common/reference/add-lab-storage.php', 'lis', 'fa-solid fa-caret-right', 'no', 'allMenu common-reference-lab-storage', $systemConfigId, 24, 'active', DateUtility::getCurrentDateTime()];
    $menuData = array_combine($menuKeys, $menuValues);
    $appMenuService->insertMenu($menuData);


    /** Sample Storage Reports menu under vl->Management */
    $vlManagement = $db->rawQueryOne("SELECT id FROM s_app_menu WHERE module='vl' AND display_text='Management'");
    $vlManagementId = $vlManagement['id'];

    $menuData = [
        'module' => 'vl',
        'sub_module' => null,
        'is_header' => 'no',
        'display_text' => 'Freezer/Storage Reports',
        'link' => '/vl/program-management/sample-storage-reports.php',
        'inner_pages' => null,
        'show_mode' => 'lis',
        'icon' => 'fa-solid fa-caret-right',
        'has_children' => 'no',
        'additional_class_names' => 'allMenu vlStorageMenu',
        'parent_id' => $vlManagementId,
        'display_order' => 109,
        'status' => 'active',
        'updated_datetime' => DateUtility::getCurrentDateTime()
    ];

    $appMenuService->insertMenu($menuData);
} catch (Exception $e) {
    $message = "Exception : " . $e->getMessage() . PHP_EOL;
    LoggerUtility::log('error', $message);
    if (!empty($db->getLastError())) {
        LoggerUtility::log('error', $db->getLastError());
    }

    echo $message;
}
