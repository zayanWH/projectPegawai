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
                <input type="text" id="searchInput" placeholder="Masukkan file dokumen..."
                    class="w-80 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <svg class="absolute right-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <div id="searchResults"
                    class="absolute z-20 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg hidden"></div>
            </div>
            <!-- Logo -->
            <img src="<?= base_url('images/logo.png') ?>" alt="Logo USSI" class="h-10 w-auto rounded-lg">
        </div>
    </div>
</div>

<!-- Tombol Baru -->
<div class="relative inline-block text-left mb-6">
    <button id="dropdownButton"
        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium flex items-center space-x-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
        </svg>
        <span>Baru</span>
    </button>

    <div id="dropdownMenu" class="absolute z-10 mt-2 w-48 bg-white border border-gray-200 rounded-lg shadow-lg hidden">
        <a href="#" id="openCreateFolder" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">📁 Buat Folder</a>
        <a href="#" id="openUploadFolder" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">📂 Upload Folder</a>
    </div>
</div>

<input type="file" id="folderUploadInput" webkitdirectory="" mozdirectory="" style="display: none;">

<!-- Modal -->
<div id="modalCreateFolder"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/10 backdrop-blur-sm hidden">
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
        <input type="text" id="folderName" placeholder="Masukan nama folder"
            class="w-full border rounded-lg px-3 py-2 mb-4">

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
                <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Tanggal Dibuat
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

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Definisi variabel global dari PHP (pastikan ini di-pass dari controller)
        window.currentFolderId = <?= json_encode($currentFolderId ?? null) ?>;
        window.currentUserId = <?= json_encode($currentUserId ?? null) ?>;
        window.currentUserRole = <?= json_encode($userRoleName ?? null) ?>;

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

        // --- Elemen untuk Search ---
        const searchInput = document.getElementById('searchInput');
        const searchResults = document.getElementById('searchResults');


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
            folderTypeSelect.value = ''; // Reset to default "Pilih jenis folder"
            folderAccessSelect.value = ''; // Reset to default "Pilih akses"
            accessRolesContainer.classList.add('hidden'); // Sembunyikan container peran akses
            accessRolesCheckboxes.forEach(checkbox => checkbox.checked = false); // Hapus centang semua peran
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
        document.addEventListener('click', function (event) {
            if (dropdownButton && dropdownMenu && !dropdownButton.contains(event.target) && !dropdownMenu.contains(event.target)) {
                hideDropdown(dropdownMenu);
            }
            // Tutup juga dropdown aksi item tabel jika terbuka
            document.querySelectorAll('.menu-dropdown').forEach(otherMenu => {
                hideDropdown(otherMenu);
            });
        });

        // Event Listener untuk link "Buat Folder" di dropdown
        if (openCreateFolder && modalCreateFolder) {
            openCreateFolder.addEventListener('click', function (e) {
                e.preventDefault();
                hideDropdown(dropdownMenu); // Tutup dropdown "Baru"
                resetCreateFolderForm(); // Reset form sebelum membuka
                showModal(modalCreateFolder); // Tampilkan modal folder
            });
        }

        // Event Listener untuk link "Unggah File" di dropdown
        if (openUploadFile && modalUploadFile) {
            openUploadFile.addEventListener('click', function (e) {
                e.preventDefault();
                hideDropdown(dropdownMenu); // Tutup dropdown "Baru"
                resetUploadFileForm(); // Reset form sebelum membuka
                showModal(modalUploadFile); // Tampilkan modal upload
            });
        }

        // --- Event Listener untuk Unggah Folder ---
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
                    formData.append('parent_id', window.currentFolderId || null); // Menggunakan currentFolderId dari PHP
                    formData.append('user_id', window.currentUserId); // Menggunakan currentUserId dari PHP

                    try {
                        const response = await fetch('<?= base_url('hrd/upload-from-folder') ?>', { // Mengubah endpoint ke HRD
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
                    accessRolesCheckboxes.forEach(checkbox => checkbox.checked = false); // Hapus centang saat tidak 'shared'
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
                // Validasi hanya jika folderType adalah 'shared'
                if (folderType === 'shared') {
                    if (folderAccess === '') {
                        alert('Silakan pilih jenis akses untuk Shared Folder!');
                        return;
                    }
                    if (selectedAccessRoles.length === 0) {
                        alert('Untuk Shared Folder, minimal satu peran akses harus dipilih!');
                        return;
                    }
                }


                fetch('<?= base_url('hrd/create-folder') ?>', { // Mengubah endpoint ke HRD
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

                fetch('<?= base_url('hrd/upload-file') ?>', { // Mengubah endpoint ke HRD
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

        // --- LOGIKA UNTUK DROPDOWN AKSI PER BARIS TABEL ---
        document.querySelectorAll('[id^="options-menu-"], [id^="file-options-menu-"]').forEach(button => {
            button.addEventListener('click', function (e) {
                e.stopPropagation(); // Penting: Menghentikan event click menyebar ke document

                const menu = this.nextElementSibling; // Mengambil elemen menu dropdown berikutnya

                // Tutup semua menu dropdown lainnya yang terbuka
                document.querySelectorAll('.menu-dropdown').forEach(otherMenu => {
                    if (otherMenu !== menu && otherMenu.classList.contains('visible')) {
                        hideDropdown(otherMenu);
                    }
                });

                // Toggle menu dropdown saat ini
                const isVisible = menu.classList.contains('visible');
                if (isVisible) {
                    hideDropdown(menu);
                } else {
                    showDropdown(menu);
                }
            });
        });

        // --- LOGIKA UNTUK EDIT DAN HAPUS FOLDER/FILE (Contoh Saja) ---
        // Anda perlu mengimplementasikan modal edit dan endpoint backend yang sesuai

        document.querySelectorAll('.folder-edit-btn').forEach(button => {
            button.addEventListener('click', function (e) {
                e.preventDefault();
                const folderId = this.dataset.id;
                // Di sini Anda akan memicu modal edit folder
                alert('Edit Folder ID: ' + folderId + '. Implementasi modal edit perlu ditambahkan.');
                // Contoh: showModal(modalEditFolder); populateEditFolderForm(folderId);
            });
        });

        document.querySelectorAll('.folder-delete-btn').forEach(button => {
            button.addEventListener('click', async function (e) {
                e.preventDefault();
                const folderId = this.dataset.id;
                if (confirm('Apakah Anda yakin ingin menghapus folder ini?')) {
                    try {
                        const response = await fetch('<?= base_url('hrd/delete-folder/') ?>' + folderId, {
                            method: 'POST', // Menggunakan POST untuk operasi hapus
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        const result = await response.json();
                        if (result.status === 'success') {
                            alert(result.message);
                            window.location.reload();
                        } else {
                            alert('Gagal menghapus folder: ' + (result.message || 'Terjadi kesalahan.'));
                        }
                    } catch (error) {
                        console.error('Error deleting folder:', error);
                        alert('Terjadi kesalahan saat menghapus folder.');
                    }
                }
            });
        });

        document.querySelectorAll('.file-edit-btn').forEach(button => {
            button.addEventListener('click', function (e) {
                e.preventDefault();
                const fileId = this.dataset.id;
                // Di sini Anda akan memicu modal edit file
                alert('Edit File ID: ' + fileId + '. Implementasi modal edit perlu ditambahkan.');
                // Contoh: showModal(modalEditFile); populateEditFileForm(fileId);
            });
        });

        document.querySelectorAll('.file-delete-btn').forEach(button => {
            button.addEventListener('click', async function (e) {
                e.preventDefault();
                const fileId = this.dataset.id;
                if (confirm('Apakah Anda yakin ingin menghapus file ini?')) {
                    try {
                        const response = await fetch('<?= base_url('hrd/delete-file/') ?>' + fileId, {
                            method: 'POST', // Menggunakan POST untuk operasi hapus
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        const result = await response.json();
                        if (result.status === 'success') {
                            alert(result.message);
                            window.location.reload();
                        } else {
                            alert('Gagal menghapus file: ' + (result.message || 'Terjadi kesalahan.'));
                        }
                    } catch (error) {
                        console.error('Error deleting file:', error);
                        alert('Terjadi kesalahan saat menghapus file.');
                    }
                }
            });
        });


        // --- LOGIKA PENCARIAN ---
        if (searchInput) {
            searchInput.addEventListener('input', function () {
                const query = this.value.trim();

                if (query.length < 2) { // Minimal 2 karakter untuk pencarian
                    searchResults.innerHTML = '';
                    searchResults.classList.add('hidden');
                    return;
                }

                fetch('<?= site_url('hrd/search') ?>', { // Mengubah endpoint ke HRD
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
            document.addEventListener('click', function (event) {
                if (!searchInput.contains(event.target) && !searchResults.contains(event.target)) {
                    searchResults.classList.add('hidden');
                }
            });
        }
    });
</script>


<?= $this->endSection() ?>