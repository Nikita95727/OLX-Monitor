<?php

declare(strict_types=1);

namespace Autodoctor\OlxWatcher\Database;

use Autodoctor\OlxWatcher\Enums\FilesEnum;
use Autodoctor\OlxWatcher\Exceptions\WatcherException;
use Autodoctor\OlxWatcher\FileProcessor;

class FileRepository extends SubjectCollection implements Cache
{
    public function all(): array
    {
        return $this->getArrayCopy();
    }

    public function clear(): bool
    {
        $this->exchangeArray([]);

        return true;
    }

    public function get(string $key): mixed
    {
        return $this->offsetExists($key) ? $this->offsetGet($key) : false;
    }

    public function mGet(array $keys): array
    {
        if ($keys === []) {
            return $this->all();
        }
        return array_filter($this->all(), fn($key) => in_array($key, $keys), ARRAY_FILTER_USE_KEY);
    }

    public function keys(string $keyPattern = ''): array
    {
        return array_filter(
            array_keys($this->all()),
            fn($key) => str_starts_with($key, $keyPattern)
        );
    }

    public function set(string $key, mixed $value): bool
    {
        $this->offsetSet($key, $value);

        return true;
    }

    public function mSet(array $data): bool
    {
        array_map(fn($value, $key) => $this->offsetSet($key, $value), $data, array_keys($data));

        return true;
    }

    public function remove(string $key): void
    {
        if ($this->offsetExists($key)) {
            $this->offsetUnset($key);
        }
    }

    /**
     * @throws WatcherException
     */
    protected function loadData(): array
    {
        $content = FileProcessor::getContent(FilesEnum::SUBSCRIBE_FILE);

        if ($content === '') {
            return [];
        }
        $this->unserialize($content);

        return $this->all();
    }

    /**
     * @throws WatcherException
     */
    protected function saveData(): void
    {
        $data = $this->serialize();
        FileProcessor::putContent(FilesEnum::SUBSCRIBE_FILE, $data, LOCK_EX);
    }

    /**
     * @throws \Exception
     */
    public function isExpired(string $lastTime, int $expirationInterval): bool
    {
        $nowTimestamp = (new \DateTime('now'))->getTimestamp();
        $lastTimestamp = (new \DateTime($lastTime))->getTimestamp();

        return $nowTimestamp - $expirationInterval > $lastTimestamp;
    }
}
