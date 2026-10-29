@extends('layouts.admin')

@section('title', 'Edit Profil')
@section('page-title', 'Edit Profil')
@section('content')
    <div class="max-w-2xl">
        <a href="{{ url()->previous() === route('profile.edit', false) ? '#' : url()->previous() }}" class="text-sm text-gray-500 hover:text-emerald-600 mb-4 inline-block">← Kembali</a>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <form method="POST" action="{{ route('profile.update') }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required value="{{ old('name', $user->name) }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Username <span class="text-red-500">*</span></label>
                        <input type="text" name="username" required value="{{ old('username', $user->username) }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        @error('username')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                        <input type="email" name="email" required value="{{ old('email', $user->email) }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nomor HP</label>
                    <input type="tel" name="phone" value="{{ old('phone', $user->phone) }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    @error('phone')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <hr class="my-4">

                <h3 class="text-sm font-semibold text-gray-700">Reset Password (opsional)</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Password Baru</label>
                        <input type="password" name="password" minlength="8" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500" placeholder="Kosongkan jika tidak direset">
                        @error('password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password</label>
                        <input type="password" name="password_confirmation" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="submit" class="px-6 py-2.5 bg-emerald-600 text-white rounded-lg text-sm hover:bg-emerald-700">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
@endsection
