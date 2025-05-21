<?php

declare(strict_types=1);

namespace Autodoctor\OlxMonitor\Console;

use Autodoctor\OlxMonitor\Services\MonitorService;

class Monitor extends AbstractCommand
{
    public const START = 'Watcher started.';
    public const STOP = 'Watcher stopped.';
    protected const ERROR = 'Watcher error. ';

    public function __invoke(): int|string
    {
        return $this->handler(MonitorService::class);
    }
}
