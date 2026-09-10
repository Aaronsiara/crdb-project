<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') · CRDB Segmentation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="glass-bg">

<div class="sidebar">
    <div class="brand">
        <img src="{{ asset('images/crdb-logo.png') }}" alt="CRDB Bank logo" onerror="this.style.display='none'">
        <div class="brand-text">
            CRDB Segmentation
            <small>Data Department Field Work</small>
        </div>
    </div>
    <nav>
        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">Dashboard</a>
        <a href="{{ route('segmentation.create') }}" class="{{ request()->routeIs('segmentation.create') ? 'active' : '' }}">New Segmentation</a>
        <a href="{{ route('segmentation.index') }}" class="{{ request()->routeIs('segmentation.index') ? 'active' : '' }}">Run History</a>
        <a href="{{ route('about') }}" class="{{ request()->routeIs('about') ? 'active' : '' }}">About / Project History</a>
    </nav>
    @auth
    <div class="user-box">
        <a href="{{ route('profile.edit') }}" class="d-flex align-items-center gap-2 mb-1 text-decoration-none">
            @if (auth()->user()->avatarUrl())
                <img src="{{ auth()->user()->avatarUrl() }}" alt="{{ auth()->user()->name }}"
                     style="width:36px; height:36px; border-radius:50%; object-fit:cover; border:2px solid var(--crdb-gold);">
            @else
                <div style="width:36px; height:36px; border-radius:50%; background:rgba(255,255,255,0.15); display:flex; align-items:center; justify-content:center; font-weight:700; font-size:14px; color:#fff;">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
            @endif
            <div>
                <div style="color:#fff;"><strong>{{ auth()->user()->name }}</strong></div>
                @if (auth()->user()->position)
                    <div style="font-size:11px; color:#cfe8db;">{{ auth()->user()->position }}</div>
                @endif
            </div>
        </a>
        <span class="role">{{ ucfirst(auth()->user()->role) }}</span>
        <div class="mt-2">
            <a href="{{ route('profile.edit') }}" style="font-size:12px; color:#d7ecdf;">Edit Profile</a>
        </div>
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit">Log out</button>
        </form>
    </div>
    @endauth
</div>

<div class="main-content">
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @yield('content')
</div>

</body>
</html>
