<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Support\LocalUserStore;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request, LocalUserStore $users): RedirectResponse
    {
        $credentials = $request->validate([
            'contact' => [
                'required', 'string', 'max:255',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (is_string($value) && ! LocalUserStore::isValidContact($value)) {
                        $fail('Masukkan email atau nomor telepon yang valid.');
                    }
                },
            ],
            'password' => ['required', 'string'],
        ], [
            'contact.required' => 'Email atau nomor telepon perlu diisi.',
            'contact.max' => 'Email atau nomor telepon terlalu panjang.',
            'password.required' => 'Password perlu diisi.',
        ]);

        $record = $users->findByContact($credentials['contact']);

        if (! $record) {
            return redirect()->route('login')
                ->withErrors(['contact' => 'Email atau nomor telepon belum terdaftar.'])
                ->withInput($request->only('contact'));
        }

        if (! Auth::attempt([
            'email' => $credentials['contact'],
            'password' => $credentials['password'],
        ])) {
            return redirect()->route('login')
                ->withErrors(['contact' => 'Email atau password yang kamu masukkan salah.'])
                ->withInput($request->only('contact'));
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'))
            ->with('status', 'Selamat datang kembali, '.$record['name'].'!');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'Kamu berhasil keluar.');
    }
}
