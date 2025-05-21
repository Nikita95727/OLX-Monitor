<?php

declare(strict_types=1);

namespace Autodoctor\OlxWatcher\Parsers;

class ParserFactory
{
    public static function getParser(): Parser
    {
        return new Parser();
    }
}
