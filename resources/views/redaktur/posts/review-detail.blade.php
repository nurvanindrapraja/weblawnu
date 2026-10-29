@extends('layouts.admin')
@section('title', 'Review Berita')
@section('page-title', 'Review Berita')
@section('content')
    <div class="flex items-center justify-between mb-6">
        <a href="{{ route('redaktur.posts.review') }}" class="text-sm text-gray-500 hover:text-emerald-600">← Kembali ke Menunggu Review</a>
        @if($reviewable)
        <div class="flex gap-2">
            <button type="button" onclick="openPostPreviewGet('{{ route('redaktur.posts.preview', $post) }}')"
                    class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200">
                Preview Tampilan Publik
            </button>
            <button onclick="postAction('{{ $post->slug }}', 'reject')"
                    class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700">
                Tolak
            </button>
            <button onclick="postAction('{{ $post->slug }}', 'approve')"
                    class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm hover:bg-emerald-700">
                Setujui
            </button>
        </div>
        @endif
    </div>

    @unless($reviewable)
    <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 flex items-center justify-between gap-4">
        <p class="text-sm text-amber-800">
            Berita ini sudah diproses (status: <span class="font-semibold capitalize">{{ $post->status }}</span>)
            sehingga tidak dapat direview lagi.
        </p>
        <a href="{{ route('redaktur.posts.index', ['status' => $post->status]) }}" class="text-sm font-medium text-amber-800 underline shrink-0">Lihat di daftar</a>
    </div>
    @endunless

    <div class="bg-white rounded-xl shadow-sm p-6">
        <span class="px-3 py-1 text-xs rounded-full capitalize {{
            match($post->status) {
                'submitted' => 'bg-yellow-100 text-yellow-700',
                'review' => 'bg-blue-100 text-blue-700',
                default => 'bg-gray-100 text-gray-600',
            }
        }}">{{ $post->status }}</span>

        <h1 class="text-2xl font-bold text-gray-900 mt-3">{{ $post->title }}</h1>
        <div class="flex flex-wrap items-center gap-4 text-sm text-gray-500 mt-2">
            <span>Penulis: {{ $post->author?->name }}</span>
            <span>Kategori: {{ $post->category?->name ?? '-' }}</span>
            <span>Submitted: {{ $post->submitted_at?->format('d F Y H:i') ?? '-' }}</span>
        </div>

        @if($post->excerpt)
        <div class="mt-4 p-4 bg-gray-50 border border-gray-200 rounded-lg">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Excerpt</h3>
            <p class="text-sm text-gray-700 mt-1">{{ $post->excerpt }}</p>
        </div>
        @endif

        @if($post->featuredMedia)
        <img src="{{ $post->featuredMedia->url }}" alt="" class="mt-4 w-full max-h-64 object-cover rounded-lg">
        @endif

        <div class="prose prose-lg max-w-none mt-6">{!! $post->content !!}</div>

        @if($post->tags->isNotEmpty())
        <div class="mt-6 pt-6 border-t border-gray-200">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Tag</h3>
            <div class="flex flex-wrap gap-2">
                @foreach($post->tags as $tag)
                <span class="px-2 py-1 text-xs bg-gray-100 text-gray-600 rounded-full">#{{ $tag->name }}</span>
                @endforeach
            </div>
        </div>
        @endif

        @if($post->media->isNotEmpty())
        <div class="mt-6 pt-6 border-t border-gray-200">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Media</h3>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                @foreach($post->media as $m)
                <a href="{{ $m->url }}" target="_blank" class="block overflow-hidden rounded-lg border border-gray-200">
                    <img src="{{ $m->url }}" alt="{{ $m->name ?? '' }}" class="w-full h-24 object-cover hover:scale-105 transition-transform">
                </a>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    {{-- Preview modal (iframe, stays on this page) --}}
    <x-post-preview-modal />
@endsection
@push('scripts')
<script>
async function postAction(slug, action) {
    let note = '';
    if (action === 'reject') {
        note = await confirmModal('Tolak berita ini? Anda dapat memberikan catatan untuk reporter.', {
            title: 'Tolak Berita',
            okLabel: 'Tolak',
            okClass: 'bg-red-600 hover:bg-red-700',
            input: true,
            inputPlaceholder: 'Catatan penolakan (opsional)...'
        });
        if (note === false) return;
    } else {
        const ok = await confirmModal(`Yakin ingin ${action.replace('-',' ')} berita ini?`, {
            title: 'Konfirmasi',
            okLabel: 'Ya, lanjutkan'
        });
        if (!ok) return;
    }

    const body = action === 'reject' ? JSON.stringify({ note }) : null;

    fetch(`{{ url('/redaktur/posts') }}/${slug}/${action}`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body
    }).then(r=>r.json()).then(d=>{
        showToast(d.message, d.success?'success':'error');
        if(d.success) {
            // Jangan reload halaman ini — setelah ditolak/disetujui,
            // statusnya bukan lagi reviewable (reload → 404).
            // Arahkan ke daftar dengan filter status yang cocok.
            const redirectUrls = {
                reject: '{{ route("redaktur.posts.index", ["status" => "rejected"]) }}',
                approve: '{{ route("redaktur.posts.index", ["status" => "approved"]) }}',
            };
            setTimeout(() => { location.href = redirectUrls[action] || '{{ route("redaktur.posts.review") }}'; }, 1000);
        }
    }).catch(()=>showToast('Error','error'));
}
</script>
@endpush
