<?php

namespace App\Service\Payment;

class StripeGateway implements PaymentGatewayInterface
{
    private string $apiKey;

    public function __construct()
    {
        $this->apiKey = $_ENV['STRIPE_SECRET_KEY'] ?? '';
    }

    public function charge(float $amount, string $currency, array $metadata = []): string
    {
        // Initialize Stripe client
        $stripe = new \Stripe\StripeClient($this->apiKey);

        $paymentIntent = $stripe->paymentIntents->create([
            'amount'   => (int) ($amount * 100), // Stripe expects amount in cents
            'currency' => strtolower($currency),
            'metadata' => $metadata,
        ]);

        return $paymentIntent->id;
    }
}
