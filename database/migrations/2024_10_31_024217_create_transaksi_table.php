<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();                                    // Auto-increment primary key
            $table->unsignedBigInteger('id_pesanan');        // Foreign key ke tabel orders
            $table->unsignedBigInteger('payment_id');        // Foreign key ke tabel payments
            $table->integer('total_bayar');
            $table->enum('status_pesanan', ['Verified', 'Unverified', 'Incomplete'])->default('Incomplete');
            $table->date('waktu_pembayaran')->nullable();
            $table->string('bukti')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('id_pesanan')->references('id')->on('orders')->onDelete('cascade');
            $table->foreign('payment_id')->references('id')->on('payments')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('transactions');
    }
};
