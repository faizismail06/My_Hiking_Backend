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
        if (Schema::hasTable('admin_fee_settings')) {
            return;
        }

        Schema::create('admin_fee_settings', function (Blueprint $table) {
            $table->id();
            $table->decimal('fee_percentage', 5, 2)->comment('Persentase biaya admin dari total pendapatan'); // e.g., 5.00 = 5%
            $table->decimal('fixed_fee', 15, 2)->default(0)->comment('Biaya admin tetap per transaksi'); // e.g., 5000
            $table->string('fee_type', 50)->default('percentage')->comment('Type: percentage, fixed, or both');
            $table->text('description')->nullable();
            $table->string('updated_by')->nullable(); // ID admin yang update
            $table->timestamps();
        });

        // Insert default settings
        \DB::table('admin_fee_settings')->insert([
            'fee_percentage' => 5.00,
            'fixed_fee' => 0,
            'fee_type' => 'percentage',
            'description' => 'Default admin fee setting - 5% of withdrawal amount',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_fee_settings');
    }
};
