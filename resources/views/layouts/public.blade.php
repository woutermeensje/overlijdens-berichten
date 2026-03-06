<!doctype html>
<html lang="nl" data-theme="memorial">
<head>
    @php
        $configuredSiteName = trim((string) config('app.name', ''));
        $siteName = $configuredSiteName !== '' && $configuredSiteName !== 'Laravel'
            ? $configuredSiteName
            : 'overlijdens-berichten.nl';
        $defaultDescription = 'Overlijdensberichten, familieberichten en rouwadvertenties.';
        $pageTitle = trim($__env->yieldContent('title'));
        $title = $pageTitle !== '' ? $pageTitle.' | '.$siteName : $siteName;
        $description = trim($__env->yieldContent('meta_description')) ?: $defaultDescription;
        $robots = trim($__env->yieldContent('meta_robots'));

        if ($robots === '') {
            $robots = request()->has('q')
                ? 'noindex,follow,max-snippet:-1,max-image-preview:large,max-video-preview:-1'
                : 'index,follow,max-snippet:-1,max-image-preview:large,max-video-preview:-1';
        }

        $canonicalUrl = trim($__env->yieldContent('canonical_url')) ?: url()->current();
        $ogType = trim($__env->yieldContent('og_type')) ?: 'website';
        $ogImage = trim($__env->yieldContent('og_image')) ?: asset('favicon.ico');
        $twitterCard = trim($__env->yieldContent('twitter_card')) ?: 'summary';
    @endphp
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }}</title>
    <meta name="description" content="{{ $description }}">
    <meta name="robots" content="{{ $robots }}">
    <link rel="canonical" href="{{ $canonicalUrl }}">
    <meta name="author" content="{{ $siteName }}">

    <meta property="og:locale" content="nl_NL">
    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:type" content="{{ $ogType }}">
    <meta property="og:title" content="{{ $pageTitle !== '' ? $pageTitle : $siteName }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:image" content="{{ $ogImage }}">

    <meta name="twitter:card" content="{{ $twitterCard }}">
    <meta name="twitter:title" content="{{ $pageTitle !== '' ? $pageTitle : $siteName }}">
    <meta name="twitter:description" content="{{ $description }}">
    <meta name="twitter:image" content="{{ $ogImage }}">

    @hasSection('structured_data')
        @yield('structured_data')
    @endif
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
<body class="min-h-screen bg-base-200 text-base-content flex flex-col">
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
                        <li><a href="{{ route('notice.wizard') }}"><i class="ph ph-note-pencil"></i> Bericht plaatsen</a></li>
                        @auth
                            <li><a href="{{ route('account.dashboard') }}"><i class="ph ph-house"></i> Account</a></li>
                        @else
                            <li><a href="{{ route('login') }}"><i class="ph ph-sign-in"></i> Inloggen (uitvaartondernemer)</a></li>
                        @endauth
                    </ul>
                </div>
                <a href="{{ route('home') }}" class="btn btn-ghost text-lg">overlijdens-berichten.nl</a>
            </div>
            <div class="navbar-center hidden lg:flex">
                <ul class="menu menu-horizontal px-1 gap-1">
                    <li><a href="{{ route('blog.index') }}"><i class="ph ph-newspaper"></i> Blog</a></li>
                    <li><a href="{{ route('cities.index') }}"><i class="ph ph-map-pin-area"></i> Steden</a></li>
                    <li><a href="{{ route('notice.wizard') }}"><i class="ph ph-note-pencil"></i> Bericht plaatsen</a></li>
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
                    <a href="{{ route('login') }}" class="btn btn-ghost btn-sm hidden sm:inline-flex"><i class="ph ph-sign-in"></i> Inloggen ondernemer</a>
                    <a href="{{ route('notice.wizard') }}" class="btn btn-primary btn-sm"><i class="ph ph-note-pencil"></i> <span class="hidden sm:inline">Bericht plaatsen</span></a>
                @endauth
            </div>
        </div>
    </header>

    <main class="max-w-6xl mx-auto w-full px-4 py-6 flex-1">
        @yield('content')
    </main>

    <footer class="bg-black text-white">
        <div class="max-w-6xl mx-auto w-full px-4 py-8">
            <h2 class="text-lg font-semibold mb-3">Steden</h2>
            @if(!empty($footerCities))
                <div class="flex flex-wrap gap-2">
                    @foreach($footerCities as $city)
                        <a
                            href="{{ route('city.show', ['city' => $city['slug']]) }}"
                            class="inline-flex items-center rounded-md border border-white/20 px-3 py-1.5 text-sm hover:bg-white hover:text-black transition-colors"
                        >
                            {{ $city['name'] }}
                        </a>
                    @endforeach
                </div>
            @else
                <p class="text-white/70 text-sm">Nog geen steden beschikbaar.</p>
            @endif
        </div>
    </footer>
</body>
</html>
