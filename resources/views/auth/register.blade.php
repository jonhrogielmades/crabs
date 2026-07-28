@extends('layouts.app')
@section('content')
<section class="auth-screen">
    <form class="auth-card" method="post" action="{{ route('register') }}">@csrf
        <div class="auth-card-head">
            <h2>Create Account</h2>
            <p>Set up your account to save scans and submit recognition feedback.</p>
        </div>

        <div class="field-group">
            <label for="register-name">Full name</label>
            <div class="field-icon"><i data-lucide="user-round"></i><input id="register-name" name="name" placeholder="Juan Dela Cruz" value="{{ old('name') }}" autocomplete="name" required></div>
            @error('name')<p class="error">{{ $message }}</p>@enderror
        </div>

        <div class="field-group">
            <label for="register-email">Email address</label>
            <div class="field-icon"><i data-lucide="mail"></i><input id="register-email" name="email" type="email" placeholder="you@example.com" value="{{ old('email') }}" autocomplete="email" required></div>
            @error('email')<p class="error">{{ $message }}</p>@enderror
        </div>

        <div class="field-grid">
            <div class="field-group">
                <label for="register-password">Password</label>
                <div class="field-icon"><i data-lucide="lock-keyhole"></i><input id="register-password" name="password" type="password" placeholder="Password" autocomplete="new-password" required></div>
                @error('password')<p class="error">{{ $message }}</p>@enderror
            </div>
            <div class="field-group">
                <label for="register-password-confirmation">Confirm password</label>
                <div class="field-icon"><i data-lucide="lock-keyhole"></i><input id="register-password-confirmation" name="password_confirmation" type="password" placeholder="Confirm" autocomplete="new-password" required></div>
            </div>
        </div>

        <button class="button auth-submit"><i data-lucide="user-plus"></i>Register</button>
        <p class="auth-switch">Already registered? <a href="{{ route('login') }}">Login</a></p>
    </form>
</section>
@endsection
