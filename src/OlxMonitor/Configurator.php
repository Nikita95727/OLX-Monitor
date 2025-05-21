<?php

declare(strict_types=1);

namespace Autodoctor\OlxWatcher;

use Autodoctor\OlxWatcher\Enums\FilesEnum;
use Autodoctor\OlxWatcher\Exceptions\WatcherException;

class Configurator
{
    public const EXPIRATION = 60 * 60 * 24 * 7;

    /**
     * @throws WatcherException
     */
    public static function config(): array
    {
        $config = parse_ini_file(FilesEnum::CONFIG_FILE, true);

        if ($config === false) {
            throw new WatcherException('Error reading configuration.');
        }
        return $config;
    }

    /**
     * @throws WatcherException
     */
    public static function expiration(): int
    {
        return (int)Configurator::config()['cache']['exp'] ?? self::EXPIRATION;
    }
}
