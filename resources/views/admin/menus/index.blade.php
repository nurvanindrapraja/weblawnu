@extends('layouts.admin')
@section('title', 'Menu')
@section('page-title', 'Manajemen Menu')
@section('content')
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-lg font-medium text-gray-900">Menu Navigasi</h2>
        <button onclick="openModal()" class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm hover:bg-emerald-700">+ Tambah Menu</button>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 border-b"><tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Judul</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">URL</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Target</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sort</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aktif</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($menus as $menu)
                <tr class="hover:bg-gray-50 {{ $menu->parent_id ? 'bg-gray-50/50' : '' }}">
                    <td class="px-4 py-3 text-sm font-medium text-gray-900">
                        {{ $menu->parent_id ? '    ' : '' }}{{ $menu->title }}
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-500 capitalize">{{ $menu->type }}</td>
                    <td class="px-4 py-3 text-sm text-gray-500 max-w-xs truncate">{{ $menu->resolved_url }}</td>
                    <td class="px-4 py-3 text-sm text-gray-500">{{ $menu->target }}</td>
                    <td class="px-4 py-3 text-sm text-gray-500">{{ $menu->sort_order }}</td>
                    <td class="px-4 py-3"><button onclick="toggleMenu({{ $menu->id }}, {{ $menu->is_active ? 0 : 1 }})" class="px-2 py-1 text-xs rounded-full {{ $menu->is_active?'bg-green-100 text-green-700':'bg-gray-100 text-gray-600' }}">{{ $menu->is_active?'Aktif':'Nonaktif' }}</button></td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-1">
                            <button onclick="editMenu({{ $menu->id }}, '{{ $menu->title }}', '{{ $menu->type }}', '{{ $menu->url }}', '{{ $menu->target }}', {{ $menu->sort_order }}, {{ $menu->parent_id ?? 0 }})" class="p-1.5 text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 rounded" title="Edit"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
                            <button onclick="deleteMenu({{ $menu->id }}, '{{ $menu->title }}')" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded" title="Hapus"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-4 py-8 text-center text-gray-500">Belum ada menu.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div id="menu-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-xl p-6 w-full max-w-md mx-4">
            <h3 class="font-semibold text-gray-900 mb-4" id="modal-title">Tambah Menu</h3>
            <form id="menu-form" class="space-y-4">
                <input type="hidden" name="id" id="menu-id">
                <input type="hidden" name="parent_id" id="menu-parent" value="0">
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Judul <span class="text-red-500">*</span></label><input type="text" name="title" id="menu-title" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"></div>
                <div class="grid grid-cols-2 gap-3">
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                        <select name="type" id="menu-type" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            <option value="custom">Custom URL</option>
                            <option value="page">Page</option>
                            <option value="post">Post</option>
                            <option value="category">Category</option>
                            <option value="dropdown">Dropdown</option>
                        </select>
                    </div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Sort</label><input type="number" name="sort_order" id="menu-sort" value="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"></div>
                </div>
                <div id="url-field"><label class="block text-sm font-medium text-gray-700 mb-1">URL</label><input type="text" name="url" id="menu-url" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="/halaman/... atau https://..."></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Target</label>
                    <select name="target" id="menu-target" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="_self">_self</option>
                        <option value="_blank">_blank</option>
                    </select>
                </div>
                <div class="flex gap-3 pt-2"><button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm">Simpan</button><button type="button" onclick="closeModal()" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm">Batal</button></div>
            </form>
        </div>
    </div>
@endsection
@push('scripts')
<script>
function openModal() {
    document.getElementById('menu-modal').classList.remove('hidden');
    document.getElementById('modal-title').textContent = 'Tambah Menu';
    document.getElementById('menu-id').value = '';
    document.getElementById('menu-parent').value = '0';
    document.getElementById('menu-title').value = '';
    document.getElementById('menu-type').value = 'custom';
    document.getElementById('menu-url').value = '';
    document.getElementById('menu-sort').value = '0';
}
function editMenu(id, title, type, url, target, sort, parent) {
    document.getElementById('menu-modal').classList.remove('hidden');
    document.getElementById('modal-title').textContent = 'Edit Menu';
    document.getElementById('menu-id').value = id;
    document.getElementById('menu-parent').value = parent || 0;
    document.getElementById('menu-title').value = title;
    document.getElementById('menu-type').value = type;
    document.getElementById('menu-url').value = url || '';
    document.getElementById('menu-target').value = target || '_self';
    document.getElementById('menu-sort').value = sort;
}
function closeModal() { document.getElementById('menu-modal').classList.add('hidden'); }

document.getElementById('menu-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const id = document.getElementById('menu-id').value;
    const fd = new FormData(this);
    const url = id ? `{{ url('/admin/menus') }}/${id}` : '{{ route("admin.menus.store") }}';
    const method = id ? 'PUT' : 'POST';
    try {
        const res = await fetch(url, {method, headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,'Accept':'application/json'}, body:fd});
        const d = await res.json();
        showToast(d.message, d.success?'success':'error');
        if(d.success) setTimeout(()=>location.reload(), 1000);
    } catch(e) { showToast('Error','error'); }
});

async function toggleMenu(id, val) {
    try {
        const res = await fetch(`{{ url('/admin/menus') }}/${id}/status`, {method:'PATCH',headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,'Accept':'application/json','Content-Type':'application/json'},body:JSON.stringify({is_active:val})});
        const d = await res.json();
        showToast(d.message, d.success?'success':'error');
        if(d.success) setTimeout(()=>location.reload(), 1000);
    } catch(e) { showToast('Error','error'); }
}
async function deleteMenu(id, title) {
    const ok = await confirmModal(`Hapus menu "${title}"? Tindakan ini tidak dapat dibatalkan.`, {
        title: 'Hapus Menu',
        okLabel: 'Hapus',
        okClass: 'bg-red-600 hover:bg-red-700'
    });
    if (!ok) return;
    try {
        const res = await fetch(`{{ url('/admin/menus') }}/${id}`, {method:'DELETE',headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,'Accept':'application/json'}});
        const d = await res.json();
        showToast(d.message, d.success?'success':'error');
        if(d.success) setTimeout(()=>location.reload(), 1000);
    } catch(e) { showToast('Error','error'); }
}
</script>
@endpush
