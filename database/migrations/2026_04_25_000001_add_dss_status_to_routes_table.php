<?php
// database/migrations/2026_04_25_000001_add_dss_status_to_routes_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('routes', 'dss_status')) {
            return;
        }

        $afterColumn = collect([
            'is_refund_allowed',
            'daily_hiker_limit',
            'route_source',
            'route_points',
            'longitude',
            'latitude',
            'biaya',
        ])->first(fn (string $column) => Schema::hasColumn('routes', $column));

        Schema::table('routes', function (Blueprint $table) use ($afterColumn) {
            $column = $table->enum('dss_status', ['pending', 'approved'])
                ->default('approved')
                ->comment('Status verifikasi data DSS oleh admin');

            if ($afterColumn !== null) {
                $column->after($afterColumn);
            }
        });

        // Data lama otomatis approved
        if (Schema::hasColumn('routes', 'panorama_score')) {
            DB::table('routes')
                ->whereNotNull('panorama_score')
                ->update(['dss_status' => 'approved']);
        }
    }

    public function down(): void
    {
        if (!Schema::hasColumn('routes', 'dss_status')) {
            return;
        }

        Schema::table('routes', function (Blueprint $table) {
            $table->dropColumn('dss_status');
        });
    }
};
