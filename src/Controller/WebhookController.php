<?php

namespace App\Controller;

use App\Service\Payment\StripeWebhookHandler;
use App\Service\Payment\PaypalWebhookHandler;

class WebhookController extends BaseController
{
    public function stripe(): string
    {
        $payload   = file_get_contents('php://input');
        $signature = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

        $handler = new StripeWebhookHandler();

        if (!$handler->verify($payload, $signature)) {
            return $this->error('Invalid signature', 401);
        }

        $event = json_decode($payload, true);
        $handler->handle($event);

        $this->logger->info("Stripe webhook received: {$event['type']}");

        return $this->success(['received' => true]);
    }

    public function paypal(): string
    {
        $payload = file_get_contents('php://input');
        $headers = [
            'PAYPAL-AUTH-ALGO'        => $_SERVER['HTTP_PAYPAL_AUTH_ALGO'] ?? '',
            'PAYPAL-CERT-URL'         => $_SERVER['HTTP_PAYPAL_CERT_URL'] ?? '',
            'PAYPAL-TRANSMISSION-ID'  => $_SERVER['HTTP_PAYPAL_TRANSMISSION_ID'] ?? '',
            'PAYPAL-TRANSMISSION-SIG' => $_SERVER['HTTP_PAYPAL_TRANSMISSION_SIG'] ?? '',
            'PAYPAL-TRANSMISSION-TIME'=> $_SERVER['HTTP_PAYPAL_TRANSMISSION_TIME'] ?? '',
        ];

        $handler = new PaypalWebhookHandler();

        if (!$handler->verify($headers, $payload)) {
            return $this->error('Invalid signature', 401);
        }

        $event = json_decode($payload, true);
        $handler->handle($event);

        $this->logger->info("PayPal webhook received: {$event['event_type']}");

        return $this->success(['received' => true]);
    }
}
