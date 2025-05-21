<?php

declare(strict_types=1);

namespace Autodoctor\OlxWatcher\Notifiers;

use Autodoctor\OlxWatcher\Subjects\Subject;
use SplSubject;

abstract class Observer implements Notifier
{
    abstract public function notice(string $subscriberId, Subject $subject): int;

    public function update(SplSubject|Subject $subject): void
    {
        array_map(
            fn($subscriber) => $this->notice($subscriber, $subject),
            $subject->getDTO()->subscribers,
        );
    }
}
