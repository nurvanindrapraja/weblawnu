@extends('layouts.app')

@section('title', $page->meta_title ?: $page->title)
@section('meta_description', $page->meta_description ?: \Illuminate\Support\Str::limit(strip_tags($page->excerpt ?? ''), 160))
@section('content')
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <nav class="flex items-center gap-2 text-sm text-gray-500 mb-6">
            <a href="/" class="hover:text-emerald-600">Home</a>
            <span>/</span>
            <span class="text-gray-900">{{ $page->title }}</span>
        </nav>

        <header class="sticky top-16 z-40 -mx-4 sm:-mx-6 lg:-mx-8 bg-gray-50 px-4 sm:px-6 lg:px-8 pt-4 pb-3 mb-5">
            <h1 class="text-3xl md:text-4xl font-bold text-gray-900">{{ $page->title }}</h1>
            @if($page->excerpt)
            <p class="mt-3 text-lg text-gray-600">{{ $page->excerpt }}</p>
            @endif
        </header>

        @if($page->featuredMedia)
        <div class="mb-8 rounded-xl overflow-hidden">
            <img src="{{ $page->featuredMedia->url }}" alt="{{ $page->title }}" class="w-full max-h-[400px] object-cover">
        </div>
        @endif

        <div class="prose prose-lg max-w-none text-gray-800">
            {!! $page->content !!}
        </div>
    </div>
@endsection
