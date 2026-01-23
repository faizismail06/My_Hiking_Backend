<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('anggota_pesanan', function (Blueprint $table) {
            $table->id(); // Menggunakan id auto-increment dengan tipe bigint
            $table->bigInteger('id_pesanan')->constrained('pesanan')->onDelete('cascade');
            $table->bigInteger('id_user')->nullable()->constrained('users');
            $table->timestamps(); // Menambahkan kolom created_at dan updated_at

            $table->foreign('id_user')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('id_pesanan')->references('id')->on('pesanan')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('anggota_pesanan');
    }
};
