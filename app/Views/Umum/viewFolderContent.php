<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<div class="bg-white rounded-lg shadow-sm p-6 mb-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-2 text-xl font-semibold text-gray-800">
            <a href="<?= site_url('hrd/dokumen-staff') ?>" class="text-blue-600 hover:text-blue-800">Dokumen Staff</a>
            <?php if (!empty($breadcrumbs)): ?>
                <?php foreach ($breadcrumbs as $breadcrumb): ?>
                    <span class="text-gray-500">/</span>
                    <a href="<?= site_url('hrd/view-staff-folder/' . $breadcrumb['id']) ?>"
                        class="text-blue-600 hover:text-blue-800"><?= esc($breadcrumb['name']) ?></a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="flex items-center space-x-4">
            <img src="<?= base_url('images/logo.png') ?>" alt="Logo USSI" class="h-10 w-auto rounded-lg">
        </div>
    </div>
</div>

<div class="relative inline-block text-left mb-6">
    <button id="newButton"
        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium flex items-center space-x-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
        </svg>
        <span>Baru</span>
    </button>

    <div id="newDropdown" class="absolute z-10 mt-2 w-48 bg-white border border-gray-200 rounded-lg shadow-lg hidden">
        <a href="#" id="createFolderLink" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">📁 Buat Sub-Folder</a>
        <a href="#" id="uploadFileLink" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">⬆️ Upload File</a>
    </div>
</div>

<div id="modalUploadFile"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/10 backdrop-blur-sm hidden">
    <div class="bg-white p-6 rounded-lg shadow-xl w-full max-w-md transition-all duration-300 ease-in-out">
        <h2 class="text-xl font-semibold mb-4">Upload File Baru</h2>
        <input type="file" id="fileInput" />

        <div class="flex justify-end space-x-4">
            <button id="cancelUploadFileModal" class="text-blue-500 hover:text-blue-700">Batal</button>
            <button id="submitUploadFile"
                class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-md">Upload</button>
        </div>
    </div>
</div>

<div id="modalCreateFolder"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/10 backdrop-blur-sm hidden">
    <div class="bg-white p-6 rounded-lg shadow-xl w-full max-w-md transition-all duration-300 ease-in-out">
        <h2 class="text-xl font-semibold mb-4">Buat Folder Baru</h2>
        <label class="block text-sm font-medium">Nama Folder</label>
        <input type="text" id="newFolderNameInput" placeholder="Masukan nama folder"
            class="w-full border rounded-lg px-3 py-2 mb-4">
        <div class="flex justify-end space-x-4">
            <button id="cancelCreateFolderModal" class="text-blue-500 hover:text-blue-700">Batal</button>
            <button id="submitCreateFolder"
                class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-md">Buat</button>
        </div>
    </div>
</div>

<div id="modalRename"
    class="fixed inset-0 z-[10000] flex items-center justify-center bg-black/20 backdrop-blur-sm hidden">
    <div class="bg-white p-6 rounded-lg shadow-xl w-full max-w-md transition-all duration-300 ease-in-out">
        <h2 class="text-xl font-semibold mb-4">Ganti Nama <span id="renameItemTypeLabel"></span></h2>
        <label class="block text-sm font-medium mb-1">Nama Baru</label>
        <input type="text" id="newFileName" class="w-full border rounded-lg px-3 py-2 mb-4"
            placeholder="Masukkan nama baru">
        <div class="flex justify-end space-x-4">
            <button onclick="closeRenameModal()" class="text-blue-500">Batal</button>
            <button onclick="submitRename()" class="text-blue-600 font-semibold">Simpan</button>
        </div>
    </div>
</div>

<div id="floatingMenu"
    class="fixed w-48 bg-white rounded-xl shadow-lg hidden transition ease-out duration-200 transform scale-95 opacity-0 z-[9998]">
    <ul class="text-sm text-gray-700 divide-y divide-gray-100">
        <li onclick="showRenameModal()" class="flex items-center px-4 py-3 hover:bg-gray-100 cursor-pointer">
            <svg class="w-5 h-5 mr-3 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                <path
                    d="M17.414 2.586a2 2 0 00-2.828 0l-1.793 1.793 2.828 2.828 1.793-1.793a2 2 0 000-2.828zM2 13.586V17h3.414l9.793-9.793-2.828-2.828L2 13.586z" />
            </svg>
            Ganti nama
        </li>
        <li id="downloadMenuItem" class="flex items-center px-4 py-3 hover:bg-gray-100 cursor-pointer">
            <svg class="w-5 h-5 mr-3 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                <path d="M3 3a1 1 0 011-1h3v2H5v12h10V4h-2V2h3a1 1 0 011 1v14a1 1 0 01-1 1H4a1 1 0 01-1-1V3z" />
            </svg>
            Download
        </li>
        <li onclick="showInfoDetailModal()" class="flex items-center px-4 py-3 hover:bg-gray-100 cursor-pointer">
            <svg class="w-5 h-5 mr-3 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9 2a7 7 0 100 14A7 7 0 009 2zM8 7h2v5H8V7zm1 8a1 1 0 110-2 1 1 0 010 2z" />
            </svg>
            Informasi detail
        </li>
        <li id="deleteMenuItem" class="flex items-center px-4 py-3 hover:bg-gray-100 cursor-pointer text-red-600">
            <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                <path
                    d="M6 2a1 1 0 00-1 1v1H3a1 1 0 000 2h14a1 1 0 100-2h-2V3a1 1 0 00-1-1H6zM5 7a1 1 0 011 1v9a2 2 0 002 2h4a2 2 0 002-2V8a1 1 0 112 0v9a4 4 0 01-4 4H8a4 4 0 01-4-4V8a1 1 0 011-1z" />
            </svg>
            Hapus
        </li>
    </ul>
</div>

<div id="modalInfoDetail"
    class="fixed inset-0 z-[10000] flex items-center justify-center bg-black/20 backdrop-blur-sm hidden">
    <div class="bg-white p-6 rounded-lg shadow-xl w-full max-w-md transition-all duration-300 ease-in-out">
        <h2 class="text-xl font-semibold mb-4">Detail Informasi <span id="infoDetailItemTypeLabel"></span></h2>
        <div class="text-sm text-gray-800">
            <p class="mb-2"><strong>Nama:</strong> <span id="detailName"></span></p>
            <p class="mb-2"><strong>Jenis:</strong> <span id="detailJenis"></span></p>
            <p class="mb-2"><strong>Ukuran:</strong> <span id="detailUkuran"></span></p>
            <p class="mb-2"><strong>Pemilik:</strong> <span id="detailPemilik"></span></p>
            <p class="mb-2"><strong>Dibuat:</strong> <span id="detailDibuat"></span></p>
            <p class="mb-2"><strong>Diperbarui:</strong> <span id="detailDiperbarui"></span></p>
            <p class="mb-2"><strong>Path:</strong> <span id="detailPath"></span></p>
            <p class="mb-2"><strong>Tipe Folder:</strong> <span id="detailFolderType"></span></p>
            <p class="mb-2"><strong>Dibagikan:</strong> <span id="detailIsShared"></span></p>
        </div>
        <div class="flex justify-end space-x-4 mt-4">
            <button onclick="closeInfoDetailModal()" class="text-blue-500">Tutup</button>
        </div>
    </div>
</div>


<div class="bg-white rounded-lg shadow-sm">
    <div class="p-6 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-800 flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                </path>
            </svg>
            Isi Folder
        </h2>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-blue-600">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Nama</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Jenis</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Tanggal
                        Dibuat / Diunggah</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($subFolders) && empty($filesInFolder)): ?>
                    <tr>
                        <td colspan="4" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                            Folder ini kosong.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php if (!empty($subFolders)): ?>
                        <?php foreach ($subFolders as $subFolder): ?>
                            <tr class="hover:bg-gray-50 item-with-context-menu" data-item-type="folder"
                                data-item-id="<?= esc($subFolder['id']) ?>" data-item-name="<?= esc($subFolder['name']) ?>"
                                data-folder-id="<?= esc($subFolder['id']) ?>" data-folder-name="<?= esc($subFolder['name']) ?>"
                                data-folder-type="<?= esc($subFolder['folder_type'] ?? 'personal') ?>"
                                data-folder-is-shared="<?= esc($subFolder['is_shared'] ?? 0) ?>"
                                data-folder-shared-type="<?= esc($subFolder['shared_type'] ?? '') ?>"
                                data-folder-owner-id="<?= esc($subFolder['owner_id'] ?? '') ?>"
                                data-folder-owner-name="<?= esc($subFolder['owner_display'] ?? $subFolder['owner_name'] ?? 'Unknown') ?>"
                                data-folder-created-at="<?= esc($subFolder['created_at']) ?>"
                                data-folder-updated-at="<?= esc($subFolder['updated_at']) ?>"
                                data-folder-path="<?= esc($subFolder['path'] ?? $subFolder['name']) ?>">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <a href="<?= site_url('hrd/view-staff-folder/' . $subFolder['id']) ?>"
                                        class="block h-full w-full text-sm text-gray-900 hover:text-blue-700 hover:underline">
                                        <div class="flex items-center">
                                            <svg class="w-5 h-5 text-blue-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z">
                                                </path>
                                            </svg>
                                            <?= esc($subFolder['name']) ?>
                                        </div>
                                    </a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    Folder
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?= date('d M Y', strtotime($subFolder['created_at'])) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <button
                                        onclick="showFloatingMenu(event, 'folder', '<?= esc($subFolder['id']) ?>', '<?= esc($subFolder['name']) ?>')"
                                        class="text-blue-600 hover:text-blue-900">⋮</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <?php if (!empty($filesInFolder)): ?>
                        <?php foreach ($filesInFolder as $file): ?>
                            <tr class="hover:bg-gray-50 item-with-context-menu" data-item-type="file"
                                data-item-id="<?= esc($file['id']) ?>" data-item-name="<?= esc($file['file_name']) ?>"
                                data-file-size="<?= esc($file['file_size']) ?>" data-file-type="<?= esc($file['file_type']) ?>"
                                data-file-uploaded-by="<?= esc($file['uploaded_by'] ?? 'Unknown') ?>"
                                data-file-created-at="<?= esc($file['created_at']) ?>"
                                data-file-updated-at="<?= esc($file['updated_at'] ?? $file['created_at']) ?>"
                                data-file-path="<?= esc($file['server_file_name']) ?>">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <a href="<?= site_url('hrd/file/view/' . $file['id']) ?>" target="_blank"
                                        class="block h-full w-full text-sm text-gray-900 hover:text-blue-700 hover:underline">
                                        <div class="flex items-center">
                                            <?php
                                            $fileExtension = pathinfo($file['file_name'], PATHINFO_EXTENSION);
                                            $iconSvg = '';
                                            switch (strtolower($fileExtension)) {
                                                case 'pdf':
                                                    $iconSvg = '<svg class="w-5 h-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"></path></svg>';
                                                    break;
                                                case 'doc':
                                                case 'docx':
                                                    $iconSvg = '<svg class="w-5 h-5 text-blue-600 mr-2" fill="currentColor" viewBox="0 0 20 20"><path d="M9 2a2 2 0 00-2 2v4a2 2 0 002 2h2a2 2 0 002-2V4a2 2 0 00-2-2H9z"></path><path d="M5 9a2 2 0 00-2 2v4a2 2 0 002 2h10a2 2 0 002-2v-4a2 2 0 00-2-2H5z"></path></svg>';
                                                    break;
                                                case 'xls':
                                                case 'xlsx':
                                                    $iconSvg = '<svg class="w-5 h-5 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20"><path d="M4 4h12a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6a2 2 0 012-2zM6 8v6h2V8H6zm4 0v6h2V8h-2zm4 0v6h2V8h-2z"></path></svg>';
                                                    break;
                                                case 'png':
                                                case 'jpg':
                                                case 'jpeg':
                                                case 'gif':
                                                    $iconSvg = '<svg class="w-5 h-5 text-purple-500 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-4 4 4 4-4v4z" clip-rule="evenodd"></path></svg>';
                                                    break;
                                                default:
                                                    $iconSvg = '<svg class="w-5 h-5 text-gray-500 mr-2" fill="currentColor" viewBox="0 0 20 20"><path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"></path></svg>';
                                                    break;
                                            }
                                            echo $iconSvg;
                                            ?>
                                            <span class="text-sm text-gray-900"><?= esc($file['file_name']) ?></span>
                                        </div>
                                    </a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?= esc(strtoupper($fileExtension)) ?> File
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?= date('d M Y', strtotime($file['created_at'])) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <button
                                        onclick="showFloatingMenu(event, 'file', '<?= esc($file['id']) ?>', '<?= esc($file['file_name']) ?>')"
                                        class="text-blue-600 hover:text-blue-900">⋮</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    // Pastikan variabel ini ada untuk JS
    window.currentFolderId = <?= esc($folderId ?? 'null') ?>;
    window.currentFolderType = '<?= esc($folderType ?? 'personal') ?>';
    window.isCurrentFolderShared = <?= (isset($isShared) && $isShared) ? 'true' : 'false' ?>;
    window.currentUserId = <?= session()->get('user_id') ?? 'null' ?>; // Anda perlu user_id dari sesi
    window.baseUrl = "<?php echo base_url('index.php/hrd/view-staff-folder/' . $folderId . '/'); ?>";


    // Variables for context menu (floating menu)
    let selectedItemId = null;
    let selectedItemType = null;
    let selectedItemName = null;

    // --- UTILITY FUNCTIONS ---
    function formatBytes(bytes, decimals = 2) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const dm = decimals < 0 ? 0 : decimals;
        const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
    }

    // --- FLOATING MENU LOGIC (Context Menu) ---
    function showFloatingMenu(event, itemType, itemId, itemName) {
        event.preventDefault(); // Mencegah menu konteks default browser
        event.stopPropagation(); // Mencegah event click menyebar ke document

        selectedItemId = itemId;
        selectedItemType = itemType;
        selectedItemName = itemName;

        const floatingMenu = document.getElementById('floatingMenu');
        const downloadMenuItem = document.getElementById('downloadMenuItem');

        // Show/hide download based on item type
        if (itemType === 'file') {
            downloadMenuItem.style.display = 'flex';
            // Update download link for file
            downloadMenuItem.onclick = function () {
                window.location.href = `<?= site_url('hrd/file/download/') ?>${selectedItemId}`;
                floatingMenu.classList.add('hidden');
                floatingMenu.classList.remove('scale-100', 'opacity-100');
                floatingMenu.classList.add('scale-95', 'opacity-0');
            };
        } else {
            downloadMenuItem.style.display = 'none'; // Folders cannot be downloaded directly
        }

        floatingMenu.style.left = `${event.pageX + 10}px`;
        floatingMenu.style.top = `${event.pageY - 10}px`;
        floatingMenu.classList.remove('hidden');
        setTimeout(() => {
            floatingMenu.classList.remove('scale-95', 'opacity-0');
            floatingMenu.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    // Hide floating menu when clicking outside
    document.addEventListener('click', function (event) {
        const floatingMenu = document.getElementById('floatingMenu');
        const newDropdown = document.getElementById('newDropdown'); // Juga sembunyikan newDropdown

        if (floatingMenu && !floatingMenu.contains(event.target)) {
            floatingMenu.classList.add('hidden');
            floatingMenu.classList.remove('scale-100', 'opacity-100');
            floatingMenu.classList.add('scale-95', 'opacity-0');
            selectedItemId = null;
            selectedItemType = null;
            selectedItemName = null;
        }
        if (newDropdown && !newDropdown.contains(event.target) && !document.getElementById('newButton').contains(event.target)) {
            newDropdown.classList.add('hidden');
        }
    });

    // --- RENAME MODAL LOGIC ---
    function showRenameModal() {
        if (!selectedItemId || !selectedItemType || !selectedItemName) return;

        const modalRename = document.getElementById('modalRename');
        const renameItemTypeLabel = document.getElementById('renameItemTypeLabel');
        const newFileNameInput = document.getElementById('newFileName');

        renameItemTypeLabel.textContent = selectedItemType === 'folder' ? 'Folder' : 'File';
        newFileNameInput.value = selectedItemName; // Pre-fill with current name

        modalRename.classList.remove('hidden');
        document.getElementById('floatingMenu').classList.add('hidden');
    }

    function closeRenameModal() {
        document.getElementById('modalRename').classList.add('hidden');
        document.getElementById('newFileName').value = '';
    }

    function submitRename() {
        const newName = document.getElementById('newFileName').value.trim();
        if (newName === '') {
            alert('Nama tidak boleh kosong!');
            return;
        }

        let url = '';
        if (selectedItemType === 'folder') {
            url = `<?= site_url('hrd/folder/rename') ?>`; // Rute HRD untuk rename folder
        } else if (selectedItemType === 'file') {
            url = `<?= site_url('hrd/file/rename') ?>`; // Rute HRD untuk rename file
        } else {
            alert('Tipe item tidak dikenal.');
            return;
        }

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                id: selectedItemId,
                newName: newName
            })
        })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert(data.message);
                    window.location.reload();
                } else {
                    alert('Gagal mengganti nama: ' + (data.message || 'Terjadi kesalahan.'));
                }
            })
            .catch(error => console.error('Error renaming item:', error));

        closeRenameModal();
    }

    // --- INFO DETAIL MODAL LOGIC ---
    function showInfoDetailModal() {
        if (!selectedItemId || !selectedItemType || !selectedItemName) return;

        const modalInfoDetail = document.getElementById('modalInfoDetail');
        const infoDetailItemTypeLabel = document.getElementById('infoDetailItemTypeLabel');

        // Clear previous details
        document.getElementById('detailName').textContent = '';
        document.getElementById('detailJenis').textContent = '';
        document.getElementById('detailUkuran').textContent = '';
        document.getElementById('detailPemilik').textContent = '';
        document.getElementById('detailDibuat').textContent = '';
        document.getElementById('detailDiperbarui').textContent = '';
        document.getElementById('detailPath').textContent = '';
        document.getElementById('detailFolderType').textContent = '';
        document.getElementById('detailIsShared').textContent = '';

        infoDetailItemTypeLabel.textContent = selectedItemType === 'folder' ? 'Folder' : 'File';

        if (selectedItemType === 'folder') {
            const folderElement = document.querySelector(`[data-item-id="${selectedItemId}"][data-item-type="folder"]`);
            if (folderElement) {
                document.getElementById('detailName').textContent = folderElement.dataset.itemName;
                document.getElementById('detailJenis').textContent = 'Folder';
                document.getElementById('detailUkuran').textContent = 'N/A'; // Ukuran folder biasanya tidak disimpan langsung
                document.getElementById('detailPemilik').textContent = folderElement.dataset.folderOwnerName || 'Unknown';
                document.getElementById('detailDibuat').textContent = new Date(folderElement.dataset.folderCreatedAt).toLocaleString();
                document.getElementById('detailDiperbarui').textContent = new Date(folderElement.dataset.folderUpdatedAt).toLocaleString();
                document.getElementById('detailPath').textContent = folderElement.dataset.folderPath;
                document.getElementById('detailFolderType').textContent = folderElement.dataset.folderType;
                document.getElementById('detailIsShared').textContent = folderElement.dataset.folderIsShared === '1' ? 'Ya' : 'Tidak';
            }
        } else if (selectedItemType === 'file') {
            const fileElement = document.querySelector(`[data-item-id="${selectedItemId}"][data-item-type="file"]`);
            if (fileElement) {
                document.getElementById('detailName').textContent = fileElement.dataset.itemName;
                document.getElementById('detailJenis').textContent = fileElement.dataset.fileType;
                document.getElementById('detailUkuran').textContent = formatBytes(parseInt(fileElement.dataset.fileSize));
                document.getElementById('detailPemilik').textContent = fileElement.dataset.fileUploadedBy;
                document.getElementById('detailDibuat').textContent = new Date(fileElement.dataset.fileCreatedAt).toLocaleString();
                document.getElementById('detailDiperbarui').textContent = new Date(fileElement.dataset.fileUpdatedAt).toLocaleString();
                document.getElementById('detailPath').textContent = fileElement.dataset.filePath;
                document.getElementById('detailFolderType').textContent = 'N/A'; // File tidak memiliki folder_type langsung
                document.getElementById('detailIsShared').textContent = 'N/A'; // Shared status mungkin di handle di level folder
            }
        }

        modalInfoDetail.classList.remove('hidden');
        document.getElementById('floatingMenu').classList.add('hidden');
    }

    function closeInfoDetailModal() {
        document.getElementById('modalInfoDetail').classList.add('hidden');
    }


    // --- DELETE LOGIC ---
    document.getElementById('deleteMenuItem').addEventListener('click', function () {
        if (!selectedItemId || !selectedItemType || !selectedItemName) return;

        if (confirm(`Apakah Anda yakin ingin menghapus ${selectedItemType} "${selectedItemName}"?`)) {
            let url = '';
            if (selectedItemType === 'folder') {
                url = `<?= site_url('hrd/folder/delete') ?>`; // Rute HRD untuk delete folder
            } else if (selectedItemType === 'file') {
                url = `<?= site_url('hrd/file/delete/') ?>${selectedItemId}`; // Rute HRD untuk delete file
            } else {
                alert('Tipe item tidak dikenal.');
                return;
            }

            // For folder delete, it's a POST with JSON body
            // For file delete, it's a GET (as per your route) but better as POST
            // I've changed the file delete route to POST for consistency
            let method = 'POST';
            let bodyData = null;
            if (selectedItemType === 'folder') {
                bodyData = JSON.stringify({ id: selectedItemId });
            }

            fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: bodyData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        alert(data.message);
                        window.location.reload(); // Reload page to reflect changes
                    } else {
                        alert('Gagal menghapus: ' + (data.message || 'Terjadi kesalahan.'));
                    }
                })
                .catch(error => console.error('Error deleting item:', error));
        }
        document.getElementById('floatingMenu').classList.add('hidden');
    });


    // --- INITIALIZATION FOR NEW BUTTON AND MODALS ---
    document.addEventListener('DOMContentLoaded', function () {
        const newButton = document.getElementById('newButton');
        const newDropdown = document.getElementById('newDropdown');

        // Create Folder elements
        const createFolderLink = document.getElementById('createFolderLink');
        const modalCreateFolder = document.getElementById('modalCreateFolder');
        const cancelCreateFolderModal = document.getElementById('cancelCreateFolderModal');
        const submitCreateFolder = document.getElementById('submitCreateFolder');
        const newFolderNameInput = document.getElementById('newFolderNameInput');

        // Upload File elements
        const uploadFileLink = document.getElementById('uploadFileLink');
        const modalUploadFile = document.getElementById('modalUploadFile');
        const cancelUploadFileModal = document.getElementById('cancelUploadFileModal');
        const submitUploadFile = document.getElementById('submitUploadFile');
        const uploadFileInput = document.getElementById('uploadFileInput');

        // --- DROPDOWN "BARU" LOGIC ---
        if (newButton) {
            newButton.addEventListener('click', function (event) {
                event.stopPropagation();
                newDropdown.classList.toggle('hidden');
            });
        }

        // Click outside to close dropdown (handled by general document click listener above)

        // --- MODAL "BUAT FOLDER BARU" LOGIC ---
        if (createFolderLink) {
            createFolderLink.addEventListener('click', function (event) {
                event.preventDefault();
                newDropdown.classList.add('hidden');
                modalCreateFolder.classList.remove('hidden');
            });
        }

        if (cancelCreateFolderModal) {
            cancelCreateFolderModal.addEventListener('click', function () {
                modalCreateFolder.classList.add('hidden');
            });
        }

        if (submitCreateFolder) {
            submitCreateFolder.addEventListener('click', function () {
                const folderName = newFolderNameInput.value.trim();
                if (folderName === '') {
                    alert('Nama folder tidak boleh kosong!');
                    return;
                }

                // ✨ PENTING: Ubah URL ke rute HRD ✨
                fetch(`<?= site_url('hrd/create-folder') ?>`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        name: folderName,
                        parent_id: window.currentFolderId,
                        // Anda perlu menentukan folder_type di sini,
                        // misalnya jika HRD membuat folder untuk staff, tipe-nya 'staff'
                        // atau jika ini folder pribadi HRD, tipe-nya 'hrd_personal'
                        folder_type: 'staff' // Ini asumsi, sesuaikan dengan kebutuhan Anda
                    })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            alert(data.message);
                            window.location.reload();
                        } else {
                            alert('Gagal membuat folder: ' + (data.message || 'Terjadi kesalahan.'));
                        }
                    })
                    .catch(error => console.error('Error creating folder:', error));
            });
        }


        // --- MODAL "UPLOAD FILE" LOGIC ---
        if (uploadFileLink) {
            uploadFileLink.addEventListener('click', function (event) {
                event.preventDefault();
                newDropdown.classList.add('hidden');
                modalUploadFile.classList.remove('hidden');
            });
        }

        if (cancelUploadFileModal) {
            cancelUploadFileModal.addEventListener('click', function () {
                modalUploadFile.classList.add('hidden');
            });
        }

        if (submitUploadFile) {
            submitUploadFile.addEventListener('click', function () {
                if (uploadFileInput.files.length === 0) {
                    alert('Silakan pilih file untuk diunggah.');
                    return;
                }
                const file = uploadFileInput.files[0];
                const formData = new FormData();
                formData.append('file', yourFileInputElement.files[0]); // Ini adalah file itu sendiri
                // Jika Anda ingin mengunggah ke folder tertentu:
                const targetFolderId = document.getElementById('currentFolderId').value; // Ambil dari input tersembunyi atau data attribute
                if (targetFolderId) {
                    formData.append('folder_id', targetFolderId); // Kirim folder_id di FormData
                }
                // ... lalu lakukan fetch seperti yang sudah Anda tampilkan
                fetch(`<?= site_url('hrd/uploadFile') ?>`, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        // Jika Anda menggunakan CSRF, pastikan CSRF token juga ditambahkan di sini
                        'X-CSRF-TOKEN': document.querySelector('input[name="<?= csrf_token() ?>"]').value
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            alert(data.message);
                            window.location.reload();
                        } else {
                            alert('Gagal mengunggah file: ' + (data.message || 'Terjadi kesalahan.'));
                        }
                    })
                    .catch(error => console.error('Error uploading file:', error));
            });
        }
    });
</script>

<?= $this->endSection() ?>