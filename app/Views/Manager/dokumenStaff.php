<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="bg-white rounded-lg shadow-sm p-6 mb-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <h1 class="text-2xl font-semibold text-gray-800">Dokumen Staff</h1>
        </div>
        <div class="flex items-center space-x-4">
            <div class="relative">
                <input type="text"
                    id="searchInput"
                    placeholder="Masukkan file dokumen..."
                    class="w-80 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <svg class="absolute right-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <div id="searchResults" class="absolute z-20 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg hidden"></div>
            </div>
            <img src="<?= base_url('images/logo.png') ?>" alt="Logo USSI" class="h-10 w-auto rounded-lg">
        </div>
    </div>
</div>


<div class="bg-white rounded-lg shadow-sm">
    <div class="p-6 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-800 flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            Folder Staff
        </h2>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-blue-600">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Nama Folder</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Dibuat Oleh</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Tipe Folder</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Tanggal Dibuat</th>
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
                            data-folder-owner-name="<?= esc($folder['owner_name'] ?? 'Unknown') ?>"
                            data-folder-created-at="<?= esc($folder['created_at']) ?>"
                            data-folder-updated-at="<?= esc($folder['updated_at']) ?>"
                            data-folder-path="<?= esc($folder['path'] ?? $folder['name']) ?>">

                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="<?= base_url('manager/view-staff-folder/' . $folder['id']) ?>"
                                    class="block h-full w-full text-sm text-gray-900 hover:text-blue-700 hover:underline">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 text-blue-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"></path>
                                        </svg>
                                        <?= esc($folder['name']) ?>
                                    </div>
                                </a>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div class="text-sm text-gray-500">
                                    <?= esc($folder['owner_email'] ?? '') ?>
                                </div>
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
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                            Belum ada folder dari staff yang tersedia.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if (!empty($orphanFiles)): ?>
        <div class="bg-white rounded-lg shadow-sm mt-6">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    File Tanpa Folder (Staff)
                </h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-blue-600">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Nama File</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Diupload Oleh</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Jenis</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Ukuran</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Tanggal Unggah</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($orphanFiles as $file): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
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
                                        <span class="text-sm text-gray-900"><?= esc($file['original_name'] ?? $file['file_name']) ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-6 w-6">
                                            <div class="h-6 w-6 rounded-full bg-green-500 flex items-center justify-center">
                                                <span class="text-xs font-medium text-white">
                                                    <?= strtoupper(substr($file['uploader_name'] ?? 'U', 0, 1)) ?>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="ml-2">
                                            <div class="text-sm font-medium text-gray-900">
                                                <?= esc($file['uploader_name'] ?? 'Unknown') ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?= esc(strtoupper($fileExtension)) ?> File
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?= esc(round($file['file_size'] / 1024, 2)) ?> KB
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?= date('d M Y', strtotime($file['created_at'])) ?>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <a href="<?= base_url('supervisor/download-staff-file/' . $file['id']) ?>"
                                        class="text-blue-600 hover:text-blue-900 mr-2">Unduh</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Definisi variabel global dari PHP
            window.currentFolderId = <?= json_encode($currentFolderId ?? null) ?>;
            window.currentUserId = <?= json_encode($currentUserId ?? null) ?>; // Pastikan ini dilewatkan dari controller
            window.currentUserRole = <?= json_encode($userRoleName ?? null) ?>; // Pastikan ini dilewatkan dari controller

            // --- Elemen-elemen untuk Dropdown "Baru" dan Modal "Buat Folder" ---
            const dropdownButton = document.getElementById('dropdownButton');
            const dropdownMenu = document.getElementById('dropdownMenu');
            const openCreateFolder = document.getElementById('openCreateFolder');
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
                dropdownButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation(); // ⭐ KUNCI: Mencegah event mencapai document

                    // Tutup semua menu dropdown tabel yang mungkin terbuka
                    document.querySelectorAll('.menu-dropdown').forEach(otherMenu => {
                        hideDropdown(otherMenu);
                    });

                    // Toggle dropdown "Baru"
                    const isVisible = dropdownMenu.classList.contains('visible');
                    if (isVisible) {
                        hideDropdown(dropdownMenu);
                    } else {
                        showDropdown(dropdownMenu);
                    }
                });
            }

            // Tutup dropdown "Baru" jika pengguna mengklik di luar area dropdown atau tombol
            document.addEventListener('click', function(event) {
                if (dropdownButton && dropdownMenu && !dropdownButton.contains(event.target) && !dropdownMenu.contains(event.target)) {
                    hideDropdown(dropdownMenu);
                }
            });

            // Event Listener untuk link "Buat Folder" di dropdown
            if (openCreateFolder && modalCreateFolder) {
                openCreateFolder.addEventListener('click', function(e) {
                    e.preventDefault();
                    hideDropdown(dropdownMenu); // Tutup dropdown "Baru"
                    resetCreateFolderForm(); // Reset form sebelum membuka
                    showModal(modalCreateFolder); // Tampilkan modal folder
                });
            }

            // Event Listener untuk link "Unggah File" di dropdown
            if (openUploadFile && modalUploadFile) {
                openUploadFile.addEventListener('click', function(e) {
                    e.preventDefault();
                    hideDropdown(dropdownMenu); // Tutup dropdown "Baru"
                    resetUploadFileForm(); // Reset form sebelum membuka
                    showModal(modalUploadFile); // Tampilkan modal upload
                });
            }

            // --- Event Listener untuk Unggah Folder ---
            if (openUploadFolder && folderUploadInput) {
                openUploadFolder.addEventListener('click', function(e) {
                    e.preventDefault();
                    hideDropdown(dropdownMenu);
                    folderUploadInput.click();
                });

                folderUploadInput.addEventListener('change', async function(e) {
                    const files = e.target.files;
                    if (files.length === 0) {
                        return;
                    }

                    // Simple progress indicator
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
                            const response = await fetch('<?= base_url('staff/upload-from-folder') ?>', {
                                method: 'POST',
                                body: formData,
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            });
                            const result = await response.json();
                            if (result.status !== 'success') {
                                // Stop on first error and alert user
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
                cancelModal.addEventListener('click', function() {
                    hideModal(modalCreateFolder);
                });
            }

            // Tutup modal "Buat Folder" ketika mengklik di luar area modal
            if (modalCreateFolder) {
                modalCreateFolder.addEventListener('click', function(e) {
                    if (e.target === modalCreateFolder) {
                        hideModal(modalCreateFolder);
                    }
                });
            }

            // Event Listener untuk tombol "Batal" di modal "Unggah File"
            if (cancelUploadModal && modalUploadFile) {
                cancelUploadModal.addEventListener('click', function() {
                    hideModal(modalUploadFile);
                });
            }

            // Tutup modal "Unggah File" ketika mengklik di luar area modal
            if (modalUploadFile) {
                modalUploadFile.addEventListener('click', function(e) {
                    if (e.target === modalUploadFile) {
                        hideModal(modalUploadFile);
                    }
                });
            }

            // LOGIKA TAMBAHAN: Tampilkan/Sembunyikan checkbox peran berdasarkan jenis folder
            if (folderTypeSelect && accessRolesContainer) {
                folderTypeSelect.addEventListener('change', function() {
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
                createFolderBtn.addEventListener('click', function() {
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

                    fetch('<?= base_url('staff/create-folder') ?>', {
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
                uploadFileBtn.addEventListener('click', function() {
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

                    fetch('<?= base_url('staff/upload-file') ?>', {
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
        });
    </script>

    <?= $this->endSection() ?>

    <?= $this->section('scripts') ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const searchResults = document.getElementById('searchResults');

            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    const query = this.value.trim();

                    if (query.length < 2) {
                        searchResults.innerHTML = '';
                        searchResults.classList.add('hidden');
                        return;
                    }

                    fetch('<?= site_url('staff/search') ?>', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: `q=${encodeURIComponent(query)}`
                        })
                        .then(response => response.json())
                        .then(data => {
                            searchResults.innerHTML = '';
                            if (data.length > 0) {
                                data.forEach(item => {
                                    const a = document.createElement('a');
                                    let url = '#';
                                    if (item.type === 'folder') {
                                        url = `<?= site_url('staff/folder/') ?>${item.id}`;
                                    } else {
                                        if (item.folder_id) {
                                            url = `<?= site_url('staff/folder/') ?>${item.folder_id}`;
                                        } else {
                                            url = `<?= site_url('staff/dokumen-staff') ?>`;
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

                document.addEventListener('click', function(event) {
                    if (!searchInput.contains(event.target) && !searchResults.contains(event.target)) {
                        searchResults.classList.add('hidden');
                    }
                });
            }
        });
    </script>
    <?= $this->endSection() ?>