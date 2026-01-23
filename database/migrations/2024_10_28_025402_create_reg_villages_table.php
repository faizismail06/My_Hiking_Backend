<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('reg_villages', function (Blueprint $table) {
            $table->char('id', 10)->primary(); // Kode desa/kelurahan, biasanya 10 karakter
            $table->char('district_id', 6); // Kode kecamatan
            $table->string('name', 100); // Nama desa/kelurahan
            $table->timestamps();

            $table->foreign('district_id')->references('id')->on('reg_districts')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('reg_villages');
    }
};
