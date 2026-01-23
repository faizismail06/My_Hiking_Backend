<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->string('nama_pembayaran'); // Nama payment
            $table->string('nomor_pembayaran'); // Nomor payment
            $table->string('gambar_pembayaran')->nullable(); // Gambar payment
            $table->timestamps(); // Kolom created_at dan updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
