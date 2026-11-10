<?php

namespace App\Support;

use Carbon\CarbonImmutable;

class DashboardMockData
{
    public static function make(): array
    {
        $today = CarbonImmutable::today();
        $catalogProfiles = [
            ['Alya Putri', 'Universitas Indonesia', 'Pendidikan Matematika', ['Matematika', 'IPA'], ['SD', 'SMP'], 'Jakarta', 'Senin', 'Sore', 4.9, 120, true],
            ['Rizky Maulana', 'Institut Teknologi Bandung', 'Teknik Informatika', ['Matematika', 'Bahasa Indonesia'], ['SD', 'SMP'], 'Bandung', 'Selasa', 'Malam', 4.8, 98, true],
            ['Salsa Khairunisa', 'Universitas Gadjah Mada', 'Pendidikan Biologi', ['IPA', 'Biologi'], ['SD', 'SMP'], 'Yogyakarta', 'Rabu', 'Pagi', 4.9, 110, true],
            ['Fadli Ibrahim', 'Universitas Airlangga', 'Pendidikan Bahasa Inggris', ['Bahasa Inggris'], ['SD', 'SMP'], 'Surabaya', 'Kamis', 'Siang', 4.7, 85, false],
            ['Nadia Safitri', 'Universitas Diponegoro', 'Pendidikan Bahasa Indonesia', ['Bahasa Indonesia', 'Sastra'], ['SD', 'SMP'], 'Semarang', 'Jumat', 'Sore', 4.8, 96, true],
            ['Damar Prasetyo', 'Universitas Brawijaya', 'Pendidikan Fisika', ['Fisika', 'Matematika'], ['SMP', 'SMA/SMK'], 'Malang', 'Sabtu', 'Pagi', 4.7, 72, true],
            ['Citra Lestari', 'Universitas Padjadjaran', 'Pendidikan Kimia', ['Kimia', 'IPA'], ['SMP', 'SMA/SMK'], 'Bandung', 'Minggu', 'Siang', 4.8, 88, true],
            ['Ahmad Fauzi', 'Universitas Negeri Malang', 'Pendidikan Sejarah', ['Sejarah', 'PPKn'], ['SD', 'SMP'], 'Malang', 'Senin', 'Malam', 4.6, 65, true],
            ['Putri Maharani', 'Universitas Sebelas Maret', 'Pendidikan Matematika', ['Matematika', 'Fisika'], ['SMP', 'SMA/SMK'], 'Surakarta', 'Selasa', 'Pagi', 4.9, 104, true],
            ['Bima Saputra', 'Universitas Negeri Jakarta', 'Pendidikan Ekonomi', ['IPS', 'Ekonomi'], ['SMP', 'SMA/SMK'], 'Jakarta', 'Rabu', 'Sore', 4.7, 77, true],
            ['Intan Permata', 'Universitas Negeri Yogyakarta', 'Pendidikan Bahasa Inggris', ['Bahasa Inggris'], ['SD', 'SMP'], 'Yogyakarta', 'Kamis', 'Malam', 5.0, 132, true],
            ['Rafi Hidayat', 'Institut Pertanian Bogor', 'Statistika', ['Matematika', 'IPA'], ['SMP', 'SMA/SMK'], 'Bogor', 'Jumat', 'Siang', 4.8, 91, false],
            ['Maya Anggraini', 'Universitas Pendidikan Indonesia', 'Pendidikan Biologi', ['Biologi', 'IPA'], ['SD', 'SMP'], 'Bandung', 'Sabtu', 'Sore', 4.9, 115, true],
            ['Fajar Nugroho', 'Universitas Jember', 'Pendidikan Fisika', ['Fisika', 'Matematika'], ['SMP', 'SMA/SMK'], 'Jember', 'Minggu', 'Pagi', 4.6, 59, true],
            ['Dewi Kartika', 'Universitas Hasanuddin', 'Pendidikan Bahasa Indonesia', ['Bahasa Indonesia', 'IPS'], ['SD', 'SMP'], 'Makassar', 'Senin', 'Siang', 4.8, 83, true],
            ['Yoga Pratama', 'Universitas Telkom', 'Informatika', ['Matematika', 'Programming'], ['SMP', 'SMA/SMK'], 'Bandung', 'Selasa', 'Malam', 4.7, 70, true],
            ['Ayu Wulandari', 'Universitas Negeri Surabaya', 'Pendidikan Kimia', ['Kimia', 'IPA'], ['SMP', 'SMA/SMK'], 'Surabaya', 'Rabu', 'Pagi', 4.9, 101, false],
            ['Rendra Kurniawan', 'Universitas Lampung', 'Pendidikan Sejarah', ['Sejarah', 'IPS'], ['SMP', 'SMA/SMK'], 'Bandar Lampung', 'Kamis', 'Sore', 4.6, 54, true],
            ['Nisa Rahmawati', 'Universitas Negeri Semarang', 'Pendidikan Matematika', ['Matematika'], ['SD', 'SMP'], 'Semarang', 'Jumat', 'Malam', 4.8, 93, true],
            ['Ardiansyah Putra', 'Universitas Sriwijaya', 'Pendidikan Biologi', ['Biologi', 'IPA'], ['SMP', 'SMA/SMK'], 'Palembang', 'Sabtu', 'Siang', 4.7, 68, true],
            ['Laras Puspita', 'Universitas Udayana', 'Pendidikan Bahasa Inggris', ['Bahasa Inggris'], ['SD', 'SMP'], 'Denpasar', 'Minggu', 'Sore', 5.0, 126, true],
            ['Kevin Wijaya', 'Universitas Kristen Petra', 'Pendidikan Ekonomi', ['IPS', 'Ekonomi'], ['SMP', 'SMA/SMK'], 'Surabaya', 'Senin', 'Pagi', 4.7, 74, true],
            ['Siti Aisyah', 'Universitas Negeri Makassar', 'Pendidikan Kimia', ['Kimia', 'Matematika'], ['SMP', 'SMA/SMK'], 'Makassar', 'Selasa', 'Siang', 4.8, 89, true],
            ['Galih Prabowo', 'Universitas Negeri Padang', 'Pendidikan Fisika', ['Fisika', 'IPA'], ['SMP', 'SMA/SMK'], 'Padang', 'Rabu', 'Malam', 4.6, 61, false],
        ];
        $catalogTutors = array_map(static function (array $profile): array {
            [$name, $university, $major, $subjects, $levels, $location, $day, $period, $rating, $reviews, $available] = $profile;
            $nameParts = preg_split('/\s+/', $name, -1, PREG_SPLIT_NO_EMPTY) ?: [];

            return [
                'name' => $name,
                'university' => $university,
                'major' => $major,
                'subjects' => $subjects,
                'levels' => $levels,
                'location' => $location,
                'day' => $day,
                'period' => $period,
                'rating' => $rating,
                'reviews' => $reviews,
                'available' => $available,
                'initials' => mb_strtoupper(mb_substr($nameParts[0] ?? '', 0, 1).mb_substr($nameParts[1] ?? '', 0, 1)),
                'verified' => true,
                'sessions' => $reviews + 18,
                'created_order' => count($profile),
            ];
        }, $catalogProfiles);

        return [
            'user' => [
                'name' => 'Gusti',
                'full_name' => 'Gusti Pratama',
                'email' => 'gusti@example.com',
                'initials' => 'GP',
                'level' => 'Siswa kelas 11',
            ],
            'stats' => [
                ['label' => 'Mata Pelajaran Aktif', 'value' => '4', 'detail' => '2 sesi minggu ini', 'icon' => 'book', 'tone' => 'blue', 'target' => 'mata-pelajaran'],
                ['label' => 'Jadwal Hari Ini', 'value' => '3', 'detail' => 'Sesi berikutnya 10.00', 'icon' => 'calendar', 'tone' => 'sky', 'target' => 'jadwal'],
                ['label' => 'Sesi Belajar', 'value' => '18', 'detail' => '12 sesi selesai', 'icon' => 'play', 'tone' => 'amber', 'target' => 'progress'],
                ['label' => 'Progress Belajar', 'value' => '72%', 'detail' => '+8% dari minggu lalu', 'icon' => 'trend', 'tone' => 'green', 'target' => 'progress'],
            ],
            'schedules' => [
                ['subject' => 'Matematika', 'topic' => 'Persamaan kuadrat', 'tutor' => 'Nadia Putri', 'initials' => 'NP', 'time' => '10.00 - 11.00', 'date' => $today->format('Y-m-d'), 'date_label' => 'Hari ini', 'status' => 'Akan dimulai', 'status_key' => 'today', 'color' => 'blue'],
                ['subject' => 'Programming', 'topic' => 'Dasar JavaScript', 'tutor' => 'Rizky Aditya', 'initials' => 'RA', 'time' => '13.00 - 14.00', 'date' => $today->format('Y-m-d'), 'date_label' => 'Hari ini', 'status' => 'Akan dimulai', 'status_key' => 'today', 'color' => 'purple'],
                ['subject' => 'Bahasa Inggris', 'topic' => 'Everyday conversation', 'tutor' => 'Sarah Amelia', 'initials' => 'SA', 'time' => '16.00 - 17.00', 'date' => $today->format('Y-m-d'), 'date_label' => 'Hari ini', 'status' => 'Akan dimulai', 'status_key' => 'today', 'color' => 'amber'],
                ['subject' => 'Fisika', 'topic' => 'Gerak dan gaya', 'tutor' => 'Dimas Prakoso', 'initials' => 'DP', 'time' => '09.00 - 10.00', 'date' => $today->addDay()->format('Y-m-d'), 'date_label' => 'Besok', 'status' => 'Terjadwal', 'status_key' => 'upcoming', 'color' => 'green'],
                ['subject' => 'Matematika', 'topic' => 'Latihan limit fungsi', 'tutor' => 'Nadia Putri', 'initials' => 'NP', 'time' => '14.00 - 15.00', 'date' => $today->addDays(2)->format('Y-m-d'), 'date_label' => $today->addDays(2)->locale('id')->isoFormat('dddd, D MMM'), 'status' => 'Terjadwal', 'status_key' => 'upcoming', 'color' => 'blue'],
            ],
            'tutors' => [
                ['name' => 'Nadia Putri', 'subject' => 'Matematika', 'rating' => '4.9', 'sessions' => '120+ sesi', 'description' => 'Belajar matematika jadi lebih mudah dengan latihan yang terarah.', 'initials' => 'NP', 'tone' => 'blue', 'availability' => 'Tersedia hari ini'],
                ['name' => 'Rizky Aditya', 'subject' => 'Programming', 'rating' => '4.8', 'sessions' => '86 sesi', 'description' => 'Teman belajar coding dari dasar sampai siap membuat proyek.', 'initials' => 'RA', 'tone' => 'purple', 'availability' => 'Tersedia besok'],
                ['name' => 'Sarah Amelia', 'subject' => 'Bahasa Inggris', 'rating' => '5.0', 'sessions' => '94 sesi', 'description' => 'Latihan percakapan santai agar makin percaya diri berbahasa Inggris.', 'initials' => 'SA', 'tone' => 'amber', 'availability' => 'Tersedia hari ini'],
            ],
            'catalog_tutors' => $catalogTutors,
            'progress' => [
                'weekly' => [
                    ['day' => 'Sen', 'value' => 48], ['day' => 'Sel', 'value' => 72], ['day' => 'Rab', 'value' => 55],
                    ['day' => 'Kam', 'value' => 88], ['day' => 'Jum', 'value' => 64], ['day' => 'Sab', 'value' => 100], ['day' => 'Min', 'value' => 36],
                ],
                'subjects' => [
                    ['name' => 'Matematika', 'value' => 78, 'color' => 'blue'],
                    ['name' => 'Programming', 'value' => 65, 'color' => 'purple'],
                    ['name' => 'Bahasa Inggris', 'value' => 84, 'color' => 'amber'],
                ],
            ],
            'notifications' => [
                ['title' => 'Sesi Matematika dimulai', 'detail' => 'Bersiap dengan Nadia pukul 10.00', 'time' => '15 menit lalu', 'unread' => true],
                ['title' => 'Progress mingguan diperbarui', 'detail' => 'Kamu sudah menyelesaikan 4 sesi minggu ini.', 'time' => 'Kemarin', 'unread' => true],
                ['title' => 'Tutor baru tersedia', 'detail' => 'Lihat tutor Programming yang direkomendasikan.', 'time' => '2 hari lalu', 'unread' => false],
            ],
            'today_label' => $today->locale('id')->isoFormat('dddd, D MMMM YYYY'),
        ];
    }
}
