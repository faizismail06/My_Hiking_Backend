<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class TrailWeb extends Model
{
    use HasFactory;

    protected $table = 'routes';

    protected $fillable = [
        'id_gunung',
        'nama',
        'user_id',
        'province_id',
        'regency_id',
        'district_id',
        'village_id',
        'jarak',
        'elevasi',
        'durasi',
        'tingkat_kesulitan',
        'deskripsi',
        'map_basecamp',
        'biaya',
        'daily_hiker_limit',
        'is_refund_allowed',
        'gambar_jalur',
        'latitude',
        'longitude',
        'route_points',
        'route_source',
        // ── DSS Criteria (TOPSIS) ────────────────────────────────────────
        'panorama_score',
        'fasilitas_score',
        'safety_score',
        'crowd_level',
        'popularity_score',
        'dss_status',
    ];

    protected $casts = [
        'route_points'     => 'array',
        'jarak'            => 'float',
        'daily_hiker_limit'=> 'integer',
        'is_refund_allowed'=> 'boolean',
        // DSS criteria stored as float so upstream services always get numeric values
        'panorama_score'   => 'float',
        'fasilitas_score'  => 'float',
        'safety_score'     => 'float',
        'crowd_level'      => 'float',
        'popularity_score' => 'float',
        'dss_status'       => 'string',
    ];

    // Relasi dengan model Mountain
    public function mountain()
    {
        return $this->belongsTo(MountainWeb::class, 'id_gunung', 'id');
    }

    // Alias for backward compatibility
    public function gunung()
    {
        return $this->mountain();
    }

    // Relasi dengan model User (trail guard)
    public function trailGuard()
    {
        return $this->belongsTo(UserWeb::class, 'user_id', 'id');
    }

    // Alias for backward compatibility
    public function penjaga()
    {
        return $this->trailGuard();
    }

    public function province()
    {
        return $this->belongsTo(ProvinceWeb::class, 'province_id');
    }

    public function regency()
    {
        return $this->belongsTo(RegencyWeb::class, 'regency_id');
    }
    public function district()
    {
        return $this->belongsTo(DistrictWeb::class, 'district_id');
    }
    public function village()
    {
        return $this->belongsTo(VillageWeb::class, 'village_id');
    }
    public function orders()
    {
        return $this->hasMany(OrderWeb::class, 'id_jalur', 'id')->distinct();
    }

    public function posts()
    {
        return $this->hasMany(TrailPost::class, 'trail_id')->orderBy('sequence');
    }



    // Alias for backward compatibility
    public function pesanan()
    {
        return $this->orders();
    }

    // ─────────────────────────────────────────────────────────────────────
    // DSS Helpers
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Ensure no DSS criterion is NULL before saving.
     *
     * Defaults:
     *   panorama_score   → 3   (neutral)
     *   fasilitas_score  → 3   (neutral)
     *   safety_score     → 3   (neutral)
     *   crowd_level      → 3   (neutral)
     *   popularity_score → 0   (no data yet)
     *
     * Call this before trail->save() / trail->update().
     */
    public function applyDssDefaults(): void
    {
        $defaults = [
            'panorama_score'   => 3.0,
            'fasilitas_score'  => 3.0,
            'safety_score'     => 3.0,
            'crowd_level'      => 3.0,
            'popularity_score' => 0.0,
        ];

        foreach ($defaults as $field => $default) {
            if (is_null($this->{$field})) {
                $this->{$field} = $default;
            }
        }
    }

    /**
     * Detect degenerate DSS columns (all trails have the same value).
     *
     * When a column is degenerate, the TOPSIS normalisation produces
     * a zero-variance vector which is already handled server-side, but
     * it means the data quality is poor and an admin should be notified.
     *
     * Logs a warning per offending criterion.
     * Safe to call after every bulk update.
     */
    public static function checkDssConsistency(): void
    {
        $criteria = [
            'panorama_score',
            'fasilitas_score',
            'safety_score',
            'crowd_level',
            'popularity_score',
        ];

        foreach ($criteria as $column) {
            $distinct = self::query()
                ->distinct()
                ->whereNotNull($column)
                ->pluck($column);

            if ($distinct->count() <= 1) {
                Log::warning(
                    "[DSS Consistency] Column '{$column}' is degenerate: " .
                    "all trails share the same value ({$distinct->first()}). " .
                    "TOPSIS will zero-out this criterion."
                );
            }
        }
    }
}
