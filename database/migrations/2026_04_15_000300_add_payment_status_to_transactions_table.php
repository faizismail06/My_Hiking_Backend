<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->enum('payment_status', ['pending', 'paid', 'expired', 'failed'])
                ->default('pending')
                ->after('status_pesanan');
        });

        DB::statement(
            "UPDATE transactions t
             LEFT JOIN orders o ON o.id = t.id_pesanan
             SET t.payment_status = CASE
                WHEN LOWER(t.status_pesanan) = 'complete' THEN 'paid'
                WHEN o.status = 'Expired' THEN 'expired'
                ELSE 'pending'
             END"
        );
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('payment_status');
        });
    }
};
