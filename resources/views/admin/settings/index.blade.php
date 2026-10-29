@extends('layouts.admin')
@section('title', 'Pengaturan')
@section('page-title', 'Pengaturan Website')
@section('content')
    <form id="settings-form" class="space-y-6 max-w-3xl">
        @foreach($settings as $group => $items)
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-gray-900 mb-4">{{ ucfirst(str_replace('_',' ',$group)) }}</h3>
            <div class="space-y-4">
                @foreach($items as $setting)
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ ucfirst(str_replace('_',' ',$setting->key)) }}</label>
                    @if($setting->type === 'textarea')
                    <textarea name="{{ $setting->key }}" rows="3" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">{{ $setting->value }}</textarea>
                    @elseif($setting->type === 'number')
                    <input type="number" name="{{ $setting->key }}" value="{{ $setting->value }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    @else
                    <input type="text" name="{{ $setting->key }}" value="{{ $setting->value }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endforeach

        <div class="flex gap-3">
            <button type="submit" class="px-6 py-2.5 bg-emerald-600 text-white rounded-lg text-sm hover:bg-emerald-700">Simpan Pengaturan</button>
        </div>
    </form>
@endsection
@push('scripts')
<script>
document.getElementById('settings-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = this.querySelector('button[type="submit"]');
    btn.disabled = true; btn.textContent = 'Menyimpan...';
    try {
        const fd = new FormData(this);
        const data = {};
        fd.forEach((v,k) => data[k] = v);
        const res = await fetch('{{ route("admin.settings.update") }}', {
            method: 'PUT',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json', 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const d = await res.json();
        showToast(d.message, d.success ? 'success' : 'error');
    } catch(e) { showToast('Error','error'); }
    finally { btn.disabled = false; btn.textContent = 'Simpan Pengaturan'; }
});
</script>
@endpush
