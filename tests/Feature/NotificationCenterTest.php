<?php

namespace Tests\Feature;

use App\Models\LearningSession;
use App\Models\StudentAssignment;
use App\Models\UserNotification;
use App\Models\UserNotificationPreference;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class NotificationCenterTest extends TestCase
{
    use RefreshDatabase;

    private string $userStorePath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userStorePath = storage_path('framework/testing/notification-users-'.bin2hex(random_bytes(8)).'.json');
        config(['auth.providers.users.path' => $this->userStorePath]);
        Auth::forgetGuards();
    }

    protected function tearDown(): void
    {
        if (isset($this->userStorePath) && is_file($this->userStorePath)) @unlink($this->userStorePath);
        parent::tearDown();
    }

    public function test_dashboard_generates_idempotent_notifications_from_the_owners_real_sessions_and_assignments(): void
    {
        $this->registerAndSignIn('Siswa Satu', 'siswa1@test.com');
        $userId = (string) auth()->id();
        LearningSession::query()->create([
            'student_id' => $userId, 'tutor_name' => 'Alya Putri', 'subject' => 'Matematika', 'topic' => 'Aljabar',
            'grade_level' => 'SMP', 'starts_at' => now()->addDay(), 'duration_minutes' => 60, 'status' => 'pending',
        ]);
        StudentAssignment::query()->create([
            'student_id' => $userId, 'tutor_id' => 'tutor-1', 'tutor_name' => 'Alya Putri', 'title' => 'Persamaan linear',
            'subject' => 'Matematika', 'instructions' => 'Selesaikan soal.', 'due_at' => now()->addDay(),
        ]);

        $this->get('/dashboard#notifications')->assertOk()
            ->assertSeeText('Pengajuan Sesi Belajar')
            ->assertSeeText('Tugas Baru')
            ->assertSeeText('Persamaan linear')
            ->assertSee('data-notification-filter="sessions"', false)
            ->assertSee('data-notification-filter="assignments"', false);
        $this->get('/dashboard#notifications')->assertOk();

        $this->assertDatabaseCount('user_notifications', 2);
        $this->assertDatabaseHas('user_notifications', ['user_id' => $userId, 'category' => 'sessions']);
        $this->assertDatabaseHas('user_notifications', ['user_id' => $userId, 'category' => 'assignments']);
    }

    public function test_user_can_mark_one_notification_read_but_cannot_mark_another_users_notification(): void
    {
        $this->registerAndSignIn('Siswa Satu', 'siswa1@test.com');
        $mine = UserNotification::query()->create(['user_id' => (string) auth()->id(), 'notification_key' => 'own:1', 'category' => 'system', 'title' => 'Pembaruan', 'body' => 'Info akun']);
        $other = UserNotification::query()->create(['user_id' => 'other-user', 'notification_key' => 'other:1', 'category' => 'system', 'title' => 'Rahasia', 'body' => 'Info lain']);

        $this->postJson(route('dashboard.notifications.read', $mine))->assertOk()->assertJsonPath('unread_count', 0);
        $this->assertNotNull($mine->fresh()->read_at);
        $this->postJson(route('dashboard.notifications.read', $other))->assertNotFound();
        $this->assertNull($other->fresh()->read_at);
    }

    public function test_mark_all_read_only_changes_the_signed_in_users_notifications(): void
    {
        $this->registerAndSignIn('Siswa Satu', 'siswa1@test.com');
        UserNotification::query()->create(['user_id' => (string) auth()->id(), 'notification_key' => 'own:1', 'category' => 'system', 'title' => 'Satu', 'body' => 'Info']);
        UserNotification::query()->create(['user_id' => (string) auth()->id(), 'notification_key' => 'own:2', 'category' => 'messages', 'title' => 'Dua', 'body' => 'Pesan']);
        UserNotification::query()->create(['user_id' => 'different-user', 'notification_key' => 'other:1', 'category' => 'system', 'title' => 'Tiga', 'body' => 'Pribadi']);

        $this->postJson(route('dashboard.notifications.read-all'))->assertOk()->assertJsonPath('unread_count', 0);
        $this->assertDatabaseMissing('user_notifications', ['user_id' => (string) auth()->id(), 'read_at' => null]);
        $this->assertDatabaseHas('user_notifications', ['user_id' => 'different-user', 'read_at' => null]);
    }

    public function test_preference_is_persisted_and_suppresses_new_activity_until_reenabled(): void
    {
        $this->registerAndSignIn('Siswa Satu', 'siswa1@test.com');
        $userId = (string) auth()->id();
        $this->postJson(route('dashboard.notifications.preference'), ['enabled' => false])->assertOk()->assertJsonPath('enabled', false);
        UserNotificationPreference::query()->where('user_id', $userId)->firstOrFail();
        LearningSession::query()->create([
            'student_id' => $userId, 'tutor_name' => 'Alya Putri', 'subject' => 'Matematika', 'topic' => 'Aljabar',
            'grade_level' => 'SMP', 'starts_at' => now()->addDay(), 'duration_minutes' => 60, 'status' => 'pending',
        ]);
        $this->get('/dashboard#notifications')->assertOk()->assertSee('data-notification-preference', false);
        $this->assertDatabaseCount('user_notifications', 0);

        $this->postJson(route('dashboard.notifications.preference'), ['enabled' => true])->assertOk()->assertJsonPath('enabled', true);
        $this->get('/dashboard#notifications')->assertOk()->assertSeeText('Pengajuan Sesi Belajar');
        $this->assertDatabaseCount('user_notifications', 1);
    }

    private function registerAndSignIn(string $name, string $contact): void
    {
        $this->post('/register', ['name' => $name, 'contact' => $contact, 'password' => 'TutorKita123!', 'password_confirmation' => 'TutorKita123!', 'terms' => 'on']);
        $this->post('/login', ['contact' => $contact, 'password' => 'TutorKita123!']);
    }
}
