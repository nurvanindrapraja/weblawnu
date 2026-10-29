@extends('layouts.admin')
@section('title', 'Media')
@section('page-title', 'Manajemen Media')
@section('content')
    @php
        // Shared view, rendered from admin/reporter/redaktur media routes.
        $mediaStoreUrl = request()->routeIs('reporter.media.*')
            ? route('reporter.media.store')
            : (request()->routeIs('redaktur.media.*') ? route('redaktur.media.store') : route('admin.media.store'));
    @endphp
    <div class="flex items-center justify-between mb-6">
        <div class="relative">
            <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" id="media-search" placeholder="Cari media..." value="{{ request('search') }}"
                   class="pl-9 pr-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500" autocomplete="off">
        </div>
        <label class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm hover:bg-emerald-700 cursor-pointer">
            + Upload
            <input type="file" id="media-upload" accept="image/*,video/*,audio/*" class="hidden" multiple>
        </label>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4" id="media-grid">
        @include('admin.media.partials.grid')
    </div>

    <div id="media-pagination" class="mt-4">{!! $media->links() !!}</div>
@endsection
@push('scripts')
<script>
// Live AJAX filtering: typing in the search box updates the grid
// instantly (debounced), no Filter button. Shared across the
// admin/reporter/redaktur media pages.
(function () {
    const searchInput = document.getElementById('media-search');
    const grid = document.getElementById('media-grid');
    const pagination = document.getElementById('media-pagination');
    const filterUrl = '{{ route(request()->routeIs('reporter.media.*') ? 'reporter.media.filter' : (request()->routeIs('redaktur.media.*') ? 'redaktur.media.filter' : 'admin.media.filter')) }}';

    function setLoading(on) {
        const bar = document.getElementById('loading-bar');
        if (!bar) return;
        if (on) { bar.style.display = 'block'; bar.style.width = '30%'; }
        else { bar.style.width = '100%'; setTimeout(() => { bar.style.display = 'none'; bar.style.width = '0%'; }, 300); }
    }

    function syncUrl() {
        const s = searchInput.value.trim();
        const url = new URL(window.location.href);
        if (s) url.searchParams.set('search', s); else url.searchParams.delete('search');
        url.searchParams.delete('page');
        window.history.replaceState({}, '', url);
    }

    async function applyFilter() {
        setLoading(true);
        const params = new URLSearchParams();
        const s = searchInput.value.trim();
        if (s) params.set('search', s);
        try {
            const res = await fetch(`${filterUrl}?${params.toString()}`, { headers: { 'Accept': 'application/json' } });
            const d = await res.json();
            if (d.success) {
                grid.innerHTML = d.rows;
                pagination.innerHTML = d.pagination;
                syncUrl();
            }
        } catch (e) { console.error(e); }
        setLoading(false);
    }

    let timer;
    searchInput.addEventListener('input', function () {
        clearTimeout(timer);
        timer = setTimeout(applyFilter, 300);
    });

    pagination.addEventListener('click', function (e) {
        const a = e.target.closest('a');
        if (!a) return;
        e.preventDefault();
        setLoading(true);
        fetch(a.href, { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(d => {
                if (d.success) {
                    grid.innerHTML = d.rows;
                    pagination.innerHTML = d.pagination;
                    syncUrl();
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            })
            .catch(err => console.error(err))
            .finally(() => setLoading(false));
    });
})();

document.getElementById('media-upload').addEventListener('change', async function () {
    const files = this.files;
    if (!files.length) return;
    showToast('Mengupload...');
    for (const file of files) {
        const fd = new FormData();
        fd.append('file', file);
        try {
            const res = await fetch('{{ $mediaStoreUrl }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                body: fd
            });
            const d = await res.json();
            if (d.success) showToast('Upload berhasil', 'success');
        } catch(e) { showToast('Upload gagal', 'error'); }
    }
    setTimeout(() => location.reload(), 1000);
});

async function deleteMedia(id, name) {
    // Admin menghapus via /admin/media, reporter via /reporter/media
    // (hanya untuk media miliknya yang belum published). Redaktur
    // tidak punya tombol hapus, jadi base URL-nya null.
    const destroyBase = @json(auth()->user()->isAdmin() ? url('/admin/media') : (auth()->user()->isReporter() ? url('/reporter/media') : null));
    if (!destroyBase) {
        showToast('Anda tidak dapat menghapus media ini.', 'error');
        return;
    }

    const ok = await confirmModal(`Hapus file "${name}"? Tindakan ini tidak dapat dibatalkan.`, {
        title: 'Hapus Media',
        okLabel: 'Hapus',
        okClass: 'bg-red-600 hover:bg-red-700'
    });
    if (!ok) return;
    fetch(`${destroyBase}/${id}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' }
    }).then(r=>r.json()).then(d=>{
        showToast(d.message, d.success?'success':'error');
        if(d.success) setTimeout(()=>location.reload(), 1000);
    }).catch(()=>showToast('Gagal menghapus','error'));
}
</script>
@endpush
