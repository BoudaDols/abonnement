<?php

namespace App\Controller;

use App\Service\Payment\StripeWebhookHandler;
use App\Service\Payment\PaypalWebhookHandler;

class WebhookController extends BaseController
{
    private function readPayload(): string
    {
        $maxSize = 1024 * 1024; // 1MB limit
        $contentLength = (int) ($_SERVER['CONTENT_LENGTH'] ?? 0);

        if ($contentLength > $maxSize) {
            return '';
        }

        return file_get_contents('php://input', length: $maxSize) ?: '';
    }

    public function stripe(): string
    {
        $payload   = $this->readPayload();
        $signature = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

        if (empty($payload) || empty($signature)) {
            return $this->error('Invalid request', 400);
        }

        $handler = new StripeWebhookHandler();

        if (!$handler->verify($payload, $signature)) {
            return $this->error('Invalid signature', 401);
        }

        $event = json_decode($payload, true);
        if (!is_array($event) || empty($event['type'])) {
            return $this->error('Invalid payload', 400);
        }

        $handler->handle($event);
        $this->logger->info('Stripe webhook received: ' . preg_replace('/[^a-z._]/', '', $event['type']));

        return $this->success(['received' => true]);
    }

    public function paypal(): string
    {
        $payload = $this->readPayload();
        $headers = [
            'PAYPAL-AUTH-ALGO'         => $_SERVER['HTTP_PAYPAL_AUTH_ALGO'] ?? '',
            'PAYPAL-CERT-URL'          => filter_var($_SERVER['HTTP_PAYPAL_CERT_URL'] ?? '', FILTER_VALIDATE_URL) ?: '',
            'PAYPAL-TRANSMISSION-ID'   => $_SERVER['HTTP_PAYPAL_TRANSMISSION_ID'] ?? '',
            'PAYPAL-TRANSMISSION-SIG'  => $_SERVER['HTTP_PAYPAL_TRANSMISSION_SIG'] ?? '',
            'PAYPAL-TRANSMISSION-TIME' => $_SERVER['HTTP_PAYPAL_TRANSMISSION_TIME'] ?? '',
        ];

        if (empty($payload)) {
            return $this->error('Invalid request', 400);
        }

        $handler = new PaypalWebhookHandler();

        if (!$handler->verify($headers, $payload)) {
            return $this->error('Invalid signature', 401);
        }

        $event = json_decode($payload, true);
        if (!is_array($event) || empty($event['event_type'])) {
            return $this->error('Invalid payload', 400);
        }

        $handler->handle($event);
        $this->logger->info('PayPal webhook received: ' . preg_replace('/[^A-Z._]/', '', $event['event_type']));

        return $this->success(['received' => true]);
    }
}
