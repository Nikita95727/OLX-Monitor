<?php

declare(strict_types=1);

namespace Autodoctor\OlxWatcher\Database;

use Autodoctor\OlxWatcher\Configurator;
use Autodoctor\OlxWatcher\Exceptions\WatcherException;
use Redis;

class RepositoryFactory
{
    /**
     * @throws WatcherException|\RedisException
     */
    public static function getCacheDriver(): Cache
    {
        $cacheDriver = Configurator::config()['cache']['type'];

        return match ($cacheDriver) {
            'redis' => new RedisRepository(new Redis()),
            default => new FileRepository(),
        };
    }
}
