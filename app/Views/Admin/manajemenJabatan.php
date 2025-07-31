<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>

<div class="bg-white rounded-lg shadow-sm p-6 mb-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <h1 class="text-2xl font-semibold text-gray-800">Manajemen Jabatan</h1>
        </div>
        <div class="flex items-center space-x-4">
            <img src="<?= base_url('images/logo.png') ?>" alt="Logo USSI" class="h-10 w-auto rounded-lg">
        </div>
    </div>
</div>

<div class="bg-white rounded-lg shadow-sm p-6 mb-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <form method="GET" class="flex flex-wrap items-center gap-4 md:ml-auto">
            <button type="button" id="openAddJabatanModalBtn" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 text-sm flex items-center gap-1 flex-shrink-0">
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
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Nama Jabatan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Level</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Max Storage</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php $no = 1; ?>
                <?php if (!empty($roles)): ?>
                    <?php foreach ($roles as $role): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= $no++ ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= esc($role['name']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= esc($role['level']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= esc($role['max_upload_size_mb']) ?> MB</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button type="button" class="inline-flex items-center text-yellow-500 hover:text-yellow-700 mr-3 open-edit-jabatan-modal"
                                    data-jabatan-id="<?= esc($role['id']) ?>"
                                    data-nama-jabatan="<?= esc($role['name']) ?>"
                                    data-level="<?= esc($role['level']) ?>"
                                    data-max-storage="<?= esc($role['max_upload_size_mb']) ?>">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M17.414 2.586a2 2 0 00-2.828 0l-1.793 1.793 2.828 2.828 1.793-1.793a2 2 0 000-2.828zM2 13.586V17h3.414l9.793-9.793-2.828-2.828L2 13.586z"></path>
                                    </svg>
                                </button>
                                <button type="button" class="inline-flex items-center text-red-500 hover:text-red-700 open-delete-jabatan-modal"
                                    data-jabatan-id="<?= esc($role['id']) ?>"
                                    data-jabatan-name="<?= esc($role['name']) ?>"> <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M9 2a1 1 0 011 1v1h2a1 1 0 110 2H8V3a1 1 0 011-1zm0 6a1 1 0 011 1v6a1 1 0 11-2 0V9a1 1 0 011-1zm6-3a1 1 0 011 1v10a2 2 0 01-2 2H6a2 2 0 01-2-2V7a1 1 0 011-1h10z" clip-rule="evenodd"></path>
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">Tidak ada data jabatan.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="modalAddJabatan" class="fixed inset-0 z-50 flex items-center justify-center bg-black/10 backdrop-blur-sm hidden">
    <div class="bg-white p-6 rounded-lg shadow-xl w-full max-w-md transition-all duration-300 ease-in-out">
        <h2 class="text-xl font-semibold mb-4">Tambah Jabatan</h2>

        <form id="addJabatanForm">
            <div class="mb-4">
                <label for="addNamaJabatan" class="block text-sm font-medium text-gray-700 mb-1">Nama Jabatan</label>
                <input type="text" id="addNamaJabatan" name="name" placeholder="Masukan nama jabatan" class="w-full border rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                <p class="text-red-500 text-xs italic hidden" id="errorAddNamaJabatan"></p>
            </div>
            <div class="mb-4">
                <label for="addLevel" class="block text-sm font-medium text-gray-700 mb-1">Level</label>
                <input type="number" id="addLevel" name="level" placeholder="1" class="w-full border rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                <p class="text-red-500 text-xs italic hidden" id="errorAddLevel"></p>
            </div>
            <div class="mb-6">
                <label for="addMaxStorage" class="block text-sm font-medium text-gray-700 mb-1">Max Storage</label>
                <div class="flex items-center">
                    <input type="number" id="addMaxStorage" name="max_upload_size_mb" placeholder="2000" class="w-full border rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                    <span class="ml-2 text-gray-700">MB</span>
                </div>
                <p class="text-red-500 text-xs italic hidden" id="errorAddMaxStorage"></p>
            </div>
            <div class="flex justify-end space-x-4">
                <button type="button" id="cancelAddJabatanModal" class="text-gray-600 px-4 py-2 rounded-lg hover:bg-gray-100">Batal</button>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Jabatan -->
<div id="modalEditJabatan" class="fixed inset-0 z-50 flex items-center justify-center bg-black/10 backdrop-blur-sm hidden">
    <div class="bg-white p-6 rounded-lg shadow-xl w-full max-w-md transition-all duration-300 ease-in-out">
        <h2 class="text-xl font-semibold mb-4">Edit Jabatan</h2>

        <form id="editJabatanForm">
            <!-- Hidden input untuk menyimpan ID jabatan yang diedit -->
            <input type="hidden" id="editJabatanId" name="id">

            <div class="mb-4">
                <label for="editNamaJabatan" class="block text-sm font-medium text-gray-700 mb-1">Nama Jabatan</label>
                <input type="text" id="editNamaJabatan" name="name" class="w-full border rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="mb-4">
                <label for="editLevel" class="block text-sm font-medium text-gray-700 mb-1">Level</label>
                <input type="number" id="editLevel" name="level" class="w-full border rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="mb-6">
                <label for="editMaxStorage" class="block text-sm font-medium text-gray-700 mb-1">Max Storage</label>
                <div class="flex items-center">
                    <input type="number" id="editMaxStorage" name="max_upload_size_mb" class="w-full border rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                    <span class="ml-2 text-gray-700">MB</span>
                </div>
            </div>
            <div class="flex justify-end space-x-4">
                <button type="button" id="cancelEditJabatanModal" class="text-gray-600 px-4 py-2 rounded-lg hover:bg-gray-100">Batal</button>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Pesan (Untuk sukses/error) -->
<div id="messageModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/10 backdrop-blur-sm hidden">
    <div class="bg-white p-6 rounded-lg shadow-xl w-full max-w-sm text-center">
        <h3 id="messageModalTitle" class="text-lg font-semibold mb-3"></h3>
        <p id="messageModalContent" class="text-gray-700 mb-4"></p>
        <button type="button" id="closeMessageModal" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">OK</button>
    </div>
</div>

<div id="modalDeleteJabatan" class="fixed inset-0 z-50 flex items-center justify-center bg-black/10 backdrop-blur-sm hidden">
    <div class="bg-white p-6 rounded-lg shadow-xl w-full max-w-sm transition-all duration-300 ease-in-out">
        <h2 class="text-xl font-semibold mb-4 text-gray-800">Hapus Jabatan</h2>
        <p class="text-gray-700 mb-6">
            Yakin ingin menghapus jabatan 
            <strong id="jabatanNameToDelete"></strong> ini? Tindakan ini tidak dapat dibatalkan.
        </p>
        <input type="hidden" id="jabatanIdToDelete">
        <div class="flex justify-end space-x-4">
            <button type="button" id="cancelDeleteJabatanModal" class="text-blue-500 px-4 py-2 rounded-lg hover:bg-gray-100">Batal</button>
            <button type="button" id="confirmDeleteJabatanBtn" class="text-blue-600 font-semibold px-4 py-2 rounded-lg hover:bg-blue-50">Hapus</button>
        </div>
    </div>
</div>

<script src="<?= base_url('js/editRoleAdmin.js') ?>"></script>
<script src="<?= base_url('js/addRoleAdmin.js') ?>"></script>
<script src="<?= base_url('js/deleteRoleAdmin.js') ?>"></script>


<?= $this->endSection() ?>