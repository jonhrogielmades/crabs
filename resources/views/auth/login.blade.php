@extends('layouts.app')
@section('content')
<section class="auth-screen">
    <form class="auth-card" method="post" action="{{ route('login') }}">@csrf
        <div class="auth-card-head">
            <h2>Login</h2>
            <p>Use your registered account to access scans and detection history.</p>
        </div>

        <div class="field-group">
            <label for="login-email">Email address</label>
            <div class="field-icon"><i data-lucide="mail"></i><input id="login-email" name="email" type="email" placeholder="you@example.com" value="{{ old('email') }}" autocomplete="email" required></div>
            @error('email')<p class="error">{{ $message }}</p>@enderror
        </div>

        <div class="field-group">
            <label for="login-password">Password</label>
            <div class="field-icon"><i data-lucide="lock-keyhole"></i><input id="login-password" name="password" type="password" placeholder="Enter your password" autocomplete="current-password" required></div>
            @error('password')<p class="error">{{ $message }}</p>@enderror
        </div>

        <label class="auth-check"><input type="checkbox" name="remember"> <span>Remember me on this device</span></label>

        <button class="button auth-submit"><i data-lucide="log-in"></i>Login</button>
        <p class="auth-switch">No account yet? <a href="{{ route('register') }}">Create one</a></p>
    </form>
</section>
@endsection
