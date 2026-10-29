@extends('layouts.admin')
@section('title', 'Pendaftaran')
@section('page-title', 'Pendaftaran Reporter / Redaktur')
@section('content')
    @if(session('success'))
    <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm">{{ session('error') }}</div>
    @endif

    @php
        // Dibangun di @php agar kurung di dalam label tidak mengganggu
        // parsing direktif @foreach.
        $statusChips = [
            ['value' => '', 'label' => 'Semua ('.$counts['all'].')'],
            ['value' => 'pending', 'label' => 'Menunggu ('.$counts['pending'].')'],
            ['value' => 'approved', 'label' => 'Disetujui ('.$counts['approved'].')'],
            ['value' => 'rejected', 'label' => 'Ditolak ('.$counts['rejected'].')'],
        ];
    @endphp
    <div class="flex flex-wrap gap-2 mb-6">
        @foreach($statusChips as $chip)
        <a href="?status={{ $chip['value'] }}" class="px-3 py-1.5 text-sm rounded-full {{ request('status')===$chip['value'] ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">{{ $chip['label'] }}</a>
        @endforeach
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 border-b"><tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kontak</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Peran</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Motivasi</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($registrations as $reg)
                <tr class="hover:bg-gray-50 {{ $reg->isPending() ? 'bg-blue-50/40' : '' }}">
                    <td class="px-4 py-3">
                        <p class="text-sm font-medium text-gray-900">{{ $reg->name }}</p>
                        <p class="text-xs text-gray-500">{{ $reg->username }}</p>
                    </td>
                    <td class="px-4 py-3">
                        <p class="text-sm text-gray-600">{{ $reg->email }}</p>
                        @if($reg->phone)<p class="text-xs text-gray-400">{{ $reg->phone }}</p>@endif
                    </td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 text-xs rounded-full capitalize {{ $reg->role==='reporter' ? 'bg-emerald-100 text-emerald-700' : 'bg-indigo-100 text-indigo-700' }}">{{ $reg->role }}</span>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600 max-w-xs">
                        @if($reg->reason)
                            <span class="line-clamp-2" title="{{ $reg->reason }}">{{ $reg->reason }}</span>
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 text-xs rounded-full {{
                            match($reg->status) {
                                'pending' => 'bg-yellow-100 text-yellow-700',
                                'approved' => 'bg-green-100 text-green-700',
                                'rejected' => 'bg-red-100 text-red-700',
                                default => 'bg-gray-100 text-gray-600',
                            }
                        }}">{{
                            match($reg->status) {
                                'pending' => 'Menunggu',
                                'approved' => 'Disetujui',
                                'rejected' => 'Ditolak',
                                default => $reg->status,
                            }
                        }}</span>
                        @if($reg->admin_note)
                        <p class="text-xs text-gray-400 mt-1 max-w-[12rem] line-clamp-2" title="{{ $reg->admin_note }}">{{ $reg->admin_note }}</p>
                        @endif
                        @if($reg->verifier)
                        <p class="text-xs text-gray-400 mt-1">oleh {{ $reg->verifier->name }}</p>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-500 whitespace-nowrap">{{ $reg->created_at->format('d M Y') }}</td>
                    <td class="px-4 py-3 text-right">
                        @if($reg->isPending())
                        <div class="flex items-center justify-end gap-1">
                            <button onclick="verifyReg({{ $reg->id }}, '{{ addslashes($reg->name) }}')"
                                    class="p-1.5 text-emerald-600 hover:bg-emerald-50 rounded" title="Setujui & buat akun">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </button>
                            <button onclick="rejectReg({{ $reg->id }}, '{{ addslashes($reg->name) }}')"
                                    class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded" title="Tolak">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </button>
                        </div>
                        @else
                        <span class="text-xs text-gray-400">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-4 py-8 text-center text-gray-500">Belum ada pendaftaran.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
        <div class="p-4">{{ $registrations->links() }}</div>
    </div>
@endsection
@push('scripts')
<script>
async function verifyReg(id, name) {
    const ok = await confirmModal(`Setujui pendaftaran "${name}"? Akun akan langsung dibuat dan dapat login.`, {
        title: 'Setujui Pendaftaran',
        okLabel: 'Setujui',
        okClass: 'bg-emerald-600 hover:bg-emerald-700'
    });
    if (!ok) return;
    fetch(`/admin/registrations/${id}/approve`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' }
    }).then(r=>r.json()).then(d=>{
        showToast(d.message, d.success ? 'success' : 'error');
        if (d.success) setTimeout(()=>location.reload(), 1000);
    }).catch(()=>showToast('Error','error'));
}

async function rejectReg(id, name) {
    const note = await confirmModal(`Tolak pendaftaran "${name}"?`, {
        title: 'Tolak Pendaftaran',
        okLabel: 'Tolak',
        okClass: 'bg-red-600 hover:bg-red-700',
        input: true,
        inputPlaceholder: 'Alasan penolakan (wajib)...'
    });
    if (note === false) return;
    if (!String(note).trim()) {
        showToast('Alasan penolakan wajib diisi.', 'error');
        return;
    }
    const fd = new URLSearchParams({ admin_note: String(note).trim() });
    fetch(`/admin/registrations/${id}/reject`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
        body: fd
    }).then(r=>r.json()).then(d=>{
        showToast(d.message, d.success ? 'success' : 'error');
        if (d.success) setTimeout(()=>location.reload(), 1000);
    }).catch(()=>showToast('Error','error'));
}
</script>
@endpush
