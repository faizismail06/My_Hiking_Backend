<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('routes', function (Blueprint $table) {
            if (!Schema::hasColumn('routes', 'elevasi')) {
                $table->decimal('elevasi', 8, 2)->nullable()->after('jarak');
            }

            if (!Schema::hasColumn('routes', 'durasi')) {
                $table->decimal('durasi', 5, 2)->nullable()->after('elevasi');
            }

            if (!Schema::hasColumn('routes', 'tingkat_kesulitan')) {
                $table->enum('tingkat_kesulitan', ['mudah', 'sedang', 'sulit', 'sangat_sulit'])
                    ->nullable()
                    ->after('durasi');
            }

            if (!Schema::hasColumn('routes', 'panorama_score')) {
                $table->tinyInteger('panorama_score')->unsigned()->nullable()->after('tingkat_kesulitan');
            }

            if (!Schema::hasColumn('routes', 'fasilitas_score')) {
                $table->tinyInteger('fasilitas_score')->unsigned()->nullable()->after('panorama_score');
            }

            if (!Schema::hasColumn('routes', 'popularity_score')) {
                $table->unsignedInteger('popularity_score')->nullable()->after('fasilitas_score');
            }

            if (!Schema::hasColumn('routes', 'safety_score')) {
                $table->tinyInteger('safety_score')->unsigned()->nullable()->after('popularity_score');
            }

            if (!Schema::hasColumn('routes', 'crowd_level')) {
                $table->tinyInteger('crowd_level')->unsigned()->nullable()->after('safety_score');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('routes', function (Blueprint $table) {
            $dropColumns = [];

            if (Schema::hasColumn('routes', 'tingkat_kesulitan')) {
                $dropColumns[] = 'tingkat_kesulitan';
            }

            if (Schema::hasColumn('routes', 'crowd_level')) {
                $dropColumns[] = 'crowd_level';
            }

            if (Schema::hasColumn('routes', 'safety_score')) {
                $dropColumns[] = 'safety_score';
            }

            if (Schema::hasColumn('routes', 'popularity_score')) {
                $dropColumns[] = 'popularity_score';
            }

            if (Schema::hasColumn('routes', 'fasilitas_score')) {
                $dropColumns[] = 'fasilitas_score';
            }

            if (Schema::hasColumn('routes', 'panorama_score')) {
                $dropColumns[] = 'panorama_score';
            }

            if (Schema::hasColumn('routes', 'durasi')) {
                $dropColumns[] = 'durasi';
            }

            if (Schema::hasColumn('routes', 'elevasi')) {
                $dropColumns[] = 'elevasi';
            }

            if (!empty($dropColumns)) {
                $table->dropColumn($dropColumns);
            }
        });
    }
};
