@extends('layouts.admin')
@section('title', 'Detail Berita')
@section('page-title', 'Detail Berita')
@section('content')
    <div class="flex items-center justify-between mb-6">
        <a href="{{ route('admin.posts.index') }}" class="text-sm text-gray-500 hover:text-emerald-600">← Kembali</a>
        <div class="flex gap-2">
            <a href="{{ route('admin.posts.edit', $post) }}" class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm hover:bg-emerald-700">Edit</a>
            @if($post->status === 'published')
            <a href="/berita/{{ $post->slug }}" target="_blank" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200">Lihat di Website</a>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm p-6">
            <span class="px-3 py-1 text-xs rounded-full capitalize {{
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

            <h1 class="text-2xl font-bold text-gray-900 mt-3">{{ $post->title }}</h1>
            <div class="flex items-center gap-4 text-sm text-gray-500 mt-2">
                <span>Penulis: {{ $post->author?->name }}</span>
                <span>Kategori: {{ $post->category?->name }}</span>
                <span>Views: {{ $post->views }}</span>
                <span>Rating: {{ $post->rating_average }} ({{ $post->rating_count }})</span>
            </div>

            @if($post->rejection_note)
            <div class="mt-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                <h3 class="text-sm font-semibold text-red-800">Catatan Penolakan</h3>
                <p class="text-sm text-red-700 mt-1">{{ $post->rejection_note }}</p>
            </div>
            @endif

            @if($post->featuredMedia)
            <img src="{{ $post->featuredMedia->url }}" alt="" class="mt-4 w-full max-h-64 object-cover rounded-lg">
            @endif

            <div class="prose prose-lg max-w-none mt-6">{!! $post->content !!}</div>
        </div>

        <div class="space-y-4">
            <div class="bg-white rounded-xl shadow-sm p-5">
                <h3 class="font-semibold text-gray-900 mb-3">Info</h3>
                <dl class="space-y-2 text-sm">
                    <div><dt class="text-gray-500">Dibuat</dt><dd class="text-gray-900">{{ $post->created_at->format('d M Y H:i') }}</dd></div>
                    <div><dt class="text-gray-500">Diupdate</dt><dd class="text-gray-900">{{ $post->updated_at->format('d M Y H:i') }}</dd></div>
                    @if($post->submitted_at)<div><dt class="text-gray-500">Submitted</dt><dd class="text-gray-900">{{ $post->submitted_at->format('d M Y H:i') }}</dd></div>@endif
                    @if($post->published_at)<div><dt class="text-gray-500">Published</dt><dd class="text-gray-900">{{ $post->published_at->format('d M Y H:i') }}</dd></div>@endif
                    @if($post->reviewed_at)<div><dt class="text-gray-500">Reviewed</dt><dd class="text-gray-900">{{ $post->reviewed_at->format('d M Y H:i') }} oleh {{ $post->reviewer?->name }}</dd></div>@endif
                </dl>
            </div>

            @if($post->tags->isNotEmpty())
            <div class="bg-white rounded-xl shadow-sm p-5">
                <h3 class="font-semibold text-gray-900 mb-3">Tags</h3>
                <div class="flex flex-wrap gap-2">
                    @foreach($post->tags as $tag)
                    <span class="px-2 py-1 text-xs bg-gray-100 text-gray-600 rounded-full">#{{ $tag->name }}</span>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
@endsection
