<?php

namespace App\Support;

use App\Models\LearningSession;
use Illuminate\Support\Collection;

class SessionHistoryData
{
    /** @param Collection<int, LearningSession> $sessions
     *  @return array<string, mixed>
     */
    public static function make(Collection $sessions): array
    {
        $history = $sessions->sortByDesc('starts_at')->values();
        $total = $history->count();
        $completed = $history->where('display_status', 'completed')->count();
        $inProgress = $history->where('display_status', 'in_progress')->count();
        $cancelled = $history->where('display_status', 'cancelled')->count();

        $favorites = $history->groupBy('tutor_name')->map(function (Collection $tutorSessions): array {
            /** @var LearningSession $sample */
            $sample = $tutorSessions->first();

            return [
                'name' => $sample->tutor_name,
                'university' => $sample->tutor_university,
                'subject' => $tutorSessions->countBy('subject')->sortDesc()->keys()->first() ?? $sample->subject,
                'initials' => $sample->initials,
                'sessions' => $tutorSessions->count(),
            ];
        })->sortByDesc('sessions')->take(3)->values();

        $bars = collect(range(5, 0))->map(function (int $monthsAgo) use ($history): array {
            $month = now()->startOfMonth()->subMonths($monthsAgo);
            return [
                'label' => $month->locale('id')->isoFormat('MMM'),
                'count' => $history->filter(fn (LearningSession $session): bool => $session->starts_at->isSameMonth($month))->count(),
            ];
        });
        $maxBar = max(1, (int) $bars->max('count'));
        $bars = $bars->map(fn (array $bar): array => $bar + ['height' => max(8, (int) round($bar['count'] / $maxBar * 100))]);

        return [
            'session_history' => $history,
            'session_history_total' => $total,
            'session_history_completed' => $completed,
            'session_history_in_progress' => $inProgress,
            'session_history_cancelled' => $cancelled,
            'session_history_progress' => $total > 0 ? (int) round($completed / $total * 100) : 0,
            'session_history_favorites' => $favorites,
            'session_history_bars' => $bars,
            'session_history_subjects' => $history->pluck('subject')->unique()->sort()->values(),
        ];
    }
}
