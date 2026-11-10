<aside class="sidebar" id="main-sidebar" aria-label="Navigasi utama">
    <div class="sidebar-brand"><a href="{{ route('dashboard') }}" aria-label="TutorKita Dashboard"><img src="{{ asset('assets/tutorkita/tutorkita-logo.svg') }}" alt="TutorKita" width="150" height="50"></a><button class="icon-button sidebar-close" type="button" aria-label="Tutup navigasi" data-sidebar-close><x-icon name="x" /></button></div>
    <div class="sidebar-label">MENU BELAJAR</div>
    <nav class="sidebar-nav" aria-label="Menu belajar" data-spa-nav>
        <button class="nav-link is-current" type="button" data-page-link="dashboard" aria-current="page"><x-icon name="home" /><span>Beranda</span></button>
        <button class="nav-link" type="button" data-page-link="tutors"><x-icon name="grid" /><span>Katalog Tutor</span></button>
        <button class="nav-link" type="button" data-page-link="sessions"><x-icon name="calendar" /><span>Sesi Belajar</span></button>
        <button class="nav-link" type="button" data-page-link="materials"><x-icon name="book" /><span>Materi Pembelajaran</span></button>
        <button class="nav-link" type="button" data-page-link="assignments"><x-icon name="check" /><span>Tugas & Penilaian</span></button>
        <button class="nav-link" type="button" data-page-link="notifications"><x-icon name="bell" /><span>Notifikasi</span>@if ($notification_unread_count)<i class="sidebar-unread-dot"></i>@endif</button>
        <button class="nav-link" type="button" data-page-link="history"><x-icon name="clock" /><span>Riwayat Sesi</span></button>
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
            <span><strong>{{ $user['full_name'] }}</strong><small>Siswa</small></span>
            <x-icon name="chevron-down" class="icon-sm" />
        </div>
    </div>
</aside>
