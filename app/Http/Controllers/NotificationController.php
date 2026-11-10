<?php

namespace App\Http\Controllers;

use App\Models\UserNotification;
use App\Models\UserNotificationPreference;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function read(Request $request, UserNotification $notification): JsonResponse
    {
        abort_unless($notification->user_id === (string) $request->user()->getAuthIdentifier(), 404);
        $notification->update(['read_at' => $notification->read_at ?? now()]);

        return response()->json(['unread_count' => UserNotification::query()->where('user_id', (string) $request->user()->getAuthIdentifier())->whereNull('read_at')->count()]);
    }

    public function readAll(Request $request): JsonResponse
    {
        UserNotification::query()->where('user_id', (string) $request->user()->getAuthIdentifier())->whereNull('read_at')->update(['read_at' => now()]);

        return response()->json(['unread_count' => 0, 'message' => 'Semua notifikasi sudah dibaca.']);
    }

    public function preference(Request $request): JsonResponse
    {
        $data = $request->validate(['enabled' => ['required', 'boolean']]);
        $preference = UserNotificationPreference::query()->updateOrCreate(
            ['user_id' => (string) $request->user()->getAuthIdentifier()],
            ['enabled' => $data['enabled']],
        );

        return response()->json(['enabled' => $preference->enabled, 'message' => $preference->enabled ? 'Notifikasi diaktifkan.' : 'Notifikasi dinonaktifkan.']);
    }
}
