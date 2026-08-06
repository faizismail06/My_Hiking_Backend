<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mountains', function (Blueprint $table) {
            if (Schema::hasColumn('mountains', 'regency_id')) {
                try {
                    $table->dropForeign(['regency_id']);
                } catch (\Throwable $e) {}
                $table->dropColumn('regency_id');
            }
            if (Schema::hasColumn('mountains', 'district_id')) {
                try {
                    $table->dropForeign(['district_id']);
                } catch (\Throwable $e) {}
                $table->dropColumn('district_id');
            }
            if (Schema::hasColumn('mountains', 'village_id')) {
                try {
                    $table->dropForeign(['village_id']);
                } catch (\Throwable $e) {}
                $table->dropColumn('village_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mountains', function (Blueprint $table) {
            if (!Schema::hasColumn('mountains', 'regency_id')) {
                $table->char('regency_id', 4)->nullable()->after('province_id');
            }
            if (!Schema::hasColumn('mountains', 'district_id')) {
                $table->char('district_id', 7)->nullable()->after('regency_id');
            }
            if (!Schema::hasColumn('mountains', 'village_id')) {
                $table->char('village_id', 10)->nullable()->after('district_id');
            }
        });
    }
};
