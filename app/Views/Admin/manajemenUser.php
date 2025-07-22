<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>

<div class="bg-white rounded-lg shadow-sm p-6 mb-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <h1 class="text-2xl font-semibold text-gray-800">Manajemen User</h1>
        </div>
        <div class="flex items-center space-x-4">
            <img src="<?= base_url('images/logo.png') ?>" alt="Logo USSI" class="h-10 w-auto rounded-lg">
        </div>
    </div>
</div>

<div class="bg-white rounded-lg shadow-sm p-6 mb-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="relative flex-grow">
            <label for="searchUser" class="sr-only">Cari nama user</label>
            <input type="text"
                   id="searchUser"
                   name="search_user"
                   placeholder="Cari nama user"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent pl-10">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
        </div>

        <form method="GET" class="flex flex-wrap items-center gap-4 md:ml-auto">
            <div class="relative">
                <label for="filterJabatan" class="sr-only">Jabatan</label>
                <select id="filterJabatan" name="jabatan" class="appearance-none border border-gray-300 text-gray-700 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 px-4 py-2 pr-10">
                    <option value="">Jabatan</option>
                    <option value="manager" <?= request()->getGet('jabatan') == 'manager' ? 'selected' : '' ?>>Manager</option>
                    <option value="staff" <?= request()->getGet('jabatan') == 'staff' ? 'selected' : '' ?>>Staff</option>
                    <option value="admin" <?= request()->getGet('jabatan') == 'admin' ? 'selected' : '' ?>>Admin</option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-gray-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
            </div>
            
            <div class="relative">
                <label for="filterStatus" class="sr-only">Status</label>
                <select id="filterStatus" name="status" class="appearance-none border border-gray-300 text-gray-700 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 px-4 py-2 pr-10">
                    <option value="">Status</option>
                    <option value="aktif" <?= request()->getGet('status') == 'aktif' ? 'selected' : '' ?>>Aktif</option>
                    <option value="nonaktif" <?= request()->getGet('status') == 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-gray-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
            </div>

            <button type="button" id="openAddUserModal" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 text-sm flex items-center gap-1 flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                Tambah
            </button>
        </form>
    </div>
</div>

<div class="bg-white rounded-lg shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-blue-600">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">No</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Nama Lengkap</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Jabatan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">1</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Nizar Hadabi Erawan</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">nizarmanager@gmail.com</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Manager</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Aktif</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <button type="button" class="inline-flex items-center text-yellow-500 hover:text-yellow-700 mr-3 open-edit-user-modal" data-user-id="1">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M17.414 2.586a2 2 0 00-2.828 0l-1.793 1.793 2.828 2.828 1.793-1.793a2 2 0 000-2.828zM2 13.586V17h3.414l9.793-9.793-2.828-2.828L2 13.586z"></path>
                            </svg>
                        </button>
                        <button type="button" class="inline-flex items-center text-red-500 hover:text-red-700 open-delete-user-modal" data-user-id="1">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M9 2a1 1 0 011 1v1h2a1 1 0 110 2H8V3a1 1 0 011-1zm0 6a1 1 0 011 1v6a1 1 0 11-2 0V9a1 1 0 011-1zm6-3a1 1 0 011 1v10a2 2 0 01-2 2H6a2 2 0 01-2-2V7a1 1 0 011-1h10z" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                    </td>
                </tr>
                </tbody>
        </table>
    </div>
</div>

<div id="modalAddUser" class="fixed inset-0 z-50 flex items-center justify-center bg-black/10 backdrop-blur-sm hidden">
    <div class="bg-white p-6 rounded-lg shadow-xl w-full max-w-md transition-all duration-300 ease-in-out">
        <h2 class="text-xl font-semibold mb-4">Tambah User</h2>

        <form>
            <div class="mb-4">
                <label for="addNamaLengkap" class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                <input type="text" id="addNamaLengkap" placeholder="Masukan nama lengkap" class="w-full border rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="mb-4">
                <label for="addEmail" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" id="addEmail" placeholder="abcd@gmail.com" class="w-full border rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="mb-4">
                <label for="addPassword" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <div class="relative">
                    <input type="password" id="addPassword" placeholder="Masukan password" class="w-full border rounded-lg px-3 py-2 pr-10 focus:ring-blue-500 focus:border-blue-500">
                    <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 toggle-password-visibility">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <div class="mb-4">
                <label for="addJabatan" class="block text-sm font-medium text-gray-700 mb-1">Jabatan</label>
                <div class="relative">
                    <select id="addJabatan" class="w-full border rounded-lg px-3 py-2 pr-10 appearance-none focus:ring-blue-500 focus:border-blue-500">
                        <option disabled selected>Pilih jabatan</option>
                        <option value="Staff">Staff</option>
                        <option value="Manager">Manager</option>
                        <option value="Supervisor">Supervisor</option>
                        <option value="Direksi">Direksi</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </div>
            </div>
            <div class="mb-6">
                <label for="addStatus" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <div class="relative">
                    <select id="addStatus" class="w-full border rounded-lg px-3 py-2 pr-10 appearance-none focus:ring-blue-500 focus:border-blue-500">
                        <option disabled selected>Pilih Status</option>
                        <option value="Aktif">Aktif</option>
                        <option value="Nonaktif">Nonaktif</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </div>
            </div>
            <div class="flex justify-end space-x-4">
                <button type="button" id="cancelAddUserModal" class="text-gray-600 px-4 py-2 rounded-lg hover:bg-gray-100">Batal</button>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">Simpan</button>
            </div>
        </form>
    </div>
</div>

<div id="modalEditUser" class="fixed inset-0 z-50 flex items-center justify-center bg-black/10 backdrop-blur-sm hidden">
    <div class="bg-white p-6 rounded-lg shadow-xl w-full max-w-md transition-all duration-300 ease-in-out">
        <h2 class="text-xl font-semibold mb-4">Edit User</h2>

        <form>
            <div class="mb-4">
                <label for="editNamaLengkap" class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                <input type="text" id="editNamaLengkap" value="Nizar Hadabi Erawan" class="w-full border rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="mb-4">
                <label for="editEmail" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" id="editEmail" value="nizarmanager@gmail.com" class="w-full border rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="mb-4">
                <label for="editPassword" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <div class="relative">
                    <input type="password" id="editPassword" value="••••••••••" class="w-full border rounded-lg px-3 py-2 pr-10 focus:ring-blue-500 focus:border-blue-500">
                    <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 toggle-password-visibility">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <div class="mb-4">
                <label for="editJabatan" class="block text-sm font-medium text-gray-700 mb-1">Jabatan</label>
                <div class="relative">
                    <select id="editJabatan" class="w-full border rounded-lg px-3 py-2 pr-10 appearance-none focus:ring-blue-500 focus:border-blue-500">
                        <option value="Manager" selected>Manager</option>
                        <option value="Staff">Staff</option>
                        <option value="Supervisor">Supervisor</option>
                        <option value="Direksi">Direksi</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </div>
            </div>
            <div class="mb-6">
                <label for="editStatus" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <div class="relative">
                    <select id="editStatus" class="w-full border rounded-lg px-3 py-2 pr-10 appearance-none focus:ring-blue-500 focus:border-blue-500">
                        <option value="Aktif" selected>Aktif</option>
                        <option value="Nonaktif">Nonaktif</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </div>
            </div>
            <div class="flex justify-end space-x-4">
                <button type="button" id="cancelEditUserModal" class="text-gray-600 px-4 py-2 rounded-lg hover:bg-gray-100">Batal</button>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">Simpan</button>
            </div>
        </form>
    </div>
</div>

<div id="modalDeleteUser" class="fixed inset-0 z-50 flex items-center justify-center bg-black/10 backdrop-blur-sm hidden">
    <div class="bg-white p-6 rounded-lg shadow-xl w-full max-w-sm transition-all duration-300 ease-in-out">
        <h2 class="text-xl font-semibold mb-4 text-gray-800">Hapus User</h2>
        <p class="text-gray-700 mb-6">Yakin ingin menghapus data user?</p>
        <div class="flex justify-end space-x-4">
            <button type="button" id="cancelDeleteUserModal" class="text-blue-500 px-4 py-2 rounded-lg hover:bg-gray-100">Batal</button>
            <button type="button" id="confirmDeleteUserBtn" class="text-blue-600 font-semibold px-4 py-2 rounded-lg hover:bg-blue-50">Hapus</button>
        </div>
    </div>
</div>


<?= $this->endSection() ?>