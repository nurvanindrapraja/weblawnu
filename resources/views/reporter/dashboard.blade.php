@extends('layouts.admin')
@section('title', 'Dashboard Reporter')
@section('page-title', 'Dashboard Reporter')
@section('content')
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm p-5">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['sent'] ?? 0 }}</p>
                    <p class="text-sm text-gray-500">Berita Terkirim</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['under_review'] ?? 0 }}</p>
                    <p class="text-sm text-gray-500">Sedang Direview</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['published'] ?? 0 }}</p>
                    <p class="text-sm text-gray-500">Published</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['rejected'] ?? 0 }}</p>
                    <p class="text-sm text-gray-500">Rejected</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-900">Berita Terbaru Saya</h3>
                <a href="{{ route('reporter.posts.create') }}" class="text-sm text-emerald-600 hover:text-emerald-700">+ Tulis Berita</a>
            </div>
            <div class="space-y-3">
                @forelse($recentPosts as $post)
                <a href="{{ route('reporter.posts.show', $post) }}" class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-50 transition-colors">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ $post->title }}</p>
                        <p class="text-xs text-gray-500">{{ $post->created_at->format('d M Y') }}</p>
                    </div>
                    <span class="px-2 py-1 text-xs rounded-full capitalize shrink-0 {{
                        match($post->status) {
                            'published' => 'bg-green-100 text-green-700',
                            'draft' => 'bg-gray-100 text-gray-600',
                            'submitted' => 'bg-yellow-100 text-yellow-700',
                            'review' => 'bg-blue-100 text-blue-700',
                            'approved' => 'bg-indigo-100 text-indigo-700',
                            'rejected' => 'bg-red-100 text-red-700',
                            default => 'bg-gray-100 text-gray-600'
                        }
                    }}">{{ $post->status }}</span>
                </a>
                @empty
                <p class="text-sm text-gray-500">Belum ada berita. <a href="{{ route('reporter.posts.create') }}" class="text-emerald-600">Tulis berita pertama</a>.</p>
                @endforelse
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-gray-900 mb-4">Aktivitas Terbaru</h3>
            <div class="space-y-3">
                @forelse($recentLogs as $log)
                <div class="p-3 rounded-lg bg-gray-50">
                    <p class="text-sm text-gray-700">{{ $log->description }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ $log->created_at->format('d M Y H:i') }}</p>
                </div>
                @empty
                <p class="text-sm text-gray-500">Belum ada aktivitas.</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection
