<?php

namespace Autodoctor\OlxWatcher\Console;

use Autodoctor\OlxWatcher\Services\SubscriptionService;
use Psr\Log\LoggerInterface;

class PriceCheckerCommand
{
    private SubscriptionService $subscriptionService;
    private LoggerInterface $logger;

    public function __construct(
        SubscriptionService $subscriptionService,
        LoggerInterface $logger
    ) {
        $this->subscriptionService = $subscriptionService;
        $this->logger = $logger;
    }

    public function execute(): void
    {
        try {
            $this->logger->info('Starting price check cycle');
            
            $this->subscriptionService->checkPriceChanges();
            
            $this->logger->info('Price check cycle completed successfully');
        } catch (\Exception $e) {
            $this->logger->error('Error during price check cycle: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
} 