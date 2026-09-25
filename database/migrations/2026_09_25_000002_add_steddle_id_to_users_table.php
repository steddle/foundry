<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Published into an imprint that signs in through the Steddle account, with
 * `php artisan vendor:publish --tag=foundry-account`. An existing row is
 * linked at its account's first sign-in, by its address.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('steddle_id')->nullable()->unique();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropUnique(['steddle_id']);
            $table->dropColumn('steddle_id');
        });
    }
};
