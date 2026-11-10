<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class SubjectsController extends Controller
{
    public function catalog(Request $request): JsonResponse
    {
        $this->ensureStudent($request);
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'level' => ['nullable', 'string', Rule::exists('grade_levels', 'name')],
            'category' => ['nullable', 'string', 'max:60'],
            'scope' => ['nullable', Rule::in(['all', 'mine'])],
        ]);
        $studentId = (string) $request->user()->getAuthIdentifier();
        $selectedSubjectIds = DB::table('student_subjects')
            ->where('student_id', $studentId)
            ->select('subject_id');
        $query = Subject::query()
            ->when(($filters['scope'] ?? 'all') === 'mine', fn ($subjects) => $subjects->whereIn('id', $selectedSubjectIds))
            ->when(($filters['search'] ?? '') !== '', function ($subjects) use ($filters): void {
                $term = '%'.addcslashes(trim($filters['search']), '%_\\').'%';
                $subjects->where(fn ($subject) => $subject->where('name', 'like', $term)->orWhere('description', 'like', $term));
            })
            ->when(($filters['category'] ?? '') !== '', fn ($subjects) => $subjects->where('category', $filters['category']))
            ->when(($filters['level'] ?? '') !== '', fn ($subjects) => $subjects->whereHas(
                'gradeLevels', fn ($levels) => $levels->where('grade_levels.name', $filters['level']),
            ));

        return response()->json([
            'ids' => $query->orderBy('name')->pluck('id')->map(fn ($id): int => (int) $id)->all(),
        ]);
    }

    public function select(Request $request, Subject $subject): RedirectResponse|JsonResponse
    {
        $this->ensureStudent($request);
        $validated = $request->validate(['grade_level_id' => ['required', 'integer', 'exists:grade_levels,id']]);
        $this->ensureSubjectLevel($subject, (int) $validated['grade_level_id']);
        $studentId = (string) $request->user()->getAuthIdentifier();
        $alreadySelected = DB::table('student_subjects')->where('student_id', $studentId)->where('subject_id', $subject->id)->exists();
        if (! $alreadySelected) {
            DB::table('student_subjects')->insert([
                'student_id' => $studentId,
                'subject_id' => $subject->id,
                'grade_level_id' => $validated['grade_level_id'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            DB::table('student_subjects')->where('student_id', $studentId)->where('subject_id', $subject->id)
                ->update(['grade_level_id' => $validated['grade_level_id'], 'updated_at' => now()]);
        }

        $level = DB::table('grade_levels')->where('id', $validated['grade_level_id'])->value('name');
        $message = $alreadySelected ? 'Jenjang '.$subject->name.' diperbarui menjadi '.$level.'.' : $subject->name.' · '.$level.' ditambahkan ke daftar belajarmu.';

        return $request->expectsJson()
            ? response()->json(['message' => $message, 'selected' => true])
            : redirect('/dashboard?materials=subjects#materials')->with('status', $message);
    }

    public function update(Request $request, Subject $subject): RedirectResponse|JsonResponse
    {
        $this->ensureStudent($request);
        $studentId = (string) $request->user()->getAuthIdentifier();
        $selection = DB::table('student_subjects')->where('student_id', $studentId)->where('subject_id', $subject->id)->first();
        abort_unless($selection, 404, 'Mata pelajaran tidak ada di daftar pilihanmu.');

        $validated = $request->validate([
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'grade_level_id' => ['required', 'integer', 'exists:grade_levels,id'],
        ]);
        $newSubject = Subject::query()->findOrFail($validated['subject_id']);
        $this->ensureSubjectLevel($newSubject, (int) $validated['grade_level_id']);
        $duplicate = DB::table('student_subjects')->where('student_id', $studentId)
            ->where('subject_id', $newSubject->id)->where('id', '<>', $selection->id)->exists();
        if ($duplicate) {
            throw ValidationException::withMessages(['subject_id' => 'Mata pelajaran tersebut sudah ada di daftar belajarmu.']);
        }

        DB::transaction(fn () => DB::table('student_subjects')->where('id', $selection->id)->update([
            'subject_id' => $newSubject->id,
            'grade_level_id' => $validated['grade_level_id'],
            'updated_at' => now(),
        ]));
        $level = DB::table('grade_levels')->where('id', $validated['grade_level_id'])->value('name');
        $message = 'Pilihan diperbarui: '.$newSubject->name.' · '.$level.'.';

        return $request->expectsJson()
            ? response()->json(['message' => $message, 'selected' => true, 'subject_id' => $newSubject->id, 'grade_level_id' => (int) $validated['grade_level_id']])
            : redirect('/dashboard?materials=subjects#materials')->with('status', $message);
    }

    public function destroy(Request $request, Subject $subject): RedirectResponse|JsonResponse
    {
        $this->ensureStudent($request);
        $studentId = (string) $request->user()->getAuthIdentifier();
        $selection = DB::table('student_subjects')
            ->where('student_id', $studentId)
            ->where('subject_id', $subject->id);

        $removed = DB::transaction(fn (): int => $selection->delete());
        abort_unless($removed === 1, 404, 'Mata pelajaran tidak ada di daftar pilihanmu.');

        $message = 'Mata pelajaran berhasil dihapus dari daftar belajarmu.';

        return $request->expectsJson()
            ? response()->json(['message' => $message, 'selected' => false])
            : redirect('/dashboard?materials=subjects#materials')->with('status', $message);
    }

    private function ensureStudent(Request $request): void
    {
        abort_unless(($request->user()->role ?? null) === 'student', 403, 'Fitur ini khusus untuk siswa.');
    }

    private function ensureSubjectLevel(Subject $subject, int $gradeLevelId): void
    {
        abort_unless($subject->gradeLevels()->where('grade_levels.id', $gradeLevelId)->exists(), 422, 'Jenjang tersebut tidak tersedia untuk mata pelajaran ini.');
    }
}
