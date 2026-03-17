<?php

require_once __DIR__ . '/BaseTestCase.php';

use App\Model\Plan;
use App\Model\Subscription;
use App\Model\Payment;
use Carbon\Carbon;

class ModelTest extends BaseTestCase
{
    // Plan tests
    public function testPlanIsFreeReturnsTrueForFreePlan(): void
    {
        $plan = new Plan(['type' => 'free']);
        $this->assertTrue($plan->isFree());
    }

    public function testPlanIsFreeReturnsFalseForPaidPlan(): void
    {
        $plan = new Plan(['type' => 'paid']);
        $this->assertFalse($plan->isFree());
    }

    public function testPlanFillable(): void
    {
        $plan = new Plan([
            'name'         => 'Pro',
            'type'         => 'paid',
            'price'        => 9.99,
            'duration_days'=> 30,
            'is_active'    => true,
        ]);

        $this->assertEquals('Pro', $plan->name);
        $this->assertEquals('paid', $plan->type);
        $this->assertEquals(9.99, $plan->price);
        $this->assertEquals(30, $plan->duration_days);
        $this->assertTrue($plan->is_active);
    }

    // Subscription tests
    public function testSubscriptionIsActiveReturnsTrueWhenActiveAndNotExpired(): void
    {
        $subscription = new Subscription([
            'status'   => 'active',
            'ends_at'  => Carbon::now()->addDays(10),
        ]);

        $this->assertTrue($subscription->isActive());
    }

    public function testSubscriptionIsActiveReturnsFalseWhenExpired(): void
    {
        $subscription = new Subscription([
            'status'  => 'active',
            'ends_at' => Carbon::now()->subDay(),
        ]);

        $this->assertFalse($subscription->isActive());
    }

    public function testSubscriptionIsActiveReturnsFalseWhenCanceled(): void
    {
        $subscription = new Subscription([
            'status'  => 'canceled',
            'ends_at' => Carbon::now()->addDays(10),
        ]);

        $this->assertFalse($subscription->isActive());
    }

    public function testSubscriptionFillable(): void
    {
        $subscription = new Subscription([
            'user_id'  => 1,
            'plan_id'  => 2,
            'status'   => 'active',
            'starts_at'=> Carbon::now(),
            'ends_at'  => Carbon::now()->addDays(30),
        ]);

        $this->assertEquals(1, $subscription->user_id);
        $this->assertEquals(2, $subscription->plan_id);
        $this->assertEquals('active', $subscription->status);
    }

    // Payment tests
    public function testPaymentFillable(): void
    {
        $payment = new Payment([
            'subscription_id' => 1,
            'amount'          => 9.99,
            'status'          => 'paid',
            'transaction_id'  => 'txn_123',
        ]);

        $this->assertEquals(1, $payment->subscription_id);
        $this->assertEquals(9.99, $payment->amount);
        $this->assertEquals('paid', $payment->status);
        $this->assertEquals('txn_123', $payment->transaction_id);
    }
}
