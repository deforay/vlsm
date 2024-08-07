<?php

namespace App\Utilities;

use Throwable;
use ZipArchive;
use Sqids\Sqids;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\Ulid;
use App\Exceptions\SystemException;

final class MiscUtility
{
    public static function encode($data)
    {
        $data = !is_array($data) ? [$data] : $data;
        $sqids = new Sqids('', 10);
        return $sqids->encode($data);
    }

    public static function decode($data)
    {
        $sqids = new Sqids();
        $ids = $sqids->decode($data);
        if (count($ids) == 1) {
            return $ids[0];
        }
    }
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

    public static function generateRandomNumber(int $length = 8): string
    {
        $result = '';
        for ($i = 0; $i < $length; $i++) {
            $result .= random_int(0, 9);
        }
        return $result;
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

        // Write the UTF-8 BOM
        fwrite($handle, "\xEF\xBB\xBF");

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

    public static function getGenderFromString(?string $gender)
    {
        return match (strtolower($gender)) {
            'male', 'm' => _translate('Male'),
            'female', 'f' => _translate('Female'),
            default => _translate('Unreported')
        };
    }
    public static function removeFromAssociativeArray(array $fullArray, array $unwantedKeys)
    {
        return array_diff_key($fullArray, array_flip($unwantedKeys));
    }

    // Updates entries in targetArray with values from sourceArray where keys exist in targetArray
    public static function updateFromArray(?array $targetArray, ?array $sourceArray)
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

    // Generate a UUIDv4 with an optional extra string
    public static function generateUUID($attachExtraString = true): string
    {
        $uuid = Uuid::v4()->toRfc4122();
        if ($attachExtraString) {
            $uuid .= '-' . self::generateRandomString(6);
        }
        return $uuid;
    }

    // Generate a UUIDv5 with a name and namespace
    public static function generateUUIDv5($name, $namespace = null): string
    {
        if ($namespace === null) {
            $namespace = Uuid::fromString(Uuid::NAMESPACE_OID);
        } elseif (is_string($namespace)) {
            $namespace = Uuid::fromString($namespace);
        }
        return Uuid::v5($namespace, $name)->toRfc4122();
    }


    // Generate a ULID
    public static function generateULID($attachExtraString = true): string
    {
        $ulid = (new Ulid())->toRfc4122();
        if ($attachExtraString) {
            $ulid .= '-' . self::generateRandomString(6);
        }
        return $ulid;
    }

    /**
     * String to a file inside a zip archive.
     *
     * @param string $stringData
     * @param string $fileName The FULL PATH of the file inside the zip archive.
     * @return bool Returns true on success, false on failure.
     */
    public static function dataToZippedFile(string $stringData, string $fileName): bool
    {
        if (empty($stringData) || empty($fileName)) {
            return false;
        }

        $zip = new ZipArchive();
        $zipPath = $fileName . '.zip';

        if ($zip->open($zipPath, ZipArchive::CREATE) === true) {
            $zip->addFromString(basename($fileName), $stringData);
            $result = $zip->status == ZipArchive::ER_OK;
            $zip->close();
            return $result;
        }

        return false;
    }

    /**
     * Unzips an archive and returns contents of a file inside it.
     *
     * @param string $zipFile The path to the zip file.
     * @param string $fileName The name of the JSON file inside the zip archive.
     * @return string
     */
    public static function getDataFromZippedFile(string $zipFile, string $fileName): string
    {
        if (!file_exists($zipFile)) {
            return "{}";
        }
        $zip = new ZipArchive;
        if ($zip->open($zipFile) === true) {
            $json = $zip->getFromName($fileName);
            $zip->close();

            return $json !== false ? $json : "{}";
        } else {
            return "{}";
        }
    }

    public static function getFileExtension($filename): string
    {
        if (empty($filename)) {
            return '';
        }

        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        return strtolower($extension);
    }


    public static function isFileType(string $filePath, string $expectedMimeType): bool
    {
        // Check if the file exists
        if (!file_exists($filePath)) {
            return false;
        }

        // Get the MIME type of the file
        $actualMimeType = mime_content_type($filePath);

        // Compare the actual MIME type with the expected MIME type
        return $actualMimeType === $expectedMimeType;
    }

    public static function displayProgressBar($current, $total = null, $size = 30)
    {
        static $startTime;

        // Start the timer
        if (empty($startTime)) {
            $startTime = time();
        }

        // Calculate elapsed time
        $elapsed = time() - $startTime;

        if ($total !== null) {
            // Calculate the percentage
            $progress = ($current / $total);
            $bar = floor($progress * $size);

            // Generate the progress bar string
            $progressBar = str_repeat('=', $bar) . str_repeat(' ', $size - $bar);

            // Output the progress bar
            printf("\r[%s] %d%% Complete (%d/%d) - %d sec elapsed", $progressBar, $progress * 100, $current, $total, $elapsed);
        } else {
            // Output the current progress without percentage
            printf("\rProcessed %d items - %d sec elapsed", $current, $elapsed);
        }

        // Flush output
        if ($total !== null && $current === $total) {
            echo "\n";
        }
    }

    public static function removeDuplicates($input)
    {
        // Check if the input is a string
        if (is_string($input)) {
            // Split the string into an array
            $inputArray = explode(',', $input);
        } elseif (is_array($input)) {
            // Use the input array directly
            $inputArray = $input;
        } else {
            // Invalid input type
            return $input;
        }

        // Remove duplicate values
        $uniqueArray = array_unique($inputArray);

        // Optionally, remove any empty values
        $uniqueArray = array_filter($uniqueArray);

        // Return the same type as the input
        if (is_string($input)) {
            // Convert the array back to a comma-separated string
            return implode(',', $uniqueArray);
        } else {
            // Return the unique array
            return $uniqueArray;
        }
    }

    public static function getMacAddress()
    {
        $macAddress = null;

        // Attempt to get the MAC address on Windows
        if (strncasecmp(PHP_OS, 'WIN', 3) == 0) {
            $output = [];
            @exec('getmac', $output);

            foreach ($output as $line) {
                if (preg_match('/([0-9A-F]{2}[:-]){5}([0-9A-F]{2})/i', $line, $matches)) {
                    $macAddress = $matches[0];
                    break;
                }
            }
        }
        // Attempt to get the MAC address on Linux or Unix
        else {
            $output = [];
            @exec('ifconfig -a', $output);

            foreach ($output as $line) {
                if (preg_match('/([0-9A-F]{2}[:-]){5}([0-9A-F]{2})/i', $line, $matches)) {
                    $macAddress = $matches[0];
                    break;
                }
            }

            if ($macAddress === null) {
                $output = [];
                @exec('ip addr show', $output);

                foreach ($output as $line) {
                    if (preg_match('/([0-9A-F]{2}[:-]){5}([0-9A-F]{2})/i', $line, $matches)) {
                        $macAddress = $matches[0];
                        break;
                    }
                }
            }
        }

        // If the MAC address was found, return it, otherwise return null
        return $macAddress;
    }
}
