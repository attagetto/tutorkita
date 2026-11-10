<?php

namespace Database\Seeders;

use App\Models\LearningMaterial;
use Illuminate\Database\Seeder;

class LearningMaterialSeeder extends Seeder
{
    public function run(): void
    {
        $materials = [
            ['persamaan-kuadrat', 'Persamaan Kuadrat', 'Matematika', 'Aljabar', 'SMP', 'Nadia Putri', 'module_pdf', 'Pelajari bentuk umum persamaan kuadrat, cara memfaktorkan, dan langkah memeriksa akar dengan substitusi.', 'graph', 15, null, 48, 2],
            ['sistem-pernapasan-manusia', 'Sistem Pernapasan Manusia', 'IPA', 'Sistem tubuh manusia', 'SMP', 'Salsa Khairunisa', 'video', 'Kenali organ pernapasan dan alur pertukaran oksigen melalui penjelasan sistem tubuh manusia.', 'lungs', null, 750, 36, 5],
            ['teks-argumentasi', 'Teks Argumentasi', 'Bahasa Indonesia', 'Menulis argumentasi', 'SMA/SMK', 'Nadia Safitri', 'material_pdf', 'Rangkuman struktur, fakta pendukung, dan cara menyusun argumen yang runtut dan mudah dipahami.', 'book', 10, null, 29, 24],
            ['simple-present-tense', 'Simple Present Tense', 'Bahasa Inggris', 'Grammar dasar', 'SMP', 'Fadli Ibrahim', 'video', 'Latihan penggunaan simple present untuk rutinitas, fakta umum, serta bentuk kalimat positif dan negatif.', 'english', null, 645, 64, 7],
            ['bangun-ruang-sisi-datar', 'Bangun Ruang Sisi Datar', 'Matematika', 'Geometri', 'SMP', 'Rizky Maulana', 'material_pdf', 'Pelajari unsur kubus dan balok serta cara menghitung luas permukaan dan volumenya.', 'cube', 18, null, 41, 20],
            ['fotosintesis', 'Fotosintesis', 'IPA', 'Tumbuhan', 'SMP', 'Citra Lestari', 'module_pdf', 'Ringkasan proses fotosintesis, bahan yang diperlukan tumbuhan, dan faktor yang memengaruhi prosesnya.', 'plant', 12, null, 33, 22],
            ['masuknya-hindu-buddha', 'Masuknya Hindu-Buddha di Indonesia', 'Sejarah', 'Sejarah Indonesia', 'SMA/SMK', 'Ahmad Fauzi', 'summary', 'Ringkasan jalur penyebaran, bukti sejarah, dan perkembangan kerajaan bercorak Hindu-Buddha di Indonesia.', 'temple', 9, null, 22, 30],
            ['nilai-pancasila', 'Nilai-nilai Pancasila dalam Kehidupan Sehari-hari', 'PPKn', 'Pancasila', 'SMP', 'Damar Prasetyo', 'exercise', 'Latihan mengenali penerapan nilai Pancasila melalui contoh situasi di rumah, sekolah, dan masyarakat.', 'civic', 9, null, 18, 31],
        ];

        foreach ($materials as [$slug, $title, $subject, $topic, $level, $tutor, $type, $description, $art, $pages, $duration, $views, $hoursAgo]) {
            LearningMaterial::query()->firstOrCreate(
                ['slug' => $slug],
                [
                    'title' => $title,
                    'subject' => $subject,
                    'topic' => $topic,
                    'grade_level' => $level,
                    'tutor_name' => $tutor,
                    'material_type' => $type,
                    'description' => $description,
                    'illustration' => $art,
                    'page_count' => $pages,
                    'duration_seconds' => $duration,
                    'view_count' => $views,
                    'is_published' => true,
                    'published_at' => now()->subHours($hoursAgo),
                    'file_path' => null,
                    'video_url' => null,
                ],
            );
        }
    }
}
