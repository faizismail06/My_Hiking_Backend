<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('reg_regencies', function (Blueprint $table) {
            $table->char('id', 4)->primary(); // Kode kabupaten/kota, biasanya 4 karakter
            $table->char('province_id', 2); // Kode provinsi
            $table->string('name', 100); // Nama kabupaten/kota
            $table->timestamps();

            $table->foreign('province_id')->references('id')->on('reg_provinces')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('reg_regencies');
    }
};
