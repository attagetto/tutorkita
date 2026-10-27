<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Support\LocalUserStore;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request, LocalUserStore $users): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'contact' => [
                'required', 'string', 'max:255',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (is_string($value) && ! LocalUserStore::isValidContact($value)) {
                        $fail('Masukkan email atau nomor telepon yang valid.');
                    }
                },
            ],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'terms' => ['accepted'],
        ], [
            'name.required' => 'Nama lengkap perlu diisi.',
            'name.max' => 'Nama lengkap maksimal 120 karakter.',
            'contact.required' => 'Email atau nomor telepon perlu diisi.',
            'contact.max' => 'Email atau nomor telepon terlalu panjang.',
            'password.required' => 'Password perlu diisi.',
            'password.min' => 'Gunakan password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password belum sama.',
            'terms.accepted' => 'Persetujuan syarat dan ketentuan diperlukan.',
        ]);

        $record = $users->create($data['name'], $data['contact'], $data['password']);

        if (! $record) {
            return redirect()->route('register')
                ->withErrors(['contact' => 'Email atau nomor telepon sudah terdaftar.'])
                ->withInput($request->only('name', 'contact'));
        }

        return redirect()->route('login')
            ->with('status', 'Pendaftaran berhasil. Silakan masuk menggunakan akunmu.');
    }
}
