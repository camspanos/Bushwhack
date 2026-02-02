<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Monthly',
                'slug' => 'monthly',
                'description' => 'Perfect for trying out premium features',
                'price' => 999, // $9.99
                'billing_interval' => 'month',
                'billing_interval_count' => 1,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Annual',
                'slug' => 'annual',
                'description' => 'Best value - save 2 months!',
                'price' => 9999, // $99.99
                'billing_interval' => 'year',
                'billing_interval_count' => 1,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Lifetime',
                'slug' => 'lifetime',
                'description' => 'One-time payment, forever access',
                'price' => 24999, // $249.99
                'billing_interval' => 'lifetime',
                'billing_interval_count' => 1,
                'is_active' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::updateOrCreate(
                ['slug' => $plan['slug']],
                $plan
            );
        }
    }
}

