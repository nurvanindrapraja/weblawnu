@extends('layouts.app')

@section('title', 'Cek Status Pesan')
@section('content')
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="sticky top-16 z-40 -mx-4 sm:-mx-6 lg:-mx-8 bg-gray-50 px-4 sm:px-6 lg:px-8 pt-4 pb-3 mb-5">
            <h1 class="text-3xl font-bold text-gray-900">Cek Status Pesan</h1>
            <p class="text-gray-500 mt-1">
                Masukkan nomor tiket yang Anda terima setelah mengirim pesan untuk melihat status dan balasan admin.
            </p>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 md:p-8">
            <form method="GET" action="{{ route('contact.status') }}" class="flex flex-col sm:flex-row gap-3">
                <div class="flex-1">
                    <label for="ticket" class="block text-sm font-medium text-gray-700 mb-1">Nomor Tiket</label>
                    <input type="text" id="ticket" name="ticket" value="{{ $ticket }}"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg font-mono tracking-widest uppercase focus:outline-none focus:ring-2 focus:ring-emerald-500"
                           placeholder="Contoh: TKT-8FK2QZ" autocomplete="off">
                </div>
                <div class="flex items-end">
                    <button type="submit"
                            class="w-full sm:w-auto bg-emerald-600 hover:bg-emerald-700 text-white font-medium py-2.5 px-6 rounded-lg transition-colors">
                        Cek Status
                    </button>
                </div>
            </form>
        </div>

        @if($ticket !== '')
            @if($message)
                <div class="mt-6 bg-white rounded-xl shadow-sm p-6 md:p-8 space-y-5">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <p class="text-sm text-gray-500">Nomor Tiket</p>
                            <p class="text-xl font-mono font-bold tracking-widest text-emerald-700">{{ $message->ticket_number }}</p>
                        </div>
                        <span class="px-3 py-1.5 text-sm rounded-full {{
                            match($message->status) {
                                'new' => 'bg-blue-100 text-blue-700',
                                'read' => 'bg-gray-100 text-gray-600',
                                'in_progress' => 'bg-yellow-100 text-yellow-700',
                                'resolved' => 'bg-green-100 text-green-700',
                                'archived' => 'bg-gray-200 text-gray-600',
                                default => 'bg-gray-100 text-gray-600',
                            }
                        }}">
                            {{
                                match($message->status) {
                                    'new' => 'Baru Diterima',
                                    'read' => 'Sudah Dibaca',
                                    'in_progress' => 'Sedang Diproses',
                                    'resolved' => 'Selesai',
                                    'archived' => 'Diarsipkan',
                                    default => ucfirst($message->status),
                                }
                            }}
                        </span>
                    </div>

                    <div class="border-t border-gray-100 pt-4">
                        <p class="text-sm text-gray-500">Subjek</p>
                        <p class="text-gray-900">{{ $message->subject }}</p>
                        <p class="text-xs text-gray-400 mt-1">Dikirim {{ $message->created_at->format('d M Y H:i') }}</p>
                    </div>

                    <div class="border-t border-gray-100 pt-4">
                        <p class="text-sm font-medium text-gray-700 mb-2">Balasan Admin</p>
                        @if($message->reply)
                            <div class="bg-emerald-50 border border-emerald-100 rounded-lg p-4">
                                <p class="text-gray-700 whitespace-pre-wrap">{{ $message->reply }}</p>
                                <p class="text-xs text-gray-400 mt-2">Dibalas {{ $message->replied_at->format('d M Y H:i') }}</p>
                            </div>
                        @else
                            <p class="text-sm text-gray-500">Belum ada balasan. Tim kami akan segera menindaklanjuti pesan Anda — silakan cek kembali halaman ini nanti.</p>
                        @endif
                    </div>
                </div>
            @else
                <div class="mt-6 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg">
                    <p class="font-medium">Tiket tidak ditemukan.</p>
                    <p class="text-sm mt-1">Periksa kembali nomor tiket Anda (format: TKT-XXXXXX), lalu coba lagi. Jika masih bermasalah, silakan <a href="{{ route('contact') }}" class="underline">hubungi kami kembali</a>.</p>
                </div>
            @endif
        @endif
    </div>
@endsection
