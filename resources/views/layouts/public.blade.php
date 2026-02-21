<!doctype html>
<html lang="nl" data-theme="memorial">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="@yield('meta_description', 'Overlijdensberichten, familieberichten en rouwadvertenties.')">
    <title>@yield('title', 'overlijdens-berichten.nl')</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daisyui@5" type="text/css">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css">
    <style>
        [data-theme="memorial"] .btn-primary {
            background-color: #5a3a3a;
            border-color: #5a3a3a;
            color: #fff;
        }
        [data-theme="memorial"] .btn-primary:hover {
            background-color: #4a2f2f;
            border-color: #4a2f2f;
        }
        [data-theme="memorial"] .menu li > a.active,
        [data-theme="memorial"] .menu li > a:active {
            background-color: #f3e9e2;
            color: #4a2f2f;
        }
    </style>
</head>
<body class="min-h-screen bg-base-200 text-base-content">
    <header class="bg-base-100 shadow-sm border-b border-base-300">
        <div class="navbar max-w-6xl w-full mx-auto px-4 min-h-16">
            <div class="navbar-start">
                <div class="dropdown lg:hidden">
                    <label tabindex="0" class="btn btn-ghost btn-square">
                        <i class="ph ph-list"></i>
                    </label>
                    <ul tabindex="0" class="menu menu-sm dropdown-content mt-3 z-[1] p-2 shadow bg-base-100 rounded-box w-72 border border-base-300">
                        <li><a href="{{ route('blog.index') }}"><i class="ph ph-newspaper"></i> Blog</a></li>
                        <li><a href="{{ route('cities.index') }}"><i class="ph ph-map-pin-area"></i> Steden</a></li>
                        <li><a href="{{ route('notice.place') }}"><i class="ph ph-note-pencil"></i> Overlijdensbericht plaatsen</a></li>
                        @auth
                            <li><a href="{{ route('account.dashboard') }}"><i class="ph ph-house"></i> Account</a></li>
                        @else
                            <li><a href="{{ route('login') }}"><i class="ph ph-sign-in"></i> Inloggen</a></li>
                            <li><a href="{{ route('register') }}"><i class="ph ph-user-plus"></i> Account aanmaken</a></li>
                        @endauth
                    </ul>
                </div>
                <a href="{{ route('home') }}" class="btn btn-ghost text-lg">overlijdens-berichten.nl</a>
            </div>
            <div class="navbar-center hidden lg:flex">
                <ul class="menu menu-horizontal px-1 gap-1">
                    <li><a href="{{ route('blog.index') }}"><i class="ph ph-newspaper"></i> Blog</a></li>
                    <li><a href="{{ route('cities.index') }}"><i class="ph ph-map-pin-area"></i> Steden</a></li>
                    <li><a href="{{ route('notice.place') }}"><i class="ph ph-note-pencil"></i> Overlijdensbericht plaatsen</a></li>
                </ul>
            </div>
            <div class="navbar-end gap-2">
                @auth
                    <a href="{{ route('account.dashboard') }}" class="btn btn-ghost btn-sm"><i class="ph ph-house"></i> Account</a>
                    <form method="post" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline btn-sm"><i class="ph ph-sign-out"></i> <span class="hidden sm:inline">Uitloggen</span></button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="btn btn-ghost btn-sm hidden sm:inline-flex"><i class="ph ph-sign-in"></i> Inloggen</a>
                    <a href="{{ route('register') }}" class="btn btn-primary btn-sm"><i class="ph ph-user-plus"></i> <span class="hidden sm:inline">Account aanmaken</span></a>
                @endauth
            </div>
        </div>
    </header>

    <main class="max-w-6xl mx-auto w-full px-4 py-6">
        @yield('content')
    </main>
</body>
</html>
