<?php

namespace App\Service\Payment;

use App\Model\Payment;
use Carbon\Carbon;

class PaypalWebhookHandler
{
    private string $webhookId;
    private string $baseUrl;

    public function __construct()
    {
        $this->webhookId = $_ENV['PAYPAL_WEBHOOK_ID'] ?? '';
        $this->baseUrl   = $_ENV['APP_ENV'] === 'prod'
            ? 'https://api.paypal.com'
            : 'https://api.sandbox.paypal.com';
    }

    public function verify(array $headers, string $payload): bool
    {
        $accessToken = $this->getAccessToken();

        $ch = curl_init("{$this->baseUrl}/v1/notifications/verify-webhook-signature");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => [
                "Authorization: Bearer {$accessToken}",
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'auth_algo'         => $headers['PAYPAL-AUTH-ALGO'] ?? '',
                'cert_url'          => $headers['PAYPAL-CERT-URL'] ?? '',
                'transmission_id'   => $headers['PAYPAL-TRANSMISSION-ID'] ?? '',
                'transmission_sig'  => $headers['PAYPAL-TRANSMISSION-SIG'] ?? '',
                'transmission_time' => $headers['PAYPAL-TRANSMISSION-TIME'] ?? '',
                'webhook_id'        => $this->webhookId,
                'webhook_event'     => json_decode($payload, true),
            ]),
        ]);

        $response = json_decode(curl_exec($ch), true);
        curl_close($ch);

        return ($response['verification_status'] ?? '') === 'SUCCESS';
    }

    public function handle(array $event): void
    {
        $orderId = $event['resource']['id'] ?? null;

        match ($event['event_type']) {
            'PAYMENT.CAPTURE.COMPLETED' => $this->onSuccess($orderId),
            'PAYMENT.CAPTURE.DENIED'    => $this->onFailed($orderId),
            default                     => null,
        };
    }

    private function onSuccess(string $orderId): void
    {
        $payment = Payment::where('transaction_id', $orderId)->first();

        if (!$payment) {
            return;
        }

        $payment->update(['status' => 'paid', 'paid_at' => Carbon::now()]);
        $payment->subscription->update(['status' => 'active']);
    }

    private function onFailed(string $orderId): void
    {
        $payment = Payment::where('transaction_id', $orderId)->first();

        if (!$payment) {
            return;
        }

        $payment->update(['status' => 'failed']);
        $payment->subscription->update(['status' => 'pending']);
    }

    private function getAccessToken(): string
    {
        $clientId     = $_ENV['PAYPAL_CLIENT_ID'] ?? '';
        $clientSecret = $_ENV['PAYPAL_CLIENT_SECRET'] ?? '';

        $ch = curl_init("{$this->baseUrl}/v1/oauth2/token");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERPWD        => "{$clientId}:{$clientSecret}",
            CURLOPT_POSTFIELDS     => 'grant_type=client_credentials',
        ]);

        $response = json_decode(curl_exec($ch), true);
        curl_close($ch);

        return $response['access_token'];
    }
}
