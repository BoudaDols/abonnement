<?php

namespace App\Service\Payment;

class PaypalGateway implements PaymentGatewayInterface
{
    private string $clientId;
    private string $clientSecret;
    private string $baseUrl;

    public function __construct()
    {
        $this->clientId     = $_ENV['PAYPAL_CLIENT_ID'] ?? '';
        $this->clientSecret = $_ENV['PAYPAL_CLIENT_SECRET'] ?? '';
        $this->baseUrl      = $_ENV['APP_ENV'] === 'prod'
            ? 'https://api.paypal.com'
            : 'https://api.sandbox.paypal.com';
    }

    public function charge(float $amount, string $currency, array $metadata = []): string
    {
        $accessToken = $this->getAccessToken();

        $response = $this->request('POST', '/v2/checkout/orders', $accessToken, [
            'intent'         => 'CAPTURE',
            'purchase_units' => [[
                'amount'      => [
                    'currency_code' => strtoupper($currency),
                    'value'         => number_format($amount, 2, '.', ''),
                ],
                'custom_id'   => $metadata['subscription_id'] ?? '',
            ]],
        ]);

        return $response['id'];
    }

    private function getAccessToken(): string
    {
        $ch = curl_init("{$this->baseUrl}/v1/oauth2/token");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERPWD        => "{$this->clientId}:{$this->clientSecret}",
            CURLOPT_POSTFIELDS     => 'grant_type=client_credentials',
        ]);

        $response = json_decode(curl_exec($ch), true);
        curl_close($ch);

        return $response['access_token'];
    }

    private function request(string $method, string $path, string $token, array $body): array
    {
        $ch = curl_init("{$this->baseUrl}{$path}");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_HTTPHEADER     => [
                "Authorization: Bearer {$token}",
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS     => json_encode($body),
        ]);

        $response = json_decode(curl_exec($ch), true);
        curl_close($ch);

        return $response;
    }
}
