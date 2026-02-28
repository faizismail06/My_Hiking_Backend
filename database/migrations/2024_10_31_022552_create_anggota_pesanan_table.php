<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('order_members', function (Blueprint $table) {
            $table->id();                                    // Auto-increment primary key
            $table->unsignedBigInteger('id_pesanan');        // Foreign key ke orders
            $table->unsignedBigInteger('id_user')->nullable(); // Foreign key ke users (nullable)
            $table->timestamps();                            // created_at dan updated_at

            // Foreign keys
            $table->foreign('id_pesanan')->references('id')->on('orders')->onDelete('cascade');
            $table->foreign('id_user')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('order_members');
    }
};
