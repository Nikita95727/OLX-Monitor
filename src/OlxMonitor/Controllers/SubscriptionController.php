<?php

namespace Autodoctor\OlxWatcher\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Autodoctor\OlxWatcher\Services\SubscriptionService;
use Autodoctor\OlxWatcher\Validators\SubscriptionValidator;

class SubscriptionController
{
    private SubscriptionService $subscriptionService;
    private SubscriptionValidator $validator;

    public function __construct(
        SubscriptionService $subscriptionService,
        SubscriptionValidator $validator
    ) {
        $this->subscriptionService = $subscriptionService;
        $this->validator = $validator;
    }

    public function subscribe(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        
        try {
            $this->validator->validate($data);
            
            $subscription = $this->subscriptionService->createSubscription(
                $data['olx_url'],
                $data['email']
            );
            
            $response->getBody()->write(json_encode([
                'success' => true,
                'message' => 'Successfully subscribed to price changes',
                'data' => $subscription
            ]));
            
            return $response->withHeader('Content-Type', 'application/json')
                           ->withStatus(201);
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]));
            
            return $response->withHeader('Content-Type', 'application/json')
                           ->withStatus(400);
        }
    }
} 