@extends('layouts.admin')

@php
    $isReporterRoute = request()->routeIs('reporter.galleries.*');
    $createUrl = $isReporterRoute ? route('reporter.galleries.create') : route('admin.galleries.create');
    $filterUrl = $isReporterRoute ? route('reporter.galleries.filter') : route('admin.galleries.filter');
    $apiBase = $isReporterRoute ? url('/reporter/galleries') : url('/admin/galleries');
@endphp

@section('title', 'Galeri')
@section('page-title', 'Galeri Kegiatan')
@section('content')
    <div class="flex items-center justify-between mb-6">
        <div class="flex flex-wrap items-center gap-2" role="search">
            <div class="relative">
                <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" id="gallery-search" placeholder="Cari galeri..." value="{{ request('search') }}"
                       class="pl-9 pr-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500" autocomplete="off">
            </div>
            <div class="relative">
                <select id="gallery-status-filter"
                        class="appearance-none pl-3 pr-9 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 cursor-pointer bg-white">
                    <option value="">Semua Status</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="submitted" {{ request('status') === 'submitted' ? 'selected' : '' }}>Menunggu Review</option>
                    <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                </select>
                <svg class="w-4 h-4 text-gray-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </div>
        </div>
        <a href="{{ $createUrl }}" class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm hover:bg-emerald-700">
            + Tambah Galeri
        </a>
    </div>

    @if($counts)
    <div class="flex flex-wrap gap-2 mb-4">
        @php
            $statusChips = [
                ['value' => '', 'label' => 'Semua ('.$counts['all'].')'],
                ['value' => 'draft', 'label' => 'Draft ('.$counts['draft'].')'],
                ['value' => 'submitted', 'label' => 'Menunggu ('.$counts['submitted'].')'],
                ['value' => 'published', 'label' => 'Published ('.$counts['published'].')'],
                ['value' => 'rejected', 'label' => 'Ditolak ('.$counts['rejected'].')'],
            ];
        @endphp
        @foreach($statusChips as $chip)
        <a href="?status={{ $chip['value'] }}" class="px-3 py-1.5 text-sm rounded-full {{ request('status') === $chip['value'] ? 'bg-emerald-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-100' }}">{{ $chip['label'] }}</a>
        @endforeach
    </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kegiatan</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal Kegiatan</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Foto</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dibuat</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody id="gallery-rows" class="divide-y divide-gray-100">
                    @include('admin.galleries.partials.rows')
                </tbody>
            </table>
        </div>
        <div id="gallery-pagination" class="p-4">{!! $galleries->links() !!}</div>
    </div>
@endsection

@push('scripts')
<script>
(function() {
    const searchInput = document.getElementById('gallery-search');
    const statusSelect = document.getElementById('gallery-status-filter');
    const rowsEl = document.getElementById('gallery-rows');
    const paginationEl = document.getElementById('gallery-pagination');
    const filterUrl = @json($filterUrl);

    let debounceTimer = null;

    function buildUrl(page) {
        const params = new URLSearchParams();
        if (searchInput.value.trim()) params.set('search', searchInput.value.trim());
        if (statusSelect.value) params.set('status', statusSelect.value);
        if (page > 1) params.set('page', page);
        const qs = params.toString();
        return qs ? filterUrl + '?' + qs : filterUrl;
    }

    async function loadRows(page) {
        showLoading();
        try {
            const res = await fetch(buildUrl(page), { headers: { 'Accept': 'application/json' } });
            const data = await res.json();
            if (!data.success) return;
            rowsEl.innerHTML = data.rows;
            paginationEl.innerHTML = data.pagination;
            bindPagination();
            const qs = buildUrl(page).split('?')[1];
            history.replaceState(null, '', qs ? '?' + qs : location.pathname);
        } catch (e) {
            showToast('Gagal memuat data.', 'error');
        } finally {
            hideLoading();
        }
    }

    function bindPagination() {
        paginationEl.querySelectorAll('a[href*="page="]').forEach((link) => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const page = parseInt(new URL(link.href, location.origin).searchParams.get('page') || '1', 10);
                loadRows(page);
            });
        });
    }
    bindPagination();

    searchInput.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => loadRows(1), 300);
    });
    statusSelect.addEventListener('change', () => loadRows(1));
})();

const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
const apiBase = @json($apiBase);

async function patchGallery(slug, action, extraBody) {
    const ok = await confirmModal(`Konfirmasi aksi "${action}"?`);
    if (!ok) return;
    try {
        const res = await fetch(`${apiBase}/${slug}/${action}`, {
            method: 'PATCH',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: extraBody || undefined
        });
        const data = await res.json();
        showToast(data.message, data.success ? 'success' : 'error');
        if (data.success) setTimeout(() => location.reload(), 1000);
    } catch (e) {
        showToast('Terjadi kesalahan.', 'error');
    }
}

async function submitGallery(slug, title) {
    const ok = await confirmModal(`Submit galeri "${title}" untuk review redaktur?`);
    if (!ok) return;
    try {
        const res = await fetch(`${apiBase}/${slug}/submit`, {
            method: 'PATCH',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
        });
        const data = await res.json();
        showToast(data.message, data.success ? 'success' : 'error');
        if (data.success) setTimeout(() => location.reload(), 1000);
    } catch (e) {
        showToast('Terjadi kesalahan.', 'error');
    }
}

function publishGallery(slug, title) { patchGallery(slug, 'publish'); }
function unpublishGallery(slug, title) { patchGallery(slug, 'unpublish'); }

async function approveGallery(slug, title) {
    const ok = await confirmModal(`Setujui & publikasikan galeri "${title}"?`, {
        title: 'Setujui Galeri',
        okLabel: 'Setujui',
        okClass: 'bg-emerald-600 hover:bg-emerald-700'
    });
    if (!ok) return;
    try {
        const res = await fetch(`${apiBase}/${slug}/approve`, {
            method: 'PATCH',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
        });
        const data = await res.json();
        showToast(data.message, data.success ? 'success' : 'error');
        if (data.success) setTimeout(() => location.reload(), 1000);
    } catch (e) {
        showToast('Terjadi kesalahan.', 'error');
    }
}

async function rejectGallery(slug, title) {
    let note = '';
    do {
        note = await confirmModal(`Tolak galeri "${title}"? Ketik catatan penolakan.`, {
            title: 'Tolak Galeri',
            okLabel: 'Tolak',
            okClass: 'bg-red-600 hover:bg-red-700',
            input: true,
            inputPlaceholder: 'Catatan penolakan (wajib)'
        });
        if (note === false) return; // dibatalkan
    } while (!note.trim());
    try {
        const res = await fetch(`${apiBase}/${slug}/reject`, {
            method: 'PATCH',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'Content-Type': 'application/json' },
            body: JSON.stringify({ note })
        });
        const data = await res.json();
        showToast(data.message, data.success ? 'success' : 'error');
        if (data.success) setTimeout(() => location.reload(), 1000);
    } catch (e) {
        showToast('Terjadi kesalahan.', 'error');
    }
}

async function deleteGallery(slug, title) {
    const ok = await confirmModal(`Hapus galeri "${title}"? Tindakan ini tidak dapat dibatalkan.`, {
        title: 'Hapus Galeri',
        okLabel: 'Hapus',
        okClass: 'bg-red-600 hover:bg-red-700'
    });
    if (!ok) return;
    try {
        const res = await fetch(`${apiBase}/${slug}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
        });
        const data = await res.json();
        showToast(data.message, data.success ? 'success' : 'error');
        if (data.success) setTimeout(() => location.reload(), 1000);
    } catch (e) {
        showToast('Terjadi kesalahan.', 'error');
    }
}
</script>
@endpush
