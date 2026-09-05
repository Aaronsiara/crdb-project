<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') · CRDB Segmentation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --crdb-green: #00543C;
            --crdb-green-dark: #003D2B;
            --crdb-gold: #F2B705;
            --sidebar-width: 240px;
        }
        body { background: #f5f7fa; margin: 0; }

        .sidebar {
            position: fixed;
            top: 0; left: 0; bottom: 0;
            width: var(--sidebar-width);
            background: var(--crdb-green);
            color: #fff;
            display: flex;
            flex-direction: column;
            border-right: 4px solid var(--crdb-gold);
        }
        .sidebar .brand {
            padding: 18px;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 1px solid rgba(255,255,255,0.15);
        }
        .sidebar .brand img {
            height: 34px;
            width: auto;
            flex-shrink: 0;
        }
        .sidebar .brand .brand-text {
            font-weight: 700;
            font-size: 15px;
            line-height: 1.25;
        }
        .sidebar .brand small {
            display: block;
            font-weight: 400;
            font-size: 11px;
            color: #cfe8db;
            margin-top: 2px;
        }
        .sidebar nav { flex: 1; padding: 12px 0; }
        .sidebar nav a {
            display: block;
            padding: 11px 20px;
            color: #d7ecdf;
            text-decoration: none;
            font-size: 14px;
            border-left: 3px solid transparent;
        }
        .sidebar nav a:hover { background: rgba(255,255,255,0.08); color: #fff; }
        .sidebar nav a.active {
            background: rgba(255,255,255,0.12);
            border-left-color: var(--crdb-gold);
            color: #fff;
            font-weight: 600;
        }
        .sidebar .user-box {
            padding: 16px 20px;
            border-top: 1px solid rgba(255,255,255,0.15);
            font-size: 13px;
        }
        .sidebar .user-box .role {
            display: inline-block;
            background: var(--crdb-gold);
            color: #3a2a00;
            font-size: 11px;
            font-weight: 600;
            border-radius: 10px;
            padding: 1px 8px;
            margin-top: 4px;
        }
        .sidebar .user-box form { margin-top: 10px; }
        .sidebar .user-box button {
            background: none;
            border: 1px solid rgba(255,255,255,0.3);
            color: #fff;
            font-size: 12px;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
        }
        .sidebar .user-box button:hover { background: rgba(255,255,255,0.1); }

        .main-content { margin-left: var(--sidebar-width); padding: 28px 32px 60px; }

        .card { border: 1px solid #e1e5eb; border-radius: 8px; margin-bottom: 22px; }
        .card-header { background: #eef6f1; color: var(--crdb-green); font-weight: 600; }
        .segment-pill {
            display: inline-block;
            background: var(--crdb-gold);
            color: #3a2a00;
            font-weight: 600;
            border-radius: 12px;
            padding: 2px 10px;
            font-size: 12px;
        }
        .btn-crdb { background: var(--crdb-green); border-color: var(--crdb-green); color: #fff; }
        .btn-crdb:hover { background: var(--crdb-green-dark); color: #fff; }

        @media (max-width: 768px) {
            .sidebar { position: static; width: 100%; border-right: none; border-bottom: 4px solid var(--crdb-gold); }
            .main-content { margin-left: 0; }
        }
    </style>
    @stack('styles')
</head>
<body>

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
        <div><strong>{{ auth()->user()->name }}</strong></div>
        <span class="role">{{ ucfirst(auth()->user()->role) }}</span>
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
