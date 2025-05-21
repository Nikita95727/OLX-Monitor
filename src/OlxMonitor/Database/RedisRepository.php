<?php

declare(strict_types=1);

namespace Autodoctor\OlxWatcher\Database;

use Autodoctor\OlxWatcher\Configurator;
use Autodoctor\OlxWatcher\Exceptions\WatcherException;
use Redis;
use RedisException;

class RedisRepository extends SubjectCollection implements Cache
{
    /**
     * @throws WatcherException|RedisException
     */
    public function __construct(
        protected Redis $redis,
    ) {
        $this->redis->connect(Configurator::config()['redis']['host']);
        $this->redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);
        parent::__construct();
    }

    /**
     * @throws RedisException
     */
    public function clear(): bool
    {
        return $this->redis->flushAll();
    }

    /**
     * @throws RedisException
     */
    public function get(string $key): mixed
    {
        return ($this->redis->get($key));
    }

    /**
     * @throws RedisException
     */
    public function mGet(array $keys): array
    {
        $values = $this->redis->mGet($keys);

        return $values === false ? [] : $values;
    }

    /**
     * @throws RedisException
     */
    public function keys(string $keyPattern = '*'): array
    {
        $keys = $this->redis->keys($keyPattern);

        return $keys === false ? [] : $keys;
    }

    /**
     * @throws RedisException|WatcherException
     */
    public function set(string $key, mixed $value): bool
    {
        return $this->redis->set($key, $value, Configurator::expiration());
    }

    /**
     * @throws RedisException
     */
    public function mSet(array $data): bool
    {
        return $this->redis->mSet($data);
    }

    /**
     * @throws RedisException
     */
    protected function loadData(): array
    {
        $keys = $this->keys();

        return array_combine($keys, $this->mGet($keys));
    }

    /**
     * @throws RedisException|WatcherException
     */
    protected function saveData(): void
    {
        foreach ($this->getIterator() as $url => $subject) {
            $this->set($url, $subject);
        }
    }
}
