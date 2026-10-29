{{--
    Post preview modal: renders the public article look inside an iframe so
    editors can preview on the same page (no tab switch, no navigation).
    Open via window.openPostPreviewGet(url) or window.openPostPreviewPost(action, fields)
    — see resources/js/post-preview.js.
--}}
<div id="post-preview-backdrop" class="fixed inset-0 z-[11001] bg-black/50 hidden" onclick="closePostPreview()"></div>
<div id="post-preview-modal" class="fixed inset-0 z-[11002] hidden items-center justify-center p-4 sm:p-6">
    <div class="relative flex flex-col w-full max-w-5xl h-[85vh] bg-white rounded-2xl shadow-xl overflow-hidden">
        <div class="flex items-center justify-between gap-3 px-4 sm:px-5 py-3 border-b border-gray-200 bg-white">
            <div class="flex items-center gap-2 min-w-0">
                <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                <h3 class="text-sm font-semibold text-gray-900 truncate">Preview Tampilan Publik</h3>
            </div>
            <div class="flex items-center gap-2 flex-shrink-0">
                <a id="post-preview-open-tab" href="#" target="_blank" rel="noopener" class="text-xs text-gray-500 hover:text-emerald-600 hidden">Buka di tab baru ↗</a>
                <button type="button" onclick="closePostPreview()" class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg" title="Tutup (Esc)">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>
        <div class="relative flex-1 bg-gray-100">
            <div id="post-preview-loading" class="absolute inset-0 z-10 flex items-center justify-center gap-3 text-sm text-gray-500 bg-gray-100">
                <span class="inline-block w-5 h-5 border-2 border-gray-300 border-t-emerald-600 rounded-full animate-spin"></span>
                Memuat preview...
            </div>
            <iframe id="post-preview-frame" title="Preview tampilan publik" class="w-full h-full border-0 bg-white" onload="postPreviewFrameLoaded()"></iframe>
        </div>
        <form id="post-preview-form" method="POST" target="post-preview-frame" class="hidden"></form>
    </div>
</div>
