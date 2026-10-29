@extends('layouts.admin')

@section('title', 'Pengguna')
@section('page-title', 'Pengguna')
@section('content')
    <div class="flex items-center justify-between mb-6">
        <div class="flex flex-wrap items-center gap-2" role="search">
            <div class="relative">
                <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" id="user-search" placeholder="Cari pengguna..." value="{{ request('search') }}"
                       class="pl-9 pr-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500" autocomplete="off">
            </div>
            <div class="relative">
                <select id="user-status-filter"
                        class="appearance-none pl-3 pr-9 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 cursor-pointer bg-white">
                    <option value="">Semua Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
                <svg class="w-4 h-4 text-gray-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </div>
        </div>
        <button type="button" onclick="openUserModal()" class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm hover:bg-emerald-700">
            + Tambah User
        </button>
    </div>

    <!-- Modal Tambah User -->
    <div id="user-modal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black bg-opacity-50" onclick="closeUserModal()"></div>
        <div class="relative flex min-h-full items-center justify-center p-4">
            <div class="relative bg-white rounded-xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 sticky top-0 bg-white rounded-t-xl">
                    <h2 class="text-lg font-semibold text-gray-900">Tambah Pengguna</h2>
                    <button type="button" onclick="closeUserModal()" class="p-1 text-gray-400 hover:text-gray-600 rounded" title="Tutup">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form id="user-modal-form" onsubmit="return saveUser(event)" class="px-6 py-5 space-y-4">
                    <div id="user-modal-errors" class="hidden mb-1 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
                        <ul class="list-disc pl-4 space-y-1"></ul>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input type="text" name="name" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500" value="{{ old('name') }}">
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Username <span class="text-red-500">*</span></label>
                            <input type="text" name="username" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500" value="{{ old('username') }}">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                            <input type="email" name="email" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500" value="{{ old('email') }}">
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
                            <input type="password" name="password" required minlength="8" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500" placeholder="Min. 8 karakter">
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
                    <div class="flex justify-end gap-3 pt-3 border-t border-gray-100">
                        <button type="button" onclick="closeUserModal()" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200">Batal</button>
                        <button type="submit" class="px-5 py-2.5 bg-emerald-600 text-white rounded-lg text-sm hover:bg-emerald-700">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Username</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Last Login</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody id="users-rows" class="divide-y divide-gray-100">
                    @include('admin.users.partials.rows')
                </tbody>
            </table>
        </div>
        <div id="users-pagination" class="p-4">{!! $users->links() !!}</div>
    </div>
@endsection

@push('scripts')
<script>
// Live AJAX filtering: search text (debounced) + dropdown apply instantly,
// no Filter button needed. A top loading bar shows while the request runs.
(function() {
    const searchInput = document.getElementById('user-search');
    const statusSelect = document.getElementById('user-status-filter');
    const rowsEl = document.getElementById('users-rows');
    const paginationEl = document.getElementById('users-pagination');
    const filterUrl = '{{ route("admin.users.filter") }}';

    let debounceTimer = null;
    let currentPage = {{ $users->currentPage() }};
    let seq = 0;

    function buildParams() {
        const p = new URLSearchParams();
        const s = searchInput.value.trim();
        if (s) p.set('search', s);
        if (statusSelect.value) p.set('status', statusSelect.value);
        if (currentPage > 1) p.set('page', currentPage);
        return p;
    }

    function syncUrl(p) {
        const qs = p.toString();
        history.replaceState(null, '', qs ? '?' + qs : location.pathname);
    }

    async function loadUsers(resetPage) {
        if (resetPage) currentPage = 1;
        const mySeq = ++seq;
        const p = buildParams();
        if (window.startLoading) window.startLoading();
        try {
            const res = await fetch(filterUrl + '?' + p.toString(), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            });
            if (!res.ok) throw new Error('HTTP ' + res.status);
            const data = await res.json();
            if (mySeq !== seq) return; // superseded by a newer request
            rowsEl.innerHTML = data.rows;
            paginationEl.innerHTML = data.pagination || '';
            currentPage = data.page || 1;
            syncUrl(p);
        } catch (err) {
            if (mySeq !== seq) return;
            console.error('Filter pengguna gagal:', err);
            showToast('Gagal memuat daftar pengguna.', 'error');
        } finally {
            if (mySeq === seq && window.stopLoading) window.stopLoading();
        }
    }

    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function() { loadUsers(true); }, 350);
    });
    statusSelect.addEventListener('change', function() { loadUsers(true); });

    // Pagination links are intercepted and loaded via AJAX as well.
    paginationEl.addEventListener('click', function(e) {
        const link = e.target.closest('a');
        if (!link || !paginationEl.contains(link)) return;
        e.preventDefault();
        const page = parseInt(new URL(link.href, location.origin).searchParams.get('page') || '1', 10);
        currentPage = isNaN(page) ? 1 : page;
        loadUsers(false);
    });
})();

function toggleStatus(id, currentStatus, el) {
    fetch(`{{ url('/admin/users') }}/${id}/status`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    }).then(r => r.json()).then(data => {
        if (data.success) {
            const newStatus = data.data.status;
            el.className = `px-2 py-1 text-xs rounded-full ${newStatus === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'}`;
            el.textContent = newStatus;
            showToast(data.message, 'success');
        }
    }).catch(() => showToast('Gagal update status.', 'error'));
}

async function deleteUser(id, name) {
    const ok = await confirmModal(`Apakah Anda yakin ingin menghapus user "${name}"? Tindakan ini tidak dapat dibatalkan.`, {
        title: 'Hapus User',
        okLabel: 'Hapus',
        okClass: 'bg-red-600 hover:bg-red-700'
    });
    if (!ok) return;
    fetch(`{{ url('/admin/users') }}/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    }).then(r => r.json()).then(data => {
        showToast(data.message, data.success ? 'success' : 'error');
        if (data.success) setTimeout(() => location.reload(), 1000);
    }).catch(() => showToast('Gagal menghapus user.', 'error'));
}

// ===== Tambah User (modal) =====
function openUserModal() {
    document.getElementById('user-modal').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
    const first = document.querySelector('#user-modal-form input[name="name"]');
    if (first) first.focus();
}

function closeUserModal() {
    document.getElementById('user-modal').classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
    hideUserModalErrors();
    document.getElementById('user-modal-form').reset();
}

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && !document.getElementById('user-modal').classList.contains('hidden')) {
        closeUserModal();
    }
});

function showUserModalErrors(errors) {
    const box = document.getElementById('user-modal-errors');
    const ul = box.querySelector('ul');
    ul.innerHTML = '';
    Object.values(errors || {}).forEach((msg) => {
        const li = document.createElement('li');
        li.textContent = msg;
        ul.appendChild(li);
    });
    box.classList.remove('hidden');
}

function hideUserModalErrors() {
    const box = document.getElementById('user-modal-errors');
    box.classList.add('hidden');
    box.querySelector('ul').innerHTML = '';
}

async function saveUser(event) {
    event.preventDefault();
    const form = event.currentTarget;
    const btn = form.querySelector('button[type="submit"]');
    btn.disabled = true;
    btn.textContent = 'Menyimpan...';
    try {
        const res = await fetch('{{ route("admin.users.store") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: new FormData(form)
        });
        const data = await res.json();
        if (res.status === 422) {
            showUserModalErrors(data.errors || {});
            return;
        }
        showToast(data.message, data.success ? 'success' : 'error');
        if (data.success) setTimeout(() => location.reload(), 800);
    } catch (e) {
        showToast('Gagal menambah user.', 'error');
    } finally {
        btn.disabled = false;
        btn.textContent = 'Simpan';
    }
}
</script>
@endpush
