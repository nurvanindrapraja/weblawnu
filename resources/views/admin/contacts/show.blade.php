@extends('layouts.admin')
@section('title', 'Detail Pesan')
@section('page-title', 'Detail Pesan')
@section('content')
    @php
        // Shared view, rendered from admin/redaktur contact routes.
        $contactsRoute = request()->routeIs('redaktur.contacts.*') ? 'redaktur.contacts' : 'admin.contacts';
        $replyUrl = route($contactsRoute.'.reply', $message->id);
    @endphp
    <a href="{{ route($contactsRoute.'.index') }}" class="text-sm text-gray-500 hover:text-emerald-600 mb-4 inline-block">← Kembali</a>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900">{{ $message->subject }}</h2>
                <span class="px-2 py-1 text-xs rounded-full capitalize {{
                    match($message->status) {
                        'new' => 'bg-blue-100 text-blue-700',
                        'read' => 'bg-gray-100 text-gray-600',
                        'in_progress' => 'bg-yellow-100 text-yellow-700',
                        'resolved' => 'bg-green-100 text-green-700',
                        default => 'bg-gray-100 text-gray-600'
                    }
                }}">{{ $message->status }}</span>
            </div>
            <div class="prose max-w-none text-gray-700">
                <p class="whitespace-pre-wrap">{{ $message->message }}</p>
            </div>
        </div>

        <div class="space-y-4">
            <div class="bg-white rounded-xl shadow-sm p-5">
                <h3 class="font-semibold text-gray-900 mb-3">Info Pengirim</h3>
                <dl class="space-y-2 text-sm">
                    <div><dt class="text-gray-500">Nama</dt><dd class="text-gray-900">{{ $message->name }}</dd></div>
                    <div><dt class="text-gray-500">Email</dt><dd class="text-gray-900">{{ $message->email }}</dd></div>
                    @if($message->phone)<div><dt class="text-gray-500">Telepon</dt><dd class="text-gray-900">{{ $message->phone }}</dd></div>@endif
                    <div><dt class="text-gray-500">Tanggal</dt><dd class="text-gray-900">{{ $message->created_at->format('d M Y H:i') }}</dd></div>
                </dl>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-5">
                <h3 class="font-semibold text-gray-900 mb-3">Balasan untuk Pengunjung</h3>
                @if($message->reply)
                <div class="bg-emerald-50 border border-emerald-100 rounded-lg p-3 mb-3">
                    <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $message->reply }}</p>
                    <p class="text-xs text-gray-400 mt-2">Dibalas {{ $message->replied_at->format('d M Y H:i') }}</p>
                </div>
                @endif
                <form id="reply-form" class="space-y-3">
                    <textarea name="reply" rows="4" placeholder="Tulis balasan untuk pengunjung... (tampil di halaman cek status tiket {{ $message->ticket_number }})" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">{{ old('reply', $message->reply) }}</textarea>
                    <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700">Kirim Balasan</button>
                </form>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-5">
                <h3 class="font-semibold text-gray-900 mb-3">Ubah Status</h3>
                <form id="status-form" class="space-y-3">
                    <input type="hidden" name="id" value="{{ $message->id }}">
                    <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        @foreach(['new','read','in_progress','resolved','archived'] as $s)
                        <option value="{{ $s }}" {{ $message->status===$s?'selected':'' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                        @endforeach
                    </select>
                    <textarea name="admin_note" rows="3" placeholder="Catatan admin..." class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">{{ $message->admin_note }}</textarea>
                    <button type="submit" class="w-full px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm hover:bg-emerald-700">Simpan</button>
                </form>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
<script>
document.getElementById('reply-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const fd = new FormData(this);
    try {
        const res = await fetch('{{ $replyUrl }}', {
            method: 'PATCH',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
            body: fd
        });
        const d = await res.json();
        showToast(d.message, d.success?'success':'error');
        if(d.success) setTimeout(()=>location.reload(), 1000);
    } catch(e) { showToast('Error','error'); }
});

document.getElementById('status-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const fd = new FormData(this);
    try {
        const res = await fetch('{{ route($contactsRoute.'.update-status', $message->id) }}', {
            method: 'PATCH',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
            body: fd
        });
        const d = await res.json();
        showToast(d.message, d.success?'success':'error');
        if(d.success) setTimeout(()=>location.reload(), 1000);
    } catch(e) { showToast('Error','error'); }
});
</script>
@endpush
