<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('transactions', 'payment_code')) {
                $table->string('payment_code')->nullable()->after('payment_status');
            }

            if (!Schema::hasColumn('transactions', 'payment_code_label')) {
                $table->string('payment_code_label')->nullable()->after('payment_code');
            }

            if (!Schema::hasColumn('transactions', 'payment_instruction')) {
                $table->text('payment_instruction')->nullable()->after('payment_code_label');
            }

            if (!Schema::hasColumn('transactions', 'deeplink_url')) {
                $table->text('deeplink_url')->nullable()->after('payment_instruction');
            }
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (Schema::hasColumn('transactions', 'deeplink_url')) {
                $table->dropColumn('deeplink_url');
            }

            if (Schema::hasColumn('transactions', 'payment_instruction')) {
                $table->dropColumn('payment_instruction');
            }

            if (Schema::hasColumn('transactions', 'payment_code_label')) {
                $table->dropColumn('payment_code_label');
            }

            if (Schema::hasColumn('transactions', 'payment_code')) {
                $table->dropColumn('payment_code');
            }
        });
    }
};
