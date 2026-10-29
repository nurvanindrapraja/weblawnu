@forelse($posts as $post)
<tr class="hover:bg-gray-50">
    <td class="px-4 py-3">
        <a href="{{ route('admin.posts.show', $post) }}" class="text-sm font-medium text-gray-900 hover:text-emerald-600 line-clamp-1">{{ $post->title }}</a>
    </td>
    <td class="px-4 py-3 text-sm text-gray-600">{{ $post->category?->name ?? '-' }}</td>
    <td class="px-4 py-3 text-sm text-gray-600">{{ $post->author?->name ?? '-' }}</td>
    <td class="px-4 py-3">
        <span class="px-2 py-1 text-xs rounded-full capitalize {{
            match($post->status) {
                'published' => 'bg-green-100 text-green-700',
                'draft' => 'bg-gray-100 text-gray-600',
                'submitted' => 'bg-yellow-100 text-yellow-700',
                'review' => 'bg-blue-100 text-blue-700',
                'approved' => 'bg-indigo-100 text-indigo-700',
                'rejected' => 'bg-red-100 text-red-700',
                'archived' => 'bg-gray-200 text-gray-600',
                default => 'bg-gray-100 text-gray-600',
            }
        }}">{{ $post->status }}</span>
    </td>
    <td class="px-4 py-3 text-sm text-gray-500">{{ $post->published_at?->format('d M Y') ?? '-' }}</td>
    <td class="px-4 py-3 text-sm text-gray-500">{{ $post->views }}</td>
    <td class="px-4 py-3 text-right">
        <div class="flex items-center justify-end gap-1">
            <!-- Workflow Actions -->
            @if(in_array($post->status, ['draft', 'rejected']))
            <button onclick="postAction('{{ $post->slug }}', 'submit')" class="p-1.5 text-yellow-600 hover:bg-yellow-50 rounded" title="Submit">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
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

            <button type="button" onclick="openPostPreviewGet('{{ route('admin.posts.preview', $post) }}')" class="p-1.5 text-blue-500 hover:bg-blue-50 rounded" title="Preview tampilan publik">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
            </button>

            <a href="{{ route('admin.posts.edit', $post) }}" class="p-1.5 text-gray-400 hover:text-emerald-600" title="Edit">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            </a>
            <button onclick="deletePost('{{ $post->slug }}', '{{ $post->title }}')" class="p-1.5 text-gray-400 hover:text-red-600" title="Hapus">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </button>
        </div>
    </td>
</tr>
@empty
<tr><td colspan="7" class="px-4 py-8 text-center text-gray-500">Belum ada berita.</td></tr>
@endforelse
