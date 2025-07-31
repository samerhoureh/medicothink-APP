<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->after('id');
            $table->string('phone_number')->unique()->after('email');
            $table->integer('age')->nullable()->after('phone_number');
            $table->string('city')->nullable()->after('age');
            $table->string('nationality')->nullable()->after('city');
            $table->enum('specialization', ['Medical', 'Dentist', 'Pharmacy', 'X-ray'])->nullable()->after('nationality');
            $table->enum('education_level', ['High School', 'Bachelor', 'Master', 'PhD', 'Other'])->nullable()->after('specialization');
            $table->string('profile_image')->nullable()->after('education_level');
            $table->timestamp('phone_verified_at')->nullable()->after('email_verified_at');
            $table->boolean('is_active')->default(true)->after('password');
            $table->timestamp('last_login_at')->nullable()->after('is_active');
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'username', 'phone_number', 'age', 'city', 'nationality',
                'specialization', 'education_level', 'profile_image',
                'phone_verified_at', 'is_active', 'last_login_at'
            ]);
            $table->dropSoftDeletes();
        });
    }
};