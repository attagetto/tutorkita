<?php

namespace App\Http\Controllers;

use App\Support\DashboardMockData;
use App\Support\SubjectsMockData;
use App\Models\LearningSession;
use App\Support\LearningMaterialsDashboardData;
use App\Support\AssignmentDashboardData;
use App\Support\NotificationCenterData;
use App\Support\SessionHistoryData;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $authenticatedUser = Auth::user();
        $fullName = (string) $authenticatedUser->name;
        $nameParts = preg_split('/\s+/', trim($fullName), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $initials = mb_strtoupper(implode('', array_map(
            fn (string $part): string => mb_substr($part, 0, 1),
            array_slice($nameParts, 0, 2),
        )));

        $dashboardData = DashboardMockData::make();
        $dashboardData['user'] = [
            'name' => $fullName,
            'full_name' => $fullName,
            'email' => (string) $authenticatedUser->email,
            'initials' => $initials,
            'level' => 'Anggota TutorKita',
        ];

        $now = now();
        $dashboardData['learning_sessions'] = LearningSession::query()
            ->where('student_id', (string) $authenticatedUser->getAuthIdentifier())
            ->orderBy('starts_at')
            ->get()
            ->map(function (LearningSession $session) use ($now): LearningSession {
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

                $session->display_status = $status;
                $session->status_label = [
                    'pending' => 'Menunggu Konfirmasi',
                    'confirmed' => 'Terkonfirmasi',
                    'in_progress' => 'Sedang Berlangsung',
                    'completed' => 'Selesai',
                    'cancelled' => 'Dibatalkan',
                ][$status] ?? 'Menunggu Konfirmasi';
                $session->date_label = $session->starts_at->locale('id')->isoFormat('dddd, D MMMM YYYY');
                $session->short_date_label = $session->starts_at->locale('id')->isoFormat('ddd, D MMM YYYY');
                $session->time_label = $session->starts_at->format('H:i').' – '.$endsAt->format('H:i');
                $session->initials = mb_strtoupper(implode('', array_map(
                    static fn (string $part): string => mb_substr($part, 0, 1),
                    array_slice(preg_split('/\s+/', trim($session->tutor_name), -1, PREG_SPLIT_NO_EMPTY) ?: [], 0, 2),
                )));
                $session->can_join = in_array($status, ['confirmed', 'in_progress'], true)
                    && $hasRealMeetingUrl
                    && $now->between($session->starts_at->copy()->subMinutes(15), $endsAt);
                $session->meeting_url = $hasRealMeetingUrl ? $meetingUrl : null;

                return $session;
            });

        $dashboardData['today_sessions'] = $dashboardData['learning_sessions']
            ->filter(fn (LearningSession $session): bool => $session->starts_at->isSameDay($now) && $session->display_status !== 'cancelled');
        $weekStart = $now->copy()->startOfWeek();
        $weekEnd = $now->copy()->endOfWeek();
        $dashboardData['week_sessions'] = $dashboardData['learning_sessions']
            ->filter(fn (LearningSession $session): bool => $session->starts_at->betweenIncluded($weekStart, $weekEnd) && $session->display_status !== 'cancelled')
            ->take(4);
        $dashboardData['session_tutors'] = $dashboardData['catalog_tutors'];
        $dashboardData += SessionHistoryData::make($dashboardData['learning_sessions']);
        $dashboardData += LearningMaterialsDashboardData::make($authenticatedUser);
        $dashboardData += AssignmentDashboardData::make($authenticatedUser);
        $dashboardData = array_replace($dashboardData, NotificationCenterData::make($authenticatedUser));

        return view('dashboard.index', $dashboardData + [
            'active_nav' => 'dashboard',
            'page_title' => 'Dashboard',
            'search_placeholder' => 'Cari jadwal atau tutor...',
            'subjects' => SubjectsMockData::make(),
        ]);
    }
}
