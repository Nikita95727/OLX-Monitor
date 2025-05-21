<?php

declare(strict_types=1);

namespace Autodoctor\OlxWatcher\Subjects;

readonly class AdsDTO implements DTO
{
    private function __construct(
        public string $id,
        public string $previousPrice,
        public string $lastPrice,
        public string $previousTime,
        public string $lastTime,
        public array  $subscribers,
        public bool   $hasUpdate = false,
    ) {
    }

    public function toArray(): array
    {
        return [
            'url' => $this->id,
            'previous_price' => $this->previousPrice,
            'last_price' => $this->lastPrice,
            'previous_time' => $this->previousTime,
            'last_time' => $this->lastTime,
            'subscribers' => $this->subscribers,
            'has_update' => $this->hasUpdate,
        ];
    }

    public static function createFromArray(array $data): self
    {
        return new self(
            id: $data['url'],
            previousPrice: $data['previous_price'],
            lastPrice: $data['last_price'],
            previousTime: $data['previous_time'],
            lastTime: $data['last_time'],
            subscribers: $data['subscribers'],
            hasUpdate: $data['has_update'],
        );
    }

    public static function createFromRequest(string $price, string $email, string $url): self
    {
        $dateTime = (new \DateTime('now'))->format('Y-m-d H:i:s');

        return new self(
            id: $url,
            previousPrice: $price,
            lastPrice: $price,
            previousTime: $dateTime,
            lastTime: $dateTime,
            subscribers: [$email],
            hasUpdate: false,
        );
    }

    public static function changeUpdateFlag(array $data): self
    {
        $data['has_update'] = false;

        return self::createFromArray($data);
    }

    public static function addSubscriber(array $data, string $email): self
    {
        if (!in_array($email, $data['subscribers'])) {
            $data['subscribers'][] = $email;
        }
        return self::createFromArray($data);
    }

    public static function deleteSubscriber(array $data, string $subscriber): self
    {
        $data['subscribers'] = self::filter($data['subscribers'], $subscriber);

        return self::createFromArray($data);
    }

    public static function updatePrice(array $data, string $price): self
    {
        return new self(
            id: $data['url'],
            previousPrice: $data['last_price'],
            lastPrice: $price,
            previousTime: $data['last_time'],
            lastTime: (new \DateTime('now'))->format('Y-m-d H:i:s'),
            subscribers: $data['subscribers'],
            hasUpdate: true,
        );
    }

    private static function filter(array $subscribers, string $subscriber): array
    {
        return array_filter(
            $subscribers,
            fn($item) => $item !== $subscriber,
        );
    }
}
