<?php

namespace App\HttpHandlers;

use App\Registries\ContainerRegistry;
use App\Services\CommonService;
use MysqliDb;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\Response\RedirectResponse;

class LegacyRequestHandler implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {

        /** @var MysqliDb $db */
        $db = ContainerRegistry::get('db');

        /** @var CommonService $general */
        $general = ContainerRegistry::get(CommonService::class);

        // Get the requested URI
        $uri = $request->getUri()->getPath();

        // Clean up the URI
        $uri = preg_replace('/([\/.])\1+/', '$1', $uri);
        $uri = trim(parse_url($uri, PHP_URL_PATH), "/");

        //error_log("RequestHandler 1 :::" . $uri);

        switch ($uri) {
            case null:
            case '':
                $fileToInclude = APPLICATION_PATH . '/index.php';
                break;
            default:
                if (is_dir(APPLICATION_PATH . DIRECTORY_SEPARATOR . $uri)) {
                    $fileToInclude = (APPLICATION_PATH . DIRECTORY_SEPARATOR . $uri . '/index.php');
                } elseif (is_file(APPLICATION_PATH . DIRECTORY_SEPARATOR . $uri)) {
                    $fileToInclude = (APPLICATION_PATH . DIRECTORY_SEPARATOR . $uri);
                } else {
                    http_response_code(404);
                    $fileToInclude = APPLICATION_PATH . '/error/404.php';
                }
                break;
        }

        //error_log("RequestHandler 2 :::" . $fileToInclude);

        // Capture output buffer to prevent it from being sent directly
        ob_start();
        require_once $fileToInclude;
        // Get the output buffer content and clean the buffer
        $output = ob_get_clean();

        // Check if there's a Location header in the headers_list()
        $location = null;
        foreach (headers_list() as $header) {
            if (stripos($header, 'Location:') === 0) {
                $location = trim(substr($header, strlen('Location:')));
                break;
            }
        }

        // If a Location header is found, create a new RedirectResponse
        if ($location !== null) {
            header_remove('Location');
            return new RedirectResponse($location);
        } else {
            // Create a new response with the captured output
            $response = new Response();
            $response->getBody()->write($output);

            return $response;
        }
    }
}