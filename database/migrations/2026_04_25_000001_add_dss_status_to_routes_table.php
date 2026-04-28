<?php
// database/migrations/2026_04_25_000001_add_dss_status_to_routes_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('routes', function (Blueprint $table) {
            $table->enum('dss_status', ['pending', 'approved'])
                  ->default('approved')
                  ->comment('Status verifikasi data DSS oleh admin');
        });
    }

    public function down(): void
    {
        Schema::table('routes', function (Blueprint $table) {
            $table->dropColumn('dss_status');
        });
    }
};