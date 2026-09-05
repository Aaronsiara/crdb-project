@extends('layouts.guest')

@section('content')
<div class="login-card">
    <div class="logo-row">
        <img src="{{ asset('images/crdb-logo.png') }}" alt="CRDB Bank logo" onerror="this.style.display='none'">
    </div>
    <h1>Create an Account</h1>
    <p class="subtitle">CRDB Customer Segmentation — Data Department</p>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="mb-3">
            <label for="name" class="form-label">Full Name</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}"
                   class="form-control @error('name') is-invalid @enderror" autofocus required>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}"
                   class="form-control @error('email') is-invalid @enderror" required>
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input id="password" type="password" name="password"
                   class="form-control @error('password') is-invalid @enderror" required>
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="password_confirmation" class="form-label">Confirm Password</label>
            <input id="password_confirmation" type="password" name="password_confirmation"
                   class="form-control" required>
        </div>

        <button type="submit" class="btn btn-crdb">Create Account</button>
    </form>

    <p class="text-muted mt-3 mb-0" style="font-size:12px;">
        Already have an account? <a href="{{ route('login') }}">Log in</a>
    </p>
</div>
@endsection
