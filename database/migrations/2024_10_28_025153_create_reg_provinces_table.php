<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('reg_provinces', function (Blueprint $table) {
            $table->char('id', 2)->primary(); // Kode provinsi, biasanya 2 karakter
            $table->string('name', 100); // Nama provinsi
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('reg_provinces');
    }
};
