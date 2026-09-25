<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Published into an imprint that signs in by email, with
 * `php artisan vendor:publish --tag=foundry-sign-in`. Only the token's
 * SHA-256 is stored, so a leaked table signs no one in.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('magic_links', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->char('token_hash', 64)->unique();
            $table->string('purpose')->default('login');
            $table->text('intended')->nullable();
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->string('used_ip', 45)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('magic_links');
    }
};
