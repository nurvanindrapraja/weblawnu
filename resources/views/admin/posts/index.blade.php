@extends('layouts.admin')

@section('title', 'Berita')
@section('page-title', 'Manajemen Berita')
@section('content')
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div class="flex flex-wrap items-center gap-2" role="search">
            <div class="relative">
                <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" id="post-search" placeholder="Cari judul..." value="{{ request('search') }}"
                       class="pl-9 pr-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500" autocomplete="off">
            </div>
            <div class="relative">
                <select id="post-status-filter"
                        class="appearance-none pl-3 pr-9 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 cursor-pointer bg-white">
                    <option value="">Semua Status</option>
                    @foreach(['draft', 'submitted', 'review', 'approved', 'published', 'rejected', 'archived'] as $s)
                    <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
                <svg class="w-4 h-4 text-gray-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </div>
            <div class="relative">
                <select id="post-category-filter"
                        class="appearance-none pl-3 pr-9 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 cursor-pointer bg-white">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
                <svg class="w-4 h-4 text-gray-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </div>
        </div>
        <a href="{{ route('admin.posts.create') }}" class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm hover:bg-emerald-700">
            + Buat Berita
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th data-sort="title" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer select-none hover:text-gray-800">Judul<span class="sort-arrow"></span></th>
                        <th data-sort="category" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer select-none hover:text-gray-800">Kategori<span class="sort-arrow"></span></th>
                        <th data-sort="author" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer select-none hover:text-gray-800">Penulis<span class="sort-arrow"></span></th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th data-sort="published" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer select-none hover:text-gray-800">Published<span class="sort-arrow"></span></th>
                        <th data-sort="views" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer select-none hover:text-gray-800">Views<span class="sort-arrow"></span></th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody id="posts-rows" class="divide-y divide-gray-100">
                    @include('admin.posts.partials.rows')
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
// Live AJAX filtering: search text (debounced) + dropdowns apply instantly,
// no Filter button needed. A top loading bar shows while the request runs.
(function() {
    const searchInput = document.getElementById('post-search');
    const statusSelect = document.getElementById('post-status-filter');
    const categorySelect = document.getElementById('post-category-filter');
    const rowsEl = document.getElementById('posts-rows');
    const paginationEl = document.getElementById('posts-pagination');
    const filterUrl = '{{ route("admin.posts.filter") }}';

    let debounceTimer = null;
    let currentPage = {{ $posts->currentPage() }};
    let seq = 0;

    // ===== Sorting kolom (klik header) =====
    const defaultDirs = { title: 'asc', category: 'asc', author: 'asc', published: 'desc', views: 'desc' };
    let sortKey = @json(request('sort', 'published'));
    let sortDir = @json(request('dir', 'desc'));
    const arrowUp = '<svg class="w-3 h-3 inline-block ml-1 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"/></svg>';
    const arrowDown = '<svg class="w-3 h-3 inline-block ml-1 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>';

    function updateSortArrows() {
        document.querySelectorAll('th[data-sort]').forEach((th) => {
            const arrow = th.querySelector('.sort-arrow');
            if (!arrow) return;
            arrow.innerHTML = (th.dataset.sort === sortKey)
                ? (sortDir === 'asc' ? arrowUp : arrowDown)
                : '';
            const active = th.dataset.sort === sortKey;
            th.classList.toggle('text-emerald-700', active);
            th.classList.toggle('text-gray-500', !active);
        });
    }

    function sortPosts(key) {
        if (sortKey === key) {
            sortDir = sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            sortKey = key;
            sortDir = defaultDirs[key] || 'asc';
        }
        updateSortArrows();
        loadPosts(true);
    }

    document.querySelectorAll('th[data-sort]').forEach((th) => {
        th.addEventListener('click', () => sortPosts(th.dataset.sort));
    });
    updateSortArrows();

    function buildParams() {
        const p = new URLSearchParams();
        const s = searchInput.value.trim();
        if (s) p.set('search', s);
        if (statusSelect.value) p.set('status', statusSelect.value);
        if (categorySelect.value) p.set('category', categorySelect.value);
        p.set('sort', sortKey);
        p.set('dir', sortDir);
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
    categorySelect.addEventListener('change', function() { loadPosts(true); });

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

async function postAction(slug, action) {
    let confirmMsg = '';
    if (action === 'reject') {
        confirmMsg = await confirmModal('Tolak berita ini? Anda dapat memberikan catatan untuk reporter.', {
            title: 'Tolak Berita',
            okLabel: 'Tolak',
            okClass: 'bg-red-600 hover:bg-red-700',
            input: true,
            inputPlaceholder: 'Catatan penolakan (opsional)...'
        });
        if (confirmMsg === false) return;
    } else if (action === 'delete') {
        const ok = await confirmModal('Yakin hapus berita ini? Tindakan ini tidak dapat dibatalkan.', {
            title: 'Hapus Berita',
            okLabel: 'Hapus',
            okClass: 'bg-red-600 hover:bg-red-700'
        });
        if (!ok) return;
    } else {
        const ok = await confirmModal(`Yakin ingin ${action} berita ini?`, {
            title: 'Konfirmasi',
            okLabel: 'Ya, lanjutkan'
        });
        if (!ok) return;
    }

    const body = action === 'reject' ? JSON.stringify({ note: confirmMsg }) : null;

    fetch(`{{ url('/admin/posts') }}/${slug}/${action}`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body
    }).then(r => r.json()).then(data => {
        showToast(data.message, data.success ? 'success' : 'error');
        if (data.success) setTimeout(() => location.reload(), 1000);
    }).catch(() => showToast('Terjadi kesalahan.', 'error'));
}

async function deletePost(slug, title) {
    const ok = await confirmModal(`Hapus berita "${title}"? Tindakan ini tidak dapat dibatalkan.`, {
        title: 'Hapus Berita',
        okLabel: 'Hapus',
        okClass: 'bg-red-600 hover:bg-red-700'
    });
    if (!ok) return;
    fetch(`{{ url('/admin/posts') }}/${slug}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' }
    }).then(r => r.json()).then(data => {
        showToast(data.message, data.success ? 'success' : 'error');
        if (data.success) setTimeout(() => location.reload(), 1000);
    }).catch(() => showToast('Gagal menghapus.', 'error'));
}
</script>
@endpush
