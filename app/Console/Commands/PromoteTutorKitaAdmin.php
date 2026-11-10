<?php

namespace App\Console\Commands;

use App\Support\LocalUserStore;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PromoteTutorKitaAdmin extends Command
{
    protected $signature = 'tutorkita:admin';

    protected $description = 'Create an account or promote an existing account to TutorKita admin (interactive and confirmed).';

    public function handle(LocalUserStore $users): int
    {
        $contact = trim((string) $this->ask('Email atau nomor telepon admin'));
        if (! LocalUserStore::isValidContact($contact)) {
            $this->error('Email atau nomor telepon tidak valid.');

            return self::FAILURE;
        }

        $record = $users->findByContact($contact);
        $newAccount = null;
        if (! $record) {
            $name = trim((string) $this->ask('Nama admin'));
            if ($name === '') return self::FAILURE;
            $password = (string) $this->secret('Password admin baru (tidak ditampilkan)');
            $confirmation = (string) $this->secret('Ulangi password admin');
            if ($password === '' || ! hash_equals($password, $confirmation) || strlen($password) < 12) {
                $this->error('Password harus sama dan minimal 12 karakter.');

                return self::FAILURE;
            }
            $newAccount = ['name' => $name, 'password' => $password];
        }

        $confirmationPrompt = $newAccount
            ? 'Buat akun dan jadikan kontak tersebut ADMIN? Akses admin berlaku penuh di aplikasi.'
            : 'Promosikan akun terpilih menjadi ADMIN? Perubahan ini memberi akses administrasi aplikasi.';
        if (! $this->confirm($confirmationPrompt, false)) {
            $this->warn('Tidak ada perubahan role yang dilakukan.');

            return self::SUCCESS;
        }

        if ($newAccount) {
            $record = $users->create($newAccount['name'], $contact, $newAccount['password']);
            if (! $record) {
                $this->error('Akun dengan kontak tersebut sudah terdaftar. Jalankan kembali untuk promosi akun itu.');

                return self::FAILURE;
            }
        }

        $id = (string) $record['id'];
        DB::transaction(function () use ($id): void {
            $oldRole = DB::table('user_roles')->where('user_id', $id)->lockForUpdate()->value('role');
            if ($oldRole === null) {
                DB::table('user_roles')->insert(['user_id' => $id, 'role' => 'admin', 'created_at' => now(), 'updated_at' => now()]);
            } else {
                DB::table('user_roles')->where('user_id', $id)->update(['role' => 'admin', 'updated_at' => now()]);
            }
            DB::table('user_role_changes')->insert([
                'user_id' => $id, 'actor_id' => null, 'old_role' => $oldRole, 'new_role' => 'admin',
                'source' => 'artisan_admin_command', 'created_at' => now(), 'updated_at' => now(),
            ]);
        });

        $this->info('Akun berhasil dipromosikan. Kredensial tidak ditampilkan.');

        return self::SUCCESS;
    }
}
