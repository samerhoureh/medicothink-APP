<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SubscriptionPlan;

class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'basic',
                'display_name_en' => 'Basic Plan',
                'display_name_ar' => 'الباقة الأساسية',
                'price' => 9.99,
                'currency' => 'USD',
                'duration' => 'monthly',
                'tokens_limit' => 100,
                'images_limit' => 10,
                'videos_limit' => 2,
                'conversations_limit' => 50,
                'features' => [
                    'AI Chat Support',
                    'Basic Image Analysis',
                    'Limited Video Generation',
                    'Email Support',
                ],
                'is_active' => true,
                'is_popular' => false,
            ],
            [
                'name' => 'advanced',
                'display_name_en' => 'Advanced Plan',
                'display_name_ar' => 'الباقة المتقدمة',
                'price' => 19.99,
                'currency' => 'USD',
                'duration' => 'monthly',
                'tokens_limit' => 500,
                'images_limit' => 50,
                'videos_limit' => 10,
                'conversations_limit' => 200,
                'features' => [
                    'Advanced AI Chat',
                    'Medical Image Analysis',
                    'Video Generation',
                    'Flashcards Generation',
                    'Priority Support',
                ],
                'is_active' => true,
                'is_popular' => true,
            ],
            [
                'name' => 'premium',
                'display_name_en' => 'Premium Plan',
                'display_name_ar' => 'الباقة المميزة',
                'price' => 39.99,
                'currency' => 'USD',
                'duration' => 'monthly',
                'tokens_limit' => -1, // Unlimited
                'images_limit' => -1, // Unlimited
                'videos_limit' => -1, // Unlimited
                'conversations_limit' => -1, // Unlimited
                'features' => [
                    'Unlimited AI Chat',
                    'Unlimited Image Analysis',
                    'Unlimited Video Generation',
                    'Advanced Flashcards',
                    'Priority Support',
                    'API Access',
                    'Custom AI Models',
                ],
                'is_active' => true,
                'is_popular' => false,
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::create($plan);
        }
    }
}