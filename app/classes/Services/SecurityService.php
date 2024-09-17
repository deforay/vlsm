<?php

namespace App\Services;

use App\Utilities\MiscUtility;
use App\Exceptions\SystemException;
use Laminas\Diactoros\ServerRequest;

final class SecurityService
{
    public function __construct() {}

    public static function checkContentLength(ServerRequest $request)
    {
        $contentLength = $request->getHeaderLine('Content-Length');
        if ($contentLength && strlen($request->getBody()->getContents()) !== (int)$contentLength) {
            throw new SystemException(_translate('Invalid Request. Please try again'));
        }
    }
    public static function checkCSRF(ServerRequest $request, bool $invalidate = false): void
    {
        if ($request->getMethod() === 'POST' && isset($_SESSION['csrf_token'])) {
            $csrfToken = null;

            if (CommonService::isAjaxRequest($request)) {
                $csrfToken = $request->getHeaderLine('X-CSRF-Token') ?? null;
                $invalidate = false;
            } else {
                $csrfToken = $request->getParsedBody()['csrf_token'] ?? null;
            }

            if (isset($_SESSION['csrf_token_time']) && time() - $_SESSION['csrf_token_time'] > 3600) { // 1 hour expiration
                throw new SystemException(_translate('Request token expired. Please refresh the page and try again.'));
            }
            if (!$csrfToken || $csrfToken !== $_SESSION['csrf_token']) {
                throw new SystemException(_translate('Invalid Request token. Please refresh the page and try again.'));
            }
            // Invalidate the CSRF token if requested
            if ($invalidate) {
                // Remove or regenerate the CSRF token after successful validation
                self::invalidateCSRF();
                self::generateCSRF();
            }
        }
    }
    private static function generateCSRF()
    {
        if (!isset($_SESSION['csrf_token']) || time() - ($_SESSION['csrf_token_time'] ?? 0) > 3600) {
            $_SESSION['csrf_token_time'] = time();
            $_SESSION['csrf_token'] = MiscUtility::generateRandomString();
        }
    }
    private static function invalidateCSRF()
    {
        if (isset($_SESSION['csrf_token'])) {
            unset($_SESSION['csrf_token']);
            unset($_SESSION['csrf_token_time']);
        }
    }
}