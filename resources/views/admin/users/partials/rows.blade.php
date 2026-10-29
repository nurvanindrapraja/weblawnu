@forelse($users as $user)
<tr class="hover:bg-gray-50">
    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $user->name }}</td>
    <td class="px-4 py-3 text-sm text-gray-600">{{ $user->username }}</td>
    <td class="px-4 py-3 text-sm text-gray-600">{{ $user->email }}</td>
    <td class="px-4 py-3 text-sm">
        @foreach($user->roles as $role)
        <span class="px-2 py-1 text-xs rounded-full {{ $role->name === 'admin' ? 'bg-purple-100 text-purple-700' : ($role->name === 'redaktur' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600') }}">
            {{ $role->display_name }}
        </span>
        @endforeach
    </td>
    <td class="px-4 py-3">
        <button onclick="toggleStatus({{ $user->id }}, '{{ $user->status }}', this)"
                class="px-2 py-1 text-xs rounded-full cursor-pointer {{ $user->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
            {{ $user->status }}
        </button>
    </td>
    <td class="px-4 py-3 text-sm text-gray-500">{{ $user->last_login_at?->format('d M Y H:i') ?? '-' }}</td>
    <td class="px-4 py-3 text-right">
        <div class="flex items-center justify-end gap-2">
            <a href="{{ route('admin.users.edit', $user) }}" class="p-2 text-gray-400 hover:text-emerald-600" title="Edit">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            </a>
            <button onclick="deleteUser({{ $user->id }}, '{{ $user->name }}')"
                    class="p-2 text-gray-400 hover:text-red-600" title="Hapus">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </button>
        </div>
    </td>
</tr>
@empty
<tr><td colspan="7" class="px-4 py-8 text-center text-gray-500">Belum ada pengguna.</td></tr>
@endforelse
