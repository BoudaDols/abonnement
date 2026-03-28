<?php

namespace App\Controller;

use App\Model\Plan;
use App\Model\Subscription;
use Carbon\Carbon;

class SubscriptionController extends BaseController
{
    public function show(string $id): string
    {
        $subscription = Subscription::with('plan')->find($id);

        if (!$subscription) {
            return $this->error('Subscription not found', 404);
        }

        return $this->success($subscription);
    }

    public function create(): string
    {
        $body = $this->getInput();
        $userId = $body['user_id'] ?? null;
        $planId = $body['plan_id'] ?? null;

        if (!$userId || !$planId) {
            return $this->error('user_id and plan_id are required', 422);
        }

        $plan = Plan::where('is_active', true)->find($planId);

        if (!$plan) {
            return $this->error('Plan not found', 404);
        }

        // Free plan: check if user already had a free subscription
        if ($plan->isFree()) {
            $exists = Subscription::where('user_id', $userId)
                ->whereHas('plan', fn($q) => $q->where('type', 'free'))
                ->exists();

            if ($exists) {
                return $this->error('Free plan is non-renewable', 422);
            }
        }

        // Check if user already has an active subscription to this plan
        $active = Subscription::where('user_id', $userId)
            ->where('plan_id', $planId)
            ->where('status', 'active')
            ->where('ends_at', '>', Carbon::now())
            ->exists();

        if ($active) {
            return $this->error('User already has an active subscription to this plan', 422);
        }

        $subscription = Subscription::create([
            'user_id'   => $userId,
            'plan_id'   => $planId,
            'status'    => $plan->isFree() ? 'active' : 'pending',
            'starts_at' => Carbon::now(),
            'ends_at'   => Carbon::now()->addDays($plan->duration_days),
        ]);

        return $this->json($subscription->load('plan'), 201);
    }

    public function delete(string $id): string
    {
        $subscription = Subscription::find($id);

        if (!$subscription) {
            return $this->error('Subscription not found', 404);
        }

        if ($subscription->status === 'canceled') {
            return $this->error('Subscription is already canceled', 422);
        }

        $subscription->update(['status' => 'canceled']);

        return $this->success(['message' => 'Subscription canceled']);
    }
}