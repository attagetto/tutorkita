<?php

namespace Tests\Feature;

use App\Models\LearningMaterial;
use App\Models\MaterialBookmark;
use App\Models\MaterialProgress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class LearningMaterialTest extends TestCase
{
    use RefreshDatabase;

    private string $userStorePath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userStorePath = storage_path('framework/testing/material-users-'.bin2hex(random_bytes(8)).'.json');
        config(['auth.providers.users.path' => $this->userStorePath]);
        Auth::forgetGuards();
        $this->seed(\Database\Seeders\SubjectCatalogSeeder::class);
    }

    protected function tearDown(): void
    {
        if (isset($this->userStorePath) && is_file($this->userStorePath)) {
            @unlink($this->userStorePath);
        }
        parent::tearDown();
    }

    public function test_material_dashboard_renders_seeded_materials_and_dynamic_progress(): void
    {
        $this->registerAndSignIn('Budi', 'budi@test.com');
        $this->seed(\Database\Seeders\LearningMaterialSeeder::class);
        $material = LearningMaterial::query()->where('slug', 'persamaan-kuadrat')->firstOrFail();

        $this->get('/dashboard#materials')->assertOk()
            ->assertSee('data-material-card', false)
            ->assertSee('data-material-subject="Matematika"', false)
            ->assertSee('Cari materi, judul, atau tutor...', false)
            ->assertSeeText('0 dari 8 materi selesai');

        $this->postJson(route('dashboard.materials.complete', $material))->assertOk();
        $this->get('/dashboard#materials')->assertOk()
            ->assertSeeText('1 dari 8 materi selesai')
            ->assertSee('style="--progress:13%"', false);
    }

    public function test_bookmarks_are_persistent_and_private_to_each_account(): void
    {
        $this->registerAndSignIn('Budi', 'budi@test.com');
        $this->seed(\Database\Seeders\LearningMaterialSeeder::class);
        $material = LearningMaterial::query()->firstOrFail();
        $studentId = (string) auth()->id();

        $this->postJson(route('dashboard.materials.bookmark', $material))
            ->assertOk()->assertJsonPath('bookmarked', true);
        $this->assertDatabaseHas('material_bookmarks', ['student_id' => $studentId, 'learning_material_id' => $material->id]);
        $this->get('/dashboard#materials')->assertOk()->assertSee('data-material-bookmark="'.$material->id.'"', false)
            ->assertSee('aria-label="Hapus bookmark" aria-pressed="true"', false);
        $this->postJson(route('dashboard.materials.bookmark', $material))->assertOk()->assertJsonPath('bookmarked', false);
        $this->assertDatabaseMissing('material_bookmarks', ['student_id' => $studentId, 'learning_material_id' => $material->id]);
        $this->postJson(route('dashboard.materials.bookmark', $material))->assertOk()->assertJsonPath('bookmarked', true);
        $this->post('/logout');

        $this->registerAndSignIn('Andi', 'andi@test.com');
        $response = $this->get('/dashboard#materials')->assertOk();
        $bookmarkButtonPattern = '/<button\b(?=[^>]*\bdata-material-bookmark="' . preg_quote((string) $material->id, '/') . '")(?=[^>]*\baria-pressed="true")[^>]*>/s';
        $this->assertDoesNotMatchRegularExpression($bookmarkButtonPattern, $response->getContent());
        $this->postJson(route('dashboard.materials.bookmark', $material))
            ->assertOk()->assertJsonPath('bookmarked', true);
        $this->assertDatabaseCount('material_bookmarks', 2);
    }

    public function test_opening_material_records_activity_without_marking_it_complete_and_private_file_is_not_faked(): void
    {
        $this->registerAndSignIn('Budi', 'budi@test.com');
        $this->seed(\Database\Seeders\LearningMaterialSeeder::class);
        $material = LearningMaterial::query()->firstOrFail();

        $this->getJson(route('dashboard.materials.show', $material))
            ->assertOk()->assertJsonPath('material.completed', false)
            ->assertJsonPath('material.file_url', null)
            ->assertJsonPath('material.video_url', null);
        $this->assertNotNull(MaterialProgress::query()->firstOrFail()->last_opened_at);
        $this->assertNull(MaterialProgress::query()->firstOrFail()->completed_at);
        $this->get(route('dashboard.materials.file', $material))->assertNotFound();
    }

    public function test_material_routes_require_an_authenticated_account(): void
    {
        $this->seed(\Database\Seeders\LearningMaterialSeeder::class);
        $material = LearningMaterial::query()->firstOrFail();
        $this->getJson(route('dashboard.materials.show', $material))->assertUnauthorized();
        $this->postJson(route('dashboard.materials.bookmark', $material))->assertUnauthorized();
    }

    public function test_tutor_can_create_pdf_material_and_student_can_open_its_private_file(): void
    {
        Storage::fake('local');
        $this->registerAndSignIn('Tutor Uji', 'tutor-uji@test.com');
        $this->setAccountRole('tutor-uji@test.com', 'tutor');
        $this->post('/logout');
        $this->post('/login', ['contact' => 'tutor-uji@test.com', 'password' => 'TutorKita123!']);
        $tutorId = (string) auth()->id();

        $response = $this->postJson(route('dashboard.materials.store'), [
            'title' => 'Materi Uji Aljabar', 'subject' => 'Matematika', 'topic' => 'Persamaan Linear',
            'grade_level' => 'SMP', 'material_type' => 'module_pdf', 'description' => 'PDF uji untuk validasi penyimpanan.',
            'is_published' => '1', 'file' => UploadedFile::fake()->createWithContent('aljabar.pdf', "%PDF-1.4\n% test pdf\n"),
        ])->assertCreated()->assertJsonPath('message', 'Materi pembelajaran berhasil ditambahkan.');
        $material = LearningMaterial::query()->findOrFail($response->json('id'));
        $this->assertSame($tutorId, $material->created_by);
        $this->assertSame('Tutor Uji', $material->tutor_name);
        $this->assertTrue(Storage::disk('local')->exists($material->file_path));
        $this->assertDatabaseHas('learning_materials', ['id' => $material->id, 'is_published' => 1]);

        $this->post('/logout');
        $this->registerAndSignIn('Siswa Uji', 'siswa-uji@test.com');
        $this->get(route('dashboard.materials.file', $material))->assertOk();
        $this->getJson(route('dashboard.materials.show', $material))->assertOk()->assertJsonPath('material.file_url', route('dashboard.materials.file', $material));
    }

    public function test_material_upload_requires_a_real_pdf_and_students_cannot_manage_materials(): void
    {
        Storage::fake('local');
        $this->registerAndSignIn('Siswa Uji', 'siswa-uji@test.com');
        $this->postJson(route('dashboard.materials.store'), [
            'title' => 'Materi Tanpa Hak', 'subject' => 'Matematika', 'grade_level' => 'SMP',
            'material_type' => 'module_pdf', 'description' => 'Tidak boleh dibuat siswa.', 'is_published' => '1',
        ])->assertForbidden();

        $this->post('/logout');
        $this->registerAndSignIn('Tutor Uji', 'tutor-uji@test.com');
        $this->setAccountRole('tutor-uji@test.com', 'tutor');
        $this->post('/logout');
        $this->post('/login', ['contact' => 'tutor-uji@test.com', 'password' => 'TutorKita123!']);
        $this->postJson(route('dashboard.materials.store'), [
            'title' => 'Materi File Tidak Valid', 'subject' => 'Matematika', 'grade_level' => 'SMP',
            'material_type' => 'module_pdf', 'description' => 'Format file harus diperiksa.', 'is_published' => '1',
            'file' => UploadedFile::fake()->createWithContent('bukan-pdf.txt', 'not a PDF'),
        ])->assertUnprocessable()->assertJsonValidationErrors('file');
        $this->assertDatabaseMissing('learning_materials', ['title' => 'Materi File Tidak Valid']);
    }

    public function test_tutor_can_edit_and_archive_only_owned_material_without_deleting_student_records(): void
    {
        Storage::fake('local');
        $this->registerAndSignIn('Tutor Pemilik', 'pemilik@test.com');
        $this->setAccountRole('pemilik@test.com', 'tutor');
        $this->post('/logout');
        $this->post('/login', ['contact' => 'pemilik@test.com', 'password' => 'TutorKita123!']);
        $tutorId = (string) auth()->id();
        $this->seed(\Database\Seeders\LearningMaterialSeeder::class);
        $material = LearningMaterial::query()->firstOrFail();
        $material->update(['created_by' => $tutorId, 'file_path' => 'learning-materials/owned.pdf']);
        Storage::disk('local')->put('learning-materials/owned.pdf', "%PDF-1.4\nowner file");
        MaterialProgress::query()->create(['learning_material_id' => $material->id, 'student_id' => 'student-77', 'completed_at' => now()]);
        MaterialBookmark::query()->create(['learning_material_id' => $material->id, 'student_id' => 'student-77']);

        $this->putJson(route('dashboard.materials.update', $material), [
            'title' => 'Judul Materi Diubah', 'subject' => $material->subject, 'topic' => 'Topik Diperbarui',
            'grade_level' => $material->grade_level, 'material_type' => 'video', 'video_url' => 'https://video.example.test/lesson',
            'description' => 'Deskripsi baru.', 'is_published' => '1',
        ])->assertOk()->assertJsonPath('message', 'Materi pembelajaran berhasil diperbarui.');
        $this->assertDatabaseHas('learning_materials', ['id' => $material->id, 'title' => 'Judul Materi Diubah', 'file_path' => null]);
        Storage::disk('local')->assertMissing('learning-materials/owned.pdf');

        $this->deleteJson(route('dashboard.materials.destroy', $material))->assertOk()
            ->assertJsonPath('message', 'Materi diarsipkan dari katalog. Progres dan bookmark tetap disimpan.');
        $this->assertSoftDeleted('learning_materials', ['id' => $material->id]);
        $this->assertDatabaseHas('material_progress', ['learning_material_id' => $material->id, 'student_id' => 'student-77']);
        $this->assertDatabaseHas('material_bookmarks', ['learning_material_id' => $material->id, 'student_id' => 'student-77']);
        $this->getJson(route('dashboard.materials.show', $material))->assertNotFound();
    }

    public function test_tutor_cannot_edit_or_archive_a_material_owned_by_another_tutor(): void
    {
        $this->registerAndSignIn('Tutor Satu', 'satu@test.com');
        $this->setAccountRole('satu@test.com', 'tutor');
        $this->post('/logout');
        $this->post('/login', ['contact' => 'satu@test.com', 'password' => 'TutorKita123!']);
        $this->seed(\Database\Seeders\LearningMaterialSeeder::class);
        $material = LearningMaterial::query()->firstOrFail();
        $material->update(['created_by' => (string) auth()->id()]);

        $this->post('/logout');
        $this->registerAndSignIn('Tutor Dua', 'dua@test.com');
        $this->setAccountRole('dua@test.com', 'tutor');
        $this->post('/logout');
        $this->post('/login', ['contact' => 'dua@test.com', 'password' => 'TutorKita123!']);
        $this->putJson(route('dashboard.materials.update', $material), [
            'title' => 'Percobaan lintas pemilik', 'subject' => $material->subject, 'grade_level' => 'SMP',
            'material_type' => 'video', 'video_url' => 'https://video.example.test/lesson',
            'description' => 'Tidak boleh.', 'is_published' => '1',
        ])->assertNotFound();
        $this->deleteJson(route('dashboard.materials.destroy', $material))->assertNotFound();
        $this->assertDatabaseHas('learning_materials', ['id' => $material->id, 'title' => $material->title]);
    }

    private function registerAndSignIn(string $name, string $contact): void
    {
        $this->post('/register', [
            'name' => $name,
            'contact' => $contact,
            'password' => 'TutorKita123!',
            'password_confirmation' => 'TutorKita123!',
            'terms' => 'on',
        ]);
        $this->post('/login', ['contact' => $contact, 'password' => 'TutorKita123!']);
    }

    private function setAccountRole(string $contact, string $role): void
    {
        $records = json_decode(file_get_contents($this->userStorePath), true, flags: JSON_THROW_ON_ERROR);
        foreach ($records as &$record) {
            if (($record['contact'] ?? null) === $contact) $record['role'] = $role;
        }
        unset($record);
        file_put_contents($this->userStorePath, json_encode($records, JSON_THROW_ON_ERROR));
        $user = collect($records)->first(fn (array $record): bool => ($record['contact'] ?? null) === $contact);
        DB::table('user_roles')->updateOrInsert(['user_id' => (string) $user['id']], ['role' => $role, 'created_at' => now(), 'updated_at' => now()]);
    }
}
