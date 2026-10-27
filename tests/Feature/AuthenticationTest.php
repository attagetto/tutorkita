<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
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
