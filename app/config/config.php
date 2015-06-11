<?php

// Global configuration
$app->config(array(
    'mode' => !empty($_SERVER['APP_MODE'])?$_SERVER['APP_MODE']:'development',
    'debug' => false, // Disable pretty exceptions

    // Logging
    'log.name' => 'coupons',
    'log.handlers' => array(
        new \Monolog\Handler\RotatingFileHandler(APP_BASE_PATH . '/logs/log'),
    ),
    'log.processors' => array(
        new \Monolog\Processor\WebProcessor()
    )
));

$mode = $app->config('mode');
$modeConfig = APP_PATH . '/config/' .$mode. '.php';
if (file_exists($modeConfig)) {
    include_once $modeConfig;
}
