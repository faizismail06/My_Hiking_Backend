<?php
// database/migrations/2026_04_25_000002_create_dss_pending_submissions_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dss_pending_submissions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('route_id')
                  ->constrained('routes')
                  ->onDelete('cascade');

                  $table->foreignId('submitted_by')
                        ->constrained('users')
                        ->onDelete('cascade');

            // Data DSS yang menunggu verifikasi
            $table->tinyInteger('panorama_score_pending')->unsigned();
            $table->tinyInteger('fasilitas_score_pending')->unsigned();
            $table->tinyInteger('safety_score_pending')->unsigned();
            $table->tinyInteger('crowd_level_pending')->unsigned();
            $table->integer('popularity_score_pending')->unsigned()->nullable();

            $table->enum('status', ['pending', 'approved', 'rejected'])
                  ->default('pending');

            $table->text('rejection_reason')->nullable()
                  ->comment('Diisi admin jika rejected');

                  $table->foreignId('reviewed_by')
                        ->nullable()
                        ->constrained('users')
                        ->onDelete('set null');

            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();

            // Satu route hanya boleh punya 1 submission pending aktif
            $table->unique(['route_id', 'status'], 'unique_pending_per_route');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dss_pending_submissions');
    }
};