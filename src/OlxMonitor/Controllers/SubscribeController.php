<?php

declare(strict_types=1);

namespace Autodoctor\OlxWatcher\Controllers;

use Autodoctor\OlxWatcher\Exceptions\ValidateException;
use Autodoctor\OlxWatcher\Exceptions\WatcherException;
use Autodoctor\OlxWatcher\Services\SubscribeService;
use Autodoctor\OlxWatcher\Validator\ValidateService;

class SubscribeController
{
    protected array $validData;

    public function __construct(
        protected SubscribeService $service,
    ) {}

    /**
     * @throws WatcherException
     */
    public function __invoke(): string
    {
        $this->validData = $this->getValidData();
        $this->service->setSubject($this->validData['url']);

        if ($this->validData['status'] === true) {
            $message = $this->unsubscribe();
        } else {
            $message = $this->subscribe();
        }
        return json_encode($this->responseToArray($message));
    }

    private function rules(): array
    {
        return [
            'email' => [
                'filter' => FILTER_VALIDATE_EMAIL,
            ],
            'url' => [
                'filter' => FILTER_VALIDATE_URL,
                'flags' => FILTER_FLAG_PATH_REQUIRED,
            ],
            'status' => [
                'filter' => FILTER_CALLBACK,
                'options' => fn($value) => $value === 'unsubscribe' ? true : '',
            ],
        ];
    }

    /**
     * @throws ValidateException
     */
    public function getValidData(): array
    {
        return ValidateService::validated($this->rules());
    }

    /**
     * @throws WatcherException
     */
    public function subscribe(): string
    {
        return $this->service->subscribe(
            $this->validData['url'],
            $this->validData['email'],
        );
    }

    public function unsubscribe(): string
    {
        return $this->service->unsubscribe(
            $this->validData['url'],
            $this->validData['email'],
        );
    }

    public function errorResponse(string $errorMessage): string
    {
        return json_encode(
            [
                'data' => [
                    'success' => false,
                    'message' => $errorMessage,
                ]
            ]
        );
    }

    public function responseToArray(string $message): array
    {
        return [
            'data' => [
                'success' => true,
                'email' => $this->validData['email'],
                'url' => $this->validData['url'],
                'message' => $message,
            ],
        ];
    }
}
