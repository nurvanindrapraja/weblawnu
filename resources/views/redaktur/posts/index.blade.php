@extends('layouts.admin')
@section('title', 'Review Berita')
@section('page-title', 'Review Berita')
@section('content')
    <div class="flex flex-wrap gap-2 mb-6">
        @foreach([['','Semua'],['submitted','Submitted'],['review','In Review'],['approved','Approved'],['published','Published'],['rejected','Rejected']] as [$val,$label])
        <a href="?status={{ $val }}" class="px-3 py-1.5 text-sm rounded-full {{ request('status')===$val ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">{{ $label }}</a>
        @endforeach
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
                                    'published' => 'bg-green-100 text-green-700',
                                    'submitted' => 'bg-yellow-100 text-yellow-700',
                                    'review' => 'bg-blue-100 text-blue-700',
                                    'approved' => 'bg-indigo-100 text-indigo-700',
                                    'rejected' => 'bg-red-100 text-red-700',
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
                                @if($post->status === 'submitted')
                                <button onclick="postAction('{{ $post->slug }}', 'start-review')" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded" title="Mulai Review">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </button>
                                @endif
                                @if(in_array($post->status, ['submitted', 'review']))
                                <button onclick="postAction('{{ $post->slug }}', 'approve')" class="p-1.5 text-green-600 hover:bg-green-50 rounded" title="Approve">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                </button>
                                <button onclick="postAction('{{ $post->slug }}', 'reject')" class="p-1.5 text-red-600 hover:bg-red-50 rounded" title="Reject">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                                @endif
                                @if($post->status === 'approved')
                                <button onclick="postAction('{{ $post->slug }}', 'publish')" class="p-1.5 text-green-600 hover:bg-green-50 rounded" title="Publish">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"/></svg>
                                </button>
                                @endif
                                @if($post->status === 'published')
                                <button onclick="postAction('{{ $post->slug }}', 'unpublish')" class="p-1.5 text-orange-600 hover:bg-orange-50 rounded" title="Unpublish">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-8 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">Tidak ada berita.</td></tr>
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
