<?php

namespace Tests\Feature;

use App\Models\LearningMaterial;
use App\Models\LearningSession;
use App\Models\MaterialProgress;
use App\Models\Subject;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    private string $userStorePath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userStorePath = storage_path('framework/testing/auth-users-'.bin2hex(random_bytes(8)).'.json');
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

    public function test_registration_stores_a_password_hash_and_rejects_duplicate_contacts(): void
    {
        $this->post('/register', $this->registrationData('Budi', 'budi@test.com', 'Budi123!'))
            ->assertRedirect('/login')
            ->assertSessionHas('status');

        $users = json_decode(file_get_contents($this->userStorePath), true, flags: JSON_THROW_ON_ERROR);
        $this->assertCount(1, $users);
        $this->assertSame('Budi', $users[0]['name']);
        $this->assertSame('budi@test.com', $users[0]['email']);
        $this->assertTrue(Hash::check('Budi123!', $users[0]['password']));
        $this->assertNotSame('Budi123!', $users[0]['password']);

        $this->from('/register')
            ->post('/register', $this->registrationData('Budi lain', 'BUDI@test.com', 'Budi123!'))
            ->assertRedirect('/register')
            ->assertSessionHasErrors('contact');
    }

    public function test_login_rejects_unknown_users_and_wrong_passwords_then_accepts_valid_credentials(): void
    {
        $this->from('/login')->post('/login', [
            'contact' => 'tidakada@test.com',
            'password' => 'Budi123!',
        ])->assertRedirect('/login')
            ->assertSessionHasErrors(['contact' => 'Email atau nomor telepon belum terdaftar.']);
        $this->get('/login')->assertSeeText('Email atau nomor telepon belum terdaftar.');
        $this->assertGuest();

        $this->post('/register', $this->registrationData('Budi', 'budi@test.com', 'Budi123!'))
            ->assertRedirect('/login');

        $this->from('/login')->post('/login', [
            'contact' => 'budi@test.com',
            'password' => 'TidakBenar-987!',
        ])->assertRedirect('/login')
            ->assertSessionHasErrors(['contact' => 'Email atau password yang kamu masukkan salah.']);
        $this->get('/login')
            ->assertSeeText('Email atau password yang kamu masukkan salah.')
            ->assertDontSee('TidakBenar-987!');
        $this->assertGuest();

        $this->post('/login', [
            'contact' => 'BUDI@test.com',
            'password' => 'Budi123!',
        ])->assertRedirect('/dashboard');
        $this->assertAuthenticated();
        $this->get('/dashboard')->assertOk()->assertSeeText('Selamat datang kembali, Budi!');
    }

    public function test_dashboard_is_protected_and_logout_clears_authentication(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $this->post('/register', $this->registrationData('Budi', 'budi@test.com', 'Budi123!'));
        $this->post('/login', ['contact' => 'budi@test.com', 'password' => 'Budi123!'])
            ->assertRedirect('/dashboard');

        $this->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_each_account_sees_its_own_dashboard_identity(): void
    {
        $this->post('/register', $this->registrationData('Budi', 'budi@test.com', 'Budi123!'));
        $this->post('/login', ['contact' => 'budi@test.com', 'password' => 'Budi123!']);
        $this->get('/dashboard')->assertSeeText('Selamat datang kembali, Budi!');
        $this->post('/logout');

        $this->post('/register', $this->registrationData('Andi', 'andi@test.com', 'Andi123!'));
        $this->post('/login', ['contact' => 'andi@test.com', 'password' => 'Andi123!']);
        $this->get('/dashboard')
            ->assertSeeText('Selamat datang kembali, Andi!')
            ->assertDontSeeText('Selamat datang kembali, Budi!');
    }

    public function test_invalid_contact_password_and_terms_are_rejected(): void
    {
        $this->from('/register')->post('/register', [
            'name' => '',
            'contact' => 'not-an-email-or-phone',
            'password' => 'short',
            'password_confirmation' => 'different',
        ])->assertRedirect('/register')
            ->assertSessionHasErrors(['name', 'contact', 'password', 'terms']);

        $this->from('/login')->post('/login', ['contact' => '', 'password' => ''])
            ->assertRedirect('/login')
            ->assertSessionHasErrors(['contact', 'password']);
        $this->assertGuest();
    }

    public function test_a_phone_number_can_be_used_to_register_and_log_in(): void
    {
        $this->post('/register', $this->registrationData('Andi', '081234567890', 'Andi123!'))
            ->assertRedirect('/login');

        $this->post('/login', [
            'contact' => '0812-3456-7890',
            'password' => 'Andi123!',
        ])->assertRedirect('/dashboard');

        $this->get('/dashboard')->assertSeeText('Selamat datang kembali, Andi!');
    }

    public function test_dashboard_renders_the_full_interactive_tutor_catalog(): void
    {
        $this->post('/register', $this->registrationData('Budi', 'budi@test.com', 'Budi123!'));
        $this->post('/login', ['contact' => 'budi@test.com', 'password' => 'Budi123!'])
            ->assertRedirect('/dashboard');

        $response = $this->get('/dashboard#tutors')->assertOk()
            ->assertSeeText('Katalog Tutor')
            ->assertSeeText('Alya Putri')
            ->assertSeeText('Rizky Maulana')
            ->assertSee('data-catalog-filter="subject"', false)
            ->assertSee('data-catalog-filter="schedule"', false)
            ->assertSee('data-catalog-sort', false)
            ->assertSee('data-catalog-pagination', false);

        $this->assertSame(24, substr_count($response->getContent(), 'data-catalog-card'));
    }

    public function test_dashboard_summary_and_schedule_use_the_signed_in_students_database_records(): void
    {
        $this->post('/register', $this->registrationData('Budi', 'budi@test.com', 'Budi123!'));
        $this->post('/login', ['contact' => 'budi@test.com', 'password' => 'Budi123!']);

        $this->seed(\Database\Seeders\SubjectCatalogSeeder::class);
        $this->seed(\Database\Seeders\LearningMaterialSeeder::class);

        $studentId = (string) auth()->id();
        $subject = Subject::query()->where('name', 'Matematika')->firstOrFail();
        $gradeId = DB::table('grade_levels')->where('name', 'SMP')->value('id');
        DB::table('student_subjects')->insert([
            'student_id' => $studentId,
            'subject_id' => $subject->id,
            'grade_level_id' => $gradeId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $material = LearningMaterial::query()->where('slug', 'persamaan-kuadrat')->firstOrFail();
        MaterialProgress::query()->create([
            'student_id' => $studentId,
            'learning_material_id' => $material->id,
            'last_opened_at' => now(),
            'completed_at' => now(),
        ]);
        LearningSession::query()->create([
            'student_id' => $studentId,
            'tutor_name' => 'Nadia Putri',
            'tutor_university' => 'Universitas Indonesia',
            'tutor_major' => 'Pendidikan Matematika',
            'subject' => 'Matematika',
            'topic' => 'Persamaan Kuadrat',
            'grade_level' => 'SMP',
            'starts_at' => now()->copy()->startOfDay()->addHours(16),
            'duration_minutes' => 60,
            'meeting_platform' => 'Google Meet',
            'status' => 'pending',
        ]);

        $this->get('/dashboard')->assertOk()
            ->assertSee('<div class="stat-value">1</div><div class="stat-label">Mata Pelajaran Aktif</div>', false)
            ->assertSee('<div class="stat-value">1</div><div class="stat-label">Jadwal Hari Ini</div>', false)
            ->assertSee('<div class="stat-value">1</div><div class="stat-label">Sesi Belajar</div>', false)
            ->assertSee('<div class="stat-value">13%</div><div class="stat-label">Progress Belajar</div>', false)
            ->assertSeeText('Persamaan Kuadrat')
            ->assertSeeText('Nadia Putri')
            ->assertSee('data-schedule-item', false);
    }

    /** @return array<string, string> */
    private function registrationData(string $name, string $contact, string $password): array
    {
        return [
            'name' => $name,
            'contact' => $contact,
            'password' => $password,
            'password_confirmation' => $password,
            'terms' => 'on',
        ];
    }
}
