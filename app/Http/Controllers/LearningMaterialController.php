<?php

namespace App\Http\Controllers;

use App\Models\LearningMaterial;
use App\Models\MaterialBookmark;
use App\Models\MaterialProgress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LearningMaterialController extends Controller
{
    public function show(Request $request, int $learningMaterial): JsonResponse
    {
        $material = $this->publishedMaterial($learningMaterial);
        $material->increment('view_count');
        $material->refresh();
        $studentId = (string) $request->user()->getAuthIdentifier();
        $progress = MaterialProgress::query()->firstOrNew([
            'learning_material_id' => $material->id,
            'student_id' => $studentId,
        ]);
        $progress->last_opened_at = now();
        $progress->save();

        $hasFile = is_string($material->file_path)
            && $material->file_path !== ''
            && Storage::disk('local')->exists($material->file_path);
        $videoUrl = $this->validWebUrl($material->video_url) ? $material->video_url : null;

        return response()->json(['material' => [
            'id' => $material->id,
            'title' => $material->title,
            'subject' => $material->subject,
            'topic' => $material->topic,
            'grade_level' => $material->grade_level,
            'tutor_name' => $material->tutor_name,
            'material_type' => $material->material_type,
            'description' => $material->description,
            'page_count' => $material->page_count,
            'duration_seconds' => $material->duration_seconds,
            'view_count' => $material->view_count,
            'completed' => $progress->completed_at !== null,
            'bookmarked' => MaterialBookmark::query()
                ->where('learning_material_id', $material->id)
                ->where('student_id', $studentId)
                ->exists(),
            'file_url' => $hasFile ? route('dashboard.materials.file', $material) : null,
            'video_url' => $videoUrl,
        ]]);
    }

    public function toggleBookmark(Request $request, int $learningMaterial): JsonResponse
    {
        $material = $this->publishedMaterial($learningMaterial);
        $studentId = (string) $request->user()->getAuthIdentifier();
        $bookmark = MaterialBookmark::query()
            ->where('learning_material_id', $material->id)
            ->where('student_id', $studentId)
            ->first();

        if ($bookmark) {
            $bookmark->delete();
            $bookmarked = false;
        } else {
            MaterialBookmark::query()->create([
                'learning_material_id' => $material->id,
                'student_id' => $studentId,
            ]);
            $bookmarked = true;
        }

        return response()->json([
            'bookmarked' => $bookmarked,
            'message' => $bookmarked ? 'Materi disimpan ke bookmark.' : 'Materi dihapus dari bookmark.',
        ]);
    }

    public function complete(Request $request, int $learningMaterial): JsonResponse
    {
        $material = $this->publishedMaterial($learningMaterial);
        $studentId = (string) $request->user()->getAuthIdentifier();
        $progress = MaterialProgress::query()->firstOrNew([
            'learning_material_id' => $material->id,
            'student_id' => $studentId,
        ]);
        $progress->last_opened_at ??= now();
        $progress->completed_at ??= now();
        $progress->save();

        return response()->json([
            'completed' => true,
            'message' => 'Materi ditandai selesai. Progres belajarmu sudah diperbarui.',
        ]);
    }

    public function file(int $learningMaterial)
    {
        $material = $this->publishedMaterial($learningMaterial);

        abort_unless(is_string($material->file_path) && $material->file_path !== '', 404);
        abort_unless(Storage::disk('local')->exists($material->file_path), 404);

        return Storage::disk('local')->download($material->file_path, basename($material->file_path));
    }

    private function publishedMaterial(int $id): LearningMaterial
    {
        return LearningMaterial::query()
            ->where('is_published', true)
            ->findOrFail($id);
    }

    private function validWebUrl(?string $url): bool
    {
        return is_string($url)
            && filter_var($url, FILTER_VALIDATE_URL)
            && in_array(parse_url($url, PHP_URL_SCHEME), ['http', 'https'], true);
    }
}
