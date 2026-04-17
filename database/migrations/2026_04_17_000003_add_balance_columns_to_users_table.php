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
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('total_earnings', 15, 2)->default(0)->after('tier_source')->comment('Total pendapatan dari jalur');
            $table->decimal('withdrawn_amount', 15, 2)->default(0)->after('total_earnings')->comment('Total amount withdrawn');
            $table->decimal('available_balance', 15, 2)->default(0)->after('withdrawn_amount')->comment('Saldo yang bisa ditarik');
            $table->integer('transaction_count')->default(0)->after('available_balance')->comment('Jumlah transaksi di jalur');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['total_earnings', 'withdrawn_amount', 'available_balance', 'transaction_count']);
        });
    }
};
