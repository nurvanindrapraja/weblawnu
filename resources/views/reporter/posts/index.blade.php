@extends('layouts.admin')
@section('title', 'Berita Saya')
@section('page-title', 'Manajemen Berita')
@section('content')
    <div class="flex items-center justify-between mb-6">
        <div class="flex flex-wrap items-center gap-2" role="search">
            <div class="relative">
                <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" id="post-search" placeholder="Cari judul..." value="{{ request('search') }}"
                       class="pl-9 pr-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500" autocomplete="off">
            </div>
            <div class="relative">
                <select id="post-status-filter" class="appearance-none pl-3 pr-9 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 cursor-pointer bg-white">
                    <option value="">Semua Status</option>
                    @foreach(['draft','submitted','review','approved','published','rejected'] as $s)
                    <option value="{{ $s }}" {{ request('status')===$s?'selected':'' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
                <svg class="w-4 h-4 text-gray-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </div>
        </div>
        <a href="{{ route('reporter.posts.create') }}" class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm hover:bg-emerald-700">+ Tulis Berita</a>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b"><tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Judul</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kategori</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Views</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                </tr></thead>
                <tbody id="posts-rows" class="divide-y divide-gray-100">
                    @include('reporter.posts.partials.rows')
                </tbody>
            </table>
        </div>
        <div id="posts-pagination" class="p-4">{!! $posts->links() !!}</div>
    </div>

    {{-- Preview modal (iframe, stays on this page) --}}
    <x-post-preview-modal />
@endsection
@push('scripts')
<script>
// Live AJAX filtering: search text (debounced) + status dropdown apply
// instantly, no Filter button needed. Top loading bar shown while loading.
(function() {
    const searchInput = document.getElementById('post-search');
    const statusSelect = document.getElementById('post-status-filter');
    const rowsEl = document.getElementById('posts-rows');
    const paginationEl = document.getElementById('posts-pagination');
    const filterUrl = '{{ route("reporter.posts.filter") }}';

    let debounceTimer = null;
    let currentPage = {{ $posts->currentPage() }};
    let seq = 0;

    function buildParams() {
        const p = new URLSearchParams();
        const s = searchInput.value.trim();
        if (s) p.set('search', s);
        if (statusSelect.value) p.set('status', statusSelect.value);
        if (currentPage > 1) p.set('page', currentPage);
        return p;
    }

    function syncUrl(p) {
        const qs = p.toString();
        history.replaceState(null, '', qs ? '?' + qs : location.pathname);
    }

    async function loadPosts(resetPage) {
        if (resetPage) currentPage = 1;
        const mySeq = ++seq;
        const p = buildParams();
        if (window.startLoading) window.startLoading();
        try {
            const res = await fetch(filterUrl + '?' + p.toString(), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            });
            if (!res.ok) throw new Error('HTTP ' + res.status);
            const data = await res.json();
            if (mySeq !== seq) return; // superseded by a newer request
            rowsEl.innerHTML = data.rows;
            paginationEl.innerHTML = data.pagination || '';
            currentPage = data.page || 1;
            syncUrl(p);
        } catch (err) {
            if (mySeq !== seq) return;
            console.error('Filter berita gagal:', err);
            showToast('Gagal memuat daftar berita.', 'error');
        } finally {
            if (mySeq === seq && window.stopLoading) window.stopLoading();
        }
    }

    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function() { loadPosts(true); }, 350);
    });
    statusSelect.addEventListener('change', function() { loadPosts(true); });

    // Pagination links are intercepted and loaded via AJAX as well.
    paginationEl.addEventListener('click', function(e) {
        const link = e.target.closest('a');
        if (!link || !paginationEl.contains(link)) return;
        e.preventDefault();
        const page = parseInt(new URL(link.href, location.origin).searchParams.get('page') || '1', 10);
        currentPage = isNaN(page) ? 1 : page;
        loadPosts(false);
    });
})();

async function submitPost(slug) {
    const ok = await confirmModal('Submit berita untuk review? Redaktur akan meninjau berita Anda.', {
        title: 'Submit untuk Review',
        okLabel: 'Submit'
    });
    if (!ok) return;
    fetch(`{{ url('/reporter/posts') }}/${slug}/submit`, {
        method: 'PATCH',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' }
    }).then(r=>r.json()).then(d=>{
        showToast(d.message, d.success?'success':'error');
        if(d.success) setTimeout(()=>location.reload(), 1000);
    }).catch(()=>showToast('Error','error'));
}
async function deletePost(slug, title) {
    const ok = await confirmModal(`Hapus berita "${title}"? Tindakan ini tidak dapat dibatalkan.`, {
        title: 'Hapus Berita',
        okLabel: 'Hapus',
        okClass: 'bg-red-600 hover:bg-red-700'
    });
    if (!ok) return;
    fetch(`{{ url('/reporter/posts') }}/${slug}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' }
    }).then(r=>r.json()).then(d=>{
        showToast(d.message, d.success?'success':'error');
        if(d.success) setTimeout(()=>location.reload(), 1000);
    }).catch(()=>showToast('Error','error'));
}
</script>
@endpush
