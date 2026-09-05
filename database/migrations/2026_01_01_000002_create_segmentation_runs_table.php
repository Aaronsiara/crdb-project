<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tracks every dataset a user uploads for segmentation: parameters,
 * status, and result summary. Actual result files (CSVs, plots) live on
 * disk under storage/app/segmentation/<run_uid>/; this table is the
 * queryable index over them.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('segmentation_runs', function (Blueprint $table) {
            $table->id();
            $table->string('run_uid', 64)->unique(); // e.g. 20260101_120000_ab12cd
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('original_filename');
            $table->unsignedInteger('k')->nullable();       // null = auto-selected
            $table->string('features', 500)->nullable();     // comma-separated columns, null = auto-detected
            $table->string('status', 20)->default('pending'); // pending, running, complete, failed
            $table->unsignedInteger('total_records')->nullable();
            $table->unsignedInteger('num_segments')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('segmentation_runs');
    }
};
