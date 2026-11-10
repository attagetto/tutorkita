<?php

namespace Tests\Feature;

use App\Models\AssignmentSubmission;
use App\Models\StudentAssignment;
use App\Support\LocalUserStore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class AssignmentTest extends TestCase
{
    use RefreshDatabase;

    private string $userStorePath;
    private string $privateFileRoot;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userStorePath = storage_path('framework/testing/assignment-users-'.bin2hex(random_bytes(8)).'.json');
        $this->privateFileRoot = storage_path('framework/testing/assignment-files-'.bin2hex(random_bytes(8)));
        config(['auth.providers.users.path' => $this->userStorePath]);
        config(['filesystems.disks.local.root' => $this->privateFileRoot]);
        Auth::forgetGuards();
    }

    protected function tearDown(): void
    {
        if (isset($this->userStorePath) && is_file($this->userStorePath)) @unlink($this->userStorePath);
        if (isset($this->privateFileRoot) && is_dir($this->privateFileRoot)) File::deleteDirectory($this->privateFileRoot);
        parent::tearDown();
    }

    public function test_student_only_sees_real_assigned_tasks_and_statistics_are_calculated_from_them(): void
    {
        $this->registerAndSignIn('Siswa Satu', 'siswa1@test.com');
        $own = $this->assignment((string) auth()->id(), 'Latihan Persamaan Kuadrat');
        $this->assignment('different-student', 'Tugas milik siswa lain');

        $this->get('/dashboard#assignments')->assertOk()
            ->assertSeeText('Tugas')
            ->assertSeeText('Penilaian')
            ->assertSee('data-assignment-card', false)
            ->assertSeeText('Latihan Persamaan Kuadrat')
            ->assertDontSeeText('Tugas milik siswa lain')
            ->assertSeeText('0 dari 1 tugas selesai')
            ->assertSeeText('Belum ada nilai');

        $this->postJson(route('dashboard.assignments.submit', $own), ['answer_text' => 'Akar persamaannya adalah dua dan tiga.'])
            ->assertCreated()->assertJsonPath('status', 'submitted');
        $this->get('/dashboard#assignments')->assertOk()->assertSeeText('1 dari 1 tugas selesai');
    }

    public function test_student_can_submit_validated_private_file_and_another_student_cannot_access_it(): void
    {
        $this->registerAndSignIn('Siswa Satu', 'siswa1@test.com');
        $studentId = (string) auth()->id();
        $task = $this->assignment($studentId, 'Unggah laporan', now()->addDay(), true);

        $response = $this->postJson(route('dashboard.assignments.submit', $task), [
            'answer_file' => UploadedFile::fake()->create('jawaban.pdf', 120, 'application/pdf'),
        ])->assertCreated();
        $submission = AssignmentSubmission::query()->firstOrFail();
        $this->assertNotNull($submission->file_path);
        $this->assertFileExists($this->privateFileRoot.DIRECTORY_SEPARATOR.$submission->file_path);

        $this->get(route('dashboard.assignments.submission-file', [$task, $submission]))->assertOk();
        $this->postJson(route('dashboard.assignments.submit', $task), [
            'answer_file' => UploadedFile::fake()->create('bad.exe', 20, 'application/x-msdownload'),
        ])->assertUnprocessable();

        $this->post('/logout');
        $this->registerAndSignIn('Siswa Dua', 'siswa2@test.com');
        $this->getJson(route('dashboard.assignments.show', $task))->assertNotFound();
        $this->get(route('dashboard.assignments.submission-file', [$task, $submission]))->assertNotFound();
        $this->postJson(route('dashboard.assignments.submit', $task), ['answer_text' => 'Tebak'])->assertNotFound();
    }

    public function test_deadline_is_recorded_and_resubmission_requires_permission_before_deadline(): void
    {
        $this->registerAndSignIn('Siswa Satu', 'siswa1@test.com');
        $task = $this->assignment((string) auth()->id(), 'Tugas boleh diperbarui', now()->addDay(), true);
        $this->postJson(route('dashboard.assignments.submit', $task), ['answer_text' => 'Versi pertama'])->assertCreated();
        $this->postJson(route('dashboard.assignments.submit', $task), ['answer_text' => 'Versi kedua'])->assertCreated();
        $this->assertSame('Versi kedua', AssignmentSubmission::query()->firstOrFail()->answer_text);

        $closed = $this->assignment((string) auth()->id(), 'Tugas sudah lewat', now()->subMinute(), true);
        $this->postJson(route('dashboard.assignments.submit', $closed), ['answer_text' => 'Terlambat'])->assertCreated()->assertJsonPath('status', 'late');
        $this->assertDatabaseHas('assignment_submissions', ['student_assignment_id' => $closed->id, 'is_late' => true]);

        $locked = $this->assignment((string) auth()->id(), 'Tidak boleh dikirim ulang', now()->addDay(), false);
        $this->postJson(route('dashboard.assignments.submit', $locked), ['answer_text' => 'Pertama'])->assertCreated();
        $this->postJson(route('dashboard.assignments.submit', $locked), ['answer_text' => 'Kedua'])->assertStatus(409);
    }

    public function test_tutor_can_create_and_grade_only_their_assignment_and_students_cannot_grade(): void
    {
        $this->registerAndSignIn('Siswa Satu', 'siswa1@test.com');
        $studentId = (string) auth()->id();
        $this->post('/logout');
        $this->registerAndSignIn('Tutor Satu', 'tutor1@test.com');
        $this->setAccountRole((string) auth()->id(), 'tutor');
        Auth::logout();
        $this->post('/login', ['contact' => 'tutor1@test.com', 'password' => 'TutorKita123!']);

        $create = $this->postJson(route('dashboard.assignments.create'), [
            'student_id' => $studentId,
            'title' => 'Latihan aljabar tutor',
            'subject' => 'Matematika',
            'instructions' => 'Selesaikan latihan berikut.',
            'due_at' => now()->addDay()->toIso8601String(),
            'accepts_text' => true,
            'accepts_file' => false,
        ])->assertCreated();
        $task = StudentAssignment::query()->findOrFail($create->json('id'));
        $this->postJson(route('dashboard.assignments.grade', [$task, 1]), ['score' => 90, 'feedback' => 'Langkah pengerjaanmu sudah runtut.'])->assertNotFound();

        $submission = AssignmentSubmission::query()->create([
            'student_assignment_id' => $task->id,
            'student_id' => $studentId,
            'answer_text' => 'Jawaban uji.',
            'submitted_at' => now(),
        ]);
        $this->postJson(route('dashboard.assignments.grade', [$task, $submission]), [
            'score' => 91,
            'feedback' => 'Bagus, periksa satu langkah hitung lagi.',
        ])->assertOk();
        $this->assertDatabaseHas('assignment_submissions', ['id' => $submission->id, 'score' => '91.00', 'graded_by_tutor_id' => (string) auth()->id()]);

        $this->post('/logout');
        $this->post('/login', ['contact' => 'siswa1@test.com', 'password' => 'TutorKita123!']);
        $this->postJson(route('dashboard.assignments.grade', [$task, $submission]), ['score' => 101])->assertForbidden();
        $this->getJson(route('dashboard.assignments.show', $task))->assertOk()
            ->assertJsonPath('assignment.submission.score', '91.00')
            ->assertJsonPath('assignment.submission.feedback', 'Bagus, periksa satu langkah hitung lagi.');
    }

    private function assignment(string $studentId, string $title, mixed $dueAt = null, bool $allowResubmission = false): StudentAssignment
    {
        return StudentAssignment::query()->create([
            'student_id' => $studentId,
            'tutor_id' => 'tutor-1',
            'tutor_name' => 'Alya Putri',
            'title' => $title,
            'subject' => 'Matematika',
            'instructions' => 'Kerjakan dan jelaskan langkahnya.',
            'due_at' => $dueAt ?? now()->addDay(),
            'accepts_text' => true,
            'accepts_file' => true,
            'allow_resubmission' => $allowResubmission,
            'max_file_kilobytes' => 10240,
        ]);
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

    private function setAccountRole(string $id, string $role): void
    {
        $users = json_decode(file_get_contents($this->userStorePath), true, flags: JSON_THROW_ON_ERROR);
        foreach ($users as &$user) if ((string) $user['id'] === $id) $user['role'] = $role;
        unset($user);
        file_put_contents($this->userStorePath, json_encode($users, JSON_THROW_ON_ERROR));
    }
}
