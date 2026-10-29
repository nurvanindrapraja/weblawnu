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
                @if(request()->routeIs('admin.galleries.*'))
                <p class="text-xs text-gray-400 truncate">{{ $gallery->author?->name }}</p>
                @endif
            </div>
        </div>
    </td>
    <td class="px-4 py-3 text-sm text-gray-500 whitespace-nowrap">{{ $gallery->event_date?->format('d M Y') ?? '-' }}</td>
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
    <td class="px-4 py-3 text-sm text-gray-500 whitespace-nowrap">{{ $gallery->created_at->format('d M Y') }}</td>
    <td class="px-4 py-3 text-right">
        @include('admin.galleries.partials.actions')
    </td>
</tr>
@empty
<tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">Belum ada galeri.</td></tr>
@endforelse
