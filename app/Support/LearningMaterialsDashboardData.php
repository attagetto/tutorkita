<?php

namespace App\Support;

use App\Models\LearningMaterial;
use App\Models\MaterialBookmark;
use App\Models\MaterialProgress;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;

class LearningMaterialsDashboardData
{
    /** @return array{materials: Collection, recent_materials: Collection, recommended_materials: Collection, material_count: int, completed_material_count: int, material_progress_percent: int, material_has_progress: bool} */
    public static function make(Authenticatable $user): array
    {
        $studentId = (string) $user->getAuthIdentifier();
        $materials = LearningMaterial::query()
            ->where('is_published', true)
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->get();
        $bookmarkIds = MaterialBookmark::query()->where('student_id', $studentId)->pluck('learning_material_id')->all();
        $progress = MaterialProgress::query()
            ->where('student_id', $studentId)
            ->whereIn('learning_material_id', $materials->modelKeys())
            ->get()
            ->keyBy('learning_material_id');
        $completedIds = $progress->filter(fn (MaterialProgress $record): bool => $record->completed_at !== null)->keys()->all();

        $materials->each(function (LearningMaterial $material) use ($bookmarkIds, $progress): void {
            $record = $progress->get($material->id);
            $material->setAttribute('is_bookmarked', in_array($material->id, $bookmarkIds, true));
            $material->setAttribute('is_completed', $record?->completed_at !== null);
        });

        $subjectHistory = MaterialProgress::query()
            ->with('material:id,subject')
            ->where('student_id', $studentId)
            ->whereNotNull('last_opened_at')
            ->get()
            ->filter(fn (MaterialProgress $record): bool => $record->material !== null)
            ->countBy(fn (MaterialProgress $record): string => $record->material->subject);

        $recommended = $materials
            ->whereNotIn('id', $completedIds)
            ->sort(function (LearningMaterial $left, LearningMaterial $right) use ($subjectHistory): int {
                $leftInterest = $subjectHistory->get($left->subject, 0);
                $rightInterest = $subjectHistory->get($right->subject, 0);

                return ($rightInterest <=> $leftInterest)
                    ?: ($right->view_count <=> $left->view_count)
                    ?: ($right->published_at <=> $left->published_at);
            })
            ->take(3)
            ->values();

        $total = $materials->count();
        $completed = count($completedIds);

        return [
            'materials' => $materials->values(),
            'recent_materials' => $materials->take(3)->values(),
            'recommended_materials' => $recommended,
            'material_count' => $total,
            'completed_material_count' => $completed,
            'material_progress_percent' => $total > 0 ? (int) round(($completed / $total) * 100) : 0,
            'material_has_progress' => $completed > 0,
        ];
    }
}
