<?php

declare(strict_types=1);

namespace Autodoctor\OlxWatcher\Notifiers;

use Autodoctor\OlxWatcher\Subjects\Subject;

interface Notifier extends \SplObserver
{
    public function notice(string $subscriberId, Subject $subject): int;
}
