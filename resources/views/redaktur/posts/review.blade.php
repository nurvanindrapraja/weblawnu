@extends('layouts.admin')
@section('title', 'Menunggu Review')
@section('page-title', 'Menunggu Review')
@section('content')
    <div class="flex items-center justify-between mb-6">
        <a href="{{ route('redaktur.dashboard') }}" class="text-sm text-gray-500 hover:text-emerald-600">← Kembali ke Dashboard</a>
        <a href="{{ route('redaktur.posts.index') }}" class="text-sm text-gray-500 hover:text-emerald-600">Semua Berita →</a>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b"><tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Judul</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Penulis</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kategori</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Submitted</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                </tr></thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($posts as $post)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <a href="{{ route('redaktur.posts.review-detail', $post) }}" class="text-sm font-medium text-gray-900 hover:text-emerald-600">{{ $post->title }}</a>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $post->author?->name }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $post->category?->name ?? '-' }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 text-xs rounded-full capitalize {{
                                match($post->status) {
                                    'submitted' => 'bg-yellow-100 text-yellow-700',
                                    'review' => 'bg-blue-100 text-blue-700',
                                    default => 'bg-gray-100 text-gray-600'
                                }
                            }}">{{ $post->status }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $post->submitted_at?->format('d M Y') ?? '-' }}</td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <button type="button" onclick="openPostPreviewGet('{{ route('redaktur.posts.preview', $post) }}')" class="p-1.5 text-blue-500 hover:bg-blue-50 rounded" title="Preview tampilan publik">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </button>
                                <a href="{{ route('redaktur.posts.review-detail', $post) }}" class="p-1.5 text-emerald-600 hover:bg-emerald-50 rounded" title="Review">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                @if(in_array($post->status, ['submitted', 'review']))
                                <button onclick="postAction('{{ $post->slug }}', 'approve')" class="p-1.5 text-green-600 hover:bg-green-50 rounded" title="Approve">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                </button>
                                <button onclick="postAction('{{ $post->slug }}', 'reject')" class="p-1.5 text-red-600 hover:bg-red-50 rounded" title="Reject">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">Tidak ada berita yang menunggu review.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{!! $posts->links() !!}</div>
    </div>

    {{-- Preview modal (iframe, stays on this page) --}}
    <x-post-preview-modal />
@endsection
@push('scripts')
<script>
async function postAction(slug, action) {
    let note = '';
    if (action === 'reject') {
        note = await confirmModal('Tolak berita ini? Anda dapat memberikan catatan untuk reporter.', {
            title: 'Tolak Berita',
            okLabel: 'Tolak',
            okClass: 'bg-red-600 hover:bg-red-700',
            input: true,
            inputPlaceholder: 'Catatan penolakan (opsional)...'
        });
        if (note === false) return;
    } else {
        const ok = await confirmModal(`Yakin ingin ${action.replace('-',' ')} berita ini?`, {
            title: 'Konfirmasi',
            okLabel: 'Ya, lanjutkan'
        });
        if (!ok) return;
    }

    const body = action === 'reject' ? JSON.stringify({ note }) : null;

    fetch(`{{ url('/redaktur/posts') }}/${slug}/${action}`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body
    }).then(r=>r.json()).then(d=>{
        showToast(d.message, d.success?'success':'error');
        if(d.success) setTimeout(()=>location.reload(), 1000);
    }).catch(()=>showToast('Error','error'));
}
</script>
@endpush
