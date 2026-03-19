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
        if (Schema::hasTable('chat_histories')) {
            return;
        }

        Schema::create('chat_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('role', 20)->default('pendaki');
            $table->string('title')->nullable();
            $table->longText('messages');
            $table->timestamps();

            $table->index(['user_id', 'role', 'updated_at'], 'idx_chat_histories_user_role_updated');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_histories');
    }
};
