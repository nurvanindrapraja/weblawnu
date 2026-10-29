@php use App\Models\Setting; @endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/webp" href="{{ asset('images/logo_nu.webp') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo_nu.webp') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', Setting::get('site_name', 'LAWNU JATIM')) | {{ Setting::get('site_name', 'LAWNU JATIM') }}</title>
    <meta name="description" content="@yield('meta_description', Setting::get('site_description', ''))">
    <link rel="canonical" href="{{ request()->url() }}">

    <!-- Open Graph -->
    <meta property="og:title" content="@yield('title', Setting::get('site_name', 'LAWNU JATIM'))">
    <meta property="og:description" content="@yield('meta_description', Setting::get('site_description', ''))">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ request()->url() }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-50 text-gray-900 antialiased min-h-screen flex flex-col">
    <!-- Loading Bar -->
    <div id="loading-bar" class="fixed top-0 left-0 h-1 bg-emerald-500 z-[9999] transition-all duration-300" style="width: 0%; display: none;"></div>

    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Logo -->
                <a href="/" class="flex items-center gap-3">
                    <img src="{{ asset('images/logo_nu.webp') }}" alt="Logo Nahdlatul Ulama" width="61" height="40" class="h-10 w-auto">
                    <span class="text-xl font-bold text-emerald-700 whitespace-nowrap">{{ Setting::get('site_name', 'LAWNU JATIM') }}</span>
                </a>

                <!-- Desktop Nav -->
                <nav class="hidden lg:flex items-center gap-1" id="main-nav">
                    @php $navMenus = \App\Models\Menu::active()->topLevel()->get(); @endphp
                    @foreach($navMenus as $menu)
                        @if($menu->children->isNotEmpty())
                            <div x-data="{ open: false }" class="relative">
                                <div class="flex items-center">
                                    @if($menu->url)
                                        <a href="{{ $menu->url }}" class="px-3 py-2 text-sm font-medium text-gray-700 hover:text-emerald-700 transition-colors">
                                            {{ $menu->title }}
                                        </a>
                                    @else
                                        <button @click="open = !open" class="px-3 py-2 text-sm font-medium text-gray-700 hover:text-emerald-700 transition-colors">
                                            {{ $menu->title }}
                                        </button>
                                    @endif
                                    <button @click="open = !open" type="button" class="p-1.5 -ml-1 text-gray-500 hover:text-emerald-700 transition-colors" aria-label="Buka submenu {{ $menu->title }}">
                                        <svg class="w-4 h-4 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </button>
                                </div>
                                <div x-show="open" @click.outside="open = false" x-transition class="absolute top-full left-0 bg-white shadow-lg rounded-lg border border-gray-100 py-2 w-56">
                                    @foreach($menu->children as $child)
                                        <a href="{{ $child->url }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-emerald-50 hover:text-emerald-700">
                                            {{ $child->title }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <a href="{{ $menu->url }}" class="px-3 py-2 text-sm font-medium text-gray-700 hover:text-emerald-700 transition-colors" {{ $menu->target === '_blank' ? 'target="_blank"' : '' }}>
                                {{ $menu->title }}
                            </a>
                        @endif
                    @endforeach
                </nav>

                <!-- Search & Mobile Toggle -->
                <div class="flex items-center gap-2">
                    <!-- Search -->
                    <form action="/pencarian" method="GET" class="hidden md:block">
                        <input type="text" name="q" placeholder="Cari berita..." class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 w-48" value="{{ request('q') }}">
                    </form>

                    <!-- Auth -->
                    @auth
                        <a href="{{ url('/admin/dashboard') }}" class="hidden md:inline-block px-3 py-1.5 text-sm font-medium text-gray-700 hover:text-emerald-700 transition-colors">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="hidden md:inline-block px-3 py-1.5 text-sm font-medium text-gray-700 hover:text-emerald-700 transition-colors">Login</a>
                    @endauth

                    <!-- Mobile Menu Button -->
                    <button class="lg:hidden p-2 rounded-lg hover:bg-gray-100" id="mobile-menu-btn" type="button">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Nav -->
        <div id="mobile-nav" class="hidden lg:hidden border-t border-gray-200 bg-white">
            <div class="px-4 py-3 space-y-1">
                @php $navMenusMobile = \App\Models\Menu::active()->topLevel()->get(); @endphp
                @foreach($navMenusMobile as $menu)
                    @if($menu->children->isNotEmpty())
                        <div x-data="{ open: false }">
                            <div class="flex items-center justify-between">
                                @if($menu->url)
                                    <a href="{{ $menu->url }}" class="px-3 py-2 text-sm font-medium text-gray-700 hover:text-emerald-700">{{ $menu->title }}</a>
                                @else
                                    <span class="px-3 py-2 text-sm font-medium text-gray-700">{{ $menu->title }}</span>
                                @endif
                                <button @click="open = !open" type="button" class="p-2 rounded-lg hover:bg-gray-100" aria-label="Buka submenu {{ $menu->title }}">
                                    <svg class="w-4 h-4 transition-transform" :class="open ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </button>
                            </div>
                            <div x-show="open" x-transition class="pl-4">
                                @foreach($menu->children as $child)
                                    <a href="{{ $child->url }}" class="block px-3 py-2 text-sm text-gray-600 hover:text-emerald-700">{{ $child->title }}</a>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <a href="{{ $menu->url }}" class="block px-3 py-2 text-sm font-medium text-gray-700 hover:text-emerald-700">{{ $menu->title }}</a>
                    @endif
                @endforeach
                <div class="border-t border-gray-200 mt-2 pt-2">
                    @auth
                        <a href="{{ url('/admin/dashboard') }}" class="block px-3 py-2 text-sm font-medium text-gray-700 hover:text-emerald-700">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="block px-3 py-2 text-sm font-medium text-gray-700 hover:text-emerald-700">Login</a>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 text-gray-300 mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <h3 class="text-white font-bold text-lg mb-4">{{ Setting::get('site_name', 'LAWNU JATIM') }}</h3>
                    <p class="text-sm text-gray-400">{{ Setting::get('site_description', 'Website resmi Lembaga Wakaf dan Pertanahan PWNU Jatim') }}</p>
                </div>
                <div>
                    <h3 class="text-white font-bold mb-4">Kontak</h3>
                    <ul class="space-y-2 text-sm">
                        <li>📧 {{ Setting::get('site_email', 'admin@lwpnujatim.com') }}</li>
                        <li>📱 {{ Setting::get('site_phone', '') }}</li>
                        <li>📍 {{ Setting::get('site_address', '') }}</li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-white font-bold mb-4">Media Sosial</h3>
                    <div class="flex gap-3">
                        @if(Setting::get('facebook_url'))
                            <a href="{{ Setting::get('facebook_url') }}" target="_blank" class="text-gray-400 hover:text-white">Facebook</a>
                        @endif
                        @if(Setting::get('instagram_url'))
                            <a href="{{ Setting::get('instagram_url') }}" target="_blank" class="text-gray-400 hover:text-white">Instagram</a>
                        @endif
                        @if(Setting::get('youtube_url'))
                            <a href="{{ Setting::get('youtube_url') }}" target="_blank" class="text-gray-400 hover:text-white">YouTube</a>
                        @endif
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-800 mt-8 pt-8 text-center text-sm text-gray-500">
                &copy; {{ date('Y') }} {{ Setting::get('site_name', 'LAWNU JATIM') }}. All rights reserved.
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
