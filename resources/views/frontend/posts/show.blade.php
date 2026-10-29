@extends('layouts.app')

@section('title', $post->meta_title ?: $post->title)
@section('meta_description', $post->meta_description ?: $post->excerpt_text)
@section('content')
    @if(!empty($preview))
    <div class="sticky top-16 z-40 bg-amber-500 text-white text-sm font-medium text-center py-2 shadow">
        <span class="mx-auto block max-w-4xl px-4">👁 Mode Preview — ini tampilan publik berita (status: <span class="uppercase">{{ $post->status }}</span>)</span>
    </div>
    @endif
    <article class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Breadcrumb -->
        <nav class="flex items-center gap-2 text-sm text-gray-500 mb-6">
            <a href="/" class="hover:text-emerald-600">Home</a>
            <span>/</span>
            <a href="/berita" class="hover:text-emerald-600">Berita</a>
            <span>/</span>
            <span class="text-gray-900">{{ $post->title }}</span>
        </nav>

        <!-- Header -->
        <header class="mb-8">
            <span class="text-sm font-medium text-emerald-600 bg-emerald-50 px-3 py-1 rounded-full">
                {{ $post->category?->name ?? 'Umum' }}
            </span>
            <h1 class="mt-4 text-3xl md:text-4xl font-bold text-gray-900 leading-tight">{{ $post->title }}</h1>

            <div class="mt-4 flex flex-wrap items-center gap-4 text-sm text-gray-500">
                <span class="flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    {{ $post->author?->name }}
                </span>
                <span class="flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    {{ $post->published_at?->format('d F Y') }}
                </span>
                <span class="flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    {{ $post->views }} views
                </span>
            </div>
        </header>

        <!-- Featured Image -->
        @if($post->featuredMedia)
        <div class="mb-8 rounded-xl overflow-hidden">
            <img src="{{ $post->featuredMedia->url }}" alt="{{ $post->title }}"
                 class="w-full max-h-[500px] object-cover" loading="eager">
            @if($post->featuredMedia->caption)
                <p class="text-sm text-gray-500 text-center mt-2">{{ $post->featuredMedia->caption }}</p>
            @endif
        </div>
        @endif

        <!-- Content -->
        <div class="prose prose-lg max-w-none text-gray-800">
            {!! $post->content !!}
        </div>

        <!-- Tags -->
        @if($post->tags->isNotEmpty())
        <div class="mt-8 pt-8 border-t border-gray-200">
            <div class="flex flex-wrap gap-2">
                @foreach($post->tags as $tag)
                <a href="/berita?tag={{ $tag->slug }}" class="px-3 py-1 text-sm bg-gray-100 text-gray-600 rounded-full hover:bg-emerald-50 hover:text-emerald-700">
                    #{{ $tag->name }}
                </a>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Rating -->
        @if(empty($preview))
        <div class="mt-8 p-6 bg-white rounded-xl shadow-sm" id="rating-section" data-post-slug="{{ $post->slug }}">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Berikan Rating</h3>
                    <p class="text-sm text-gray-500">Bagaimana pendapat Anda tentang berita ini?</p>
                </div>
                <div class="text-right">
                    <span id="rating-average" class="text-3xl font-bold text-gray-900">{{ number_format($post->rating_average, 1) }}</span>
                    <span class="text-gray-500">/5</span>
                    <p class="text-sm text-gray-400">Berdasarkan <span id="rating-count">{{ $post->rating_count }}</span> penilaian</p>
                </div>
            </div>
            <div class="mt-4 flex items-center gap-1" id="rating-stars">
                @for($i = 1; $i <= 5; $i++)
                <button type="button" data-rating="{{ $i }}"
                        class="rating-star text-2xl text-gray-300 hover:text-yellow-400 transition-colors cursor-pointer"
                        title="{{ ['1' => 'Sangat Tidak Menarik', '2' => 'Tidak Menarik', '3' => 'Cukup', '4' => 'Menarik', '5' => 'Sangat Menarik'][$i] }}">
                    ★
                </button>
                @endfor
            </div>
            <p id="rating-message" class="mt-2 text-sm hidden"></p>
        </div>
        @endif

        <!-- Share -->
        <div class="mt-8 flex items-center gap-4 flex-wrap">
            <span class="text-sm font-medium text-gray-700">Bagikan:</span>
            <a href="https://wa.me/?text={{ urlencode($post->title . ' ' . url("/berita/{$post->slug}")) }}" target="_blank"
               class="p-2 bg-green-100 text-green-700 rounded-lg hover:bg-green-200" title="Share to WhatsApp">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
            </a>
            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url("/berita/{$post->slug}")) }}" target="_blank"
               class="p-2 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200" title="Share to Facebook">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
            </a>
            <a href="https://twitter.com/intent/tweet?text={{ urlencode($post->title) }}&url={{ urlencode(url("/berita/{$post->slug}")) }}" target="_blank"
               class="p-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200" title="Share to X">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
            </a>
            <button type="button" onclick="navigator.clipboard.writeText(window.location.href).then(() => { this.innerHTML = '✓ Copied'; setTimeout(() => this.innerHTML = '📋 Copy Link', 2000); })"
                    class="p-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm">
                📋 Copy Link
            </button>
        </div>

        <!-- Previous / Next -->
        <div class="mt-8 flex justify-between gap-4">
            @if($previousPost)
            <a href="/berita/{{ $previousPost->slug }}" class="flex-1 p-4 bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow">
                <span class="text-xs text-gray-400">← Sebelumnya</span>
                <p class="text-sm font-medium text-gray-900 line-clamp-1 mt-1">{{ $previousPost->title }}</p>
            </a>
            @else
            <div class="flex-1"></div>
            @endif

            @if($nextPost)
            <a href="/berita/{{ $nextPost->slug }}" class="flex-1 p-4 bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow text-right">
                <span class="text-xs text-gray-400">Berikutnya →</span>
                <p class="text-sm font-medium text-gray-900 line-clamp-1 mt-1">{{ $nextPost->title }}</p>
            </a>
            @else
            <div class="flex-1"></div>
            @endif
        </div>

        <!-- Related Posts -->
        @if($relatedPosts->isNotEmpty())
        <section class="mt-12">
            <h2 class="text-xl font-bold text-gray-900 mb-6">Berita Terkait</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($relatedPosts as $related)
                <a href="/berita/{{ $related->slug }}" class="group flex gap-3 p-3 bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow">
                    <div class="w-20 h-16 flex-shrink-0 rounded-lg overflow-hidden bg-gray-200">
                        @if($related->featuredMedia)
                            <img src="{{ $related->featuredMedia->url }}" alt="{{ $related->title }}" class="w-full h-full object-cover" loading="lazy">
                        @endif
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-sm font-medium text-gray-900 group-hover:text-emerald-700 line-clamp-2">{{ $related->title }}</h3>
                        <p class="text-xs text-gray-400 mt-1">{{ $related->published_at?->format('d M Y') }}</p>
                    </div>
                </a>
                @endforeach
            </div>
        </section>
        @endif
    </article>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const section = document.getElementById('rating-section');
    if (!section) return;
    const postSlug = section.dataset.postSlug;
    const stars = section.querySelectorAll('.rating-star');
    let voted = false;

    stars.forEach(star => {
        star.addEventListener('click', async function() {
            if (voted) return;
            const rating = parseInt(this.dataset.rating);

            // Update visual
            stars.forEach(s => {
                const val = parseInt(s.dataset.rating);
                s.classList.toggle('text-yellow-400', val <= rating);
                s.classList.toggle('text-gray-300', val > rating);
            });

            const msg = document.getElementById('rating-message');
            try {
                const res = await fetch(`/api/frontend/posts/${postSlug}/rating`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ rating })
                });
                const data = await res.json();
                if (data.success) {
                    voted = true;
                    msg.textContent = 'Terima kasih atas rating Anda!';
                    msg.classList.remove('hidden');
                    msg.classList.add('text-green-600');
                } else {
                    msg.textContent = data.message;
                    msg.classList.remove('hidden');
                    msg.classList.add('text-red-600');
                }
            } catch(e) {
                msg.textContent = 'Gagal mengirim rating.';
                msg.classList.remove('hidden');
                msg.classList.add('text-red-600');
            }
        });
    });
});
</script>
@endpush
