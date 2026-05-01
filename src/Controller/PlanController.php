<?php

namespace App\Controller;

use App\Model\Plan;

class PlanController extends BaseController
{
    public function index(): string
    {
        $plans = Plan::where('is_active', true)->get();
        return $this->success($plans);
    }

    public function show(string $id): string
    {
        $plan = Plan::where('is_active', true)->find((int) $id);

        if (!$plan) {
            return $this->error('Plan not found', 404);
        }

        return $this->success($plan);
    }
}