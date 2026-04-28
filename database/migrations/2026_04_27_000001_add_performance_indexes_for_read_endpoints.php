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
        Schema::table('orders', function (Blueprint $table) {
            $table->index(
                ['id_jalur', 'tanggal_naik', 'tanggal_turun', 'status'],
                'idx_orders_trail_dates_status'
            );
            $table->index(['id_user', 'status'], 'idx_orders_user_status');
        });

        Schema::table('friends', function (Blueprint $table) {
            $table->index(['user_id', 'status'], 'idx_friends_user_status');
            $table->index(['friend_id', 'status'], 'idx_friends_friend_status');
        });

        Schema::table('rules', function (Blueprint $table) {
            $table->index(['jalur_id'], 'idx_rules_jalur_id');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->index(['id_pesanan', 'status_pesanan'], 'idx_transactions_order_status');
        });

        Schema::table('panic_requests', function (Blueprint $table) {
            $table->index(['order_id', 'status'], 'idx_panic_order_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('idx_orders_trail_dates_status');
            $table->dropIndex('idx_orders_user_status');
        });

        Schema::table('friends', function (Blueprint $table) {
            $table->dropIndex('idx_friends_user_status');
            $table->dropIndex('idx_friends_friend_status');
        });

        Schema::table('rules', function (Blueprint $table) {
            $table->dropIndex('idx_rules_jalur_id');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex('idx_transactions_order_status');
        });

        Schema::table('panic_requests', function (Blueprint $table) {
            $table->dropIndex('idx_panic_order_status');
        });
    }
};
