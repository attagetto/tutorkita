<?php

namespace App\Support;

use App\Models\StudentAssignment;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;

class AssignmentDashboardData
{
    /** @return array<string, mixed> */
    public static function make(Authenticatable $user): array
    {
        $studentId = (string) $user->getAuthIdentifier();
        $assignments = StudentAssignment::query()
            ->with(['submission' => fn ($query) => $query->where('student_id', $studentId)])
            ->where('student_id', $studentId)
            ->orderBy('due_at')
            ->get();

        $graded = $assignments->filter(fn (StudentAssignment $assignment): bool => $assignment->submission?->score !== null);
        $scores = $graded->map(fn (StudentAssignment $assignment): float => (float) $assignment->submission->score);
        $submittedCount = $assignments->filter(fn (StudentAssignment $assignment): bool => $assignment->submission?->submitted_at !== null)->count();
        $average = $scores->isNotEmpty() ? round($scores->avg()) : null;

        $assignments->each(function (StudentAssignment $assignment): void {
            $submission = $assignment->submission;
            $assignment->setAttribute('display_status', match (true) {
                $submission?->score !== null => 'graded',
                $submission?->submitted_at !== null && $submission->is_late => 'late',
                $submission?->submitted_at !== null => 'submitted',
                now()->greaterThan($assignment->due_at) => 'late',
                default => 'active',
            });
        });

        return [
            'student_assignments' => $assignments,
            'assignment_count' => $assignments->count(),
            'submitted_assignment_count' => $submittedCount,
            'assignment_completion_percent' => $assignments->isNotEmpty() ? (int) round($submittedCount / $assignments->count() * 100) : 0,
            'assignment_average_score' => $average,
            'assignment_grade_category' => match (true) {
                $average === null => 'Belum ada nilai',
                $average >= 85 => 'Sangat Baik',
                $average >= 70 => 'Baik',
                $average >= 55 => 'Cukup',
                default => 'Perlu ditingkatkan',
            },
            'recent_assignment_grades' => $graded->sortByDesc(fn (StudentAssignment $assignment) => $assignment->submission->graded_at?->getTimestamp() ?? 0)->take(3)->values(),
        ];
    }
}
