<?php

class SqlLogger implements \Doctrine\DBAL\Logging\SQLLogger
{
    protected $log = null;

    public function __construct($log) {
        $this->log = $log;
    }

    public function startQuery($sql, array $params = null, array $types = null)
    {
        $this->log->debug(sprintf('sql: %s. params(%s) types(%s)', $sql, implode(', ', $params), implode(', ', $types)));
    }

    public function stopQuery() {}
}

class DoctrineDBALMiddleware extends \Slim\Middleware
{
    public function __construct() {
        $app = \Slim\Slim::getInstance();
        $app->container->singleton('db', function () use ($app) {
            $log = $app->log;

            $dbConfig = $app->config('database');
            if (! $dbConfig) {
                $log->critical('No database configuration found');
            }

            try {
                $db = \Doctrine\DBAL\DriverManager::getConnection($dbConfig);
		$db->getConfiguration()->setSQLLogger(new SQLLogger($log));
                return $db;

            } catch (\Doctrine\DBAL\DBALException $e) {
                $log->critical('Error while configuring database: ' . $e->getMessage());
                die();
            }
        });
    }

    public function call()
    {
        $this->next->call();
    }
}
