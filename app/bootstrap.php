<?php

// Define application paths
define('APP_PATH', dirname(__FILE__));
define('APP_BASE_PATH', realpath(APP_PATH . '/..'));

// Start the session
session_cache_limiter(false);
session_start();

// Composer autoloading
require APP_BASE_PATH . '/vendor/autoload.php';

$app = new \Slim\Slim();

// Include config
require 'config/config.php';

// Set the timezone
$timezone = $app->config('timezone');
if ($timezone) {
    date_default_timezone_set($timezone);
}

// Setup logging
require 'middleware/logging.php';
$app->add(new MonologMiddleware());

// Utils
require 'utils/email.php';
require 'utils/template.php';

// API Middleware
require 'middleware/api.php';
$app->add(new ApiMiddleware());

// Database middleware
require 'middleware/database.php';
$app->add(new DoctrineDBALMiddleware());

// Setup error logging
$app->hook('api.error', function ($error) use ($app) {
    $app->log->notice(sprintf('api error: %s (%d)', $error['message'],
        $error['code']));
});
$app->hook('api.unathorized', function ($error) use ($app) {
    $app->log->notice(sprintf('unauthorized api access: %s (%d)',
        $error['code'], $error['message']));
});
$app->error(function (\Exception $e) use ($app) {
    $app->log->critical('app error: ' . $e->getMessage());
    $app->log->critical($e->getTraceAsString());
    $app->api->error(500, -1, 'something bad happened');
});

// Add routes
require 'routes.php';
