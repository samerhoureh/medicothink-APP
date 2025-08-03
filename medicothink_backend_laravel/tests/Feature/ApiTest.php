<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\SubscriptionPlan;
use App\Models\Subscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test user
        $this->user = User::factory()->create([
            'email' => 'test@medicothink.com',
            'password' => bcrypt('password123'),
        ]);
        
        // Create subscription plan
        $plan = SubscriptionPlan::create([
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
            'features' => ['AI Chat', 'Basic Support'],
            'is_active' => true,
        ]);
        
        // Create active subscription
        Subscription::create([
            'user_id' => $this->user->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);
        
        // Generate token
        $this->token = $this->user->createToken('test-token')->plainTextToken;
    }

    public function test_health_check()
    {
        $response = $this->getJson('/api/health');
        
        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'MedicoThink API is running',
                ]);
    }

    public function test_system_status()
    {
        $response = $this->getJson('/api/status');
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'database',
                        'ai_services',
                        'payment_gateway',
                        'storage',
                    ],
                ]);
    }

    public function test_user_registration()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'newuser@medicothink.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone_number' => '+966501234567',
        ];

        $response = $this->postJson('/api/auth/register', $userData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'user',
                        'token',
                        'token_type',
                    ],
                ]);
    }

    public function test_user_login()
    {
        $loginData = [
            'email' => 'test@medicothink.com',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/auth/login', $loginData);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'user',
                        'token',
                        'token_type',
                    ],
                ]);
    }

    public function test_get_user_profile()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/auth/me');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'user' => [
                            'id',
                            'name',
                            'email',
                            'subscription',
                        ],
                    ],
                ]);
    }

    public function test_ai_chat()
    {
        $chatData = [
            'message' => 'What are the symptoms of flu?',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/ai/chat', $chatData);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'conversation_id',
                        'user_message',
                        'ai_message',
                    ],
                ]);
    }

    public function test_image_analysis()
    {
        Storage::fake('public');
        
        $file = UploadedFile::fake()->image('medical_image.jpg');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/ai/analyze-image', [
            'image' => $file,
            'question' => 'What do you see in this image?',
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'conversation_id',
                        'user_message',
                        'analysis',
                    ],
                ]);
    }

    public function test_generate_flashcards()
    {
        $flashcardData = [
            'topic' => 'Cardiovascular System',
            'count' => 3,
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/ai/generate-flashcards', $flashcardData);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'conversation_id',
                        'flashcards',
                        'message_id',
                        'topic',
                        'count',
                    ],
                ]);
    }

    public function test_get_conversations()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/conversations');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'conversations',
                        'pagination',
                    ],
                ]);
    }

    public function test_subscription_status()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/subscription/status');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'has_subscription',
                        'subscription',
                        'plan',
                        'usage',
                    ],
                ]);
    }

    public function test_get_subscription_plans()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/subscription/plans');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'plans',
                    ],
                ]);
    }

    public function test_payment_history()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/payment/history');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'payments',
                        'pagination',
                    ],
                ]);
    }

    public function test_unauthorized_access()
    {
        $response = $this->postJson('/api/ai/chat', [
            'message' => 'Test message',
        ]);

        $response->assertStatus(401);
    }

    public function test_subscription_required()
    {
        // Create user without subscription
        $userWithoutSub = User::factory()->create();
        $tokenWithoutSub = $userWithoutSub->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $tokenWithoutSub,
        ])->postJson('/api/ai/chat', [
            'message' => 'Test message',
        ]);

        $response->assertStatus(403)
                ->assertJson([
                    'success' => false,
                    'error_code' => 'SUBSCRIPTION_REQUIRED',
                ]);
    }
}