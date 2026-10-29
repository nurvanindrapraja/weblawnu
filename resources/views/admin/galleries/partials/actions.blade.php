@php
    $isReporterRoute = request()->routeIs('reporter.galleries.*');
@endphp
<div class="flex items-center justify-end gap-1">
    @if($isReporterRoute)
        @if(in_array($gallery->status, ['draft', 'rejected'], true))
        <a href="{{ route('reporter.galleries.edit', $gallery) }}" class="p-1.5 text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 rounded" title="Edit">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
        </a>
        @endif
        @if(in_array($gallery->status, ['draft', 'rejected'], true))
        <button onclick="submitGallery('{{ $gallery->slug }}', '{{ addslashes($gallery->title) }}')" class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded" title="Submit untuk review">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
        </button>
        @endif
        @if($gallery->status === 'draft')
        <button onclick="deleteGallery('{{ $gallery->slug }}', '{{ addslashes($gallery->title) }}')" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded" title="Hapus">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
        </button>
        @endif
    @else
        <a href="{{ route('admin.galleries.edit', $gallery) }}" class="p-1.5 text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 rounded" title="Edit">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
        </a>
        @if($gallery->status === 'submitted')
        <button onclick="approveGallery('{{ $gallery->slug }}', '{{ addslashes($gallery->title) }}')" class="p-1.5 text-gray-400 hover:text-green-600 hover:bg-green-50 rounded" title="Setujui & publish">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </button>
        <button onclick="rejectGallery('{{ $gallery->slug }}', '{{ addslashes($gallery->title) }}')" class="p-1.5 text-gray-400 hover:text-orange-600 hover:bg-orange-50 rounded" title="Tolak">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </button>
        @endif
        @if($gallery->status === 'published')
        <button onclick="unpublishGallery('{{ $gallery->slug }}', '{{ addslashes($gallery->title) }}')" class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded" title="Unpublish">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
        </button>
        @else
        <button onclick="publishGallery('{{ $gallery->slug }}', '{{ addslashes($gallery->title) }}')" class="p-1.5 text-gray-400 hover:text-green-600 hover:bg-green-50 rounded" title="Publish langsung">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18M15 16l4-4m0 0l-4-4m4 4H3"/></svg>
        </button>
        @endif
        <button onclick="deleteGallery('{{ $gallery->slug }}', '{{ addslashes($gallery->title) }}')" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded" title="Hapus">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
        </button>
    @endif
</div>
