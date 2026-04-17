<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OfflineTrackSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_sync_creates_record_for_valid_payload(): void
    {
        $user = $this->createReadyUser();
        $orderId = $this->createOrderForUser($user, 'Sedang Mendaki');

        Sanctum::actingAs($user);

        $payload = $this->validPayload(['client_cache_id' => 'cache_sync_create_1']);
        $response = $this->postJson("/api/orders/{$orderId}/offline-track-sync", $payload);

        $response
            ->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.client_cache_id', 'cache_sync_create_1')
            ->assertJsonPath('data.sync_status', 'synced')
            ->assertJsonPath('data.is_duplicate', false);

        $this->assertDatabaseHas('offline_track_syncs', [
            'order_id' => $orderId,
            'user_id' => $user->id,
            'client_cache_id' => 'cache_sync_create_1',
            'sync_status' => 'synced',
        ]);
    }

    public function test_sync_returns_idempotent_response_for_duplicate_cache_id(): void
    {
        $user = $this->createReadyUser();
        $orderId = $this->createOrderForUser($user, 'Sedang Mendaki');

        Sanctum::actingAs($user);

        $payload = $this->validPayload(['client_cache_id' => 'cache_sync_dup_1']);
        $first = $this->postJson("/api/orders/{$orderId}/offline-track-sync", $payload);
        $second = $this->postJson("/api/orders/{$orderId}/offline-track-sync", $payload);

        $first->assertStatus(201);
        $second
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.client_cache_id', 'cache_sync_dup_1')
            ->assertJsonPath('data.sync_status', 'duplicate')
            ->assertJsonPath('data.is_duplicate', true);

        $this->assertSame(1, DB::table('offline_track_syncs')->count());
    }

    public function test_sync_rejects_if_order_not_owned_by_authenticated_user(): void
    {
        $owner = $this->createReadyUser();
        $otherUser = $this->createReadyUser();
        $orderId = $this->createOrderForUser($owner, 'Sedang Mendaki');

        Sanctum::actingAs($otherUser);

        $response = $this->postJson(
            "/api/orders/{$orderId}/offline-track-sync",
            $this->validPayload(['client_cache_id' => 'cache_sync_forbidden_1'])
        );

        $response
            ->assertStatus(403)
            ->assertJsonPath('success', false)
            ->assertJsonPath('code', 'FORBIDDEN_ORDER_ACCESS');
    }

    public function test_sync_rejects_if_order_status_not_sedang_mendaki(): void
    {
        $user = $this->createReadyUser();
        $orderId = $this->createOrderForUser($user, 'Booking');

        Sanctum::actingAs($user);

        $response = $this->postJson(
            "/api/orders/{$orderId}/offline-track-sync",
            $this->validPayload(['client_cache_id' => 'cache_sync_status_1'])
        );

        $response
            ->assertStatus(409)
            ->assertJsonPath('success', false)
            ->assertJsonPath('code', 'ORDER_STATUS_NOT_SYNCABLE');
    }

    public function test_sync_validates_required_payload(): void
    {
        $user = $this->createReadyUser();
        $orderId = $this->createOrderForUser($user, 'Sedang Mendaki');

        Sanctum::actingAs($user);

        $payload = $this->validPayload();
        unset($payload['gpx_content']);

        $response = $this->postJson("/api/orders/{$orderId}/offline-track-sync", $payload);

        $response->assertStatus(422);
        $this->assertSame(0, DB::table('offline_track_syncs')->count());
    }

    public function test_sync_rejects_payload_when_gpx_too_large(): void
    {
        $user = $this->createReadyUser();
        $orderId = $this->createOrderForUser($user, 'Sedang Mendaki');

        Sanctum::actingAs($user);

        $response = $this->postJson(
            "/api/orders/{$orderId}/offline-track-sync",
            $this->validPayload([
                'client_cache_id' => 'cache_sync_too_large_1',
                'gpx_content' => str_repeat('x', 1000001),
            ])
        );

        $response
            ->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('code', 'GPX_TOO_LARGE');

        $this->assertSame(0, DB::table('offline_track_syncs')->count());
    }

    public function test_list_syncs_returns_owned_order_records(): void
    {
        $user = $this->createReadyUser();
        $orderId = $this->createOrderForUser($user, 'Sedang Mendaki');

        Sanctum::actingAs($user);

        $this->postJson(
            "/api/orders/{$orderId}/offline-track-sync",
            $this->validPayload(['client_cache_id' => 'cache_sync_list_1'])
        )->assertStatus(201);

        $this->postJson(
            "/api/orders/{$orderId}/offline-track-sync",
            $this->validPayload(['client_cache_id' => 'cache_sync_list_2'])
        )->assertStatus(201);

        $response = $this->getJson("/api/orders/{$orderId}/offline-track-syncs?limit=1");

        $response
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('meta.order_id', $orderId)
            ->assertJsonPath('meta.limit', 1)
            ->assertJsonPath('meta.count', 1)
            ->assertJsonPath('data.0.client_cache_id', 'cache_sync_list_2');

        $response->assertJsonMissingPath('data.0.gpx_content');

        $responseWithGpx = $this->getJson("/api/orders/{$orderId}/offline-track-syncs?with_gpx=1&limit=1");
        $responseWithGpx
            ->assertStatus(200)
            ->assertJsonPath('meta.with_gpx', true);
    }

    public function test_list_syncs_rejects_non_owner(): void
    {
        $owner = $this->createReadyUser();
        $otherUser = $this->createReadyUser();
        $orderId = $this->createOrderForUser($owner, 'Sedang Mendaki');

        Sanctum::actingAs($otherUser);

        $response = $this->getJson("/api/orders/{$orderId}/offline-track-syncs");

        $response
            ->assertStatus(403)
            ->assertJsonPath('success', false)
            ->assertJsonPath('code', 'FORBIDDEN_ORDER_ACCESS');
    }

    private function createReadyUser(): User
    {
        return User::factory()->create([
            'level' => 2,
            'address' => 'Jl Test No. 1',
            'nik' => random_int(1000000000000000, 9999999999999999),
            'phone' => random_int(810000000000, 899999999999),
            'emergency_phone' => random_int(810000000000, 899999999999),
            'date_of_birth' => '1990-01-01',
        ]);
    }

    private function createOrderForUser(User $user, string $status): string
    {
        $now = now();

        DB::table('reg_provinces')->insert([
            'id' => '33',
            'name' => 'Jawa Tengah',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('reg_regencies')->insert([
            'id' => '3301',
            'province_id' => '33',
            'name' => 'Kabupaten Test',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('reg_districts')->insert([
            'id' => '330101',
            'regency_id' => '3301',
            'name' => 'Kecamatan Test',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('reg_villages')->insert([
            'id' => '3301010001',
            'district_id' => '330101',
            'name' => 'Desa Test',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $mountainId = DB::table('mountains')->insertGetId([
            'province_id' => '33',
            'regency_id' => '3301',
            'district_id' => '330101',
            'village_id' => '3301010001',
            'nama' => 'Gunung Test',
            'deskripsi' => 'Gunung untuk testing endpoint sync offline.',
            'ketinggian' => 2000,
            'latitude' => -7.1234567,
            'longitude' => 110.1234567,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $routeId = DB::table('routes')->insertGetId([
            'nama' => 'Jalur Test',
            'id_gunung' => $mountainId,
            'user_id' => null,
            'province_id' => '33',
            'regency_id' => '3301',
            'district_id' => '330101',
            'village_id' => '3301010001',
            'jarak' => 7000,
            'deskripsi' => 'Jalur untuk testing endpoint sync offline.',
            'map_basecamp' => null,
            'gambar_jalur' => null,
            'biaya' => 20000,
            'latitude' => -7.1234567,
            'longitude' => 110.1234567,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $orderId = (string) random_int(1000000000, 9999999999);

        DB::table('orders')->insert([
            'id' => $orderId,
            'id_gunung' => $mountainId,
            'id_jalur' => $routeId,
            'id_user' => $user->id,
            'tanggal_naik' => now()->toDateString(),
            'tanggal_turun' => now()->addDay()->toDateString(),
            'total_harga_tiket' => 150000,
            'status' => $status,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return $orderId;
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'client_cache_id' => 'cache_default_1',
            'source' => 'mobile_offline_tracking',
            'cached_at' => now()->subMinutes(5)->toIso8601String(),
            'point_count' => 12,
            'distance_meters' => 1450.6,
            'duration_seconds' => 1280,
            'gpx_content' => '<?xml version="1.0" encoding="UTF-8"?><gpx><trk><trkseg><trkpt lat="-7.1" lon="110.1"/><trkpt lat="-7.2" lon="110.2"/></trkseg></trk></gpx>',
        ], $overrides);
    }
}
