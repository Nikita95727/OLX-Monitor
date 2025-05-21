<?php

return [
    'smtp' => [
        'host' => getenv('SMTP_HOST') ?: 'smtp.gmail.com',
        'port' => getenv('SMTP_PORT') ?: 587,
        'username' => getenv('SMTP_USERNAME'),
        'password' => getenv('SMTP_PASSWORD'),
        'encryption' => 'tls',
        'from_email' => getenv('SMTP_FROM_EMAIL') ?: 'noreply@olxwatcher.com',
        'from_name' => getenv('SMTP_FROM_NAME') ?: 'OLX Price Watcher'
    ],
    'rate_limits' => [
        'emails_per_hour' => getenv('EMAIL_RATE_LIMIT') ?: 100,
        'check_interval_minutes' => getenv('PRICE_CHECK_INTERVAL') ?: 15
    ]
]; 