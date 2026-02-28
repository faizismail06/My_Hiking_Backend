<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();                           // Primary Key: ID pesanan (auto increment)
            $table->unsignedBigInteger('id_gunung'); // Foreign key ke tabel mountains
            $table->unsignedBigInteger('id_jalur');  // Foreign key ke tabel routes
            $table->unsignedBigInteger('id_user');   // Pemesan utama
            $table->date('tanggal_naik');            // Kolom tanggal naik
            $table->date('tanggal_turun');           // Kolom tanggal turun
            $table->double('total_harga_tiket');     // Kolom total harga tiket
            $table->enum('status', ['Booking', 'Sedang Mendaki', 'Selesai'])->default('Booking'); // Kolom status
            $table->timestamps();                    // Kolom created_at dan updated_at

            // Foreign keys
            $table->foreign('id_gunung')->references('id')->on('mountains')->onDelete('cascade');
            $table->foreign('id_jalur')->references('id')->on('routes')->onDelete('cascade');
            $table->foreign('id_user')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('orders');
    }
};
