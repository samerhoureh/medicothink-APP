<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('plan_name');
            $table->enum('plan_type', ['basic', 'premium', 'professional'])->default('basic');
            $table->decimal('price', 8, 2);
            $table->string('currency', 3)->default('USD');
            $table->timestamp('starts_at');
            $table->timestamp('expires_at');
            $table->boolean('is_active')->default(true);
            $table->boolean('auto_renew')->default(false);
            $table->string('stripe_subscription_id')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'is_active']);
            $table->index(['expires_at', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};