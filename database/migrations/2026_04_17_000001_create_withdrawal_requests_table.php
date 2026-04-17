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
        if (Schema::hasTable('withdrawal_requests')) {
            return;
        }

        Schema::create('withdrawal_requests', function (Blueprint $table) {
            $table->id();
            $table->string('user_id'); // ID penjaga jalur yang request
            $table->decimal('amount', 15, 2); // Jumlah yang diminta
            $table->decimal('admin_fee', 15, 2)->default(0); // Biaya admin
            $table->decimal('net_amount', 15, 2); // Jumlah bersih setelah biaya admin
            $table->enum('withdrawal_method', ['bank_transfer', 'e_wallet']); // Metode penarikan
            $table->string('bank_name')->nullable(); // Nama bank (jika bank transfer)
            $table->string('account_number')->nullable(); // Nomor rekening
            $table->string('account_holder')->nullable(); // Nama pemegang rekening
            $table->string('e_wallet_type')->nullable(); // Tipe e-wallet (GCash, Grab, etc)
            $table->string('e_wallet_number')->nullable(); // Nomor e-wallet
            $table->enum('status', ['pending', 'approved', 'rejected', 'completed'])->default('pending');
            $table->text('rejection_reason')->nullable(); // Alasan penolakan
            $table->string('approved_by')->nullable(); // ID admin yang approve
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('withdrawal_requests');
    }
};
