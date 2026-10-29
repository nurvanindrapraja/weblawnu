@extends('layouts.admin')

@section('title', 'Tambah User')
@section('page-title', 'Tambah Pengguna')
@section('content')
    <div class="max-w-2xl">
        <a href="{{ route('admin.users.index') }}" class="text-sm text-gray-500 hover:text-emerald-600 mb-4 inline-block">← Kembali</a>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <form id="user-form" class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500" value="{{ old('name') }}">
                    @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Username <span class="text-red-500">*</span></label>
                        <input type="text" name="username" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500" value="{{ old('username') }}">
                        @error('username')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                        <input type="email" name="email" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500" value="{{ old('email') }}">
                        @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nomor HP</label>
                        <input type="tel" name="phone" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500" value="{{ old('phone') }}">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Role <span class="text-red-500">*</span></label>
                        <select name="role_id" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <option value="">Pilih Role</option>
                            @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>{{ $role->display_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Password <span class="text-red-500">*</span></label>
                        <input type="password" name="password" required minlength="8" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        @error('password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password <span class="text-red-500">*</span></label>
                        <input type="password" name="password_confirmation" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
                    <select name="status" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="submit" class="px-6 py-2.5 bg-emerald-600 text-white rounded-lg text-sm hover:bg-emerald-700">Simpan</button>
                    <a href="{{ route('admin.users.index') }}" class="px-6 py-2.5 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection
