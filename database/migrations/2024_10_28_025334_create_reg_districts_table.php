<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('reg_districts', function (Blueprint $table) {
            $table->char('id', 6)->primary(); // Kode kecamatan, biasanya 7 karakter
            $table->char('regency_id', 4); // Kode kabupaten/kota
            $table->string('name', 100); // Nama kecamatan
            $table->timestamps();

            $table->foreign('regency_id')->references('id')->on('reg_regencies')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('reg_districts');
    }
};
