<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('app_versions', function (Blueprint $table) {
            $table->id();
            $table->string('version_name'); // e.g., "1.2.0"
            $table->integer('version_code'); // e.g., 120
            $table->enum('platform', ['android', 'ios']);
            $table->string('download_url');
            $table->boolean('is_required')->default(false);
            $table->boolean('is_active')->default(true);
            $table->json('release_notes')->nullable();
            $table->string('min_supported_version')->nullable();
            $table->timestamps();

            $table->index(['platform', 'is_active']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('app_versions');
    }
};