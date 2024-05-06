<?php

namespace App\Utilities;

use Throwable;
use ZipArchive;
use Ramsey\Uuid\Uuid;
use App\Exceptions\SystemException;

final class MiscUtility
{
    public static function generateRandomString(int $length = 32): string
    {
        $bytes = ceil($length * 3 / 4);
        try {
            $randomBytes = random_bytes($bytes);
            $base64String = base64_encode($randomBytes);
            // Replace base64 characters with some alphanumeric characters
            $customBase64String = strtr($base64String, '+/=', 'ABC');
            return substr($customBase64String, 0, $length);
        } catch (Throwable $e) {
            throw new SystemException('Failed to generate random string: ' . $e->getMessage());
        }
    }

    public static function randomHexColor(): string
    {
        $hexColorPart = function () {
            return str_pad(dechex(random_int(0, 255)), 2, '0', STR_PAD_LEFT);
        };

        return strtoupper($hexColorPart() . $hexColorPart() . $hexColorPart());
    }

    public static function removeDirectory($dirname): bool
    {
        if (!file_exists($dirname)) {
            return false;
        }

        if (is_file($dirname) || is_link($dirname)) {
            return unlink($dirname);
        }

        $dir = dir($dirname);
        while (false !== ($entry = $dir->read())) {
            if ($entry == '.' || $entry == '..') {
                continue;
            }

            $fullPath = $dirname . DIRECTORY_SEPARATOR . $entry;
            if (!self::removeDirectory($fullPath)) {
                $dir->close(); // Close the directory handle if a recursive delete fails.
                return false;
            }
        }

        $dir->close();
        return rmdir($dirname);
    }

    //dump the contents of a variable to the error log in a readable format
    public static function dumpToErrorLog($object = null, $useVarDump = true): void
    {
        ob_start();
        if ($useVarDump) {
            var_dump($object);
            $output = ob_get_clean();
            // Remove newline characters
            $output = str_replace("\n", "", $output);
        } else {
            print_r($object);
            $output = ob_get_clean();
        }

        // Additional context
        $timestamp = date('Y-m-d H:i:s');
        $output = "[{$timestamp}] " . $output;

        error_log($output);
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
            if ($value === null || trim((string) $value) === "") {
                return true;
            }
        }
        return false;
    }

    public static function isJSON($string): bool
    {
        if (empty($string) || !is_string($string)) {
            return false;
        }

        json_decode($string);
        if (json_last_error() === JSON_ERROR_NONE) {
            return true;
        } else {
            LoggerUtility::log('error', 'JSON decoding error: ' . json_last_error_msg());
            return false;
        }
    }


    public static function toJSON($data): ?string
    {
        if (!empty($data)) {
            if (self::isJSON($data)) {
                return $data;
            } else {
                $json = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                if ($json !== false) {
                    return $json;
                } else {
                    LoggerUtility::log('error', 'Data could not be encoded as JSON: ' . json_last_error_msg());
                }
            }
        }
        return null;
    }

    public static function prettyJson($json): string
    {
        if (is_array($json)) {
            $encodedJson = json_encode($json, JSON_PRETTY_PRINT);
        } else {
            $decodedJson = json_decode((string) $json);
            if (json_last_error() !== JSON_ERROR_NONE) {
                // Handle the error, maybe log it and return a safe error message
                return htmlspecialchars("Error in JSON decoding: " . json_last_error_msg(), ENT_QUOTES, 'UTF-8');
            }
            $encodedJson = json_encode($decodedJson, JSON_PRETTY_PRINT);
        }

        if (json_last_error() !== JSON_ERROR_NONE) {
            // Handle the error, maybe log it and return a safe error message
            return htmlspecialchars("Error in JSON encoding: " . json_last_error_msg(), ENT_QUOTES, 'UTF-8');
        }

        return htmlspecialchars($encodedJson, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Unzips a JSON file and displays its contents in a pretty format.
     *
     * @param string $zipFile The path to the zip file.
     * @param string $jsonFile The name of the JSON file inside the zip archive.
     */
    public static function getJsonFromZip(string $zipFile, string $jsonFile): string
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
    public static function zipJson(string $json, string $fileName)
    {
        $result = false;
        if (!empty($json) && !empty($fileName)) {
            $zip = new ZipArchive();
            $zipPath = $fileName . '.zip';

            if ($zip->open($zipPath, ZipArchive::CREATE) === true) {
                $zip->addFromString(basename($fileName), $json);

                if ($zip->status == ZIPARCHIVE::ER_OK) {
                    $result = true;
                }
                $zip->close();
            }
        }
        return $result;
    }


    public static function fileExists($filePath): bool
    {
        return !empty($filePath) && file_exists($filePath) && is_file($filePath);
    }

    public static function imageExists($filePath): bool
    {
        if (!self::fileExists($filePath)) {
            return false;
        }

        // Attempt to obtain image size and type
        $imageInfo = getimagesize($filePath);
        if ($imageInfo === false) {
            return false;
        }

        // Check if the image type is recognized by PHP
        return isset($imageInfo[2]) && $imageInfo[2] > 0;
    }


    public static function getMimeType($file, $allowedMimeTypes)
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);

        if ($finfo === false) {
            return false;
        }

        $mime = finfo_file($finfo, $file);
        finfo_close($finfo);

        return in_array($mime, $allowedMimeTypes) ? $mime : false;
    }

    public static function makeDirectory($path, $mode = 0777, $recursive = true): bool
    {
        if (is_dir($path)) {
            return true;
        }

        return mkdir($path, $mode, $recursive);
    }

    public static function generateCsv($headings, $data, $filename, $delimiter = ',', $enclosure = '"')
    {
        $handle = fopen($filename, 'w'); // Open file for writing

        // The headings first
        if (!empty($headings)) {
            fputcsv($handle, $headings, $delimiter, $enclosure);
        }
        // Then the data
        if (!empty($data)) {
            foreach ($data as $line) {
                fputcsv($handle, $line, $delimiter, $enclosure);
            }
        }

        //Clear Memory
        unset($data);
        fclose($handle);
        return $filename;
    }

    public static function generateCsvRow($handle, $row, $delimiter = ',', $enclosure = '"')
    {
        if ($handle) {
            fputcsv($handle, $row, $delimiter, $enclosure);
        }
    }

    public static function initializeCsv($filename, $headings, $delimiter = ',', $enclosure = '"')
    {
        $handle = fopen($filename, 'w'); // Open file for writing

        // Write the headings
        if (!empty($headings)) {
            self::generateCsvRow($handle, $headings, $delimiter, $enclosure);
        }

        return $handle;
    }

    public static function finalizeCsv($handle)
    {
        fclose($handle);
    }

    public static function convertToUtf8(array|string|null $input)
    {
        if (is_array($input)) {
            return array_map([self::class, 'convertToUtf8'], $input);
        }
        if (is_string($input)) {
            $encoding = mb_detect_encoding($input, mb_detect_order(), true) ?? 'UTF-8';
            return mb_convert_encoding($input, 'UTF-8', $encoding);
        }
        return $input;
    }

    public static function convertToUtf8AndEncode(array|string|null $data)
    {
        if (empty($data)) {
            return is_array($data) ? '[]' : '{}';
        }

        return json_encode(self::convertToUtf8($data));
    }

    public static function getGenderFromString(string $gender)
    {
        return match (strtolower($gender)) {
            'male', 'm' => _translate('Male'),
            'female', 'f' => _translate('Female'),
            'not_recorded', 'notrecorded', 'unreported' => _translate('Unreported'),
            default => '',
        };
    }
    public static function removeFromAssociativeArray(array $fullArray, array $unwantedKeys)
    {
        return array_diff_key($fullArray, array_flip($unwantedKeys));
    }

    // Updates entries in targetArray with values from sourceArray where keys exist in targetArray
    public static function updateFromArray(array $targetArray, array $sourceArray)
    {

        if (empty($targetArray) || empty($sourceArray)) {
            return $targetArray;
        }
        return array_merge($targetArray, array_intersect_key($sourceArray, $targetArray));
    }


    // Helper function to convert file size string to bytes
    public static function convertToBytes(string $sizeString): int
    {
        return match (substr($sizeString, -1)) {
            'M', 'm' => (int)$sizeString * 1048576,
            'K', 'k' => (int)$sizeString * 1024,
            'G', 'g' => (int)$sizeString * 1073741824,
            default => (int)$sizeString,
        };
    }

    public static function getMimeTypeStrings(array $extensions): array
    {
        $mimeTypesMap = [
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'txt' => 'text/plain',
            'csv' => 'text/csv',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'ppt' => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'mp3' => 'audio/mpeg',
            'mp4' => 'video/mp4',
            'avi' => 'video/x-msvideo',
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'html' => 'text/html',
            'xml' => 'application/xml',
            'json' => 'application/json'
        ];

        $mappedMimeTypes = [];
        foreach ($extensions as $ext) {
            $ext = strtolower($ext);
            if (isset($mimeTypesMap[$ext])) {
                $mappedMimeTypes[$ext] = $mimeTypesMap[$ext];
            } else {
                // If it's already a MIME type, just use it
                $mappedMimeTypes[$ext] = $ext;
            }
        }
        return $mappedMimeTypes;
    }

    public static function arrayToGenerator(array $array)
    {
        foreach ($array as $item) {
            yield $item;
        }
    }

    public static function removeMatchingElements(array $array, array $removeArray): array
    {
        return array_values(array_diff($array, $removeArray));
    }

    public static function arrayEmptyStringsToNull(?array $array, bool $convertEmptyJson = false): array
    {
        if (empty($array)) {
            return $array;
        }

        foreach ($array as $key => &$value) {
            if (is_array($value)) {
                if (empty($value)) {
                    $value = null; // Convert empty arrays to null
                } else {
                    // Apply the function recursively if the value is an array
                    $value = self::arrayEmptyStringsToNull($value, $convertEmptyJson);
                }
            } elseif ($value === '' || ($convertEmptyJson && is_string($value) && ($value === '{}' || $value === '[]'))) {
                // Convert empty strings or empty JSON strings/arrays to null
                $value = null;
            }
        }
        unset($value); // Break the reference after the loop
        return $array;
    }


    public static function generateUUID($attachExtraString = true): string
    {
        $uuid = Uuid::uuid4()->toString();
        $uuid .= $attachExtraString ? '-' . Self::generateRandomString(6) : '';
        return $uuid;
    }
    public static function generateUUIDv5($name = null, $namespace = Uuid::NAMESPACE_URL): string
    {
        return Uuid::uuid5($namespace, $name)->toString();
    }

    public static function getFileExtension($filename): string
    {
        if (empty($filename)) {
            return '';
        }

        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        return strtolower($extension);
    }
}
