<?php declare(strict_types=1);

namespace App\Factory;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class LoggerFactory
{
    /**
     * Create logger instance
     *
     * @param string $filePath
     * @param string $name
     * @return Logger
     * @throws \Exception
     */
    public static function create(string $filePath, string $name = 'default')
    {
        $logger = new Logger($name);
        $logFilePath = sprintf('%s/../../%s', __DIR__, $filePath);
        $fileHandler = new StreamHandler($logFilePath, Logger::DEBUG);
        $logger->pushHandler($fileHandler);

        return $logger;
    }
}
