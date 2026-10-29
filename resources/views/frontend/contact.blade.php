@extends('layouts.app')

@section('title', 'Kontak Kami')
@section('content')
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="sticky top-16 z-40 -mx-4 sm:-mx-6 lg:-mx-8 bg-gray-50 px-4 sm:px-6 lg:px-8 pt-4 pb-3 mb-5">
            <h1 class="text-3xl font-bold text-gray-900">Kontak Kami</h1>
            <p class="text-gray-500 mt-1">
                Kirimkan pertanyaan, saran, kritik, atau informasi Anda melalui form di bawah ini.
                <a href="{{ route('contact.status') }}" class="text-emerald-700 underline hover:text-emerald-800">Sudah pernah mengirim pesan? Cek status dengan nomor tiket Anda.</a>
            </p>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 md:p-8">
            <form id="contact-form" class="space-y-5">
                <!-- Success/Error Messages -->
                <div id="contact-messages"></div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama <span class="text-red-500">*</span></label>
                        <input type="text" id="name" name="name" required
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                               placeholder="Nama lengkap Anda" value="{{ old('name') }}">
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" id="email" name="email"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                               placeholder="email@example.com" value="{{ old('email') }}">
                    </div>
                </div>

                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Nomor HP</label>
                    <input type="tel" id="phone" name="phone"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                           placeholder="08xxxxxxxxxx" value="{{ old('phone') }}">
                    <p class="text-xs text-gray-400 mt-1">Email atau nomor HP minimal salah satunya harus diisi.</p>
                </div>

                <div>
                    <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">Subjek <span class="text-red-500">*</span></label>
                    <input type="text" id="subject" name="subject" required
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                           placeholder="Subjek pesan" value="{{ old('subject') }}">
                </div>

                <div>
                    <label for="message" class="block text-sm font-medium text-gray-700 mb-1">Pesan <span class="text-red-500">*</span></label>
                    <textarea id="message" name="message" rows="5" required
                              class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                              placeholder="Tulis pesan Anda di sini...">{{ old('message') }}</textarea>
                </div>

                <div>
                    <button type="submit" id="contact-submit"
                            class="w-full md:w-auto bg-emerald-600 hover:bg-emerald-700 text-white font-medium py-3 px-8 rounded-lg transition-colors">
                        Kirim Pesan
                    </button>
                </div>
            </form>
        </div>

        <!-- Contact Info -->
        <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white rounded-xl p-6 shadow-sm text-center">
                <div class="text-3xl mb-2">📧</div>
                <h3 class="font-semibold text-gray-900">Email</h3>
                <p class="text-sm text-gray-500 mt-1">{{ \App\Models\Setting::get('site_email', 'admin@lwpnujatim.com') }}</p>
            </div>
            <div class="bg-white rounded-xl p-6 shadow-sm text-center">
                <div class="text-3xl mb-2">📱</div>
                <h3 class="font-semibold text-gray-900">WhatsApp</h3>
                <p class="text-sm text-gray-500 mt-1">{{ \App\Models\Setting::get('whatsapp_number', '') }}</p>
            </div>
            <div class="bg-white rounded-xl p-6 shadow-sm text-center">
                <div class="text-3xl mb-2">📍</div>
                <h3 class="font-semibold text-gray-900">Alamat</h3>
                <p class="text-sm text-gray-500 mt-1">{{ \App\Models\Setting::get('site_address', '') }}</p>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.getElementById('contact-form')?.addEventListener('submit', async function(e) {
    e.preventDefault();

    const btn = document.getElementById('contact-submit');
    const messagesDiv = document.getElementById('contact-messages');
    btn.disabled = true;
    btn.textContent = 'Mengirim...';

    try {
        const formData = new FormData(this);
        const res = await fetch('{{ route("api.frontend.contact.store") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('[name="_token"]')?.value || '',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
        });

        const data = await res.json();

        if (data.success) {
            let successHtml = '<div class="p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg mb-4">' + data.message;
            if (data.ticket_number) {
                successHtml += '<div class="mt-3 bg-white border border-green-200 rounded-lg p-3 space-y-1">'
                    + '<p class="text-sm">Simpan nomor tiket Anda untuk mengecek status pesan:</p>'
                    + '<p class="text-2xl font-mono font-bold tracking-widest text-emerald-700">' + data.ticket_number + '</p>'
                    + '<a href="{{ route("contact.status") }}?ticket=' + encodeURIComponent(data.ticket_number) + '" class="inline-block text-sm underline text-emerald-700">Cek status pesan saya →</a>'
                    + '</div>';
            }
            successHtml += '</div>';
            messagesDiv.innerHTML = successHtml;
            this.reset();
        } else {
            let errorHtml = '<div class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg mb-4">';
            if (data.errors) {
                errorHtml += '<ul class="list-disc pl-4">';
                for (const [field, errors] of Object.entries(data.errors)) {
                    errors.forEach(e => errorHtml += `<li>${e}</li>`);
                }
                errorHtml += '</ul>';
            } else {
                errorHtml += data.message;
            }
            errorHtml += '</div>';
            messagesDiv.innerHTML = errorHtml;
        }
    } catch (err) {
        messagesDiv.innerHTML = '<div class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg mb-4">Terjadi kesalahan. Silakan coba lagi.</div>';
    } finally {
        btn.disabled = false;
        btn.textContent = 'Kirim Pesan';
    }
});
</script>
@endpush
