@extends('layouts.admin')
@section('title', 'Kategori')
@section('page-title', 'Manajemen Kategori')
@section('content')
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-lg font-medium text-gray-900">Total: {{ $categories->count() }} kategori</h2>
        <button onclick="openModal()" class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm hover:bg-emerald-700">+ Tambah Kategori</button>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 border-b"><tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Slug</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Deskripsi</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Berita</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aktif</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($categories as $cat)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $cat->name }}</td>
                    <td class="px-4 py-3 text-sm text-gray-500">{{ $cat->slug }}</td>
                    <td class="px-4 py-3 text-sm text-gray-500 max-w-xs truncate">{{ $cat->description ?? '-' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-500">{{ $cat->posts_count ?? 0 }}</td>
                    <td class="px-4 py-3"><button onclick="toggleStatus({{ $cat->id }}, {{ $cat->is_active ? 0 : 1 }})" class="px-2 py-1 text-xs rounded-full {{ $cat->is_active?'bg-green-100 text-green-700':'bg-gray-100 text-gray-600' }}">{{ $cat->is_active?'Aktif':'Nonaktif' }}</button></td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-1">
                            <button onclick="editCat({{ $cat->id }}, '{{ $cat->name }}', '{{ $cat->slug }}', '{{ $cat->description ?? '' }}')" class="p-1.5 text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 rounded" title="Edit"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
                            <button onclick="deleteCat({{ $cat->id }}, '{{ $cat->name }}')" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded" title="Hapus"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">Belum ada kategori.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Create/Edit Modal -->
    <div id="cat-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-xl p-6 w-full max-w-md mx-4">
            <h3 class="font-semibold text-gray-900 mb-4" id="modal-title">Tambah Kategori</h3>
            <form id="cat-form" class="space-y-4">
                <input type="hidden" name="id" id="cat-id">
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Nama <span class="text-red-500">*</span></label><input type="text" name="name" id="cat-name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Slug</label><input type="text" name="slug" id="cat-slug" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="otomatis"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label><textarea name="description" id="cat-desc" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"></textarea></div>
                <div class="flex gap-3 pt-2"><button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm">Simpan</button><button type="button" onclick="closeModal()" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm">Batal</button></div>
            </form>
        </div>
    </div>
@endsection
@push('scripts')
<script>
function openModal(name='', slug='', desc='') {
    document.getElementById('cat-modal').classList.remove('hidden');
    document.getElementById('modal-title').textContent = name ? 'Edit Kategori' : 'Tambah Kategori';
    document.getElementById('cat-id').value = '';
    document.getElementById('cat-name').value = name;
    document.getElementById('cat-slug').value = slug;
    document.getElementById('cat-desc').value = desc;
}
function editCat(id, name, slug, desc) {
    document.getElementById('cat-id').value = id;
    openModal(name, slug, desc);
}
function closeModal() { document.getElementById('cat-modal').classList.add('hidden'); }

document.getElementById('cat-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const id = document.getElementById('cat-id').value;
    const fd = new FormData(this);
    const url = id ? `{{ url('/admin/categories') }}/${id}` : '{{ route("admin.categories.store") }}';
    const method = id ? 'PUT' : 'POST';
    try {
        const res = await fetch(url, {method, headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,'Accept':'application/json'}, body:fd});
        const d = await res.json();
        showToast(d.message, d.success?'success':'error');
        if(d.success) setTimeout(()=>location.reload(), 1000);
    } catch(e) { showToast('Error','error'); }
});

async function toggleStatus(id, val) {
    try {
        const res = await fetch(`{{ url('/admin/categories') }}/${id}/status`, {method:'PATCH',headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,'Accept':'application/json', 'Content-Type':'application/json'},body:JSON.stringify({is_active:val})});
        const d = await res.json();
        showToast(d.message, d.success?'success':'error');
        if(d.success) setTimeout(()=>location.reload(), 1000);
    } catch(e) { showToast('Error','error'); }
}
async function deleteCat(id, name) {
    const ok = await confirmModal(`Hapus kategori "${name}"? Tindakan ini tidak dapat dibatalkan.`, {
        title: 'Hapus Kategori',
        okLabel: 'Hapus',
        okClass: 'bg-red-600 hover:bg-red-700'
    });
    if (!ok) return;
    try {
        const res = await fetch(`{{ url('/admin/categories') }}/${id}`, {method:'DELETE',headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,'Accept':'application/json'}});
        const d = await res.json();
        showToast(d.message, d.success?'success':'error');
        if(d.success) setTimeout(()=>location.reload(), 1000);
    } catch(e) { showToast('Error','error'); }
}
</script>
@endpush
