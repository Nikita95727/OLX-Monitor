<?php

declare(strict_types=1);

namespace Autodoctor\OlxWatcher\Subjects;

use SplObserver;

abstract class AbstractSubject implements \SplSubject
{
    public \SplObjectStorage $observers;

    public function __construct()
    {
        $this->observers = new \SplObjectStorage();
    }

    abstract public function notify(): void;

    public function attach(SplObserver $observer): void
    {
        $this->observers->attach($observer);
    }

    public function detach(SplObserver $observer): void
    {
        $this->observers->detach($observer);
    }
}
