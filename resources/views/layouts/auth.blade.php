<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#ffffff">
    <title>@yield('title') · TutorKita</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="auth-body">
    <main class="auth-shell">
        <section class="auth-main" aria-labelledby="auth-heading">
            <div class="auth-card">
                <a class="auth-brand" href="{{ route('home') }}" aria-label="TutorKita Beranda">
                    <img src="{{ asset('assets/tutorkita/tutorkita-logo.svg') }}" alt="TutorKita — Bimbingan Belajar Gratis" width="150" height="50">
                </a>
                <div class="auth-intro">
                    @yield('intro')
                </div>
                @if (session('status'))
                    <div class="auth-feedback" role="status">{{ session('status') }}</div>
                @endif
                @if ($errors->any())
                    <div class="auth-feedback is-error" role="alert" data-server-auth-feedback>{{ $errors->first() }}</div>
                @endif
                @yield('form')
                @yield('switch')
                <div class="auth-feedback" role="status" aria-live="polite" data-auth-feedback hidden></div>
            </div>
        </section>

        <aside class="auth-aside" aria-label="Tentang TutorKita">
            <x-auth.paper-plane />
            <div class="auth-aside-content">@yield('aside')</div>
            <x-auth.book-illustration />
        </aside>
    </main>
</body>
</html>
