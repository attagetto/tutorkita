<div class="auth-socials" role="group" aria-label="Pilihan masuk dengan akun sosial">
    @foreach (['google' => 'Google', 'facebook' => 'Facebook', 'github' => 'GitHub', 'linkedin' => 'LinkedIn'] as $provider => $label)
        <button class="auth-social-button auth-social-{{ $provider }}" type="button" aria-label="Lanjutkan dengan {{ $label }}" data-social-auth="{{ $label }}">
            <x-auth.social-icon :provider="$provider" />
        </button>
    @endforeach
</div>
