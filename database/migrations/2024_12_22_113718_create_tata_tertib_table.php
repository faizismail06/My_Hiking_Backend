<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('tata_tertibs', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->unsignedBigInteger('jalur_id'); // Sesuaikan tipe datanya
            $table->foreign('jalur_id')->references('id')->on('jalur')->onDelete('cascade');
            $table->longText('description'); // Kolom teks panjang untuk deskripsi
            $table->timestamps(); // Kolom created_at dan updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('tata_tertibs');
    }
};
