@extends('layouts.admin')
@section('title', 'Pesan Kontak')
@section('page-title', 'Manajemen Pesan Kontak')
@section('content')
    @php
        // Shared view, rendered from admin/redaktur contact routes.
        $contactsRoute = request()->routeIs('redaktur.contacts.*') ? 'redaktur.contacts' : 'admin.contacts';
    @endphp
    <div class="flex flex-wrap gap-2 mb-6">
        @foreach([['','Semua'],['new','Baru'],['read','Dibaca'],['in_progress','Diproses'],['resolved','Selesai']] as [$val,$label])
        <a href="?status={{ $val }}" class="px-3 py-1.5 text-sm rounded-full {{ request('status')===$val ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">{{ $label }}</a>
        @endforeach
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 border-b"><tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tiket</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pengirim</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subjek</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($messages as $msg)
                <tr class="hover:bg-gray-50 {{ $msg->status==='new' ? 'bg-blue-50' : '' }}">
                    <td class="px-4 py-3 font-mono text-xs text-gray-600 whitespace-nowrap">{{ $msg->ticket_number }}</td>
                    <td class="px-4 py-3">
                        <p class="text-sm font-medium text-gray-900">{{ $msg->name }}</p>
                        <p class="text-xs text-gray-500">{{ $msg->email }}</p>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600 max-w-xs truncate">{{ $msg->subject }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 text-xs rounded-full capitalize {{
                            match($msg->status) {
                                'new' => 'bg-blue-100 text-blue-700',
                                'read' => 'bg-gray-100 text-gray-600',
                                'in_progress' => 'bg-yellow-100 text-yellow-700',
                                'resolved' => 'bg-green-100 text-green-700',
                                default => 'bg-gray-100 text-gray-600'
                            }
                        }}">{{ $msg->status }}</span>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-500">{{ $msg->created_at->format('d M Y H:i') }}</td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-1">
                            <a href="{{ route($contactsRoute.'.show', $msg->id) }}" class="p-1.5 text-emerald-600 hover:bg-emerald-50 rounded" title="Lihat">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                            @if(auth()->user()->isAdmin())
                            <button onclick="deleteMsg({{ $msg->id }})" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded" title="Hapus"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">Belum ada pesan.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4">{{ $messages->links() }}</div>
    </div>
@endsection
@push('scripts')
<script>
async function deleteMsg(id) {
    const ok = await confirmModal('Hapus pesan ini? Tindakan ini tidak dapat dibatalkan.', {
        title: 'Hapus Pesan',
        okLabel: 'Hapus',
        okClass: 'bg-red-600 hover:bg-red-700'
    });
    if (!ok) return;
    fetch(`{{ url('/admin/contact-messages') }}/${id}`, {
        method:'DELETE', headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,'Accept':'application/json'}
    }).then(r=>r.json()).then(d=>{showToast(d.message,d.success?'success':'error');if(d.success)setTimeout(()=>location.reload(),1000)}).catch(()=>showToast('Error','error'));
}
</script>
@endpush
