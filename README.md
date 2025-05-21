# OLX Price Monitor

[![Supported PHP Versions](https://img.shields.io/badge/PHP-8.2,%208.3,%208.4-blue)](https://github.com/Nikita95727/OLX-Monitor/)
[![Test Coverage](https://img.shields.io/badge/Test%20Coverage-70%25-green)](https://github.com/Nikita95727/OLX-Monitor/)

## Task Description

This service monitors price changes of advertisements on OLX and notifies subscribers via email.

### Requirements

1. HTTP endpoint for subscribing to price changes:
   - Input: OLX advertisement URL and subscriber's email
   - Output: Subscription confirmation

2. Price monitoring functionality:
   - Regular price checks for subscribed advertisements
   - Email notifications when prices change
   - Optimized checks for multiple subscribers (single check per ad)

3. Email confirmation system:
   - Email verification for new subscriptions
   - Secure token-based confirmation process

### Implementation Details

#### Architecture

The service is built using:
- PHP 8.2+ for the backend
- Docker for containerization
- MySQL for data storage
- Redis for caching
- SMTP for email delivery

#### Components

1. **Price Monitoring Service** (`OlxMonitor\Services\MonitorService`):
   - Fetches current prices from OLX
   - Implements caching to prevent duplicate checks
   - Handles price change detection

2. **Subscription Service** (`OlxMonitor\Services\SubscribeService`):
   - Manages user subscriptions
   - Handles email confirmation
   - Prevents duplicate subscriptions

3. **Email Service** (`OlxMonitor\Services\EmailService`):
   - Sends price change notifications
   - Handles email confirmation
   - Uses SMTP for reliable delivery

4. **Database Layer**:
   - MySQL for persistent storage
   - Redis for caching and rate limiting
   - Optimized queries for subscription management

#### Implementation Choices

1. **Price Fetching Method**:
   - Option A: Web Scraping
     - Pros: No API dependencies, works with any OLX page
     - Cons: More fragile, needs maintenance when page structure changes
   - Option B: Mobile API
     - Pros: More stable, faster response times
     - Cons: May break if API changes, requires reverse engineering
   - Chosen: Web Scraping for reliability and maintainability

2. **Storage Options**:
   - Option A: File-based storage
     - Pros: Simple, no database setup required
     - Cons: Limited scalability, potential file locking issues
   - Option B: Database storage
     - Pros: Better scalability, concurrent access support
     - Cons: Requires database setup and maintenance
   - Chosen: Database storage for better scalability and reliability

3. **Email Delivery**:
   - Option A: Direct SMTP
     - Pros: Full control, no third-party dependencies
     - Cons: Requires SMTP server setup
   - Option B: Email service provider
     - Pros: Better deliverability, managed service
     - Cons: Additional cost, dependency on third party
   - Chosen: Direct SMTP for simplicity and control

## Installation

```bash
git clone https://github.com/Nikita95727/OLX-Monitor.git
cd /projects/olx-monitor

# Set up environment
cp .env.example .env
# Edit .env with your settings

# Start services
docker-compose up -d

# Run migrations
docker-compose exec app php bin/console migrate

# Set permissions
chmod 777 ./src/olx_monitor.log
```

## Usage

### Subscribe to Price Monitoring

```http
GET http://example-olx-monitor/?status=subscribe&email=test@mail.com&url=https://www.olx.ua/powerbank.html
```

The service will:
1. Validate the email and URL
2. Send a confirmation email
3. Activate the subscription after email confirmation
4. Start monitoring the price

### Unsubscribe

```http
GET http://example-olx-monitor/?email=test@mail.com&status=unsubscribe
```

## Testing

Run the test suite with:

```bash
docker-compose exec app vendor/bin/phpunit
```

Current test coverage: 70%+

## Docker Setup

The service runs in Docker containers:
- PHP-FPM application container
- Nginx web server
- MySQL database
- Redis cache
- SMTP server

## License

GPL License
