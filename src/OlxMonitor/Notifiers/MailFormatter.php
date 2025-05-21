<?php

declare(strict_types=1);

namespace Autodoctor\OlxWatcher\Notifiers;

use Autodoctor\OlxWatcher\Subjects\AdsDTO;

trait MailFormatter
{
    public const RN = "\r\n";
    public const UNSUBSCRIBE = 'Click the following URL to unsubscribe from this service: ';

    protected function formatMessage(string $email, AdsDTO $adsDTO): string
    {
        return $this->formatPriceUpdateMessage($adsDTO) . self::RN
            . self::UNSUBSCRIBE . self::RN
            . $this->formatUnsubscribeUrl($email, $adsDTO);
    }

    protected function formatPriceUpdateMessage(AdsDTO $adsDTO): string
    {
        return sprintf(
            '%s: %s %s %s New price: %s at %s %s Previous price: %s at %s.',
            $this->config['mail']['message'],
            self::RN,
            $adsDTO->id,
            self::RN,
            $adsDTO->lastPrice,
            $adsDTO->lastTime,
            self::RN,
            $adsDTO->previousPrice,
            $adsDTO->previousTime
        );
    }

    protected function formatUnsubscribeUrl(string $email, AdsDTO $adsDTO): string
    {
        return sprintf(
            '%s?status=unsubscribe&email=%s&url=%s',
            $this->config['metadata']['app_url'],
            $email,
            $adsDTO->id
        );
    }
}
