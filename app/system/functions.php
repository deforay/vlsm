<?php

use voku\helper\AntiXSS;
use App\Services\UsersService;
use App\Utilities\JsonUtility;
use App\Utilities\MiscUtility;
use App\Services\SystemService;
use App\Exceptions\SystemException;
use Laminas\Diactoros\UploadedFile;
use App\Registries\ContainerRegistry;

use function iter\count as iterCount;
use function iter\toArray as iterToArray;


function _translate(?string $text, bool $escapeText = false)
{
    if (empty($text) || !is_string($text) || $_SESSION['APP_LOCALE'] == 'en_US') {
        return $text;
    }

    $translatedString = SystemService::translate($text);

    if ($escapeText && $_SESSION['APP_LOCALE'] != 'en_US') {
        // Use json_encode to ensure it's safe for JavaScript.
        // json_encode will add double quotes around the string, remove them.
        $escapedString = trim(json_encode($translatedString), '"');

        // Use htmlspecialchars to convert special characters to HTML entities,
        $translatedString = htmlspecialchars($escapedString, ENT_QUOTES, 'UTF-8');
    }

    return $translatedString;
}

function _isAllowed($currentRequest, $privileges = null)
{
    /** @var UsersService  $usersService */
    $usersService = ContainerRegistry::get(UsersService::class);
    return $usersService->isAllowed($currentRequest, $privileges);
}

function _sanitizeInput($input, $nullifyEmptyStrings = false)
{
    $antiXss = new AntiXSS();

    // Recursive sanitization
    if (is_array($input)) {
        foreach ($input as $key => $value) {
            $input[$key] = _sanitizeInput($value, $nullifyEmptyStrings);
        }
    } elseif (is_object($input)) {
        foreach ($input as $key => $value) {
            $input->$key = _sanitizeInput($value, $nullifyEmptyStrings);
        }
    } else {
        // Trim and sanitize using AntiXSS
        $input = trim($input);
        $input = $antiXss->xss_clean($input);

        // Convert empty strings to null if $nullifyEmptyStrings is true
        if ($nullifyEmptyStrings && $input === '') {
            $input = null;
        }
    }

    return $input;
}

function _sanitizeFiles($files, $allowedTypes = [], $sanitizeFileName = true, $maxSize = null)
{
    if ($maxSize === null) {
        $uploadMaxSize = ini_get('upload_max_filesize');
        $uploadMaxSize = $uploadMaxSize ? trim($uploadMaxSize) : '500M';
        $maxSize = MiscUtility::convertToBytes($uploadMaxSize);
    }

    $sanitizedFiles = [];
    $allowedMimeTypes = MiscUtility::getMimeTypeStrings($allowedTypes);

    $isSingleFile = !is_array($files);
    if ($isSingleFile) {
        $files = [$files];
    }

    foreach ($files as $file) {
        if ($file instanceof UploadedFile) {
            try {
                if ($file->getError() === UPLOAD_ERR_NO_FILE) {
                    // No file was uploaded
                    throw new SystemException("No file was uploaded");
                }

                if ($file->getError() !== UPLOAD_ERR_OK) {
                    throw new SystemException('File upload error');
                }

                if ($file->getSize() > $maxSize) {
                    throw new SystemException('File size exceeds the maximum allowed size');
                }

                $fileExtension = strtolower(pathinfo($file->getClientFilename(), PATHINFO_EXTENSION));
                $fileMimeType = $file->getClientMediaType();

                if (!empty($allowedTypes) && (!in_array($fileExtension, $allowedTypes) || !in_array($fileMimeType, $allowedMimeTypes))) {
                    throw new SystemException('File type is not allowed');
                }

                if ($sanitizeFileName) {
                    $sanitizedFilename = preg_replace('/[^A-Za-z0-9._-]/', '_', $file->getClientFilename());
                    if ($sanitizedFilename[0] === '.') {
                        $sanitizedFilename = '_' . ltrim($sanitizedFilename, '.');
                    }
                    $file = new UploadedFile(
                        $file->getStream(),
                        $file->getSize(),
                        $file->getError(),
                        $sanitizedFilename,
                        $file->getClientMediaType()
                    );
                }

                $sanitizedFiles[] = $file;
            } catch (Throwable $e) {
                //error_log($e->getMessage());
                // You can choose to handle the error differently, e.g., add null to the array or skip the file
                $sanitizedFiles[] = null;
            }
        }
    }

    return $isSingleFile ? $sanitizedFiles[0] : $sanitizedFiles;
}


function _castVariable(mixed $variable, ?string $expectedType = null, ?bool $isNullable = true)
{

    if (empty(trim($variable))) {
        if ($isNullable) {
            return null;
        } else {
            return match ($expectedType) {
                'array' => [],
                'json' => '{}',
                'string' => '',
                default => null,
            };
        }
    } else {
        return match ($expectedType) {
            'int' => (int)$variable,
            'float' => (float)$variable,
            'string' => (string)$variable,
            'bool' => (bool)$variable,
            'array' => is_array($variable) ? $variable : (array)$variable,
            'json' => JsonUtility::toJson($variable),
            default => $variable,
        };
    }
}

function _capitalizeWords($string)
{
    if (empty($string) || $string == '') {
        return $string;
    }
    return mb_convert_case($string, MB_CASE_TITLE, "UTF-8");
}

function _capitalizeFirstLetter($string, $encoding = "UTF-8")
{
    if (empty($string || $string == '')) {
        return $string;
    }
    $firstChar = mb_substr($string, 0, 1, $encoding);
    $rest = mb_substr($string, 1, null, $encoding);
    return mb_strtoupper($firstChar, $encoding) . $rest;
}

function _toUpperCase(?string $string, $encoding = "UTF-8")
{
    if (empty($string || $string == '')) {
        return $string;
    }
    return mb_strtoupper($string, $encoding);
}
function _toLowerCase(?string $string, $encoding = "UTF-8")
{
    if (empty($string || $string == '')) {
        return $string;
    }
    return mb_strtolower($string, $encoding);
}


/**
 * Safely converts an iterator to an array and retrieves a specified key.
 *
 * @param mixed $iterator The potential iterator.
 * @param string $key The key to retrieve from the array.
 * @return mixed Returns the value associated with the key, or null if not found or not an iterator.
 */
function _getIteratorKey($iterator, $key)
{
    if ($iterator instanceof Iterator) {
        $array = iterToArray($iterator);
        return $array[$key] ?? null;
    }
    return null;
}

/**
 * Safely counts the elements of an iterator or Traversable object.
 *
 * @param mixed $iterator The potential iterator or Traversable object.
 * @return int Returns the count of elements. Returns 0 if the input is not Traversable.
 */
function _getIteratorCount($iterator): int
{
    if ($iterator instanceof Traversable) {
        return iterCount($iterator);
    }
    return 0;
}
