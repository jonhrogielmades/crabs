@extends('layouts.app')
@section('content')
<section class="page-head feature-head profile-head">
    <div>
        <p class="eyebrow">Account center</p>
        <h1>Profile</h1>
    </div>
    <span class="badge"><i data-lucide="user-round"></i>{{ $user->role }}</span>
</section>

<section class="stats feature-stats">
    <div class="profile-stat"><i data-lucide="file-search"></i><span>Total scans</span><strong>{{ $total }}</strong></div>
    <div class="profile-stat success"><i data-lucide="check-circle-2"></i><span>Recognized</span><strong>{{ $recognized }}</strong></div>
    <div class="profile-stat info"><i data-lucide="clipboard-check"></i><span>Feedback</span><strong>{{ $feedback }}</strong></div>
    <div class="profile-stat muted-stat"><i data-lucide="clock-3"></i><span>Last scan</span><strong>{{ $lastScan?->created_at?->format('M d') ?? 'None' }}</strong></div>
</section>

@if(session('status'))
    <section class="toast profile-toast"><i data-lucide="check-circle-2"></i>{{ session('status') }}</section>
@endif

<form class="panel saas-form profile-form" method="post" action="{{ route('profile.update') }}">
    @csrf
    @method('put')
    <div class="profile-form-head">
        <span class="profile-form-icon"><i data-lucide="user-round"></i></span>
        <div>
            <h2>Account Details</h2>
            <p>Update your name, email, or password.</p>
        </div>
    </div>
    <div class="field-grid">
        <div class="field-group">
            <label for="profileName">Name</label>
            <div class="field-icon"><i data-lucide="user-round"></i><input id="profileName" name="name" value="{{ old('name', $user->name) }}" required></div>
        </div>
        <div class="field-group">
            <label for="profileEmail">Email</label>
            <div class="field-icon"><i data-lucide="mail"></i><input id="profileEmail" name="email" type="email" value="{{ old('email', $user->email) }}" required></div>
        </div>
    </div>
    <div class="field-grid">
        <div class="field-group">
            <label for="currentPassword">Current password</label>
            <div class="field-icon password-field">
                <i data-lucide="lock-keyhole"></i>
                <input id="currentPassword" name="current_password" type="password" autocomplete="current-password">
                <button class="password-toggle" type="button" data-password-toggle aria-controls="currentPassword" aria-label="Show password" aria-pressed="false">
                    <i class="password-eye" data-lucide="eye" aria-hidden="true"></i>
                    <i class="password-eye-off" data-lucide="eye-off" aria-hidden="true"></i>
                </button>
            </div>
        </div>
        <div class="field-group">
            <label for="newPassword">New password</label>
            <div class="field-icon password-field">
                <i data-lucide="shield-check"></i>
                <input id="newPassword" name="password" type="password" autocomplete="new-password">
                <button class="password-toggle" type="button" data-password-toggle aria-controls="newPassword" aria-label="Show password" aria-pressed="false">
                    <i class="password-eye" data-lucide="eye" aria-hidden="true"></i>
                    <i class="password-eye-off" data-lucide="eye-off" aria-hidden="true"></i>
                </button>
            </div>
        </div>
    </div>
    <div class="field-group">
        <label for="confirmPassword">Confirm password</label>
        <div class="field-icon password-field">
            <i data-lucide="lock-keyhole"></i>
            <input id="confirmPassword" name="password_confirmation" type="password" autocomplete="new-password">
            <button class="password-toggle" type="button" data-password-toggle aria-controls="confirmPassword" aria-label="Show password" aria-pressed="false">
                <i class="password-eye" data-lucide="eye" aria-hidden="true"></i>
                <i class="password-eye-off" data-lucide="eye-off" aria-hidden="true"></i>
            </button>
        </div>
    </div>
    @foreach($errors->all() as $error)<p class="error">{{ $error }}</p>@endforeach
    <button class="button" type="submit"><i data-lucide="save"></i>Save Profile</button>
</form>
@endsection
