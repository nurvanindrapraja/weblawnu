<!DOCTYPE html>
<html lang="id" x-data="{ sidebarOpen: false }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/webp" href="{{ asset('images/logo_nu.webp') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo_nu.webp') }}">
    <title>@yield('title', 'Dashboard') | {{ \App\Models\Setting::get('site_name', 'LAWNU JATIM') }} Admin</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/tinymce@7.9.3/tinymce.min.js" referrerpolicy="origin"></script>
</head>
<body class="bg-gray-100 text-gray-900 antialiased min-h-screen">
    <!-- Loading Bar -->
    <div id="loading-bar" class="fixed top-0 left-0 h-1 bg-emerald-500 z-[9999] transition-all duration-300" style="width: 0%; display: none;"></div>

    <!-- Toast Container -->
    <div id="toast-container" class="fixed top-4 right-4 z-[9998] space-y-2"></div>

    <!-- Sidebar Overlay (Mobile) -->
    <div x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden" @click="sidebarOpen = false"></div>

    <!-- Sidebar -->
    <aside class="fixed top-0 left-0 h-full w-64 bg-gray-900 text-white z-50 transition-transform duration-300 lg:translate-x-0 flex flex-col"
           :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
        <div class="p-4 border-b border-gray-800 shrink-0">
            <a href="/admin/dashboard" class="flex flex-col items-center gap-2">
                <img src="{{ asset('images/logo_nu_putih.png') }}" alt="Logo Nahdlatul Ulama" class="h-14 w-auto">
                <span class="text-lg font-bold text-emerald-400 text-center leading-tight">{{ \App\Models\Setting::get('site_name', 'LAWNU JATIM') }} Admin</span>
            </a>
        </div>

        <nav class="p-4 space-y-4 overflow-y-auto flex-1">
            @php
                $role = auth()->user()->role_name ?? 'admin';

                // Badge merah: jumlah konten yang perlu direview (admin & redaktur)
                $sidebarReviewPosts = 0;
                $sidebarReviewGalleries = 0;
                if (in_array($role, ['admin', 'redaktur'], true)) {
                    $sidebarReviewPosts = \App\Models\Post::whereIn('status', ['submitted', 'review'])->count();
                    $sidebarReviewGalleries = \App\Models\Gallery::submitted()->count();
                }
            @endphp

            {{-- Dashboard --}}
            <div>
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Dashboard</h3>
                <a href="{{ $role === 'reporter' ? route('reporter.dashboard') : ($role === 'redaktur' ? route('redaktur.dashboard') : route('admin.dashboard')) }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs(str_replace('.', '.', $role . '.dashboard')) ? 'bg-emerald-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    Dashboard
                </a>
            </div>

            @if($role === 'admin')
                {{-- Konten --}}
                <div>
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Konten</h3>
                    <div class="space-y-1">
                        <a href="{{ route('admin.posts.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('admin.posts.*') ? 'bg-emerald-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6m4 8h4M5 4h6a2 2 0 012 2v4a2 2 0 01-2 2H5a2 2 0 01-2-2V6a2 2 0 012-2z"/></svg>
                            Berita
                            @if($sidebarReviewPosts > 0)
                            <span class="ml-auto px-2 py-0.5 rounded-full bg-red-600 text-white text-xs font-bold" title="Berita yang perlu direview">{{ $sidebarReviewPosts }}</span>
                            @endif
                        </a>
                        <a href="{{ route('admin.categories.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('admin.categories.*') ? 'bg-emerald-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.51 0 1.009.105 1.447.304L18 7.809A2 2 0 0119 9.818V19a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z"/></svg>
                            Kategori
                        </a>
                        <a href="{{ route('admin.tags.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('admin.tags.*') ? 'bg-emerald-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21l4-4-4-4 4-4M3 8h14M3 12h14"/></svg>
                            Tag
                        </a>
                        <a href="{{ route('admin.pages.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('admin.pages.*') ? 'bg-emerald-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Halaman
                        </a>
                        <a href="{{ route('admin.galleries.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('admin.galleries.*') ? 'bg-emerald-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            Galeri
                            @if($sidebarReviewGalleries > 0)
                            <span class="ml-auto px-2 py-0.5 rounded-full bg-red-600 text-white text-xs font-bold" title="Galeri yang perlu direview">{{ $sidebarReviewGalleries }}</span>
                            @endif
                        </a>
                        <a href="{{ route('admin.media.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('admin.media.*') ? 'bg-emerald-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            Media Library
                        </a>
                    </div>
                </div>

                {{-- Website --}}
                <div>
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Website</h3>
                    <div class="space-y-1">
                        <a href="{{ route('admin.menus.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('admin.menus.*') ? 'bg-emerald-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                            Menu
                        </a>
                        <a href="{{ route('admin.settings.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('admin.settings.*') ? 'bg-emerald-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            Pengaturan
                        </a>
                    </div>
                </div>

                {{-- Pengguna --}}
                <div>
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Pengguna</h3>
                    <div class="space-y-1">
                        <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('admin.users.*') ? 'bg-emerald-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                            Pengguna
                        </a>
                        <a href="{{ route('admin.roles.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('admin.roles.*') ? 'bg-emerald-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            Role
                        </a>
                        <a href="{{ route('admin.registrations.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('admin.registrations.*') ? 'bg-emerald-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                            Pendaftaran
                        </a>
                    </div>
                </div>

                {{-- Interaksi --}}
                <div>
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Interaksi</h3>
                    <div class="space-y-1">
                        <a href="{{ route('admin.ratings.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('admin.ratings.*') ? 'bg-emerald-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.783-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                            Rating
                        </a>
                        <a href="{{ route('admin.contacts.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('admin.contacts.*') ? 'bg-emerald-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            Pesan Kontak
                        </a>
                    </div>
                </div>

                {{-- Monitoring --}}
                <div>
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Monitoring</h3>
                    <a href="{{ route('admin.logs.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('admin.logs.*') ? 'bg-emerald-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                        Activity Log
                    </a>
                </div>
            @elseif($role === 'reporter')
                {{-- Berita (Reporter) --}}
                <div>
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Berita</h3>
                    <div class="space-y-1">
                        <a href="{{ route('reporter.posts.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('reporter.posts.*') ? 'bg-emerald-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6m4 8h4M5 4h6a2 2 0 012 2v4a2 2 0 01-2 2H5a2 2 0 01-2-2V6a2 2 0 012-2z"/></svg>
                            Semua Berita
                        </a>
                    </div>
                </div>

                {{-- Galeri (Reporter) --}}
                <div>
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Galeri</h3>
                    <a href="{{ route('reporter.galleries.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('reporter.galleries.*') ? 'bg-emerald-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Galeri Kegiatan
                    </a>
                </div>

                {{-- Media --}}
                <div>
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Media</h3>
                    <a href="{{ route('reporter.media.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('reporter.media.*') ? 'bg-emerald-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Media Library
                    </a>
                </div>
            @elseif($role === 'redaktur')
                {{-- Editorial --}}
                <div>
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Editorial</h3>
                    <div class="space-y-1">
                        <a href="{{ route('redaktur.posts.review') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('redaktur.posts.review*') ? 'bg-emerald-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Menunggu Review
                            @if($sidebarReviewPosts > 0)
                            <span class="ml-auto px-2 py-0.5 rounded-full bg-red-600 text-white text-xs font-bold" title="Berita yang perlu direview">{{ $sidebarReviewPosts }}</span>
                            @endif
                        </a>
                        <a href="{{ route('redaktur.posts.index', ['status' => 'approved']) }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('redaktur.posts.index') && request()->query('status') === 'approved' ? 'bg-emerald-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Disetujui
                        </a>
                        <a href="{{ route('redaktur.posts.index', ['status' => 'rejected']) }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('redaktur.posts.index') && request()->query('status') === 'rejected' ? 'bg-emerald-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Ditolak
                        </a>
                        <a href="{{ route('redaktur.posts.index', ['status' => 'published']) }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('redaktur.posts.index') && request()->query('status') === 'published' ? 'bg-emerald-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"/></svg>
                            Published
                        </a>
                    </div>
                </div>

                {{-- Konten --}}
                <div>
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Konten</h3>
                    <div class="space-y-1">
                        <a href="{{ route('redaktur.posts.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('redaktur.posts.index') && !request()->query('status') ? 'bg-emerald-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6m4 8h4M5 4h6a2 2 0 012 2v4a2 2 0 01-2 2H5a2 2 0 01-2-2V6a2 2 0 012-2z"/></svg>
                            Semua Berita
                        </a>
                        <a href="{{ route('redaktur.galleries.index', ['status' => 'submitted']) }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('redaktur.galleries.*') ? 'bg-emerald-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            Review Galeri
                            @if($sidebarReviewGalleries > 0)
                            <span class="ml-auto px-2 py-0.5 rounded-full bg-red-600 text-white text-xs font-bold" title="Galeri yang perlu direview">{{ $sidebarReviewGalleries }}</span>
                            @endif
                        </a>
                    </div>
                </div>

                {{-- Media --}}
                <div>
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Media</h3>
                    <a href="{{ route('redaktur.media.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-gray-300 hover:bg-gray-800">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Media Library
                    </a>
                </div>

                {{-- Interaksi --}}
                <div>
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Interaksi</h3>
                    <a href="{{ route('redaktur.contacts.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('redaktur.contacts.*') ? 'bg-emerald-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        Pesan Kontak
                    </a>
                </div>
            @endif
        </nav>
    </aside>

    <!-- Main Content -->
    <div class="lg:pl-64">
        <!-- Top Bar -->
        <div class="sticky top-0 z-30 bg-white border-b border-gray-200 px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <button class="lg:hidden p-2 rounded-lg hover:bg-gray-100" @click="sidebarOpen = true">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <h1 class="text-lg font-semibold text-gray-900">@yield('page-title', 'Dashboard')</h1>
            </div>

            <div class="flex items-center gap-4">
                <a href="/" target="_blank" class="text-sm text-gray-500 hover:text-emerald-600">View Website</a>

                <!-- User Menu Dropdown -->
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" @click.outside="open = false"
                            class="flex items-center gap-2 p-1.5 rounded-full hover:bg-gray-100 transition-colors" title="{{ auth()->user()->name }}">
                        <span class="w-8 h-8 rounded-full bg-emerald-600 text-white flex items-center justify-center text-sm font-semibold uppercase">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </span>
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>

                    <div x-show="open" x-transition
                         class="absolute right-0 mt-2 w-64 bg-white rounded-xl shadow-lg border border-gray-100 py-2 z-50">
                        <div class="px-4 py-3 border-b border-gray-100">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-gray-500 capitalize">{{ auth()->user()->role_name }}</p>
                        </div>
                        <a href="{{ route('profile.edit') }}"
                           class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            Edit Profil
                        </a>
                        <form method="POST" action="{{ route('logout') }}" id="logout-form">
                            @csrf
                            <button type="button" @click="open = false; confirmLogout()" class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Page Content -->
        <div class="p-4 sm:p-6 lg:p-8">
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">
                    {{ session('success') }}
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    <script>
        // Logout confirmation — reuses the shared Tailwind confirm modal (resources/js/confirm.js).
        function confirmLogout() {
            confirmModal('Apakah Anda yakin ingin keluar dari sistem?', {
                title: 'Konfirmasi Logout',
                okLabel: 'Logout',
                okClass: 'bg-red-600 hover:bg-red-700'
            }).then(function (confirmed) {
                if (confirmed) {
                    document.getElementById('logout-form').submit();
                }
            });
        }
    </script>

    @stack('scripts')
</body>
</html>
