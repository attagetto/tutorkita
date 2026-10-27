@extends('layouts.dashboard')

@section('title', 'Mata Pelajaran')

@section('content')
    <div class="dashboard-page subjects-page">
        <section class="subjects-intro animate-in" aria-labelledby="subjects-heading">
            <div><span class="eyebrow"><span class="eyebrow-dot"></span> Temukan cara belajar yang cocok</span><h1 id="subjects-heading">Mata Pelajaran</h1><p>Pilih pelajaran yang ingin kamu dalami. Belajar bersama tutor relawan dan berkembang setahap demi setahap.</p></div>
            <div class="subjects-intro-art" aria-hidden="true"><x-icon name="book-open" /></div>
        </section>

        <section class="panel subjects-panel" aria-label="Daftar mata pelajaran">
            <div class="panel-heading subjects-heading-row"><div><span class="section-kicker">RUANG BELAJAR</span><h2>Pelajaran pilihanmu</h2><p>Materi dan tutor tersedia untuk berbagai jenjang.</p></div><label class="filter-select-wrap"><span class="sr-only">Filter berdasarkan jenjang</span><select id="subject-level-filter" class="filter-select"><option value="all">Semua jenjang</option><option value="SD">SD</option><option value="SMP">SMP</option><option value="SMA">SMA</option></select><x-icon name="chevron-down" class="icon-sm" /></label></div>
            <div class="subjects-grid" data-subject-grid>
                @foreach ($subjects as $index => $subject)
                    <x-subject-card :subject="$subject" :index="$index" />
                @endforeach
                <p class="empty-state subjects-empty" data-subject-empty hidden>Tidak ada mata pelajaran yang sesuai dengan pencarianmu.</p>
            </div>
        </section>
    </div>
@endsection
