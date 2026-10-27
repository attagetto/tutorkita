<?php

namespace App\Support;

class LandingPageData
{
    public static function make(): array
    {
        return [
            'features' => [
                ['icon' => 'search', 'title' => 'Cari Tutor', 'description' => 'Temukan tutor relawan untuk mata pelajaran yang ingin kamu pelajari.'],
                ['icon' => 'calendar', 'title' => 'Jadwal Fleksibel', 'description' => 'Atur jadwal belajar yang cocok untukmu dan tutor.'],
                ['icon' => 'book-open', 'title' => 'Materi Belajar', 'description' => 'Akses materi, latihan soal, dan sumber belajar untuk mendukung proses belajarmu.'],
                ['icon' => 'trend', 'title' => 'Pantau Perkembangan', 'description' => 'Pantau kemajuan belajarmu melalui catatan setiap sesi.'],
            ],
            'tutors' => [
                ['name' => 'Nadia Putri', 'university' => 'Universitas Indonesia', 'major' => 'S1 Matematika', 'subject' => 'Matematika', 'initials' => 'NP', 'description' => 'Membantu siswa memahami konsep matematika dan berlatih langkah demi langkah.', 'rating' => '4.9', 'availability' => 'Tersedia hari ini'],
                ['name' => 'Rizky Aditya', 'university' => 'Institut Teknologi Bandung', 'major' => 'S1 Informatika', 'subject' => 'Pemrograman', 'initials' => 'RA', 'description' => 'Mendampingi siswa belajar pemrograman dari dasar lewat contoh yang mudah diikuti.', 'rating' => '4.8', 'availability' => 'Tersedia besok'],
                ['name' => 'Sarah Amelia', 'university' => 'Telkom University', 'major' => 'S1 Sistem Informasi', 'subject' => 'Bahasa Inggris', 'initials' => 'SA', 'description' => 'Berlatih percakapan dan kosakata bahasa Inggris untuk membangun rasa percaya diri.', 'rating' => '5.0', 'availability' => 'Tersedia hari ini'],
                ['name' => 'Dimas Prakoso', 'university' => 'Universitas Gadjah Mada', 'major' => 'S1 Fisika', 'subject' => 'Fisika', 'initials' => 'DP', 'description' => 'Menjelaskan konsep fisika dengan contoh dari kehidupan sehari-hari.', 'rating' => '4.9', 'availability' => 'Tersedia minggu ini'],
                ['name' => 'Alya Safitri', 'university' => 'Universitas Padjadjaran', 'major' => 'S1 Pendidikan Bahasa Inggris', 'subject' => 'Bahasa Inggris', 'initials' => 'AS', 'description' => 'Mendampingi latihan bahasa Inggris dengan suasana santai dan terarah.', 'rating' => '4.8', 'availability' => 'Tersedia hari ini'],
            ],
        ];
    }
}
