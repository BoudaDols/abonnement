<?php

namespace App\Seeder;

use App\Model\Plan;
use App\Service\LoggerFactory;

class PlanSeeder
{
    public static function run(): void
    {
        $logger = (new LoggerFactory())->createLogger('seeder');

        $plans = [
            ['name' => 'Free',    'type' => 'free', 'price' => 0,     'duration_days' => 30,  'is_active' => true],
            ['name' => 'Basic',   'type' => 'paid', 'price' => 4.99,  'duration_days' => 30,  'is_active' => true],
            ['name' => 'Pro',     'type' => 'paid', 'price' => 9.99,  'duration_days' => 30,  'is_active' => true],
            ['name' => 'Premium', 'type' => 'paid', 'price' => 19.99, 'duration_days' => 365, 'is_active' => true],
        ];

        $logger->info('Starting plan seeder');

        foreach ($plans as $plan) {
            $created = Plan::firstOrCreate(['name' => $plan['name']], $plan);
            $action = $created->wasRecentlyCreated ? 'inserted' : 'already exists';
            $logger->info("Plan {$plan['name']}: {$action}");
        }

        $logger->info('Plan seeder completed');
    }
}