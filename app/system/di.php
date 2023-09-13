<?php

use DI\ContainerBuilder;
use App\Services\TbService;
use App\Services\VlService;
use App\Services\ApiService;
use App\Services\EidService;
use App\Services\BatchService;
use App\Services\UsersService;
use App\Utilities\DateUtility;
use App\Utilities\MiscUtility;
use App\Services\CommonService;
use App\Services\SystemService;
use App\Services\AppMenuService;
use App\Services\Covid19Service;
use App\Services\PatientsService;
use App\Utilities\CaptchaUtility;
use App\Services\HepatitisService;
use App\Helpers\PdfWatermarkHelper;
use App\Services\FacilitiesService;
use App\Services\InstrumentsService;
use App\Services\TestResultsService;
use App\Helpers\PdfConcatenateHelper;
use App\Registries\ContainerRegistry;
use App\Services\GenericTestsService;
use App\Services\GeoLocationsService;
use App\Utilities\ImageResizeUtility;
use Psr\Container\ContainerInterface;
use App\HttpHandlers\LegacyRequestHandler;
use App\Middlewares\Api\ApiAuthMiddleware;
use App\Middlewares\App\AppAuthMiddleware;
use App\Middlewares\ErrorHandlerMiddleware;
use Laminas\Config\Factory as ConfigFactory;
use App\ErrorHandlers\ErrorResponseGenerator;
use App\Middlewares\SystemAdminAuthMiddleware;
use App\Middlewares\Api\ApiErrorHandlingMiddleware;
use App\Middlewares\Api\ApiLegacyFallbackMiddleware;

try {
    // Load configuration
    $configFile = ROOT_PATH . "/configs/config." . APPLICATION_ENV . ".php";
    if (!file_exists($configFile)) {
        $configFile = ROOT_PATH . "/configs/config.production.php";
    }

    $systemConfig = ConfigFactory::fromFile($configFile);

    // Detect if debug mode is enabled
    $debugMode = $systemConfig['system']['debug_mode'] ?? false;

    // Detect if script is running in CLI mode
    $isCli = php_sapi_name() === 'cli';
} catch (Exception $e) {
    echo "Error loading configuration file: Please ensure the config file is present";
    exit;
}

$builder = new ContainerBuilder();
$builder->useAutowiring(true);

// Enable compilation for better performance in production
if (!$isCli && !empty($systemConfig['system']['cache_di']) && true === $systemConfig['system']['cache_di']) {

    if (!is_dir(CACHE_PATH)) {
        mkdir(CACHE_PATH, 0777, true);
    }
    $builder->enableCompilation(CACHE_PATH);
    $builder->enableDefinitionCache(CACHE_PATH);
}

// Configuration and DB
$builder->addDefinitions([
    'applicationConfig' => $systemConfig,
    'db' => DI\factory(
        function (ContainerInterface $c) {
            return new MysqliDb($c->get('applicationConfig')['database']);
        }
    )
]);

// Services
$builder->addDefinitions([
    SystemService::class => DI\create(SystemService::class)
        ->constructor(DI\get(CommonService::class)),
    CommonService::class => DI\create(CommonService::class)
        ->constructor(DI\get('db')),
    BatchService::class => DI\create(BatchService::class)
        ->constructor(DI\get('db')),
    VlService::class => DI\create(VlService::class)
        ->constructor(DI\get('db'), DI\get(CommonService::class), DI\get(GeoLocationsService::class)),
    EidService::class => DI\create(EidService::class)
        ->constructor(DI\get('db'), DI\get(CommonService::class), DI\get(GeoLocationsService::class)),
    Covid19Service::class => DI\create(Covid19Service::class)
        ->constructor(DI\get('db'), DI\get(CommonService::class), DI\get(GeoLocationsService::class)),
    HepatitisService::class => DI\create(HepatitisService::class)
        ->constructor(DI\get('db'), DI\get(CommonService::class), DI\get(GeoLocationsService::class)),
    TbService::class => DI\create(TbService::class)
        ->constructor(DI\get('db'), DI\get(CommonService::class), DI\get(GeoLocationsService::class)),
    GenericTestsService::class => DI\create(GenericTestsService::class)
        ->constructor(DI\get('db'), DI\get(CommonService::class), DI\get(GeoLocationsService::class)),
    UsersService::class => DI\create(UsersService::class)
        ->constructor(DI\get('db'), DI\get('applicationConfig'), DI\get(CommonService::class)),
    GeoLocationsService::class => DI\create(GeoLocationsService::class)
        ->constructor(DI\get('db')),
    TestResultsService::class => DI\create(TestResultsService::class)
        ->constructor(DI\get('db')),
    AppMenuService::class => DI\create(AppMenuService::class)
        ->constructor(DI\get('db'), DI\get(CommonService::class), DI\get(UsersService::class)),
    FacilitiesService::class => DI\create(FacilitiesService::class)
        ->constructor(DI\get('db')),
    InstrumentsService::class => DI\create(InstrumentsService::class)
        ->constructor(DI\get('db')),
    PatientsService::class => DI\create(PatientsService::class)
        ->constructor(DI\get('db')),
    ApiService::class => DI\create(ApiService::class)
        ->constructor(DI\get('db')),
]);

// Middlewares
$builder->addDefinitions([
    LegacyRequestHandler::class => DI\create(LegacyRequestHandler::class),
    AppAuthMiddleware::class => DI\create(AppAuthMiddleware::class),
    SystemAdminAuthMiddleware::class => DI\create(SystemAdminAuthMiddleware::class),
    ApiAuthMiddleware::class => DI\create(ApiAuthMiddleware::class)
        ->constructor(DI\get(UsersService::class)),
    ErrorHandlerMiddleware::class => DI\create(ErrorHandlerMiddleware::class)
        ->constructor(DI\get(ErrorResponseGenerator::class)),
    ApiErrorHandlingMiddleware::class => DI\create(ApiErrorHandlingMiddleware::class)
        ->constructor(DI\get(ErrorResponseGenerator::class)),
    ApiLegacyFallbackMiddleware::class => DI\create(ApiLegacyFallbackMiddleware::class)
]);

// Utilities, Helpers and Other Classes
$builder->addDefinitions([
    DateUtility::class => DI\create(DateUtility::class),
    ImageResizeUtility::class => DI\create(ImageResizeUtility::class),
    CaptchaUtility::class => DI\create(CaptchaUtility::class),
    MiscUtility::class => DI\create(MiscUtility::class),
    ErrorResponseGenerator::class => DI\create(ErrorResponseGenerator::class)
        ->constructor($debugMode),
    PdfConcatenateHelper::class => DI\create(PdfConcatenateHelper::class),
    PdfWatermarkHelper::class => DI\create(PdfWatermarkHelper::class)
]);


$container = $builder->build();

// Putting $container into a singleton registry for access across the application
ContainerRegistry::setContainer($container);
