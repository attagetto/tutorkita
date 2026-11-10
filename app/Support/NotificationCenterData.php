<?php

namespace App\Support;

use App\Models\AssignmentSubmission;
use App\Models\LearningSession;
use App\Models\StudentAssignment;
use App\Models\UserNotification;
use App\Models\UserNotificationPreference;
use Illuminate\Contracts\Auth\Authenticatable;

class NotificationCenterData
{
    private const CATEGORIES = ['sessions', 'assignments', 'messages', 'system'];

    /** @return array{notifications: \Illuminate\Support\Collection, notification_unread_count: int, notification_category_counts: array<string, int>, notification_preference_enabled: bool} */
    public static function make(Authenticatable $user): array
    {
        $userId = (string) $user->getAuthIdentifier();
        $preference = UserNotificationPreference::query()->firstOrCreate(['user_id' => $userId], ['enabled' => true]);
        if ($preference->enabled) self::syncActivity($userId);

        $items = UserNotification::query()->where('user_id', $userId)->latest('created_at')->get();
        $counts = ['all' => $items->count()];
        foreach (self::CATEGORIES as $category) {
            $counts[$category] = $items->where('category', $category)->count();
        }

        $items = $items->map(function (UserNotification $item): UserNotification {
            $item->time_label = $item->created_at->locale('id')->isoFormat('HH:mm');
            $item->date_label = $item->created_at->locale('id')->isoFormat('D MMMM YYYY');
            $item->group_label = $item->created_at->isToday() ? 'Hari Ini' : ($item->created_at->isYesterday() ? 'Kemarin' : $item->created_at->locale('id')->isoFormat('D MMMM YYYY'));
            return $item;
        });

        return [
            'notifications' => $items,
            'notification_unread_count' => $items->whereNull('read_at')->count(),
            'notification_category_counts' => $counts,
            'notification_preference_enabled' => $preference->enabled,
        ];
    }

    private static function syncActivity(string $userId): void
    {
        LearningSession::query()->where('student_id', $userId)->get()->each(function (LearningSession $session) use ($userId): void {
            $statusLabel = match ($session->status) {
                'confirmed' => 'Sesi Belajar Dikonfirmasi',
                'cancelled' => 'Sesi Belajar Dibatalkan',
                'completed' => 'Sesi Belajar Selesai',
                default => 'Pengajuan Sesi Belajar',
            };
            self::store($userId, 'session:'.$session->id.':'.$session->status, 'sessions', $statusLabel,
                $session->subject.' dengan '.$session->tutor_name.' · '.$session->starts_at->locale('id')->isoFormat('D MMM YYYY, HH:mm'), '#sessions');
        });

        StudentAssignment::query()->with('submission')->where('student_id', $userId)->get()->each(function (StudentAssignment $assignment) use ($userId): void {
            self::store($userId, 'assignment:'.$assignment->id, 'assignments', 'Tugas Baru',
                $assignment->tutor_name.' memberikan tugas: “'.$assignment->title.'”. Deadline: '.$assignment->due_at->locale('id')->isoFormat('D MMMM YYYY, HH:mm').'.', '#assignments');
            $submission = $assignment->submission;
            if ($submission instanceof AssignmentSubmission && $submission->graded_at) {
                self::store($userId, 'grade:'.$submission->id.':'.$submission->graded_at->timestamp, 'assignments', 'Nilai Tugas Telah Diberikan',
                    'Tugas “'.$assignment->title.'” telah dinilai. Nilaimu: '.number_format((float) $submission->score, 0).'/100.'.($submission->feedback ? ' Feedback: '.$submission->feedback : ''), '#assignments');
            }
        });
    }

    private static function store(string $userId, string $key, string $category, string $title, string $body, ?string $url): void
    {
        UserNotification::query()->firstOrCreate(
            ['user_id' => $userId, 'notification_key' => $key],
            ['category' => $category, 'title' => $title, 'body' => $body, 'action_url' => $url],
        );
    }
}
