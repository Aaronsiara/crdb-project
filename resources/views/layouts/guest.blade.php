<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Log In · CRDB Segmentation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --crdb-green: #00543C; --crdb-gold: #F2B705; }
        body {
            background: linear-gradient(135deg, var(--crdb-green) 0%, #00251a 100%);
            min-height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: #fff;
            border-radius: 10px;
            padding: 36px 34px;
            width: 100%;
            max-width: 380px;
            box-shadow: 0 12px 40px rgba(0,0,0,0.25);
            border-top: 5px solid var(--crdb-gold);
        }
        .login-card .logo-row {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 14px;
        }
        .login-card .logo-row img {
            height: 40px;
            width: auto;
        }
        .login-card h1 { font-size: 18px; color: var(--crdb-green); margin-bottom: 4px; }
        .login-card p.subtitle { font-size: 13px; color: #6c7480; margin-bottom: 22px; }
        .btn-crdb { background: var(--crdb-green); border-color: var(--crdb-green); color: #fff; width: 100%; }
        .btn-crdb:hover { background: #003D2B; color: #fff; }
    </style>
</head>
<body>
    @yield('content')
</body>
</html>
