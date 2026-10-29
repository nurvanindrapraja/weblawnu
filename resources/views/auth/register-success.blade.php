@extends('layouts.auth')
@section('title', 'Pendaftaran Diterima')
@section('content')
    <div class="bg-white rounded-2xl shadow-xl p-8 w-full max-w-md text-center">
        <div class="w-16 h-16 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <h1 class="text-2xl font-bold text-gray-900 mb-2">Pendaftaran Diterima</h1>
        <p class="text-sm text-gray-600 mb-6">
            {{ session('success', 'Pendaftaran Anda telah kami terima. Tim admin akan memverifikasi data Anda. Anda akan dapat login setelah akun disetujui.') }}
        </p>
        <a href="{{ route('login') }}" class="inline-block w-full py-3 bg-emerald-600 text-white rounded-xl font-medium hover:bg-emerald-700 transition-colors">
            Kembali ke Login
        </a>
    </div>
@endsection
