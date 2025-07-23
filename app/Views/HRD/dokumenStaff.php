<?= $this->extend('layout/hrd') ?>

<?= $this->section('content') ?>
<!-- Header Dashboard -->
<div class="bg-white rounded-lg shadow-sm p-6 mb-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <h1 class="text-2xl font-semibold text-gray-800">Dokumen Staff</h1>
        </div>
        <div class="flex items-center space-x-4">
            <!-- Search Bar -->
            <div class="relative">
                <input type="text" 
                       placeholder="Masukkan file dokumen..." 
                       class="w-80 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <svg class="absolute right-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
            <!-- Logo -->
            <img src="<?= base_url('images/logo.png') ?>" alt="Logo USSI" class="h-10 w-auto rounded-lg">
        </div>
    </div>
</div>

<!-- Tombol Baru -->
<div class="relative inline-block text-left mb-6">
  <button id="dropdownButton" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium flex items-center space-x-2">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
    </svg>
    <span>Baru</span>
  </button>

  <div id="dropdownMenu" class="absolute z-10 mt-2 w-48 bg-white border border-gray-200 rounded-lg shadow-lg hidden">
    <a href="#" id="openCreateFolder" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">📁 Buat Folder</a>
    <a href="#" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">⬆️ Upload File</a>
    <a href="#" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">📂 Upload Folder</a>
  </div>
</div>

<!-- Modal -->
<div id="modalCreateFolder" class="fixed inset-0 z-50 flex items-center justify-center bg-black/10 backdrop-blur-sm hidden">
  <div class="bg-white p-6 rounded-lg shadow-xl w-full max-w-md transition-all duration-300 ease-in-out">
    <h2 class="text-xl font-semibold mb-4">Folder Baru</h2>

    <label class="block text-sm font-medium mb-1">Jenis Folder</label>
    <div class="relative mb-4">
      <select id="folderType" class="w-full border rounded-lg px-3 py-2 pr-10 appearance-none">
        <option disabled selected>Pilih jenis folder</option>
        <option value="personal">Personal Folder</option>
        <option value="shared">Shared Folder</option>
      </select>
      <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-gray-400">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
        </svg>
      </div>
    </div>

    <div class="grid grid-cols-2 gap-2 mb-4">
      <label><input type="checkbox" value="Staff" class="mr-2"> Staff</label>
      <label><input type="checkbox" value="Manager" class="mr-2"> Manager</label>
      <label><input type="checkbox" value="Supervisor" class="mr-2"> Supervisor</label>
      <label><input type="checkbox" value="Direksi" class="mr-2"> Direksi</label>
    </div>

    <label class="block text-sm font-medium mb-1">Akses</label>
    <div class="relative mb-4">
      <select id="folderAccess" class="w-full border rounded-lg px-3 py-2 pr-10 appearance-none">
        <option disabled selected>Pilih akses</option>
        <option value="full">Full Access</option>
        <option value="read">Read Only</option>
      </select>
      <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-gray-400">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
        </svg>
      </div>
    </div>

    <label class="block text-sm font-medium">Nama Folder</label>
    <input type="text" id="folderName" placeholder="Masukan nama folder" class="w-full border rounded-lg px-3 py-2 mb-4">

    <div class="flex justify-end space-x-4">
      <button id="cancelModal" class="text-blue-500">Batal</button>
      <button id="createFolderBtn" class="text-blue-600 font-semibold">Buat</button>
    </div>
  </div>
</div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-blue-600">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Nama Folder</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Tipe Folder</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Tanggal Dibuat</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (!empty($personalFolders)): ?>
                    <?php foreach ($personalFolders as $folder): ?>
                        <tr class="hover:bg-gray-50"
                            data-folder-id="<?= esc($folder['id']) ?>"
                            data-folder-name="<?= esc($folder['name']) ?>"
                            data-folder-type="<?= esc($folder['folder_type']) ?>"
                            data-folder-is-shared="<?= esc($folder['is_shared'] ?? 0) ?>"
                            data-folder-shared-type="<?= esc($folder['shared_type'] ?? '') ?>"
                            data-folder-owner-id="<?= esc($folder['owner_id']) ?>"
                            data-folder-owner-name="<?= esc($folder['owner_display'] ?? $folder['owner_name'] ?? 'Unknown') ?>"
                            data-folder-created-at="<?= esc($folder['created_at']) ?>"
                            data-folder-updated-at="<?= esc($folder['updated_at']) ?>"
                            data-folder-path="<?= esc($folder['path'] ?? $folder['name']) ?>">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="<?= site_url('hrd/view-staff-folder/' . $folder['id']) ?>" class="block h-full w-full text-sm text-gray-900 hover:text-blue-700 hover:underline">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 text-blue-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"></path>
                                        </svg>
                                        <?= esc($folder['name']) ?>
                                    </div>
                                </a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?= esc(ucfirst($folder['folder_type'])) ?>
                                <?php if (isset($folder['is_shared']) && $folder['is_shared'] == 1): ?>
                                    (<?= esc(ucfirst($folder['shared_type'])) ?>)
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?= date('d M Y', strtotime($folder['created_at'])) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <button onclick="toggleMenu(this)" class="text-blue-600 hover:text-blue-900">⋮</button>
                                </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                            Belum ada dokumen yang tersedia.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
    

<?= $this->endSection() ?>
