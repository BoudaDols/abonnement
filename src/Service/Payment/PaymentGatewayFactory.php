<?php

namespace App\Service\Payment;

class PaymentGatewayFactory
{
    public static function make(): PaymentGatewayInterface
    {
        $gateway = $_ENV['PAYMENT_GATEWAY'] ?? 'stripe';

        return match ($gateway) {
            'stripe' => new StripeGateway(),
            'paypal' => new PaypalGateway(),
            default  => throw new \InvalidArgumentException("Unsupported payment gateway: {$gateway}"),
        };
    }
}
