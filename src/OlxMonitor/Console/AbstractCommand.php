<?php

declare(strict_types=1);

namespace Autodoctor\OlxWatcher\Console;

use Autodoctor\OlxWatcher\Logger\Logger;

abstract class AbstractCommand
{
    abstract public function __invoke(): int|string;

    public function handler(string $serviceName): int|string
    {
        $logger = new Logger();

        try {
            $closure = $this->commandClosure($serviceName, $logger);

            return $closure();
        } catch (\Throwable $e) {
            $logger->error(static::ERROR, $logger->getExceptionContext($e));

            return static::ERROR . $e->getMessage();
        }
    }

    protected function commandClosure(string $serviceName, Logger $logger): \Closure
    {
        return function () use ($logger, $serviceName) {
            $logger->info(static::START);
            $service = new $serviceName();
            $service->setLogger($logger);
            $result = $service();
            $logger->info(static::STOP);

            return $result;
        };
    }
}
