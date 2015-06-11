<?php

class MonologLogWriter {
    protected $logger;
    protected $config;

    protected $log_level = array(
        \Slim\Log::EMERGENCY => \Monolog\Logger::EMERGENCY,
        \Slim\Log::ALERT => \Monolog\Logger::ALERT,
        \Slim\Log::CRITICAL => \Monolog\Logger::CRITICAL,
        \Slim\Log::ERROR => \Monolog\Logger::ERROR,
        \Slim\Log::WARN => \Monolog\Logger::WARNING,
        \Slim\Log::NOTICE => \Monolog\Logger::NOTICE,
        \Slim\Log::INFO => \Monolog\Logger::INFO,
        \Slim\Log::DEBUG => \Monolog\Logger::DEBUG,
    );

    public function __construct($config) {
        $this->config = $config;
    }

    public function write($object, $level) {
        if (! $this->logger) {
            $this->logger = new \Monolog\Logger($this->config['name']);

            foreach ($this->config['handlers'] as $handler) {
                $this->logger->pushHandler($handler);
            }

            foreach ($this->config['processors'] as $processor ) {
                $this->logger->pushProcessor($processor);
            }
        }

        $this->logger->addRecord(
            $this->log_level[$level] ?: \Monolog\Logger::WARNING,
            $object
        );
    }
}

class MonologMiddleware extends \Slim\Middleware {
    public function __construct() {
        $app = \Slim\Slim::getInstance();
        $config = array(
            'name' => $app->config('log.name') ?: 'slim_app',
            'handlers' => $app->config('log.handlers') ?: array(),
            'processors' => $app->config('log.processors') ?: array()
        );

        $logger = new MonologLogWriter($config);
        $app->config('log.writer', $logger);
    }

    public function call() {
        $this->next->call();
    }
}
