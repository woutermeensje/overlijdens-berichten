<!doctype html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="@yield('meta_description', 'Overlijdensberichten, familieberichten en rouwadvertenties.')">
    <title>@yield('title', 'overlijdens-berichten.nl')</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css">
    <style>
        :root { --bg:#f6f6f5; --card:#fff; --text:#1e1e1e; --line:#d9d9d7; --brand:#193a59; }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Inter, system-ui, -apple-system, Segoe UI, sans-serif; background: var(--bg); color: var(--text); }
        .container { width: min(1200px, 92vw); margin: 0 auto; }
        .topbar { background: #fff; border-bottom: 1px solid var(--line); }
        .topbar-inner { display:flex; justify-content:space-between; align-items:center; padding:16px 0; }
        .brand { font-weight: 700; color: var(--brand); text-decoration: none; font-size: 20px; }
        .menu { display:flex; gap:10px; align-items:center; }
        .menu a, .menu button { border:1px solid var(--line); background:#fff; padding:10px 12px; border-radius:10px; text-decoration:none; color:var(--text); font:inherit; cursor:pointer; }
        .menu .primary { background: var(--brand); color:#fff; border-color: var(--brand); }
        main { padding: 28px 0 40px; }
        .card { background: var(--card); border:1px solid var(--line); border-radius: 14px; padding: 18px; }
        .grid { display:grid; gap:14px; }
        .grid-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        @media (max-width: 950px) { .grid-3 { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
<header class="topbar">
    <div class="container topbar-inner">
        <a href="{{ route('home') }}" class="brand">overlijdens-berichten.nl</a>
        <nav class="menu">
            <a href="{{ route('notice.place') }}"><i class="ph ph-note-pencil"></i> Overlijdensbericht plaatsen</a>
            @auth
                <a href="{{ route('account.dashboard') }}"><i class="ph ph-house"></i> Account</a>
                <form method="post" action="{{ route('logout') }}">@csrf<button type="submit"><i class="ph ph-sign-out"></i> Uitloggen</button></form>
            @else
                <a href="{{ route('login') }}"><i class="ph ph-sign-in"></i> Inloggen</a>
                <a class="primary" href="{{ route('register') }}"><i class="ph ph-user-plus"></i> Account aanmaken</a>
            @endauth
        </nav>
    </div>
</header>

<main>
    <div class="container">
        @yield('content')
    </div>
</main>
</body>
</html>
