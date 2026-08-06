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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'is_emergency_phone_verified')) {
                $table->boolean('is_emergency_phone_verified')->default(false)->after('emergency_phone');
            }
            if (!Schema::hasColumn('users', 'emergency_phone_verified_at')) {
                $table->timestamp('emergency_phone_verified_at')->nullable()->after('is_emergency_phone_verified');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'is_emergency_phone_verified')) {
                $table->dropColumn('is_emergency_phone_verified');
            }
            if (Schema::hasColumn('users', 'emergency_phone_verified_at')) {
                $table->dropColumn('emergency_phone_verified_at');
            }
        });
    }
};
