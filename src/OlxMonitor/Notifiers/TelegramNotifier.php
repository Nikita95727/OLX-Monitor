<?php

declare(strict_types=1);

namespace Autodoctor\OlxWatcher\Notifiers;

use Autodoctor\OlxWatcher\Subjects\Subject;

class TelegramNotifier extends Observer
{
    public function notice(string $subscriberId, Subject $subject): int
    {
        // TODO: Implement notice() method.
    }
}
