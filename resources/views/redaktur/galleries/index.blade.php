@extends('layouts.admin')

@section('title', 'Review Galeri')
@section('page-title', 'Review Galeri Kegiatan')
@section('content')
    <div class="flex flex-wrap gap-2 mb-6">
        @php
            $statusChips = [
                ['value' => 'submitted', 'label' => 'Menunggu Review ('.$counts['submitted'].')'],
                ['value' => 'published', 'label' => 'Published ('.$counts['published'].')'],
                ['value' => 'rejected', 'label' => 'Ditolak ('.$counts['rejected'].')'],
                ['value' => '', 'label' => 'Semua ('.$counts['all'].')'],
            ];
        @endphp
        @foreach($statusChips as $chip)
        <a href="?status={{ $chip['value'] }}" class="px-3 py-1.5 text-sm rounded-full {{ request('status') === $chip['value'] ? 'bg-emerald-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-100' }}">{{ $chip['label'] }}</a>
        @endforeach
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kegiatan</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reporter</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Foto</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($galleries as $gallery)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <?php $cover = $gallery->images->first(); ?>
                                @if($cover)
                                    <img src="{{ $cover->url }}" alt="" class="w-12 h-9 object-cover rounded bg-gray-200 flex-shrink-0">
                                @else
                                    <div class="w-12 h-9 rounded bg-gray-100 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01"/></svg>
                                    </div>
                                @endif
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate max-w-[14rem]">{{ $gallery->title }}</p>
                                    <p class="text-xs text-gray-400">{{ $gallery->submitted_at?->format('d M Y H:i') ?? $gallery->created_at->format('d M Y') }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $gallery->author?->name ?? '-' }}</td>
                        <td class="px-4 py-3">
                            @php
                                $badges = [
                                    'draft' => ['bg-gray-100 text-gray-600', 'Draft'],
                                    'submitted' => ['bg-yellow-100 text-yellow-700', 'Menunggu Review'],
                                    'published' => ['bg-green-100 text-green-700', 'Published'],
                                    'rejected' => ['bg-red-100 text-red-700', 'Ditolak'],
                                ];
                                [$badgeClass, $badgeLabel] = $badges[$gallery->status] ?? ['bg-gray-100 text-gray-600', $gallery->status];
                            @endphp
                            <span class="px-2.5 py-1 text-xs font-medium rounded-full {{ $badgeClass }}">{{ $badgeLabel }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500 text-center">{{ $gallery->images_count ?? $gallery->images->count() }}</td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('redaktur.galleries.review', $gallery) }}" class="p-1.5 text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 rounded" title="Lihat & Review">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                @if($gallery->status === 'submitted')
                                <button onclick="approveGallery('{{ $gallery->slug }}', '{{ addslashes($gallery->title) }}')" class="p-1.5 text-gray-400 hover:text-green-600 hover:bg-green-50 rounded" title="Setujui & publish">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </button>
                                <button onclick="rejectGallery('{{ $gallery->slug }}', '{{ addslashes($gallery->title) }}')" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded" title="Tolak">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">Tidak ada galeri.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{!! $galleries->links() !!}</div>
    </div>
@endsection

@push('scripts')
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

async function approveGallery(slug, title) {
    const ok = await confirmModal(`Setujui & publikasikan galeri "${title}"?`, {
        title: 'Setujui Galeri',
        okLabel: 'Setujui',
        okClass: 'bg-emerald-600 hover:bg-emerald-700'
    });
    if (!ok) return;
    try {
        const res = await fetch(`/redaktur/galleries/${slug}/approve`, {
            method: 'PATCH',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
        });
        const data = await res.json();
        showToast(data.message, data.success ? 'success' : 'error');
        if (data.success) setTimeout(() => location.reload(), 1000);
    } catch (e) {
        showToast('Terjadi kesalahan.', 'error');
    }
}

async function rejectGallery(slug, title) {
    let note = '';
    do {
        note = await confirmModal(`Tolak galeri "${title}"? Ketik catatan penolakan.`, {
            title: 'Tolak Galeri',
            okLabel: 'Tolak',
            okClass: 'bg-red-600 hover:bg-red-700',
            input: true,
            inputPlaceholder: 'Catatan penolakan (wajib)'
        });
        if (note === false) return;
    } while (!note.trim());
    try {
        const res = await fetch(`/redaktur/galleries/${slug}/reject`, {
            method: 'PATCH',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'Content-Type': 'application/json' },
            body: JSON.stringify({ note })
        });
        const data = await res.json();
        showToast(data.message, data.success ? 'success' : 'error');
        if (data.success) setTimeout(() => location.reload(), 1000);
    } catch (e) {
        showToast('Terjadi kesalahan.', 'error');
    }
}
</script>
@endpush
