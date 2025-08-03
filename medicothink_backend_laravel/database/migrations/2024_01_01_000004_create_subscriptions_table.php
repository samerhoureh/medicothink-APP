<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('plan_id')->constrained('subscription_plans')->onDelete('cascade');
            $table->enum('status', ['pending', 'active', 'cancelled', 'expired'])->default('pending');
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->boolean('auto_renewal')->default(true);
            $table->integer('tokens_used')->default(0);
            $table->integer('images_used')->default(0);
            $table->integer('videos_used')->default(0);
            $table->integer('conversations_used')->default(0);
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('subscriptions');
    }
};