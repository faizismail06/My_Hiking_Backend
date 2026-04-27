<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DssPendingSubmission extends Model
{
    use HasFactory;

    protected $table = 'dss_pending_submissions';

    protected $fillable = [
        'route_id',
        'submitted_by',
        'panorama_score_pending',
        'fasilitas_score_pending',
        'safety_score_pending',
        'crowd_level_pending',
        'popularity_score_pending',
        'status',
        'rejection_reason',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'panorama_score_pending' => 'float',
        'fasilitas_score_pending' => 'float',
        'safety_score_pending' => 'float',
        'crowd_level_pending' => 'float',
        'popularity_score_pending' => 'float',
        'reviewed_at' => 'datetime',
    ];

    public function route()
    {
        return $this->belongsTo(TrailWeb::class, 'route_id');
    }

    public function submitter()
    {
        return $this->belongsTo(User::class, 'submitted_by', 'id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by', 'id');
    }
}
