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
            $table->string('face_photo_path')->nullable()->after('profile_picture');
            $table->boolean('is_face_verified')->default(false)->after('face_photo_path');
            $table->timestamp('face_verified_at')->nullable()->after('is_face_verified');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['face_photo_path', 'is_face_verified', 'face_verified_at']);
        });
    }
};
