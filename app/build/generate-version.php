#!/usr/bin/env php
<?php

// only run from command line
if (php_sapi_name() !== 'cli') {
    exit(0);
}

require_once __DIR__ . "/../../bootstrap.php";

use App\Services\CommonService;
use App\Registries\ContainerRegistry;

/** @var CommonService $general */
$general = ContainerRegistry::get(CommonService::class);

$currentMajorVersion = $general->getAppVersion();

$versionFilePath = APPLICATION_PATH . '/system/version.php';

// Function to extract the version number from the version.php file
function getCurrentVersion($versionFilePath)
{
    if (!file_exists($versionFilePath)) {
        return null;
    }

    $versionContent = file_get_contents($versionFilePath);
    if (preg_match("/define\('VERSION', '([\d\.]+)'\);/", $versionContent, $matches)) {
        return $matches[1];
    }
    return null;
}
try {
    // Get the current version from version.php
    $currentVersion = getCurrentVersion($versionFilePath);

    if ($currentVersion === null) {
        // If version.php does not exist or has issues, initialize it with major.minor.patch.1
        $newVersion = "{$currentMajorVersion}.1";
    } else {
        // Extract the major.minor.patch part and the build number
        $currentVersionParts = explode('.', $currentVersion);

        // Extract the current major.minor.patch version from the file
        $currentVersionCore = implode('.', array_slice($currentVersionParts, 0, 3));

        // Extract the current build number
        $currentBuildNumber = intval(end($currentVersionParts));

        if ($currentVersionCore !== $currentMajorVersion) {
            // If the major.minor.patch version has changed, reset the build number to 1
            $newVersion = "{$currentMajorVersion}.1";
        } else {
            // Otherwise, increment the build number
            $newVersion = $currentMajorVersion . '.' . ($currentBuildNumber + 1);
        }
    }

    // Generate the content for version.php
    $versionFileContent = <<<PHP
<?php

// DO NOT MODIFY THIS FILE
// Version is defined in composer.json
// This file is automatically generated by the build process
defined('VERSION')
    || define('VERSION', '$newVersion');

PHP;

    // Write the new version content to version.php
    file_put_contents($versionFilePath, $versionFileContent);

    echo "version.php has been updated to version " . htmlspecialchars($newVersion) . "\n";
} catch (Throwable $e) {
    LoggerUtility::logError($e->getMessage(), [
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'last_db_query' => $db->getLastQuery(),
        'last_db_error' => $db->getLastError(),
        'trace' => $e->getTraceAsString(),
    ]);
}
