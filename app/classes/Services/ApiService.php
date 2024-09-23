<?php

namespace App\Services;

use Exception;
use Throwable;
use GuzzleHttp\Client;
use GuzzleHttp\Middleware;
use GuzzleHttp\HandlerStack;
use App\Utilities\JsonUtility;
use App\Utilities\MiscUtility;
use GuzzleHttp\RequestOptions;
use App\Utilities\LoggerUtility;
use App\Exceptions\SystemException;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ServerRequestInterface;

final class ApiService
{
    protected ?Client $client = null;
    protected int $maxRetries;
    protected int $delayMultiplier;
    protected float $jitterFactor;
    protected int $maxRetryDelay;

    protected CommonService $commonService;

    public function __construct(CommonService $commonService, ?Client $client = null, int $maxRetries = 3, int $delayMultiplier = 1000, float $jitterFactor = 0.2, int $maxRetryDelay = 10000)
    {
        $this->maxRetries = $maxRetries;
        $this->delayMultiplier = $delayMultiplier;
        $this->jitterFactor = $jitterFactor;
        $this->maxRetryDelay = $maxRetryDelay;
        $this->commonService = $commonService;

        // Use the injected client if provided, or create a new one
        $this->client = $client ?? $this->createApiClient();
    }

    private function logError(Throwable $e, string $message): void
    {
        LoggerUtility::log('error', "$message: " . $e->getMessage(), [
            'exception' => $e,
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'stacktrace' => $e->getTraceAsString()
        ]);
    }

    public static function generateAuthToken(): string
    {
        return base64_encode(MiscUtility::generateUUID());
    }

    protected function createApiClient(): Client
    {
        $handlerStack = HandlerStack::create();
        $handlerStack->push(Middleware::retry(
            $this->retryDecider(),
            $this->retryDelay()
        ));

        return new Client(['handler' => $handlerStack]);
    }

    private function retryDecider()
    {
        return function ($retries, $request, $response, $exception) {
            if ($retries >= $this->maxRetries) {
                return false;
            }
            if ($exception instanceof RequestException) {
                if ($response) {
                    $statusCode = $response->getStatusCode();
                    // Retry on server errors (5xx) or rate limiting errors (429)
                    return $statusCode >= 500 || $statusCode === 429;
                }
                return true;
            }
            return false;
        };
    }

    private function retryDelay()
    {
        return function ($retries) {
            $delay = $this->delayMultiplier * (2 ** $retries);
            $jitter = random_int(0, (int)($this->jitterFactor * 1000)) / 1000;
            return min($this->maxRetryDelay, $delay * (1 + $jitter));
        };
    }


    public function checkConnectivity(string $url): bool
    {
        try {
            $response = $this->client->get($url);
            $statusCode = (int) $response->getStatusCode();
            return $statusCode === 200;
        } catch (Throwable $e) {
            LoggerUtility::log('error', "Unable to connect to $url: " . $e->getMessage(), [
                'exception' => $e,
                'file' => $e->getFile(), // File where the error occurred
                'line' => $e->getLine(), // Line number of the error
                'stacktrace' => $e->getTraceAsString()
            ]);
            return false; // Error occurred while making the request
        }
    }

    public function post($url, $payload, $gzip = false, $returnWithStatusCode = false): array|string|null
    {

        $options = [
            RequestOptions::HEADERS => [
                'X-Request-ID' => MiscUtility::generateULID(),
                'X-Timestamp'  => time(),
                'X-Instance-ID' => $this->commonService->getInstanceId(),
                'X-Requestor-Version' => VERSION ?? $this->commonService->getAppVersion(),
                'Content-Type' => 'application/json; charset=utf-8',
            ]
        ];

        try {
            $payload = JsonUtility::isJSON($payload) ? $payload : JsonUtility::encodeUtf8Json($payload);
            if ($gzip) {
                $payload = gzencode($payload);
                $options[RequestOptions::HEADERS]['Content-Encoding'] = 'gzip';
                $options[RequestOptions::HEADERS]['Accept-Encoding'] = 'gzip, deflate';
            }

            // Correctly set Content-Length based on the payload size in bytes
            $options[RequestOptions::HEADERS]['Content-Length'] = mb_strlen($payload, '8bit');

            // Set the request body
            $options[RequestOptions::BODY] = $payload;


            $response = $this->client->post($url, $options);

            if ($returnWithStatusCode) {
                $apiResponse = [
                    'httpStatusCode' => $response->getStatusCode(),
                    'body' => $response->getBody()->getContents()
                ];
            } else {
                $apiResponse = $response->getBody()->getContents();
            }
        } catch (RequestException $e) {
            // Extract the response body from the exception, if available
            $responseBody = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : null;

            // Log the error along with the response body
            $this->logError($e, "Unable to post to $url. Server responded with: " . ($responseBody ?? 'No response body'));

            // Return the response body (if available) or null
            if ($returnWithStatusCode) {
                $apiResponse = [
                    'httpStatusCode' => $e->getResponse() ? $e->getResponse()->getStatusCode() : 500,
                    'body' => $responseBody
                ];
            } else {
                $apiResponse = $responseBody;
            }
        } catch (Throwable $e) {
            $this->logError($e, "Unable to post to $url");
            $apiResponse = null; // Error occurred while making the request
        }
        return $apiResponse;
    }

    public function postFile($url, $fileName, $jsonFilePath, $params = [], $gzip = true): ?string
    {
        // Prepare multipart data
        $multipartData = [];

        try {

            if ($gzip) {
                $fileContents = gzencode(file_get_contents($jsonFilePath));
                $fileSize = mb_strlen($fileContents, '8bit');
            } else {
                $fileContents = fopen($jsonFilePath, 'r');
                $fileSize = filesize($jsonFilePath);
            }

            // Prepare file content for multipart
            $multipartData = [
                [
                    'name'     => $fileName,
                    'contents' => $fileContents,
                    'filename' => basename($jsonFilePath) . ($gzip ? '.gz' : ''),
                ]
            ];

            // Add additional parameters to multipart data
            foreach ($params as $name => $value) {
                $multipartData[] = [
                    'name'     => $name,
                    'contents' => $value
                ];
            }

            // Prepare headers
            $headers = [
                'Content-Length' => $fileSize,
                'X-Timestamp'    => time(),
                'X-Request-ID'    => MiscUtility::generateULID(),
                'X-Requestor-Version' => VERSION ?? $this->commonService->getAppVersion(),
                'X-Instance-ID' => $this->commonService->getInstanceId()
            ];

            // Initialize the options array for multipart form data
            $options = [
                RequestOptions::MULTIPART => $multipartData,
                RequestOptions::HEADERS   => $headers
            ];

            // Send the request
            $response = $this->client->post($url, $options);

            $apiResponse = $response->getBody()->getContents();
        } catch (RequestException $e) {
            // Extract the response body from the exception, if available
            $responseBody = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : null;
            $errorCode = $e->getResponse() ? $e->getResponse()->getStatusCode() : 500;
            // Log the error along with the response body
            $this->logError($e, "Unable to post to $url. Server responded with $errorCode : " . ($responseBody ?? 'No response body'));

            $apiResponse = $responseBody ?? null;
        } catch (Throwable $e) {
            $this->logError($e, "Unable to post to $url");
            $apiResponse = null; // Error occurred while making the request
        }
        return $apiResponse;
    }


    public function getJsonFromRequest(ServerRequestInterface $request, bool $decode = false)
    {
        try {
            // Check the content encoding of the request body
            $contentEncoding = $request->getHeaderLine('Content-Encoding');
            $jsonData = (string) ($request->getBody()); // Read the request body

            // Decompress the request body if it's encoded
            if ($contentEncoding === 'gzip' || $contentEncoding === 'application/gzip') {
                $decodedData = gzdecode($jsonData);
                if ($decodedData === false) {
                    // Log and handle invalid gzip data
                    LoggerUtility::log('error', 'Gzip decompression failed, treating as raw JSON');
                    $decodedData = $jsonData; // Treat it as raw JSON
                }
                $jsonData = $decodedData;
            } elseif ($contentEncoding === 'deflate' || $contentEncoding === 'application/deflate') {
                $decodedData = gzinflate($jsonData);
                if ($decodedData === false) {
                    // Log and handle invalid deflate data
                    LoggerUtility::log('error', 'Deflate decompression failed, treating as raw JSON');
                    $decodedData = $jsonData; // Treat it as raw JSON
                }
                $jsonData = $decodedData;
            }

            // If decoding is requested, decode the JSON string
            if ($decode) {
                $decodedJson = json_decode($jsonData, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new SystemException("JSON decoding error: " . json_last_error_msg());
                }
                return $decodedJson;
            } else {
                return JsonUtility::isJSON($jsonData) ? $jsonData : '{}'; // Ensure valid JSON or return empty object
            }
        } catch (Throwable $e) {
            $this->logError($e, "Unable to retrieve json");
            return null;
        }
    }


    /**
     * Download a file from a given URL and save it to a specified path.
     *
     * @param string $fileUrl The URL of the file to download.
     * @param string $downloadPath The local path with filename where the file should be saved.
     * @param array $allowedFileTypes An array of allowed file types.
     * @param string $safePath The base path to ensure the download path is within the allowed directory.
     * @return bool Returns true on successful download, false otherwise.
     */
    public function downloadFile(string $fileUrl, string $downloadPath, array $allowedFileTypes = [], $safePath = ROOT_PATH): int|bool
    {

        // Validate the URL
        if (!filter_var($fileUrl, FILTER_VALIDATE_URL)) {
            $this->logError(new Exception("Invalid URL"), "Invalid URL provided for downloading");
            return false;
        }
        $downloadFolder = dirname($downloadPath);
        $fileName = basename($downloadPath);
        // Check if $fileName is null or empty
        if (empty($fileName)) {
            $fileName = basename($fileUrl);
        }
        // Normalize the safePath and downloadPath to ensure both are absolute and resolved
        $resolvedSafePath = realpath($safePath);
        $resolvedDownloadPath = realpath($downloadFolder);

        // If realpath returns false, the path does not exist
        if (!$resolvedDownloadPath) {
            // Try creating the directory or handling the error as needed
            if (!MiscUtility::makeDirectory($downloadFolder) && !is_dir($downloadFolder)) {
                $this->logError(new Exception("Invalid path"), "The download path cannot be created or does not exist");
                return false;
            }
            $resolvedDownloadPath = realpath($downloadFolder);
        }

        // Ensure the downloadPath starts with the resolved safePath
        if ($resolvedDownloadPath === false || !str_starts_with($resolvedDownloadPath, $resolvedSafePath)) {
            $this->logError(new Exception("Invalid path"), "The download path is not within the allowed directory");
            return false;
        }

        try {
            // Use Guzzle to download the file
            $response = $this->client->request('GET', $fileUrl, ['stream' => true]);

            // Validate response status
            if ($response->getStatusCode() !== 200) {
                $this->logError(new Exception("HTTP error " . $response->getStatusCode()), "Failed to download file from $fileUrl");
                return false;
            }

            // Check MIME type if allowed file types are specified
            if (!empty($allowedFileTypes)) {
                $contentType = $response->getHeaderLine('Content-Type');
                $allowedMimeTypes = MiscUtility::getMimeTypeStrings($allowedFileTypes);
                if (!in_array($contentType, $allowedMimeTypes)) {
                    $this->logError(new Exception("Invalid file type"), "The file type '$contentType' is not allowed.");
                    return false;
                }
            }

            // Save the file
            $fileResource = fopen($resolvedDownloadPath . DIRECTORY_SEPARATOR . $fileName, 'wb');
            if ($fileResource === false || stream_copy_to_stream($response->getBody()->detach(), $fileResource) === false) {
                if ($fileResource !== false) {
                    fclose($fileResource);
                }
                $this->logError(new Exception("Failed to save file"), "Unable to save the downloaded file.");
                return false;
            }
            fclose($fileResource);
            return true;
        } catch (Throwable $e) {
            $this->logError($e, "Unable to download file from $fileUrl");
            return false;
        }
    }

    public function sendJsonResponse(mixed $payload, ServerRequestInterface $request)
    {
        // Ensure payload is a JSON string
        $jsonPayload = is_array($payload) || is_object($payload) ? JsonUtility::encodeUtf8Json($payload) : $payload;

        // Check for json_encode errors
        if (json_last_error() != JSON_ERROR_NONE) {
            // Handle the error, maybe log it or set an error message
            return null;
        }

        // Get 'Accept-Encoding' header to check for supported compression methods
        $acceptEncoding = $request->getHeaderLine('Accept-Encoding');

        // Initialize variables for content encoding and payload
        $compressedPayload = null;
        $contentEncoding = null;

        // Gzip or deflate based on client capabilities
        if (strpos($acceptEncoding, 'gzip') !== false) {
            $compressedPayload = gzencode($jsonPayload);
            $contentEncoding = 'gzip';
        } elseif (strpos($acceptEncoding, 'deflate') !== false) {
            $compressedPayload = gzdeflate($jsonPayload);
            $contentEncoding = 'deflate';
        } else {
            // No compression supported or requested, send plain JSON
            $compressedPayload = $jsonPayload;
        }

        // Send headers based on content encoding
        if ($contentEncoding) {
            header("Content-Encoding: $contentEncoding");
            header('Content-Length: ' . mb_strlen($compressedPayload, '8bit'));
        }

        return $compressedPayload;
    }


    /**
     * Retrieves the bearer token from the Authorization header using ServerRequestInterface.
     *
     * @param ServerRequestInterface $request The request object.
     * @return string|null Returns the bearer token if present, otherwise null.
     */
    public function getAuthorizationBearerToken(ServerRequestInterface $request): ?string
    {
        $authorization = $request->getHeaderLine('Authorization');
        if (preg_match('/bearer\s+(\S+)/i', $authorization, $matches)) {
            return $matches[1];
        }

        return null;
    }


    /**
     * Retrieves a specific header value or values from the request.
     *
     * @param ServerRequestInterface $request The request object.
     * @param string $key The header key to retrieve.
     * @return string|array|null Returns the header values as a single string if concatenated, an array if multiple, or null if not present.
     */
    public function getHeader(ServerRequestInterface $request, string $key)
    {
        $headerValues = $request->getHeader($key);
        if (empty($headerValues)) {
            return null;
        } elseif (count($headerValues) === 1) {
            return $headerValues[0];
        } else {
            return $headerValues;
        }
    }
}
