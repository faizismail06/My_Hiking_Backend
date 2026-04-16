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
        Schema::table('user_experiences', function (Blueprint $table) {
            $table->json('questionnaire_answers')->nullable()->after('jumlah_summit');
            $table->unsignedSmallInteger('weighted_score')->nullable()->after('questionnaire_answers');
            $table->enum('weighted_tier', ['pemula', 'menengah', 'mahir'])->nullable()->after('weighted_score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_experiences', function (Blueprint $table) {
            $table->dropColumn(['questionnaire_answers', 'weighted_score', 'weighted_tier']);
        });
    }
};
