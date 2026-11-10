<?php

namespace Tests\Feature;

use App\Models\LearningMaterial;
use App\Models\Subject;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class SubjectSelectionTest extends TestCase
{
    use RefreshDatabase;

    private string $userStorePath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userStorePath = storage_path('framework/testing/subject-users-'.bin2hex(random_bytes(8)).'.json');
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

    public function test_materials_page_contains_the_database_backed_subject_manager_and_legacy_route_redirects(): void
    {
        $this->registerAndLogin('Ayu', 'ayu@example.test');
        $this->seed(\Database\Seeders\SubjectCatalogSeeder::class);
        $this->assertDatabaseCount('subjects', 11);
        $this->assertDatabaseCount('grade_level_subject', 29);
        LearningMaterial::query()->create([
            'slug' => 'persamaan-linear-test', 'title' => 'Persamaan Linear', 'subject' => 'Matematika',
            'topic' => 'Aljabar', 'grade_level' => 'SMP', 'tutor_name' => 'Tutor Ayu', 'material_type' => 'pdf',
            'description' => 'Materi contoh', 'illustration' => 'chart', 'is_published' => true,
        ]);

        $this->get('/dashboard?materials=subjects#materials')->assertOk()
            ->assertSeeText('Materi Pembelajaran')->assertSeeText('Belajar Lebih Mudah')
            ->assertSeeText('Semua Mata Pelajaran')->assertSeeText('Mata Pelajaranku')
            ->assertSeeText('Matematika')->assertSeeText('1 materi')
            ->assertSee('data-subject-confirm-dialog', false)->assertSee('data-subject-confirm-cancel', false)
            ->assertSee('data-subject-confirm-accept', false)->assertSeeText('Hapus dari Daftar')
            ->assertSee('data-subject-search', false)->assertSee('data-subject-level-filter', false)
            ->assertSee('data-subject-category-filter', false)->assertSee('data-material-search', false)
            ->assertSee('data-material-bookmark', false);
        $searchResults = $this->get(route('subjects.catalog', ['search' => 'fenomena']))->assertOk()->json('ids');
        $this->assertContains(Subject::query()->where('name', 'IPA')->value('id'), $searchResults);
        $this->assertNotContains(Subject::query()->where('name', 'Matematika')->value('id'), $searchResults);
        $levelResults = $this->get(route('subjects.catalog', ['level' => 'SD']))->assertOk()->json('ids');
        $this->assertNotContains(Subject::query()->where('name', 'Kimia')->value('id'), $levelResults);
        $this->get(route('subjects.index'))->assertRedirect('/dashboard?materials=subjects#materials');
    }

    public function test_student_can_add_and_remove_only_their_own_subject_selection(): void
    {
        $this->registerAndLogin('Ayu', 'ayu@example.test');
        $subject = Subject::query()->where('name', 'Matematika')->firstOrFail();
        $levelId = \App\Models\GradeLevel::query()->where('name', 'SMP')->value('id');
        $material = LearningMaterial::query()->create([
            'slug' => 'materi-matematika-test', 'title' => 'Materi Matematika', 'subject' => 'Matematika',
            'grade_level' => 'SMP', 'tutor_name' => 'Tutor', 'material_type' => 'pdf',
            'description' => 'Materi', 'illustration' => 'chart', 'is_published' => true,
        ]);

        $this->postJson(route('subjects.select', $subject), ['grade_level_id' => $levelId])->assertOk();
        $this->assertDatabaseHas('student_subjects', ['student_id' => '1', 'subject_id' => $subject->id, 'grade_level_id' => $levelId]);
        $this->get('/dashboard?materials=subjects#materials')->assertSee('data-selected="1"', false);
        $this->postJson(route('dashboard.materials.bookmark', $material))->assertOk()->assertJsonPath('bookmarked', true);
        $this->postJson(route('dashboard.materials.complete', $material))->assertOk()->assertJsonPath('completed', true);
        $this->deleteJson(route('subjects.destroy', $subject))->assertOk()
            ->assertJsonPath('selected', false)
            ->assertJsonPath('message', 'Mata pelajaran berhasil dihapus dari daftar belajarmu.');
        $this->assertDatabaseMissing('student_subjects', ['student_id' => '1', 'subject_id' => $subject->id]);
        $this->assertDatabaseHas('subjects', ['id' => $subject->id, 'name' => 'Matematika']);
        $this->assertDatabaseHas('learning_materials', ['id' => $material->id, 'subject' => 'Matematika']);
        $this->assertDatabaseHas('material_bookmarks', ['learning_material_id' => $material->id, 'student_id' => '1']);
        $this->assertDatabaseHas('material_progress', ['learning_material_id' => $material->id, 'student_id' => '1']);
        $this->deleteJson(route('subjects.destroy', $subject))->assertNotFound();
    }

    public function test_student_can_add_and_edit_subject_and_grade_level_with_validation(): void
    {
        $this->registerAndLogin('Ayu', 'ayu@example.test');
        $math = Subject::query()->where('name', 'Matematika')->firstOrFail();
        $ipa = Subject::query()->where('name', 'IPA')->firstOrFail();
        $smp = \App\Models\GradeLevel::query()->where('name', 'SMP')->firstOrFail();
        $sma = \App\Models\GradeLevel::query()->where('name', 'SMA/SMK')->firstOrFail();
        $sd = \App\Models\GradeLevel::query()->where('name', 'SD')->firstOrFail();

        $this->postJson(route('subjects.select', $math), ['grade_level_id' => $smp->id])->assertOk()->assertJsonPath('selected', true);
        $this->assertDatabaseHas('student_subjects', ['student_id' => '1', 'subject_id' => $math->id, 'grade_level_id' => $smp->id]);
        $this->putJson(route('subjects.update', $math), ['subject_id' => $math->id, 'grade_level_id' => $sma->id])->assertOk();
        $this->assertDatabaseHas('student_subjects', ['student_id' => '1', 'subject_id' => $math->id, 'grade_level_id' => $sma->id]);
        $this->postJson(route('subjects.select', $ipa), ['grade_level_id' => $sd->id])->assertOk();
        $this->putJson(route('subjects.update', $math), ['subject_id' => $ipa->id, 'grade_level_id' => $sd->id])->assertUnprocessable();
        $kimia = Subject::query()->where('name', 'Kimia')->firstOrFail();
        $this->putJson(route('subjects.update', $math), ['subject_id' => $kimia->id, 'grade_level_id' => $sd->id])->assertUnprocessable();
        $this->postJson(route('subjects.select', $ipa), [])->assertUnprocessable();
        $this->assertDatabaseHas('student_subjects', ['student_id' => '1', 'subject_id' => $math->id, 'grade_level_id' => $sma->id]);
        $this->assertDatabaseHas('student_subjects', ['student_id' => '1', 'subject_id' => $ipa->id, 'grade_level_id' => $sd->id]);
    }

    public function test_material_search_bookmark_and_progress_remain_available_on_integrated_page(): void
    {
        $this->registerAndLogin('Ayu', 'ayu@example.test');
        $material = LearningMaterial::query()->create([
            'slug' => 'materi-search-progress', 'title' => 'Sistem Persamaan Linear', 'subject' => 'Matematika',
            'grade_level' => 'SMP', 'tutor_name' => 'Tutor', 'material_type' => 'pdf',
            'description' => 'Materi contoh', 'illustration' => 'chart', 'is_published' => true,
        ]);
        $this->get('/dashboard#materials')->assertOk()->assertSeeText('Sistem Persamaan Linear')
            ->assertSee('data-material-search', false)->assertSee('data-material-subject="Matematika"', false);

        $this->post(route('dashboard.materials.bookmark', $material))->assertOk()->assertJson(['bookmarked' => true]);
        $this->assertDatabaseHas('material_bookmarks', ['learning_material_id' => $material->id, 'student_id' => '1']);
        $this->post(route('dashboard.materials.complete', $material))->assertOk()->assertJson(['completed' => true]);
        $this->assertDatabaseHas('material_progress', ['learning_material_id' => $material->id, 'student_id' => '1']);
        $this->get('/dashboard#materials')->assertOk()->assertSeeText('1 dari 1 materi selesai');
    }

    public function test_student_cannot_remove_another_students_selection(): void
    {
        $this->registerAndLogin('Ayu', 'ayu@example.test');
        $subject = Subject::query()->where('name', 'Matematika')->firstOrFail();
        $this->post(route('subjects.select', $subject), ['grade_level_id' => \App\Models\GradeLevel::query()->where('name', 'SMP')->value('id')]);

        $this->post('/logout');
        $this->registerAndLogin('Bima', 'bima@example.test');
        $this->delete(route('subjects.destroy', $subject))->assertNotFound();
        $this->putJson(route('subjects.update', $subject), ['subject_id' => $subject->id, 'grade_level_id' => \App\Models\GradeLevel::query()->where('name', 'SD')->value('id')])->assertNotFound();
        $this->assertDatabaseHas('student_subjects', ['student_id' => '1', 'subject_id' => $subject->id]);
    }

    public function test_subject_catalog_legacy_route_requires_login_and_redirects_into_materials(): void
    {
        $this->get(route('subjects.index'))->assertRedirect(route('login'));
        $subject = Subject::query()->firstOrFail();
        $this->post(route('subjects.select', $subject))->assertRedirect(route('login'));
    }

    public function test_catalog_and_selection_actions_are_for_students_only(): void
    {
        $this->post('/register', [
            'name' => 'Tutor', 'contact' => 'tutor@example.test', 'password' => 'TutorPass123!',
            'password_confirmation' => 'TutorPass123!', 'terms' => 'on',
        ])->assertRedirect('/login');

        $records = json_decode(file_get_contents($this->userStorePath), true, flags: JSON_THROW_ON_ERROR);
        $records[0]['role'] = 'tutor';
        file_put_contents($this->userStorePath, json_encode($records, JSON_THROW_ON_ERROR));
        $this->post('/login', ['contact' => 'tutor@example.test', 'password' => 'TutorPass123!'])
            ->assertRedirect('/dashboard');

        $this->get(route('dashboard'))->assertOk();
        $this->post(route('subjects.select', Subject::query()->firstOrFail()), ['grade_level_id' => \App\Models\GradeLevel::query()->where('name', 'SD')->value('id')])->assertForbidden();
    }

    private function registerAndLogin(string $name, string $email): void
    {
        $this->post('/register', [
            'name' => $name, 'contact' => $email, 'password' => 'AyuPass123!',
            'password_confirmation' => 'AyuPass123!', 'terms' => 'on',
        ])->assertRedirect('/login');
        $this->post('/login', ['contact' => $email, 'password' => 'AyuPass123!'])
            ->assertRedirect('/dashboard');
    }
}
