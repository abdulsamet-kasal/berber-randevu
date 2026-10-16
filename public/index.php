<?php

declare(strict_types=1);

use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Dotenv\Dotenv;
use App\Middleware\CsrfMiddleware;
use App\Middleware\RateLimitMiddleware;

require __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$app = AppFactory::create();

// Parse JSON / form bodies so API controllers receive parsed data
$app->addBodyParsingMiddleware();

// Security headers
$app->add(function (Request $request, $handler) {
    $response = $handler->handle($request);
    return $response
        ->withHeader('X-Content-Type-Options', 'nosniff')
        ->withHeader('X-Frame-Options', 'SAMEORIGIN')
        ->withHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
});

// Add middlewares
$app->add(new CsrfMiddleware());
$app->add(new RateLimitMiddleware(60, 60)); // 60 requests per minute

// Add error handling middleware (detailed errors only when APP_DEBUG=true)
$debug = filter_var($_ENV['APP_DEBUG'] ?? 'false', FILTER_VALIDATE_BOOLEAN);
$app->addErrorMiddleware($debug, true, true);

// Include routes
require __DIR__ . '/../src/routes.php';

$app->run();
