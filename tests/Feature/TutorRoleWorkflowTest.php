<?php

namespace Tests\Feature;

use App\Models\Subject;
use App\Support\LocalUserStore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TutorRoleWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private string $userStorePath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userStorePath = storage_path('framework/testing/role-users-'.bin2hex(random_bytes(8)).'.json');
        config(['auth.providers.users.path' => $this->userStorePath]);
        Auth::forgetGuards();
        Storage::fake('local');
        $this->seed(\Database\Seeders\SubjectCatalogSeeder::class);
    }

    protected function tearDown(): void
    {
        if (isset($this->userStorePath) && is_file($this->userStorePath)) @unlink($this->userStorePath);
        parent::tearDown();
    }

    public function test_public_registration_always_creates_a_student_and_keeps_the_single_dashboard_login(): void
    {
        $this->post('/register', $this->registrationData('Sita', 'sita@example.test') + ['role' => 'admin', 'is_admin' => true])
            ->assertRedirect('/login');
        $record = json_decode(file_get_contents($this->userStorePath), true, flags: JSON_THROW_ON_ERROR)[0];
        $this->assertSame('student', $record['role']);
        $this->assertTrue(Hash::check('TutorKita123!', $record['password']));
        $this->assertNotSame('TutorKita123!', $record['password']);
        $this->post('/login', ['contact' => 'sita@example.test', 'password' => 'TutorKita123!'])->assertRedirect('/dashboard');
        $this->assertDatabaseHas('user_roles', ['user_id' => '1', 'role' => 'student']);
        $this->get('/dashboard')->assertOk()->assertSee('data-spa-page="dashboard"', false)->assertSee('data-profile-toggle', false)
            ->assertSeeText('Student')->assertSeeText('Ajukan Menjadi Tutor');
        $this->post('/logout')->assertRedirect('/login');
    }

    public function test_student_can_submit_valid_application_and_pending_duplicate_is_blocked(): void
    {
        $this->registerAndLogin('Sita', 'sita@example.test');
        $math = Subject::query()->where('name', 'Matematika')->firstOrFail();
        $response = $this->post(route('dashboard.tutor-applications.store'), $this->applicationData($math->id));
        $response->assertRedirect('/dashboard#profile')->assertSessionHas('status');
        $this->assertDatabaseHas('tutor_applications', ['user_id' => '1', 'status' => 'pending', 'institution' => 'Universitas Contoh']);
        $application = DB::table('tutor_applications')->where('user_id', '1')->first();
        $this->assertDatabaseHas('tutor_application_subject', ['tutor_application_id' => $application->id, 'subject_id' => $math->id]);
        $this->assertDatabaseHas('user_roles', ['user_id' => '1', 'role' => 'student']);
        $this->assertSame(1, DB::table('tutor_application_reviews')->where('tutor_application_id', $application->id)->count());
        $this->from('/dashboard')->post(route('dashboard.tutor-applications.store'), $this->applicationData($math->id))
            ->assertStatus(409);
        $this->assertDatabaseCount('tutor_applications', 1);
    }

    public function test_photo_type_must_be_an_image_and_application_requires_catalog_subject_and_valid_bio(): void
    {
        $this->registerAndLogin('Sita', 'sita@example.test');
        $invalid = $this->applicationData(99999);
        $invalid['bio'] = 'pendek';
        $invalid['avatar'] = UploadedFile::fake()->create('dokumen.pdf', 30, 'application/pdf');
        $this->from('/dashboard')->post(route('dashboard.tutor-applications.store'), $invalid)
            ->assertSessionHasErrors(['avatar', 'subject_ids.0', 'bio']);
        $this->assertDatabaseCount('tutor_applications', 0);
    }

    public function test_only_admin_can_approve_and_approval_atomically_promotes_student_and_preserves_shared_dashboard(): void
    {
        $this->registerAndLogin('Sita', 'sita@example.test');
        $math = Subject::query()->where('name', 'Matematika')->firstOrFail();
        $this->post(route('dashboard.tutor-applications.store'), $this->applicationData($math->id))->assertRedirect('/dashboard#profile');
        $applicationId = DB::table('tutor_applications')->where('user_id', '1')->value('id');

        $this->post(route('dashboard.admin.tutor-applications.decide', $applicationId), ['decision' => 'approve'])->assertForbidden();
        $this->assertDatabaseHas('user_roles', ['user_id' => '1', 'role' => 'student']);

        $this->post('/logout');
        $this->registerAndLogin('Admin Uji', 'admin@example.test');
        DB::table('user_roles')->where('user_id', '2')->update(['role' => 'admin']);
        Auth::logout();
        Auth::login(Auth::guard()->getProvider()->retrieveById('2'));

        $this->get('/dashboard')->assertOk()->assertSeeText('Verifikasi Tutor')->assertSeeText('Sita')
            ->assertSeeText('Universitas Contoh')->assertSeeText('Matematika');
        $this->post(route('dashboard.admin.tutor-applications.decide', $applicationId), ['decision' => 'approve'])
            ->assertRedirect()->assertSessionHas('status');
        $this->assertDatabaseHas('tutor_applications', ['id' => $applicationId, 'status' => 'approved', 'reviewed_by' => '2']);
        $this->assertDatabaseHas('user_roles', ['user_id' => '1', 'role' => 'tutor']);
        $this->assertDatabaseHas('user_role_changes', ['user_id' => '1', 'actor_id' => '2', 'old_role' => 'student', 'new_role' => 'tutor']);
        $this->assertDatabaseHas('tutor_profiles', ['user_id' => '1', 'is_active' => 1, 'institution' => 'Universitas Contoh']);
        $this->assertDatabaseHas('user_notifications', ['user_id' => '1', 'category' => 'system', 'title' => 'Pengajuan tutor disetujui']);

        $this->post('/logout');
        $this->post('/login', ['contact' => 'sita@example.test', 'password' => 'TutorKita123!'])->assertRedirect('/dashboard');
        $this->get('/dashboard')->assertOk()->assertSee('data-spa-page="dashboard"', false)->assertSeeText('Tutor terverifikasi')
            ->assertSeeText('Edit Profil Tutor')->assertSee('data-material-create', false);
        $this->post(route('dashboard.admin.tutor-applications.decide', $applicationId), ['decision' => 'approve'])->assertForbidden();
    }

    public function test_rejection_requires_reason_keeps_student_role_and_allows_resubmission(): void
    {
        $this->registerAndLogin('Sita', 'sita@example.test');
        $math = Subject::query()->where('name', 'Matematika')->firstOrFail();
        $this->post(route('dashboard.tutor-applications.store'), $this->applicationData($math->id));
        $applicationId = DB::table('tutor_applications')->where('user_id', '1')->value('id');
        $this->post('/logout');
        $this->registerAndLogin('Admin Uji', 'admin@example.test');
        DB::table('user_roles')->where('user_id', '2')->update(['role' => 'admin']);
        Auth::logout();
        Auth::login(Auth::guard()->getProvider()->retrieveById('2'));

        $this->post(route('dashboard.admin.tutor-applications.decide', $applicationId), ['decision' => 'reject'])
            ->assertSessionHasErrors('reason');
        $this->post(route('dashboard.admin.tutor-applications.decide', $applicationId), ['decision' => 'reject', 'reason' => 'Lengkapi informasi pendidikan terlebih dahulu.'])
            ->assertRedirect();
        $this->assertDatabaseHas('tutor_applications', ['id' => $applicationId, 'status' => 'rejected', 'reviewed_by' => '2']);
        $this->assertDatabaseHas('user_roles', ['user_id' => '1', 'role' => 'student']);
        $this->assertDatabaseHas('tutor_application_reviews', ['tutor_application_id' => $applicationId, 'action' => 'reject']);

        $this->post('/logout');
        $this->post('/login', ['contact' => 'sita@example.test', 'password' => 'TutorKita123!']);
        $this->get('/dashboard')->assertSee('Perbaiki & Ajukan Ulang', false)->assertSeeText('Lengkapi informasi pendidikan');
        $this->post(route('dashboard.tutor-applications.store'), $this->applicationData($math->id))->assertRedirect('/dashboard#profile');
        $this->assertDatabaseHas('tutor_applications', ['id' => $applicationId, 'status' => 'pending', 'rejection_reason' => null]);
        $this->assertDatabaseHas('tutor_application_reviews', ['tutor_application_id' => $applicationId, 'action' => 'resubmitted']);
    }

    public function test_tutor_can_edit_own_profile_photo_subjects_and_availability_but_non_tutor_cannot_use_tutor_controls(): void
    {
        $this->registerAndLogin('Sita', 'sita@example.test');
        $math = Subject::query()->where('name', 'Matematika')->firstOrFail();
        $this->post(route('dashboard.tutor-applications.store'), $this->applicationData($math->id));
        $applicationId = DB::table('tutor_applications')->where('user_id', '1')->value('id');
        $this->post('/logout');
        $this->registerAndLogin('Admin Uji', 'admin@example.test');
        DB::table('user_roles')->where('user_id', '2')->update(['role' => 'admin']);
        Auth::logout();
        Auth::login(Auth::guard()->getProvider()->retrieveById('2'));
        $this->post(route('dashboard.admin.tutor-applications.decide', $applicationId), ['decision' => 'approve']);
        $this->post('/logout');
        $this->post('/login', ['contact' => 'sita@example.test', 'password' => 'TutorKita123!']);

        $ipa = Subject::query()->where('name', 'IPA')->firstOrFail();
        $this->post(route('dashboard.profile.update'), [
            'name' => 'Sita Tutor', 'institution' => 'Institut Baru', 'education_level' => 'S2',
            'program' => 'Pendidikan IPA', 'graduation_year' => now()->year, 'bio' => 'Biografi tutor yang sudah diperbarui untuk kebutuhan uji.',
            'experience' => 'Dua tahun mengajar', 'subject_ids' => [$ipa->id], 'availability' => ['Senin · 15.00–18.00'],
            'avatar' => $this->fakeImage('profil.png'), 'role' => 'admin',
        ])->assertRedirect();
        $this->assertDatabaseHas('tutor_profiles', ['user_id' => '1', 'institution' => 'Institut Baru', 'is_active' => 1]);
        $this->assertDatabaseHas('tutor_profile_subject', ['tutor_profile_id' => DB::table('tutor_profiles')->where('user_id', '1')->value('id'), 'subject_id' => $ipa->id]);
        $this->assertSame('tutor', DB::table('user_roles')->where('user_id', '1')->value('role'));
        $this->assertNotNull(app(LocalUserStore::class)->findById('1')['avatar_path']);
        $availability = json_decode(DB::table('tutor_profiles')->where('user_id', '1')->value('availability'), true, flags: JSON_THROW_ON_ERROR);
        $this->assertSame(['Senin · 15.00–18.00'], $availability);
    }

    private function registerAndLogin(string $name, string $email): void
    {
        $this->post('/register', $this->registrationData($name, $email))->assertRedirect('/login');
        $this->post('/login', ['contact' => $email, 'password' => 'TutorKita123!'])->assertRedirect('/dashboard');
    }

    private function registrationData(string $name, string $email): array
    {
        return ['name' => $name, 'contact' => $email, 'password' => 'TutorKita123!', 'password_confirmation' => 'TutorKita123!', 'terms' => 'on'];
    }

    private function applicationData(int $subjectId): array
    {
        return [
            'applicant_name' => 'Sita Calon Tutor', 'education_level' => 'S1', 'institution' => 'Universitas Contoh',
            'program' => 'Pendidikan Matematika', 'graduation_year' => 2024, 'subject_ids' => [$subjectId],
            'bio' => 'Saya senang membantu siswa memahami pelajaran dengan contoh yang mudah.',
            'experience' => 'Pernah menjadi relawan belajar selama satu tahun.',
            'avatar' => $this->fakeImage('calon-tutor.png'),
        ];
    }

    private function fakeImage(string $name): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($name, file_get_contents(public_path('assets/tutorkita/tutorkita-ikon-app.png')));
    }
}
