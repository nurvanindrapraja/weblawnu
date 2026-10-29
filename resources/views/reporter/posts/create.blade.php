@extends('layouts.admin')
@section('title', 'Tulis Berita')
@section('page-title', 'Tulis Berita Baru')
@section('content')
    <a href="{{ route('reporter.posts.index') }}" class="text-sm text-gray-500 hover:text-emerald-600 mb-4 inline-block">← Kembali</a>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-xl shadow-sm p-6">
                <form id="post-form" class="space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Judul <span class="text-red-500">*</span></label>
                        <input type="text" name="title" id="title" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 text-lg font-medium" value="{{ old('title') }}">
                        @error('title')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Ringkasan</label>
                        <textarea name="excerpt" rows="3" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">{{ old('excerpt') }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Isi Berita <span class="text-red-500">*</span></label>
                        <textarea name="content" id="content" rows="15" required class="rich-editor w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 text-sm">{{ old('content') }}</textarea>
                        @error('content')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </form>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-xl shadow-sm p-5">
                <h3 class="font-semibold text-gray-900 mb-4">Publikasi</h3>
                <div class="space-y-3">
                    <button onclick="savePost('draft')" class="w-full px-4 py-2.5 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200 font-medium">Simpan Draft</button>
                    <button onclick="savePost('submitted')" class="w-full px-4 py-2.5 bg-emerald-600 text-white rounded-lg text-sm hover:bg-emerald-700 font-medium">Submit untuk Review</button>
                    <button onclick="previewPost()" class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-lg text-sm hover:border-emerald-500 hover:text-emerald-600 font-medium">👁 Preview Tampilan Publik</button>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-5">
                <h3 class="font-semibold text-gray-900 mb-4">Kategori</h3>
                <select name="category_id" id="category" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="">Pilih Kategori</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-5">
                <h3 class="font-semibold text-gray-900 mb-4">Featured Image</h3>
                <div class="text-center">
                    <div id="featured-preview" class="mb-3 hidden">
                        <img id="featured-img" src="" alt="" class="w-full h-40 object-cover rounded-lg">
                    </div>
                    <input type="file" id="featured-upload" accept="image/*" class="w-full text-sm">
                    <input type="hidden" name="featured_media_id" id="featured-media-id">
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-5">
                <h3 class="font-semibold text-gray-900 mb-4">Tag</h3>
                <div id="tag-combo" data-tags='@json($tags->map(fn($t) => ['id' => $t->id, 'name' => $t->name]))'>
                    <div id="tag-chips" class="flex flex-wrap gap-1.5 mb-2"></div>
                    <div class="relative">
                        <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input type="text" id="tag-search" autocomplete="off" placeholder="Cari & pilih tag..."
                               class="w-full pl-9 pr-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <div id="tag-dropdown" class="absolute left-0 right-0 top-full mt-1 z-30 bg-white border border-gray-200 rounded-lg shadow-lg max-h-52 overflow-y-auto hidden">
                            <div id="tag-options"></div>
                            <div id="tag-empty" class="px-3 py-3 text-sm text-gray-400"></div>
                        </div>
                    </div>
                </div>
                <button type="button" onclick="openTagModal()" class="mt-3 w-full px-3 py-2 border border-dashed border-gray-300 rounded-lg text-sm text-emerald-600 hover:border-emerald-400 hover:bg-emerald-50 font-medium">+ Tambah Tag baru</button>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-5">
                <h3 class="font-semibold text-gray-900 mb-4">SEO</h3>
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Meta Title</label>
                        <input type="text" name="meta_title" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" value="{{ old('meta_title') }}">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Meta Description</label>
                        <textarea name="meta_description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">{{ old('meta_description') }}</textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>
{{-- Tag modal --}}
<div id="tag-backdrop" class="fixed inset-0 z-[10001] bg-black/40 hidden" onclick="closeTagModal()"></div>
<div id="tag-modal" class="fixed inset-0 z-[10002] items-center justify-center p-4 hidden pointer-events-none">
    <div class="relative w-full max-w-sm bg-white rounded-2xl shadow-xl overflow-hidden pointer-events-auto">
        <div class="px-6 pt-6 pb-4">
            <h3 class="text-base font-semibold text-gray-900">Tambah Tag</h3>
            <p class="mt-1 text-sm text-gray-500">Tag baru langsung bisa dipilih untuk berita ini.</p>
            <input type="text" id="tag-name" placeholder="Nama tag..." maxlength="100"
                   class="mt-4 w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
        </div>
        <div class="flex items-center justify-end gap-3 px-6 py-4 bg-gray-50 border-t border-gray-100">
            <button type="button" onclick="closeTagModal()" class="px-4 py-2 rounded-lg text-sm font-medium bg-white border border-gray-300 text-gray-700 hover:bg-gray-100">Batal</button>
            <button type="button" id="tag-save-btn" onclick="saveTag()" class="px-4 py-2 rounded-lg text-sm font-medium bg-emerald-600 text-white hover:bg-emerald-700">Simpan Tag</button>
        </div>
    </div>
</div>

{{-- Preview modal (iframe, stays on this page) --}}
<x-post-preview-modal />
@endsection

@push('scripts')
<script>
// Tag combobox — searchable multi-select (see resources/js/tag-combo.js).
document.addEventListener('DOMContentLoaded', function() {
    const combo = document.getElementById('tag-combo');
    if (combo && window.initTagCombo) window.initTagCombo(combo);
});

// Tag modal — create new tags inline via AJAX (admin.tags.store)
function openTagModal() {
    document.getElementById('tag-backdrop').classList.remove('hidden');
    const modal = document.getElementById('tag-modal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    const input = document.getElementById('tag-name');
    input.value = '';
    setTimeout(() => input.focus(), 50);
}
function closeTagModal() {
    document.getElementById('tag-backdrop').classList.add('hidden');
    const modal = document.getElementById('tag-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}
document.getElementById('tag-name')?.addEventListener('keydown', (e) => {
    if (e.key === 'Enter') { e.preventDefault(); saveTag(); }
    if (e.key === 'Escape') closeTagModal();
});

async function saveTag() {
    const input = document.getElementById('tag-name');
    const name = input.value.trim();
    const btn = document.getElementById('tag-save-btn');
    if (!name) { input.focus(); return; }
    const dup = (document.getElementById('tag-combo').dataset.tags || '[]').trim().startsWith('[')
        ? JSON.parse(document.getElementById('tag-combo').dataset.tags || '[]')
            .some(t => String(t.name).trim().toLowerCase() === name.toLowerCase())
        : false;
    if (dup) { showToast('Tag "' + name + '" sudah ada, pilih dari daftar.', 'error'); return; }
    btn.disabled = true;
    btn.textContent = 'Menyimpan...';
    try {
        const res = await fetch('{{ route('admin.tags.store') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ name })
        });
        const data = await res.json();
        if (data.success) {
            const combo = document.getElementById('tag-combo');
            if (combo && combo._tagCombo) combo._tagCombo.addTag({ id: data.data.id, name: data.data.name });
            closeTagModal();
            showToast('Tag "' + data.data.name + '" ditambahkan.', 'success');
        } else {
            const msg = data.message || (data.errors ? Object.values(data.errors).flat().join(' ') : 'Gagal menambah tag.');
            showToast(msg, 'error');
        }
    } catch (e) {
        console.error('saveTag error', e);
        showToast('Terjadi kesalahan.', 'error');
    } finally {
        btn.disabled = false;
        btn.textContent = 'Simpan Tag';
    }
}

document.getElementById('title')?.addEventListener('input', function() {
    const slug = this.value.toLowerCase().replace(/[^a-z0-9\s-]/g,'').replace(/\s+/g,'-').replace(/-+/g,'-');
    const slugField = document.querySelector('[name="slug"]');
    if (slugField) slugField.value = slug;
});

document.getElementById('featured-upload')?.addEventListener('change', function() {
    const file = this.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('featured-img').src = e.target.result;
            document.getElementById('featured-preview').classList.remove('hidden');
        };
        reader.readAsDataURL(file);
    }
});

// Live preview of a new (unsaved) post: opens the public article look in a
// new tab without saving. Featured image only appears after saving.
// Syncs the TinyMCE editor back into its textarea (works with or without id).
function flushRichEditor() {
    try {
        if (!window.tinymce) return;
        const ta = document.querySelector('textarea[name="content"]');
        const byTarget = ta && window.tinymce.editors
            ? Object.values(window.tinymce.editors).find((e) => e.targetEl === ta)
            : undefined;
        (byTarget || window.tinymce.get('content'))?.save();
    } catch (_) { /* ignore */ }
}

function previewPost() {
    flushRichEditor();

    const title = document.getElementById('title')?.value || '';
    const content = document.querySelector('textarea[name="content"]')?.value || '';
    const category = document.getElementById('category')?.value || '';
    if (!title.trim() || !content.trim() || !category) {
        showToast('Lengkapi judul, isi berita, dan kategori sebelum preview.', 'error');
        return;
    }

    const fields = {
        _token: document.querySelector('meta[name="csrf-token"]').content,
        title,
        slug: document.querySelector('[name="slug"]')?.value || '',
        excerpt: document.querySelector('[name="excerpt"]')?.value || '',
        content,
        category_id: category,
        featured_media_id: document.getElementById('featured-media-id')?.value || '',
        meta_title: document.querySelector('[name="meta_title"]')?.value || '',
        meta_description: document.querySelector('[name="meta_description"]')?.value || '',
    };
    const tagCombo = document.getElementById('tag-combo');
    if (tagCombo && tagCombo._tagCombo) {
        const tags = [];
        tagCombo._tagCombo.eachTag((id) => tags.push(id));
        fields.tags = tags;
    }

    // Render the unsaved preview inside the in-page modal.
    window.openPostPreviewPost('{{ route("reporter.posts.preview-draft") }}', fields);
}

async function savePost(status) {
    const btns = document.querySelectorAll('button[onclick*="savePost"]');
    btns.forEach(b => { b.disabled = true; b.textContent = 'Menyimpan...'; });

    // Sync TinyMCE content back into the source textarea first.
    // TinyMCE disables that textarea while active, so it is NOT part of
    // FormData serialization — we re-append it explicitly below.
    flushRichEditor();

    try {
        let featuredMediaId = '';
        const fileInput = document.getElementById('featured-upload');
        if (fileInput.files[0]) {
            const fd = new FormData();
            fd.append('file', fileInput.files[0]);
            const res = await fetch('{{ route("admin.media.store") }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                body: fd
            });
            const mediaData = await res.json();
            if (mediaData.success) featuredMediaId = mediaData.data.id;
        }

        const formData = new FormData(document.getElementById('post-form'));

        // Defensive: re-set core fields from the DOM. TinyMCE disables
        // the source textarea, so `content` (and friends) must be set
        // explicitly or the server receives it as empty.
        const titleEl = document.getElementById('title');
        if (titleEl) formData.set('title', titleEl.value);
        const contentEl = document.getElementById('content');
        if (contentEl) formData.set('content', contentEl.value || '');
        const excerptEl = document.querySelector('[name="excerpt"]');
        if (excerptEl) formData.set('excerpt', excerptEl.value || '');

        formData.append('status', status);
        formData.append('featured_media_id', featuredMediaId);
        formData.append('category_id', document.getElementById('category').value);

        const tagCombo = document.getElementById('tag-combo');
        if (tagCombo && tagCombo._tagCombo) tagCombo._tagCombo.eachTag((id) => formData.append('tags[]', id));

        document.querySelectorAll('[name="meta_title"], [name="meta_description"]').forEach(el => {
            formData.set(el.name, el.value);
        });

        const res = await fetch('{{ route("reporter.posts.store") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
            body: formData
        });

        const data = await res.json();
        const msg = data.message || (data.errors ? Object.values(data.errors).flat().join(' ') : 'Terjadi kesalahan.');
        showToast(msg, data.success ? 'success' : 'error');
        if (data.success) setTimeout(() => location.href = '{{ route("reporter.posts.index") }}', 1000);
    } catch (e) {
        console.error('savePost error', e);
        showToast('Terjadi kesalahan. Cek console untuk detail.', 'error');
    } finally {
        btns.forEach(b => { b.disabled = false; });
        btns[0].textContent = 'Simpan Draft';
        btns[1].textContent = 'Submit untuk Review';
    }
}
</script>
@endpush
