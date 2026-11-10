<?php

namespace Tests\Feature;

use App\Models\LearningMaterial;
use App\Models\MaterialBookmark;
use App\Models\MaterialProgress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
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
        $this->post('/logout');

        $this->registerAndSignIn('Andi', 'andi@test.com');
        $this->get('/dashboard#materials')->assertOk()->assertDontSee('aria-pressed="true"', false);
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
}
