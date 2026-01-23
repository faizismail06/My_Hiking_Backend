<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('jalur', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 60);
            $table->unsignedBigInteger('id_gunung'); // Sesuai dengan tipe data id di tabel gunung
            $table->char('province_id', 2); // Sesuai dengan tipe data di reg_provinces
            $table->char('regency_id', 4); // Sesuai dengan tipe data di reg_regencies
            $table->char('district_id', 7); // Sesuai dengan tipe data di reg_districts
            $table->char('village_id', 10); // Sesuai dengan tipe data di reg_villages
            $table->integer('jarak');
            $table->text('deskripsi');
            $table->string('map_basecamp', 60);
            $table->string('gambar_jalur')->nullable();
            $table->integer('biaya');
            $table->timestamps();

            // Definisi foreign key
            $table->foreign('id_gunung')->references('id')->on('gunung')->onDelete('cascade');
            $table->foreign('province_id')->references('id')->on('reg_provinces')->onDelete('cascade');
            $table->foreign('regency_id')->references('id')->on('reg_regencies')->onDelete('cascade');
            $table->foreign('district_id')->references('id')->on('reg_districts')->onDelete('cascade');
            $table->foreign('village_id')->references('id')->on('reg_villages')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jalur');
    }
};
