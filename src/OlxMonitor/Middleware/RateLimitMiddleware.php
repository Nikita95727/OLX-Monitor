<?php

namespace Autodoctor\OlxWatcher\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Redis;

class RateLimitMiddleware implements MiddlewareInterface
{
    private Redis $redis;
    private int $maxRequests;
    private int $windowSeconds;

    public function __construct(Redis $redis, int $maxRequests = 60, int $windowSeconds = 3600)
    {
        $this->redis = $redis;
        $this->maxRequests = $maxRequests;
        $this->windowSeconds = $windowSeconds;
    }

    public function process(Request $request, RequestHandler $handler): Response
    {
        $ip = $request->getServerParams()['REMOTE_ADDR'] ?? 'unknown';
        $key = "rate_limit:{$ip}";

        $current = $this->redis->get($key);
        
        if ($current === false) {
            $this->redis->setex($key, $this->windowSeconds, 1);
        } else {
            if ($current >= $this->maxRequests) {
                $response = new \Slim\Psr7\Response();
                $response->getBody()->write(json_encode([
                    'error' => 'Too many requests',
                    'retry_after' => $this->redis->ttl($key)
                ]));
                
                return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withHeader('Retry-After', $this->redis->ttl($key))
                    ->withStatus(429);
            }
            
            $this->redis->incr($key);
        }

        return $handler->handle($request);
    }
} 