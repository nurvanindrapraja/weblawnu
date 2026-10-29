@extends('layouts.admin')
@section('title', 'Log Aktivitas')
@section('page-title', 'Log Aktivitas')
@section('content')
    <div class="flex flex-wrap items-center gap-2 mb-6" role="search">
        <div class="relative">
            <select id="log-module-filter"
                    class="appearance-none pl-3 pr-9 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 cursor-pointer bg-white">
                <option value="">Semua Modul</option>
                @foreach($modules as $m)
                <option value="{{ $m }}" {{ request('module') === $m ? 'selected' : '' }}>{{ $m }}</option>
                @endforeach
            </select>
            <svg class="w-4 h-4 text-gray-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </div>
        <div class="relative">
            <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" id="log-search" placeholder="Cari aktivitas..." value="{{ request('search') }}"
                   class="pl-9 pr-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500" autocomplete="off">
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 border-b"><tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Waktu</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Modul</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Deskripsi</th>
            </tr></thead>
            <tbody id="logs-rows" class="divide-y divide-gray-100">
                @include('admin.logs.partials.rows')
            </tbody>
        </table>
        <div id="logs-pagination" class="p-4">{!! $logs->links() !!}</div>
    </div>
@endsection

@push('scripts')
<script>
// Live AJAX filtering: search text (debounced) + dropdown apply instantly,
// no Filter button needed. A top loading bar shows while the request runs.
(function() {
    const moduleSelect = document.getElementById('log-module-filter');
    const searchInput = document.getElementById('log-search');
    const rowsEl = document.getElementById('logs-rows');
    const paginationEl = document.getElementById('logs-pagination');
    const filterUrl = '{{ route("admin.logs.filter") }}';

    let debounceTimer = null;
    let currentPage = {{ $logs->currentPage() }};
    let seq = 0;

    function buildParams() {
        const p = new URLSearchParams();
        if (moduleSelect.value) p.set('module', moduleSelect.value);
        const s = searchInput.value.trim();
        if (s) p.set('search', s);
        if (currentPage > 1) p.set('page', currentPage);
        return p;
    }

    function syncUrl(p) {
        const qs = p.toString();
        history.replaceState(null, '', qs ? '?' + qs : location.pathname);
    }

    async function loadLogs(resetPage) {
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
            console.error('Filter log aktivitas gagal:', err);
            showToast('Gagal memuat log aktivitas.', 'error');
        } finally {
            if (mySeq === seq && window.stopLoading) window.stopLoading();
        }
    }

    moduleSelect.addEventListener('change', function() { loadLogs(true); });
    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function() { loadLogs(true); }, 350);
    });

    // Pagination links are intercepted and loaded via AJAX as well.
    paginationEl.addEventListener('click', function(e) {
        const link = e.target.closest('a');
        if (!link || !paginationEl.contains(link)) return;
        e.preventDefault();
        const page = parseInt(new URL(link.href, location.origin).searchParams.get('page') || '1', 10);
        currentPage = isNaN(page) ? 1 : page;
        loadLogs(false);
    });
})();
</script>
@endpush
