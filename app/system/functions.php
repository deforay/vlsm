<?php

use Laminas\Filter\StripTags;
use App\Services\UsersService;
use App\Utilities\MiscUtility;
use Laminas\Filter\StringTrim;
use App\Services\SystemService;
use Laminas\Filter\FilterChain;
use App\Utilities\LoggerUtility;
use App\Exceptions\SystemException;
use App\Registries\ContainerRegistry;

function _translate(?string $text, ?bool $escapeText = false)
{
    if (empty($text) || !is_string($text)) {
        return $text;
    }

    $translatedString = SystemService::translate($text);

    if ($escapeText) {
        // Use htmlspecialchars to convert special characters to HTML entities,
        // and then use json_encode to ensure it's safe for JavaScript.
        $escapedString = json_encode(htmlspecialchars((string) $translatedString, ENT_QUOTES, 'UTF-8'));
        // json_encode will add double quotes around the string, remove them.
        return trim($escapedString, '"');
    }

    return $translatedString;
}

function _isAllowed($currentRequest, $privileges = null)
{
    return (ContainerRegistry::get(UsersService::class))
        ->isAllowed($currentRequest, $privileges);
}

function _sanitizeInput(string|array|null $data, $customFilters = [])
{
    // Check for null, empty array, or empty string and return appropriately
    if ($data === null) {
        return null;
    } elseif (is_array($data) && empty($data)) {
        return [];
    } elseif (is_string($data) && $data === '') {
        return '';
    }

    // Default Laminas filter chain with StripTags and StringTrim
    $defaultFilterChain = new FilterChain();
    $defaultFilterChain->attach(new StripTags())
        ->attach(new StringTrim());

    // Convert single string to array for uniform processing
    $data = is_array($data) ? $data : [$data];

    // Apply filters
    foreach ($data as $key => &$value) {
        // Skip processing for null values
        if ($value === null) {
            continue;
        }

        if (is_array($value)) {
            // Recursive call for nested arrays
            $value = _sanitizeInput($value, $customFilters[$key] ?? []);
        } else {
            // Use custom filter if defined, otherwise default filter
            $filterChain = $customFilters[$key] ?? $defaultFilterChain;
            $value = $filterChain->filter($value);
        }
    }
    unset($value); // Break reference link

    return $data;
}


function _sanitizeFiles($filesInput, $allowedTypes = [], $sanitizeFileName = true, $maxSize = null)
{
    if ($maxSize === null) {
        $maxSize = MiscUtility::convertToBytes(ini_get('upload_max_filesize') ?? '500M');
    }

    $sanitizedFiles = [];

    // Check if the input is a single file, multiple files from one input, or multiple single-file inputs
    $isSingleFile = isset($filesInput['name']) && is_string($filesInput['name']);
    $isMultiFileArray = isset($filesInput['name']) && is_array($filesInput['name']);

    // Normalize input
    if ($isSingleFile) {
        $files = ['singleFile' => $filesInput];
    } elseif ($isMultiFileArray) {
        $files = [];
        foreach ($filesInput['name'] as $i => $name) {
            $files[] = array(
                'name' => $filesInput['name'][$i],
                'type' => $filesInput['type'][$i],
                'tmp_name' => $filesInput['tmp_name'][$i],
                'error' => $filesInput['error'][$i],
                'size' => $filesInput['size'][$i]
            );
        }
    } else {
        $files = $filesInput;
    }

    foreach ($files as $key => $file) {
        try {
            if ($file['error'] != UPLOAD_ERR_OK) {
                throw new SystemException(_translate('File upload error'), 500);
            }

            if ($file['size'] > $maxSize) {
                throw new SystemException(_translate('File size exceeds the maximum allowed size'), 400);
            }

            if (!empty($allowedTypes) && !empty($file['tmp_name'])) {
                $allowedMimeTypes = MiscUtility::getMimeTypeStrings($allowedTypes);
                $fileType = strtolower(mime_content_type($file['tmp_name']));
                $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

                if (!in_array($fileType, $allowedMimeTypes) && !isset($allowedMimeTypes[$fileExtension])) {
                    throw new SystemException(_translate('File type is not allowed'), 400);
                }
            }

            if ($sanitizeFileName) {
                // Sanitize the filename
                $sanitizedFilename = preg_replace('/[^A-Za-z0-9._-]/', '_', $file['name']);
                // Ensure the filename does not start with a dot
                if ($sanitizedFilename[0] === '.') {
                    $sanitizedFilename = '_' . ltrim($sanitizedFilename, '.');
                }
                // Assign the sanitized filename back to the file array
                $file['name'] = $sanitizedFilename;
            }

            $sanitizedFiles[$key] = $file;
        } catch (SystemException |Exception $e) {
            LoggerUtility::log('error', $e->getMessage());
            // Set to empty array to indicate failure
            $sanitizedFiles[$key] = [];
            continue;
        }
    }

    // Return the sanitized files in the same structure as the input
    if ($isSingleFile) {
        // Return single file data directly
        return reset($sanitizedFiles);
    } elseif ($isMultiFileArray) {
        // Return array of files for multi-file input
        return array_values($sanitizedFiles);
    } else {
        // Return array of single-file inputs
        return $sanitizedFiles;
    }
}

function _castVariable(mixed $variable, ?string $expectedType = null, ?bool $isNullable = true)
{

    if (empty(trim($variable))) {
        if ($isNullable) {
            return null;
        } else {
            switch ($expectedType) {
                case 'array':
                    return [];
                case 'json':
                    return '{}';
                case 'string':
                    return '';
                default:
                    return null;
            }
        }
    } else {
        switch ($expectedType) {
            case 'int':
                return (int) $variable;
            case 'float':
                return (float) $variable;
            case 'string':
                return (string) $variable;
            case 'bool':
                return (bool) $variable;
            case 'array':
                return is_array($variable) ? $variable : (array) $variable;
            case 'json':
                return is_string($variable) ? json_decode($variable, true) : json_encode($variable);
            default:
                return $variable;
        }
    }
}
