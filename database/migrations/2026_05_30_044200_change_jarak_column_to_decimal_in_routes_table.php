<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * Mengubah kolom `jarak` dari integer ke decimal(8,2)
     * agar mendukung data desimal/float (misal: 5.63 km).
     */
    public function up(): void
    {
        Schema::table('routes', function (Blueprint $table) {
            $table->decimal('jarak', 8, 2)->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('routes', function (Blueprint $table) {
            $table->integer('jarak')->change();
        });
    }
};
