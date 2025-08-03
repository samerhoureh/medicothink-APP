<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained()->onDelete('cascade');
            $table->text('content');
            $table->boolean('is_from_user');
            $table->enum('message_type', ['text', 'image', 'video', 'flashcards'])->default('text');
            $table->string('image_path')->nullable();
            $table->string('video_path')->nullable();
            $table->json('flashcards')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['conversation_id', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('messages');
    }
};