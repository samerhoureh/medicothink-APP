<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('conversation_summaries', function (Blueprint $table) {
            $table->id();
            $table->string('conversation_id');
            $table->string('title');
            $table->text('summary');
            $table->json('key_points')->nullable();
            $table->json('recommendations')->nullable();
            $table->text('diagnosis')->nullable();
            $table->json('symptoms')->nullable();
            $table->text('treatment')->nullable();
            $table->timestamps();

            $table->foreign('conversation_id')->references('id')->on('conversations')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('conversation_summaries');
    }
};