<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TutorProfile extends Model
{
    protected $fillable = [
        'user_id', 'education_level', 'institution', 'program', 'graduation_year', 'bio',
        'experience', 'additional_info', 'avatar_path', 'availability', 'is_active',
    ];

    protected function casts(): array
    {
        return ['availability' => 'array', 'is_active' => 'boolean'];
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'tutor_profile_subject');
    }
}
