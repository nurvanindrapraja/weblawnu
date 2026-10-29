@extends('layouts.admin')
@section('title', 'Roles')
@section('page-title', 'Manajemen Role')
@section('content')
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-lg font-medium text-gray-900">Total: {{ $roles->count() }} role</h2>
        <button onclick="openModal()" class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm hover:bg-emerald-700">+ Tambah Role</button>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 border-b"><tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Display Name</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Deskripsi</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Users</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Permissions</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($roles as $role)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $role->name }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $role->display_name }}</td>
                    <td class="px-4 py-3 text-sm text-gray-500 max-w-xs truncate">{{ $role->description ?? '-' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-500">{{ $role->users_count ?? $role->users->count() }}</td>
                    <td class="px-4 py-3 text-sm text-gray-500">{{ $role->permissions->count() }}</td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-1">
                            <a href="{{ route('admin.roles.edit', $role) }}" class="p-1.5 text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 rounded" title="Edit Permissions"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></a>
                            <button onclick="deleteRole({{ $role->id }}, '{{ $role->name }}')" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded" title="Hapus"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">Belum ada role.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div id="role-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-xl p-6 w-full max-w-md mx-4">
            <h3 class="font-semibold text-gray-900 mb-4">Tambah Role</h3>
            <form id="role-form" class="space-y-4">
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label><input type="text" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="admin"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Display Name <span class="text-red-500">*</span></label><input type="text" name="display_name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Administrator"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label><textarea name="description" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"></textarea></div>
                <div class="flex gap-3 pt-2"><button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm">Simpan</button><button type="button" onclick="document.getElementById('role-modal').classList.add('hidden')" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm">Batal</button></div>
            </form>
        </div>
    </div>
@endsection
@push('scripts')
<script>
function openModal() { document.getElementById('role-modal').classList.remove('hidden'); }
document.getElementById('role-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const fd = new FormData(this);
    try {
        const res = await fetch('{{ route("admin.roles.store") }}', {method:'POST', headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,'Accept':'application/json'}, body:fd});
        const d = await res.json();
        showToast(d.message, d.success?'success':'error');
        if(d.success) setTimeout(()=>location.reload(), 1000);
    } catch(e) { showToast('Error','error'); }
});
async function deleteRole(id, name) {
    const ok = await confirmModal(`Hapus role "${name}"? Tindakan ini tidak dapat dibatalkan.`, {
        title: 'Hapus Role',
        okLabel: 'Hapus',
        okClass: 'bg-red-600 hover:bg-red-700'
    });
    if (!ok) return;
    try {
        const res = await fetch(`{{ url('/admin/roles') }}/${id}`, {method:'DELETE',headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,'Accept':'application/json'}});
        const d = await res.json();
        showToast(d.message, d.success?'success':'error');
        if(d.success) setTimeout(()=>location.reload(), 1000);
    } catch(e) { showToast('Error','error'); }
}
</script>
@endpush
