@extends('layouts.app')

@section('title', 'Manajemen Pengguna')

@section('content')
<div class="mx-auto"
     x-data="{
         modalOpen: false,
         formMode: 'create',
         formAction: '{{ route('users.store') }}',
         formUserId: '',
         formData: { username: '', role: '', password: '', password_confirmation: '' },
         openCreate() {
             this.formMode = 'create';
             this.formAction = '{{ route('users.store') }}';
             this.formUserId = '';
             this.formData = { username: '', role: '', password: '', password_confirmation: '' };
             this.modalOpen = true;
         },
         openEdit(user) {
             this.formMode = 'edit';
             this.formAction = '{{ url('/users') }}/' + user.id;
             this.formUserId = user.id;
             this.formData = { username: user.username, role: user.role, password: '', password_confirmation: '' };
             this.modalOpen = true;
         },
         closeModal() { this.modalOpen = false; }
     }"
     x-init="
         @if ($errors->any())
             formMode = @js(old('_form_mode', 'create'));
             formUserId = @js(old('_user_id', ''));
             formAction = formMode === 'edit' ? '{{ url('/users') }}/' + formUserId : '{{ route('users.store') }}';
             formData = {
                 username: @js(old('username', '')),
                 role: @js(old('role', '')),
                 password: '',
                 password_confirmation: ''
             };
             modalOpen = true;
         @endif
     "
     @keydown.escape.window="closeModal()">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-4">
        <h1 class="text-2xl font-bold text-gray-800">Manajemen Pengguna</h1>
        <button type="button" @click="openCreate()"
            class="inline-flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded transition duration-150 w-full sm:w-auto">
            Tambah Pengguna
        </button>
    </div>

    {{-- Tab Filter Aktif vs Terhapus --}}
    <div class="mb-5 flex items-center gap-2 border-b border-gray-200">
        <a href="{{ route('users.index') }}"
           class="inline-flex items-center gap-2 border-b-2 px-4 py-2.5 text-sm font-semibold transition {{ !($showTrashed ?? false) ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
            Data Aktif
        </a>
        <a href="{{ route('users.index', ['trashed' => 1]) }}"
           class="inline-flex items-center gap-2 border-b-2 px-4 py-2.5 text-sm font-semibold transition {{ ($showTrashed ?? false) ? 'border-red-600 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
            Terhapus
            @if (($trashedCount ?? 0) > 0)
                <span class="inline-flex items-center justify-center rounded-full px-2 py-0.5 text-xs font-bold {{ ($showTrashed ?? false) ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600' }}">
                    {{ $trashedCount }}
                </span>
            @endif
        </a>
    </div>

    <div class="bg-white shadow rounded-lg overflow-hidden p-4 sm:p-6">
        @if ($users->isEmpty())
            <div class="text-center py-12 text-gray-500">
                <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <p class="text-lg font-medium">{{ ($showTrashed ?? false) ? 'Tidak ada pengguna yang dihapus' : 'Belum ada pengguna' }}</p>
                @if (!($showTrashed ?? false))
                    <p class="text-sm mt-1">Tambahkan pengguna baru untuk memulai.</p>
                @endif
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="datatable min-w-full divide-y divide-gray-200 text-left">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">No</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">Username</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">Peran</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">Terakhir Login</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider no-sort">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($users as $index => $user)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3.5 whitespace-nowrap text-sm text-gray-500">
                                {{ $loop->iteration }}
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $user->username }}
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                @if ($user->role === 'admin')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        Admin
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        Kasir
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                @if ($showTrashed ?? false)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        Dihapus {{ $user->deleted_at?->format('d/m/Y') }}
                                    </span>
                                @elseif ($user->is_active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap text-sm text-gray-500">
                                {{ $user->last_login ? $user->last_login->format('d/m/Y H:i') : '-' }}
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap text-center text-sm font-medium">
                                @if ($showTrashed ?? false)
                                    <form method="POST" action="{{ route('users.restore', $user->id) }}" class="inline">
                                        @csrf @method('PATCH')
                                        <button type="submit"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg transition shadow-xs">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                            </svg>
                                            Pulihkan
                                        </button>
                                    </form>
                                @else
                                    {{-- Desktop Actions --}}
                                <div class="hidden sm:inline-flex items-center space-x-2">
                                    <button type="button"
                                        @click="openEdit({ id: {{ $user->id }}, username: @js($user->username), role: @js($user->role) })"
                                        class="inline-flex items-center px-2.5 py-1.5 bg-yellow-500 hover:bg-yellow-600 text-white text-xs rounded transition duration-150">
                                        Edit
                                    </button>

                                    @if ($user->is_active && $user->id !== auth()->id())
                                        <form method="POST" action="{{ route('users.deactivate', $user) }}"
                                            class="inline"
                                            onsubmit="return confirm('Yakin ingin menonaktifkan pengguna ini?')">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                class="inline-flex items-center px-2.5 py-1.5 bg-orange-500 hover:bg-orange-600 text-white text-xs rounded transition duration-150">
                                                Nonaktifkan
                                            </button>
                                        </form>
                                    @endif

                                    @if ($user->id !== auth()->id())
                                        <form method="POST" action="{{ route('users.destroy', $user) }}"
                                            class="inline"
                                            onsubmit="return confirm('Yakin ingin menghapus pengguna ini? Tindakan ini tidak dapat dibatalkan.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="inline-flex items-center px-2.5 py-1.5 bg-red-500 hover:bg-red-600 text-white text-xs rounded transition duration-150">
                                                Hapus
                                            </button>
                                        </form>
                                    @else
                                        <button disabled
                                            class="inline-flex items-center px-2.5 py-1.5 bg-gray-300 text-gray-500 text-xs rounded cursor-not-allowed">
                                            Hapus
                                        </button>
                                    @endif
                                </div>

                                {{-- Mobile Actions (3-Dots Dropdown) --}}
                                <div class="sm:hidden relative inline-block text-left" x-data="{ open: false }">
                                    <button @click="open = !open" @click.outside="open = false" type="button"
                                            class="p-1.5 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition focus:outline-none">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                                        </svg>
                                    </button>
                                    <div x-show="open"
                                         x-transition:enter="transition ease-out duration-100"
                                         x-transition:enter-start="transform opacity-0 scale-95"
                                         x-transition:enter-end="transform opacity-100 scale-100"
                                         x-transition:leave="transition ease-in duration-75"
                                         x-transition:leave-start="transform opacity-100 scale-100"
                                         x-transition:leave-end="transform opacity-0 scale-95"
                                         class="absolute right-0 z-30 mt-1 w-36 origin-top-right rounded-xl bg-white shadow-lg ring-1 ring-black ring-opacity-5 divide-y divide-gray-100 focus:outline-none text-left"
                                         style="display: none;">
                                        <div class="py-1">
                                            <button type="button"
                                               @click="openEdit({ id: {{ $user->id }}, username: @js($user->username), role: @js($user->role) }); open = false"
                                               class="flex items-center gap-2 px-3 py-2 text-xs text-yellow-600 hover:bg-yellow-50 font-medium">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                                Edit
                                            </button>
                                            @if ($user->is_active && $user->id !== auth()->id())
                                                <form method="POST" action="{{ route('users.deactivate', $user) }}"
                                                      onsubmit="return confirm('Yakin ingin menonaktifkan pengguna ini?')">
                                                    @csrf @method('PATCH')
                                                    <button type="submit"
                                                            class="w-full flex items-center gap-2 px-3 py-2 text-xs text-orange-600 hover:bg-orange-50 font-medium">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                                                        Nonaktifkan
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                        @if ($user->id !== auth()->id())
                                            <div class="py-1">
                                                <form method="POST" action="{{ route('users.destroy', $user) }}"
                                                      onsubmit="return confirm('Yakin ingin menghapus pengguna ini? Tindakan ini tidak dapat dibatalkan.')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit"
                                                            class="w-full flex items-center gap-2 px-3 py-2 text-xs text-red-600 hover:bg-red-50 font-medium">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                        Hapus
                                                    </button>
                                                </form>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        @endif
    </div>

    <div x-show="modalOpen" x-cloak x-transition.opacity
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/50"
         @click.self="closeModal()">
        <div x-show="modalOpen" x-transition
             class="w-full max-w-lg bg-white rounded-2xl shadow-xl overflow-hidden"
             role="dialog" aria-modal="true" aria-labelledby="user-modal-title">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h2 id="user-modal-title" class="text-lg font-bold text-gray-900"
                    x-text="formMode === 'edit' ? 'Edit Pengguna' : 'Tambah Pengguna'"></h2>
                <button type="button" @click="closeModal()" class="text-gray-400 hover:text-gray-600" aria-label="Tutup">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form method="POST" :action="formAction" class="p-6">
                @csrf
                <input type="hidden" name="_form_mode" :value="formMode">
                <input type="hidden" name="_user_id" :value="formUserId">
                <template x-if="formMode === 'edit'">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div class="mb-5">
                    <label for="modal-username" class="block text-sm font-medium text-gray-700 mb-1">
                        Username <span class="text-red-500">*</span>
                    </label>
                    <input id="modal-username" type="text" name="username" x-model="formData.username"
                           class="w-full px-3 py-2 border {{ $errors->has('username') ? 'border-red-300 bg-red-50' : 'border-gray-300' }} rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="Masukkan username">
                    @error('username') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="mb-5">
                    <label for="modal-role" class="block text-sm font-medium text-gray-700 mb-1">
                        Peran <span class="text-red-500">*</span>
                    </label>
                    <select id="modal-role" name="role" x-model="formData.role"
                            class="w-full px-3 py-2 border {{ $errors->has('role') ? 'border-red-300 bg-red-50' : 'border-gray-300' }} rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Pilih Peran --</option>
                        <option value="admin">Admin</option>
                        @foreach ($tenantRoles as $tenantRole)
                            <option value="{{ $tenantRole->slug }}">{{ $tenantRole->name }}</option>
                        @endforeach
                    </select>
                    @error('role') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="mb-5">
                    <label for="modal-password" class="block text-sm font-medium text-gray-700 mb-1">
                        Password <span x-show="formMode === 'create'" class="text-red-500">*</span>
                        <span x-show="formMode === 'edit'" class="text-gray-400 font-normal">(kosongkan jika tidak diubah)</span>
                    </label>
                    <input id="modal-password" type="password" name="password" x-model="formData.password"
                           class="w-full px-3 py-2 border {{ $errors->has('password') ? 'border-red-300 bg-red-50' : 'border-gray-300' }} rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="Minimal 8 karakter">
                    @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="mb-6">
                    <label for="modal-password-confirmation" class="block text-sm font-medium text-gray-700 mb-1">
                        Konfirmasi Password <span x-show="formMode === 'create'" class="text-red-500">*</span>
                    </label>
                    <input id="modal-password-confirmation" type="password" name="password_confirmation"
                           x-model="formData.password_confirmation"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="Ulangi password">
                </div>

                <div class="flex items-center justify-end gap-3">
                    <button type="button" @click="closeModal()"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md"
                            x-text="formMode === 'edit' ? 'Perbarui' : 'Simpan'"></button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
