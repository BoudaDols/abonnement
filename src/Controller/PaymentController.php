<?php

namespace App\Controller;

use App\Model\Payment;
use App\Model\Subscription;
use Carbon\Carbon;

class PaymentController extends BaseController
{
    public function index(): string
    {
        $userId = $_GET['user_id'] ?? null;

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
        $body = $this->getInput();
        $subscriptionId = $body['subscription_id'] ?? null;
        $amount         = $body['amount'] ?? null;
        $transactionId  = $body['transaction_id'] ?? null;

        if (!$subscriptionId || !$amount || !$transactionId) {
            return $this->error('subscription_id, amount and transaction_id are required', 422);
        }

        $subscription = Subscription::find($subscriptionId);

        if (!$subscription) {
            return $this->error('Subscription not found', 404);
        }

        $payment = Payment::create([
            'subscription_id' => $subscriptionId,
            'amount'          => $amount,
            'status'          => 'paid',
            'transaction_id'  => $transactionId,
            'paid_at'         => Carbon::now(),
        ]);

        // Activate subscription after payment
        $subscription->update(['status' => 'active']);

        return $this->json($payment, 201);
    }
}