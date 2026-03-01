<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_pesanan');
            $table->integer('total_bayar');
            $table->enum('status_pesanan', ['Incomplete', 'Complete'])->default('Incomplete');
            $table->datetime('waktu_pembayaran')->nullable();
            $table->string('bukti')->nullable();
            
            // Midtrans Payment Gateway columns
            $table->string('snap_token')->nullable();
            $table->string('midtrans_order_id')->nullable();
            $table->string('payment_type')->nullable();
            $table->string('transaction_id')->nullable();
            $table->datetime('transaction_time')->nullable();
            $table->string('fraud_status')->nullable();
            
            $table->timestamps();

            // Foreign keys
            $table->foreign('id_pesanan')->references('id')->on('orders')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('transactions');
    }
};
