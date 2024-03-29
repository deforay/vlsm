<?php

namespace App\Services;

use Whoops\Run;
use Whoops\Util\Misc;
use Gettext\Loader\MoLoader;
use App\Services\CommonService;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Handler\JsonResponseHandler;

class SystemService
{
    protected ?CommonService $commonService;

    public function __construct(CommonService $commonService)
    {
        $this->commonService = $commonService;
    }

    // Application Bootstrap
    public function bootstrap(): SystemService
    {
        $this->setLocale();
        $this->setDateTimeZone();

        return $this;
    }

    // Setup Translation
    public function setLocale($locale = null, $domain = "messages"): void
    {
        // Set the default locale
        $defaultLocale = 'en_US';

        // Determine the locale to use
        $_SESSION['APP_LOCALE'] = $locale ?? $_SESSION['userLocale'] ?? $_SESSION['APP_LOCALE'] ?? $this->commonService->getGlobalConfig('app_locale') ?? $defaultLocale;


        // Construct the path to the .mo file
        $moFilePath = sprintf(
            '%s%slocales%s%s%sLC_MESSAGES%s%s.mo',
            APPLICATION_PATH,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR,
            $_SESSION['APP_LOCALE'],
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR,
            $domain
        );

        // Initialize translations to null
        $_SESSION['translations'] = null;

        // Load translations if the locale is not the default and the .mo file exists
        if ($_SESSION['APP_LOCALE'] !== $defaultLocale && file_exists($moFilePath)) {
            $loader = new MoLoader();
            $translations = $loader->loadFile($moFilePath);

            // Store translations in the session
            $_SESSION['translations'] = $translations;
        }
    }

    public static function translate($text)
    {
        if ($text != null) {
            if (empty($_SESSION['translations']) || empty($_SESSION['translations']->find(null, $text))) {
                return $text;
            } else {
                return $_SESSION['translations']->find(null, $text)->getTranslation();
            }
        }
        return $text;
    }



    // Setup Timezone
    public function setDateTimeZone(): void
    {
        $this->commonService->setGlobalDateFormat();

        $_SESSION['APP_TIMEZONE'] = $_SESSION['APP_TIMEZONE'] ??
            $this->commonService->getGlobalConfig('default_time_zone') ?? 'UTC';
        date_default_timezone_set($_SESSION['APP_TIMEZONE']);
    }

    // Setup debugging
    public function debug($debugMode = false): SystemService
    {

        if ($debugMode) {

            error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);
            $whoops = new Run;

            // We want the error page to be shown by default, if this is a
            // regular request, so that's the first thing to go into the stack:
            $whoops->pushHandler(new PrettyPageHandler);

            // Now, we want a second handler that will run before the error page,
            // and immediately return an error message in JSON format, if something
            // goes awry.
            if (Misc::isAjaxRequest()) {
                $jsonHandler = new JsonResponseHandler;

                // Setup JsonResponseHandler to give a full stack trace:
                $jsonHandler->addTraceToOutput(true);

                // Return a result compliant to the json:api spec
                // re: http://jsonapi.org/examples/#error-objects
                // tl;dr: error[] becomes errors[[]]
                $jsonHandler->setJsonApi(true);
                $whoops->pushHandler($jsonHandler);
            }
            $whoops->register();
        }
        return $this;
    }

    public static function getActiveModules(bool $onlyTests = false): array
    {
        $activeModules = [];

        if ($onlyTests === false) {
            $activeModules = ['admin', 'dashboard', 'common'];
        }
        return array_merge($activeModules, array_keys(array_filter(SYSTEM_CONFIG['modules'])));
    }
}
