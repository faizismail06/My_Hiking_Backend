<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rules\Unique;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigInteger('id')->primary();           // Primary Key: ID user
            $table->string('name');                   // Nama user atau pendaki
            $table->string('email')->unique();        // Email user
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');               // Password user
            $table->integer('level')->check('level IN (1, 2, 3)')->nullable();  // Level user (opsional)
            $table->string('address')->nullable();    // Alamat user (opsional)
            $table->bigInteger('nik')->unique()->nullable(); // NIK user (opsional dan unik)
            $table->bigInteger('phone')->unique()->nullable(); // Nomor telepon user (opsional dan unik)
            $table->bigInteger('emergency_phone')->nullable(); // Nomor telepon darurat user (opsional)
            $table->string('profile_picture')->nullable(); // Kartu Identitas (opsional)
            $table->date('date_of_birth')->nullable(); // Tanggal lahir user (opsional)
            $table->rememberToken();                  // Token remember me (untuk autentikasi)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
