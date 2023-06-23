<?php

namespace App\Utilities;

use ZipArchive;

class MiscUtility
{

    public static function randomHexColor(): string
    {
        $hexColorPart = function () {
            return str_pad(dechex(random_int(0, 255)), 2, '0', STR_PAD_LEFT);
        };

        return strtoupper($hexColorPart() . $hexColorPart() . $hexColorPart());
    }

    public static function removeDirectory($dirname): bool
    {
        // Sanity check
        if (!file_exists($dirname)) {
            return false;
        }

        // Simple delete for a file
        if (is_file($dirname) || is_link($dirname)) {
            return unlink($dirname);
        }

        // Loop through the folder
        $dir = dir($dirname);
        while (false !== ($entry = $dir->read())) {
            // Skip pointers
            if ($entry == '.' || $entry == '..') {
                continue;
            }

            // Recurse
            self::removeDirectory($dirname . DIRECTORY_SEPARATOR . $entry);
        }

        // Clean up
        $dir->close();
        return rmdir($dirname);
    }

    //dump the contents of a variable to the error log in a readable format
    public static function errorLog($object = null): void
    {
        ob_start();
        var_dump($object);
        error_log(ob_get_clean());
    }

    /**
     * Checks if the array contains any null or empty string values.
     *
     * @param array $array The array to check.
     * @return bool Returns true if any value is null or an empty string, false otherwise.
     */
    public static function hasEmpty(array $array): bool
    {
        foreach ($array as $value) {
            if ($value === null || trim($value) === "") {
                return true;
            }
        }
        return false;
    }

    public static function prettyJson($json): string
    {
        if (is_array($json)) {
            $json = json_encode($json, JSON_PRETTY_PRINT);
        } else {
            $json = json_encode(json_decode($json), JSON_PRETTY_PRINT);
        }
        return htmlspecialchars($json, ENT_QUOTES, 'UTF-8');
    }
    /**
     * Unzips a JSON file and displays its contents in a pretty format.
     *
     * @param string $zipFile The path to the zip file.
     * @param string $jsonFile The name of the JSON file inside the zip archive.
     */
    public static function getJsonFromZip($zipFile, $jsonFile): string
    {
        if (!file_exists($zipFile)) {
            return "{}";
        }
        $zip = new ZipArchive;
        if ($zip->open($zipFile) === true) {
            $json = $zip->getFromName($jsonFile);
            $zip->close();

            return $json;
        } else {
            return "{}";
        }
    }

    /**
     * Zips a JSON string.
     *
     * @param string $json The JSON string to zip.
     * @param string $fileName The FULL PATH of the file inside the zip archive.
     * @return bool Returns true on success, false on failure.
     */
    public static function zipJson($json, $fileName)
    {
        if (empty($json) || empty($fileName)) {
            return false;
        }
        $zip = new ZipArchive();
        $zipPath = $fileName . '.zip';

        if ($zip->open($zipPath, ZipArchive::CREATE) !== true) {
            return false;
        }
        $zip->addFromString(basename($fileName), $json);

        if (!$zip->status == ZIPARCHIVE::ER_OK) {
            $zip->close();
            return false;
        }

        $zip->close();

        return true;
    }
}
