<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement(
            "ALTER TABLE orders MODIFY status ENUM('Booking', 'Cancel Requested', 'Cancelled', 'Sedang Mendaki', 'Selesai', 'Expired') NOT NULL DEFAULT 'Booking'"
        );
    }

    public function down(): void
    {
        DB::statement("UPDATE orders SET status = 'Booking' WHERE status IN ('Cancel Requested', 'Cancelled')");
        DB::statement(
            "ALTER TABLE orders MODIFY status ENUM('Booking', 'Sedang Mendaki', 'Selesai', 'Expired') NOT NULL DEFAULT 'Booking'"
        );
    }
};
