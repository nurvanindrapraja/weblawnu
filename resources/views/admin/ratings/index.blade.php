@extends('layouts.admin')
@section('title', 'Rating')
@section('page-title', 'Statistik Rating')
@section('content')
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm p-5">
            <p class="text-sm text-gray-500">Total Rating</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">{{ $totalRatings }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5">
            <p class="text-sm text-gray-500">Rata-rata</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">{{ number_format($avgRating, 2) }} ★</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5">
            <p class="text-sm text-gray-500">Berita Teratas</p>
            <p class="text-sm font-medium text-gray-900 mt-2 line-clamp-2">{{ $topPost?->title ?? '-' }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 border-b"><tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Berita</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rata-rata</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jumlah</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($posts as $post)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <a href="{{ route('admin.posts.show', $post) }}" class="text-sm font-medium text-gray-900 hover:text-emerald-600">{{ $post->title }}</a>
                    </td>
                    <td class="px-4 py-3">
                        <span class="text-sm font-medium text-yellow-500">{{ number_format($avgRatings[$post->id] ?? 0, 1) }}</span>
                        <span class="text-sm text-gray-500">★</span>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $post->ratings_count }}</td>
                </tr>
                @empty
                <tr><td colspan="3" class="px-4 py-8 text-center text-gray-500">Belum ada rating.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4">{{ $posts->links() }}</div>
    </div>
@endsection
