<?php

namespace App\Http\Controllers;

use App\Models\LearningMaterial;
use App\Models\MaterialBookmark;
use App\Models\MaterialProgress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class LearningMaterialController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $this->authorizeTutor($request);
        $data = $this->validatedMaterial($request);
        $this->requireMaterialAsset($data, $request, null);
        $filePath = $request->file('file')?->store('learning-materials', 'local');
        unset($data['file']);

        try {
            $material = DB::transaction(fn (): LearningMaterial => LearningMaterial::query()->create([
                ...$data,
                'slug' => $this->uniqueSlug($data['title']),
                'tutor_name' => (string) $request->user()->name,
                'created_by' => (string) $request->user()->getAuthIdentifier(),
                'file_path' => $filePath,
                'illustration' => $this->illustrationFor($data['material_type']),
                'published_at' => $data['is_published'] ? now() : null,
            ]));
        } catch (\Throwable $exception) {
            if ($filePath) Storage::disk('local')->delete($filePath);
            throw $exception;
        }

        return response()->json(['id' => $material->id, 'message' => 'Materi pembelajaran berhasil ditambahkan.'], 201);
    }

    public function update(Request $request, int $learningMaterial): JsonResponse
    {
        $this->authorizeTutor($request);
        $material = $this->ownedMaterial($request, $learningMaterial);
        $data = $this->validatedMaterial($request);
        $this->requireMaterialAsset($data, $request, $material);
        $newPath = $request->file('file')?->store('learning-materials', 'local');
        unset($data['file']);
        $oldPath = $material->file_path;
        $changes = [
            ...$data,
            'tutor_name' => (string) $request->user()->name,
            'illustration' => $this->illustrationFor($data['material_type']),
            'published_at' => $data['is_published'] ? ($material->published_at ?? now()) : null,
        ];
        if ($newPath) $changes['file_path'] = $newPath;
        elseif ($data['material_type'] === 'video') $changes['file_path'] = null;

        try {
            DB::transaction(fn () => $material->update($changes));
        } catch (\Throwable $exception) {
            if ($newPath) Storage::disk('local')->delete($newPath);
            throw $exception;
        }
        if ($oldPath && $oldPath !== $material->file_path) Storage::disk('local')->delete($oldPath);

        return response()->json(['message' => 'Materi pembelajaran berhasil diperbarui.']);
    }

    public function destroy(Request $request, int $learningMaterial): JsonResponse
    {
        $this->authorizeTutor($request);
        $material = $this->ownedMaterial($request, $learningMaterial);
        DB::transaction(fn () => $material->delete());

        return response()->json(['message' => 'Materi diarsipkan dari katalog. Progres dan bookmark tetap disimpan.']);
    }

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

    /** @return array<string, mixed> */
    private function validatedMaterial(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'subject' => ['required', 'string', 'max:100', Rule::exists('subjects', 'name')],
            'topic' => ['nullable', 'string', 'max:120'],
            'grade_level' => ['required', Rule::in(['SD', 'SMP', 'SMA/SMK', 'Perguruan Tinggi'])],
            'material_type' => ['required', Rule::in(['module_pdf', 'material_pdf', 'video', 'exercise', 'summary'])],
            'description' => ['required', 'string', 'max:5000'],
            'video_url' => ['nullable', 'url', 'max:2048'],
            'is_published' => ['required', 'boolean'],
            'file' => ['nullable', 'file', 'mimes:pdf', 'mimetypes:application/pdf', 'max:20480'],
        ]);

        if ($data['material_type'] === 'video') {
            if (empty($data['video_url']) || ! $this->validWebUrl($data['video_url'])) {
                throw ValidationException::withMessages(['video_url' => 'Masukkan tautan video HTTP atau HTTPS yang valid.']);
            }
        } elseif (! empty($data['video_url']) && ! $this->validWebUrl($data['video_url'])) {
            throw ValidationException::withMessages(['video_url' => 'Tautan harus menggunakan HTTP atau HTTPS.']);
        }

        return $data;
    }

    private function requireMaterialAsset(array $data, Request $request, ?LearningMaterial $material): void
    {
        if (in_array($data['material_type'], ['module_pdf', 'material_pdf'], true)
            && ! $request->hasFile('file')
            && ! $material?->file_path) {
            throw ValidationException::withMessages(['file' => 'Unggah file PDF untuk jenis materi ini.']);
        }
    }

    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'materi';
        $slug = $base;
        $suffix = 2;
        while (LearningMaterial::withTrashed()->where('slug', $slug)->exists()) $slug = $base.'-'.$suffix++;

        return $slug;
    }

    private function illustrationFor(string $type): string
    {
        return match ($type) {
            'video' => 'video',
            'exercise' => 'chart',
            'summary' => 'book',
            default => 'document',
        };
    }

    private function ownedMaterial(Request $request, int $id): LearningMaterial
    {
        return LearningMaterial::query()
            ->where('created_by', (string) $request->user()->getAuthIdentifier())
            ->findOrFail($id);
    }

    private function authorizeTutor(Request $request): void
    {
        abort_unless(($request->user()->role ?? 'student') === 'tutor', 403, 'Pengelolaan materi hanya tersedia untuk akun tutor.');
    }
}
