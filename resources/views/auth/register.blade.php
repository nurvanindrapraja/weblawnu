@extends('layouts.auth')
@section('title', 'Daftar')
@section('content')
    <div class="bg-white rounded-2xl shadow-xl p-8 w-full max-w-md">
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-emerald-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Daftar Akun</h1>
            <p class="text-sm text-gray-500 mt-1">Pendaftaran reporter / redaktur — diverifikasi oleh admin</p>
        </div>

        @if($errors->any())
        <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
            <ul class="list-disc pl-4 space-y-1">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('register.post') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                <input type="text" name="name" required autofocus value="{{ old('name') }}"
                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500" placeholder="Nama lengkap Anda">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" required value="{{ old('email') }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500" placeholder="email@example.com">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">No. HP</label>
                    <input type="tel" name="phone" value="{{ old('phone') }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500" placeholder="08xxxxxxxxxx">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                <input type="text" name="username" required value="{{ old('username') }}"
                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500" placeholder="tanpa spasi, mis. budi_santoso">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Daftar Sebagai</label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="flex items-center gap-2 p-3 border rounded-xl cursor-pointer {{ old('role', 'reporter')==='reporter' ? 'border-emerald-500 bg-emerald-50' : 'border-gray-300' }}">
                        <input type="radio" name="role" value="reporter" class="text-emerald-600 focus:ring-emerald-500" @checked(old('role', 'reporter')==='reporter')>
                        <span class="text-sm font-medium text-gray-800">Reporter</span>
                    </label>
                    <label class="flex items-center gap-2 p-3 border rounded-xl cursor-pointer {{ old('role')==='redaktur' ? 'border-emerald-500 bg-emerald-50' : 'border-gray-300' }}">
                        <input type="radio" name="role" value="redaktur" class="text-emerald-600 focus:ring-emerald-500" @checked(old('role')==='redaktur')>
                        <span class="text-sm font-medium text-gray-800">Redaktur</span>
                    </label>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Motivasi / Keterangan <span class="text-gray-400">(opsional)</span></label>
                <textarea name="reason" rows="3"
                          class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500" placeholder="Ceritakan singkat latar belakang Anda...">{{ old('reason') }}</textarea>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input type="password" name="password" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500" placeholder="Min. 8 karakter">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi</label>
                    <input type="password" name="password_confirmation" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500" placeholder="Ulangi password">
                </div>
            </div>
            <button type="submit" class="w-full py-3 bg-emerald-600 text-white rounded-xl font-medium hover:bg-emerald-700 transition-colors">
                Kirim Pendaftaran
            </button>
            <p class="text-center text-sm text-gray-500">
                Sudah punya akun? <a href="{{ route('login') }}" class="text-emerald-700 underline hover:text-emerald-800">Login</a>
            </p>
        </form>
    </div>
@endsection
