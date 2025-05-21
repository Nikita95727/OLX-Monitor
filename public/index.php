<?php

use Autodoctor\OlxWatcher\Console\Subscribe;

require __DIR__ . '/../vendor/autoload.php';

$command = new Subscribe();

echo $command();
