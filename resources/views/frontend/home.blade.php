@extends('layouts.app')

@section('title', 'Beranda')
@section('content')
    <!-- Hero Section -->
    <section class="bg-gradient-to-br from-emerald-700 to-emerald-900 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-24">
            <div class="max-w-3xl">
                <h1 class="text-3xl md:text-5xl font-bold mb-4">
                    Lembaga Aset dan Wakaf
                </h1>
                <p class="text-lg md:text-xl text-emerald-100 mb-8">
                    Portal informasi resmi LAW Pengurus Wilayah Nahdlatul Ulama Jawa Timur.
                    Pusat pengelolaan, pendataan, dan pemberdayaan aset wakaf.
                </p>
                <div class="flex flex-wrap gap-4">
                    <a href="/berita" class="bg-white text-emerald-800 px-6 py-3 rounded-lg font-medium hover:bg-emerald-50 transition-colors">
                        Lihat Berita
                    </a>
                    <a href="/halaman/tentang-lawnu" class="border-2 border-white text-white px-6 py-3 rounded-lg font-medium hover:bg-white hover:text-emerald-800 transition-colors">
                        Tentang LAWNU
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Latest News Slideshow -->
    @if($slideshowPosts->isNotEmpty())
    @php
        $slides = $slideshowPosts->map(fn ($post) => [
            'id' => $post->id,
            'slug' => $post->slug,
            'title' => $post->title,
            'category' => $post->category?->name ?? 'Umum',
            'date' => $post->published_at?->format('d F Y'),
            'image' => $post->featuredMedia?->url,
        ])->values()->all();
    @endphp
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-8 mb-12"
             x-data="newsSlideshow(@js($slides))"
             @mouseenter="stopAutoplay()"
             @mouseleave="startAutoplay()">
        <div class="relative rounded-xl shadow-lg overflow-hidden bg-white">
            <!-- Slides -->
            <template x-for="(slide, index) in slides" :key="slide.id">
                <a :href="'/berita/' + slide.slug" x-show="index === current" class="group block">
                    <div class="relative h-64 sm:h-80 md:h-96 overflow-hidden bg-gray-200">
                        <template x-if="slide.image">
                            <img :src="slide.image" :alt="slide.title"
                                 class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
                        </template>
                        <div x-show="!slide.image" class="absolute inset-0 flex items-center justify-center">
                            <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <div class="absolute inset-0 bg-gradient-to-t from-gray-900/90 via-gray-900/30 to-transparent"></div>
                        <div class="absolute bottom-0 left-0 right-0 p-6 md:p-8">
                            <span class="text-xs font-medium text-white bg-emerald-600 px-2 py-1 rounded-full">
                                <span x-text="slide.category"></span>
                            </span>
                            <h2 class="mt-3 text-xl md:text-3xl font-bold text-white line-clamp-2" x-text="slide.title"></h2>
                            <p class="mt-2 text-sm text-gray-200" x-text="slide.date"></p>
                        </div>
                    </div>
                </a>
            </template>

            <!-- Prev -->
            <button type="button" @click="prev()" x-show="slides.length > 1" aria-label="Berita sebelumnya"
                    class="absolute left-4 top-1/2 -translate-y-1/2 z-10 p-2.5 rounded-full bg-white/80 hover:bg-white text-gray-800 shadow transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </button>

            <!-- Next -->
            <button type="button" @click="next()" x-show="slides.length > 1" aria-label="Berita berikutnya"
                    class="absolute right-4 top-1/2 -translate-y-1/2 z-10 p-2.5 rounded-full bg-white/80 hover:bg-white text-gray-800 shadow transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>

            <!-- Dots -->
            <div x-show="slides.length > 1" class="absolute bottom-4 right-6 md:right-8 z-10 flex gap-2">
                <template x-for="(slide, index) in slides" :key="slide.id">
                    <button type="button" @click="current = index" :aria-label="'Slide ' + (index + 1)"
                            class="h-2.5 w-2.5 rounded-full transition-colors"
                            :class="index === current ? 'bg-white' : 'bg-white/50 hover:bg-white/75'"></button>
                </template>
            </div>
        </div>
    </section>
    @endif

    <script>
        window.newsSlideshow = (slides) => ({
            slides,
            current: 0,
            timer: null,
            init() {
                this.startAutoplay();
            },
            next() {
                if (this.slides.length > 1) {
                    this.current = (this.current + 1) % this.slides.length;
                }
            },
            prev() {
                if (this.slides.length > 1) {
                    this.current = (this.current - 1 + this.slides.length) % this.slides.length;
                }
            },
            startAutoplay() {
                if (this.slides.length < 2) {
                    return;
                }
                this.stopAutoplay();
                this.timer = setInterval(() => this.next(), 5000);
            },
            stopAutoplay() {
                if (this.timer) {
                    clearInterval(this.timer);
                    this.timer = null;
                }
            },
        });
    </script>

    <!-- Latest News + Sidebar -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-12">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Latest News -->
            <div class="lg:col-span-2">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-gray-900">Berita Terbaru</h2>
                    <a href="/berita" class="text-sm text-emerald-600 hover:text-emerald-700 font-medium">Lihat Semua →</a>
                </div>

                <div class="space-y-6">
                    @forelse($latestPosts as $post)
                    <a href="/berita/{{ $post->slug }}" class="group flex gap-4 bg-white rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow">
                        <div class="w-32 h-24 sm:w-48 sm:h-32 flex-shrink-0 rounded-lg overflow-hidden bg-gray-200">
                            @if($post->featuredMedia)
                                <img src="{{ $post->featuredMedia->url }}" alt="{{ $post->title }}"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" loading="lazy">
                            @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01"/></svg>
                                </div>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <span class="text-xs font-medium text-emerald-600">{{ $post->category?->name ?? 'Umum' }}</span>
                            <h3 class="mt-1 text-lg font-semibold text-gray-900 group-hover:text-emerald-700 line-clamp-2">
                                {{ $post->title }}
                            </h3>
                            <p class="mt-1 text-sm text-gray-500 line-clamp-2 hidden sm:block">{{ $post->excerpt_text }}</p>
                            <p class="mt-2 text-xs text-gray-400">{{ $post->published_at?->format('d F Y') }}</p>
                        </div>
                    </a>
                    @empty
                    <div class="bg-white rounded-xl p-8 text-center text-gray-500">
                        Belum ada berita yang dipublikasikan.
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-8">
                <!-- About LWP -->
                @if($aboutPage)
                <div class="bg-white rounded-xl p-6 shadow-sm">
                    <h3 class="text-lg font-bold text-gray-900 mb-3">Tentang LAWNU</h3>
                    <div class="text-sm text-gray-600 line-clamp-6">
                        {!! \Illuminate\Support\Str::limit(strip_tags($aboutPage->content), 300) !!}
                    </div>
                    <a href="/halaman/tentang-lawnu" class="mt-3 inline-block text-sm text-emerald-600 hover:text-emerald-700 font-medium">
                        Selengkapnya →
                    </a>
                </div>
                @endif

                <!-- Popular News -->
                @if($popularPosts->isNotEmpty())
                <div class="bg-white rounded-xl p-6 shadow-sm">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Berita Populer</h3>
                    <div class="space-y-4">
                        @foreach($popularPosts as $post)
                        <a href="/berita/{{ $post->slug }}" class="group flex gap-3">
                            <div class="w-16 h-16 flex-shrink-0 rounded-lg overflow-hidden bg-gray-200">
                                @if($post->featuredMedia)
                                    <img src="{{ $post->featuredMedia->url }}" alt="{{ $post->title }}" class="w-full h-full object-cover" loading="lazy">
                                @endif
                            </div>
                            <div class="min-w-0">
                                <h4 class="text-sm font-medium text-gray-900 group-hover:text-emerald-700 line-clamp-2">{{ $post->title }}</h4>
                                <p class="text-xs text-gray-400 mt-1">{{ $post->published_at?->format('d M Y') }}</p>
                            </div>
                        </a>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Categories -->
                <div class="bg-white rounded-xl p-6 shadow-sm">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Kategori</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach(\App\Models\Category::active()->get() as $cat)
                        <a href="/kategori/{{ $cat->slug }}"
                           class="px-3 py-1.5 text-sm bg-gray-100 text-gray-700 rounded-full hover:bg-emerald-50 hover:text-emerald-700 transition-colors">
                            {{ $cat->name }}
                        </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="bg-emerald-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 text-center">
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Punya pertanyaan atau saran?</h2>
            <p class="text-gray-600 mb-6">Kami siap membantu Anda dengan informasi seputar wakaf dan pertanahan.</p>
            <a href="/kontak" class="inline-block bg-emerald-600 text-white px-8 py-3 rounded-lg font-medium hover:bg-emerald-700 transition-colors">
                Hubungi Kami
            </a>
        </div>
    </section>
@endsection
