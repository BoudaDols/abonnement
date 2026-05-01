<?php

namespace App\Service\Payment;

use App\Model\Payment;
use App\Model\Subscription;
use Carbon\Carbon;

class StripeWebhookHandler
{
    private string $webhookSecret;

    public function __construct()
    {
        $this->webhookSecret = $_ENV['STRIPE_WEBHOOK_SECRET'] ?? '';
    }

    public function verify(string $payload, string $signature): bool
    {
        $parts     = [];
        foreach (explode(',', $signature) as $part) {
            [$key, $value] = explode('=', $part, 2);
            $parts[$key]   = $value;
        }

        $timestamp     = $parts['t'] ?? '';
        $expectedSig   = hash_hmac('sha256', "{$timestamp}.{$payload}", $this->webhookSecret);

        return hash_equals($expectedSig, $parts['v1'] ?? '');
    }

    public function handle(array $event): void
    {
        $intentId = $event['data']['object']['id'] ?? null;

        match ($event['type']) {
            'payment_intent.succeeded'       => $this->onSuccess($intentId),
            'payment_intent.payment_failed'  => $this->onFailed($intentId),
            default                          => null,
        };
    }

    private function onSuccess(string $intentId): void
    {
        $payment = Payment::where('transaction_id', $intentId)->first();

        if (!$payment) {
            return;
        }

        $payment->update(['status' => 'paid', 'paid_at' => Carbon::now()]);
        $payment->subscription->update(['status' => 'active']);
    }

    private function onFailed(string $intentId): void
    {
        $payment = Payment::where('transaction_id', $intentId)->first();

        if (!$payment) {
            return;
        }

        $payment->update(['status' => 'failed']);
        $payment->subscription->update(['status' => 'pending']);
    }
}
