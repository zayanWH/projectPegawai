<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
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
                    class="absolute z-20 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg hidden"></div>
            </div>
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

    <div id="dropdownMenu" class="absolute z-10 mt-2 w-48 bg-white border border-gray-200 rounded-lg shadow-lg hidden">
        <a href="#" id="openCreateFolder" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">📁 Buat Folder</a>
        <a href="#" id="openUploadFolder" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">📂 Upload Folder</a>
    </div>
</div>

<input type="file" id="folderUploadInput" webkitdirectory="" mozdirectory="" style="display: none;">

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
                    <option value="shared">Shared Folder</option>
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
        const createFolderForm = document.getElementById('createFolderForm');
        const folderNameInput = document.getElementById('folderName');
        const folderTypeSelect = document.getElementById('folderType');
        const folderAccessSelect = document.getElementById('folderAccess');
        const sharedAccessContainer = document.getElementById('sharedAccessContainer');
        const accessRolesCheckboxes = document.querySelectorAll('input[name="access_roles[]"]');

        // --- Elemen-elemen untuk Modal "Unggah File" ---
        // const openUploadFile = document.getElementById('openUploadFile'); // Tidak ada di HTML Anda
        // const modalUploadFile = document.getElementById('modalUploadFile'); // Tidak ada di HTML Anda
        // const cancelUploadModal = document.getElementById('cancelUploadModal'); // Tidak ada di HTML Anda
        // const uploadFileBtn = document.getElementById('uploadFileBtn'); // Tidak ada di HTML Anda
        // const fileInput = document.getElementById('fileInput'); // Tidak ada di HTML Anda
        // const fileDescription = document.getElementById('fileDescription'); // Tidak ada di HTML Anda

        // --- Elemen untuk Unggah Folder ---
        const openUploadFolder = document.getElementById('openUploadFolder');
        const folderUploadInput = document.getElementById('folderUploadInput');

        // --- Elemen untuk Search ---
        const searchInput = document.getElementById('searchInput');
        const searchResults = document.getElementById('searchResults');


        // --- FUNGSI UTAMA UNTUK DROPDOWN DAN MODAL ---
        function showDropdown(element) {
            element.classList.remove('hidden');
        }

        function hideDropdown(element) {
            element.classList.add('hidden');
        }

        function showModal(modalElement) {
            modalElement.classList.remove('hidden');
        }

        function hideModal(modalElement) {
            modalElement.classList.add('hidden');
        }

        function resetCreateFolderForm() {
            createFolderForm.reset(); // Menggunakan form.reset() untuk mereset semua elemen form
            sharedAccessContainer.classList.add('hidden'); // Sembunyikan container peran akses
        }

        // --- EVENT LISTENERS UTAMA ---
        // Event Listener untuk tombol dropdown "Baru"
        if (dropdownButton && dropdownMenu) {
            dropdownButton.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();

                const isVisible = !dropdownMenu.classList.contains('hidden');
                if (isVisible) {
                    hideDropdown(dropdownMenu);
                } else {
                    document.querySelectorAll('.menu-dropdown').forEach(menu => hideDropdown(menu));
                    showDropdown(dropdownMenu);
                }
            });
        }

        document.addEventListener('click', function (event) {
            if (dropdownButton && dropdownMenu && !dropdownButton.contains(event.target) && !dropdownMenu.contains(event.target)) {
                hideDropdown(dropdownMenu);
            }
            document.querySelectorAll('.menu-dropdown').forEach(otherMenu => {
                hideDropdown(otherMenu);
            });
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

        // LOGIKA TAMBAHAN: Tampilkan/Sembunyikan checkbox peran berdasarkan jenis folder
        if (folderTypeSelect && sharedAccessContainer) {
            folderTypeSelect.addEventListener('change', function () {
                if (this.value === 'shared') {
                    sharedAccessContainer.classList.remove('hidden');
                } else {
                    sharedAccessContainer.classList.add('hidden');
                    folderAccessSelect.value = ''; // Reset dropdown akses
                    accessRolesCheckboxes.forEach(checkbox => checkbox.checked = false); // Hapus centang semua peran
                }
            });
        }

        // --- LOGIKA FETCH UNTUK MEMBUAT FOLDER (SUDAH DIPERBAIKI) ---
        createFolderForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const folderName = folderNameInput.value.trim();
            const folderType = folderTypeSelect.value;
            let endpoint = '';
            let payload = {};

            // Validasi Frontend
            if (folderName === '') {
                alert('Nama folder tidak boleh kosong!');
                return;
            }
            if (folderType === '') {
                alert('Silakan pilih jenis folder!');
                return;
            }

            // Tentukan endpoint dan payload berdasarkan jenis folder
            if (folderType === 'personal') {
                endpoint = '<?= site_url('hrd/create-folder-staff') ?>';
                payload = {
                    name: folderName,
                    parent_id: window.currentFolderId
                };
            } else if (folderType === 'shared') {
                endpoint = '<?= site_url('hrd/create-folder') ?>';
                let selectedAccessRoles = Array.from(accessRolesCheckboxes)
                    .filter(checkbox => checkbox.checked)
                    .map(checkbox => parseInt(checkbox.value));

                if (selectedAccessRoles.length === 0) {
                    alert('Untuk Shared Folder, minimal satu peran akses harus dipilih!');
                    return;
                }

                const folderAccess = folderAccessSelect.value;
                if (folderAccess === '') {
                    alert('Silakan pilih jenis akses untuk Shared Folder!');
                    return;
                }

                payload = {
                    name: folderName,
                    parent_id: window.currentFolderId,
                    folder_type: 'shared',
                    is_shared: 1,
                    shared_type: folderAccess,
                    access_roles: selectedAccessRoles
                };
            }

            // Lakukan fetch
            fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(payload)
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

        // --- LOGIKA FETCH UNTUK MENGUNGGAH FILE ---
        // Jika Anda ingin menambahkan modal unggah file, tambahkan kode di sini

        // --- LOGIKA UNTUK DROPDOWN AKSI PER BARIS TABEL ---
        function toggleMenu(button) {
            const menu = button.parentElement.querySelector('.menu-dropdown');
            if (menu) {
                document.querySelectorAll('.menu-dropdown').forEach(otherMenu => {
                    if (otherMenu !== menu) {
                        hideDropdown(otherMenu);
                    }
                });
                const isVisible = !menu.classList.contains('hidden');
                if (isVisible) {
                    hideDropdown(menu);
                } else {
                    showDropdown(menu);
                }
            }
        }
        window.toggleMenu = toggleMenu;

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