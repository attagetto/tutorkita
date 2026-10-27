<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#EFF6FE">
    <title>@yield('title', 'Dashboard') · TutorKita</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="dashboard-body">
    <div class="app-shell" id="dashboard-top">
        <button class="mobile-scrim" type="button" aria-label="Tutup navigasi" data-sidebar-close hidden></button>
        @include('dashboard.partials.sidebar')
        <div class="app-main">
            @include('dashboard.partials.topbar')
            <main class="main-content" id="main-content">
                @yield('content')
            </main>
        </div>
    </div>
    <div class="toast-region" aria-live="polite" aria-atomic="true" data-toast-region></div>
</body>
</html>
