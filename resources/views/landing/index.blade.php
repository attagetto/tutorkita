@extends('layouts.landing')

@section('content')
    <section class="landing-hero" aria-labelledby="hero-heading">
        <div class="hero-inner">
            <div class="hero-copy">
                <span class="hero-badge"><i></i>Bimbingan Belajar Gratis</span>
                <h1 id="hero-heading">Belajar Bareng,<br><span>Raih Mimpi Lebih Tinggi</span></h1>
                <p>TutorKita menghadirkan bimbingan belajar gratis bersama mahasiswa relawan bagi siswa SD, SMP, dan SMA dari keluarga yang membutuhkan.</p>
                <div class="hero-stats" aria-label="Dampak TutorKita">
                    <div><strong>120+</strong><span>Tutor Relawan</span></div>
                    <div><strong>350+</strong><span>Siswa Terbantu</span></div>
                    <div><strong>900+</strong><span>Sesi Belajar</span></div>
                </div>
            </div>
            <div class="hero-photo"><img src="{{ asset('assets/images/tutorkita-hero-reference.jpg') }}" alt="Seorang tutor mendampingi anak-anak belajar di meja bersama" fetchpriority="high"></div>
        </div>
    </section>

    <section class="features-section" id="fitur" aria-labelledby="features-heading">
        <div class="section-heading" data-reveal><h2 id="features-heading">Belajar Jadi Lebih Mudah</h2><p>Dukungan belajar yang sesuai untuk setiap siswa.</p></div>
        <div class="feature-grid">
            @foreach ($features as $index => $feature)
                <x-landing.feature :feature="$feature" :index="$index" />
            @endforeach
        </div>
    </section>

    <section class="landing-cta" id="mulai-belajar" aria-labelledby="cta-heading" data-reveal>
        <div class="cta-content"><h2 id="cta-heading">Setiap Anak Berhak Mendapat<br>Kesempatan Belajar</h2><p>Mari wujudkan kesempatan belajar yang lebih merata bersama TutorKita.</p><a class="landing-button button-white" href="{{ route('login') }}">Mulai Belajar Gratis</a></div>
    </section>

    <section class="tutors-section" id="tutors" aria-labelledby="tutors-heading">
        <div class="tutors-heading-row" data-reveal><div class="section-heading"><h2 id="tutors-heading">Kenali Tutor Relawan Kami</h2><p>Mahasiswa dari berbagai jurusan siap mendampingi belajarmu.</p></div><label class="landing-tutor-search"><x-icon name="search" /><span class="sr-only">Cari tutor berdasarkan nama atau mata pelajaran</span><input type="search" placeholder="Cari tutor atau mata pelajaran" data-landing-tutor-search></label></div>
        <div class="landing-tutor-grid">
            @foreach ($tutors as $index => $tutor)
                <x-landing.tutor-card :tutor="$tutor" :index="$index" />
            @endforeach
            <p class="landing-tutor-empty" data-landing-tutor-empty hidden>Tutor tidak ditemukan. Coba kata kunci lain.</p>
        </div>
    </section>

    <section class="about-section" id="about" aria-labelledby="about-heading" data-reveal>
        <div class="about-copy"><span class="about-kicker">BELAJAR BERSAMA, TUMBUH BERSAMA</span><h2 id="about-heading">Tentang TutorKita</h2><p>Kami percaya setiap anak berhak mendapat kesempatan belajar. TutorKita mempertemukan siswa dengan mahasiswa relawan dalam suasana belajar yang ramah dan saling mendukung.</p></div>
        <a class="about-link" href="#tutors">Lihat tutor relawan <x-icon name="arrow-right" class="icon-sm" /></a>
    </section>
@endsection
