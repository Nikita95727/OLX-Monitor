<?php

namespace Autodoctor\OlxWatcher\Services;

use Illuminate\Database\Capsule\Manager as DB;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailConfirmationService
{
    private PHPMailer $mailer;
    private string $baseUrl;

    public function __construct(array $config, string $baseUrl)
    {
        $this->mailer = new PHPMailer(true);
        $this->baseUrl = $baseUrl;
        
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

    public function sendConfirmationEmail(string $email, string $olxUrl): string
    {
        $token = bin2hex(random_bytes(32));
        
        // Store token in database
        DB::table('subscriptions')
            ->where('email', $email)
            ->where('olx_url', $olxUrl)
            ->update(['confirmation_token' => $token]);
        
        try {
            $this->mailer->addAddress($email);
            $this->mailer->Subject = 'Confirm your OLX Price Monitor subscription';
            
            $confirmationUrl = $this->baseUrl . '/confirm/' . $token;
            
            $body = "
                <h2>Confirm Your Subscription</h2>
                <p>Thank you for subscribing to OLX Price Monitor!</p>
                <p>Please click the link below to confirm your subscription:</p>
                <p><a href='{$confirmationUrl}'>{$confirmationUrl}</a></p>
                <p>This link will expire in 24 hours.</p>
            ";
            
            $this->mailer->Body = $body;
            $this->mailer->send();
            
            return $token;
        } catch (Exception $e) {
            throw new \Exception('Failed to send confirmation email: ' . $e->getMessage());
        }
    }

    public function confirmEmail(string $token): bool
    {
        $subscription = DB::table('subscriptions')
            ->where('confirmation_token', $token)
            ->whereNull('email_confirmed_at')
            ->first();
        
        if (!$subscription) {
            return false;
        }
        
        DB::table('subscriptions')
            ->where('id', $subscription->id)
            ->update([
                'is_active' => true,
                'email_confirmed_at' => now(),
                'confirmation_token' => null
            ]);
        
        return true;
    }
} 