@extends('layouts.guest')

@section('content')
<div class="login-card">
    <div class="logo-row">
        <img src="{{ asset('images/crdb-logo.png') }}" alt="CRDB Bank logo" onerror="this.style.display='none'">
    </div>
    <h1>CRDB Customer Segmentation</h1>
    <p class="subtitle">Data Department Field Work Project</p>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}"
                   class="form-control @error('email') is-invalid @enderror" autofocus required>
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

        <div class="mb-3 form-check">
            <input type="checkbox" name="remember" id="remember" class="form-check-input">
            <label for="remember" class="form-check-label">Remember Me</label>
        </div>

        <button type="submit" class="btn btn-crdb">Log In</button>
    </form>

    <p class="text-muted mt-3 mb-0" style="font-size:12px;">
        Default admin login after setup: <code>admin@crdb-segmentation.local</code> /
        <code>crdb-admin-2026</code> — change this immediately.
    </p>
    <p class="text-muted mt-2 mb-0" style="font-size:13px;">
        Don't have an account? <a href="{{ route('register') }}">Sign up</a>
    </p>
</div>
@endsection
