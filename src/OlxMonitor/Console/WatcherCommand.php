<?php

declare(strict_types=1);

namespace Autodoctor\OlxWatcher\Console;

require __DIR__ . '/../../../vendor/autoload.php';

echo date('Y-m-d H:i:s') . Watcher::START . PHP_EOL;
$command = new Watcher();
$result = $command();
echo date('Y-m-d H:i:s') . Watcher::STOP . PHP_EOL;

return $result;
