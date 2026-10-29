@extends('layouts.admin')
@section('title', 'Edit Role')
@section('page-title', 'Edit Role: '.$role->name)
@section('content')
    <a href="{{ route('admin.roles.index') }}" class="text-sm text-gray-500 hover:text-emerald-600 mb-4 inline-block">← Kembali</a>

    <div class="max-w-2xl bg-white rounded-xl shadow-sm p-6">
        <form id="role-form" class="space-y-5">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label><input type="text" name="name" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg" value="{{ $role->name }}"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Display Name <span class="text-red-500">*</span></label><input type="text" name="display_name" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg" value="{{ $role->display_name }}"></div>
            </div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label><textarea name="description" rows="2" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg">{{ $role->description }}</textarea></div>

            <hr class="my-4">
            <h3 class="font-semibold text-gray-900">Permissions</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-96 overflow-y-auto">
                @foreach($permissions as $perm)
                <label class="flex items-center gap-2 text-sm p-2 rounded-lg hover:bg-gray-50 cursor-pointer">
                    <input type="checkbox" name="permissions[]" value="{{ $perm->name }}"
                           class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
                           {{ in_array($perm->name, $role->permissions->pluck('name')->toArray()) ? 'checked' : '' }}>
                    <span class="text-gray-700">{{ $perm->display_name }}</span>
                </label>
                @endforeach
            </div>

            <div class="flex gap-3 pt-4">
                <button type="button" onclick="saveRole()" class="px-6 py-2.5 bg-emerald-600 text-white rounded-lg text-sm hover:bg-emerald-700">Simpan</button>
                <a href="{{ route('admin.roles.index') }}" class="px-6 py-2.5 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200">Batal</a>
            </div>
        </form>
    </div>
@endsection
@push('scripts')
<script>
async function saveRole() {
    const btn = document.querySelector('button[onclick*="saveRole"]');
    btn.disabled=true;btn.textContent='Menyimpan...';
    try {
        const fd = new FormData(document.getElementById('role-form'));
        const data = { name: fd.get('name'), display_name: fd.get('display_name'), description: fd.get('description'), permissions: [] };
        document.querySelectorAll('input[name="permissions[]"]:checked').forEach(cb => data.permissions.push(cb.value));
        const res = await fetch(`{{ url('/admin/roles') }}/{{ $role->id }}/permissions`, {
            method:'PUT',
            headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,'Accept':'application/json','Content-Type':'application/json'},
            body:JSON.stringify(data)
        });
        const d=await res.json();showToast(d.message,d.success?'success':'error');
        if(d.success)setTimeout(()=>location.href='{{ route("admin.roles.index") }}',1000);
    }catch(e){showToast('Error','error')}finally{btn.disabled=false;btn.textContent='Simpan'}
}
</script>
@endpush
