<?php

declare(strict_types=1);

namespace Autodoctor\OlxWatcher\Services;

use Autodoctor\OlxWatcher\Database\RepositoryFactory;
use Autodoctor\OlxWatcher\Database\Cache;
use Autodoctor\OlxWatcher\Subjects\DTO;
use Autodoctor\OlxWatcher\Subjects\Subject;
use Autodoctor\OlxWatcher\Exceptions\WatcherException;
use Autodoctor\OlxWatcher\Parsers\ParserFactory;
use Psr\Log\LoggerAwareTrait;
use RedisException;

class BaseService
{
    use LoggerAwareTrait;

    protected Cache $cache;
    protected DTO|false $DTO;
    protected Subject $subject;

    /**
     * @throws RedisException|WatcherException
     */
    public function __construct()
    {
        $this->setCache();
        $this->subject = new Subject();
    }

    /**
     * @throws RedisException|WatcherException
     */
    public function setCache(): void
    {
        $this->cache = RepositoryFactory::getCacheDriver();
    }

    public function setDTO(string $url): void
    {
        $data = $this->cache->offsetGet($url);
        $this->DTO = is_a($data, DTO::class) ? $data : false;
    }

    /**
     * @throws WatcherException
     */
    protected function getPrice(string $url): string
    {
        $parser = ParserFactory::getParser();
        $parser->setTargetUrl($url);
        $parser->parse();

        return $parser->getPrice();
    }
}
