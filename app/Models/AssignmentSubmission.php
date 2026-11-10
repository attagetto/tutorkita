<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssignmentSubmission extends Model
{
    protected $fillable = [
        'student_assignment_id', 'student_id', 'answer_text', 'file_path', 'submitted_at',
        'is_late', 'score', 'feedback', 'graded_at', 'graded_by_tutor_id',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'immutable_datetime',
            'is_late' => 'boolean',
            'score' => 'decimal:2',
            'graded_at' => 'immutable_datetime',
        ];
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(StudentAssignment::class, 'student_assignment_id');
    }
}
