<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#ffffff">
    <meta name="description" content="TutorKita menghubungkan siswa dengan mahasiswa relawan untuk bimbingan belajar gratis, mudah, dan terpercaya.">
    <title>TutorKita — Bimbingan Belajar Gratis</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="landing-body">
    @include('landing.partials.header')
    <main id="top">
        @yield('content')
    </main>
    @include('landing.partials.footer')
    <div class="toast-region landing-toast" aria-live="polite" aria-atomic="true" data-landing-toast></div>
    <div class="tutor-modal" data-tutor-modal hidden>
        <button class="tutor-modal-backdrop" type="button" aria-label="Tutup detail tutor" data-modal-close></button>
        <section class="tutor-modal-panel" role="dialog" aria-modal="true" aria-labelledby="tutor-modal-name" tabindex="-1">
            <button class="tutor-modal-close" type="button" aria-label="Tutup detail tutor" data-modal-close><x-icon name="x" /></button>
            <span class="section-kicker">TUTOR RELAWAN TUTORKITA</span>
            <div class="tutor-modal-person"><span class="tutor-modal-avatar" data-modal-initials></span><div><h2 id="tutor-modal-name" data-modal-name></h2><p data-modal-university></p><p data-modal-major></p></div></div>
            <div class="tutor-modal-meta"><span data-modal-subject></span><span class="rating"><x-icon name="star" class="icon-sm" /><span data-modal-rating></span></span></div>
            <p class="tutor-modal-description" data-modal-description></p>
            <div class="tutor-modal-availability" data-modal-availability></div>
            <a class="landing-button button-primary tutor-modal-action" href="{{ route('login') }}">Belajar bersama tutor ini</a>
        </section>
    </div>
</body>
</html>
