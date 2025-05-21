<?php

declare(strict_types=1);

namespace Autodoctor\OlxWatcher;

use Autodoctor\OlxWatcher\Exceptions\WatcherException;

class FileProcessor
{
    /**
     * @throws WatcherException
     */
    public static function getContent(string $fileName): string
    {
        $data = file_get_contents($fileName);

        if ($data === false) {
            throw new WatcherException(sprintf('Error reading %s file.', $fileName));
        }
        return $data;
    }

    /**
     * @throws WatcherException
     */
    public static function putContent(string $fileName, mixed $data, int $flag = 0): int
    {
        $result = file_put_contents($fileName, $data, $flag);

        if ($result === false) {
            throw new WatcherException(sprintf('Error writing to file %s.', $fileName));
        }
        return $result;
    }
}
