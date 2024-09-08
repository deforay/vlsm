<?php

namespace App\Utilities;

use App\Utilities\LoggerUtility;

final class JsonUtility
{
    // Validate if a string is valid JSON
    public static function isJSON($string, bool $logError = false, $checkUtf8Encoding = false): bool
    {
        if (empty($string) || !is_string($string)) {
            return false;
        }

        // Optional check for UTF-8 encoding
        if ($checkUtf8Encoding && !mb_check_encoding($string, 'UTF-8')) {
            if ($logError) {
                LoggerUtility::log('error', 'String is not valid UTF-8.');
            }
            return false;
        }

        json_decode($string);

        if (json_last_error() === JSON_ERROR_NONE) {
            return true;
        } else {
            if ($logError) {
                LoggerUtility::log('error', 'JSON decoding error: ' . json_last_error_msg());
                LoggerUtility::log('error', "Invalid JSON: $string");
            }
            return false;
        }
    }

    // Convert input to UTF-8 encoding
    public static function toUtf8(array|string|null $input): array|string|null
    {
        if (is_array($input)) {
            return array_map([self::class, 'toUtf8'], $input);
        }
        if (is_string($input)) {
            if (!mb_check_encoding($input, 'UTF-8')) {
                $encoding = mb_detect_encoding($input, mb_detect_order(), true) ?? 'UTF-8';
                return mb_convert_encoding($input, 'UTF-8', $encoding);
            }
        }
        return $input;
    }

    // Encode data to JSON with UTF-8 encoding
    public static function encodeUtf8Json(array|string|null $data): string
    {
        if (is_null($data)) {
            return '{}';
        } elseif (is_array($data) && empty($data)) {
            return '[]';
        } elseif (is_string($data) && self::isJSON($data, checkUtf8Encoding: true)) {
            return $data;
        }

        return self::toJSON(self::toUtf8($data));
    }

    // Convert data to JSON string
    public static function toJSON($data, int $flags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR): ?string
    {
        // Check if the data is already a valid JSON string
        if (is_string($data) && self::isJSON($data)) {
            return $data;
        }

        // Convert the data to JSON
        $json = json_encode($data, $flags);
        if ($json === false) {
            LoggerUtility::log('error', 'Data could not be encoded as JSON: ' . json_last_error_msg());
            return null;
        }
        return $json;
    }

    // Pretty-print JSON
    public static function prettyJson(array|string $json): string
    {
        $decodedJson = is_array($json) ? $json : self::decodeJson($json);
        if ($decodedJson === null) {
            return htmlspecialchars("Error in JSON decoding: " . json_last_error_msg(), ENT_QUOTES, 'UTF-8');
        }

        $encodedJson = json_encode($decodedJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return htmlspecialchars("Error in JSON encoding: " . json_last_error_msg(), ENT_QUOTES, 'UTF-8');
        }

        return $encodedJson;
    }

    // Merge multiple JSON strings into one
    public static function mergeJson(...$jsonStrings): ?string
    {
        $mergedArray = [];

        foreach ($jsonStrings as $json) {
            $array = self::decodeJson($json);
            if ($array === null) {
                return null;
            }
            $mergedArray = array_merge_recursive($mergedArray, $array);
        }

        return self::toJSON($mergedArray);
    }

    // Extract specific data from JSON using a path
    public static function extractJsonData($json, $path): mixed
    {
        $data = self::decodeJson($json);
        if ($data === null) {
            return null;
        }

        foreach (explode('.', $path) as $segment) {
            if (!isset($data[$segment])) {
                return null;
            }
            $data = $data[$segment];
        }

        return $data;
    }

    // Decode JSON string to array or object
    public static function decodeJson($json, bool $assoc = true): mixed
    {
        $data = json_decode($json, $assoc);
        if (json_last_error() !== JSON_ERROR_NONE) {
            LoggerUtility::log('error', 'Error decoding JSON: ' . json_last_error_msg());
            return null;
        }
        return $data;
    }

    // Minify JSON string
    public static function minifyJson($json): string
    {
        $decodedJson = self::decodeJson($json);
        if ($decodedJson === null) {
            return '';
        }

        return self::toJSON($decodedJson, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    // Get keys from JSON object
    public static function getJsonKeys($json): array
    {
        $data = self::decodeJson($json);
        if ($data === null) {
            return [];
        }

        return array_keys($data);
    }

    // Get values from JSON object
    public static function getJsonValues($json): array
    {
        $data = self::decodeJson($json);
        if ($data === null) {
            return [];
        }

        return array_values($data);
    }

    // Convert a value to a JSON-compatible string representation
    public static function jsonValueToString($value): string
    {
        if (is_null($value)) {
            return 'null';
        } elseif (is_bool($value)) {
            return $value ? 'true' : 'false';
        } elseif (is_numeric($value)) {
            return (string) $value;
        } elseif (is_array($value)) {
            return "'" . addslashes(json_encode($value)) . "'";
        } else {
            return "'" . addslashes((string) $value) . "'";
        }
    }

    // Convert a JSON string to a string that can be used with JSON_SET()
    public static function jsonToSetString(?string $json, string $column, $newData = []): ?string
    {
        // Decode JSON string to array
        $jsonData = $json && self::isJSON($json) ? json_decode($json, true) : [];

        // Decode newData if it's a string
        if (is_string($newData)) {
            $newData = json_decode($newData, true);
        }

        // Combine original data and new data
        $data = array_merge($jsonData, $newData);

        // Return null if there's nothing to set
        if (empty($data)) {
            return null;
        }

        // Build the set string
        $setString = '';
        foreach ($data as $key => $value) {
            $setString .= ', "$.' . $key . '", ' . self::jsonValueToString($value);
        }

        // Construct and return the JSON_SET query
        return 'JSON_SET(COALESCE(' . $column . ', "{}")' . $setString . ')';
    }
}
