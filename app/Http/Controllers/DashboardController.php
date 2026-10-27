<?php

namespace App\Http\Controllers;

use App\Support\DashboardMockData;
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

        return view('dashboard.index', $dashboardData + [
            'active_nav' => 'dashboard',
            'page_title' => 'Dashboard',
            'search_placeholder' => 'Cari jadwal atau tutor...',
        ]);
    }
}
