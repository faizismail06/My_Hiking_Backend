<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('trail_posts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('trail_id');
            $table->string('name', 120);
            $table->unsignedInteger('sequence')->default(1);
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->decimal('elevation', 8, 2)->nullable();
            $table->string('icon_type', 50)->default('signpost');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->foreign('trail_id')
                ->references('id')
                ->on('routes')
                ->onDelete('cascade');

            $table->unique(['trail_id', 'sequence']);
            $table->index(['trail_id', 'latitude', 'longitude']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trail_posts');
    }
};
