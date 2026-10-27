@extends('layouts.auth')

@section('title', 'Masuk')

@section('intro')
    <h1 id="auth-heading">Masuk ke TutorKita</h1>
@endsection

@section('form')
    <div class="auth-social-block">
        @include('auth.partials.social-buttons')
        <p class="auth-divider"><span>Atau masuk dengan email</span></p>
    </div>
    <form class="auth-form" method="post" action="{{ route('login') }}" data-auth-form="login" novalidate>
        @csrf
        <div class="auth-field">
            <label class="sr-only" for="login-contact">Email atau nomor telepon</label>
            <input id="login-contact" name="contact" type="text" value="{{ old('contact') }}" placeholder="Email atau nomor telepon" autocomplete="username" required data-auth-field>
        </div>
        <div class="auth-field">
            <label class="sr-only" for="login-password">Kata sandi</label>
            <input id="login-password" name="password" type="password" placeholder="Kata sandi" autocomplete="current-password" required data-auth-field>
        </div>
        <button class="auth-forgot" type="button" data-password-help>Lupa kata sandi?</button>
        <button class="auth-submit" type="submit">Masuk</button>
    </form>
@endsection

@section('aside')
    <h2>Halo, Teman<br>Belajar</h2>
    <a class="auth-aside-action" href="{{ route('register') }}">Daftar Sekarang</a>
@endsection
