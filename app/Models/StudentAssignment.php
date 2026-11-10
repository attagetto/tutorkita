<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class StudentAssignment extends Model
{
    protected $fillable = [
        'student_id', 'tutor_id', 'tutor_name', 'title', 'subject', 'instructions', 'due_at',
        'accepts_text', 'accepts_file', 'allow_resubmission', 'max_file_kilobytes',
    ];

    protected function casts(): array
    {
        return [
            'due_at' => 'immutable_datetime',
            'accepts_text' => 'boolean',
            'accepts_file' => 'boolean',
            'allow_resubmission' => 'boolean',
            'max_file_kilobytes' => 'integer',
        ];
    }

    public function submission(): HasOne
    {
        return $this->hasOne(AssignmentSubmission::class);
    }
}
