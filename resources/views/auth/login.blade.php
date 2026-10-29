@extends('layouts.auth')
@section('title', 'Login')
@section('content')
    <div class="bg-white rounded-2xl shadow-xl p-8 w-full max-w-md">
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-emerald-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Login LWP NU Jatim</h1>
            <p class="text-sm text-gray-500 mt-1">Masuk ke panel manajemen</p>
        </div>

        @if($errors->any())
        <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
            {{ $errors->first() }}
        </div>
        @endif

        <form method="POST" action="{{ route('login.post') }}" class="space-y-5">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email / Username</label>
                <input type="text" name="login" required autofocus class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500" placeholder="admin atau email@lwpnujatim.or.id" value="{{ old('login') }}">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input type="password" name="password" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500" placeholder="••••••••">
            </div>
            <div class="flex items-center justify-between">
                <label class="flex items-center gap-2 text-sm text-gray-600">
                    <input type="checkbox" name="remember" class="rounded text-emerald-600 focus:ring-emerald-500">
                    Ingat saya
                </label>
            </div>
            <button type="submit" class="w-full py-3 bg-emerald-600 text-white rounded-xl font-medium hover:bg-emerald-700 transition-colors">
                Masuk
            </button>
        </form>
        <p class="text-center text-sm text-gray-500 mt-6">
            Belum punya akun? <a href="{{ route('register') }}" class="text-emerald-700 underline hover:text-emerald-800">Daftar sebagai reporter / redaktur</a>
        </p>
    </div>
@endsection
