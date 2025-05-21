<?php

declare(strict_types=1);

namespace Autodoctor\OlxWatcher\Enums;

enum FilesEnum: string
{
    public const CONFIG_FILE = __DIR__ . '/../../config.ini';
    public const LOG_FILE = __DIR__ . '/../../olx_watcher.log';
    public const SUBSCRIBE_FILE = __DIR__ . '/../../subscribe.db';
}
