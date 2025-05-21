<?php

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use Autodoctor\OlxWatcher\Services\PriceCheckerService;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Redis;
use Mockery;

class PriceCheckerServiceTest extends TestCase
{
    private PriceCheckerService $service;
    private $httpClient;
    private $redis;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->httpClient = Mockery::mock(Client::class);
        $this->redis = Mockery::mock(Redis::class);
        
        $this->service = new PriceCheckerService($this->httpClient, $this->redis);
    }

    public function testGetCurrentPriceFromCache()
    {
        $url = 'https://www.olx.ua/item/123';
        $cachedPrice = '1000.50';
        
        $this->redis->shouldReceive('get')
            ->with('price:' . md5($url))
            ->once()
            ->andReturn($cachedPrice);
        
        $price = $this->service->getCurrentPrice($url);
        
        $this->assertEquals(1000.50, $price);
    }

    public function testGetCurrentPriceFromWebsite()
    {
        $url = 'https://www.olx.ua/item/123';
        $html = '<div class="price-label"><strong>1,234.56 грн</strong></div>';
        
        $this->redis->shouldReceive('get')
            ->with('price:' . md5($url))
            ->once()
            ->andReturn(false);
            
        $this->httpClient->shouldReceive('get')
            ->with($url)
            ->once()
            ->andReturn(new Response(200, [], $html));
            
        $this->redis->shouldReceive('setex')
            ->with('price:' . md5($url), 300, '1234.56')
            ->once();
        
        $price = $this->service->getCurrentPrice($url);
        
        $this->assertEquals(1234.56, $price);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
} 