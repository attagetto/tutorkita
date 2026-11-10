<aside class="sidebar" id="main-sidebar" aria-label="Navigasi utama">
    <div class="sidebar-brand"><a href="{{ route('dashboard') }}" aria-label="TutorKita Dashboard"><img src="{{ asset('assets/tutorkita/tutorkita-logo.svg') }}" alt="TutorKita" width="150" height="50"></a><button class="icon-button sidebar-close" type="button" aria-label="Tutup navigasi" data-sidebar-close><x-icon name="x" /></button></div>
    <div class="sidebar-label">MENU BELAJAR</div>
    @php($dashboardSpa = request()->routeIs('dashboard'))
    <nav class="sidebar-nav" aria-label="Menu belajar" @if ($dashboardSpa) data-spa-nav @endif>
        @foreach ([['dashboard', 'home', 'Beranda'], ['tutors', 'grid', 'Katalog Tutor'], ['sessions', 'calendar', 'Sesi Belajar'], ['materials', 'book', 'Materi Pembelajaran'], ['assignments', 'check', 'Tugas & Penilaian'], ['notifications', 'bell', 'Notifikasi'], ['history', 'clock', 'Riwayat Sesi']] as [$page, $icon, $label])
            @if ($dashboardSpa)
                <button class="nav-link {{ $page === 'dashboard' ? 'is-current' : '' }}" type="button" data-page-link="{{ $page }}" @if ($page === 'dashboard') aria-current="page" @endif><x-icon :name="$icon" /><span>{{ $label }}</span>@if ($page === 'notifications' && $notification_unread_count)<i class="sidebar-unread-dot"></i>@endif</button>
            @else
                <a class="nav-link" href="{{ route('dashboard') }}#{{ $page }}"><x-icon :name="$icon" /><span>{{ $label }}</span>@if ($page === 'notifications' && $notification_unread_count)<i class="sidebar-unread-dot"></i>@endif</a>
            @endif
        @endforeach
    </nav>
    <div class="sidebar-bottom">
        <section class="help-card" aria-label="Motivasi belajar">
            <div class="catalog-help-card">
                <x-icon name="graduation-cap" />
                <strong>Pendidikan Hari Ini Masa Depan yang Lebih Baik</strong>
                <button type="button" aria-label="Mulai belajar" data-toast="Semangat belajar bersama TutorKita!"><x-icon name="arrow-right" class="icon-sm" /></button>
            </div>
        </section>
        <div class="sidebar-user-profile">
            <span class="sidebar-user-avatar" aria-hidden="true">{{ $user['initials'] }}</span>
            <span><strong>{{ $user['full_name'] }}</strong><small>{{ $user['role_label'] }}</small></span>
            <x-icon name="chevron-down" class="icon-sm" />
        </div>
    </div>
</aside>
