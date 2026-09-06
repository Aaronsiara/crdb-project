<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds two columns to `users`:
 *   - position: which Data Department role this account represents
 *   - avatar_path: path (relative to the "public" storage disk) to a
 *     profile photo of that person
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('position', 64)->nullable()->after('role');
            $table->string('avatar_path')->nullable()->after('position');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['position', 'avatar_path']);
        });
    }
};
