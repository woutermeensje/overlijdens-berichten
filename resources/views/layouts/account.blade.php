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
        $description = trim($__env->yieldContent('meta_description')) ?: 'Beheeromgeving.';
        $canonicalUrl = trim($__env->yieldContent('canonical_url')) ?: url()->current();
    @endphp
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }}</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="32x32">
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

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Work+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daisyui@5" type="text/css">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css">
    <style>
        :root {
            --font-sans: "Work Sans", sans-serif;
        }

        html,
        body {
            font-family: var(--font-sans);
        }

        /* Geen box-shadows op het platform, ook niet vanuit daisyUI-componenten */
        [data-theme="memorial"] {
            --depth: 0;
        }
        *,
        *::before,
        *::after {
            box-shadow: none !important;
        }

        /* daisyUI 5 kent .form-control niet meer: label boven het veld, met ruimte ertussen */
        .form-control {
            display: flex;
            flex-direction: column;
            gap: 0.375rem;
        }

        /* Eén border-stijl voor het hele platform: 1px solid base-300, radius 5px */
        [data-theme="memorial"] {
            --radius-box: 5px;
            --radius-field: 5px;
            --radius-selector: 5px;
            --border: 1px;
        }
        [data-theme="memorial"] .input,
        [data-theme="memorial"] .select,
        [data-theme="memorial"] .textarea,
        [data-theme="memorial"] .file-input {
            border: 1px solid var(--color-base-300);
            border-radius: 5px;
        }

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
            <div class="navbar bg-base-100 lg:hidden">
                <div class="flex-none">
                    <label for="account-drawer" class="btn btn-square btn-ghost">
                        <i class="ph ph-list"></i>
                    </label>
                </div>
                <div class="flex-1">
                    @include('partials.logo')
                </div>
            </div>

            <main class="p-4 lg:p-6">
                @if(session('status'))
                    <div class="alert alert-success mb-4"><span>{{ session('status') }}</span></div>
                @endif

                <div class="card bg-base-100 border border-base-300">
                    <div class="card-body">
                        @yield('content')
                    </div>
                </div>
            </main>
        </div>

        <div class="drawer-side">
            <label for="account-drawer" class="drawer-overlay"></label>
            <aside class="w-72 min-h-full bg-base-100 border-r border-base-300 p-4 flex flex-col gap-4">
                @include('partials.logo', ['class' => 'justify-start'])

                <ul class="menu bg-base-100 rounded-box">
                    @if(auth()->user()->isAdmin())
                        <li><a class="{{ request()->routeIs('account.dashboard') ? 'active' : '' }}" href="{{ route('account.dashboard') }}"><i class="ph ph-house"></i> Dashboard</a></li>
                        <li><a class="{{ request()->routeIs('account.subscriptions') ? 'active' : '' }}" href="{{ route('account.subscriptions') }}"><i class="ph ph-bell"></i> Meldingen</a></li>
                        <li><a class="{{ request()->routeIs('account.condolences') ? 'active' : '' }}" href="{{ route('account.condolences') }}"><i class="ph ph-chat-circle-text"></i> Reacties</a></li>
                    @endif
                    <li><a class="{{ request()->routeIs('account.notices.index') ? 'active' : '' }}" href="{{ route('account.notices.index') }}"><i class="ph ph-list-bullets"></i> Mijn berichten</a></li>
                    @if(auth()->user()->isAdmin())
                        <li><a class="{{ request()->routeIs('account.notices.create') ? 'active' : '' }}" href="{{ route('account.notices.create') }}"><i class="ph ph-plus-circle"></i> Nieuw bericht</a></li>
                    @else
                        <li><a href="{{ route('notice.wizard') }}"><i class="ph ph-plus-circle"></i> Nieuw bericht</a></li>
                    @endif
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
