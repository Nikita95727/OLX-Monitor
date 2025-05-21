<?php

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use Autodoctor\OlxWatcher\Services\EmailService;
use PHPMailer\PHPMailer\PHPMailer;
use Mockery;

class EmailServiceTest extends TestCase
{
    private EmailService $service;
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
        
        $this->service = new EmailService($config);
    }

    public function testSendPriceChangeNotification()
    {
        $toEmail = 'recipient@test.com';
        $url = 'https://www.olx.ua/item/123';
        $oldPrice = 1000.50;
        $newPrice = 900.00;
        
        $this->mailer->shouldReceive('addAddress')
            ->with($toEmail)
            ->once();
            
        $this->mailer->shouldReceive('Subject')
            ->with('OLX Price Change Alert')
            ->once();
            
        $this->mailer->shouldReceive('Body')
            ->once();
            
        $this->mailer->shouldReceive('send')
            ->once();
        
        $this->service->sendPriceChangeNotification($toEmail, $url, $oldPrice, $newPrice);
    }

    public function testSendPriceChangeNotificationWithError()
    {
        $toEmail = 'recipient@test.com';
        $url = 'https://www.olx.ua/item/123';
        $oldPrice = 1000.50;
        $newPrice = 900.00;
        
        $this->mailer->shouldReceive('addAddress')
            ->with($toEmail)
            ->once()
            ->andThrow(new \Exception('SMTP error'));
        
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Failed to send email: SMTP error');
        
        $this->service->sendPriceChangeNotification($toEmail, $url, $oldPrice, $newPrice);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
} 