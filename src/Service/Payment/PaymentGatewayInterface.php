<?php

namespace App\Service\Payment;

interface PaymentGatewayInterface
{
    /**
     * Charge a payment.
     * Returns a transaction ID on success, throws an exception on failure.
     */
    public function charge(float $amount, string $currency, array $metadata = []): string;
}
