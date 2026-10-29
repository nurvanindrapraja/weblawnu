@extends('layouts.admin')
@section('title', 'Halaman')
@section('page-title', 'Manajemen Halaman')
@section('content')
    <div class="flex items-center justify-between mb-6">
        <form class="flex gap-2" method="GET">
            <input type="text" name="search" placeholder="Cari halaman..." value="{{ request('search') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-sm">
            <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                <option value="">Semua</option>
                <option value="draft" {{ request('status')==='draft'?'selected':'' }}>Draft</option>
                <option value="published" {{ request('status')==='published'?'selected':'' }}>Published</option>
                <option value="archived" {{ request('status')==='archived'?'selected':'' }}>Archived</option>
            </select>
            <button class="px-4 py-2 bg-gray-100 rounded-lg text-sm">Filter</button>
        </form>
        <a href="{{ route('admin.pages.create') }}" class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm hover:bg-emerald-700">+ Tambah Halaman</a>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 border-b"><tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Judul</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Slug</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Published</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($pages as $page)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $page->title }}</td>
                    <td class="px-4 py-3 text-sm text-gray-500">/{{ $page->slug }}</td>
                    <td class="px-4 py-3"><span class="px-2 py-1 text-xs rounded-full capitalize {{ $page->status==='published'?'bg-green-100 text-green-700':'bg-gray-100 text-gray-600' }}">{{ $page->status }}</span></td>
                    <td class="px-4 py-3 text-sm text-gray-500">{{ $page->published_at?->format('d M Y') ?? '-' }}</td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-1">
                            <a href="{{ route('admin.pages.edit', $page) }}" class="p-1.5 text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 rounded" title="Edit"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></a>
                            <button onclick="deletePage({{ $page->id }}, '{{ $page->title }}')" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded" title="Hapus"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">Belum ada halaman.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4">{{ $pages->links() }}</div>
    </div>
@endsection
@push('scripts')
<script>
async function deletePage(id, title) {
    const ok = await confirmModal(`Hapus halaman "${title}"? Tindakan ini tidak dapat dibatalkan.`, {
        title: 'Hapus Halaman',
        okLabel: 'Hapus',
        okClass: 'bg-red-600 hover:bg-red-700'
    });
    if (!ok) return;
    fetch(`{{ url('/admin/pages') }}/${id}`, { method:'DELETE', headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,'Accept':'application/json'} })
    .then(r=>r.json()).then(d=>{showToast(d.message,d.success?'success':'error');if(d.success)setTimeout(()=>location.reload(),1000)}).catch(()=>showToast('Gagal menghapus.','error'));
}
</script>
@endpush
