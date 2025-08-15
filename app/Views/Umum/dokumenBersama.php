<?= $this->extend('layout/main') ?>

<?= $this->section('pageTitle') ?>
Share Folder
<?= $this->endSection() ?>

<?= $this->section('pageLogo') ?>
<img src="<?= base_url('images/logo.jpg') ?>" alt="Logo USSI" class="h-10 w-auto rounded-lg">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="hidden md:block">
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <h1 class="text-2xl font-semibold text-gray-800">Shared Folder</h1>
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
            class="absolute z-10 mt-2 w-48 bg-white border border-gray-200 rounded-lg shadow-lg opacity-0 invisible transform scale-95 transition-all duration-200 ease-out">
            <a href="#" id="openCreateFolder" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">📁 Buat Folder</a>
        </div>
    </div>

    <input type="file" id="folderUploadInput" webkitdirectory style="display: none;" />

    <div class="bg-white rounded-lg shadow-sm">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                Dokumen Bersama
            </h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full" id="sharedFoldersTable">
                <thead class="bg-blue-600">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Nama
                            Folder
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Dari
                            Jabatan
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">
                            Pengunggah
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Ke
                            Jabatan
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Akses
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($sharedFolders)): ?>
                        <tr class="hover:bg-gray-50 empty-state-row" id="noFoldersRow">
                            <td colspan="6" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                Tidak ada folder yang di-share untuk Anda.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($sharedFolders as $folder): ?>
                            <tr class="hover:bg-gray-50 folder-row" data-folder-id="<?= esc($folder['id']) ?>"
                                data-folder-name="<?= esc($folder['name']) ?>"
                                data-folder-path="<?= esc($folder['path'] ?? $folder['id']) ?>"
                                data-folder-type="<?= esc($folder['folder_type'] ?? 'personal') ?>"
                                data-folder-is-shared="<?= esc($folder['is_shared'] ? '1' : '0') ?>"
                                data-folder-shared-type="<?= esc($folder['shared_type'] ?? '') ?>"
                                data-folder-owner-id="<?= esc($folder['owner_id']) ?>"
                                data-folder-owner-name="<?= esc($folder['owner_name'] ?? 'Unknown') ?>"
                                data-folder-created-at="<?= esc($folder['created_at'] ?? '') ?>"
                                data-folder-updated-at="<?= esc($folder['updated_at'] ?? '') ?>">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <a href="<?= base_url('umum/view-shared-folder/' . $folder['id']) ?>"
                                        class="block h-full w-full text-sm text-gray-900 hover:text-blue-700 hover:underline">
                                        <div class="flex items-center">
                                            <img src="<?= base_url('images/folder.png') ?>" alt="Folder Icon"
                                                class="w-5 h-5 mr-2">
                                            <?= esc($folder['name']) ?>
                                        </div>
                                    </a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?= esc($folder['owner_role']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?= esc($folder['owner_name']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?= esc($folder['access_roles'] ? implode(', ', json_decode($folder['access_roles'])) : '-') ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?= esc(ucfirst($folder['shared_type'])) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php if (($folder['folder_type'] ?? '') === 'shared' && ($folder['shared_type'] ?? '') !== 'read'): ?>
                                        <button
                                            onclick="showFloatingMenu(event, '<?= esc($folder['folder_type'] ?? '') ?>', '<?= esc($folder['id']) ?>', '<?= esc($folder['name']) ?>')"
                                            class="text-blue-600 hover:text-blue-900">⋮</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <tr class="hover:bg-gray-50 search-empty-row" style="display: none;">
                        <td colspan="6" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                            Tidak ada folder yang cocok dengan pencarian Anda.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
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
        </div>
    </div>

    <input type="file" id="folderUploadInput" webkitdirectory style="display: none;" />

    <div class="bg-white rounded-lg shadow-sm mb-6">
        <div class="p-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">Folder Terbaru</h2>
        </div>
        <div class="divide-y divide-gray-200">
            <?php if (!empty($sharedFolders)): ?>
                <?php foreach ($sharedFolders as $folder): ?>
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
                                <a href="<?= base_url('umum/view-shared-folder/' . $folder['id']) ?>"
                                    class="block font-medium text-gray-900 hover:text-blue-700 hover:underline">
                                    <?= esc($folder['name']) ?>
                                </a>
                                <div class="text-gray-500 text-xs">
                                    <?= 'Dari ' . esc($folder['owner_role']) ?>
                                </div>
                            </div>
                        </div>
                        <?php if (($folder['shared_type'] ?? '') !== 'read'): ?>
                            <button onclick="toggleMenu(this)" class="text-gray-600 hover:text-black  -900">⋮</button>
                        <?php endif; ?>
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

        <label class="block text-sm font-medium mb-1">Jenis Folder</label>
        <div class="relative mb-4">
            <select id="folderType" class="w-full border rounded-lg px-3 py-2 pr-10 appearance-none">
                <option disabled selected value="">Pilih jenis folder</option>
                <option value="shared">Shared Folder</option>
            </select>
            <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-gray-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                </svg>
            </div>
        </div>

        <?php
        $loggedInRole = $userRoleName ?? '';

        // Tentukan apakah setiap checkbox harus disabled
        $isStaffDisabled = false;
        $isSupervisorDisabled = false;
        $isManagerDisabled = false;
        $isDireksiDisabled = false;

        switch ($loggedInRole) {
            case 'Staff':
                $isSupervisorDisabled = true;
                $isManagerDisabled = true;
                $isDireksiDisabled = true;
                break;
            case 'Supervisor':
                $isManagerDisabled = true;
                $isDireksiDisabled = true;
                break;
            case 'Manajer':
                $isDireksiDisabled = true;
                break;
            case 'Direksi':
            case 'HRD': // Tambahkan HRD jika mereka juga bisa centang semua
            case 'Admin': // Tambahkan Admin jika mereka juga bisa centang semua
                // Semua enable, tidak perlu set disabled
                break;
            default:
                // Default: semua disabled jika peran tidak dikenali atau tidak ada
                $isStaffDisabled = true;
                $isSupervisorDisabled = true;
                $isManagerDisabled = true;
                $isDireksiDisabled = true;
                break;
        }
        ?>

        <div id="accessRolesContainer" class="grid grid-cols-2 gap-2 mb-4 hidden">
            <label>
                <input type="checkbox" name="accessRoles[]" value="Staff" class="mr-2" <?= $isStaffDisabled ? 'disabled' : '' ?>> Staff
            </label>
            <label>
                <input type="checkbox" name="accessRoles[]" value="Supervisor" class="mr-2" <?= $isSupervisorDisabled ? 'disabled' : '' ?>> Supervisor
            </label>
            <label>
                <input type="checkbox" name="accessRoles[]" value="Manager" class="mr-2" <?= $isManagerDisabled ? 'disabled' : '' ?>> Manager
            </label>
            <label>
                <input type="checkbox" name="accessRoles[]" value="Direksi" class="mr-2" <?= $isDireksiDisabled ? 'disabled' : '' ?>> Direksi
            </label>
        </div>

        <label class="block text-sm font-medium mb-1">Akses</label>
        <div class="relative mb-4">
            <select id="folderAccess" class="w-full border rounded-lg px-3 py-2 pr-10 appearance-none">
                <option disabled selected value="">Pilih akses</option>
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
        <input type="text" id="folderName" placeholder="Masukan nama folder"
            class="w-full border rounded-lg px-3 py-2 mb-4">

        <div class="flex justify-end space-x-4">
            <button id="cancelModal" class="text-blue-500">Batal</button>
            <button id="createFolderBtn" class="text-blue-600 font-semibold">Buat</button>
        </div>
    </div>
</div>

<div id="modalUploadFile"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/10 backdrop-blur-sm hidden">
    <div class="bg-white p-6 rounded-lg shadow-xl w-full max-w-md transition-all duration-300 ease-in-out">
        <h2 class="text-xl font-semibold mb-4">Unggah File</h2>

        <label class="block text-sm font-medium mb-1">Pilih File</label>
        <input type="file" id="fileInput" class="w-full border rounded-lg px-3 py-2 mb-4">

        <label class="block text-sm font-medium mb-1">Deskripsi (Opsional)</label>
        <textarea id="fileDescription" class="w-full border rounded-lg px-3 py-2 mb-4" rows="3"
            placeholder="Tambahkan deskripsi file..."></textarea>

        <div class="flex justify-end space-x-4">
            <button id="cancelUploadModal" class="text-blue-500">Batal</button>
            <button id="uploadFileBtn" class="text-blue-600 font-semibold">Unggah</button>
        </div>
    </div>
</div>



<?= $this->endSection() ?>



<?= $this->section('scripts') ?>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Definisi variabel global dari PHP
        window.currentFolderId = <?= json_encode($currentFolderId ?? null) ?>;
        window.currentUserId = <?= json_encode($currentUserId ?? null) ?>;
        window.currentUserRole = <?= json_encode($userRoleName ?? null) ?>;

        // Tentukan URL API secara dinamis berdasarkan peran pengguna
        let createFolderApiUrl = '';
        let uploadFileApiUrl = '';
        let uploadFromFolderApiUrl = '';

        const userRole = window.currentUserRole ? window.currentUserRole.toLowerCase() : 'staff';

        // Menentukan URL API berdasarkan peran pengguna
        if (userRole === 'manajer') {
            createFolderApiUrl = '<?= base_url('manager/create-folder') ?>';
            uploadFileApiUrl = '<?= base_url('manager/upload-file') ?>';
            uploadFromFolderApiUrl = '<?= base_url('manager/upload-from-folder') ?>';
        } else if (userRole === 'direksi') {
            createFolderApiUrl = '<?= base_url('direksi/create-folder') ?>';
            uploadFileApiUrl = '<?= base_url('direksi/upload-file') ?>';
            uploadFromFolderApiUrl = '<?= base_url('direksi/upload-from-folder') ?>';
        } else if (userRole === 'supervisor') {
            createFolderApiUrl = '<?= base_url('supervisor/create-folder') ?>';
            uploadFileApiUrl = '<?= base_url('supervisor/upload-file') ?>';
            uploadFromFolderApiUrl = '<?= base_url('supervisor/upload-from-folder') ?>';
        } else if (userRole === 'hrd') {
            createFolderApiUrl = '<?= base_url('hrd/create-folder') ?>';
            uploadFileApiUrl = '<?= base_url('hrd/upload-file') ?>';
            uploadFromFolderApiUrl = '<?= base_url('hrd/upload-from-folder') ?>';
        } else {
            // Default untuk Staff atau peran lain
            createFolderApiUrl = '<?= base_url('staff/create-folder') ?>';
            uploadFileApiUrl = '<?= base_url('staff/upload-file') ?>';
            uploadFromFolderApiUrl = '<?= base_url('staff/upload-from-folder') ?>';
        }

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
        const accessRolesCheckboxes = document.querySelectorAll('input[name="accessRoles[]"]');

        // --- Elemen-elemen untuk Modal "Unggah File" ---
        const openUploadFile = document.getElementById('openUploadFile');
        const modalUploadFile = document.getElementById('modalUploadFile');
        const cancelUploadModal = document.getElementById('cancelUploadModal');
        const uploadFileBtn = document.getElementById('uploadFileBtn');
        const fileInput = document.getElementById('fileInput');
        const fileDescription = document.getElementById('fileDescription');

        // --- Elemen untuk Unggah Folder ---
        const openUploadFolder = document.getElementById('openUploadFolder');
        const folderUploadInput = document.getElementById('folderUploadInput');


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
            folderTypeSelect.value = '';
            folderAccessSelect.value = '';
            accessRolesContainer.classList.add('hidden');
            accessRolesCheckboxes.forEach(checkbox => checkbox.checked = false);
        }

        function resetUploadFileForm() {
            fileInput.value = '';
            fileDescription.value = '';
        }

        // --- EVENT LISTENERS UTAMA ---

        // Event Listener untuk tombol dropdown "Baru"
        if (dropdownButton && dropdownMenu) {
            dropdownButton.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();

                document.querySelectorAll('.menu-dropdown').forEach(otherMenu => {
                    hideDropdown(otherMenu);
                });

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

        if (dropdownButtonMobile && dropdownMenuMobile) {
            dropdownButtonMobile.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();

                document.querySelectorAll('.menu-dropdown').forEach(otherMenu => {
                    hideDropdown(otherMenu);
                });

                const isVisible = dropdownMenuMobile.classList.contains('visible');
                if (isVisible) {
                    hideDropdown(dropdownMenuMobile);
                } else {
                    showDropdown(dropdownMenuMobile);
                }
            });
        }

        // Tutup dropdown "Baru" jika pengguna mengklik di luar area dropdown atau tombol
        document.addEventListener('click', function (event) {
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
                    formData.append('file', files[i]);
                    formData.append('relativePath', files[i].webkitRelativePath);
                    formData.append('parent_id', window.currentFolderId || null);

                    try {
                        const response = await fetch(uploadFromFolderApiUrl, {
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
                if (this.value === 'shared') {
                    accessRolesContainer.classList.remove('hidden');
                } else {
                    accessRolesContainer.classList.add('hidden');
                    accessRolesCheckboxes.forEach(checkbox => checkbox.checked = false);
                }
            });
        }

        // --- LOGIKA FETCH UNTUK MEMBUAT FOLDER ---
        if (createFolderBtn && folderNameInput && folderTypeSelect && folderAccessSelect) {
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

                // Validasi Frontend
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
                if (folderType === 'shared' && selectedAccessRoles.length === 0) {
                    alert('Untuk Shared Folder, minimal satu peran akses harus dipilih!');
                    return;
                }

                fetch(createFolderApiUrl, { // PENTING: Menggunakan URL dinamis
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
                        parent_id: window.currentFolderId
                    })
                })
                    .then(response => {
                        const contentType = response.headers.get('content-type');
                        if (contentType && contentType.indexOf('application/json') !== -1) {
                            return response.json();
                        } else {
                            return response.text().then(text => {
                                console.error('Server returned non-JSON response:', text);
                                throw new Error('Server returned non-JSON response. Check PHP error logs. Response: ' + text);
                            });
                        }
                    })
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
                                    errorMessages += `${data.errors[key]}\n`;
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
                formData.append('file', file);
                formData.append('description', description);
                formData.append('parent_id', window.currentFolderId);
                formData.append('user_id', window.currentUserId);

                fetch(uploadFileApiUrl, { // PENTING: Menggunakan URL dinamis
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                    .then(response => {
                        const contentType = response.headers.get('content-type');
                        if (contentType && contentType.indexOf('application/json') !== -1) {
                            return response.json();
                        } else {
                            return response.text().then(text => {
                                console.error('Server returned non-JSON response for upload:', text);
                                throw new Error('Server returned non-JSON response for upload. Check PHP error logs. Response: ' + text);
                            });
                        }
                    })
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
                                    errorMessages += `${data.errors[key]}\n`;
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

        // --- LOGIKA PENCARIAN BARU UNTUK FILTER TABEL ---
        const searchInput = document.getElementById('searchInput');
        const tableRows = document.querySelectorAll('.folder-row');
        const noFoldersRow = document.getElementById('noFoldersRow');
        const searchEmptyRow = document.querySelector('.search-empty-row');

        if (searchInput) {
            searchInput.addEventListener('keyup', function (event) {
                const searchTerm = event.target.value.toLowerCase();
                let visibleRowsCount = 0;

                tableRows.forEach(row => {
                    const folderName = row.cells[0].textContent.toLowerCase();
                    const dariJabatan = row.cells[1].textContent.toLowerCase();
                    const pengunggah = row.cells[2].textContent.toLowerCase();
                    const keJabatan = row.cells[3].textContent.toLowerCase();

                    if (folderName.includes(searchTerm) || dariJabatan.includes(searchTerm) || pengunggah.includes(searchTerm) || keJabatan.includes(searchTerm)) {
                        row.style.display = '';
                        visibleRowsCount++;
                    } else {
                        row.style.display = 'none';
                    }
                });

                if (searchEmptyRow) {
                    if (visibleRowsCount === 0) {
                        searchEmptyRow.style.display = '';
                    } else {
                        searchEmptyRow.style.display = 'none';
                    }
                }

                if (noFoldersRow) {
                    noFoldersRow.style.display = 'none';
                }
            });

            searchInput.addEventListener('input', function () {
                if (this.value.trim() === '') {
                    tableRows.forEach(row => row.style.display = '');
                    if (searchEmptyRow) {
                        searchEmptyRow.style.display = 'none';
                    }
                    if (tableRows.length === 0 && noFoldersRow) {
                        noFoldersRow.style.display = '';
                    }
                }
            });
        }

        const searchInputMobile = document.getElementById('searchInputMobile');
        // Selektor ini akan mencari semua DIV yang berfungsi sebagai baris folder
        const folderRowsMobile = document.querySelectorAll('.block.md\\:hidden .flex.items-center.justify-between');
        const noFoldersRowMobile = document.querySelector('.block.md\\:hidden .flex.items-center.justify-center');

        if (searchInputMobile) {
            searchInputMobile.addEventListener('keyup', function (event) {
                const searchTerm = event.target.value.toLowerCase();
                let visibleRowsCount = 0;

                folderRowsMobile.forEach(row => {
                    // Ambil teks dari elemen <a> (nama folder)
                    const folderName = row.querySelector('a').textContent.toLowerCase();
                    // Ambil teks dari div di bawahnya (dari jabatan)
                    const dariJabatan = row.querySelector('.text-gray-500.text-xs').textContent.toLowerCase();

                    // Cek apakah ada kecocokan
                    if (folderName.includes(searchTerm) || dariJabatan.includes(searchTerm)) {
                        row.style.display = 'flex'; // Tampilkan baris folder
                        visibleRowsCount++;
                    } else {
                        row.style.display = 'none'; // Sembunyikan baris folder
                    }
                });

                if (noFoldersRowMobile) {
                    noFoldersRowMobile.style.display = (visibleRowsCount === 0) ? 'flex' : 'none';
                }
            });

            searchInputMobile.addEventListener('input', function () {
                if (this.value.trim() === '') {
                    folderRowsMobile.forEach(row => row.style.display = 'flex');
                    if (noFoldersRowMobile && folderRowsMobile.length === 0) {
                        noFoldersRowMobile.style.display = 'flex';
                    }
                }
            });
        }
    });
</script>
<?= $this->endSection() ?>