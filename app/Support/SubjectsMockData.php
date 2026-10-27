<?php

namespace App\Support;

class SubjectsMockData
{
    public static function make(): array
    {
        return [
            ['name' => 'Matematika', 'category' => 'Eksakta', 'levels' => ['SMP', 'SMA'], 'progress' => 78, 'icon' => 'book', 'tutor_count' => 8, 'tone' => 'blue', 'description' => 'Latih logika dan pecahkan soal dengan langkah yang lebih mudah dipahami.'],
            ['name' => 'Bahasa Inggris', 'category' => 'Bahasa', 'levels' => ['SD', 'SMP', 'SMA'], 'progress' => 84, 'icon' => 'chat', 'tutor_count' => 6, 'tone' => 'sky', 'description' => 'Bangun kepercayaan diri lewat percakapan, kosakata, dan latihan sehari-hari.'],
            ['name' => 'Ilmu Pengetahuan Alam', 'category' => 'Eksakta', 'levels' => ['SD', 'SMP'], 'progress' => 62, 'icon' => 'sparkle', 'tutor_count' => 5, 'tone' => 'navy', 'description' => 'Kenali alam dan sains melalui penjelasan yang dekat dengan kehidupan.'],
            ['name' => 'Programming', 'category' => 'Teknologi', 'levels' => ['SMP', 'SMA'], 'progress' => 65, 'icon' => 'code', 'tutor_count' => 4, 'tone' => 'soft', 'description' => 'Mulai belajar logika pemrograman dan ciptakan proyek pertamamu.'],
            ['name' => 'Bahasa Indonesia', 'category' => 'Bahasa', 'levels' => ['SD', 'SMP', 'SMA'], 'progress' => 71, 'icon' => 'book-open', 'tutor_count' => 7, 'tone' => 'blue', 'description' => 'Asah kemampuan membaca, menulis, dan menyampaikan gagasan dengan baik.'],
            ['name' => 'Fisika', 'category' => 'Eksakta', 'levels' => ['SMA'], 'progress' => 54, 'icon' => 'trend', 'tutor_count' => 3, 'tone' => 'sky', 'description' => 'Pahami konsep dan hubungan sebab-akibat di balik berbagai fenomena.'],
        ];
    }
}
