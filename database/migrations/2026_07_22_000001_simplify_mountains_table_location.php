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
            $table->dropForeign(['regency_id']);
            $table->dropForeign(['district_id']);
            $table->dropForeign(['village_id']);

            $table->dropColumn(['regency_id', 'district_id', 'village_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mountains', function (Blueprint $table) {
            $table->char('regency_id', 4)->nullable()->after('province_id');
            $table->char('district_id', 7)->nullable()->after('regency_id');
            $table->char('village_id', 10)->nullable()->after('district_id');

            $table->foreign('regency_id')->references('id')->on('reg_regencies')->onDelete('cascade');
            $table->foreign('district_id')->references('id')->on('reg_districts')->onDelete('cascade');
            $table->foreign('village_id')->references('id')->on('reg_villages')->onDelete('cascade');
        });
    }
};
