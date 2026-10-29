@extends('layouts.admin')
@section('title', 'Dashboard Redaktur')
@section('page-title', 'Dashboard Redaktur')
@section('content')
    <!-- Notifikasi: galeri yang perlu direview -->
    @if(($stats['galleries_submitted'] ?? 0) > 0)
    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <div class="p-2.5 bg-red-100 rounded-lg">
                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            </div>
            <div>
                <p class="text-sm font-semibold text-red-800">{{ $stats['galleries_submitted'] }} galeri kegiatan perlu direview</p>
                <p class="text-xs text-red-600 mt-0.5">Galeri dari reporter menunggu review Anda sebelum dipublikasikan.</p>
            </div>
        </div>
        <a href="{{ route('redaktur.galleries.index', ['status' => 'submitted']) }}" class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700">Review Sekarang</a>
    </div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm p-5">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['submitted'] ?? 0 }}</p>
                    <p class="text-sm text-gray-500">Menunggu Review</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['review'] ?? 0 }}</p>
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

    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-900">Antrean Review</h3>
            <a href="{{ route('redaktur.posts.index') }}" class="text-sm text-emerald-600 hover:text-emerald-700">Lihat Semua →</a>
        </div>
        <div class="space-y-3">
            @forelse($pendingPosts as $post)
            <div class="flex items-center justify-between p-4 rounded-lg border border-gray-100 hover:border-emerald-200 transition-colors">
                <div class="flex-1 min-w-0">
                    <a href="{{ route('redaktur.posts.review-detail', $post) }}" class="text-sm font-medium text-gray-900 hover:text-emerald-600 truncate block">{{ $post->title }}</a>
                    <p class="text-xs text-gray-500 mt-1">
                        Oleh {{ $post->author?->name }} · {{ $post->submitted_at?->format('d M Y H:i') ?? $post->created_at->format('d M Y') }}
                    </p>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <span class="px-2 py-1 text-xs rounded-full capitalize {{
                        match($post->status) {
                            'submitted' => 'bg-yellow-100 text-yellow-700',
                            'review' => 'bg-blue-100 text-blue-700',
                            default => 'bg-gray-100 text-gray-600'
                        }
                    }}">{{ $post->status }}</span>
                    @if($post->status === 'submitted')
                    <button onclick="startReview('{{ $post->slug }}')" class="px-3 py-1.5 text-xs bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200">Review</button>
                    @endif
                </div>
            </div>
            @empty
            <p class="text-sm text-gray-500">Tidak ada berita yang menunggu review.</p>
            @endforelse
        </div>
    </div>
@endsection
@push('scripts')
<script>
function startReview(slug) {
    fetch(`{{ url('/redaktur/posts') }}/${slug}/start-review`, {
        method: 'PATCH',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' }
    }).then(r=>r.json()).then(d=>{
        showToast(d.message, d.success?'success':'error');
        if(d.success) setTimeout(()=>location.reload(), 1000);
    }).catch(()=>showToast('Error','error'));
}
</script>
@endpush
