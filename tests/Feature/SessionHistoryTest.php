<?php

namespace Tests\Feature;

use App\Models\LearningSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class SessionHistoryTest extends TestCase
{
    use RefreshDatabase;

    private string $userStorePath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userStorePath = storage_path('framework/testing/history-users-'.bin2hex(random_bytes(8)).'.json');
        config(['auth.providers.users.path' => $this->userStorePath]);
        Auth::forgetGuards();
    }

    protected function tearDown(): void
    {
        if (isset($this->userStorePath) && is_file($this->userStorePath)) @unlink($this->userStorePath);
        parent::tearDown();
    }

    public function test_history_statistics_and_favorite_tutors_use_only_the_signed_in_users_sessions(): void
    {
        $this->registerAndSignIn('Siswa Satu', 'siswa1@test.com');
        $owner = (string) auth()->id();
        $this->makeLearningSession($owner, 'Alya Putri', 'Matematika', 'completed', now()->subDays(3));
        $this->makeLearningSession($owner, 'Alya Putri', 'Matematika', 'confirmed', now()->subMinutes(15));
        $this->makeLearningSession($owner, 'Salsa Khairunisa', 'IPA', 'cancelled', now()->subDays(20));
        $this->makeLearningSession('another-student', 'Tutor Rahasia', 'Fisika', 'completed', now()->subDays(1));

        $this->get('/dashboard#history')->assertOk()
            ->assertSeeText('Riwayat Sesi')
            ->assertSeeText('1 dari 3 sesi selesai')
            ->assertSeeText('Sesi Berlangsung')
            ->assertSeeText('Sesi Dibatalkan')
            ->assertSeeText('Alya Putri')
            ->assertDontSeeText('Tutor Rahasia')
            ->assertSee('data-history-card', false)
            ->assertSee('data-history-status="in_progress"', false)
            ->assertSee('disabled title="Materi atau catatan belum dihubungkan ke sesi ini."', false);
    }

    public function test_history_search_filter_period_sort_and_pagination_controls_render_for_real_rows(): void
    {
        $this->registerAndSignIn('Siswa Satu', 'siswa1@test.com');
        $owner = (string) auth()->id();
        $this->makeLearningSession($owner, 'Alya Putri', 'Matematika', 'completed', now()->subDays(2));
        $this->makeLearningSession($owner, 'Rizky Maulana', 'IPA', 'cancelled', now()->subDays(45));
        $this->makeLearningSession($owner, 'Salsa Khairunisa', 'Biologi', 'completed', now()->subDays(10));
        $this->makeLearningSession($owner, 'Fadli Ibrahim', 'Bahasa Inggris', 'pending', now()->addDays(2));
        $this->makeLearningSession($owner, 'Nadia Safitri', 'Bahasa Indonesia', 'completed', now()->subDays(15));
        $this->makeLearningSession($owner, 'Damar Prasetyo', 'Fisika', 'completed', now()->subDays(25));

        $this->get('/dashboard#history')->assertOk()
            ->assertSee('data-history-search', false)
            ->assertSee('data-history-subject', false)
            ->assertSee('data-history-period', false)
            ->assertSee('data-history-sort', false)
            ->assertSee('data-history-pagination', false)
            ->assertSee('data-history-status="completed"', false)
            ->assertSee('data-history-status="cancelled"', false)
            ->assertSee('data-history-status="in_progress"', false);
    }

    public function test_session_detail_remains_owner_scoped_from_the_history_action(): void
    {
        $this->registerAndSignIn('Siswa Satu', 'siswa1@test.com');
        $session = $this->makeLearningSession((string) auth()->id(), 'Alya Putri', 'Matematika', 'completed', now()->subDay());
        $this->getJson(route('dashboard.sessions.show', $session))->assertOk()
            ->assertJsonPath('session.tutor_name', 'Alya Putri')
            ->assertJsonPath('session.duration_minutes', 60);

        $this->post('/logout');
        $this->registerAndSignIn('Siswa Dua', 'siswa2@test.com');
        $this->getJson(route('dashboard.sessions.show', $session))->assertNotFound();
        $this->get('/dashboard#history')->assertOk()->assertDontSee('data-history-card data-id="'.$session->id.'"', false);
    }

    private function makeLearningSession(string $studentId, string $tutor, string $subject, string $status, mixed $startsAt): LearningSession
    {
        return LearningSession::query()->create([
            'student_id' => $studentId,
            'tutor_name' => $tutor,
            'tutor_university' => 'Universitas Belajar',
            'tutor_major' => 'Pendidikan '.$subject,
            'subject' => $subject,
            'topic' => 'Topik '.$subject,
            'grade_level' => 'SMP',
            'starts_at' => $startsAt,
            'duration_minutes' => 60,
            'meeting_platform' => 'Google Meet',
            'status' => $status,
        ]);
    }

    private function registerAndSignIn(string $name, string $contact): void
    {
        $this->post('/register', ['name' => $name, 'contact' => $contact, 'password' => 'TutorKita123!', 'password_confirmation' => 'TutorKita123!', 'terms' => 'on']);
        $this->post('/login', ['contact' => $contact, 'password' => 'TutorKita123!']);
    }
}
