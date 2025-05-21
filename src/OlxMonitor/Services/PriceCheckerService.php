<?php

namespace Autodoctor\OlxWatcher\Services;

use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;
use Redis;

class PriceCheckerService
{
    private Client $httpClient;
    private Redis $redis;
    private const CACHE_TTL = 300; // 5 minutes

    public function __construct(Client $httpClient, Redis $redis)
    {
        $this->httpClient = $httpClient;
        $this->redis = $redis;
    }

    public function getCurrentPrice(string $olxUrl): float
    {
        // Check cache first
        $cacheKey = 'price:' . md5($olxUrl);
        $cachedPrice = $this->redis->get($cacheKey);
        
        if ($cachedPrice !== false) {
            return (float) $cachedPrice;
        }

        try {
            $response = $this->httpClient->get($olxUrl);
            $html = (string) $response->getBody();
            
            $crawler = new Crawler($html);
            
            // Find price element (you'll need to adjust the selector based on OLX's HTML structure)
            $priceText = $crawler->filter('.price-label strong')->text();
            
            // Clean up price text and convert to float
            $price = (float) preg_replace('/[^0-9.]/', '', $priceText);
            
            // Cache the result
            $this->redis->setex($cacheKey, self::CACHE_TTL, $price);
            
            return $price;
        } catch (\Exception $e) {
            throw new \Exception('Failed to fetch price from OLX: ' . $e->getMessage());
        }
    }
} 