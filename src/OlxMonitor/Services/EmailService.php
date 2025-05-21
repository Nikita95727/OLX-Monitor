<?php

namespace Autodoctor\OlxWatcher\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailService
{
    private PHPMailer $mailer;

    public function __construct(array $config)
    {
        $this->mailer = new PHPMailer(true);
        
        // Configure mailer
        $this->mailer->isSMTP();
        $this->mailer->Host = $config['smtp_host'];
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = $config['smtp_username'];
        $this->mailer->Password = $config['smtp_password'];
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port = $config['smtp_port'];
        
        $this->mailer->setFrom($config['from_email'], $config['from_name']);
        $this->mailer->isHTML(true);
    }

    public function sendPriceChangeNotification(
        string $toEmail,
        string $olxUrl,
        float $oldPrice,
        float $newPrice
    ): void {
        try {
            $this->mailer->addAddress($toEmail);
            $this->mailer->Subject = 'OLX Price Change Alert';
            
            $priceChange = $newPrice - $oldPrice;
            $changeDirection = $priceChange > 0 ? 'increased' : 'decreased';
            
            $body = "
                <h2>Price Change Alert</h2>
                <p>The price of the item you're watching has {$changeDirection}.</p>
                <p><strong>Old Price:</strong> {$oldPrice}</p>
                <p><strong>New Price:</strong> {$newPrice}</p>
                <p><strong>Change:</strong> {$priceChange}</p>
                <p><a href='{$olxUrl}'>View the listing</a></p>
            ";
            
            $this->mailer->Body = $body;
            $this->mailer->send();
        } catch (Exception $e) {
            throw new \Exception('Failed to send email: ' . $e->getMessage());
        }
    }
} 