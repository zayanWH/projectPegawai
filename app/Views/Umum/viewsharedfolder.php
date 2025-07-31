<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<div class="bg-white rounded-lg shadow-sm p-6 mb-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-2 text-xl font-semibold text-gray-800">
            <a href="<?= base_url('umum/dokumen-bersama') ?>" class="text-blue-600 hover:text-blue-800">Kembali</a>
            <?php if (!empty($breadcrumbs)): ?>
                <?php foreach ($breadcrumbs as $breadcrumb): ?>
                    <span class="text-gray-500">/</span>
                    <a href="<?= base_url('umum/view-shared-folder/' . $breadcrumb['id']) ?>"
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
    <button id="dropdownButton"
        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium flex items-center space-x-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
        </svg>
        <span>Baru</span>
    </button>

    <div id="dropdownMenu"
        class="absolute z-10 mt-2 w-48 bg-white border border-gray-200 rounded-lg shadow-lg opacity-0 invisible transform scale-95 transition-all duration-200 ease-out">
        <a href="#" id="openCreateFolder" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">📁 Buat Folder</a>
        <a href="#" id="openUploadFile" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">⬆️ Upload File</a>
    </div>
</div>

<div id="modalCreateFolder"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/10 backdrop-blur-sm hidden">
    <div class="bg-white p-6 rounded-lg shadow-xl w-full max-w-md transition-all duration-300 ease-in-out">
        <h2 class="text-xl font-semibold mb-4">Buat Folder Baru</h2>
        <label class="block text-sm font-medium">Nama Folder</label>
        <input type="text" id="folderName" placeholder="Masukkan nama folder"
            class="w-full border rounded-lg px-3 py-2 mb-4">
        <div class="flex justify-end space-x-4">
            <button id="cancelModal" class="text-blue-500 hover:text-blue-700">Batal</button>
            <button id="createFolderBtn"
                class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-md">Buat</button>
        </div>
    </div>
</div>

<div id="modalUploadFile"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/10 backdrop-blur-sm hidden">
    <div class="bg-white p-6 rounded-lg shadow-xl w-full max-w-md transition-all duration-300 ease-in-out">
        <h2 class="text-xl font-semibold mb-4">Unggah File</h2>
        <label class="block text-sm font-medium mb-1">Pilih File</label>
        <input type="file" id="fileInput" class="w-full border rounded-lg px-3 py-2 mb-4">
        <div class="flex justify-end space-x-4">
            <button id="cancelUploadModal" class="text-blue-500 hover:text-blue-700">Batal</button>
            <button id="uploadFileBtn"
                class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-md">Unggah</button>
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
        <table class="w-full" id="mainTable">
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
                <?php if (empty($sharedFolders) && empty($sharedFiles)): ?>
                    <tr class="hover:bg-gray-50 empty-state-row" id="noItemsRow">
                        <td colspan="4" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                            Folder ini kosong.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php if (!empty($sharedFolders)): ?>
                        <?php foreach ($sharedFolders as $folder): ?>
                            <tr class="hover:bg-gray-50 item-row folder-row" data-item-type="folder"
                                data-item-name="<?= esc($folder['name']) ?>">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <a href="<?= base_url('umum/view-shared-folder/' . $folder['id']) ?>"
                                        class="block h-full w-full text-sm text-gray-900 hover:text-blue-700 hover:underline">
                                        <div class="flex items-center">
                                            <svg class="w-5 h-5 text-blue-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z">
                                                </path>
                                            </svg>
                                            <?= esc($folder['name']) ?>
                                        </div>
                                    </a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    Folder
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?= date('d M Y', strtotime($folder['created_at'])) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <button onclick="toggleMenu(this)" class="text-blue-600 hover:text-blue-900">⋮</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <?php if (!empty($sharedFiles)): ?>
                        <?php foreach ($sharedFiles as $file): ?>
                            <tr class="hover:bg-gray-50 item-row file-row" data-item-type="file"
                                data-item-name="<?= esc($file['file_name']) ?>">
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
                <tr class="hover:bg-gray-50 search-empty-row" style="display: none;">
                    <td colspan="4" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                        Tidak ada item yang cocok dengan pencarian Anda.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>


<?= $this->section('scripts') ?>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Variabel global yang diperlukan, diambil dari PHP.
        // Ini adalah perbaikan utama. currentFolderId akan memiliki nilai dari URL.
        window.currentFolderId = <?= json_encode($currentFolderId ?? null) ?>;
        window.currentUserId = <?= json_encode(session()->get('user_id') ?? null) ?>;

        // --- Elemen-elemen untuk Dropdown "Baru" dan Modal ---
        const dropdownButton = document.getElementById('dropdownButton');
        const dropdownMenu = document.getElementById('dropdownMenu');
        const openCreateFolder = document.getElementById('openCreateFolder');
        const modalCreateFolder = document.getElementById('modalCreateFolder');
        const cancelModal = document.getElementById('cancelModal');
        const createFolderBtn = document.getElementById('createFolderBtn');
        const folderNameInput = document.getElementById('folderName');

        const openUploadFile = document.getElementById('openUploadFile');
        const modalUploadFile = document.getElementById('modalUploadFile');
        const cancelUploadModal = document.getElementById('cancelUploadModal');
        const uploadFileBtn = document.getElementById('uploadFileBtn');
        const fileInput = document.getElementById('fileInput');

        // --- FUNGSI UTAMA UNTUK DROPDOWN DAN MODAL ---

        function showDropdown(element) {
            element.classList.remove('opacity-0', 'invisible', 'scale-95');
            element.classList.add('opacity-100', 'visible', 'scale-100');
        }

        function hideDropdown(element) {
            element.classList.remove('opacity-100', 'visible', 'scale-100');
            element.classList.add('opacity-0', 'invisible', 'scale-95');
        }

        function showModal(modalElement) {
            modalElement.classList.remove('hidden');
        }

        function hideModal(modalElement) {
            modalElement.classList.add('hidden');
        }

        function resetCreateFolderForm() {
            folderNameInput.value = '';
        }

        function resetUploadFileForm() {
            fileInput.value = '';
        }

        // --- EVENT LISTENERS UTAMA ---

        // Event Listener untuk tombol dropdown "Baru"
        if (dropdownButton && dropdownMenu) {
            dropdownButton.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                const isVisible = dropdownMenu.classList.contains('visible');
                if (isVisible) {
                    hideDropdown(dropdownMenu);
                } else {
                    showDropdown(dropdownMenu);
                }
            });
        }

        // Tutup dropdown "Baru" jika pengguna mengklik di luar area dropdown atau tombol
        document.addEventListener('click', function (event) {
            if (dropdownButton && dropdownMenu && !dropdownButton.contains(event.target) && !dropdownMenu.contains(event.target)) {
                hideDropdown(dropdownMenu);
            }
        });

        // Event Listener untuk link "Buat Folder"
        if (openCreateFolder && modalCreateFolder) {
            openCreateFolder.addEventListener('click', function (e) {
                e.preventDefault();
                hideDropdown(dropdownMenu);
                resetCreateFolderForm();
                showModal(modalCreateFolder);
            });
        }

        // Event Listener untuk link "Upload File"
        if (openUploadFile && modalUploadFile) {
            openUploadFile.addEventListener('click', function (e) {
                e.preventDefault();
                hideDropdown(dropdownMenu);
                resetUploadFileForm();
                showModal(modalUploadFile);
            });
        }

        // Event Listener untuk tombol "Batal" dan klik di luar modal "Buat Folder"
        if (cancelModal) {
            cancelModal.addEventListener('click', () => hideModal(modalCreateFolder));
            modalCreateFolder.addEventListener('click', (e) => {
                if (e.target === modalCreateFolder) hideModal(modalCreateFolder);
            });
        }

        // Event Listener untuk tombol "Batal" dan klik di luar modal "Upload File"
        if (cancelUploadModal) {
            cancelUploadModal.addEventListener('click', () => hideModal(modalUploadFile));
            modalUploadFile.addEventListener('click', (e) => {
                if (e.target === modalUploadFile) hideModal(modalUploadFile);
            });
        }

        // --- LOGIKA FETCH UNTUK MEMBUAT FOLDER (SUDAH DIPERBAIKI) ---
        if (createFolderBtn && folderNameInput) {
            createFolderBtn.addEventListener('click', function () {
                const folderName = folderNameInput.value.trim();
                if (folderName === '') {
                    alert('Nama folder tidak boleh kosong!');
                    return;
                }

                fetch('<?= base_url('staff/create-folder') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        name: folderName,
                        parent_id: window.currentFolderId, // Menggunakan ID folder saat ini
                        folder_type: 'shared', // Pastikan folder_type sudah benar
                        is_shared: 1, // Pastikan is_shared diset 1
                        owner_id: window.currentUserId
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
                    .catch(error => {
                        console.error('Error saat membuat folder:', error);
                        alert('Terjadi kesalahan saat berkomunikasi dengan server.');
                    });
            });
        }

        // --- LOGIKA FETCH UNTUK MENGUNGGAH FILE (SUDAH DIPERBAIKI) ---
        if (uploadFileBtn && fileInput) {
            uploadFileBtn.addEventListener('click', function () {
                const file = fileInput.files[0];
                if (!file) {
                    alert('Silakan pilih file untuk diunggah.');
                    return;
                }

                const formData = new FormData();
                formData.append('file_upload', file);
                formData.append('folder_id', window.currentFolderId); // Unggah ke folder yang sedang dilihat

                fetch('<?= base_url('staff/upload-file') ?>', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
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
                    .catch(error => {
                        console.error('Error saat mengunggah file:', error);
                        alert('Terjadi kesalahan saat berkomunikasi dengan server.');
                    });
            });
        }

        // --- LOGIKA PENCARIAN ---
        const searchInput = document.getElementById('searchInput');
        const tableRows = document.querySelectorAll('.item-row');
        const noItemsRow = document.getElementById('noItemsRow');
        const searchEmptyRow = document.querySelector('.search-empty-row');

        if (searchInput) {
            searchInput.addEventListener('keyup', function (event) {
                const searchTerm = event.target.value.toLowerCase();
                let visibleRowsCount = 0;

                tableRows.forEach(row => {
                    const itemName = row.dataset.itemName.toLowerCase();
                    if (itemName.includes(searchTerm)) {
                        row.style.display = '';
                        visibleRowsCount++;
                    } else {
                        row.style.display = 'none';
                    }
                });

                if (searchEmptyRow) {
                    if (visibleRowsCount === 0 && searchTerm !== '') {
                        searchEmptyRow.style.display = '';
                        if (noItemsRow) noItemsRow.style.display = 'none';
                    } else {
                        searchEmptyRow.style.display = 'none';
                        if (noItemsRow && tableRows.length === 0) {
                            noItemsRow.style.display = '';
                        }
                    }
                }
            });

            searchInput.addEventListener('input', function () {
                if (this.value.trim() === '') {
                    tableRows.forEach(row => row.style.display = '');
                    if (searchEmptyRow) {
                        searchEmptyRow.style.display = 'none';
                    }
                    if (noItemsRow && tableRows.length === 0) {
                        noItemsRow.style.display = '';
                    }
                }
            });
        }
    });
</script>
<?= $this->endSection() ?>