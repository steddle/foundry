<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Published into an imprint that onboards, with
 * `php artisan vendor:publish --tag=foundry-onboarding`. Every existing
 * account with a name of its own counts as onboarded; one named after the
 * local part of its address names itself on /welcome.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->timestamp('onboarded_at')->nullable();
        });

        DB::table('users')->select(['id', 'name', 'email'])->chunkById(500, function ($users): void {
            $named = $users
                ->reject(fn (object $user): bool => in_array(Str::lower(trim((string) $user->name)), ['', Str::lower(trim(Str::before((string) $user->email, '@')))], true))
                ->pluck('id');

            DB::table('users')->whereIn('id', $named)->update(['onboarded_at' => now()]);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('onboarded_at');
        });
    }
};
