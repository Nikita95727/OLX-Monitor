<?php

declare(strict_types=1);

namespace Autodoctor\OlxWatcher\Notifiers;

use Autodoctor\OlxWatcher\Configurator;
use Autodoctor\OlxWatcher\Exceptions\WatcherException;

class NotifierFactory
{
    /**
     * @throws WatcherException
     */
    public static function getNotifier(): Notifier
    {
        $notifierType = Configurator::config()['notifier']['type'];

        return match ($notifierType) {
            'telegram' => new TelegramNotifier(),
            default => new MailNotifier()
        };
    }
}
