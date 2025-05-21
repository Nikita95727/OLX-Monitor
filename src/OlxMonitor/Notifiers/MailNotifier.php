<?php

declare(strict_types=1);

namespace Autodoctor\OlxWatcher\Notifiers;

use Autodoctor\OlxWatcher\Configurator;
use Autodoctor\OlxWatcher\Exceptions\MailerException;
use Autodoctor\OlxWatcher\Exceptions\WatcherException;
use Autodoctor\OlxWatcher\Subjects\AdsDTO;
use Autodoctor\OlxWatcher\Subjects\Subject;

class MailNotifier extends Observer
{
    use MailFormatter;

    protected array $config = [];

    /**
     * @throws WatcherException
     */
    public function __construct()
    {
        $this->setConfig();
    }

    /**
     * @throws WatcherException
     */
    public function setConfig(): void
    {
        $this->config = Configurator::config();
    }

    public function sendMail(string $email, AdsDTO $adsDTO): bool
    {
        $to = $email;
        $subject = $this->config['mail']['subject'];
        $message = $this->formatMessage($email, $adsDTO);
        $headers[] = 'From: ' . $this->config['mail']['sender'];
        $headers[] = 'X-Mailer: PHP/' . phpversion();

        return mail($to, $subject, $message, implode(self::RN, $headers));
    }

    /**
     * @throws MailerException
     */
    public function send(string $email, AdsDTO $adsDTO): int
    {
        if ($this->sendMail($email, $adsDTO)) {
            return 0;
        }
        throw new MailerException(
            sprintf(
                'The email is not sent to the recipient: %s',
                $email
            )
        );
    }

    /**
     * @throws MailerException
     */
    public function notice(string $subscriberId, Subject $subject): int
    {
        return $this->send($subscriberId, $subject->getDTO());
    }
}
