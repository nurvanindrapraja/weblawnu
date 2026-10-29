@extends('layouts.admin')

@section('title', 'Review Galeri')
@section('page-title', 'Review: '.$gallery->title)
@section('content')
    <a href="{{ route('redaktur.galleries.index', ['status' => 'submitted']) }}" class="text-sm text-gray-500 hover:text-emerald-600 mb-4 inline-block">← Kembali ke daftar review</a>

    <div class="max-w-4xl">
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">{{ $gallery->title }}</h1>
                    <div class="mt-2 flex flex-wrap items-center gap-4 text-sm text-gray-500">
                        <span>oleh <span class="text-gray-700 font-medium">{{ $gallery->author?->name ?? '-' }}</span></span>
                        @if($gallery->event_date)
                        <span>{{ $gallery->event_date->format('d F Y') }}</span>
                        @endif
                        @if($gallery->submitted_at)
                        <span>disubmit {{ $gallery->submitted_at->diffForHumans() }}</span>
                        @endif
                    </div>
                </div>
                @php
                    $badges = [
                        'draft' => ['bg-gray-100 text-gray-600', 'Draft'],
                        'submitted' => ['bg-yellow-100 text-yellow-700', 'Menunggu Review'],
                        'published' => ['bg-green-100 text-green-700', 'Published'],
                        'rejected' => ['bg-red-100 text-red-700', 'Ditolak'],
                    ];
                    [$badgeClass, $badgeLabel] = $badges[$gallery->status] ?? ['bg-gray-100 text-gray-600', $gallery->status];
                @endphp
                <span class="px-3 py-1.5 text-xs font-medium rounded-full {{ $badgeClass }}">{{ $badgeLabel }}</span>
            </div>

            @if($gallery->status === 'rejected' && $gallery->rejection_note)
            <div class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
                Catatan penolakan terakhir: {{ $gallery->rejection_note }}
            </div>
            @endif

            @if($gallery->description)
            <div class="mt-4 text-sm text-gray-700 whitespace-pre-line">{{ $gallery->description }}</div>
            @endif
        </div>

        <!-- Foto kegiatan -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-4">Foto Kegiatan ({{ $gallery->images->count() }})</h2>
            @if($gallery->images->isNotEmpty())
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach($gallery->images as $image)
                <figure class="border border-gray-200 rounded-xl overflow-hidden">
                    <div class="aspect-[4/3] bg-gray-100">
                        <img src="{{ $image->url }}" alt="{{ $image->pivot->caption }}" class="w-full h-full object-cover">
                    </div>
                    @if($image->pivot->caption)
                    <figcaption class="px-4 py-3 text-sm text-gray-600 border-t border-gray-100">{{ $image->pivot->caption }}</figcaption>
                    @else
                    <figcaption class="px-4 py-3 text-xs text-gray-400 border-t border-gray-100 italic">Tanpa keterangan</figcaption>
                    @endif
                </figure>
                @endforeach
            </div>
            @else
            <p class="text-sm text-gray-500">Galeri ini tidak memiliki foto.</p>
            @endif
        </div>

        <!-- Aksi review -->
        @if($gallery->status === 'submitted')
        <div class="flex flex-wrap gap-3">
            <button onclick="approveGallery()" class="px-6 py-3 bg-emerald-600 text-white rounded-lg text-sm font-medium hover:bg-emerald-700 inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Setujui & Publikasikan
            </button>
            <button onclick="rejectGallery()" class="px-6 py-3 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Tolak
            </button>
        </div>
        @else
        <p class="text-sm text-gray-500">Galeri ini sudah diproses dan tidak dapat di-review kembali.</p>
        @endif
    </div>
@endsection

@push('scripts')
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
const slug = @json($gallery->slug);

async function approveGallery() {
    const ok = await confirmModal('Setujui & publikasikan galeri ini?', {
        title: 'Setujui Galeri',
        okLabel: 'Setujui',
        okClass: 'bg-emerald-600 hover:bg-emerald-700'
    });
    if (!ok) return;
    try {
        const res = await fetch(`/redaktur/galleries/${slug}/approve`, {
            method: 'PATCH',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
        });
        const data = await res.json();
        showToast(data.message, data.success ? 'success' : 'error');
        if (data.success) setTimeout(() => location.href = '/redaktur/galleries?status=published', 1200);
    } catch (e) {
        showToast('Terjadi kesalahan.', 'error');
    }
}

async function rejectGallery() {
    let note = '';
    do {
        note = await confirmModal('Tolak galeri ini? Ketik catatan penolakan untuk reporter.', {
            title: 'Tolak Galeri',
            okLabel: 'Tolak',
            okClass: 'bg-red-600 hover:bg-red-700',
            input: true,
            inputPlaceholder: 'Catatan penolakan (wajib)'
        });
        if (note === false) return;
    } while (!note.trim());
    try {
        const res = await fetch(`/redaktur/galleries/${slug}/reject`, {
            method: 'PATCH',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'Content-Type': 'application/json' },
            body: JSON.stringify({ note })
        });
        const data = await res.json();
        showToast(data.message, data.success ? 'success' : 'error');
        if (data.success) setTimeout(() => location.href = '/redaktur/galleries?status=rejected', 1200);
    } catch (e) {
        showToast('Terjadi kesalahan.', 'error');
    }
}
</script>
@endpush
