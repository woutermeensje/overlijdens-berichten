<!doctype html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Account') - overlijdens-berichten.nl</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css">
    <style>
        :root { --line:#dde1e6; --bg:#f4f6f8; --card:#fff; --sidebar:#0f2437; --accent:#1e4c73; }
        * { box-sizing: border-box; }
        body { margin:0; font-family: Inter, system-ui, sans-serif; background:var(--bg); color:#1e2630; }
        .shell { min-height:100vh; display:grid; grid-template-columns: 280px 1fr; }
        .sidebar { background:linear-gradient(180deg, #0f2437, #183852); color:#fff; padding:22px 16px; display:flex; flex-direction:column; }
        .brand { color:#fff; text-decoration:none; font-weight:700; font-size:20px; margin-bottom:16px; display:block; }
        .nav { display:flex; flex-direction:column; gap:8px; }
        .nav a { color:#dce7f2; text-decoration:none; padding:11px 12px; border-radius:10px; display:flex; gap:10px; align-items:center; }
        .nav a.active, .nav a:hover { background:rgba(255,255,255,0.11); color:#fff; }
        .sidebar form { margin-top:auto; }
        .logout { width:100%; border:1px solid rgba(255,255,255,.28); background:transparent; color:#fff; border-radius:10px; padding:10px 12px; text-align:left; font:inherit; cursor:pointer; }
        .content { padding:24px; }
        .panel { background:var(--card); border:1px solid var(--line); border-radius:14px; padding:16px; }
        .flash { background:#eaf6ea; border:1px solid #bee0c0; color:#245d29; border-radius:10px; padding:10px 12px; margin-bottom:14px; }
        .grid { display:grid; gap:12px; }
        @media (max-width:900px){ .shell{grid-template-columns:1fr;} .sidebar{padding-bottom:16px;} }
    </style>
</head>
<body>
<div class="shell">
    <aside class="sidebar">
        <a href="{{ route('home') }}" class="brand">overlijdens-berichten.nl</a>
        <nav class="nav">
            <a href="{{ route('account.dashboard') }}" class="{{ request()->routeIs('account.dashboard') ? 'active' : '' }}"><i class="ph ph-house"></i> Dashboard</a>
            <a href="{{ route('account.notices.index') }}" class="{{ request()->routeIs('account.notices.index') ? 'active' : '' }}"><i class="ph ph-list-bullets"></i> Mijn berichten</a>
            <a href="{{ route('account.notices.create') }}" class="{{ request()->routeIs('account.notices.create') ? 'active' : '' }}"><i class="ph ph-plus-circle"></i> Nieuw bericht</a>
        </nav>
        <form method="post" action="{{ route('logout') }}">@csrf<button class="logout" type="submit"><i class="ph ph-sign-out"></i> Uitloggen</button></form>
    </aside>

    <main class="content">
        @if(session('status'))<div class="flash">{{ session('status') }}</div>@endif
        <section class="panel">@yield('content')</section>
    </main>
</div>
</body>
</html>
