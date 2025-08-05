<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<div class="bg-white rounded-lg shadow-sm p-6 mb-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-2 text-xl font-semibold text-gray-800">
            <a href="<?= site_url('staff/dokumen-staff') ?>" class="text-blue-600 hover:text-blue-800">Dokumen Saya</a>
            <?php if (!empty($breadcrumbs)): ?>
                <?php foreach ($breadcrumbs as $breadcrumb): ?>
                    <span class="text-gray-500">/</span>
                    <a href="<?= site_url('staff/folder/' . $breadcrumb['id']) ?>"
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

<!-- Modal untuk Upload File -->
<div id="modalUploadFile"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/10 backdrop-blur-sm hidden">
    <div class="bg-white p-6 rounded-lg shadow-xl w-full max-w-md transition-all duration-300 ease-in-out">
        <h2 class="text-xl font-semibold mb-4">Upload File Baru</h2>
        <input type="file" id="uploadFileInput" class="w-full border rounded-lg px-3 py-2 mb-4">
        <div class="flex justify-end space-x-4">
            <button id="cancelUploadFileModal" class="text-blue-500 hover:text-blue-700">Batal</button>
            <button id="submitUploadFile"
                class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-md">Upload</button>
        </div>
    </div>
</div>

<!-- Modal untuk Membuat Folder Baru -->
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
                                    <a href="<?= base_url('staff/folder/' . $subFolder['id']) ?>"
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
                                    <button onclick="setSelectedFolderId(<?= $subFolder['id'] ?>); toggleMenu(this);"
                                        class="text-blue-600 hover:text-blue-900">⋮</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <?php if (!empty($filesInFolder)): ?>
                        <?php foreach ($filesInFolder as $file): ?>
                            <tr class="hover:bg-gray-50 item-with-context-menu" data-item-type="file"
                                data-item-id="<?= esc($file['id']) ?>" data-item-name="<?= esc($file['file_name']) ?>"
                                data-folder-id="<?= esc($file['id']) ?>" data-folder-name="<?= esc($file['file_name']) ?>"
                                data-folder-type="file" data-folder-is-shared="0" data-folder-shared-type="" data-folder-owner-id=""
                                data-folder-owner-name="" data-folder-created-at="<?= esc($file['created_at']) ?>"
                                data-folder-updated-at="<?= esc($file['updated_at']) ?>" data-folder-path="">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <a href="<?= base_url('staff/view-file/' . $file['id']) ?>" target="_blank"
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
                                    <button onclick="toggleMenu(this)" class="text-blue-600 hover:text-blue-900">⋮</button>
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
    // Hapus baris '<?= base_url() ?>' yang tidak ditugaskan ke variabel.
    // window.baseUrl akan didefinisikan di layout/main.php.

    window.currentFolderId = <?= esc($folderId ?? 'null') ?>;
    window.currentFolderType = '<?= esc($folderType ?? 'personal') ?>';
    window.isCurrentFolderShared = <?= (isset($isShared) && $isShared) ? 'true' : 'false' ?>;
    window.currentUserId = <?= session()->get('user_id') ?? 'null' ?>;

    function setSelectedFolderId(id) {
        window.selectedFolderId = id;
    }

    document.addEventListener('DOMContentLoaded', function () {
        // --- ALL ELEMENTS ---
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

        document.addEventListener('click', function (event) {
            if (newButton && !newButton.contains(event.target) && !newDropdown.contains(event.target)) {
                newDropdown.classList.add('hidden');
            }
        });

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

                fetch(`<?= site_url('staff/create-folder') ?>`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        name: folderName,
                        parent_id: window.currentFolderId,
                        folder_type: window.currentFolderType
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
                formData.append('file_upload', file);
                formData.append('folder_id', window.currentFolderId);

                fetch(`<?= site_url('staff/upload-file') ?>`, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
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

<?= $this->endsection() ?>