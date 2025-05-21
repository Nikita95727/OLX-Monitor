<?php

declare(strict_types=1);

namespace Autodoctor\OlxWatcher\Subjects;

readonly class SubjectDto
{
    private function __construct(
        public string $previousPrice,
        public string $lastPrice,
        public string $previousTime,
        public string $lastTime,
        public array  $subscribers,
        public bool   $hasUpdate = false,
    ) {}

    public function toArray(): array
    {
        return [
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
            previousPrice: $data['previous_price'],
            lastPrice: $data['last_price'],
            previousTime: $data['previous_time'],
            lastTime: $data['last_time'],
            subscribers: $data['subscribers'],
            hasUpdate: $data['has_update'],
        );
    }

    public static function createFromRequest(string $price, string $email): self
    {
        $dateTime = (new \DateTime('now'))->format('Y-m-d H:i:s');

        return new self(
            previousPrice: $price,
            lastPrice: $price,
            previousTime: $dateTime,
            lastTime: $dateTime,
            subscribers: [$email],
            hasUpdate: false,
        );
    }

    public static function updatePrice(array $data, string $price): self
    {
        return new self(
            previousPrice: $data['last_price'],
            lastPrice: $price,
            previousTime: $data['last_time'],
            lastTime: (new \DateTime('now'))->format('Y-m-d H:i:s'),
            subscribers: $data['subscribers'],
            hasUpdate: true,
        );
    }

    public static function changeUpdateFlag(array $data): self
    {
        $data['has_update'] = false;

        return self::createFromArray($data);
    }

    public static function addSubscribers(array $data, string $email): self
    {
        $data['subscribers'][] = $email;

        return self::createFromArray($data);
    }

    public static function updateSubscribers(array $data, array $subscribers): SubjectDto
    {
        $data['subscribers'] = array_values($subscribers);

        return self::createFromArray($data);
    }
}
