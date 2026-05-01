<?php

namespace App\Controller;

use App\Model\Payment;
use App\Model\Subscription;
use App\Service\Payment\PaymentGatewayFactory;
use App\Service\Payment\PaymentGatewayInterface;
use Carbon\Carbon;

class PaymentController extends BaseController
{
    public function index(): string
    {
        $userId = (int) ($_GET['user_id'] ?? 0);

        if (!$userId) {
            return $this->error('user_id is required', 422);
        }

        $payments = Payment::whereHas('subscription', fn($q) => $q->where('user_id', $userId))
            ->with('subscription')
            ->get();

        return $this->success($payments);
    }

    public function create(): string
    {
        $body           = $this->getInput();
        $subscriptionId = (int) ($body['subscription_id'] ?? 0);
        $amount         = filter_var($body['amount'] ?? null, FILTER_VALIDATE_FLOAT);
        $currency       = preg_replace('/[^a-z]/', '', strtolower($body['currency'] ?? 'usd'));

        if (!$subscriptionId || !$amount) {
            return $this->error('subscription_id and amount are required', 422);
        }

        $subscription = Subscription::find((int) $subscriptionId);

        if (!$subscription) {
            return $this->error('Subscription not found', 404);
        }

        try {
            $transactionId = $this->makeGateway()->charge($amount, $currency, [
                'subscription_id' => $subscriptionId,
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Payment failed: ' . $e->getMessage());
            return $this->error('Payment failed: ' . $e->getMessage(), 502);
        }

        $payment = Payment::create([
            'subscription_id' => $subscriptionId,
            'amount'          => $amount,
            'status'          => 'paid',
            'transaction_id'  => $transactionId,
            'paid_at'         => Carbon::now(),
        ]);

        $subscription->update(['status' => 'active']);

        return $this->json($payment, 201);
    }

    protected function makeGateway(): PaymentGatewayInterface
    {
        return PaymentGatewayFactory::make();
    }
}
