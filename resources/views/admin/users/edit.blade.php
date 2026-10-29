@extends('layouts.admin')

@section('title', 'Edit User')
@section('page-title', 'Edit Pengguna')
@section('content')
    <div class="max-w-2xl">
        <a href="{{ route('admin.users.index') }}" class="text-sm text-gray-500 hover:text-emerald-600 mb-4 inline-block">← Kembali</a>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500" value="{{ old('name', $user->name) }}">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Username <span class="text-red-500">*</span></label>
                        <input type="text" name="username" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500" value="{{ old('username', $user->username) }}">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                        <input type="email" name="email" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500" value="{{ old('email', $user->email) }}">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nomor HP</label>
                        <input type="tel" name="phone" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500" value="{{ old('phone', $user->phone) }}">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Role <span class="text-red-500">*</span></label>
                        <select name="role_id" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            @foreach($roles as $role)
                            <option value="{{ $role->id }}" @selected(old('role_id', $user->roles->first()?->id) == $role->id)>{{ $role->display_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
                    <select name="status" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="active" {{ old('status', $user->status) === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $user->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <hr class="my-4">

                <h3 class="text-sm font-semibold text-gray-700">Reset Password (opsional)</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Password Baru</label>
                        <input type="password" name="password" minlength="8" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500" placeholder="Kosongkan jika tidak direset">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password</label>
                        <input type="password" name="password_confirmation" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="submit" class="px-6 py-2.5 bg-emerald-600 text-white rounded-lg text-sm hover:bg-emerald-700">Simpan</button>
                    <a href="{{ route('admin.users.index') }}" class="px-6 py-2.5 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection
