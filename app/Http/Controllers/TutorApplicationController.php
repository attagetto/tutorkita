<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Models\TutorApplication;
use App\Models\TutorApplicationReview;
use App\Models\TutorProfile;
use App\Models\UserNotification;
use App\Support\LocalUserStore;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TutorApplicationController extends Controller
{
    public function updateProfile(Request $request, LocalUserStore $users): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'mimetypes:image/jpeg,image/png,image/webp', 'max:2048'],
            'education_level' => ['sometimes', 'nullable', 'string', 'max:80'],
            'institution' => ['sometimes', 'nullable', 'string', 'max:180'],
            'program' => ['sometimes', 'nullable', 'string', 'max:180'],
            'graduation_year' => ['sometimes', 'nullable', 'integer', 'between:1950,'.(now()->year + 8)],
            'bio' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'experience' => ['sometimes', 'nullable', 'string', 'max:4000'],
            'additional_info' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'availability' => ['sometimes', 'nullable', 'array', 'max:21'],
            'availability.*' => ['string', 'max:120'],
            'subject_ids' => ['sometimes', 'array', 'max:30'],
            'subject_ids.*' => ['integer', 'distinct', 'exists:subjects,id'],
            'availability_present' => ['sometimes', 'accepted'],
            'subject_ids_present' => ['sometimes', 'accepted'],
        ]);
        $userId = (string) $request->user()->getAuthIdentifier();
        $oldAvatar = $request->user()->avatar_path;
        $newAvatar = $request->file('avatar')?->store('profile-photos/'.$userId, 'local');

        try {
            DB::transaction(function () use ($data, $request, $userId, $newAvatar): void {
                if (($request->user()->role ?? 'student') === 'tutor') {
                    $profile = TutorProfile::query()->firstOrNew(['user_id' => $userId]);
                    foreach (['education_level', 'institution', 'program', 'graduation_year', 'bio', 'experience', 'additional_info', 'availability'] as $field) {
                        if (array_key_exists($field, $data)) $profile->{$field} = $data[$field];
                    }
                    if (array_key_exists('availability', $data) || array_key_exists('availability_present', $data)) $profile->availability = $data['availability'] ?? [];
                    if ($newAvatar) $profile->avatar_path = $newAvatar;
                    $profile->is_active = true;
                    $profile->save();
                    if (array_key_exists('subject_ids', $data) || array_key_exists('subject_ids_present', $data)) $profile->subjects()->sync($data['subject_ids'] ?? []);
                }
            });

            $update = ['name' => trim($data['name'])];
            if ($newAvatar) $update['avatar_path'] = $newAvatar;
            if (! $users->updateProfile($userId, $update)) throw new \RuntimeException('Akun tidak ditemukan.');
        } catch (\Throwable $exception) {
            if ($newAvatar) Storage::disk('local')->delete($newAvatar);
            throw $exception;
        }

        if ($newAvatar && $oldAvatar) Storage::disk('local')->delete($oldAvatar);

        return back()->with('status', 'Profil berhasil diperbarui.');
    }

    public function submit(Request $request, LocalUserStore $users): RedirectResponse
    {
        abort_unless(($request->user()->role ?? 'student') === 'student', 403);
        $userId = (string) $request->user()->getAuthIdentifier();
        $existing = TutorApplication::query()->where('user_id', $userId)->first();
        abort_if($existing?->status === 'pending', 409, 'Pengajuanmu masih menunggu pemeriksaan admin.');
        abort_if($existing?->status === 'approved' || ($request->user()->role ?? '') === 'tutor', 409, 'Akunmu sudah menjadi tutor.');

        $data = $request->validate([
            'applicant_name' => ['required', 'string', 'max:120'],
            'education_level' => ['required', 'string', 'max:80'],
            'institution' => ['required', 'string', 'max:180'],
            'program' => ['required', 'string', 'max:180'],
            'graduation_year' => ['required', 'integer', 'between:1950,'.(now()->year + 8)],
            'subject_ids' => ['required', 'array', 'min:1', 'max:30'],
            'subject_ids.*' => ['required', 'integer', 'distinct', 'exists:subjects,id'],
            'bio' => ['required', 'string', 'min:20', 'max:2000'],
            'experience' => ['nullable', 'string', 'max:4000'],
            'additional_info' => ['nullable', 'string', 'max:2000'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'mimetypes:image/jpeg,image/png,image/webp', 'max:2048'],
        ], [
            'avatar.image' => 'Unggah foto JPG, PNG, atau WebP yang valid.',
            'avatar.max' => 'Ukuran foto maksimal 2 MB.',
            'subject_ids.required' => 'Pilih minimal satu mata pelajaran yang kamu kuasai.',
            'bio.min' => 'Biografi minimal 20 karakter.',
        ]);
        $newAvatar = $request->file('avatar')?->store('profile-photos/'.$userId, 'local');
        $avatar = $newAvatar ?: $request->user()->avatar_path;
        abort_if(! $avatar, 422, 'Tambahkan foto profil sebelum mengirim pengajuan.');
        $oldAvatar = $request->user()->avatar_path;

        try {
            DB::transaction(function () use ($data, $userId, $avatar, $existing): void {
                $application = TutorApplication::query()->lockForUpdate()->firstOrNew(['user_id' => $userId]);
                if ($application->exists && $application->status === 'pending') {
                    throw ValidationException::withMessages(['application' => 'Pengajuanmu masih menunggu pemeriksaan admin.']);
                }
                if ($application->exists && $application->status === 'approved') {
                    throw ValidationException::withMessages(['application' => 'Akunmu sudah menjadi tutor.']);
                }
                $fromStatus = $application->status;
                $application->fill([
                    'applicant_name' => trim($data['applicant_name']), 'education_level' => $data['education_level'],
                    'institution' => trim($data['institution']), 'program' => trim($data['program']),
                    'graduation_year' => $data['graduation_year'], 'bio' => trim($data['bio']),
                    'experience' => $data['experience'] ?? null, 'additional_info' => $data['additional_info'] ?? null,
                    'avatar_path' => $avatar, 'status' => 'pending', 'rejection_reason' => null,
                    'reviewed_by' => null, 'submitted_at' => now(), 'reviewed_at' => null, 'approved_at' => null,
                ]);
                $application->save();
                $application->subjects()->sync($data['subject_ids']);
                $application->reviews()->create([
                    'actor_id' => $userId, 'action' => $fromStatus === 'rejected' ? 'resubmitted' : 'submitted',
                    'from_status' => $fromStatus, 'to_status' => 'pending',
                ]);
            });
        } catch (\Throwable $exception) {
            if ($newAvatar) Storage::disk('local')->delete($newAvatar);
            throw $exception;
        }

        if ($newAvatar) {
            if (! $users->updateProfile($userId, ['avatar_path' => $newAvatar])) {
                Storage::disk('local')->delete($newAvatar);
                throw new \RuntimeException('Akun tidak ditemukan.');
            }
            if ($oldAvatar) Storage::disk('local')->delete($oldAvatar);
        }

        return redirect('/dashboard#profile')->with('status', 'Pengajuan tutor berhasil dikirim. Statusnya sedang menunggu pemeriksaan admin.');
    }

    public function decide(Request $request, TutorApplication $tutorApplication, LocalUserStore $users): RedirectResponse
    {
        abort_unless(($request->user()->role ?? null) === 'admin', 403);
        $data = $request->validate([
            'decision' => ['required', Rule::in(['approve', 'reject'])],
            'reason' => ['required_if:decision,reject', 'nullable', 'string', 'min:5', 'max:2000'],
        ], ['reason.required_if' => 'Alasan penolakan wajib diisi.', 'reason.min' => 'Alasan penolakan minimal 5 karakter.']);
        $candidateId = (string) $tutorApplication->user_id;
        $candidate = $users->findById($candidateId);
        abort_unless($candidate, 404, 'Akun pemohon tidak ditemukan.');
        $candidateName = (string) $candidate['name'];

        DB::transaction(function () use ($request, $tutorApplication, $data, $candidateId, $candidateName): void {
            $application = TutorApplication::query()->whereKey($tutorApplication->id)->lockForUpdate()->with('subjects')->firstOrFail();
            abort_unless($application->status === 'pending', 409, 'Pengajuan ini sudah diproses.');
            $role = DB::table('user_roles')->where('user_id', $candidateId)->lockForUpdate()->value('role');
            abort_unless($role === 'student', 409, 'Hanya akun siswa yang dapat disetujui menjadi tutor.');
            $adminId = (string) $request->user()->getAuthIdentifier();
            $approved = $data['decision'] === 'approve';
            $application->update([
                'status' => $approved ? 'approved' : 'rejected',
                'rejection_reason' => $approved ? null : trim((string) $data['reason']),
                'reviewed_by' => $adminId, 'reviewed_at' => now(), 'approved_at' => $approved ? now() : null,
            ]);
            $application->reviews()->create([
                'actor_id' => $adminId, 'action' => $data['decision'], 'from_status' => 'pending',
                'to_status' => $approved ? 'approved' : 'rejected', 'reason' => $approved ? null : trim((string) $data['reason']),
            ]);

            if ($approved) {
                $profile = TutorProfile::query()->updateOrCreate(['user_id' => $candidateId], [
                    'education_level' => $application->education_level, 'institution' => $application->institution,
                    'program' => $application->program, 'graduation_year' => $application->graduation_year,
                    'bio' => $application->bio, 'experience' => $application->experience,
                    'additional_info' => $application->additional_info, 'avatar_path' => $application->avatar_path,
                    'is_active' => true,
                ]);
                $profile->subjects()->sync($application->subjects->modelKeys());
                $updated = DB::table('user_roles')->where('user_id', $candidateId)->where('role', 'student')->update(['role' => 'tutor', 'updated_at' => now()]);
                abort_unless($updated === 1, 409, 'Role akun berubah. Muat ulang daftar pengajuan.');
                DB::table('user_role_changes')->insert([
                    'user_id' => $candidateId, 'actor_id' => $adminId, 'old_role' => 'student', 'new_role' => 'tutor',
                    'source' => 'tutor_application_approval', 'created_at' => now(), 'updated_at' => now(),
                ]);
            }

            UserNotification::query()->create([
                'user_id' => $candidateId,
                'notification_key' => 'tutor-application-'.$application->id.'-'.$application->status.'-'.$application->updated_at->timestamp,
                'category' => 'system', 'title' => $approved ? 'Pengajuan tutor disetujui' : 'Pengajuan tutor belum disetujui',
                'body' => $approved ? 'Selamat, akunmu sekarang memiliki akses TutorKita sebagai tutor.' : 'Pengajuanmu ditolak. Alasan: '.trim((string) $data['reason']),
                'action_url' => '/dashboard#profile',
            ]);
        });

        return back()->with('status', $data['decision'] === 'approve' ? $candidateName.' disetujui sebagai tutor.' : 'Pengajuan '.$candidateName.' ditolak.');
    }

    public function photo(Request $request, string $userId, LocalUserStore $users): StreamedResponse
    {
        abort_unless(($request->user()->role ?? '') === 'admin' || (string) $request->user()->getAuthIdentifier() === $userId, 403);
        $record = $users->findById($userId);
        $path = $record['avatar_path'] ?? TutorProfile::query()->where('user_id', $userId)->value('avatar_path');
        if (! $path && ($request->user()->role ?? '') === 'admin') $path = TutorApplication::query()->where('user_id', $userId)->value('avatar_path');
        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path, headers: ['Cache-Control' => 'private, max-age=300']);
    }
}
