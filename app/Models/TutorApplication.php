<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TutorApplication extends Model
{
    protected $fillable = [
        'user_id', 'applicant_name', 'education_level', 'institution', 'program', 'graduation_year',
        'bio', 'experience', 'additional_info', 'avatar_path', 'status', 'rejection_reason',
        'reviewed_by', 'submitted_at', 'reviewed_at', 'approved_at',
    ];

    protected function casts(): array
    {
        return ['submitted_at' => 'datetime', 'reviewed_at' => 'datetime', 'approved_at' => 'datetime'];
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'tutor_application_subject');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(TutorApplicationReview::class);
    }
}
