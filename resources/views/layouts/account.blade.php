<!doctype html>
<html lang="nl" data-theme="memorial">
<head>
    @php
        $configuredSiteName = trim((string) config('app.name', ''));
        $siteName = $configuredSiteName !== '' && $configuredSiteName !== 'Laravel'
            ? $configuredSiteName
            : 'overlijdens-berichten.nl';
        $pageTitle = trim($__env->yieldContent('title')) ?: 'Account';
        $title = $pageTitle.' | '.$siteName;
        $description = trim($__env->yieldContent('meta_description')) ?: 'Beheeromgeving voor uitvaartondernemers.';
        $canonicalUrl = trim($__env->yieldContent('canonical_url')) ?: url()->current();
    @endphp
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }}</title>
    <meta name="description" content="{{ $description }}">
    <meta name="robots" content="noindex,nofollow,noarchive,nosnippet,noimageindex">
    <link rel="canonical" href="{{ $canonicalUrl }}">
    <meta property="og:locale" content="nl_NL">
    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $description }}">
    @stack('head')

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
<body class="bg-base-200 min-h-screen">
    <div class="drawer lg:drawer-open">
        <input id="account-drawer" type="checkbox" class="drawer-toggle" />

        <div class="drawer-content">
            <div class="navbar bg-base-100 shadow-sm lg:hidden">
                <div class="flex-none">
                    <label for="account-drawer" class="btn btn-square btn-ghost">
                        <i class="ph ph-list"></i>
                    </label>
                </div>
                <div class="flex-1">
                    <a href="{{ route('home') }}" class="btn btn-ghost text-lg">overlijdens-berichten.nl</a>
                </div>
            </div>

            <main class="p-4 lg:p-6">
                @if(session('status'))
                    <div class="alert alert-success mb-4"><span>{{ session('status') }}</span></div>
                @endif

                <div class="card bg-base-100 shadow-sm border border-base-300">
                    <div class="card-body">
                        @yield('content')
                    </div>
                </div>
            </main>
        </div>

        <div class="drawer-side">
            <label for="account-drawer" class="drawer-overlay"></label>
            <aside class="w-72 min-h-full bg-base-100 border-r border-base-300 p-4 flex flex-col gap-4">
                <a href="{{ route('home') }}" class="btn btn-ghost justify-start text-lg">overlijdens-berichten.nl</a>

                <ul class="menu bg-base-100 rounded-box">
                    <li><a class="{{ request()->routeIs('account.dashboard') ? 'active' : '' }}" href="{{ route('account.dashboard') }}"><i class="ph ph-house"></i> Dashboard</a></li>
                    <li><a class="{{ request()->routeIs('account.notices.index') ? 'active' : '' }}" href="{{ route('account.notices.index') }}"><i class="ph ph-list-bullets"></i> Mijn berichten</a></li>
                    <li><a class="{{ request()->routeIs('account.notices.create') ? 'active' : '' }}" href="{{ route('account.notices.create') }}"><i class="ph ph-plus-circle"></i> Nieuw bericht</a></li>
                </ul>

                <div class="mt-auto">
                    <form method="post" action="{{ route('logout') }}">
                        @csrf
                        <button class="btn btn-outline w-full" type="submit"><i class="ph ph-sign-out"></i> Uitloggen</button>
                    </form>
                </div>
            </aside>
        </div>
    </div>
</body>
</html>
