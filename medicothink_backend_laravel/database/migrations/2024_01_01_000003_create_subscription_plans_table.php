<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // basic, advanced, premium
            $table->string('display_name_en');
            $table->string('display_name_ar');
            $table->decimal('price', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->string('duration')->default('monthly'); // monthly, yearly
            $table->integer('tokens_limit')->default(-1); // -1 for unlimited
            $table->integer('images_limit')->default(-1);
            $table->integer('videos_limit')->default(-1);
            $table->integer('conversations_limit')->default(-1);
            $table->json('features'); // array of features
            $table->boolean('is_active')->default(true);
            $table->boolean('is_popular')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('subscription_plans');
    }
};