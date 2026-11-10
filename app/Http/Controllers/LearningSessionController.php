<?php

namespace App\Http\Controllers;

use App\Models\LearningSession;
use App\Support\DashboardMockData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class LearningSessionController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tutor_name' => ['required', 'string', 'max:120'],
            'subject' => ['required', 'string', 'max:100'],
            'topic' => ['required', 'string', 'max:180'],
            'grade_level' => ['required', Rule::in(['SD', 'SMP', 'SMA/SMK'])],
            'starts_at' => ['required', 'date_format:Y-m-d\\TH:i', 'after:now'],
        ]);

        $tutor = collect(DashboardMockData::make()['catalog_tutors'])
            ->firstWhere('name', $validated['tutor_name']);

        if (! $tutor) {
            return response()->json(['message' => 'Tutor yang dipilih tidak tersedia.'], 422);
        }

        if (! $tutor['available']) {
            return response()->json(['message' => 'Tutor ini sedang tidak tersedia. Silakan pilih tutor lain.'], 422);
        }

        if (! in_array($validated['subject'], $tutor['subjects'], true)) {
            return response()->json(['message' => 'Mata pelajaran tidak sesuai dengan tutor yang dipilih.'], 422);
        }

        if (! in_array($validated['grade_level'], $tutor['levels'], true)) {
            return response()->json(['message' => 'Jenjang tersebut belum tersedia untuk tutor yang dipilih.'], 422);
        }

        $session = LearningSession::create([
            'student_id' => (string) $request->user()->getAuthIdentifier(),
            'tutor_name' => $tutor['name'],
            'tutor_university' => $tutor['university'],
            'tutor_major' => $tutor['major'],
            'subject' => $validated['subject'],
            'topic' => $validated['topic'],
            'grade_level' => $validated['grade_level'],
            'starts_at' => Carbon::createFromFormat('Y-m-d\\TH:i', $validated['starts_at']),
            'duration_minutes' => 60,
            'meeting_platform' => 'Google Meet',
            'meeting_url' => null,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Pengajuan sesi berhasil disimpan dan menunggu konfirmasi tutor.',
            'session' => $this->serialize($session),
        ], 201);
    }

    public function show(Request $request, int $learningSession): JsonResponse
    {
        $session = LearningSession::query()
            ->whereKey($learningSession)
            ->where('student_id', (string) $request->user()->getAuthIdentifier())
            ->firstOrFail();

        return response()->json(['session' => $this->serialize($session)]);
    }

    public function cancel(Request $request, int $learningSession): JsonResponse
    {
        $session = LearningSession::query()
            ->whereKey($learningSession)
            ->where('student_id', (string) $request->user()->getAuthIdentifier())
            ->firstOrFail();

        if (! in_array($session->status, ['pending', 'confirmed'], true) || $session->starts_at->isPast()) {
            return response()->json(['message' => 'Sesi ini sudah tidak dapat dibatalkan.'], 422);
        }

        $session->update(['status' => 'cancelled']);

        return response()->json(['message' => 'Pengajuan sesi berhasil dibatalkan.']);
    }

    private function serialize(LearningSession $session): array
    {
        $now = now();
        $endsAt = $session->starts_at->copy()->addMinutes($session->duration_minutes);
        $status = $session->status;
        if ($status === 'confirmed' && $now->between($session->starts_at, $endsAt)) {
            $status = 'in_progress';
        } elseif ($status === 'confirmed' && $now->greaterThan($endsAt)) {
            $status = 'completed';
        }
        $meetingUrl = $session->meeting_url;
        $hasRealMeetingUrl = is_string($meetingUrl)
            && filter_var($meetingUrl, FILTER_VALIDATE_URL)
            && in_array(parse_url($meetingUrl, PHP_URL_SCHEME), ['http', 'https'], true);

        return [
            'id' => $session->id,
            'tutor_name' => $session->tutor_name,
            'tutor_university' => $session->tutor_university,
            'tutor_major' => $session->tutor_major,
            'subject' => $session->subject,
            'topic' => $session->topic,
            'grade_level' => $session->grade_level,
            'starts_at' => $session->starts_at->toIso8601String(),
            'date_label' => $session->starts_at->locale('id')->isoFormat('dddd, D MMMM YYYY'),
            'time_label' => $session->starts_at->format('H:i').' – '.$endsAt->format('H:i'),
            'duration_minutes' => $session->duration_minutes,
            'meeting_platform' => $session->meeting_platform,
            'meeting_url' => $hasRealMeetingUrl ? $meetingUrl : null,
            'status' => $status,
        ];
    }
}
