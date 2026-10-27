@extends('layouts.dashboard')

@section('title', 'Dashboard')

@section('content')
    <div class="dashboard-page">
        <div class="welcome-banner animate-in">
            <div class="welcome-copy">
                <span class="eyebrow"><span class="eyebrow-dot"></span> {{ $today_label }}</span>
                <h1>Selamat datang kembali, <span>{{ $user['name'] }}</span>!</h1>
                <p>Terus melangkah, satu sesi belajar hari ini bisa membawa kamu lebih dekat ke cita-cita.</p>
                <a class="button button-white" href="#jadwal">Lihat jadwal belajar <x-icon name="arrow-right" class="icon-sm" /></a>
            </div>
            <div class="welcome-art" aria-hidden="true" style="--welcome-image: url('{{ asset('assets/images/tutorkita-hero-reference.jpg') }}')">
                <span class="art-orbit orbit-one"></span><span class="art-orbit orbit-two"></span>
                <div class="art-book"><x-icon name="book-open" /></div>
                <div class="art-note note-one"><x-icon name="sparkle" /></div>
                <div class="art-note note-two"><span>72%</span><small>progress</small></div>
            </div>
        </div>

        <section class="stats-grid" aria-label="Ringkasan belajar">
            @foreach ($stats as $stat)
                <x-dashboard.stat-card :stat="$stat" />
            @endforeach
        </section>

        <div class="content-grid">
            <section class="panel schedule-panel" id="jadwal" aria-labelledby="schedule-heading">
                <div class="panel-heading">
                    <div><span class="section-kicker">RUTINITAS BELAJAR</span><h2 id="schedule-heading">Jadwal belajar</h2><p>Atur waktumu, raih lebih banyak hal.</p></div>
                    <a class="text-link" href="#jadwal-list">Lihat semua <x-icon name="arrow-right" class="icon-sm" /></a>
                </div>
                <div class="tabs" role="tablist" aria-label="Filter jadwal">
                    <button class="tab-button is-active" type="button" role="tab" aria-selected="true" data-schedule-filter="all">Semua <span>{{ count($schedules) }}</span></button>
                    <button class="tab-button" type="button" role="tab" aria-selected="false" data-schedule-filter="today">Hari ini <span>3</span></button>
                    <button class="tab-button" type="button" role="tab" aria-selected="false" data-schedule-filter="upcoming">Mendatang <span>2</span></button>
                </div>
                <div class="schedule-list" id="jadwal-list">
                    @foreach ($schedules as $index => $schedule)
                        <x-dashboard.schedule-item :schedule="$schedule" :index="$index" />
                    @endforeach
                    <p class="empty-state" data-schedule-empty hidden>Tidak ada jadwal pada pilihan ini.</p>
                </div>
            </section>

            <section class="panel weekly-panel" id="progress" aria-labelledby="weekly-heading">
                <div class="panel-heading compact-heading"><div><span class="section-kicker">KONSISTENSI</span><h2 id="weekly-heading">Progress belajar</h2></div><button class="icon-button quiet-button" type="button" aria-label="Informasi progress" data-toast="Target mingguanmu 10 jam. Kamu sudah belajar 8,5 jam."><x-icon name="info" /></button></div>
                <div class="progress-summary"><div><strong>8,5 <small>jam</small></strong><span>Minggu ini</span></div><span class="trend-pill"><x-icon name="trend" class="icon-sm" /> +12%</span></div>
                <div class="weekly-chart" role="img" aria-label="Grafik jam belajar dari Senin sampai Minggu">
                    @foreach ($progress['weekly'] as $day)
                        <div class="chart-column"><div class="chart-track"><span class="chart-bar" style="--bar-height: {{ $day['value'] }}%"></span></div><span class="chart-day">{{ $day['day'] }}</span></div>
                    @endforeach
                </div>
                <div class="chart-legend"><span><i></i> Waktu belajar</span><span>Target 10 jam</span></div>
                <div class="subject-progress" id="mata-pelajaran">
                    <div class="subsection-title"><h3>Progress mata pelajaran</h3><button class="text-link small-link" type="button" data-toast="Ringkasan mata pelajaran menggunakan data contoh.">Detail</button></div>
                    @foreach ($progress['subjects'] as $subject)
                        <div class="subject-progress-row"><div class="subject-progress-label"><span>{{ $subject['name'] }}</span><strong>{{ $subject['value'] }}%</strong></div><div class="progress-track"><span class="progress-fill fill-{{ $subject['color'] }}" style="width: {{ $subject['value'] }}%"></span></div></div>
                    @endforeach
                </div>
            </section>
        </div>

        <section class="panel tutors-section" id="tutor" aria-labelledby="tutor-heading">
            <div class="panel-heading">
                <div><span class="section-kicker">DIREKOMENDASIKAN UNTUKMU</span><h2 id="tutor-heading">Kenali tutor relawan kami</h2><p>Teman belajar yang siap bantu kamu berkembang.</p></div>
                <div class="tutor-tools"><label class="filter-select-wrap"><span class="sr-only">Filter mata pelajaran tutor</span><select id="tutor-subject-filter" class="filter-select"><option value="all">Semua pelajaran</option><option value="Matematika">Matematika</option><option value="Programming">Programming</option><option value="Bahasa Inggris">Bahasa Inggris</option></select><x-icon name="chevron-down" class="icon-sm" /></label><span class="tutor-count">{{ count($tutors) }} tutor</span></div>
            </div>
            <div class="tutor-grid" data-tutor-grid>
                @foreach ($tutors as $index => $tutor)
                    <x-dashboard.tutor-card :tutor="$tutor" :index="$index" />
                @endforeach
                <p class="empty-state tutor-empty" data-tutor-empty hidden>Tidak ada tutor untuk mata pelajaran ini.</p>
            </div>
        </section>

        <section class="closing-banner" aria-label="Ajakan belajar"><div class="closing-icon"><x-icon name="sparkle" /></div><div><h2>Setiap langkah kecil berarti.</h2><p>Konsisten belajar hari ini, lebih siap menyambut masa depan.</p></div><a class="button button-white" href="#jadwal">Mulai belajar <x-icon name="arrow-right" class="icon-sm" /></a></section>
        <footer class="dashboard-footer"><span>© {{ date('Y') }} TutorKita</span><span>Bimbingan belajar gratis untuk semua.</span><a href="#dashboard-top">Kembali ke atas ↑</a></footer>
    </div>
@endsection
