@forelse($media as $item)
    @php
        $viewer = auth()->user();
        $canDelete = $viewer->isAdmin()
            || ($viewer->isReporter()
                && $item->uploaded_by === $viewer->id
                && ! in_array($item->id, $publishedMediaIds ?? []));
    @endphp
    <div class="bg-white rounded-xl shadow-sm overflow-hidden group relative">
    <div class="aspect-square bg-gray-100 flex items-center justify-center">
        @if($item->isImage())
        <img src="{{ $item->url }}" alt="{{ $item->alt_text }}" class="w-full h-full object-cover">
        @else
        <div class="text-gray-400"><svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg></div>
        @endif
    </div>
    <div class="p-3">
        <p class="text-xs font-medium text-gray-900 truncate">{{ $item->original_name }}</p>
        <p class="text-xs text-gray-500">{{ $item->formatted_size }} · {{ $item->created_at->format('d M Y') }}</p>
    </div>
    @if($canDelete)
    <div class="absolute top-2 right-2 hidden group-hover:flex gap-1">
        <button onclick="deleteMedia({{ $item->id }}, '{{ $item->original_name }}')" class="p-1.5 bg-white rounded shadow text-red-600 hover:bg-red-50"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
    </div>
    @endif
</div>
@empty
<div class="col-span-full text-center py-12 text-gray-500">Belum ada media.</div>
@endforelse
