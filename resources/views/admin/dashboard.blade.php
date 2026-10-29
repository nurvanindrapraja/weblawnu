@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('content')
    <!-- Notifikasi: galeri yang perlu direview -->
    @if(($stats['galleries']['submitted'] ?? 0) > 0)
    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <div class="p-2.5 bg-red-100 rounded-lg">
                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            </div>
            <div>
                <p class="text-sm font-semibold text-red-800">{{ $stats['galleries']['submitted'] }} galeri kegiatan perlu direview</p>
                <p class="text-xs text-red-600 mt-0.5">Galeri dari reporter menunggu persetujuan Anda untuk dipublikasikan.</p>
            </div>
        </div>
        <a href="{{ route('admin.galleries.index', ['status' => 'submitted']) }}" class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700">Review Sekarang</a>
    </div>
    @endif

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-xl p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Total Berita</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['posts']['total'] }}</p>
                </div>
                <div class="p-3 bg-emerald-100 rounded-lg">
                    <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9"/></svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Menunggu Review</p>
                    <p class="text-2xl font-bold text-yellow-600 mt-1">{{ $stats['posts']['submitted'] + $stats['posts']['review'] }}</p>
                </div>
                <div class="p-3 bg-yellow-100 rounded-lg">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Published</p>
                    <p class="text-2xl font-bold text-green-600 mt-1">{{ $stats['posts']['published'] }}</p>
                </div>
                <div class="p-3 bg-green-100 rounded-lg">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Pesan Baru</p>
                    <p class="text-2xl font-bold text-blue-600 mt-1">{{ $stats['new_messages'] }}</p>
                </div>
                <div class="p-3 bg-blue-100 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Second Row Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
        <div class="bg-white rounded-xl p-4 shadow-sm flex items-center gap-3">
            <div class="p-2 bg-purple-100 rounded-lg">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </div>
            <div>
                <p class="text-sm text-gray-500">Pengguna</p>
                <p class="text-xl font-bold text-gray-900">{{ $stats['users'] }}</p>
            </div>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm flex items-center gap-3">
            <div class="p-2 bg-orange-100 rounded-lg">
                <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            </div>
            <div>
                <p class="text-sm text-gray-500">Total Pesan</p>
                <p class="text-xl font-bold text-gray-900">{{ $stats['contact_messages'] }}</p>
            </div>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm flex items-center gap-3">
            <div class="p-2 bg-pink-100 rounded-lg">
                <svg class="w-5 h-5 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.783-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
            </div>
            <div>
                <p class="text-sm text-gray-500">Rating</p>
                <p class="text-xl font-bold text-gray-900">{{ number_format($stats['ratings']) }}</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Posts -->
        <div class="bg-white rounded-xl shadow-sm">
            <div class="p-5 border-b border-gray-100 flex items-center justify-between">
                <h2 class="font-semibold text-gray-900">Berita Terbaru</h2>
                <a href="{{ route('admin.posts.index') }}" class="text-sm text-emerald-600 hover:text-emerald-700">Lihat Semua</a>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($recentPosts as $post)
                <div class="p-4 flex items-center justify-between hover:bg-gray-50">
                    <div class="min-w-0">
                        <a href="{{ route('admin.posts.show', $post) }}" class="text-sm font-medium text-gray-900 hover:text-emerald-600 truncate">{{ $post->title }}</a>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $post->created_at->format('d M Y H:i') }}</p>
                    </div>
                    <span class="px-2 py-1 text-xs rounded-full capitalize {{
                        match($post->status) {
                            'published' => 'bg-green-100 text-green-700',
                            'draft' => 'bg-gray-100 text-gray-600',
                            'submitted' => 'bg-yellow-100 text-yellow-700',
                            'review' => 'bg-blue-100 text-blue-700',
                            'approved' => 'bg-indigo-100 text-indigo-700',
                            'rejected' => 'bg-red-100 text-red-700',
                            default => 'bg-gray-100 text-gray-600',
                        }
                    }}">{{ $post->status }}</span>
                </div>
                @empty
                <div class="p-8 text-center text-gray-500 text-sm">Belum ada berita.</div>
                @endforelse
            </div>
        </div>

        <!-- Recent Messages -->
        <div class="bg-white rounded-xl shadow-sm">
            <div class="p-5 border-b border-gray-100 flex items-center justify-between">
                <h2 class="font-semibold text-gray-900">Pesan Kontak Terbaru</h2>
                <a href="{{ route('admin.contacts.index') }}" class="text-sm text-emerald-600 hover:text-emerald-700">Lihat Semua</a>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($recentMessages as $msg)
                <div class="p-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-900">{{ $msg->subject }}</span>
                        <span class="px-2 py-1 text-xs rounded-full {{ $msg->status === 'new' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600' }}">{{ $msg->status }}</span>
                    </div>
                    <p class="text-xs text-gray-400 mt-1">{{ $msg->name }} — {{ $msg->created_at->format('d M Y') }}</p>
                </div>
                @empty
                <div class="p-8 text-center text-gray-500 text-sm">Belum ada pesan.</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="mt-6 bg-white rounded-xl shadow-sm">
        <div class="p-5 border-b border-gray-100">
            <h2 class="font-semibold text-gray-900">Aktivitas Terbaru</h2>
        </div>
        <div class="divide-y divide-gray-100">
            @forelse($recentLogs as $log)
            <div class="p-4 flex items-start gap-3">
                <div class="p-2 bg-gray-100 rounded-full text-xs">
                    <span class="font-medium">{{ strtoupper(substr($log->action, 0, 3)) }}</span>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm text-gray-900">{{ $log->description }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">
                        {{ $log->user?->name ?? 'System' }} — {{ $log->created_at->format('d M Y H:i') }}
                    </p>
                </div>
                <span class="text-xs text-gray-400 flex-shrink-0">{{ $log->module }}</span>
            </div>
            @empty
            <div class="p-8 text-center text-gray-500 text-sm">Belum ada aktivitas.</div>
            @endforelse
        </div>
    </div>
@endsection
