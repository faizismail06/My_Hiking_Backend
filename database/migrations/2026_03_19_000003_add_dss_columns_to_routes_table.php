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
