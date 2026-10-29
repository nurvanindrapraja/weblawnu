@extends('layouts.admin')

@php
    $storeUrl = request()->routeIs('reporter.galleries.*') ? route('reporter.galleries.store') : route('admin.galleries.store');
    $backUrl = request()->routeIs('reporter.galleries.*') ? route('reporter.galleries.index') : route('admin.galleries.index');
@endphp

@section('title', 'Tambah Galeri')
@section('page-title', 'Tambah Galeri Kegiatan')
@section('content')
    <a href="{{ $backUrl }}" class="text-sm text-gray-500 hover:text-emerald-600 mb-4 inline-block">← Kembali</a>

    <div class="max-w-3xl bg-white rounded-xl shadow-sm p-6">
        <form id="gallery-form" class="space-y-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Judul Kegiatan <span class="text-red-500">*</span></label>
                <input type="text" name="title" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 text-lg" value="{{ old('title') }}" placeholder="mis. Bakti Sosial Penanaman Pohon 2026">
                @error('title')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Kegiatan</label>
                    <input type="date" name="event_date" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500" value="{{ old('event_date') }}">
                    @error('event_date')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Meta Title</label>
                    <input type="text" name="meta_title" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500" value="{{ old('meta_title') }}">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                <textarea name="description" rows="4" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500" placeholder="Penjelasan singkat tentang kegiatan...">{{ old('description') }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Meta Description</label>
                <textarea name="meta_description" rows="2" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">{{ old('meta_description') }}</textarea>
            </div>

            <!-- Upload gambar (lebih dari satu) + keterangan per gambar -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Foto Kegiatan <span class="text-red-500">*</span></label>
                <p class="text-xs text-gray-500 mb-2">Bisa lebih dari satu. Beri keterangan pada setiap foto.</p>
                <label class="flex flex-col items-center justify-center border-2 border-dashed border-gray-300 rounded-xl p-6 cursor-pointer hover:border-emerald-400 hover:bg-emerald-50/30 transition-colors">
                    <svg class="w-8 h-8 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M12 12v9m0-9l4 4m-4-4l-4 4"/></svg>
                    <span class="text-sm text-gray-600">Pilih gambar (JPG/PNG/WEBP/GIF, maks 10 MB per file)</span>
                    <input type="file" id="gallery-images" name="images[]" accept="image/*" multiple class="hidden">
                </label>
                @error('images')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                @error('images.*')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                <div id="image-previews" class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4"></div>
            </div>

            <div class="flex gap-3 pt-4">
                <button type="submit" class="px-6 py-2.5 bg-emerald-600 text-white rounded-lg text-sm hover:bg-emerald-700">Simpan</button>
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

    const fd = new FormData(form); // otomatis mencakup file images[]
    // Keterangan per gambar (indeks sesuai urutan file)
    previewWrap.querySelectorAll('.image-caption').forEach((input) => {
        fd.append(`image_captions[${input.dataset.index}]`, input.value);
    });

    try {
        const res = await fetch(@json($storeUrl), {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
            body: fd
        });
        const d = await res.json();
        if (res.status === 422) {
            showToast(d.message || Object.values(d.errors || {}).flat().join(', ') || 'Data tidak valid.', 'error');
            return;
        }
        showToast(d.message, d.success ? 'success' : 'error');
        if (d.success) setTimeout(() => location.href = @json($backUrl), 1000);
    } catch (err) {
        showToast('Terjadi kesalahan saat menyimpan.', 'error');
    } finally {
        btn.disabled = false;
        btn.textContent = 'Simpan';
    }
});
</script>
@endpush
