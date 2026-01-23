<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pesanan', function (Blueprint $table) {
            $table->bigInteger('id')->primary();           // Primary Key: ID pesanan
            $table->foreignId('id_gunung')->constrained('gunung'); // Foreign key ke tabel gunung
            $table->foreignId('id_jalur')->constrained('jalur');   // Foreign key ke tabel jalur
            $table->bigInteger('id_user')->constrained('users')->onDelete('cascade'); // Pemesan utama
            $table->date('tanggal_naik'); // Kolom tanggal naik
            $table->date('tanggal_turun'); // Kolom tanggal turun
            $table->double('total_harga_tiket'); // Kolom total harga tiket
            $table->enum('status', ['Booking', 'Sedang Mendaki', 'Selesai'])->default('Booking'); // Kolom status dengan pilihan enum
            $table->timestamps(); // Kolom created_at dan updated_at

            $table->foreign('id_user')->references('id')->on('users')->onDelete('cascade');

        });
    }

    public function down()
    {
        Schema::dropIfExists('pesanan');
    }
};
