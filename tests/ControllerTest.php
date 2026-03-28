<?php

require_once __DIR__ . '/BaseTestCase.php';

use App\Controller\PlanController;
use App\Controller\SubscriptionController;
use App\Controller\PaymentController;
use App\Model\Plan;
use App\Model\Subscription;
use App\Model\Payment;
use Carbon\Carbon;
use Monolog\Logger;
use Monolog\Handler\NullHandler;

class TestablePlanController extends PlanController
{
    public array $input = [];
    protected function getInput(): array { return $this->input; }
}

class TestableSubscriptionController extends SubscriptionController
{
    public array $input = [];
    protected function getInput(): array { return $this->input; }
}

class TestablePaymentController extends PaymentController
{
    public array $input = [];
    protected function getInput(): array { return $this->input; }
}

class ControllerTest extends BaseTestCase
{
    private TestablePlanController $planController;
    private TestableSubscriptionController $subscriptionController;
    private TestablePaymentController $paymentController;
    private Plan $freePlan;
    private Plan $paidPlan;

    protected function setUp(): void
    {
        parent::setUp();

        Payment::query()->delete();
        Subscription::query()->delete();
        Plan::query()->delete();

        $logger = new Logger('test');
        $logger->pushHandler(new NullHandler());

        $this->planController         = new TestablePlanController($logger);
        $this->subscriptionController = new TestableSubscriptionController($logger);
        $this->paymentController      = new TestablePaymentController($logger);

        $this->freePlan = Plan::create(['name' => 'Free',  'type' => 'free', 'price' => 0,    'duration_days' => 30, 'is_active' => true]);
        $this->paidPlan = Plan::create(['name' => 'Basic', 'type' => 'paid', 'price' => 4.99, 'duration_days' => 30, 'is_active' => true]);
    }

    // PlanController tests

    public function testIndexReturnsActivePlans(): void
    {
        $result = json_decode($this->planController->index(), true);
        $this->assertCount(2, $result);
    }

    public function testIndexDoesNotReturnInactivePlans(): void
    {
        Plan::create(['name' => 'Old', 'type' => 'paid', 'price' => 1.99, 'duration_days' => 30, 'is_active' => false]);
        $result = json_decode($this->planController->index(), true);
        $this->assertCount(2, $result);
    }

    public function testShowReturnsPlan(): void
    {
        $result = json_decode($this->planController->show((string) $this->freePlan->id), true);
        $this->assertEquals($this->freePlan->id, $result['id']);
    }

    public function testShowReturns404ForUnknownPlan(): void
    {
        $result = json_decode($this->planController->show('999'), true);
        $this->assertEquals('Plan not found', $result['error']);
    }

    // SubscriptionController tests

    public function testCreateSubscriptionForFreePlan(): void
    {
        $this->subscriptionController->input = ['user_id' => 1, 'plan_id' => $this->freePlan->id];
        $result = json_decode($this->subscriptionController->create(), true);
        $this->assertEquals('active', $result['status']);
    }

    public function testCreateSubscriptionForPaidPlanIsPending(): void
    {
        $this->subscriptionController->input = ['user_id' => 1, 'plan_id' => $this->paidPlan->id];
        $result = json_decode($this->subscriptionController->create(), true);
        $this->assertEquals('pending', $result['status']);
    }

    public function testCreateSubscriptionFailsIfFreePlanAlreadyUsed(): void
    {
        $this->subscriptionController->input = ['user_id' => 1, 'plan_id' => $this->freePlan->id];
        $this->subscriptionController->create();
        $result = json_decode($this->subscriptionController->create(), true);
        $this->assertEquals('Free plan is non-renewable', $result['error']);
    }

    public function testCreateSubscriptionFailsIfAlreadyActive(): void
    {
        Subscription::create([
            'user_id'   => 1,
            'plan_id'   => $this->paidPlan->id,
            'status'    => 'active',
            'starts_at' => Carbon::now(),
            'ends_at'   => Carbon::now()->addDays(30),
        ]);

        $this->subscriptionController->input = ['user_id' => 1, 'plan_id' => $this->paidPlan->id];
        $result = json_decode($this->subscriptionController->create(), true);
        $this->assertEquals('User already has an active subscription to this plan', $result['error']);
    }

    public function testCreateSubscriptionFailsWithMissingFields(): void
    {
        $this->subscriptionController->input = ['user_id' => 1];
        $result = json_decode($this->subscriptionController->create(), true);
        $this->assertEquals('user_id and plan_id are required', $result['error']);
    }

    public function testShowSubscription(): void
    {
        $subscription = Subscription::create([
            'user_id'   => 1,
            'plan_id'   => $this->freePlan->id,
            'status'    => 'active',
            'starts_at' => Carbon::now(),
            'ends_at'   => Carbon::now()->addDays(30),
        ]);

        $result = json_decode($this->subscriptionController->show((string) $subscription->id), true);
        $this->assertEquals($subscription->id, $result['id']);
    }

    public function testShowSubscriptionReturns404(): void
    {
        $result = json_decode($this->subscriptionController->show('999'), true);
        $this->assertEquals('Subscription not found', $result['error']);
    }

    public function testDeleteSubscription(): void
    {
        $subscription = Subscription::create([
            'user_id'   => 1,
            'plan_id'   => $this->paidPlan->id,
            'status'    => 'active',
            'starts_at' => Carbon::now(),
            'ends_at'   => Carbon::now()->addDays(30),
        ]);

        $result = json_decode($this->subscriptionController->delete((string) $subscription->id), true);
        $this->assertEquals('Subscription canceled', $result['message']);
        $this->assertEquals('canceled', Subscription::find($subscription->id)->status);
    }

    public function testDeleteAlreadyCanceledSubscriptionFails(): void
    {
        $subscription = Subscription::create([
            'user_id'   => 1,
            'plan_id'   => $this->paidPlan->id,
            'status'    => 'canceled',
            'starts_at' => Carbon::now(),
            'ends_at'   => Carbon::now()->addDays(30),
        ]);

        $result = json_decode($this->subscriptionController->delete((string) $subscription->id), true);
        $this->assertEquals('Subscription is already canceled', $result['error']);
    }

    // PaymentController tests

    public function testCreatePaymentActivatesSubscription(): void
    {
        $subscription = Subscription::create([
            'user_id'   => 1,
            'plan_id'   => $this->paidPlan->id,
            'status'    => 'pending',
            'starts_at' => Carbon::now(),
            'ends_at'   => Carbon::now()->addDays(30),
        ]);

        $this->paymentController->input = [
            'subscription_id' => $subscription->id,
            'amount'          => 4.99,
            'transaction_id'  => 'txn_123',
        ];

        $result = json_decode($this->paymentController->create(), true);
        $this->assertEquals('paid', $result['status']);
        $this->assertEquals('active', Subscription::find($subscription->id)->status);
    }

    public function testCreatePaymentFailsWithMissingFields(): void
    {
        $this->paymentController->input = ['subscription_id' => 1];
        $result = json_decode($this->paymentController->create(), true);
        $this->assertEquals('subscription_id, amount and transaction_id are required', $result['error']);
    }

    public function testCreatePaymentFailsForUnknownSubscription(): void
    {
        $this->paymentController->input = ['subscription_id' => 999, 'amount' => 4.99, 'transaction_id' => 'txn_123'];
        $result = json_decode($this->paymentController->create(), true);
        $this->assertEquals('Subscription not found', $result['error']);
    }

    public function testIndexPaymentsRequiresUserId(): void
    {
        $_GET = [];
        $result = json_decode($this->paymentController->index(), true);
        $this->assertEquals('user_id is required', $result['error']);
    }

    public function testIndexPaymentsReturnsUserPayments(): void
    {
        $subscription = Subscription::create([
            'user_id'   => 1,
            'plan_id'   => $this->paidPlan->id,
            'status'    => 'active',
            'starts_at' => Carbon::now(),
            'ends_at'   => Carbon::now()->addDays(30),
        ]);

        Payment::create([
            'subscription_id' => $subscription->id,
            'amount'          => 4.99,
            'status'          => 'paid',
            'transaction_id'  => 'txn_123',
            'paid_at'         => Carbon::now(),
        ]);

        $_GET = ['user_id' => 1];
        $result = json_decode($this->paymentController->index(), true);
        $this->assertCount(1, $result);
    }
}
