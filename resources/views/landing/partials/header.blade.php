<header class="landing-header">
    <div class="landing-header-inner">
        <a class="landing-brand" href="{{ route('home') }}" aria-label="TutorKita Beranda"><img src="{{ asset('assets/tutorkita/tutorkita-logo.svg') }}" alt="TutorKita — Bimbingan Belajar Gratis" width="180" height="60"></a>
        <button class="landing-menu-toggle" type="button" aria-label="Buka menu navigasi" aria-expanded="false" aria-controls="landing-navigation" data-landing-menu-toggle><x-icon name="menu" /></button>
        <button class="landing-menu-scrim" type="button" aria-label="Tutup menu navigasi" data-landing-menu-scrim hidden></button>
        <nav class="landing-navigation" id="landing-navigation" aria-label="Navigasi utama" data-landing-navigation>
            <a href="{{ route('home') }}#top">Beranda</a>
            <a href="{{ route('home') }}#tutors">Cari Tutor</a>
            <a href="{{ route('home') }}#about">Tentang Kami</a>
            <div class="mobile-nav-actions"><a class="landing-button button-outline" href="{{ route('login') }}">Masuk</a><a class="landing-button button-primary" href="{{ route('register') }}">Daftar Sekarang</a></div>
        </nav>
        <div class="desktop-nav-actions"><a class="landing-button button-outline" href="{{ route('login') }}">Masuk</a><a class="landing-button button-primary" href="{{ route('register') }}">Daftar Sekarang</a></div>
    </div>
</header>
