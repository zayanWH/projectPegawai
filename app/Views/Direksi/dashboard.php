<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<!-- Header Dashboard -->
<div class="bg-white rounded-lg shadow-sm p-6 mb-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <h1 class="text-2xl font-semibold text-gray-800">Dashboard</h1>
        </div>
        <div class="flex items-center space-x-4">
            <img src="<?= base_url('images/logo.png') ?>" alt="Logo USSI" class="h-10 w-auto rounded-lg">
        </div>
    </div>
</div>

<!-- Tombol Baru -->
<div class="relative inline-block text-left mb-6">

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

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-500">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 7a2 2 0 012-2h5l2 2h7a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V7z" />
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm text-gray-600">Total Folder</p>
                <p class="text-2xl font-semibold text-gray-800"><?= esc($totalFolders) ?></p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm text-gray-600">Total File</p>
                <p class="text-2xl font-semibold text-gray-800"><?= esc($totalFiles) ?></p>
            </div>
        </div>
    </div>

   <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-yellow-500">
    <div class="flex items-center">
        <div class="flex-shrink-0">
            <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>
        <div class="ml-4">
            <p class="text-sm text-gray-600">Terakhir Upload</p>
            <p class="text-2xl font-semibold text-gray-800"><?= esc($latestUploadDate) ?></p>
        </div>
    </div>
</div>
</div>

<div class="bg-white rounded-lg shadow-sm hidden md:block">
    <div class="overflow-x-auto">
        <table class="w-full">
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($recentItems)): ?>
                    <tr>
                        <td colspan="4" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                            Tidak ada item terbaru untuk ditampilkan.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($recentItems as $item): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <?php if ($item['type'] === 'folder'): ?>
                                        <svg class="w-5 h-5 text-yellow-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"></path>
                                        </svg>
                                    <?php else: ?>
                                        <svg class="w-5 h-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"></path>
                                        </svg>
                                    <?php endif; ?>
                                    <span class="text-sm text-gray-900"><?= esc($item['name']) ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <span class="text-sm text-gray-900">User ID: <?= esc($item['uploader_id']) ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?= date('d M Y', strtotime($item['created_at'])) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <button onclick="toggleMenu(this)" class="text-blue-600 hover:text-blue-900">⋮</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="bg-white rounded-lg shadow-sm block md:hidden">
    <div class="divide-y divide-gray-200">
        <?php if (empty($recentItems)): ?>
            <div class="flex items-center justify-center px-6 py-4 text-gray-500">
                Tidak ada item terbaru untuk ditampilkan.
            </div>
        <?php else: ?>
            <?php foreach ($recentItems as $item): ?>
                <div class="flex items-center justify-between px-6 py-4 hover:bg-gray-50">
                    <div class="flex items-center space-x-4">
                        <?php if ($item['type'] === 'folder'): ?>
                            <svg class="w-6 h-6 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"></path>
                            </svg>
                        <?php else: ?>
                            <svg class="w-6 h-6 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd" />
                            </svg>
                        <?php endif; ?>
                        <div class="text-sm">
                            <div class="font-medium text-gray-900"><?= esc($item['name']) ?></div>
                            <div class="text-gray-500 text-xs">
                                <?= date('d M Y', strtotime($item['created_at'])) ?> oleh User ID: <?= esc($item['uploader_id']) ?>
                            </div>
                        </div>
                    </div>
                    <button onclick="toggleMenu(this)" class="text-blue-600 text-xl">⋮</button>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- LIST VIEW (tablet and below) -->
<div class="bg-white rounded-lg shadow-sm block md:hidden">
    <div class="p-6 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-800 flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            Dokumen Terbaru
        </h2>
    </div>
    <div class="divide-y divide-gray-200">
        <div class="flex items-center justify-between px-6 py-4 hover:bg-gray-50">
            <div class="flex items-center space-x-4">
                <svg class="w-6 h-6 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd" />
                </svg>
                <div class="text-sm">
                    <div class="font-medium text-gray-900">laporan.pdf</div>
                    <div class="text-gray-500 text-xs">2 Jul 2024 oleh user@gmail.com</div>
                </div>
            </div>
            <button onclick="toggleMenu(this)" class="text-blue-600 text-xl">⋮</button>
        </div>
    </div>
</div>

<?= $this->endSection() ?>