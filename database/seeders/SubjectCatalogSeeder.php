<?php

namespace Database\Seeders;

use App\Models\GradeLevel;
use App\Models\Subject;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SubjectCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $levels = collect([
            ['SD', 1], ['SMP', 2], ['SMA/SMK', 3],
        ])->mapWithKeys(fn (array $level): array => [
            $level[0] => GradeLevel::query()->updateOrCreate(
                ['name' => $level[0]],
                ['slug' => Str::slug($level[0]), 'sort_order' => $level[1]],
            )->id,
        ]);

        $catalog = [
            ['Matematika', 'Eksakta', 'Latih logika dan pecahkan soal dengan langkah yang mudah dipahami.', 'book', 'blue', ['SD', 'SMP', 'SMA/SMK']],
            ['IPA', 'Eksakta', 'Pelajari sains dan fenomena alam melalui materi yang dekat dengan kehidupan.', 'sparkle', 'navy', ['SD', 'SMP', 'SMA/SMK']],
            ['IPS', 'Sosial', 'Pahami lingkungan sosial, sejarah, dan kehidupan bermasyarakat.', 'globe', 'sky', ['SD', 'SMP', 'SMA/SMK']],
            ['Bahasa Indonesia', 'Bahasa', 'Asah kemampuan membaca, menulis, dan menyampaikan gagasan.', 'book-open', 'blue', ['SD', 'SMP', 'SMA/SMK']],
            ['Bahasa Inggris', 'Bahasa', 'Bangun kepercayaan diri melalui kosakata, tata bahasa, dan percakapan.', 'chat', 'sky', ['SD', 'SMP', 'SMA/SMK']],
            ['Biologi', 'Eksakta', 'Kenali makhluk hidup, tubuh manusia, dan ekosistem.', 'sparkle', 'green', ['SMP', 'SMA/SMK']],
            ['Fisika', 'Eksakta', 'Pahami konsep dan hubungan sebab-akibat di balik berbagai fenomena.', 'trend', 'sky', ['SMP', 'SMA/SMK']],
            ['Kimia', 'Eksakta', 'Pelajari zat, perubahan, dan reaksi kimia secara bertahap.', 'flask', 'green', ['SMP', 'SMA/SMK']],
            ['Sejarah', 'Sosial', 'Telusuri peristiwa dan perubahan yang membentuk kehidupan masa kini.', 'clock', 'amber', ['SD', 'SMP', 'SMA/SMK']],
            ['PPKn', 'Sosial', 'Kenali nilai Pancasila, kewarganegaraan, dan kehidupan berbangsa.', 'check', 'soft', ['SD', 'SMP', 'SMA/SMK']],
            ['Informatika', 'Teknologi', 'Kembangkan logika digital dan pemahaman teknologi informasi.', 'code', 'navy', ['SMP', 'SMA/SMK']],
        ];

        foreach ($catalog as [$name, $category, $description, $icon, $tone, $levelNames]) {
            $slug = Str::slug($name);
            $subject = Subject::query()->updateOrCreate(
                ['slug' => $slug],
                compact('name', 'slug', 'category', 'description', 'icon', 'tone'),
            );
            $subject->gradeLevels()->sync(collect($levelNames)->map(fn (string $level): int => $levels[$level])->all());
        }
    }
}
