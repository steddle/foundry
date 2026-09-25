<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Published into an imprint that signs in through the Steddle account, with
 * `php artisan vendor:publish --tag=foundry-account`. An existing row is
 * linked at its account's first sign-in, by its address. The account signs
 * in on auth, so the password and its resets go: a row made at a first
 * sign-in has no password, and Laravel's stock column refuses it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('steddle_id')->nullable()->unique();
        });

        if (Schema::hasColumn('users', 'password')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->dropColumn('password');
            });
        }

        Schema::dropIfExists('password_reset_tokens');
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropUnique(['steddle_id']);
            $table->dropColumn('steddle_id');
            $table->string('password')->nullable();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table): void {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }
};
