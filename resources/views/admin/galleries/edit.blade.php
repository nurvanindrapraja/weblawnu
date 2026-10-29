@extends('layouts.admin')

@php
    $isReporterRoute = request()->routeIs('reporter.galleries.*');
    $updateUrl = $isReporterRoute ? route('reporter.galleries.update', $gallery) : route('admin.galleries.update', $gallery);
    $backUrl = $isReporterRoute ? route('reporter.galleries.index') : route('admin.galleries.index');
@endphp

@section('title', 'Edit Galeri')
@section('page-title', 'Edit Galeri: '.$gallery->title)
@section('content')
    <a href="{{ $backUrl }}" class="text-sm text-gray-500 hover:text-emerald-600 mb-4 inline-block">← Kembali</a>

    @if($gallery->status === 'rejected' && $gallery->rejection_note)
    <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl">
        <p class="text-sm font-semibold text-red-800 mb-1">Galeri ini ditolak oleh redaktur</p>
        <p class="text-sm text-red-700">{{ $gallery->rejection_note }}</p>
    </div>
    @endif

    @if($gallery->status === 'submitted')
    <div class="mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded-xl text-sm text-yellow-800">
        Galeri ini sedang menunggu review redaktur.
    </div>
    @endif

    <div class="max-w-3xl bg-white rounded-xl shadow-sm p-6">
        <form id="gallery-form" class="space-y-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Judul Kegiatan <span class="text-red-500">*</span></label>
                <input type="text" name="title" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 text-lg" value="{{ old('title', $gallery->title) }}">
                @error('title')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Kegiatan</label>
                    <input type="date" name="event_date" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500" value="{{ old('event_date', $gallery->event_date?->format('Y-m-d')) }}">
                    @error('event_date')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Meta Title</label>
                    <input type="text" name="meta_title" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500" value="{{ old('meta_title', $gallery->meta_title) }}">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                <textarea name="description" rows="4" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">{{ old('description', $gallery->description) }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Meta Description</label>
                <textarea name="meta_description" rows="2" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">{{ old('meta_description', $gallery->meta_description) }}</textarea>
            </div>

            <!-- Gambar yang sudah ada -->
            @if($gallery->images->isNotEmpty())
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Foto yang Sudah Ada ({{ $gallery->images->count() }})</label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @foreach($gallery->images as $image)
                    <div class="existing-image bg-gray-50 border border-gray-200 rounded-xl p-3">
                        <img src="{{ $image->url }}" alt="" class="w-full h-32 object-cover rounded-lg">
                        <input type="text" name="existing_captions[{{ $image->id }}]" value="{{ $image->pivot->caption }}"
                               class="mt-2 w-full px-3 py-1.5 border border-gray-300 rounded-lg text-sm" placeholder="Keterangan foto">
                        <label class="mt-2 inline-flex items-center gap-2 text-xs text-red-600 cursor-pointer">
                            <input type="checkbox" name="remove_images[{{ $image->id }}]" value="1" class="existing-remove rounded text-red-600 border-gray-300 focus:ring-red-500">
                            Hapus foto ini
                        </label>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Tambah foto baru -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tambah Foto Baru</label>
                <p class="text-xs text-gray-500 mb-2">Bisa lebih dari satu. Beri keterangan pada setiap foto.</p>
                <label class="flex flex-col items-center justify-center border-2 border-dashed border-gray-300 rounded-xl p-6 cursor-pointer hover:border-emerald-400 hover:bg-emerald-50/30 transition-colors">
                    <svg class="w-8 h-8 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M12 12v9m0-9l4 4m-4-4l-4 4"/></svg>
                    <span class="text-sm text-gray-600">Pilih gambar (JPG/PNG/WEBP/GIF, maks 10 MB per file)</span>
                    <input type="file" id="gallery-images" name="images[]" accept="image/*" multiple class="hidden">
                </label>
                @error('images.*')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                <div id="image-previews" class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4"></div>
            </div>

            <div class="flex gap-3 pt-4">
                <button type="submit" class="px-6 py-2.5 bg-emerald-600 text-white rounded-lg text-sm hover:bg-emerald-700">Simpan</button>
                @if($isReporterRoute && in_array($gallery->status, ['draft', 'rejected'], true))
                <button type="button" onclick="submitGallery()" class="px-6 py-2.5 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700">Submit untuk Review</button>
                @endif
                <a href="{{ $backUrl }}" class="px-6 py-2.5 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200">Batal</a>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
const fileInput = document.getElementById('gallery-images');
const previewWrap = document.getElementById('image-previews');

fileInput.addEventListener('change', () => {
    previewWrap.innerHTML = '';
    [...fileInput.files].forEach((file, i) => {
        const reader = new FileReader();
        reader.onload = (e) => {
            const card = document.createElement('div');
            card.className = 'bg-gray-50 border border-gray-200 rounded-xl p-3';
            card.innerHTML = `
                <img src="${e.target.result}" alt="" class="w-full h-32 object-cover rounded-lg">
                <input type="text" class="image-caption mt-2 w-full px-3 py-1.5 border border-gray-300 rounded-lg text-sm"
                       data-index="${i}" placeholder="Keterangan foto (opsional)">
            `;
            previewWrap.appendChild(card);
        };
        reader.readAsDataURL(file);
    });
});

// Checkbox hapus → sembunyikan preview thumbnail agar jelas
document.querySelectorAll('.existing-remove').forEach((cb) => {
    cb.addEventListener('change', () => {
        cb.closest('.existing-image').querySelector('img').style.opacity = cb.checked ? '0.3' : '1';
    });
});

document.getElementById('gallery-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const form = e.currentTarget;
    const btn = form.querySelector('button[type="submit"]');
    btn.disabled = true;
    btn.textContent = 'Menyimpan...';

    const MAX_MB = 10;
    for (const f of fileInput.files) {
        if (f.size > MAX_MB * 1024 * 1024) {
            showToast(`Foto "${f.name}" melebihi batas ${MAX_MB} MB.`, 'error');
            return;
        }
    }

    const fd = new FormData(form); // otomatis: field, existing_captions[], remove_images[], images[]
    previewWrap.querySelectorAll('.image-caption').forEach((input) => {
        fd.append(`image_captions[${input.dataset.index}]`, input.value);
    });

    try {
        const res = await fetch(@json($updateUrl), {
            method: 'PUT',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
            body: fd
        });
        const d = await res.json();
        if (res.status === 422) {
            showToast(d.message || Object.values(d.errors || {}).flat().join(', ') || 'Data tidak valid.', 'error');
            return;
        }
        showToast(d.message, d.success ? 'success' : 'error');
        if (d.success) setTimeout(() => location.reload(), 1000);
    } catch (err) {
        showToast('Terjadi kesalahan saat menyimpan.', 'error');
    } finally {
        btn.disabled = false;
        btn.textContent = 'Simpan';
    }
});

@if($isReporterRoute && in_array($gallery->status, ['draft', 'rejected'], true))
async function submitGallery() {
    const ok = await confirmModal(`Submit galeri "{{ addslashes($gallery->title) }}" untuk review redaktur?`);
    if (!ok) return;
    try {
        const res = await fetch(@json(route('reporter.galleries.submit', $gallery)), {
            method: 'PATCH',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' }
        });
        const d = await res.json();
        showToast(d.message, d.success ? 'success' : 'error');
        if (d.success) setTimeout(() => location.href = @json(route('reporter.galleries.index')), 1000);
    } catch (err) {
        showToast('Terjadi kesalahan.', 'error');
    }
}
@endif
</script>
@endpush
