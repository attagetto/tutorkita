<aside class="sidebar" id="main-sidebar" aria-label="Navigasi utama">
    <div class="sidebar-brand"><a href="{{ route('dashboard') }}" aria-label="TutorKita Dashboard"><img src="{{ asset('assets/tutorkita/tutorkita-logo.svg') }}" alt="TutorKita" width="150" height="50"></a><button class="icon-button sidebar-close" type="button" aria-label="Tutup navigasi" data-sidebar-close><x-icon name="x" /></button></div>
    <div class="sidebar-label">MENU UTAMA</div>
    <nav class="sidebar-nav">
        <a class="nav-link {{ ($active_nav ?? 'dashboard') === 'dashboard' ? 'is-current' : '' }}" href="{{ route('dashboard') }}" @if (($active_nav ?? 'dashboard') === 'dashboard') aria-current="page" @endif><x-icon name="grid" /><span>Dashboard</span></a>
        <a class="nav-link {{ ($active_nav ?? '') === 'subjects' ? 'is-current' : '' }}" href="{{ route('subjects') }}" @if (($active_nav ?? '') === 'subjects') aria-current="page" @endif><x-icon name="book" /><span>Mata Pelajaran</span></a>
        <a class="nav-link" href="{{ route('dashboard') }}#tutor"><x-icon name="users" /><span>Tutor</span></a>
        <a class="nav-link" href="{{ route('dashboard') }}#jadwal"><x-icon name="calendar" /><span>Jadwal</span></a>
    </nav>
    <div class="sidebar-label secondary-label">AKUN</div>
    <nav class="sidebar-nav">
        <button class="nav-link nav-action" type="button" data-toast="Selamat belajar, {{ $user['name'] }}!"><x-icon name="user" /><span>Profil</span></button>
        <a class="nav-link" href="{{ route('home') }}"><x-icon name="logout" /><span>Logout</span></a>
    </nav>
    <div class="sidebar-bottom"><div class="help-card"><div class="help-icon"><x-icon name="sparkle" /></div><strong>Semangat belajarnya!</strong><p>Setiap sesi adalah langkah menuju cita-citamu.</p><a href="{{ route('dashboard') }}#progress">Lihat progress <x-icon name="arrow-right" class="icon-sm" /></a></div><div class="sidebar-footnote">Bimbingan Belajar Gratis</div></div>
</aside>
