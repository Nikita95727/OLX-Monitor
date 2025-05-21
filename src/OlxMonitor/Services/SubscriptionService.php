<?php

namespace Autodoctor\OlxWatcher\Services;

use Illuminate\Database\Capsule\Manager as DB;
use Autodoctor\OlxWatcher\Services\PriceCheckerService;
use Autodoctor\OlxWatcher\Services\EmailService;
use Autodoctor\OlxWatcher\Services\EmailConfirmationService;

class SubscriptionService
{
    private PriceCheckerService $priceChecker;
    private EmailService $emailService;
    private EmailConfirmationService $confirmationService;

    public function __construct(
        PriceCheckerService $priceChecker,
        EmailService $emailService,
        EmailConfirmationService $confirmationService
    ) {
        $this->priceChecker = $priceChecker;
        $this->emailService = $emailService;
        $this->confirmationService = $confirmationService;
    }

    public function createSubscription(string $olxUrl, string $email): array
    {
        // Check if subscription already exists
        $existingSubscription = DB::table('subscriptions')
            ->where('olx_url', $olxUrl)
            ->where('email', $email)
            ->first();

        if ($existingSubscription) {
            if ($existingSubscription->is_active) {
                throw new \Exception('Subscription already exists');
            } else {
                // Resend confirmation email
                $this->confirmationService->sendConfirmationEmail($email, $olxUrl);
                return [
                    'message' => 'Confirmation email resent. Please check your inbox.'
                ];
            }
        }

        // Get initial price
        $initialPrice = $this->priceChecker->getCurrentPrice($olxUrl);

        // Create subscription
        $subscriptionId = DB::table('subscriptions')->insertGetId([
            'olx_url' => $olxUrl,
            'email' => $email,
            'last_price' => $initialPrice,
            'last_checked_at' => now(),
            'is_active' => false,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Send confirmation email
        $this->confirmationService->sendConfirmationEmail($email, $olxUrl);

        return [
            'id' => $subscriptionId,
            'olx_url' => $olxUrl,
            'email' => $email,
            'initial_price' => $initialPrice,
            'message' => 'Please check your email to confirm your subscription.'
        ];
    }

    public function checkPriceChanges(): void
    {
        $subscriptions = DB::table('subscriptions')
            ->where('is_active', true)
            ->get();

        foreach ($subscriptions as $subscription) {
            $currentPrice = $this->priceChecker->getCurrentPrice($subscription->olx_url);
            
            if ($currentPrice !== $subscription->last_price) {
                // Price has changed, send notification
                $this->emailService->sendPriceChangeNotification(
                    $subscription->email,
                    $subscription->olx_url,
                    $subscription->last_price,
                    $currentPrice
                );

                // Update subscription
                DB::table('subscriptions')
                    ->where('id', $subscription->id)
                    ->update([
                        'last_price' => $currentPrice,
                        'last_checked_at' => now(),
                        'updated_at' => now()
                    ]);
            }
        }
    }
} 