<?php

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use Autodoctor\OlxWatcher\Services\SubscriptionService;
use Autodoctor\OlxWatcher\Services\PriceCheckerService;
use Autodoctor\OlxWatcher\Services\EmailService;
use Illuminate\Database\Capsule\Manager as DB;
use Mockery;

class SubscriptionServiceTest extends TestCase
{
    private SubscriptionService $service;
    private $priceChecker;
    private $emailService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->priceChecker = Mockery::mock(PriceCheckerService::class);
        $this->emailService = Mockery::mock(EmailService::class);
        
        $this->service = new SubscriptionService($this->priceChecker, $this->emailService);
        
        // Setup test database
        $this->setupTestDatabase();
    }

    private function setupTestDatabase(): void
    {
        $capsule = new DB;
        $capsule->addConnection([
            'driver'    => 'sqlite',
            'database'  => ':memory:',
            'prefix'    => '',
        ]);
        $capsule->setAsGlobal();
        $capsule->bootEloquent();
        
        // Create tables
        $schema = $capsule->schema();
        $schema->create('subscriptions', function ($table) {
            $table->id();
            $table->string('olx_url');
            $table->string('email');
            $table->decimal('last_price', 10, 2)->nullable();
            $table->timestamp('last_checked_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['olx_url', 'email']);
        });
    }

    public function testCreateSubscription()
    {
        $url = 'https://www.olx.ua/item/123';
        $email = 'test@example.com';
        $price = 1000.50;
        
        $this->priceChecker->shouldReceive('getCurrentPrice')
            ->with($url)
            ->once()
            ->andReturn($price);
        
        $result = $this->service->createSubscription($url, $email);
        
        $this->assertEquals($url, $result['olx_url']);
        $this->assertEquals($email, $result['email']);
        $this->assertEquals($price, $result['initial_price']);
        
        // Verify database entry
        $subscription = DB::table('subscriptions')->first();
        $this->assertEquals($url, $subscription->olx_url);
        $this->assertEquals($email, $subscription->email);
        $this->assertEquals($price, $subscription->last_price);
    }

    public function testCreateDuplicateSubscription()
    {
        $url = 'https://www.olx.ua/item/123';
        $email = 'test@example.com';
        $price = 1000.50;
        
        // Create first subscription
        $this->priceChecker->shouldReceive('getCurrentPrice')
            ->with($url)
            ->once()
            ->andReturn($price);
        
        $this->service->createSubscription($url, $email);
        
        // Try to create duplicate
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Subscription already exists');
        
        $this->service->createSubscription($url, $email);
    }

    public function testCheckPriceChanges()
    {
        $url = 'https://www.olx.ua/item/123';
        $email = 'test@example.com';
        $oldPrice = 1000.50;
        $newPrice = 900.00;
        
        // Create subscription
        $this->priceChecker->shouldReceive('getCurrentPrice')
            ->with($url)
            ->once()
            ->andReturn($oldPrice);
        
        $this->service->createSubscription($url, $email);
        
        // Check price changes
        $this->priceChecker->shouldReceive('getCurrentPrice')
            ->with($url)
            ->once()
            ->andReturn($newPrice);
        
        $this->emailService->shouldReceive('sendPriceChangeNotification')
            ->with($email, $url, $oldPrice, $newPrice)
            ->once();
        
        $this->service->checkPriceChanges();
        
        // Verify price was updated
        $subscription = DB::table('subscriptions')->first();
        $this->assertEquals($newPrice, $subscription->last_price);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
} 