<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserExperience extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'jumlah_pendakian',
        'jumlah_summit',
        'questionnaire_answers',
        'weighted_score',
        'weighted_tier',
        'onboarding_completed_at',
    ];

    protected $casts = [
        'questionnaire_answers' => 'array',
        'weighted_score' => 'integer',
        'onboarding_completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
