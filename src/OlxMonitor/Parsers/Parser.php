<?php

declare(strict_types=1);

namespace Autodoctor\OlxWatcher\Parsers;

use Autodoctor\OlxWatcher\CurlTrait;
use Autodoctor\OlxWatcher\Exceptions\WatcherException;

class Parser
{
    use CurlTrait;

    private const STRING_START = '<title data-rh="true">';
    private const STRING_END = '</title>';
    private const CURRENCY_START = ': ';
    private const CURRENCY_STRING = 'грн';
    private const ERROR = 'Хьюстон, в нас проблема.';
    private const TIME_OUT = 15;
    private const CONNECT_TIMEOUT = 20;

    private string $targetUrl;
    private string $target = '';
    private string $title = '';
    private string $price = '';

    public function getPrice(): string
    {
        return $this->price;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @throws WatcherException
     */
    public function parse(): void
    {
        $this->target = $this->getUri($this->targetUrl);
        $this->checkUrl($this->target);
        $this->title = $this->parseTitle();
        $this->price = $this->parsePrice();
    }

    public function setTargetUrl(string $targetUrl): void
    {
        $this->targetUrl = $targetUrl;
    }

    /**
     * @throws WatcherException
     */
    protected function checkUrl(string $target): bool
    {
        if (stripos($target, self::STRING_END) === false) {
            throw new WatcherException('Target URL not available.');
        }
        return true;
    }

    private function cutter(string $input, string $start, string $end): string
    {
        $temp = stristr($input, $end, true);

        return str_replace(
            $start,
            '',
            strstr($temp, $start),
        );
    }

    private function parsePrice(): string
    {
        $tempPrice = trim(
            $this->cutter(
                input: $this->title,
                start: self::CURRENCY_START,
                end: self::CURRENCY_STRING,
            )
        );
        return str_replace(' ', '', $tempPrice);
    }

    private function parseTitle(): string
    {
        return $this->cutter(
            input: $this->target,
            start: self::STRING_START,
            end: self::STRING_END,
        );
    }
}
