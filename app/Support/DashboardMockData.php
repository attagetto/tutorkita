<?php

namespace App\Support;

use Carbon\CarbonImmutable;

class DashboardMockData
{
    public static function make(): array
    {
        $today = CarbonImmutable::today();

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
