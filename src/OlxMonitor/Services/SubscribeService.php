<?php

declare(strict_types=1);

namespace Autodoctor\OlxWatcher\Services;

use Autodoctor\OlxWatcher\Exceptions\WatcherException;
use Autodoctor\OlxWatcher\Subjects\AdsDTO;

class SubscribeService extends BaseService
{
    public const NEW_SUBSCRIBE = 'Subscription to the resource has been completed.';
    public const UNSUBSCRIBE = 'Unsubscribe from the resource is complete.';

    /**
     * @throws WatcherException
     */
    public function subscribe(string $url, string $email): string
    {
        if ($this->DTO === false) {
            $price = $this->getPrice($url);
            $this->DTO = AdsDTO::createFromRequest($price, $email, $url);
        } else {
            $this->DTO = AdsDTO::addSubscriber($this->DTO->toArray(), $email);
        }
        $this->logger->notice(self::NEW_SUBSCRIBE, [$email, $url]);
        $this->cache->offsetSet($url, $this->DTO);

        return self::NEW_SUBSCRIBE;
    }

    public function unsubscribe(string $url, string $email): string
    {
        if ($this->DTO) {
            $this->DTO = AdsDTO::deleteSubscriber($this->DTO->toArray(), $email);
            $this->logger->notice(self::UNSUBSCRIBE, [$email, $url]);
            $this->cache->offsetSet($url, $this->DTO);
        }
        return self::UNSUBSCRIBE;
    }
}
