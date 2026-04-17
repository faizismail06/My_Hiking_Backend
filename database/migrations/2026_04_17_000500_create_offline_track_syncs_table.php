<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offline_track_syncs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('user_id');
            $table->string('client_cache_id', 191);
            $table->string('source', 80)->default('mobile_offline_tracking');
            $table->timestamp('cached_at')->nullable();
            $table->unsignedInteger('point_count');
            $table->double('distance_meters')->default(0);
            $table->unsignedInteger('duration_seconds')->default(0);
            $table->longText('gpx_content');
            $table->enum('sync_status', ['synced'])->default('synced');
            $table->timestamp('synced_at');
            $table->timestamps();

            $table->unique(['order_id', 'client_cache_id'], 'offline_track_syncs_order_client_unique');
            $table->index(['user_id', 'created_at'], 'offline_track_syncs_user_created_index');

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offline_track_syncs');
    }
};
