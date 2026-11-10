<?php

namespace Tests\Feature;

use App\Models\LearningSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class LearningSessionTest extends TestCase
{
    use RefreshDatabase;

    private string $userStorePath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userStorePath = storage_path('framework/testing/session-users-'.bin2hex(random_bytes(8)).'.json');
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

    public function test_student_can_request_a_session_and_it_is_saved_without_a_fake_meeting_link(): void
    {
        $this->registerAndSignIn('Budi', 'budi@test.com');

        $response = $this->postJson(route('dashboard.sessions.store'), [
            'tutor_name' => 'Alya Putri',
            'subject' => 'Matematika',
            'topic' => 'Persamaan kuadrat',
            'grade_level' => 'SMP',
            'starts_at' => now()->addDays(2)->setTime(10, 0)->format('Y-m-d\\TH:i'),
        ])->assertCreated()
            ->assertJsonPath('session.status', 'pending')
            ->assertJsonPath('session.meeting_url', null);

        $session = LearningSession::query()->firstOrFail();
        $this->assertSame((string) auth()->id(), $session->student_id);
        $this->assertSame('Alya Putri', $session->tutor_name);
        $this->assertSame('Matematika', $session->subject);
        $this->assertNull($session->meeting_url);

        $this->get('/dashboard#sessions')->assertOk()
            ->assertSeeText('Sesi Belajar')
            ->assertSeeText('Persamaan kuadrat')
            ->assertSee('data-session-card', false);

        $this->getJson(route('dashboard.sessions.show', $response->json('session.id')))
            ->assertOk()
            ->assertJsonPath('session.tutor_name', 'Alya Putri');
    }

    public function test_session_data_is_private_and_owner_can_cancel_a_future_request(): void
    {
        $this->registerAndSignIn('Budi', 'budi@test.com');
        $session = LearningSession::query()->create([
            'student_id' => (string) auth()->id(),
            'tutor_name' => 'Alya Putri',
            'tutor_university' => 'Universitas Indonesia',
            'tutor_major' => 'Pendidikan Matematika',
            'subject' => 'Matematika',
            'topic' => 'Aljabar',
            'grade_level' => 'SMP',
            'starts_at' => now()->addDays(2),
            'duration_minutes' => 60,
            'meeting_platform' => 'Google Meet',
            'status' => 'pending',
        ]);
        $this->post('/logout');

        $this->registerAndSignIn('Andi', 'andi@test.com');
        $this->getJson(route('dashboard.sessions.show', $session->id))->assertNotFound();
        $this->postJson(route('dashboard.sessions.cancel', $session->id))->assertNotFound();
        $this->get('/dashboard#sessions')->assertOk()->assertDontSeeText('Aljabar');
        $this->post('/logout');

        $this->registerAndSignIn('Budi', 'budi@test.com');
        $this->postJson(route('dashboard.sessions.cancel', $session->id))
            ->assertOk()
            ->assertJsonPath('message', 'Pengajuan sesi berhasil dibatalkan.');
        $this->assertDatabaseHas('learning_sessions', ['id' => $session->id, 'status' => 'cancelled']);
    }

    public function test_unavailable_tutors_and_unmatched_subjects_cannot_be_requested(): void
    {
        $this->registerAndSignIn('Budi', 'budi@test.com');
        $request = [
            'subject' => 'Bahasa Inggris',
            'topic' => 'Percakapan sehari-hari',
            'grade_level' => 'SMP',
            'starts_at' => now()->addDays(2)->setTime(10, 0)->format('Y-m-d\\TH:i'),
        ];

        $this->postJson(route('dashboard.sessions.store'), $request + ['tutor_name' => 'Fadli Ibrahim'])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Tutor ini sedang tidak tersedia. Silakan pilih tutor lain.');

        $this->postJson(route('dashboard.sessions.store'), $request + ['tutor_name' => 'Alya Putri'])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Mata pelajaran tidak sesuai dengan tutor yang dipilih.');

        $this->assertDatabaseCount('learning_sessions', 0);
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
