<?php

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use Autodoctor\OlxWatcher\Services\EmailConfirmationService;
use PHPMailer\PHPMailer\PHPMailer;
use Illuminate\Database\Capsule\Manager as DB;
use Mockery;

class EmailConfirmationServiceTest extends TestCase
{
    private EmailConfirmationService $service;
    private $mailer;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->mailer = Mockery::mock(PHPMailer::class);
        
        $config = [
            'smtp_host' => 'smtp.test.com',
            'smtp_port' => 587,
            'smtp_username' => 'test@test.com',
            'smtp_password' => 'password',
            'from_email' => 'noreply@test.com',
            'from_name' => 'Test Sender'
        ];
        
        $this->service = new EmailConfirmationService($config, 'http://test.com');
        
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
        $schema = $capsule::schema();
        $schema->create('subscriptions', function ($table) {
            $table->id();
            $table->string('olx_url');
            $table->string('email');
            $table->decimal('last_price', 10, 2)->nullable();
            $table->timestamp('last_checked_at')->nullable();
            $table->boolean('is_active')->default(false);
            $table->string('confirmation_token')->nullable();
            $table->timestamp('email_confirmed_at')->nullable();
            $table->timestamps();
            $table->unique(['olx_url', 'email']);
        });
    }

    public function testSendConfirmationEmail()
    {
        $email = 'test@example.com';
        $url = 'https://www.olx.ua/item/123';
        
        // Create subscription
        DB::table('subscriptions')->insert([
            'olx_url' => $url,
            'email' => $email,
            'is_active' => false,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        $this->mailer->shouldReceive('addAddress')
            ->with($email)
            ->once();
            
        $this->mailer->shouldReceive('Subject')
            ->with('Confirm your OLX Price Watcher subscription')
            ->once();
            
        $this->mailer->shouldReceive('Body')
            ->once();
            
        $this->mailer->shouldReceive('send')
            ->once();
        
        $token = $this->service->sendConfirmationEmail($email, $url);
        
        $this->assertNotEmpty($token);
        
        // Verify token was stored
        $subscription = DB::table('subscriptions')->first();
        $this->assertEquals($token, $subscription->confirmation_token);
    }

    public function testConfirmEmail()
    {
        $email = 'test@example.com';
        $url = 'https://www.olx.ua/item/123';
        $token = 'test_token';
        
        // Create subscription with token
        DB::table('subscriptions')->insert([
            'olx_url' => $url,
            'email' => $email,
            'is_active' => false,
            'confirmation_token' => $token,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        $result = $this->service->confirmEmail($token);
        
        $this->assertTrue($result);
        
        // Verify subscription was activated
        $subscription = DB::table('subscriptions')->first();
        $this->assertTrue($subscription->is_active);
        $this->assertNotNull($subscription->email_confirmed_at);
        $this->assertNull($subscription->confirmation_token);
    }

    public function testConfirmEmailWithInvalidToken()
    {
        $result = $this->service->confirmEmail('invalid_token');
        $this->assertFalse($result);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
} 