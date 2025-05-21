<?php

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

return [
    'default' => 'file',
    'channels' => [
        'file' => [
            'driver' => 'monolog',
            'path' => getenv('LOG_PATH') ?: __DIR__ . '/../../storage/logs/olx-watcher.log',
            'level' => getenv('LOG_LEVEL') ?: 'debug',
            'bubble' => true,
            'permission' => 0664,
        ],
    ],
    'handlers' => [
        'file' => function () {
            $logger = new Logger('olx-watcher');
            $logger->pushHandler(new StreamHandler(
                getenv('LOG_PATH') ?: __DIR__ . '/../../storage/logs/olx-watcher.log',
                Logger::DEBUG
            ));
            return $logger;
        },
    ],
]; 