@extends('layouts.admin')
@section('title', 'Tambah Halaman')
@section('page-title', 'Tambah Halaman')
@section('content')
    <a href="{{ route('admin.pages.index') }}" class="text-sm text-gray-500 hover:text-emerald-600 mb-4 inline-block">← Kembali</a>
    <div class="max-w-3xl bg-white rounded-xl shadow-sm p-6">
        <form id="page-form" class="space-y-5">
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Judul <span class="text-red-500">*</span></label><input type="text" name="title" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 text-lg" value="{{ old('title') }}"></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Ringkasan</label><textarea name="excerpt" rows="2" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">{{ old('excerpt') }}</textarea></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Konten <span class="text-red-500">*</span></label><textarea id="content" name="content" rows="15" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 rich-editor" placeholder="Tulis konten halaman di sini...">{{ old('content') }}</textarea></div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Status</label><select name="status" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg"><option value="draft" selected>Draft</option><option value="published">Published</option></select></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Meta Title</label><input type="text" name="meta_title" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg" value="{{ old('meta_title') }}"></div>
            </div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Meta Description</label><textarea name="meta_description" rows="2" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg">{{ old('meta_description') }}</textarea></div>
            <div class="flex gap-3 pt-4"><button type="button" onclick="savePage()" class="px-6 py-2.5 bg-emerald-600 text-white rounded-lg text-sm hover:bg-emerald-700">Simpan</button><a href="{{ route('admin.pages.index') }}" class="px-6 py-2.5 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200">Batal</a></div>
        </form>
    </div>
@endsection
@push('scripts')
<script>
async function savePage() {
    const btn = document.querySelector('button[onclick*="savePage"]');
    btn.disabled=true;btn.textContent='Menyimpan...';
    try {
        const fd = new FormData(document.getElementById('page-form'));
        // TinyMCE mematikan textarea aslinya, jadi isi editor harus di-set eksplisit.
        if (window.tinymce && window.tinymce.get('content')) {
            fd.set('content', window.tinymce.get('content').getContent());
        }
        const res = await fetch('{{ route("admin.pages.store") }}',{method:'POST',headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,'Accept':'application/json'},body:fd});
        const d=await res.json();showToast(d.message,d.success?'success':'error');if(d.success)setTimeout(()=>location.href='{{ route("admin.pages.index") }}',1000);
    }catch(e){showToast('Error','error')}finally{btn.disabled=false;btn.textContent='Simpan'}
}
</script>
@endpush
