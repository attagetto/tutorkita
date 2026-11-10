<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LearningSession extends Model
{
    protected $fillable = [
        'student_id',
        'tutor_name',
        'tutor_university',
        'tutor_major',
        'subject',
        'topic',
        'grade_level',
        'starts_at',
        'duration_minutes',
        'meeting_platform',
        'meeting_url',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'immutable_datetime',
            'duration_minutes' => 'integer',
        ];
    }
}
