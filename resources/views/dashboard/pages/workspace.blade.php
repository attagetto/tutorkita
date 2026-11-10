@php
    $workspaceSubjects = $subjects ?? \App\Support\SubjectsMockData::make();
@endphp
<section class="dashboard-page spa-page catalog-page" data-spa-page="tutors" hidden>
    <div class="catalog-heading"><h1>Katalog <em>Tutor</em></h1><p>Temukan tutor relawan yang sesuai dengan kebutuhan belajar kamu.</p></div>
    <form class="catalog-search-row" data-catalog-search-form>
        <label class="catalog-search"><x-icon name="search"/><span class="sr-only">Cari tutor</span><input type="search" placeholder="Cari nama tutor atau mata pelajaran..." data-catalog-search></label>
        <button class="catalog-search-submit" type="submit">Cari Tutor <x-icon name="arrow-right" class="icon-sm"/></button>
    </form>
    <div class="catalog-filters" aria-label="Filter tutor">
        <label class="catalog-filter"><span>Mata Pelajaran</span><span class="catalog-select"><x-icon name="book"/><select data-catalog-filter="subject" aria-label="Mata pelajaran"><option value="all">Semua Mata Pelajaran</option>@foreach (['Matematika','IPA','IPS','Bahasa Indonesia','Bahasa Inggris','Biologi','Fisika','Kimia','Sejarah','PPKn','Sastra','Ekonomi','Programming'] as $subject)<option value="{{ $subject }}">{{ $subject }}</option>@endforeach</select><x-icon name="chevron-down" class="select-chevron"/></span></label>
        <label class="catalog-filter"><span>Jenjang Kelas</span><span class="catalog-select"><x-icon name="grid"/><select data-catalog-filter="level" aria-label="Jenjang kelas"><option value="all">Semua Jenjang</option><option value="SD">SD</option><option value="SMP">SMP</option><option value="SMA/SMK">SMA/SMK</option></select><x-icon name="chevron-down" class="select-chevron"/></span></label>
        <label class="catalog-filter"><span>Jadwal Tersedia</span><span class="catalog-select"><x-icon name="calendar"/><select data-catalog-filter="schedule" aria-label="Jadwal tersedia"><option value="all">Pilih Jadwal</option><optgroup label="Hari"><option>Senin</option><option>Selasa</option><option>Rabu</option><option>Kamis</option><option>Jumat</option><option>Sabtu</option><option>Minggu</option></optgroup><optgroup label="Waktu"><option>Pagi</option><option>Siang</option><option>Sore</option><option>Malam</option></optgroup></select><x-icon name="chevron-down" class="select-chevron"/></span></label>
        <label class="catalog-filter"><span>Lokasi (Opsional)</span><span class="catalog-select"><x-icon name="map-pin"/><select data-catalog-filter="location" aria-label="Lokasi"><option value="all">Semua Lokasi</option>@foreach (['Jakarta','Bandung','Yogyakarta','Surabaya','Semarang','Malang','Bogor','Surakarta','Makassar','Jember','Bandar Lampung','Palembang','Denpasar','Padang'] as $location)<option>{{ $location }}</option>@endforeach</select><x-icon name="chevron-down" class="select-chevron"/></span></label>
        <div class="catalog-more-wrap"><button class="catalog-more-button" type="button" aria-expanded="false" aria-controls="catalog-more-filters" data-catalog-more-toggle><x-icon name="filter"/> Filter Lainnya <x-icon name="chevron-down" class="icon-sm more-chevron"/></button><div class="catalog-more-popover" id="catalog-more-filters" hidden><strong>Filter tambahan</strong><label>Rating minimum<select data-catalog-filter="rating"><option value="0">Semua rating</option><option value="4.5">4.5 ke atas</option><option value="4.8">4.8 ke atas</option><option value="5">5.0</option></select></label><label class="catalog-available-check"><input type="checkbox" data-catalog-filter="available"> Hanya tutor tersedia</label><button class="catalog-clear-filters" type="button" data-catalog-reset>Reset filter</button></div></div>
    </div>
    <div class="catalog-results-row"><p aria-live="polite">Menampilkan <strong data-catalog-total>24</strong> tutor relawan</p><label class="catalog-sort"><span>Urutkan berdasarkan</span><select data-catalog-sort aria-label="Urutkan tutor"><option value="rating-desc">Rating Tertinggi</option><option value="rating-asc">Rating Terendah</option><option value="name-asc">Nama A–Z</option><option value="name-desc">Nama Z–A</option><option value="newest">Tutor Terbaru</option></select></label></div>
    <div class="catalog-grid" data-catalog-grid>
        @foreach ($catalog_tutors as $catalogIndex => $tutor)
            <article class="catalog-card" data-catalog-card data-name="{{ \Illuminate\Support\Str::lower($tutor['name']) }}" data-searchable="{{ \Illuminate\Support\Str::lower($tutor['name'].' '.$tutor['university'].' '.$tutor['major'].' '.implode(' ', $tutor['subjects']).' '.implode(' ', $tutor['levels']).' '.$tutor['location']) }}" data-subjects="{{ \Illuminate\Support\Str::lower(implode('|', $tutor['subjects'])) }}" data-levels="{{ implode('|', $tutor['levels']) }}" data-location="{{ $tutor['location'] }}" data-day="{{ $tutor['day'] }}" data-period="{{ $tutor['period'] }}" data-available="{{ $tutor['available'] ? '1' : '0' }}" data-rating="{{ $tutor['rating'] }}" data-created="{{ $catalogIndex }}" style="--catalog-delay: {{ ($catalogIndex % 8) * 35 }}ms">
                <button class="catalog-favorite" type="button" aria-label="Simpan {{ $tutor['name'] }} sebagai favorit" aria-pressed="false" data-catalog-favorite="{{ $tutor['name'] }}"><x-icon name="heart"/></button>
                <div class="catalog-tutor-main"><span class="catalog-avatar avatar-tone-{{ $catalogIndex % 4 }}" aria-hidden="true"><i></i></span><div class="catalog-tutor-info"><h2>{{ $tutor['name'] }} <span class="catalog-verified" aria-label="Tutor terverifikasi">✓</span></h2><p>{{ $tutor['university'] }}</p><p>{{ $tutor['major'] }}</p></div></div>
                <div class="catalog-rating-row"><span class="catalog-availability {{ $tutor['available'] ? 'is-available' : 'is-unavailable' }}">{{ $tutor['available'] ? 'Tersedia' : 'Tidak Tersedia' }}</span><span class="catalog-rating"><b>★</b> {{ number_format($tutor['rating'], 1) }} <small>({{ $tutor['reviews'] }} ulasan)</small></span></div>
                <div class="catalog-tags">@foreach ($tutor['subjects'] as $tag)<span>{{ $tag }}</span>@endforeach @foreach ($tutor['levels'] as $level)<span>{{ $level }}</span>@endforeach</div>
                <button class="catalog-profile-button" type="button" data-catalog-profile="{{ $catalogIndex }}">Lihat Profil <x-icon name="arrow-right" class="icon-sm"/></button>
            </article>
        @endforeach
        <p class="catalog-empty" data-catalog-empty hidden>Tidak ada tutor yang cocok. Coba ubah pencarian atau filter.</p>
    </div>
    <nav class="catalog-pagination" aria-label="Halaman katalog tutor" data-catalog-pagination></nav>
    <dialog class="catalog-profile-dialog" data-catalog-profile-dialog aria-labelledby="catalog-profile-name"><button class="catalog-dialog-close" type="button" aria-label="Tutup" data-catalog-dialog-close>×</button><div data-catalog-profile-content></div></dialog>
</section>

<section class="dashboard-page spa-page sessions-page" data-spa-page="sessions" data-session-config data-store-url="{{ route('dashboard.sessions.store') }}" data-detail-url="{{ route('dashboard.sessions.show', ['learningSession' => '__ID__']) }}" data-cancel-url="{{ route('dashboard.sessions.cancel', ['learningSession' => '__ID__']) }}" hidden>
    <div class="sessions-heading"><div><span class="section-kicker">RUANG BELAJARMU</span><h1>Sesi <em>Belajar</em></h1></div><span class="session-total"><strong>{{ $learning_sessions->count() }}</strong> sesi tercatat</span></div>
    <div class="sessions-layout">
        <div class="sessions-main-column">
            <nav class="session-tabs" role="tablist" aria-label="Kategori sesi belajar">
                <button class="is-active" type="button" role="tab" aria-selected="true" data-session-filter="all"><x-icon name="grid"/> Semua Sesi</button>
                <button type="button" role="tab" aria-selected="false" data-session-filter="upcoming"><x-icon name="clock"/> Sesi Mendatang</button>
                <button type="button" role="tab" aria-selected="false" data-session-filter="completed"><x-icon name="check"/> Sesi Selesai</button>
                <button type="button" role="tab" aria-selected="false" data-session-filter="cancelled"><x-icon name="x"/> Sesi Dibatalkan</button>
                <button type="button" role="tab" aria-selected="false" data-session-filter="pending"><x-icon name="document"/> Pengajuan Sesi</button>
                <button class="session-new-button" type="button" data-session-dialog-open><x-icon name="plus"/> Ajukan Sesi Baru</button>
            </nav>
            <div class="session-list-heading"><div><h2 data-session-heading>Sesi Mendatang</h2><p data-session-description>Jadwal sesi belajar yang sudah dikonfirmasi atau menunggu konfirmasi.</p></div><span class="session-filter-count" aria-live="polite"><strong data-session-count>{{ $learning_sessions->count() }}</strong> sesi</span></div>
            <div class="session-list" data-session-list>
                @foreach ($learning_sessions as $index => $session)
                    <article class="session-card" data-session-card data-session-status="{{ $session->display_status }}" data-session-start="{{ $session->starts_at->toIso8601String() }}" data-session-tutor="{{ $session->tutor_name }}" data-session-detail-url="{{ route('dashboard.sessions.show', ['learningSession' => $session->id]) }}" data-session-cancel-url="{{ route('dashboard.sessions.cancel', ['learningSession' => $session->id]) }}" style="--session-delay: {{ min($index, 6) * 45 }}ms">
                        <div class="session-tutor-block"><span class="session-avatar" aria-hidden="true">{{ $session->initials }}</span><div class="session-tutor-copy"><h3>{{ $session->tutor_name }} <span class="session-verified" aria-label="Tutor terverifikasi">✓</span></h3><p>{{ $session->tutor_university }}</p><p>{{ $session->tutor_major }}</p><div class="session-subject-tags"><span>{{ $session->subject }}</span><span>{{ $session->grade_level }}</span></div></div></div>
                        <div class="session-schedule-block"><p><x-icon name="calendar"/><span>{{ $session->date_label }}</span></p><p><x-icon name="clock"/><span>{{ $session->time_label }}</span></p><p><x-icon name="video"/><span>Online ({{ $session->meeting_platform }})</span></p></div>
                        <div class="session-actions-block"><span class="session-status status-{{ $session->display_status }}">@if ($session->display_status === 'in_progress')<i></i>@endif{{ $session->status_label }}</span><div class="session-action-buttons"><button class="button button-outline" type="button" data-session-detail> Lihat Detail </button>@if ($session->can_join)<a class="button button-primary" href="{{ $session->meeting_url }}" target="_blank" rel="noopener noreferrer"><x-icon name="video"/> Masuk Kelas</a>@else<button class="button button-primary is-disabled" type="button" disabled title="Tautan kelas akan aktif setelah dibagikan tutor."><x-icon name="video"/> Masuk Kelas</button>@endif</div></div>
                    </article>
                @endforeach
                <div class="sessions-empty" data-session-empty @if ($learning_sessions->isNotEmpty()) hidden @endif><span class="spa-icon"><x-icon name="calendar"/></span><h3>Belum ada sesi belajar</h3><p>Ajukan sesi baru untuk mulai menyusun jadwal belajar dengan tutor.</p><button type="button" class="button button-primary" data-session-dialog-open>＋ Ajukan Sesi Baru</button></div>
                <div class="sessions-empty sessions-filter-empty" data-session-filter-empty hidden><span class="spa-icon"><x-icon name="search"/></span><h3>Belum ada sesi pada kategori ini</h3><p>Sesi dengan status tersebut akan muncul di sini.</p></div>
            </div>
        </div>
        <aside class="sessions-sidebar" aria-label="Ringkasan jadwal">
            <section class="session-side-card" aria-labelledby="sessions-today-title"><div class="session-side-heading"><h2 id="sessions-today-title">Jadwal Hari Ini</h2><button type="button" class="text-link small-link" data-session-filter-shortcut="all">Lihat Semua <x-icon name="arrow-right" class="icon-sm"/></button></div>
                @forelse ($today_sessions as $session)
                    <button class="today-session-item" type="button" data-session-side-detail="{{ $session->id }}"><span class="today-session-icon"><x-icon name="calendar"/></span><span class="today-session-copy"><strong>{{ $session->subject }} – {{ $session->topic }}</strong><small>{{ $session->starts_at->format('H:i') }} – {{ $session->starts_at->copy()->addMinutes($session->duration_minutes)->format('H:i') }}</small><span class="today-tutor"><i>{{ $session->initials }}</i>{{ $session->tutor_name }}</span><em class="session-status status-{{ $session->display_status }}">{{ $session->status_label }}</em></span><x-icon name="arrow-right" class="today-arrow"/></button>
                @empty
                    <div class="schedule-empty"><span class="today-session-icon"><x-icon name="calendar"/></span><p>Belum ada sesi yang dijadwalkan hari ini.</p></div>
                @endforelse
            </section>
            <section class="session-side-card" aria-labelledby="sessions-week-title"><div class="session-side-heading"><h2 id="sessions-week-title">Jadwal Minggu Ini</h2><button type="button" class="text-link small-link" data-session-filter-shortcut="upcoming">Lihat Semua <x-icon name="arrow-right" class="icon-sm"/></button></div>
                @forelse ($week_sessions as $session)
                    <button class="week-session-item" type="button" data-session-side-detail="{{ $session->id }}"><span class="today-session-icon"><x-icon name="calendar"/></span><span class="week-session-copy"><strong>{{ $session->short_date_label }}</strong><small>{{ $session->starts_at->format('H:i') }} – {{ $session->starts_at->copy()->addMinutes($session->duration_minutes)->format('H:i') }}</small><small>{{ $session->subject }} · {{ $session->tutor_name }}</small></span><em class="session-status status-{{ $session->display_status }}">{{ $session->status_label }}</em></button>
                @empty
                    <div class="schedule-empty"><span class="today-session-icon"><x-icon name="calendar"/></span><p>Jadwal minggu ini akan muncul setelah sesi dikonfirmasi.</p></div>
                @endforelse
            </section>
            <button class="session-promo-card" type="button" data-session-dialog-open><span class="session-promo-icon"><x-icon name="graduation-cap"/></span><span><strong>Ajukan Sesi Belajar Baru</strong><small>Temukan tutor dan pilih jadwal yang sesuai dengan waktu luangmu.</small></span><i><x-icon name="arrow-right"/></i></button>
        </aside>
    </div>
    <dialog class="session-dialog" data-session-create-dialog aria-labelledby="session-create-title"><button class="session-dialog-close" type="button" aria-label="Tutup" data-session-dialog-close>×</button><span class="section-kicker">MULAI BELAJAR BERSAMA</span><h2 id="session-create-title">Ajukan Sesi Belajar Baru</h2><p class="session-dialog-lead">Pilih tutor dan waktu yang kamu inginkan. Pengajuan akan menunggu konfirmasi tutor.</p><form data-session-create-form action="{{ route('dashboard.sessions.store') }}" method="post">@csrf
        <label>Nama Tutor<select name="tutor_name" required data-session-tutor>@foreach ($session_tutors as $tutor)<option value="{{ $tutor['name'] }}" data-subjects="{{ implode('|', $tutor['subjects']) }}" data-levels="{{ implode('|', $tutor['levels']) }}" @disabled(! $tutor['available'])>{{ $tutor['name'] }} · {{ implode(', ', $tutor['subjects']) }}{{ $tutor['available'] ? '' : ' · Tidak tersedia' }}</option>@endforeach</select></label>
        <label>Mata Pelajaran<select name="subject" required data-session-subject></select></label>
        <label>Topik Pembelajaran<input name="topic" type="text" maxlength="180" required placeholder="Contoh: Persamaan kuadrat"></label>
        <label>Jenjang<select name="grade_level" required data-session-level><option value="SD">SD</option><option value="SMP">SMP</option><option value="SMA/SMK">SMA/SMK</option></select></label>
        <label>Waktu yang Diinginkan<input name="starts_at" type="datetime-local" min="{{ now()->addMinute()->format('Y-m-d\\TH:i') }}" required></label>
        <p class="session-form-note"><x-icon name="info"/> Tautan kelas akan ditambahkan oleh tutor setelah sesi dikonfirmasi.</p><p class="session-form-error" data-session-form-error role="alert" hidden></p>
        <div class="session-dialog-actions"><button class="button button-outline" type="button" data-session-dialog-close>Batal</button><button class="button button-primary" type="submit" data-session-submit>Ajukan Sesi <x-icon name="arrow-right" class="icon-sm"/></button></div>
    </form></dialog>
    <dialog class="session-dialog session-detail-dialog" data-session-detail-dialog aria-labelledby="session-detail-title"><button class="session-dialog-close" type="button" aria-label="Tutup" data-session-detail-close>×</button><div data-session-detail-content><span class="section-kicker">DETAIL SESI BELAJAR</span><h2 id="session-detail-title">Memuat detail sesi</h2><p>Mohon tunggu sebentar.</p></div><div class="session-dialog-actions"><button class="button button-outline" type="button" data-session-detail-close>Tutup</button><button class="button button-danger" type="button" data-session-cancel hidden>Batalkan Sesi</button></div></dialog>
</section>

<section class="dashboard-page spa-page" data-spa-page="materials" hidden>
    @php
        $materials = $materials ?? collect();
        $materialSubjects = $materials->pluck('subject')->unique()->values();
        $mainMaterialSubjects = ['Matematika', 'IPA', 'Bahasa Indonesia', 'Bahasa Inggris'];
        $extraMaterialSubjects = $materialSubjects->reject(fn ($subject) => in_array($subject, $mainMaterialSubjects, true))->values();
    @endphp
    <div class="materials-heading"><div><h1>Materi <em>Pembelajaran</em></h1><p>Akses berbagai materi belajar dari tutor, mulai dari rangkuman, modul, video, hingga latihan soal.</p></div></div>
    <div class="materials-layout">
        <div class="materials-primary">
            <section class="materials-hero" aria-label="Belajar lebih mudah"><div class="materials-hero-copy"><h2>Belajar Lebih Mudah<br> dengan Materi Lengkap</h2><p>Temukan materi sesuai mata pelajaran dan jenjang kelasmu, lalu mulai belajar sekarang!</p></div><div class="materials-hero-illustration" aria-hidden="true"><span class="hero-sun"></span><div class="hero-book hero-book-one"></div><div class="hero-book hero-book-two"></div><div class="hero-cap"><i></i></div><div class="hero-screen"><span>▶</span></div></div></section>
            <nav class="materials-subject-nav" aria-label="Filter mata pelajaran" data-material-subjects>
                <button class="is-active" type="button" data-material-subject="all"><x-icon name="grid"/>Semua Materi</button>
                <button type="button" data-material-subject="Matematika"><x-icon name="trend"/>Matematika</button>
                <button type="button" data-material-subject="IPA"><x-icon name="sparkle"/>IPA</button>
                <button type="button" data-material-subject="Bahasa Indonesia"><x-icon name="book"/>Bahasa Indonesia</button>
                <button type="button" data-material-subject="Bahasa Inggris"><x-icon name="chat"/>Bahasa Inggris</button>
                @if ($extraMaterialSubjects->isNotEmpty())<div class="materials-more-wrap"><button type="button" data-material-more aria-expanded="false">Lainnya <x-icon name="chevron-down"/></button><div class="materials-more-menu" data-material-more-menu hidden>@foreach ($extraMaterialSubjects as $subject)<button type="button" data-material-subject="{{ $subject }}">{{ $subject }}</button>@endforeach</div></div>@else<button type="button" class="materials-more-empty" disabled>Lainnya <x-icon name="chevron-down"/></button>@endif
            </nav>
            <div class="materials-controls"><label class="materials-search"><x-icon name="search"/><span class="sr-only">Cari materi</span><input type="search" placeholder="Cari materi, judul, atau tutor..." data-material-search></label><label class="materials-select"><x-icon name="graduation-cap"/><span class="sr-only">Jenjang</span><select data-material-grade><option value="all">Semua Jenjang</option><option>SD</option><option>SMP</option><option>SMA/SMK</option><option>Perguruan Tinggi</option></select><x-icon name="chevron-down"/></label><label class="materials-select materials-sort"><x-icon name="filter"/><span class="sr-only">Urutan</span><select data-material-sort><option value="newest">Terbaru</option><option value="popular">Terpopuler</option></select><x-icon name="chevron-down"/></label></div>
            <div class="materials-list-heading"><h2>Daftar Materi Pembelajaran <span data-material-count>{{ $materials->count() }}</span></h2><span data-material-result-label></span></div>
            <div class="materials-card-grid" data-material-grid>@forelse ($materials as $material)
                <article class="learning-material-card {{ $material->is_completed ? 'is-completed' : '' }}" data-material-card data-id="{{ $material->id }}" data-subject="{{ $material->subject }}" data-grade="{{ $material->grade_level }}" data-title="{{ $material->title }}" data-search="{{ \Illuminate\Support\Str::lower($material->title.' '.$material->tutor_name.' '.$material->subject.' '.$material->topic.' '.$material->grade_level) }}" data-views="{{ $material->view_count }}" data-date="{{ $material->published_at?->timestamp ?? 0 }}" tabindex="0" role="button" aria-label="Lihat detail {{ $material->title }}">
                    <div class="learning-material-thumb"><span class="learning-material-subject">{{ $material->subject }}</span><x-material-art :kind="$material->illustration"/></div>
                    @php
                        $materialTypeLabel = ['module_pdf' => 'Modul (PDF)', 'material_pdf' => 'Materi (PDF)', 'video' => 'Video', 'exercise' => 'Latihan Soal', 'summary' => 'Rangkuman'][$material->material_type] ?? $material->material_type;
                    @endphp
                    <div class="learning-material-info"><h3>{{ $material->title }}</h3><p class="learning-material-author"><x-icon name="user"/>Oleh {{ $material->tutor_name }}</p><div class="learning-material-meta"><span><x-icon name="{{ $material->material_type === 'video' ? 'play' : 'document' }}"/>{{ $materialTypeLabel }}@if ($material->page_count) · {{ $material->page_count }} halaman @elseif ($material->duration_seconds) · {{ gmdate('i:s', $material->duration_seconds) }} @endif</span><small>{{ number_format($material->view_count) }} kali dilihat</small></div></div>
                    <button class="learning-material-bookmark {{ $material->is_bookmarked ? 'is-saved' : '' }}" type="button" data-material-bookmark="{{ $material->id }}" data-url="{{ route('dashboard.materials.bookmark', $material) }}" aria-label="{{ $material->is_bookmarked ? 'Hapus bookmark' : 'Simpan materi' }}" aria-pressed="{{ $material->is_bookmarked ? 'true' : 'false' }}"><x-icon name="bookmark"/></button>
                </article>
            @empty <div class="materials-empty" data-material-empty>Belum ada materi yang tersedia.</div> @endforelse</div>
            <div class="materials-empty" data-material-filter-empty hidden>Materi tidak ditemukan. Coba ubah kata pencarian atau filternya.</div>
            <nav class="materials-pagination" data-material-pagination aria-label="Halaman materi"></nav>
        </div>
        <aside class="materials-sidebar">
            <section class="materials-progress-card"><div class="materials-side-heading"><h2>Progress Belajar</h2><button type="button" data-material-detail-progress>Lihat Detail <x-icon name="arrow-right"/></button></div><div class="materials-progress-content"><div class="materials-progress-ring" data-progress-ring style="--progress:{{ $material_progress_percent ?? 0 }}%"><strong>{{ $material_progress_percent ?? 0 }}%</strong></div><div class="materials-progress-summary"><p>{{ $completed_material_count ?? 0 }} dari {{ $material_count ?? 0 }} materi selesai</p><div class="materials-progress-track"><span data-progress-bar style="width:{{ $material_progress_percent ?? 0 }}%"></span></div></div></div><p class="materials-progress-empty" data-material-progress-empty @if (($completed_material_count ?? 0) > 0) hidden @endif>Mulai pelajari materi dan tandai selesai untuk melihat progresmu.</p></section>
            <section class="materials-side-card"><div class="materials-side-heading"><h2>Materi Terbaru</h2><button type="button" data-material-see-all>Lihat Semua</button></div><div class="materials-side-list">@forelse ($recent_materials as $material)<button type="button" class="materials-side-item" data-material-open="{{ $material->id }}"><span class="materials-side-thumb"><x-material-art :kind="$material->illustration"/></span><span><strong>{{ $material->title }}</strong><small>{{ $material->subject }} · {{ $material->published_at?->locale('id')->diffForHumans() ?? 'Baru' }}</small></span><x-icon name="arrow-right"/></button>@empty<p class="materials-side-empty">Belum ada materi terbaru.</p>@endforelse</div></section>
            <section class="materials-side-card"><div class="materials-side-heading"><h2>Rekomendasi untukmu</h2></div><div class="materials-side-list">@forelse ($recommended_materials as $material)<button type="button" class="materials-side-item" data-material-open="{{ $material->id }}"><span class="materials-side-thumb"><x-material-art :kind="$material->illustration"/></span><span><strong>{{ $material->title }}</strong><small>{{ $material->subject }}</small></span><x-icon name="arrow-right"/></button>@empty<p class="materials-side-empty">Selesaikan materi untuk membuka rekomendasi baru.</p>@endforelse</div></section>
        </aside>
    </div>
    <dialog class="material-detail-dialog" data-material-dialog aria-labelledby="material-detail-title"><button type="button" class="material-dialog-close" data-material-close aria-label="Tutup">×</button><div data-material-detail><p>Memuat detail materi…</p></div></dialog>
</section>

<section class="dashboard-page spa-page" data-spa-page="assignments" hidden>
    @php
        $averageScore = $assignment_average_score;
    @endphp
    <div class="assignment-heading"><div><h1>Tugas & <em>Penilaian</em></h1><p>Kelola tugas, kumpulkan hasil pekerjaan, dan lihat penilaian dari tutor.</p></div></div>
    <div class="assignment-dashboard-layout">
        <div class="assignment-primary-column">
            <section class="assignment-hero"><div class="assignment-hero-copy"><h2>Kerjakan Tugas dan<br>Pantau Penilaianmu</h2><p>Selesaikan tugas tepat waktu, dapatkan feedback dari tutor, dan lihat perkembangan belajarmu.</p></div><x-assignment-hero-art/></section>
            <nav class="assignment-tabs" aria-label="Filter tugas" data-assignment-tabs>
                <button class="is-active" type="button" data-assignment-filter="all"><x-icon name="list"/>Semua Tugas</button>
                <button type="button" data-assignment-filter="active"><x-icon name="clock"/>Tugas Aktif</button>
                <button type="button" data-assignment-filter="submitted"><x-icon name="check"/>Tugas Dikumpulkan</button>
                <button type="button" data-assignment-filter="graded"><x-icon name="chart"/>Dinilai</button>
            </nav>
            <div class="assignment-list-heading"><h2>Daftar Tugas</h2><span data-assignment-count>{{ $assignment_count }}</span></div>
            <div class="assignment-list" data-assignment-list>
                @forelse ($student_assignments as $assignment)
                    @php
                        $statusLabel = ['active' => 'Aktif', 'submitted' => 'Dikumpulkan', 'late' => 'Terlambat', 'graded' => 'Dinilai'][$assignment->display_status];
                        $statusClass = ['active' => 'assignment-status-active', 'submitted' => 'assignment-status-submitted', 'late' => 'assignment-status-late', 'graded' => 'assignment-status-graded'][$assignment->display_status];
                        $subjectIcon = match (true) { str_contains(mb_strtolower($assignment->subject), 'matematika') => 'code', str_contains(mb_strtolower($assignment->subject), 'bahasa') => 'book-open', str_contains(mb_strtolower($assignment->subject), 'ipa') => 'sparkle', default => 'document' };
                    @endphp
                    <article class="assignment-row" data-assignment-card data-id="{{ $assignment->id }}" data-status="{{ $assignment->display_status }}" data-submitted="{{ $assignment->submission?->submitted_at ? 'true' : 'false' }}" data-search="{{ \Illuminate\Support\Str::lower($assignment->title.' '.$assignment->subject.' '.$assignment->tutor_name) }}">
                        <span class="assignment-subject-art assignment-tone-{{ $subjectIcon }}"><x-icon name="{{ $subjectIcon }}"/></span>
                        <div class="assignment-row-main"><h3>{{ $assignment->title }}</h3><p>{{ $assignment->subject }} <span>·</span> Oleh {{ $assignment->tutor_name }}</p><div class="assignment-meta"><span><x-icon name="calendar"/>Deadline: {{ $assignment->due_at->locale('id')->isoFormat('D MMMM YYYY, HH:mm') }}</span><span><x-icon name="document"/>{{ $assignment->accepts_file ? 'File' : '' }}{{ $assignment->accepts_file && $assignment->accepts_text ? ' / ' : '' }}{{ $assignment->accepts_text ? 'Deskripsi' : '' }}</span></div></div>
                        <span class="assignment-status {{ $statusClass }}"><x-icon name="{{ $assignment->display_status === 'graded' ? 'check' : 'clock' }}"/>{{ $statusLabel }}</span>
                        @if ($assignment->submission?->score !== null)<div class="assignment-score-pill"><small>Nilai</small><strong>{{ rtrim(rtrim(number_format((float) $assignment->submission->score, 1), '0'), '.') }}<small>/100</small></strong></div>@endif
                        <button class="button {{ $assignment->display_status === 'graded' ? 'button-outline' : 'button-primary' }} assignment-row-action" type="button" data-assignment-open="{{ $assignment->id }}">{{ $assignment->display_status === 'graded' ? 'Lihat Hasil' : ($assignment->submission?->submitted_at ? 'Lihat Tugas' : 'Kerjakan Tugas') }} <x-icon name="arrow-right"/></button>
                    </article>
                @empty
                    <div class="assignment-empty" data-assignment-empty><span><x-icon name="document"/></span><h3>Belum ada tugas</h3><p>Tugas dari tutor akan muncul di sini setelah diberikan.</p></div>
                @endforelse
            </div>
            <p class="assignment-empty assignment-filter-empty" data-assignment-filter-empty hidden>Tidak ada tugas pada kategori ini.</p>
        </div>
        <aside class="assignment-sidebar">
            <section class="assignment-score-card"><div class="assignment-side-heading"><h2>Rata-rata Nilai</h2><button type="button" data-assignment-show-grades>Lihat Detail <x-icon name="arrow-right"/></button></div><div class="assignment-score-summary"><div class="assignment-score-ring" style="--score:{{ $averageScore ?? 0 }}%"><strong>{{ $averageScore !== null ? number_format($averageScore) : '—' }}</strong><span>/100</span></div><div><p>Kategori Nilai</p><b class="assignment-grade-category {{ $averageScore === null ? 'is-empty' : '' }}"><x-icon name="check"/>{{ $assignment_grade_category }}</b></div></div></section>
            <section class="assignment-progress-card"><div class="assignment-side-heading"><h2>Progress Tugas</h2><button type="button" data-assignment-show-list>Lihat Detail <x-icon name="arrow-right"/></button></div><p>{{ $submitted_assignment_count }} dari {{ $assignment_count }} tugas selesai</p><div class="assignment-progress-line"><span data-assignment-progress style="width:{{ $assignment_completion_percent }}%"></span></div><strong>{{ $assignment_completion_percent }}%</strong></section>
            <section class="assignment-recent-card"><div class="assignment-side-heading"><h2>Penilaian Terbaru</h2><button type="button" data-assignment-show-grades>Lihat Semua <x-icon name="arrow-right"/></button></div><div class="assignment-recent-list">
                @forelse ($recent_assignment_grades as $assignment)
                    <button type="button" class="assignment-recent-item" data-assignment-open="{{ $assignment->id }}"><span class="assignment-mini-art"><x-icon name="{{ str_contains(mb_strtolower($assignment->subject), 'bahasa') ? 'book-open' : 'code' }}"/></span><span><strong>{{ $assignment->title }}</strong><small>{{ $assignment->subject }}</small></span><b>{{ rtrim(rtrim(number_format((float) $assignment->submission->score, 1), '0'), '.') }}</b></button>
                @empty
                    <p class="assignment-recent-empty">Nilai dan feedback tutor akan muncul di sini setelah tugas diperiksa.</p>
                @endforelse
            </div></section>
            <section class="assignment-motivation-card"><x-icon name="trophy"/><div><h2>Pertahankan Semangatmu!</h2><p>Kamu sudah mengumpulkan {{ $assignment_completion_percent }}% tugas. Terus tingkatkan hasil belajarmu!</p></div></section>
        </aside>
    </div>
    <dialog class="assignment-detail-dialog" data-assignment-dialog aria-labelledby="assignment-detail-title"><button type="button" class="assignment-dialog-close" data-assignment-close aria-label="Tutup">×</button><div data-assignment-detail><p>Memuat detail tugas…</p></div><form class="assignment-submit-form" data-assignment-submit-form hidden enctype="multipart/form-data"><label data-answer-text-wrap>Jawaban<textarea name="answer_text" rows="6" maxlength="20000" placeholder="Tulis jawabanmu di sini…"></textarea></label><label data-answer-file-wrap>Lampirkan file <input type="file" name="answer_file" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.txt,.csv,.png,.jpg,.jpeg"><small data-answer-file-help></small></label><p class="assignment-submit-error" data-assignment-error role="alert" hidden></p><button type="submit" class="button button-primary" data-assignment-submit-button>Kumpulkan Tugas <x-icon name="arrow-right"/></button></form></dialog>
</section>

<section class="dashboard-page spa-page" data-spa-page="notifications" hidden>
    <div class="notification-heading"><h1>Noti<span>fikasi</span></h1><p>Dapatkan informasi terbaru seputar sesi belajar, tugas, dan aktivitas belajarmu.</p></div>
    <div class="notification-layout">
        <div class="notification-main-column">
            <section class="notification-hero" aria-label="Informasi notifikasi"><div><h2>Jangan Lewatkan<br>Informasi Penting!</h2><p>Pantau semua notifikasi agar kamu selalu update dengan jadwal, tugas, dan pesan dari tutor.</p></div><x-notification-hero-art :unread-count="$notification_unread_count" /></section>
            <div class="notification-tabs" role="tablist" aria-label="Filter kategori notifikasi">
                <button type="button" class="is-active" role="tab" aria-selected="true" data-notification-filter="all"><x-icon name="list"/>Semua Notifikasi <span>{{ $notification_category_counts['all'] }}</span></button>
                <button type="button" role="tab" aria-selected="false" data-notification-filter="sessions"><x-icon name="calendar"/>Sesi Belajar <span>{{ $notification_category_counts['sessions'] }}</span></button>
                <button type="button" role="tab" aria-selected="false" data-notification-filter="assignments"><x-icon name="check"/>Tugas & Penilaian <span>{{ $notification_category_counts['assignments'] }}</span></button>
                <button type="button" role="tab" aria-selected="false" data-notification-filter="messages"><x-icon name="chat"/>Pesan <span>{{ $notification_category_counts['messages'] }}</span></button>
                <button type="button" role="tab" aria-selected="false" data-notification-filter="system"><x-icon name="sparkle"/>Sistem <span>{{ $notification_category_counts['system'] }}</span></button>
            </div>
            <div class="notification-feed" aria-live="polite">
                @forelse ($notifications->groupBy('group_label') as $group => $groupNotifications)
                    <section class="notification-group" data-notification-group><h2>{{ $group }}</h2><div class="notification-group-list">
                        @foreach ($groupNotifications as $notification)
                            <article class="notification-center-card {{ $notification->read_at ? '' : 'is-unread' }}" data-notification-card data-category="{{ $notification->category }}" data-notification-id="{{ $notification->id }}">
                                <a class="notification-center-open" href="{{ $notification->action_url ?: '#notifications' }}" data-notification-open aria-label="Buka notifikasi: {{ $notification->title }}">
                                    <span class="notification-center-icon icon-{{ $notification->category }}"><x-icon name="{{ ['sessions' => 'calendar', 'assignments' => 'check', 'messages' => 'chat', 'system' => 'sparkle'][$notification->category] ?? 'bell' }}"/></span>
                                    <span class="notification-center-copy"><strong>{{ $notification->title }}</strong><span>{{ $notification->body }}</span></span>
                                    <time datetime="{{ $notification->created_at->toIso8601String() }}">{{ $notification->time_label }}</time>
                                    @if (! $notification->read_at)<i class="notification-unread-dot" aria-label="Belum dibaca"></i>@endif
                                </a>
                                @if (! $notification->read_at)<button type="button" class="notification-mark-one" data-notification-read aria-label="Tandai {{ $notification->title }} sudah dibaca">Tandai dibaca</button>@endif
                            </article>
                        @endforeach
                    </div></section>
                @empty
                    <div class="notification-empty"><span><x-icon name="bell"/></span><h2>Belum ada notifikasi</h2><p>Informasi sesi dan tugas akan muncul di sini saat ada aktivitas baru.</p></div>
                @endforelse
                <p class="notification-filter-empty" data-notification-filter-empty hidden>Tidak ada notifikasi pada kategori ini.</p>
            </div>
        </div>
        <aside class="notification-sidebar">
            <button type="button" class="notification-unread-summary" data-mark-all-read><span class="notification-envelope"><x-icon name="mail"/>@if ($notification_unread_count)<i></i>@endif</span><strong><span data-unread-count>{{ $notification_unread_count }}</span> Notifikasi Belum Dibaca</strong><x-icon name="chevron-right"/></button>
            <section class="notification-filter-panel"><h2>Filter Notifikasi</h2>
                @foreach (['all' => ['grid', 'Semua Notifikasi'], 'sessions' => ['calendar', 'Sesi Belajar'], 'assignments' => ['check', 'Tugas & Penilaian'], 'messages' => ['chat', 'Pesan'], 'system' => ['sparkle', 'Sistem']] as $key => [$icon, $label])
                    <button type="button" class="notification-filter-choice {{ $key === 'all' ? 'is-active' : '' }}" data-notification-filter="{{ $key }}"><x-icon name="{{ $icon }}"/><span>{{ $label }}</span><small>{{ $notification_category_counts[$key] }}</small></button>
                @endforeach
            </section>
            <section class="notification-activate-card"><div class="notification-envelope-art"><x-icon name="mail"/><span></span></div><h2>Aktifkan Notifikasi</h2><p>Nyalakan notifikasi agar kamu tidak ketinggalan informasi penting terkait pembelajaran.</p><label class="notification-switch"><input type="checkbox" role="switch" aria-label="Aktifkan notifikasi" data-notification-preference {{ $notification_preference_enabled ? 'checked' : '' }}><span></span></label></section>
        </aside>
    </div>
</section>

<section class="dashboard-page spa-page" data-spa-page="history" hidden>
    <div class="history-heading"><div><h1>Riwayat <em>Sesi</em></h1><p>Lihat kembali seluruh sesi belajar yang telah kamu ikuti bersama tutor.</p></div></div>
    <div class="history-layout">
        <div class="history-main-column">
            <section class="history-hero"><div><h2>Rekam Jejak Perjalanan<br>Belajarmu</h2><p>Pelajari kembali materi, lihat catatan, dan pantau perkembangan belajarmu dari setiap sesi.</p></div><div class="history-hero-art" aria-hidden="true"><span class="history-book history-book-one"></span><span class="history-book history-book-two"></span><div class="history-calendar"><i></i><i></i><i></i><b>✓</b><b>✓</b><span></span><span></span></div><div class="history-clock"><x-icon name="clock"/></div></div></section>
            <div class="history-stat-grid" aria-label="Statistik riwayat sesi">
                <article class="history-stat"><span class="history-stat-icon tone-blue"><x-icon name="calendar"/></span><div><strong>{{ $session_history_total }}</strong><span>Total Sesi</span></div></article>
                <article class="history-stat"><span class="history-stat-icon tone-green"><x-icon name="check"/></span><div><strong>{{ $session_history_completed }}</strong><span>Sesi Selesai</span></div></article>
                <article class="history-stat"><span class="history-stat-icon tone-amber"><x-icon name="clock"/></span><div><strong>{{ $session_history_in_progress }}</strong><span>Sesi Berlangsung</span></div></article>
                <article class="history-stat"><span class="history-stat-icon tone-red"><x-icon name="x"/></span><div><strong>{{ $session_history_cancelled }}</strong><span>Sesi Dibatalkan</span></div></article>
            </div>
            <nav class="history-tabs" aria-label="Filter status riwayat sesi">
                <button type="button" class="is-active" aria-pressed="true" data-history-status="all"><x-icon name="list"/>Semua Sesi</button>
                <button type="button" aria-pressed="false" data-history-status="completed"><x-icon name="check"/>Selesai</button>
                <button type="button" aria-pressed="false" data-history-status="in_progress"><x-icon name="clock"/>Berlangsung</button>
                <button type="button" aria-pressed="false" data-history-status="cancelled"><x-icon name="x"/>Dibatalkan</button>
            </nav>
            <div class="history-controls">
                <label class="history-search"><x-icon name="search"/><span class="sr-only">Cari riwayat sesi</span><input type="search" placeholder="Cari sesi, tutor, atau mata pelajaran..." data-history-search></label>
                <label class="history-select"><x-icon name="book"/><span class="sr-only">Mata pelajaran</span><select data-history-subject><option value="all">Semua Mata Pelajaran</option>@foreach ($session_history_subjects as $subject)<option value="{{ $subject }}">{{ $subject }}</option>@endforeach</select><x-icon name="chevron-down"/></label>
                <label class="history-select"><x-icon name="calendar"/><span class="sr-only">Periode sesi</span><select data-history-period><option value="all">Semua Periode</option><option value="7">7 hari terakhir</option><option value="30">30 hari terakhir</option><option value="90">3 bulan terakhir</option><option value="365">1 tahun terakhir</option></select><x-icon name="chevron-down"/></label>
            </div>
            <div class="history-list-heading"><h2>Daftar Riwayat Sesi</h2><label>Urutkan: <select data-history-sort aria-label="Urutkan riwayat sesi"><option value="newest">Terbaru</option><option value="oldest">Terlama</option></select></label><span data-history-result-count>{{ $session_history_total }} sesi</span></div>
            <div class="history-session-list" data-history-list>
                @forelse ($session_history as $index => $session)
                    <article class="history-session-card" data-history-card data-id="{{ $session->id }}" data-status="{{ $session->display_status }}" data-tutor="{{ $session->tutor_name }}" data-subject="{{ $session->subject }}" data-date="{{ $session->starts_at->timestamp }}" data-search="{{ \Illuminate\Support\Str::lower($session->tutor_name.' '.$session->subject.' '.$session->topic.' '.$session->tutor_university) }}" data-detail-url="{{ route('dashboard.sessions.show', ['learningSession' => $session->id]) }}" style="--history-delay:{{ min($index, 7) * 35 }}ms">
                        <span class="history-avatar" aria-hidden="true">{{ $session->initials }}</span>
                        <div class="history-tutor"><strong>{{ $session->tutor_name }}</strong>@if ($session->tutor_university)<span>{{ $session->tutor_university }}</span>@endif<p>{{ $session->subject }}</p></div>
                        <div class="history-schedule"><span><x-icon name="calendar"/>{{ $session->date_label }}</span><span><x-icon name="clock"/>{{ $session->time_label }} ({{ $session->duration_minutes }} menit)</span><span><x-icon name="video"/>Online ({{ $session->meeting_platform }})</span></div>
                        <span class="history-status history-status-{{ $session->display_status }}"><x-icon name="{{ $session->display_status === 'completed' ? 'check' : ($session->display_status === 'cancelled' ? 'x' : 'clock') }}"/>{{ $session->status_label }}</span>
                        <div class="history-card-actions"><button type="button" class="button button-outline" data-history-detail>Lihat Detail</button><button type="button" class="button button-outline" disabled title="Materi atau catatan belum dihubungkan ke sesi ini."><x-icon name="document"/>Lihat Materi</button></div>
                    </article>
                @empty
                    <div class="history-empty" data-history-empty><span><x-icon name="calendar"/></span><h3>Belum ada riwayat sesi</h3><p>Sesi yang kamu ikuti akan tercatat di sini setelah dijadwalkan.</p><button type="button" class="button button-primary" data-history-browse>Temukan Tutor <x-icon name="arrow-right"/></button></div>
                @endforelse
                <div class="history-empty history-filter-empty" data-history-filter-empty hidden><span><x-icon name="search"/></span><h3>Sesi tidak ditemukan</h3><p>Coba ubah pencarian atau filter riwayatmu.</p></div>
            </div>
            <nav class="history-pagination" data-history-pagination aria-label="Halaman riwayat sesi"></nav>
        </div>
        <aside class="history-sidebar">
            <section class="history-side-card history-total-card"><h2>Total Sesi Belajar</h2><div class="history-total-content"><span class="history-total-icon"><x-icon name="calendar"/></span><strong>{{ $session_history_total }} <small>Sesi</small></strong><div class="history-mini-chart" aria-label="Aktivitas sesi dalam enam bulan terakhir">@foreach ($session_history_bars as $bar)<i style="--bar-height:{{ $bar['height'] }}%" title="{{ $bar['label'] }}: {{ $bar['count'] }} sesi"></i>@endforeach</div></div></section>
            <section class="history-side-card history-progress-card"><h2>Progress Belajar</h2><div class="history-progress-content"><div class="history-progress-ring" style="--history-progress:{{ $session_history_progress }}%"><strong>{{ $session_history_progress }}%</strong></div><div><p>{{ $session_history_completed }} dari {{ $session_history_total }} sesi selesai</p><span class="history-progress-track"><i style="width:{{ $session_history_progress }}%"></i></span></div></div></section>
            <section class="history-side-card history-favorites-card"><div class="history-side-heading"><h2>Tutor Favorit</h2><button type="button" data-history-open-tutors>Lihat Semua <x-icon name="arrow-right"/></button></div>@forelse ($session_history_favorites as $favorite)<div class="history-favorite"><span class="history-favorite-avatar">{{ $favorite['initials'] }}</span><span><strong>{{ $favorite['name'] }}</strong><small>{{ $favorite['subject'] }} · {{ $favorite['sessions'] }} sesi</small></span><x-icon name="chevron-right"/></div>@empty<p class="history-side-empty">Tutor yang pernah mengajar akan muncul di sini.</p>@endforelse</section>
            <section class="history-promo-card"><span class="history-promo-icon"><x-icon name="calendar"/><i><x-icon name="clock"/></i></span><h2>Ingin Belajar Lagi?</h2><p>Jadwalkan sesi baru dengan tutor favoritmu dan lanjutkan perkembangan belajarmu!</p><button type="button" class="button button-primary" data-history-browse>Ajukan Sesi Belajar Baru <x-icon name="arrow-right"/></button></section>
        </aside>
    </div>
    <dialog class="history-detail-dialog" data-history-detail-dialog aria-labelledby="history-detail-title"><button type="button" class="history-dialog-close" aria-label="Tutup" data-history-dialog-close>×</button><div data-history-detail-content><p>Memuat detail sesi…</p></div></dialog>
</section>
