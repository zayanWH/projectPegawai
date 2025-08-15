<?= $this->extend('layout/main') ?>

<?= $this->section('pageTitle') ?>
Dokumen Staff
<?= $this->endSection() ?>

<?= $this->section('pageLogo') ?>
<img src="<?= base_url('images/logo.jpg') ?>" alt="Logo USSI" class="h-10 w-auto rounded-lg">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="hidden md:block">
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <h1 class="text-2xl font-semibold text-gray-800">Dokumen Staff</h1>
            </div>
            <div class="flex items-center space-x-4">
                <div class="relative">
                    <input type="text" id="searchInput" placeholder="Masukkan file dokumen..."
                        class="w-80 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <svg class="absolute right-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <div id="searchResults"
                        class="absolute z-20 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg hidden">
                    </div>
                </div>
                <img src="<?= base_url('images/logo.jpg') ?>" alt="Logo USSI" class="h-10 w-auto rounded-lg">
            </div>
        </div>
    </div>

    <div class="relative inline-block text-left mb-6">
        <button id="dropdownButton"
            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium flex items-center space-x-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6">
                </path>
            </svg>
            <span>Baru</span>
        </button>

        <div id="dropdownMenu"
            class="absolute z-10 mt-2 w-48 bg-white border border-gray-200 rounded-lg shadow-lg hidden">
            <a href="#" id="openCreateFolder" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">📁 Buat Folder</a>
        </div>
    </div>

    <input type="file" id="folderUploadInput" webkitdirectory="" mozdirectory="" style="display: none;">
    <input type="file" id="folderUploadInputMobile" webkitdirectory="" mozdirectory="" style="display: none;">
</div>

<div class="hidden md:block">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-blue-600">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Nama Folder
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Tipe Folder
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Tanggal
                        Dibuat
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (!empty($personalFolders)): ?>
                    <?php foreach ($personalFolders as $folder): ?>
                        <tr class="hover:bg-gray-50" data-folder-id="<?= esc($folder['id']) ?>"
                            data-folder-name="<?= esc($folder['name']) ?>" data-folder-type="<?= esc($folder['folder_type']) ?>"
                            data-folder-is-shared="<?= esc($folder['is_shared'] ?? 0) ?>"
                            data-folder-shared-type="<?= esc($folder['shared_type'] ?? '') ?>"
                            data-folder-owner-id="<?= esc($folder['owner_id']) ?>"
                            data-folder-owner-name="<?= esc($folder['owner_display'] ?? $folder['owner_name'] ?? 'Unknown') ?>"
                            data-folder-created-at="<?= esc($folder['created_at']) ?>"
                            data-folder-updated-at="<?= esc($folder['updated_at']) ?>"
                            data-folder-path="<?= esc($folder['path'] ?? $folder['name']) ?>">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="<?= site_url('hrd/view-staff-folder/' . $folder['id']) ?>"
                                    class="block h-full w-full text-sm text-gray-900 hover:text-blue-700 hover:underline">
                                    <div class="flex items-center">
                                        <img src="<?= base_url('images/folder.png') ?>" alt="Folder Icon" class="w-5 h-5 mr-2">
                                        <?= esc($folder['name']) ?>
                                    </div>
                                </a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?= esc(ucfirst($folder['folder_type'])) ?>
                                <?php if (isset($folder['is_shared']) && $folder['is_shared'] == 1): ?>
                                    <?= esc(ucfirst($folder['shared_type'])) ?>
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

<div class="block md:hidden">
    <div class="bg-white rounded-lg shadow-sm p-4 mb-4">
        <div class="relative">
            <input type="text" id="searchInputMobile" placeholder="Masukkan file dokumen..."
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            <svg class="absolute right-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            <div id="searchResultsMobile"
                class="absolute z-20 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg hidden"></div>
        </div>
    </div>

    <div class="relative inline-block text-left mb-6">
        <button id="dropdownButtonMobile"
            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium flex items-center space-x-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6">
                </path>
            </svg>
            <span>Baru</span>
        </button>
        <div id="dropdownMenuMobile"
            class="absolute z-10 mt-2 w-48 bg-white border border-gray-200 rounded-lg shadow-lg opacity-0 invisible transform scale-95 transition-all duration-200 ease-out">
            <a href="#" id="openCreateFolderMobile" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">📁 Buat
                Folder</a>
            <a href="#" id="openUploadFolderMobile" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">📁 Unggah
                Folder</a>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm mb-6">
        <div class="p-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">Folder Terbaru</h2>
        </div>
        <div class="divide-y divide-gray-200">
            <?php if (!empty($personalFolders)): ?>
                <?php foreach ($personalFolders as $folder): ?>
                    <div class="relative flex items-center justify-between px-4 py-3 hover:bg-gray-50"
                        data-folder-id="<?= esc($folder['id']) ?>" data-folder-name="<?= esc($folder['name']) ?>"
                        data-folder-type="<?= esc($folder['folder_type']) ?>"
                        data-folder-is-shared="<?= esc($folder['is_shared'] ?? 0) ?>"
                        data-folder-shared-type="<?= esc($folder['shared_type'] ?? '') ?>"
                        data-folder-owner-id="<?= esc($folder['owner_id']) ?>"
                        data-folder-owner-name="<?= esc($folder['owner_display'] ?? $folder['owner_name'] ?? 'Unknown') ?>"
                        data-folder-created-at="<?= esc($folder['created_at']) ?>"
                        data-folder-updated-at="<?= esc($folder['updated_at']) ?>"
                        data-folder-path="<?= esc($folder['path'] ?? $folder['name']) ?>">
                        <div class="flex items-center space-x-4">
                            <img src="<?= base_url('images/folder.png') ?>" alt="Folder Icon" class="w-6 h-6">
                            <div>
                                <a href="<?= base_url('hrd/view-staff-folder/' . $folder['id']) ?>"
                                    class="block font-medium text-gray-900 hover:text-blue-700 hover:underline">
                                    <?= esc($folder['name']) ?>
                                </a>
                                <div class="text-gray-500 text-xs">
                                    <?= date('d M Y', strtotime($folder['created_at'])) ?>
                                </div>
                            </div>
                        </div>
                        <button onclick="toggleMenu(this)"
                            class="text-gray-500 hover:text-gray-900 text-xl font-bold">⋮</button>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="flex items-center justify-center px-6 py-4 text-gray-500">
                    Tidak ada folder yang tersedia.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div id="modalCreateFolder"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/10 backdrop-blur-sm hidden">
    <div class="bg-white p-6 rounded-lg shadow-xl w-full max-w-md transition-all duration-300 ease-in-out">
        <h2 class="text-xl font-semibold mb-4">Folder Baru</h2>
        <form id="createFolderForm">
            <label class="block text-sm font-medium mb-1">Jenis Folder</label>
            <div class="relative mb-4">
                <select id="folderType" name="folder_type"
                    class="w-full border rounded-lg px-3 py-2 pr-10 appearance-none">
                    <option value="" disabled selected>Pilih jenis folder</option>
                    <option value="personal">Personal Folder</option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
            </div>

            <div id="sharedAccessContainer" class="hidden">
                <label class="block text-sm font-medium mb-1">Pilih Peran Akses</label>
                <div class="grid grid-cols-2 gap-2 mb-4">
                    <?php if (isset($roles)): ?>
                        <?php foreach ($roles as $role): ?>
                            <label><input type="checkbox" name="access_roles[]" value="<?= esc($role['id']) ?>"
                                    class="mr-2"><?= esc($role['name']) ?></label>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <label class="block text-sm font-medium mb-1">Tipe Akses</label>
                <div class="relative mb-4">
                    <select id="folderAccess" name="shared_type"
                        class="w-full border rounded-lg px-3 py-2 pr-10 appearance-none">
                        <option value="" disabled selected>Pilih akses</option>
                        <option value="full">Full Access</option>
                        <option value="read_only">Read Only</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </div>
            </div>

            <label class="block text-sm font-medium">Nama Folder</label>
            <input type="text" id="folderName" name="name" placeholder="Masukan nama folder"
                class="w-full border rounded-lg px-3 py-2 mb-4">

            <div class="flex justify-end space-x-4">
                <button type="button" id="cancelModal" class="text-blue-500">Batal</button>
                <button type="submit" id="createFolderBtn" class="text-blue-600 font-semibold">Buat</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        window.currentFolderId = <?= json_encode($currentFolderId ?? null) ?>;
        window.currentUserId = <?= json_encode($currentUserId ?? null) ?>;
        window.currentUserRole = <?= json_encode($userRoleName ?? null) ?>;
        window.currentFolderName = "<?= esc($currentFolder['name'] ?? 'Root') ?>";

        // --- Elemen-elemen untuk Dropdown "Baru" dan Modal "Buat Folder" ---
        const dropdownButton = document.getElementById('dropdownButton');
        const dropdownMenu = document.getElementById('dropdownMenu');
        const openCreateFolder = document.getElementById('openCreateFolder');
        const dropdownButtonMobile = document.getElementById('dropdownButtonMobile');
        const dropdownMenuMobile = document.getElementById('dropdownMenuMobile');
        const openCreateFolderMobile = document.getElementById('openCreateFolderMobile');
        const modalCreateFolder = document.getElementById('modalCreateFolder');
        const cancelModal = document.getElementById('cancelModal');
        const createFolderBtn = document.getElementById('createFolderBtn');
        const folderNameInput = document.getElementById('folderName');
        const folderTypeSelect = document.getElementById('folderType');
        const folderAccessSelect = document.getElementById('folderAccess');
        const accessRolesContainer = document.getElementById('accessRolesContainer');
        const accessRolesCheckboxes = document.querySelectorAll('#accessRolesContainer input[type="checkbox"]');

        // --- Elemen-elemen untuk Modal "Unggah File" ---
        const openUploadFile = document.getElementById('openUploadFile');
        const modalUploadFile = document.getElementById('modalUploadFile');
        const cancelUploadModal = document.getElementById('cancelUploadModal');
        const uploadFileBtn = document.getElementById('uploadFileBtn');
        const fileInput = document.getElementById('fileInput');
        const fileDescription = document.getElementById('fileDescription');

        // --- Elemen untuk Unggah Folder ---
        const openUploadFolder = document.getElementById('openUploadFolder');
        const openUploadFolderMobile = document.getElementById('openUploadFolderMobile');
        const folderUploadInput = document.getElementById('folderUploadInput');
        const folderUploadInputMobile = document.getElementById('folderUploadInputMobile');

        const searchInput = document.getElementById('searchInput');
        const searchResults = document.getElementById('searchResults');


        // --- FUNGSI UTAMA UNTUK DROPDOWN DAN MODAL ---

        function showDropdown(element) {
            element.classList.remove('hidden', 'opacity-0', 'invisible', 'scale-95');
            element.classList.add('opacity-100', 'visible', 'scale-100');
        }

        function hideDropdown(element) {
            element.classList.remove('opacity-100', 'visible', 'scale-100');
            element.classList.add('hidden', 'opacity-0', 'invisible', 'scale-95');
        }

        function showModal(modalElement) {
            modalElement.classList.remove('hidden');
        }

        function hideModal(modalElement) {
            modalElement.classList.add('hidden');
        }

        function resetCreateFolderForm() {
            folderNameInput.value = '';
            folderTypeSelect.value = '';
            folderAccessSelect.value = '';
            if (accessRolesContainer) {
                accessRolesContainer.classList.add('hidden');
            }
            accessRolesCheckboxes.forEach(checkbox => checkbox.checked = false);
        }

        function resetUploadFileForm() {
            fileInput.value = '';
            fileDescription.value = '';
        }

        // --- EVENT LISTENERS UTAMA ---

        // Event Listener untuk tombol dropdown "Baru" (versi desktop)
        if (dropdownButton && dropdownMenu) {
            dropdownButton.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                document.querySelectorAll('.menu-dropdown').forEach(otherMenu => {
                    if (otherMenu !== dropdownMenu) {
                        hideDropdown(otherMenu);
                    }
                });
                const isVisible = !dropdownMenu.classList.contains('hidden');
                if (isVisible) {
                    hideDropdown(dropdownMenu);
                } else {
                    showDropdown(dropdownMenu);
                }
            });
        }

        // Event Listener untuk tombol dropdown "Baru" (versi mobile)
        if (dropdownButtonMobile && dropdownMenuMobile) {
            dropdownButtonMobile.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                document.querySelectorAll('.menu-dropdown').forEach(otherMenu => {
                    if (otherMenu !== dropdownMenuMobile) {
                        hideDropdown(otherMenu);
                    }
                });
                const isVisible = !dropdownMenuMobile.classList.contains('hidden');
                if (isVisible) {
                    hideDropdown(dropdownMenuMobile);
                } else {
                    showDropdown(dropdownMenuMobile);
                }
            });
        }

        // Tutup dropdown ketika mengklik di luar area dropdown
        document.addEventListener('click', function (event) {
            if (dropdownButton && dropdownMenu && !dropdownButton.contains(event.target) && !dropdownMenu.contains(event.target)) {
                hideDropdown(dropdownMenu);
            }
            if (dropdownButtonMobile && dropdownMenuMobile && !dropdownButtonMobile.contains(event.target) && !dropdownMenuMobile.contains(event.target)) {
                hideDropdown(dropdownMenuMobile);
            }
        });

        // Event Listener untuk link "Buat Folder" di dropdown
        if (openCreateFolder && modalCreateFolder) {
            openCreateFolder.addEventListener('click', function (e) {
                e.preventDefault();
                hideDropdown(dropdownMenu);
                resetCreateFolderForm();
                showModal(modalCreateFolder);
            });
        }

        if (openCreateFolderMobile && modalCreateFolder) {
            openCreateFolderMobile.addEventListener('click', function (e) {
                e.preventDefault();
                hideDropdown(dropdownMenuMobile);
                resetCreateFolderForm();
                showModal(modalCreateFolder);
            });
        }

        // Event Listener untuk link "Unggah File" di dropdown
        if (openUploadFile && modalUploadFile) {
            openUploadFile.addEventListener('click', function (e) {
                e.preventDefault();
                hideDropdown(dropdownMenu);
                resetUploadFileForm();
                showModal(modalUploadFile);
            });
        }

        // --- Event Listener untuk Unggah Folder ---
        if (openUploadFolder && folderUploadInput) {
            openUploadFolder.addEventListener('click', function (e) {
                e.preventDefault();
                hideDropdown(dropdownMenu);
                folderUploadInput.click();
            });

            folderUploadInput.addEventListener('change', async function (e) {
                const files = e.target.files;
                if (files.length === 0) {
                    return;
                }

                const progressDiv = document.createElement('div');
                progressDiv.className = 'fixed bottom-4 right-4 bg-blue-600 text-white p-4 rounded-lg shadow-lg';
                progressDiv.textContent = `Mengunggah 0 dari ${files.length} file...`;
                document.body.appendChild(progressDiv);

                for (let i = 0; i < files.length; i++) {
                    progressDiv.textContent = `Mengunggah ${i + 1} dari ${files.length} file: ${files[i].name}`;
                    const formData = new FormData();
                    formData.append('file_upload', files[i]);
                    formData.append('relativePath', files[i].webkitRelativePath);
                    if (window.currentFolderId) {
                        formData.append('folder_id', window.currentFolderId);
                    } else {
                        formData.append('folder_id', '');
                    }
                    formData.append('uploader_id', window.currentUserId);

                    try {
                        const response = await fetch('<?= base_url('staff/upload-file') ?>', {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        const result = await response.json();
                        if (result.status !== 'success') {
                            alert(`Gagal mengunggah ${files[i].name}: ${result.message}`);
                            document.body.removeChild(progressDiv);
                            return;
                        }
                    } catch (error) {
                        alert(`Terjadi kesalahan jaringan saat mengunggah ${files[i].name}.`);
                        console.error('Error uploading file from folder:', error);
                        document.body.removeChild(progressDiv);
                        return;
                    }
                }

                progressDiv.textContent = 'Semua file berhasil diunggah!';
                setTimeout(() => {
                    document.body.removeChild(progressDiv);
                    window.location.reload();
                }, 2000);
            });
        }

        // --- Event Listener untuk Unggah Folder Mobile ---
        if (openUploadFolderMobile && folderUploadInputMobile) {
            openUploadFolderMobile.addEventListener('click', function (e) {
                e.preventDefault();
                hideDropdown(dropdownMenuMobile);
                folderUploadInputMobile.click();
            });

            folderUploadInputMobile.addEventListener('change', async function (e) {
                const files = e.target.files;
                if (files.length === 0) {
                    return;
                }

                const progressDiv = document.createElement('div');
                progressDiv.className = 'fixed bottom-4 right-4 bg-blue-600 text-white p-4 rounded-lg shadow-lg';
                progressDiv.textContent = `Mengunggah 0 dari ${files.length} file...`;
                document.body.appendChild(progressDiv);

                for (let i = 0; i < files.length; i++) {
                    progressDiv.textContent = `Mengunggah ${i + 1} dari ${files.length} file: ${files[i].name}`;
                    const formData = new FormData();
                    formData.append('file_upload', files[i]);
                    formData.append('relativePath', files[i].webkitRelativePath);
                    if (window.currentFolderId) {
                        formData.append('folder_id', window.currentFolderId);
                    } else {
                        formData.append('folder_id', '');
                    }
                    formData.append('uploader_id', window.currentUserId);

                    try {
                        const response = await fetch('<?= base_url('staff/upload-file') ?>', {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        const result = await response.json();
                        if (result.status !== 'success') {
                            alert(`Gagal mengunggah ${files[i].name}: ${result.message}`);
                            document.body.removeChild(progressDiv);
                            return;
                        }
                    } catch (error) {
                        alert(`Terjadi kesalahan jaringan saat mengunggah ${files[i].name}.`);
                        console.error('Error uploading file from folder:', error);
                        document.body.removeChild(progressDiv);
                        return;
                    }
                }

                progressDiv.textContent = 'Semua file berhasil diunggah!';
                setTimeout(() => {
                    document.body.removeChild(progressDiv);
                    window.location.reload();
                }, 2000);
            });
        }

        // Event Listener untuk tombol "Batal" di modal "Buat Folder"
        if (cancelModal && modalCreateFolder) {
            cancelModal.addEventListener('click', function () {
                hideModal(modalCreateFolder);
            });
        }

        // Tutup modal "Buat Folder" ketika mengklik di luar area modal
        if (modalCreateFolder) {
            modalCreateFolder.addEventListener('click', function (e) {
                if (e.target === modalCreateFolder) {
                    hideModal(modalCreateFolder);
                }
            });
        }

        // Event Listener untuk tombol "Batal" di modal "Unggah File"
        if (cancelUploadModal && modalUploadFile) {
            cancelUploadModal.addEventListener('click', function () {
                hideModal(modalUploadFile);
            });
        }

        // Tutup modal "Unggah File" ketika mengklik di luar area modal
        if (modalUploadFile) {
            modalUploadFile.addEventListener('click', function (e) {
                if (e.target === modalUploadFile) {
                    hideModal(modalUploadFile);
                }
            });
        }

        // LOGIKA TAMBAHAN: Tampilkan/Sembunyikan checkbox peran berdasarkan jenis folder
        if (folderTypeSelect && accessRolesContainer) {
            folderTypeSelect.addEventListener('change', function () {
                if (accessRolesContainer) {
                    accessRolesContainer.classList.add('hidden');
                }
                accessRolesCheckboxes.forEach(checkbox => checkbox.checked = false);

                if (this.value === 'shared') {
                    if (accessRolesContainer) {
                        accessRolesContainer.classList.remove('hidden');
                    }
                }
            });
        }

        if (folderTypeSelect) {
            folderTypeSelect.dispatchEvent(new Event('change'));
        }

        // --- LOGIKA FETCH UNTUK MEMBUAT FOLDER ---
        if (createFolderBtn && folderNameInput && folderTypeSelect) {
            createFolderBtn.addEventListener('click', function () {
                const folderName = folderNameInput.value.trim();
                const folderType = folderTypeSelect.value;
                const folderAccess = folderAccessSelect.value;
                let selectedAccessRoles = [];

                if (folderType === 'shared') {
                    selectedAccessRoles = Array.from(accessRolesCheckboxes)
                        .filter(checkbox => checkbox.checked)
                        .map(checkbox => checkbox.value);
                }

                if (folderName === '') {
                    alert('Nama folder tidak boleh kosong!');
                    return;
                }
                if (folderType === '') {
                    alert('Silakan pilih jenis folder!');
                    return;
                }
                if (folderType === 'shared' && folderAccess === '') {
                    alert('Silakan pilih jenis akses untuk Shared Folder!');
                    return;
                }

                fetch('<?= base_url('hrd/create-folder-staff') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        name: folderName,
                        folder_type: folderType,
                        is_shared: folderType === 'shared' ? 1 : 0,
                        shared_type: folderType === 'shared' ? folderAccess : null,
                        owner_id: window.currentUserId,
                        access_roles: folderType === 'shared' ? selectedAccessRoles : null,
                        parent_id: window.currentFolderId // Akan null jika di root folder staff
                    })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            alert(data.message);
                            hideModal(modalCreateFolder);
                            window.location.reload();
                        } else {
                            alert('Error: ' + (data.message || 'Terjadi kesalahan.'));
                            if (data.errors) {
                                let errorMessages = '';
                                for (const key in data.errors) {
                                    errorMessages += `${data.errors[key]} \n`;
                                }
                                alert('Validasi Gagal:\n' + errorMessages);
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error saat membuat folder:', error);
                        alert('Terjadi kesalahan saat berkomunikasi dengan server untuk membuat folder.');
                    });
            });
        }

        // --- LOGIKA FETCH UNTUK MENGUNGGAH FILE ---
        if (uploadFileBtn && fileInput) {
            uploadFileBtn.addEventListener('click', function () {
                const file = fileInput.files[0];
                const description = fileDescription.value.trim();

                if (!file) {
                    alert('Silakan pilih file untuk diunggah!');
                    return;
                }

                const formData = new FormData();
                formData.append('file_upload', file);
                formData.append('fileDescription', description);
                if (window.currentFolderId) {
                    formData.append('folder_id', window.currentFolderId);
                } else {
                    formData.append('folder_id', '');
                }

                fetch('<?= base_url('staff/upload-file') ?>', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            alert(data.message);
                            hideModal(modalUploadFile);
                            window.location.reload();
                        } else {
                            alert('Error unggah: ' + (data.message || 'Terjadi kesalahan saat mengunggah file.'));
                            if (data.errors) {
                                let errorMessages = '';
                                for (const key in data.errors) {
                                    errorMessages += `${data.errors[key]} \n`;
                                }
                                alert('Validasi Unggah Gagal:\n' + errorMessages);
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error saat mengunggah file:', error);
                        alert('Terjadi kesalahan saat berkomunikasi dengan server untuk unggah file.');
                    });
            });
        }
    });

    function showFloatingMenu(event, type, id, name) {
        event.stopPropagation(); // Mencegah event menyebar ke body
        // Sembunyikan semua menu yang sedang terbuka
        const existingMenus = document.querySelectorAll('.floating-menu-container');
        existingMenus.forEach(menu => menu.remove());

        const menuHtml = `
            <div class="floating-menu-container absolute bg-white shadow-lg rounded-md z-50 py-1 w-48 text-sm" style="top: ${event.clientY}px; left: ${event.clientX}px;">
                <ul class="list-none p-0 m-0">
                    <li class="hover:bg-gray-100 cursor-pointer px-4 py-2" onclick="handleMenuClick('rename', '${type}', '${id}', '${name}', event)">
                        Rename
                    </li>
                    <li class="hover:bg-gray-100 cursor-pointer px-4 py-2" onclick="handleMenuClick('move', '${type}', '${id}', '${name}', event)">
                        Move
                    </li>
                    <li class="hover:bg-gray-100 cursor-pointer px-4 py-2 text-red-500" onclick="handleMenuClick('delete', '${type}', '${id}', '${name}', event)">
                        Delete
                    </li>
                </ul>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', menuHtml);

        const menuElement = document.querySelector('.floating-menu-container');

        const windowWidth = window.innerWidth;
        const windowHeight = window.innerHeight;
        const menuRect = menuElement.getBoundingClientRect();

        // Pindahkan menu jika keluar dari viewport
        if (menuRect.right > windowWidth) {
            menuElement.style.left = `${event.clientX - menuRect.width}px`;
        }
        if (menuRect.bottom > windowHeight) {
            menuElement.style.top = `${event.clientY - menuRect.height}px`;
        }

        // Tutup menu saat mengklik di luar
        setTimeout(() => {
            document.addEventListener('click', function closeMenu(e) {
                if (!menuElement.contains(e.target)) {
                    menuElement.remove();
                    document.removeEventListener('click', closeMenu);
                }
            });
        }, 0);
    }

    function handleMenuClick(action, type, id, name, event) {
        event.stopPropagation();
        // Lakukan sesuatu berdasarkan action, type, id, dan name
        console.log(`Action: ${action}, Type: ${type}, ID: ${id}, Name: ${name}`);
        const menuElement = document.querySelector('.floating-menu-container');
        if (menuElement) {
            menuElement.remove();
        }

        switch (action) {
            case 'rename':
                alert(`Rename ${name} with ID ${id}`);
                // Implementasi rename
                break;
            case 'move':
                alert(`Move ${name} with ID ${id}`);
                // Implementasi move
                break;
            case 'delete':
                alert(`Delete ${name} with ID ${id}`);
                // Implementasi delete
                break;
        }
    }

    if (searchInput) {
            searchInput.addEventListener('input', function() {
                const query = this.value.trim();

                if (query.length < 2) { // Minimal 2 karakter untuk pencarian
                    searchResults.innerHTML = '';
                    searchResults.classList.add('hidden');
                    return;
                }

                fetch('<?= site_url('hrd/searchStaff') ?>', { // Mengubah endpoint ke HRD
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: `q=${encodeURIComponent(query)}&parent_id=${window.currentFolderId || ''}` // Kirim parent_id untuk pencarian dalam folder tertentu
                    })
                    .then(response => response.json())
                    .then(data => {
                        searchResults.innerHTML = '';
                        if (data.length > 0) {
                            data.forEach(item => {
                                const a = document.createElement('a');
                                let url = '#';
                                // Menyesuaikan URL berdasarkan tipe item (folder/file) dan apakah ada folder_id
                                if (item.type === 'folder') {
                                    url = `<?= site_url('hrd/view-staff-folder/') ?>${item.id}`;
                                } else { // type === 'file'
                                    // Jika file memiliki folder_id, arahkan ke folder tersebut
                                    if (item.folder_id) {
                                        url = `<?= site_url('hrd/view-staff-folder/') ?>${item.folder_id}`;
                                    } else {
                                        // Jika tidak ada folder_id (misal: di root dokumen staff), arahkan ke halaman dokumen staff
                                        url = `<?= site_url('hrd/dokumen-staff') ?>`;
                                    }
                                }
                                a.href = url;
                                a.className = 'block px-4 py-2 text-gray-700 hover:bg-gray-100';
                                a.textContent = `${item.type === 'folder' ? '📁' : '📄'} ${item.name}`;
                                searchResults.appendChild(a);
                            });
                            searchResults.classList.remove('hidden');
                        } else {
                            const noResult = document.createElement('div');
                            noResult.className = 'px-4 py-2 text-gray-500';
                            noResult.textContent = 'Tidak ada hasil ditemukan';
                            searchResults.appendChild(noResult);
                            searchResults.classList.remove('hidden');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        searchResults.innerHTML = '<div class="px-4 py-2 text-red-500">Error saat mencari.</div>';
                        searchResults.classList.remove('hidden');
                    });
            });

            // Menyembunyikan hasil pencarian jika mengklik di luar search bar atau hasil
            document.addEventListener('click', function(event) {
                if (!searchInput.contains(event.target) && !searchResults.contains(event.target)) {
                    searchResults.classList.add('hidden');
                }
            });
        }
</script>


<?= $this->endSection() ?>