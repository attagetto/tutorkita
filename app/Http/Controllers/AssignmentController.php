<?php

namespace App\Http\Controllers;

use App\Models\AssignmentSubmission;
use App\Models\StudentAssignment;
use App\Support\LocalUserStore;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AssignmentController extends Controller
{
    public function show(Request $request, int $assignment): JsonResponse
    {
        $task = $this->ownedAssignment($request, $assignment);
        $submission = $task->submission;

        return response()->json(['assignment' => [
            'id' => $task->id,
            'title' => $task->title,
            'subject' => $task->subject,
            'tutor_name' => $task->tutor_name,
            'instructions' => $task->instructions,
            'due_at' => $task->due_at->locale('id')->isoFormat('D MMMM YYYY, HH:mm'),
            'is_overdue' => now()->greaterThan($task->due_at),
            'accepts_text' => $task->accepts_text,
            'accepts_file' => $task->accepts_file,
            'allow_resubmission' => $task->allow_resubmission,
            'max_file_kilobytes' => $task->max_file_kilobytes,
            'editable' => ! $submission?->submitted_at || ($task->allow_resubmission && now()->lessThanOrEqualTo($task->due_at) && $submission->score === null),
            'submission' => $submission ? [
                'answer_text' => $submission->answer_text,
                'submitted_at' => $submission->submitted_at?->locale('id')->isoFormat('D MMM YYYY, HH:mm'),
                'is_late' => $submission->is_late,
                'score' => $submission->score,
                'feedback' => $submission->feedback,
                'graded_at' => $submission->graded_at?->locale('id')->isoFormat('D MMM YYYY, HH:mm'),
                'file_url' => $submission->file_path ? route('dashboard.assignments.submission-file', [$task, $submission]) : null,
            ] : null,
        ]]);
    }

    public function submit(Request $request, int $assignment): JsonResponse
    {
        $task = $this->ownedAssignment($request, $assignment);
        $studentId = (string) $request->user()->getAuthIdentifier();
        $existing = $task->submission;
        abort_if($existing?->score !== null, 409, 'Jawaban yang sudah dinilai tidak dapat diubah.');
        abort_if($existing?->submitted_at && (! $task->allow_resubmission || now()->greaterThan($task->due_at)), 409, 'Pengumpulan ulang sudah ditutup.');

        $rules = [
            'answer_text' => [$task->accepts_text ? 'nullable' : 'prohibited', 'string', 'max:20000'],
            'answer_file' => [$task->accepts_file ? 'nullable' : 'prohibited', 'file', 'mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,txt,csv,png,jpg,jpeg', 'max:'.$task->max_file_kilobytes],
        ];
        $validated = $request->validate($rules, [
            'answer_file.mimes' => 'Gunakan file PDF, Office, teks, CSV, PNG, atau JPG.',
            'answer_file.max' => 'Ukuran file melampaui batas tugas.',
        ]);
        $answerText = isset($validated['answer_text']) ? trim((string) $validated['answer_text']) : null;
        $newUpload = $request->file('answer_file');
        if (($answerText === null || $answerText === '') && ! $newUpload && ! $existing?->file_path) {
            throw ValidationException::withMessages(['answer_text' => 'Tulis jawaban atau pilih file sebelum mengirim.']);
        }

        $newPath = $newUpload?->store('assignment-submissions/'.$studentId.'/'.$task->id, 'local');
        $oldPath = $existing?->file_path;
        try {
            $submission = DB::transaction(function () use ($task, $studentId, $answerText, $newPath, $existing): AssignmentSubmission {
                $submission = $existing ?? new AssignmentSubmission([
                    'student_assignment_id' => $task->id,
                    'student_id' => $studentId,
                ]);
                $submission->answer_text = $answerText;
                if ($newPath !== null) $submission->file_path = $newPath;
                $submission->submitted_at = now();
                $submission->is_late = now()->greaterThan($task->due_at);
                $submission->score = null;
                $submission->feedback = null;
                $submission->graded_at = null;
                $submission->graded_by_tutor_id = null;
                $submission->save();

                return $submission;
            });
        } catch (\Throwable $exception) {
            if ($newPath) Storage::disk('local')->delete($newPath);
            throw $exception;
        }

        if ($newPath && $oldPath) Storage::disk('local')->delete($oldPath);

        return response()->json([
            'message' => $submission->is_late ? 'Jawaban terkirim dan ditandai terlambat.' : 'Jawaban berhasil dikumpulkan.',
            'status' => $submission->is_late ? 'late' : 'submitted',
            'submitted_at' => $submission->submitted_at->locale('id')->isoFormat('D MMMM YYYY, HH:mm'),
        ], 201);
    }

    public function file(Request $request, int $assignment, int $submission)
    {
        $task = $this->ownedAssignment($request, $assignment);
        $answer = AssignmentSubmission::query()
            ->where('student_assignment_id', $task->id)
            ->where('student_id', (string) $request->user()->getAuthIdentifier())
            ->findOrFail($submission);
        abort_unless($answer->file_path && Storage::disk('local')->exists($answer->file_path), 404);

        return Storage::disk('local')->download($answer->file_path, basename($answer->file_path));
    }

    public function create(Request $request, LocalUserStore $users): JsonResponse
    {
        $this->authorizeTutor($request);
        $data = $request->validate([
            'student_id' => ['required', 'string', 'max:80'],
            'title' => ['required', 'string', 'max:180'],
            'subject' => ['required', 'string', 'max:100'],
            'instructions' => ['required', 'string', 'max:20000'],
            'due_at' => ['required', 'date', 'after:now'],
            'accepts_text' => ['required', 'boolean'],
            'accepts_file' => ['required', 'boolean'],
            'allow_resubmission' => ['sometimes', 'boolean'],
            'max_file_kilobytes' => ['sometimes', 'integer', 'min:1', 'max:20480'],
        ]);
        abort_unless($users->findById($data['student_id']) !== null, 404);
        abort_if(! $data['accepts_text'] && ! $data['accepts_file'], 422, 'Tugas harus menerima teks atau file.');

        $task = StudentAssignment::query()->create($data + [
            'tutor_id' => (string) $request->user()->getAuthIdentifier(),
            'tutor_name' => (string) $request->user()->name,
        ]);

        return response()->json(['id' => $task->id], 201);
    }

    public function grade(Request $request, int $assignment, int $submission): JsonResponse
    {
        $this->authorizeTutor($request);
        $tutorId = (string) $request->user()->getAuthIdentifier();
        $task = StudentAssignment::query()->where('tutor_id', $tutorId)->findOrFail($assignment);
        $data = $request->validate([
            'score' => ['required', 'numeric', 'min:0', 'max:100'],
            'feedback' => ['nullable', 'string', 'max:10000'],
        ]);
        $answer = AssignmentSubmission::query()
            ->where('student_assignment_id', $task->id)
            ->findOrFail($submission);
        $answer->update([
            'score' => $data['score'],
            'feedback' => $data['feedback'] ?? null,
            'graded_at' => now(),
            'graded_by_tutor_id' => $tutorId,
        ]);

        return response()->json(['message' => 'Nilai dan feedback berhasil disimpan.']);
    }

    private function ownedAssignment(Request $request, int $id): StudentAssignment
    {
        return StudentAssignment::query()
            ->with(['submission' => fn ($query) => $query->where('student_id', (string) $request->user()->getAuthIdentifier())])
            ->where('student_id', (string) $request->user()->getAuthIdentifier())
            ->findOrFail($id);
    }

    private function authorizeTutor(Request $request): void
    {
        abort_unless(($request->user()->role ?? 'student') === 'tutor', 403, 'Fitur ini khusus akun tutor.');
    }
}
